# Polling loss investigation — handoff

**Date:** 2026-08-25
**Environment:** production (Laravel Forge, VPS `elk-moon`, site dir `/home/forge/peristalsis.on-forge.com/current`, public host `https://peristalsis.tv`); local control readings from the Herd dev box
**Status:** root cause of the post-[029] loss proven to the transport layer. Fixes designed, not built.
**Related roadmap items:** `[032]` (this diagnosis), `[029]` (the polling backbone under investigation), `[028]` (the WebSub diagnosis that preceded it, archived in `ROADMAP_DONE.md`)
**Prior reading:** `reference/WEBSUB_DELIVERY_INVESTIGATION.md`, `reference/YOUTUBE_RSS_RATE_LIMITS.md`

---

## 1. The symptom

`[029]` deployed on 2026-08-24 and made a 30-minute `channels:poll` sweep the sole automatic ingestion path. A few new videos arrived. By the next morning uploads were visibly missing from the feed, with nothing broken in the UI and no error surfaced anywhere.

Separately, the user has suspected a longer-standing loss since `[021]`: opening the WebSub push path in August surfaced a backlog of videos that had been absent from the database while the pre-push ingestion path reported healthy. A backlog appearing the moment a *new* path opens is the signature of a silent drop, not a fetch failure, so the two symptoms were investigated as potentially different causes.

---

## 2. Candidates entering the investigation

| | Candidate | New in [029]? |
| --- | --- | --- |
| A | Block cooldown latched and parked ingestion for 6 hours at a time | yes |
| B | `blockSignal` false-positives on every response (manual `Accept-Encoding` leaves the body gzipped, `looksLikeAtom` fails) | yes |
| C | Conditional-GET validators wedged, every channel answering 304 forever | yes |
| D | Silent Shorts deletion in `RssFetcher::ingest` destroying ordinary uploads | no, pre-existing |
| E | 2.0s connect / 3.0s total timeouts, tuned for an Inertia request, not a sweep | no, but only load-bearing since [029] |

---

## 3. Production readings, 2026-08-25 (~13:00-19:00 UTC)

`php8.4 artisan poll:health`:

```
Cooldown: ACTIVE until 2026-08-25T19:00:29+00:00 (178 of 193 requests failed (0 of them block signals)).
Staleness: 193 channels, max 1464 min, median 1463 min, never fetched 0.
Last 24h: 3 sweeps, 579 polls, 560 fetch failures, 0 block signals, 0 cap hits, 3 cooldowns.
All rss_url values are canonical.
```

`select started_at, channels_polled, fetched, not_modified, failed, blocked, cooldown_triggered from poll_sweeps order by started_at desc limit 48;`

```
2026-08-25 13:00:04 polled=193 fetched=15 notmod=0 failed=178 blocked=0 cooldown=1
2026-08-25 06:30:03 polled=193 fetched=3  notmod=0 failed=190 blocked=0 cooldown=1
2026-08-25 00:00:13 polled=193 fetched=1  notmod=0 failed=192 blocked=0 cooldown=1
```

Three sweeps in 24 hours where the schedule asks for 48. Every sweep trips the cooldown, so 45 of 48 sweeps never ran.

### 3.1 — Verdicts from that data

| Candidate | Reading that decides it | Verdict |
| --- | --- | --- |
| B | `blocked = 0` on every sweep. A gzip-mangled body would classify as `non_atom_200` and put `blocked` at or near `channels_polled` | **eliminated** |
| C | `not_modified = 0` on every sweep. No 304 has ever been served, so no validator is wedged | **eliminated** |
| A | 3 cooldowns across 3 sweeps, all triggered on `(failed + blocked) / attempted` | **real, but downstream of E** |
| E | 560 of 579 polls failed with 0 block signals | **root cause of the post-[029] loss** |

The failures are not 403, not 429, not a non-Atom 200, and not 304. `RssFetcher::fetchForChannels` logs them at `app/Services/RssFetcher.php:104-111` with `'status' => 'no_response'`, which is reached only when no `Response` object came back at all: a connect timeout, a read timeout, or a transport-level error against the hardcoded 2.0s / 3.0s defaults in the constructor at `app/Services/RssFetcher.php:18-23` (`connectTimeoutSeconds: 2.0`, `timeoutSeconds: 3.0`).

---

## 4. Two structural findings the readings force

**A is not an independent cause, it is an amplifier.** `PollChannelsCommand::shouldCooldown` counts `failed + blocked` against the ratio, so ordinary transport failures trip a mechanism built for IP blocks. `[029]` recorded cooling down on non-block failures as "the safe direction". In practice it converts a partial fetch problem into near-total ingestion loss: 3 sweeps a day instead of 48, and the surviving sweeps recover only what fits in the 15-entry RSS window. The cooldown must distinguish block signals from ordinary failures.

