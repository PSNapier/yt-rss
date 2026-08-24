# YouTube RSS rate limiting — research findings

**Date:** 2026-08-24
**Method:** web research across Google forums, GitHub issues, self-hosted RSS project docs, and community threads
**Status:** no authoritative threshold exists; findings are anecdotal and are labelled as such throughout
**Related:** `reference/WEBSUB_DELIVERY_INVESTIGATION.md` (the poll-floor design this research was gathered to support), `[021]` and `[028]` in the roadmap

---

## 1. Why this was researched

The proposed poll floor (`WEBSUB_DELIVERY_INVESTIGATION.md` section 8.1) sets a fixed hourly polling budget against YouTube's RSS endpoint. Choosing that budget requires knowing where YouTube starts rate limiting a single source IP.

`ROADMAP_DONE.md:386` states the working figure carried into `[021]`:

> naive polling from one server IP draws 429s at ~1,500-2,000 channels

This research set out to corroborate that number. It could not.

---

## 2. Headline findings

1. **No official limit is published.** The RSS endpoint (`https://www.youtube.com/feeds/videos.xml?channel_id=UC…`) is not part of the YouTube Data API. It has no quota documentation, no published rate limit, and no `Retry-After` contract.
2. **No credible empirical threshold was found in any source.** Multiple projects and individuals report being blocked. None reports the request volume that triggered it.
3. **The `~1,500` figure is likely misattributed** (section 4). The only measured number in this space comes from FreeTube and describes a *different endpoint* that explicitly excludes RSS.
4. **The failure mode is not a 429.** Three of four anecdotal cases never saw one. The real shape is an IP-scoped soft block or 403, lasting hours to a day (section 3).
5. **A separate and larger risk exists:** a platform-wide multi-day RSS outage occurred in February 2026 (section 6).

---

## 3. Anecdotal cases

Every case found is volume-unspecified. They are useful for the *shape* of the failure, not its threshold.

