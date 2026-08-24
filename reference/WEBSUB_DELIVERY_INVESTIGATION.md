# WebSub delivery investigation — handoff

**Date:** 2026-08-24
**Environment:** production (Laravel Forge, VPS `elk-moon`, site dir `/home/forge/peristalsis.on-forge.com/current`, public host `https://peristalsis.tv`)
**Status:** root cause narrowed to the hub side, not proven. Fix designed but not built.
**Related roadmap items:** `[028]` (this diagnosis, archived in `ROADMAP_DONE.md`), `[029]` (the fix, in `ROADMAP.md`)
**Prior work:** `[021]` (push MVP), `[022]` (hardening), `[027]` (subscribe-missing sweep) — all archived in `ROADMAP_DONE.md`

---

## 1. The symptom

WebSub push ingestion was deployed to production on 2026-08-23. It worked during the deploy: all 193 channels subscribed, verified, and backfilled. By the next morning the feed showed no new videos at all.

The scheduler was running. No errors anywhere. No new videos.

A single forced poll of all 193 channels recovered **14 videos** that push had never delivered (video count 4190 to 4204), the newest published at 15:15 that same day. So uploads were happening normally and WebSub was not delivering them.

---

## 2. Architecture, in brief

Three distinct external services are involved. Do not conflate them.

| Service | Used for | Cost model |
| --- | --- | --- |
| YouTube RSS (`/feeds/videos.xml?channel_id=UC…`) | every video ingested, via `RssFetcher` | free, no API key, **no quota**; rate-limited per source IP |
| Google WebSub hub (`pubsubhubbub.appspot.com`) | pushing that same Atom content to us | free, no quota, **best effort, no SLA** |
| YouTube Data API v3 | channel-ID resolution only (`[023]`, not yet built) | 10,000 units/day quota |

Ingestion path after `[022]`: feed controllers are pure DB reads. Videos enter the database through exactly three routes.

1. **Push** — `WebSubController::receive` verifies the HMAC, calls `RssFetcher::ingest`, stamps `last_delivery_at`
2. **Add-time backfill** — one poll when a channel is first subscribed (`WebSubSubscriber::backfill`)
3. **Backstop** — `websub:backstop`, hourly, **failure-driven only**

There is no periodic freshness poll. This matters enormously and is covered in section 6.

---

## 3. Production state at time of diagnosis

Gathered via `php8.4 artisan tinker` on production, 2026-08-24 ~15:30 UTC:

| Signal | Value | Reading |
| --- | --- | --- |
| subscriptions by status | 193/193 `active` | hub verified every callback |
| `last_verified_at` (max) | 2026-08-23 19:17 | all subscribed at deploy |
| `expires_at` (min) | 2026-08-28 19:15 | leases healthy, standard 5-day Google grant |
| `never_delivered` | **191 of 193** | ~2 POSTs landed in 20 hours |
| `last_delivery_at` (max) | 2026-08-24 04:53 | delivery is not 100% dead, just ~95% lossy |
| `delivery_failed_at` | 0 | `RssFetcher::ingest` never threw |
| `renewal_failures` | 0 | subscribe POSTs to the hub succeed |
| `app.url` | `https://peristalsis.tv` | |
| `services.websub.callback_base` | `null` | falls back to `app.url` |
| sample callback | `https://peristalsis.tv/websub/rDOzz…` | |
| sample topic | `https://www.youtube.com/feeds/videos.xml?channel_id=UCMFSTAyvpl_yBXmq6JPfhBQ` | canonical form |
| `newest_video` before poll | 2026-08-23 18:19 | matches deploy-time backfill |
| forced poll result | `fetched: 193, failed: 0` | RSS polling itself is perfectly healthy |

---

## 4. What has been ruled out, and how

Each of these was tested directly. Do not re-test them without reason.

**The scheduler.** `schedule:list` shows `websub:renew` and `websub:backstop` both due at `0 * * * *`. Both commands are `->hourly()`, so `INFO  No scheduled commands are ready to run.` is the expected output 59 minutes out of 60 and is not a symptom. A duplicate broken Forge cron (`php artisan schedule:run`, no project path, failing with `Could not open input file: artisan`) was found and deleted; it had never executed anything.

**Callback reachability from the public internet.** From an external machine:

- `GET https://peristalsis.tv/websub/<token>` returns `400` (`Missing hub.challenge`, i.e. the app was reached and `firstOrFail` passed)
- `POST` with a garbage body returns `403` with body `Invalid signature` — our application's response, not an edge block
- Same POST with a Google FeedFetcher User-Agent also reaches the app
- Root returns `200`, no redirects, valid TLS