**`no_response` is unreadable.** The log line carries only the literal string `no_response`. It does not carry the exception class or its message, so DNS failure, TLS handshake failure, connect timeout, and read timeout are indistinguishable after the fact. That gap is why the investigation needed bespoke tooling rather than `laravel.log`, and it is the first thing a fix should close.

---

## 5. Local control readings, 2026-08-25 16:03 UTC

Run from the Herd dev box (`php 8.4.7`, `curl 8.12.1`, `OpenSSL/3.0.16`), same poll headers, same code paths, 63 channels. Command: `poll:diagnose`, the single-fetch and pool timing sections.

Single fetches, tight (2.0/3.0) against generous (10/30):

```
UChk6TQce1EJMn6_liKdHDog tight    status=200 bytes=25756 ms=1877 atom=yes
UChk6TQce1EJMn6_liKdHDog generous status=200 bytes=25756 ms=549  atom=yes
UCaTznQhurW5AaiYPbhEA-KA tight    status=200 bytes=22095 ms=497  atom=yes
UCbixkBITOOa2XNviJLxMh2w tight    status=200 bytes=17498 ms=461  atom=yes
UCMFSTAyvpl_yBXmq6JPfhBQ tight    status=200 bytes=27632 ms=500  atom=yes
UCJ6KZTTnkE-s2XFJJmoTAkw tight    status=200 bytes=27986 ms=504  atom=yes
```

Pool of 20, the real sweep shape:

```
pool tight    total_ms=862   20 x http_200
pool generous total_ms=716   20 x http_200
```

Reading: from a residential IP, YouTube answers the same requests in ~500ms, and 20 concurrent requests complete in under a second, well inside the 2.0/3.0 budget. The `atom=yes` column also independently re-kills Candidate B: with `Accept-Encoding: gzip, deflate` set by hand, the body still arrives decoded and `<feed` matches. Whatever fails on production is a property of production egress, not of the endpoint, the headers, or the timeout values in the abstract.

---

## 6. Candidate D — Shorts deletion

The rule under suspicion: `RssFetcher::ingest` (`app/Services/RssFetcher.php:275-280`) hard-deletes the `videos` row and every `user_video_states` row for any entry whose Atom `alternate` href is under `/shorts/`. No log line, no counter. The concern was that YouTube canonicalises ordinary uploads under `/shorts/` liberally, making this a silent destroyer of real videos and of the user's watched state.

### 6.1 — Do the RSS href and YouTube's own classification agree?

Ten `/shorts/`-href entries sampled and checked against the watch page (`poll:diagnose --shorts-probe`), reading the canonical link, `approxDurationMs`, and the player dimensions:

```
video         canonical seconds  ratio
PlyfRGYpR0c   shorts    43       portrait 1078x1920
ngJC4gkfZP8   shorts    38       portrait 1078x1920
YKZwgRO7Dug   shorts    57       portrait 1080x1920
J1wJ-8mU7a0   shorts    60       portrait 1080x1920
uOPIrBcneNg   shorts    61       portrait 1080x1920
McbCLuWpGWk   shorts    60       square   2160x2160
VFVq7f5Y4FQ   shorts    23       portrait 360x640
ecCZvWPvEAk   shorts    56       portrait 1080x1920
jqitY22c3Ds   shorts    59       portrait 2160x3840
0B7o8yRYcAo   shorts    14       portrait 1080x1920
```

Ten of ten agree with the RSS href, all are 61 seconds or under, and nine of ten are portrait. No false positive in the sample. Titles that read like ordinary uploads (`Does Star Trek's USS Enterprise Design Make Sense?`) are genuinely 43-second vertical clips.

### 6.2 — Is anything already stored about to be deleted?

`poll:diagnose`'s diff section scans every channel's live RSS and reports entries whose href is under `/shorts/` **and** which currently exist in `videos` — that is, rows the next successful poll will destroy:

```
shorts-href entries in live RSS: 236
of those, currently stored in videos (deleted on next successful poll): 0
```

Zero churn. Nothing stored has since flipped to a `/shorts/` href.

### 6.3 — Verdict

**Candidate D is eliminated as a cause of the observed loss**, on this evidence: the classification is accurate in every sample, and no stored row is queued for deletion. The design objection stands and is recorded below as a hardening item rather than a bug: the rule deletes rather than flags, it takes `user_video_states` with it, and it leaves no trace, so if it ever *does* misfire nothing in the system will say so. YouTube's 3-minute Shorts ceiling also means a 2:50 vertical upload is deleted today with no way for the user to see it happened.

---

## 7. The pre-existing loss, observed at the [021] deploy

Recorded separately from the post-[029] regression, because the mechanisms are different.