| Source | Setup | Outcome |
| --- | --- | --- |
| [Tildes thread](https://tildes.net/~tech/12n6/ideas_how_to_unlock_googles_blocking_of_my_youtube_rss_feeds) | Home RSS reader, YouTube feeds subscribed for years, count and interval never stated | All feeds failed simultaneously. Not a 429 — Google's soft-block interstitial: "your computer or network may be sending automated queries. To protect our users, we can't process your request right now." Feeds worked over VPN, failed on the home connection. Self-resolved in roughly 24 hours when the ISP rotated the IP |
| [RSS-Bridge #2187](https://github.com/RSS-Bridge/rss-bridge/issues/2187) | Public instance `rss.trom.tf` on a datacenter IP | Ran unchanged for months, then all YouTube feeds returned 403. A sibling instance running identical software on a different IP kept working. Same code, different IP, different outcome |
| [Hacker News 39175495](https://news.ycombinator.com/item?id=39175495) | Django-based link archiver polling feeds automatically | Mix of the "automated queries" interstitial and 404s. Google's issue tracker response was that nothing could be done if the "anti abuse mechanism" had triggered. A later comment: "Seems to have been temporary, everything's working again now" |
| [RSSHub FAQ](https://docs.rsshub.app/guide/faqs) and [#2918](https://github.com/DIYgod/RSSHub/issues/2918) | Public RSSHub instances | Documented as a standing condition rather than an incident. YouTube imposes per-IP request quotas; shared and demo instances are described as "unreliable from time to time" |

### 3.1 The pattern that matters

Across all four cases the failure is consistent, and it is **not** what a rate-limited API normally does:

- **No 429, no `Retry-After`, no warning.** The block arrives as a 403 or as an HTML soft-block page served with a 200
- **IP-scoped, not account-scoped.** Changing IP (VPN, ISP rotation, a different instance) restores service immediately
- **All-or-nothing.** Every feed fails at once, not a gradual degradation
- **Temporary.** Hours to about a day
- **Datacenter IPs are treated worse than residential.** Explicit in the RSS-Bridge case and in RSSHub's documentation

Operationally: you do not get throttled, you get disappeared, and you find out by noticing the absence.

---

## 4. The `~1,500` figure and why it probably does not apply

[FreeTube's 429 post](https://blog.freetubeapp.io/experiencing-429-errors-please-read-for-info) is the only source located that contains a measured number:

> "YouTube's limit is roughly ~1500 requests"

The developer could not confirm whether that resets daily or over some other window. **Critically, the same post states that this limit does not apply to RSS:**

> "Calls to RSS are not counted towards your request limit"

That figure describes FreeTube's Innertube/scraping request path. FreeTube's own mitigation for it is to force any profile with **more than 125 subscriptions onto RSS instead** — RSS is their escape hatch *from* the limit, not something subject to it.

The `~1,500-2,000 channels` figure in `ROADMAP_DONE.md:386` is suspiciously close to FreeTube's `~1500`. This is not proof of borrowing, but the provenance of our number is unverified and the nearest matching published number describes a different endpoint under an explicit RSS exemption.

**Treat the `~1,500-2,000` figure as unsourced.** Do not use it to justify a budget without saying so.

---

## 5. Implications for the poll floor

1. **The budget number is a starting value, not a derived one.** The proposed 4,600/day was justified as "5% of the ceiling." That ceiling is unverified, so the safety margin is unknown. The budget is still defensible as a conservative choice, but the reasoning behind it should be stated as caution rather than calculation.

2. **Detection matters more than the budget.** Since the block does not announce itself as a 429, `RssFetcher` should treat all of the following as block signals, not just rate-limit responses:
   - HTTP 403
   - HTTP 429
   - A 200 response whose body is not valid Atom XML (the soft-block interstitial)
   - A sudden transition from a healthy fetch rate to a total failure rate across unrelated channels

   The last one is the most reliable tell, because the block is all-or-nothing. A per-sweep failure ratio crossing some threshold should alert loudly and pause polling rather than continue hammering a blocked IP.

3. **We are on a datacenter IP.** Production runs on a DigitalOcean VPS. That is exactly the profile in the RSS-Bridge case: ran fine for months with no change in behaviour, then blocked. Assume a stricter effective ceiling than a residential connection would see.

4. **Back off rather than retry.** Because blocks are IP-scoped and last hours, retrying through one wastes the budget and may extend the block. Detection should trigger a cooldown.

5. **User-Agent still matters.** `[021]` recorded that non-browser User-Agents are throttled harder (`ROADMAP_DONE.md:403`). `services.websub.user_agent` is already browser-like. Whatever the real ceiling is, it is lower if that config drifts.

---

## 6. Adjacent finding: the February 2026 RSS outage

Unrelated to rate limiting, but a larger threat to a polling-backbone architecture.

A **platform-wide `videos.xml` outage ran 13-17 February 2026**, at minimum. Known-good channels including Google's own and Computerphile returned 404. It was not rate limiting and not channel-specific.

Corroborated independently across:

- [ocaml/ocaml.org #3512](https://github.com/ocaml/ocaml.org/issues/3512) — "404s persistent across the last 5 daily scrape runs (Feb 13-17, 2026)"
- [RSS-Bridge #2113](https://github.com/RSS-Bridge/rss-bridge/issues/2113)
- FreeTube #8443
- [Google AI Developers Forum](https://discuss.ai.google.dev/t/youtube-rss-feed-endpoint-returns-404-errors/113379)
- [n8n community](https://community.n8n.io/t/youtube-rss-feed-endpoint-returns-404-errors/241692)

Intermittent 404/500 reports have continued since. **It is not current:** a forced poll of all 193 production channels on 2026-08-24 returned `fetched: 193, failed: 0`.

The relevance is that the endpoint the poll floor depends on has a demonstrated history of going completely dark for multiple days with no notice and no recourse. This slightly strengthens the case for retaining WebSub as a second, independent ingestion path — it does not touch `videos.xml` — even at its current poor delivery rate.

### 6.1 Item cap

Separately confirmed: the RSS feed returns only the **most recent ~15 videos** per channel. Not a limiting factor at a 200/hour budget, but it becomes one if the budget ever stretches a channel's refresh interval past its upload burst rate.

---

## 7. Scaling beyond one IP, if it ever becomes necessary

Recorded for completeness. None of this is needed at current scale, and section 8 explains why it very likely never will be.

| Approach | Capacity gain | Assessment |
| --- | --- | --- |
| Distributed polling — N servers each polling a disjoint channel slice, results posted back to the central DB | N × per-IP limit | Ordinary horizontal scaling. Spreads legitimate load rather than concealing it. No objection |
| Rotating datacenter proxies | Marginal | Datacenter ranges already score worse than residential. A larger pool of already-suspect addresses is a poor return |
| Rotating residential proxies | Effectively unbounded | This is evasion, not architecture: it defeats an anti-abuse decision deliberately applied to us by making traffic appear to originate from unrelated home users. Also a bad engineering bet — an arms race against Google's anti-abuse team, breaking without warning, and poisoning any future business relationship with YouTube. The residential proxy supply chain is independently ethically compromised. **Not recommended** |

---

## 8. Why the ceiling is probably never reached

Two structural facts make the rate-limit question far less pressing than it appears.

**Polling cost scales with distinct channels, not users.** Channels and videos are shared rows in this schema. Ten thousand users each following two hundred channels is not two million polls — overlap is heavy, and the distinct channel count is a small fraction of the product. The fixed hourly budget absorbs growth by degrading refresh interval, exactly as designed.

**The sanctioned path is cheaper than the roadmap assumes.** `[023]` treats the Data API's 10,000 units/day as scarce and reserves it for channel-ID resolution. But `playlistItems.list` against a channel's uploads playlist costs **1 unit** and returns up to 50 videos — roughly **10,000 channel checks per day within the free quota**, more than the entire proposed RSS budget. Quota increases are also available by application and are routinely granted to legitimate applications.

**Action item:** confirm the `playlistItems.list` unit cost against current Google documentation before planning on it. Inheriting an unverified number is the exact mistake that produced the `~1,500` figure in section 4.

The escalation ladder, in order:

1. RSS poll floor, single IP — ~4,600/day
2. Data API for hot channels, RSS for cold — +10,000/day
3. Data API quota increase request — 100k+/day
4. Distributed polling across owned servers — N × per-IP limit
5. *(Not recommended: residential proxy rotation)*

Legitimate options run out a very long way past current scale. At 193 channels the install sits at roughly 2% of what the free Data API quota alone would cover.

---

## 9. Sources

- [Tildes — Ideas how to unlock Google's blocking of my YouTube RSS feeds](https://tildes.net/~tech/12n6/ideas_how_to_unlock_googles_blocking_of_my_youtube_rss_feeds)
- [FreeTube — Experiencing 429 Errors? Please Read For Info](https://blog.freetubeapp.io/experiencing-429-errors-please-read-for-info)
- [RSS-Bridge #2187 — YouTube failed with error 403 (can our server's IP be blocked?)](https://github.com/RSS-Bridge/rss-bridge/issues/2187)
- [RSS-Bridge #2113 — the videos.xml endpoint occasionally returns 404](https://github.com/RSS-Bridge/rss-bridge/issues/2113)
- [Hacker News 39175495 — Is YouTube starting to protect channel RSS feeds?](https://news.ycombinator.com/item?id=39175495)
- [RSSHub FAQ](https://docs.rsshub.app/guide/faqs) and [RSSHub #2918 — IP block or something else?](https://github.com/DIYgod/RSSHub/issues/2918)
- [ocaml/ocaml.org #3512 — YouTube RSS feeds returning 404 (platform-wide outage)](https://github.com/ocaml/ocaml.org/issues/3512)
- [Google AI Developers Forum — YouTube RSS feed endpoint returns 404 errors](https://discuss.ai.google.dev/t/youtube-rss-feed-endpoint-returns-404-errors/113379)
- [n8n Community — YouTube RSS feed endpoint returns 404 errors](https://community.n8n.io/t/youtube-rss-feed-endpoint-returns-404-errors/241692)
- [Invidious — All the YouTube error messages explained](https://docs.invidious.io/youtube-errors-explained/)
- [FreshRSS #8928 — Rate Limiting Built In](https://github.com/FreshRSS/FreshRSS/issues/8928)