**The receive path end to end.** A correctly-signed POST of a real feed body to the production callback returns `200 OK` and ingests:

```bash
php8.4 artisan tinker --execute="\$s=App\Models\ChannelSubscription::with('channel')->first(); \$xml=Http::withHeaders(['User-Agent'=>config('services.websub.user_agent')])->get(\$s->channel->rssUrl())->body(); \$r=Http::withBody(\$xml,'application/atom+xml')->withHeaders(['X-Hub-Signature'=>'sha1='.hash_hmac('sha1',\$xml,\$s->secret)])->post(\$s->callbackUrl()); dump(['status'=>\$r->status(),'body'=>\$r->body()]);"
```

Result: `status: 200, body: "OK"`.

**Secret / token mismatch.** `WebSubSubscriber::postSubscribe` (`app/Services/WebSubSubscriber.php:103-125`) sends `hub.secret` and `hub.callback` derived from the same persisted `secret` and `callback_token` it stores. `ensureSubscribed` reuses existing values (`$subscription->callback_token ?: Str::random(40)`) rather than rotating them. `renew()` reuses the row untouched. No mismatch is possible on this path.

**Cloudflare.** `peristalsis.tv` is Cloudflare-proxied (resolves to `2606:4700::`), so the edge was the leading suspect for a while. Zone security analytics over the same 24h window:

- **7 mitigated requests out of 1.18k total** — nothing is being blocked at scale
- **89 POSTs zone-wide in 24h** — hub deliveries are not among them
- Bot Fight Mode is **off** (Cloudflare's own Security Insights export lists "Bot Fight Mode not enabled" as an active recommendation for this zone)
- The only `/websub/` rows in sampled logs are diagnostic probes: the 10:29-10:31 CDT entries match the external curl (10:31:03 CDT = 15:31:03 UTC, matching the response `date` header) and the VPS self-POST from its DigitalOcean address

Conclusion: hub POSTs are not being blocked at the edge. They are not arriving at the edge at all.

---

## 5. Hypotheses, ranked

**H1 — YouTube's WebSub is simply lossy.** *Most likely.* Subscriptions verify cleanly, then notifications arrive for some uploads and not others. Best-effort with no SLA, which `[021]` noted as a known risk going in. Note the shape: the 14 missing uploads never arrived at all. They were not late. This is intermittence, not latency.

**H2 — Topic-string mismatch.** The hub will verify a subscription to *any* topic URL, but only delivers for topics publishers actually ping. One character off produces exactly this signature: `active`, verified, silent. The observed topic is the canonical form, so this looks fine — but it is the classic version of this bug and should be confirmed across all 193 rows, not just the one sampled.

**H3 — Burst-subscribe throttling.** 193 topics subscribed in a single burst from a brand-new callback host. Google may deprioritize that. No way to observe from our side. Speculative.

**H4 — Something structural about our subscription rows.** Least likely, and the cheapest remaining test (section 7).

---

## 6. The real defect, independent of which hypothesis holds

Ingestion is push-only with no freshness floor. `RssFetcher::fetchForChannels` is reachable only from the backstop, and `WebSubBackstopCommand::reasonToRepoll` is **failure-driven**: it triggers on a missing subscription, callback-downtime recovery, a push that failed to ingest, a `failed` subscription, failing renewals, or a lapsed lease — and otherwise falls through to `silenceAnomaly`, which requires `count >= 3` stored uploads **plus** 48 hours of silence (`backstop_min_silence_hours`), gated further by `backstop_min_repoll_hours` (6).

During this outage **every one of those signals read healthy.** The backstop was a no-op while ~95% of uploads went missing. A hub that accepts subscriptions, verifies them, and then quietly stops delivering is invisible to every check in the system.

There is also a specific coverage hole worth fixing regardless: a subscription stuck in `Pending` never triggers a re-poll, because `leaseHasLapsed` short-circuits on `status !== Active` and `silenceAnomaly` needs three uploads and two days of silence.

---

## 7. Open tests

**7.1 — What is different about the two that delivered?** Highest-value remaining datum. If those rows are structurally identical to the other 191, H4 dies and H1 becomes the working conclusion.

```bash
php8.4 artisan tinker --execute="dump(App\Models\ChannelSubscription::whereNotNull('last_delivery_at')->with('channel')->get()->map(fn(\$s)=>['ch'=>\$s->channel->channel_id,'topic'=>\$s->topic_url,'created'=>(string)\$s->created_at,'verified'=>(string)\$s->last_verified_at,'delivered'=>(string)\$s->last_delivery_at,'lease'=>\$s->lease_seconds])->all());"
```

Note: one of these may now be an artifact of the signed self-POST in section 4, which would have stamped `last_delivery_at`. Cross-check timestamps against 2026-08-24 15:29 UTC before drawing conclusions.

**7.2 — Confirm topic strings across all rows (H2).** Look for any deviation from the canonical `https://www.youtube.com/feeds/videos.xml?channel_id=UC…`. Compare `topic_url` against `Channel::rssUrl()` for every row, and count how many do not match the canonical prefix.

**7.3 — Second reading after 24h.** The subscriptions were ~20 hours old at diagnosis. A 2-of-14 delivery ratio is bad, but delivery improving as subscriptions age has not been excluded. Re-read `never_delivered` and the `last_delivery_at` distribution before calling H1 settled.

---

## 8. Proposed fix

Keep WebSub exactly as it is — the subscribe and receive paths are correct and work when the hub cooperates. Demote it from *the* ingestion path to an accelerator, and make polling the backbone.

**8.1 — Poll floor.** A scheduled sweep polling the **N most-stale channels each hour**, N being a configured budget, stalest-first by `last_fetched_at`.

Deliberately a **fixed hourly budget**, not "poll everything older than X hours". A budget makes the outbound request rate a constant you set, independent of channel count. A staleness threshold grows linearly with the channel count and walks straight back into the 429 ceiling `[021]` was built to escape.

Freshness degrades gracefully as the install grows. At a 200/hour budget:

| Channels | Refresh interval | Requests/day |
| --- | --- | --- |
| 193 (today) | ~1 hour | ~4,600 |
| 1,000 | ~5 hours | ~4,600 |
| 2,000 | ~10 hours | ~4,600 |
| 10,000 | ~2 days | ~4,600 |

For scale: `[021]` put the 429 threshold at naive polling of ~1,500-2,000 channels, which on the old 30-minute TTL meant roughly 96,000 requests/day. A fixed 4,600/day is about 5% of that. Conditional GET is already implemented (`RssFetcher::pollHeaders` sends `If-None-Match` / `If-Modified-Since`, and 304s are handled at `RssFetcher.php:76-88`), so most of that budget returns `304 Not Modified` with no body transferred.

**8.2 — Delivery-drought signal.** A `websub:health` command (or an extension of the backstop) reporting counts by status, oldest and newest `last_delivery_at`, and the count of subscriptions that have never delivered — warning loudly when install-wide deliveries flatline. `services.websub.alert_webhook` exists but currently fires only on renewal failure, which stayed at zero throughout this outage. The `never_delivered = 191` figure was the tell here, and surfacing it took a manual tinker session.

**8.3 — Pending-subscription rule.** In `reasonToRepoll`, treat a subscription that has been `Pending` beyond a small multiple of `renew_retry_hours` as a re-poll candidate, closing the hole in section 6.

---

## 9. Evidence gotchas — read before re-investigating

Two misleading signals cost time during this investigation. Both will mislead the next investigator identically.

**An empty `laravel.log` is not evidence that pushes are absent.** A *successful* delivery logs nothing at all. `WebSubController::receive` logs only on invalid signature or ingest failure. Only `last_delivery_at` distinguishes "no pushes arrived" from "pushes arrived and worked".

**Nginx access logs prove nothing here.** Forge sets `access_log off;` per site. `/var/log/nginx/access.log` is the catch-all server block — port-scanner traffic and 444s, unrelated to your site. There are per-site `<id>-error.log` files but no per-site access log.

Also note that a `grep -c` returning zero matches exits non-zero and will silently break an `&&` command chain. Use `;` when chaining diagnostic greps. On this Forge box `grep` was not on the login shell's `PATH`; `/bin/grep` works.

---

## 10. Files worth reading

| Path | Why |
| --- | --- |
| `routes/console.php` | the two hourly scheduled commands |
| `app/Http/Controllers/WebSubController.php` | verify (GET) and receive (POST), HMAC check |
| `app/Services/WebSubSubscriber.php` | subscribe, renew, backfill, hub POST |
| `app/Services/RssFetcher.php` | `fetchForChannels`, `ingest`, conditional-GET `pollHeaders` |
| `app/Console/Commands/WebSubBackstopCommand.php` | `reasonToRepoll`, the failure-driven logic |
| `app/Models/ChannelSubscription.php` | `callbackUrl()` is derived at call time, not stored |
| `config/services.php:45-70` | all `websub.*` tuning knobs |
| `reference/FEED_PIPELINE_AUDIT.md` | the pipeline `[022]` replaced (Finding F1) |
| `tests/Feature/WebSub*Test.php` | five existing WebSub feature test files |

---

# 2026-08-24, second session

## 11. Follow-up findings

The open tests in section 7 were run and the root cause is now proven, one level further upstream than any of the ranked hypotheses placed it.

### 11.1 — H2 is dead

Test 7.2 ran on production: every `channel_subscriptions.topic_url` compared against the canonical string built from `channels.channel_id`.

```
mismatches: 0 (of 193)
```

One nuance the original doc missed while getting the right answer: `Channel::rssUrl()` returns the stored `channels.rss_url` column first and only falls back to building the canonical string (`app/Models/Channel.php:44`). All current writers (`ChannelResolver`, `SubscriptionController`, `GroupChannelListImporter`) construct the canonical form, so nothing was wrong — but a legacy row with an odd `rss_url` would have produced exactly this outage's signature. The zero-mismatch count closes both H2 and that latent variant of it.

### 11.2 — The hub has a per-subscription diagnostic page, and it settles everything

Google's hub exposes hub-side state per subscription:

```
https://pubsubhubbub.appspot.com/subscription-details
    ?hub.callback=<url-encoded callbackUrl()>
    &hub.topic=<url-encoded topic_url>
    &hub.secret=<the subscription's secret>
```

The real `hub.secret` is required — omitting it returns "The specified secret is incorrect". URLs are generated straight from production rows in tinker (`callbackUrl()`, `topic_url`, `secret`). This page was the missing instrument: it is the only view of the hub's side of the relationship, and it distinguishes "hub never had content" from "hub tried to deliver and failed" — the distinction sections 4-5 could not make from our side.

### 11.3 — The decisive read

Sampled 3 random subscriptions, then 7 subscriptions belonging to channels among the 14 recovered uploads — channels where an upload after subscribe time is *proven*. Every page, all 10, read identically:

| Hub field | Value |
| --- | --- |
| State | `verified` |
| Last successful verification | 2026-08-23 ~19:16 UTC (deploy time) |
| Expiration | 2026-08-28 (healthy 5-day lease) |
| Content received | **n/a** |
| Content delivered | n/a |
| Last delivery error | n/a |
| Aggregate statistics | 0 delivery request(s), 0% errors |

**"Content received: n/a" on topics with proven uploads is the whole story.** The hub never received content from YouTube for these feeds. There were no failed deliveries to us — there was nothing to deliver. The loss is on YouTube's publish ping to its own hub, upstream of everything sections 4-5 investigated.

### 11.4 — Verdict

- **H1 confirmed, refined:** not "hub delivery is lossy" but "YouTube's publisher does not reliably ping the hub for these feeds". Externally corroborated by the same symptom shape in [Google issue 204101548](https://issuetracker.google.com/issues/204101548) (subscribe verifies OK, callback never called).
- **H2 dead** (11.1). **H4 dead** — rows are structurally uniform and the hub confirms every sampled subscription as correctly registered.
- **H3 moot** — throttling on the delivery side cannot explain content the hub never received.
- Nothing on our side is broken, and nothing on our side can fix this. The section 8 design is validated as-is: polling must be the backbone, WebSub is an accelerator that works only when YouTube chooses to ping.

Test 7.3 (delivery ratio after 24h+) remains worth a glance before tuning the poll-floor budget, but it can no longer change the verdict — improvement with subscription age would be unobservable-in-advance and not dependable.

### 11.5 — Reproducing the hub read

Generate diagnostic URLs for the channels of recently recovered uploads:

```bash
php8.4 artisan tinker --execute="App\Models\Video::where('published_at','>','2026-08-23 19:20:00')->with('channel.webSubSubscription')->get()->unique('channel_id')->each(fn(\$v)=>print('https://pubsubhubbub.appspot.com/subscription-details?hub.callback='.urlencode(\$v->channel->webSubSubscription->callbackUrl()).'&hub.topic='.urlencode(\$v->channel->webSubSubscription->topic_url).'&hub.secret='.urlencode(\$v->channel->webSubSubscription->secret).PHP_EOL));"
```

The URLs embed live HMAC secrets: fine to open in a browser (the secret only proves ownership to the hub that already holds it), but do not publish them, and rotate the secret if one leaks somewhere public.