The `[028]` handoff already contains the explanation and it does not require a new one. Before `[021]`, ingestion ran inside the request cycle: `RssFetcher` was called from the feed controller as an Inertia deferred prop, with the same 2.0s / 3.0s budget, and a failed fetch degraded silently into "no new videos on this page load". There was no sweep, no `poll_sweeps` table, no failure counter, and no staleness report. Any channel whose fetch timed out was simply not refreshed, and the 15-entry RSS window meant a channel that stayed unrefreshed across enough uploads lost the overflow permanently.

That is the same defect as Candidate E, one layer earlier: **the timeouts were always too tight, and until `[029]` nothing counted the failures.** Opening the push path in `[021]` produced a backlog because push was the first ingestion route that did not go through that budget. So the pre-existing loss and the post-[029] loss share a root cause; `[029]` did not introduce it, it made it measurable and then amplified it through the cooldown.

The Shorts rule is a second, independent contributor to the pre-existing gap and is eliminated as such in section 6.

---

## 8. Reproduction commands

All read-only. `poll:diagnose` is the investigation's tooling, shipped as an artisan command (`app/Console/Commands/DiagnosePollingCommand.php`) rather than a one-off script, because this gap recurs: `poll:health` answers whether polling is healthy, and `poll:diagnose` answers why it is not.

**The full reading**, which is what produced sections 3 through 6:

```bash
cd /home/forge/peristalsis.on-forge.com/current
php8.4 artisan poll:health
php8.4 artisan poll:diagnose --shorts-probe=10 2>&1 | tee /tmp/poll-diagnose.out
```

Its five sections, and what each decides:

| Section | Reading | Decides |
| --- | --- | --- |
| Sweep history | last N `poll_sweeps` rows | A against B against C, by sweep shape |
| Single-fetch timing | tight (2.0/3.0) against generous (10/30), **exception class and message captured** | E, and the transport failure `no_response` hides |
| Pool timing | the same both ways across a concurrent batch | E under the shape a sweep actually uses |
| Live RSS against `videos` | every channel diffed, missing entries named with publish time and href | ground truth, plus stored rows a `/shorts/` href will delete next poll |
| Deletion tells | orphaned `user_video_states`, newest video, 24h intake | D, and any deletion path other than `ingest` |

**Narrower runs**, when the full sweep is too slow or too noisy:

```bash
php8.4 artisan poll:diagnose --skip-diff              # transport readings only, seconds not minutes
php8.4 artisan poll:diagnose --limit=5 --list=200     # five channels, every missing entry printed
php8.4 artisan poll:diagnose --shorts-probe=20        # widen the Candidate D sample
```

**Cooldown log lines**, which `poll:diagnose` does not read:

```bash
cd /home/forge/peristalsis.on-forge.com/current; /bin/grep 'Polling cooldown started' storage/logs/laravel.log | tail -20
```

---

## 9. Evidence gotchas

The `[028]` gotchas still apply and are not repeated here. Three more, specific to this investigation:

- **`no_response` in `laravel.log` is not diagnosable.** It is written for every `Response`-less outcome and carries no exception class or message. Do not try to distinguish DNS, TLS, connect timeout, and read timeout from the log; you cannot.
- **A local reading is a control, not evidence about production.** Tight timeouts pass comfortably from a residential IP. The failure is in production egress, so every timing conclusion must be re-measured on the box.
- **`php artisan tinker <file>` drops into an interactive shell after including the file**, which is useless over a non-interactive ssh. This is why the tooling is an artisan command rather than a script piped at tinker.

---

## 10. Follow-up items

| Item | What it fixes | Evidence |
| --- | --- | --- |
| Sweep-appropriate timeouts, made configurable | Candidate E, the root cause of both the post-[029] and the pre-[021] loss | section 3.1, section 5 |
| Cooldown only on block signals | Candidate A, the amplifier turning a partial failure into a 6-hour blackout | section 4 |
| Record the transport failure on a failed poll | the `no_response` blind spot | section 4 |
| Flag Shorts rather than deleting them | the hardening D leaves behind | section 6.3 |

---

## 11. Files worth reading

| Path | Why |
| --- | --- |
| `app/Services/RssFetcher.php` | timeouts (constructor), `blockSignal`, `looksLikeAtom`, the `no_response` log, the Shorts deletion in `ingest` |
| `app/Console/Commands/PollChannelsCommand.php` | `shouldCooldown`, the sweep, `poll_sweeps` writes |
| `app/Services/PollCooldown.php` | the 6-hour park and its single `Log::error` |
| `app/Console/Commands/PollHealthCommand.php` | what the health report does and does not surface |
| `app/Providers/AppServiceProvider.php` | the `RssFetcher` singleton, which passes no timeout arguments |
| `config/services.php` | `polling.*` knobs; note that no timeout knob exists |
| `routes/console.php` | the 30-minute sweep and its `withoutOverlapping(29)` |
| `reference/WEBSUB_DELIVERY_INVESTIGATION.md` | the `[028]` handoff this continues |
