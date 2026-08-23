# Roadmap Done

## [027] Subscribe channels missing a WebSub subscription (`websub:subscribe-missing`)

**Status:** `done`
**Mode:** `Auto`
**Depends On:** [021], [022]

### Goal

Close the gap where a channel exists in the database but has no `channel_subscriptions` row at all, so push never reaches it. A channel only ever gets subscribed at the moment it is added, which leaves any channel predating WebSub — or every channel in a database that was never the one the hub was pointed at — permanently push-less. Provide a manually run artisan sweep that subscribes and backfills those channels.

### Scope

- A new `websub:subscribe-missing` artisan command, run on demand, not scheduled
- `--dry-run` to list candidates without touching RSS or the hub, and an optional `--limit` to pace a large first run
- Aggregated alerting on subscribe failure, mirroring the existing renewal alert
- Explicitly NOT in scope: rows that exist but are unhealthy (`failed`, or never verified). `websub:renew` already retries those, and a second command POSTing the same subscribe would double up on the hub
- Explicitly NOT in scope: scheduling. This is a deploy-time and recovery tool, not ongoing bookkeeping

### Technical Notes

- The gap exists because `WebSubSubscriber::ensureSubscribed()` has exactly one caller, `SubscriptionController::store` (`SubscriptionController.php:101`). Nothing reaches a channel that is already in the table
- `WebSubBackstopCommand::reasonToRepoll()` already returns `'no websub subscription'` for these channels, so their video data does stay fresh over RSS. What was missing was only the subscription itself, which is why the hole is invisible in the feed
- The command reuses `ensureSubscribed()` rather than reimplementing subscribe logic, so it inherits the same backfill-then-subscribe ordering and idempotence. Re-running after a partial or interrupted run only picks up what is still missing
- Each subscribe also backfills that channel over RSS, so a first run against a large database is slow. `--limit` exists to pace it; the default is no limit
- `WebSubAlerter::subscribesFailed()` was added alongside the existing `renewalsFailed()`, same shape: one `Log::error` and one webhook POST per run, capped channel-id sample, ids sanitized before leaving the app
- Primary use is a deploy to an environment whose database has no `callback_token` rows the hub knows about. Hub subscriptions are keyed on `(topic, callback URL)`, so a new environment on its own hostname starts with zero live subscriptions regardless of what the previous one had

### Tests

- [x] `tests/Feature/WebSubSubscribeMissingTest.php` — a channel with no subscription row is subscribed at the hub and backfilled, with topic, callback token, and secret populated
- [x] `tests/Feature/WebSubSubscribeMissingTest.php` — a channel with an active subscription is left untouched and its `callback_token` is not rotated
- [x] `tests/Feature/WebSubSubscribeMissingTest.php` — a `failed` subscription is also left alone, since `websub:renew` owns that retry
- [x] `tests/Feature/WebSubSubscribeMissingTest.php` — `--limit` caps the run and warns that channels may remain; without it the run is unbounded
- [x] `tests/Feature/WebSubSubscribeMissingTest.php` — `--dry-run` reports candidates and sends no HTTP requests at all
- [x] `tests/Feature/WebSubSubscribeMissingTest.php` — a hub rejection marks the subscription `failed` and raises the aggregated alert

### Acceptance Criteria

- [x] Running the command subscribes and backfills every channel that has no subscription row
- [x] Re-running it is a no-op and reports that every channel is already subscribed
- [x] Existing subscriptions, healthy or failed, are never modified by the command
- [x] `--dry-run` previews the candidate list without contacting the hub or YouTube
- [x] The command is not registered on the scheduler; `schedule:list` shows only `websub:renew` and `websub:backstop`

---

## [026] Stop the whole grid flashing when a video's state changes

**Status:** `done`
**Mode:** `auto`
**Depends On:** none

### Goal

Marking a video watched or unwatched updates that one card. It no longer blanks every card on the page to the loading skeleton for a frame, and no longer discards infinite-scroll progress.

### Scope

- Write video state over plain XHR instead of an Inertia visit
- Return `204` from the state endpoint for XHR callers, keeping the redirect for normal form posts
- Keep the cap-on refetch, but as a partial reload that leaves the current cards on screen

### Technical Notes

- Root cause: `VideoStateController::store` returned `back()`, so `router.post` performed a full Inertia visit. `videos` is an `Inertia::defer` prop, so the visit reset it to `undefined`, `<Deferred data="videos">` rendered its `FeedGridSkeleton` fallback for a frame, and the arriving payload then ran `applyItems`, replacing the array and dropping loaded pages. The flash was the deferred fallback, not a CSS transition
- Fix: `resources/js/composables/useVideoState.ts` exposes `postVideoState(url, state)` — a `fetch` with `credentials: 'same-origin'` and an `X-XSRF-TOKEN` header read from the `XSRF-TOKEN` cookie. No Inertia visit, so no deferred prop reset. The UI already updated optimistically, so nothing needs to come back
- `VideoStateController::store` now returns `response()->noContent()` when `$request->expectsJson()`, and still `back()` otherwise, so existing tests asserting a redirect stay valid
- The cap-on branch still calls `router.reload({ only: ['videos'] })`. A partial reload keeps the previous prop value on screen until the response lands, so it does not re-trigger the fallback
- Distinct from `[006]`, which covers the skeleton flash on navigation and browser-tab return. This item only covers the state-write trigger; `[006]` stays open

### Acceptance Criteria

- [x] Toggling watched/unwatched updates only that card, with no skeleton flash across the grid
- [x] Infinite-scroll progress survives a state toggle
- [x] The cap-on refetch still happens, without flashing the grid
- [x] Non-XHR posts to the state route still redirect back

### Tests

- [x] `tests/Feature/VideoStateTest.php::an XHR state write returns 204 so the feed never re-renders`
- [x] `tests/Feature/VideoStateTest.php::a non-XHR state write still redirects back`

---

## [025] Eye toggle on video cards for watched/unwatched

**Status:** `done`
**Mode:** `auto`
**Depends On:** [024]

### Goal

Each video card carries an eye icon button in the top-left of the thumbnail that flips the video between watched and unwatched in place, without opening the video.

### Scope

- Add an icon button to the `VideoCard` thumbnail, mirroring the existing favorite star in the top-right
- Emit a `toggle-watched` event and wire it to the existing `setState` in both feed pages
- NOT in scope: the `hidden` state, which keeps its context-menu path

### Technical Notes

- Button lives in the thumbnail block in `resources/js/components/VideoCard.vue`, `absolute top-2 left-2 z-10`, opposite the `channel_is_favorite` star
- `@click.prevent.stop` is required: the card root is now an anchor (`[024]`), so without both the toggle would also navigate to YouTube
- Icon swaps on state: `EyeSlashIcon` when watched (click to unwatch), `EyeIcon` when unwatched. `aria-pressed`, `aria-label`, and `title` all reflect the current state
- Visibility: hidden until card hover (`opacity-0 group-hover:opacity-100`), pinned visible when the video is watched so watched cards always expose the way back
- New emit `toggle-watched`; `onToggleWatched` in `Feed.vue` / `Groups/Show.vue` calls the existing `setState(id, watched ? null : 'watched')`, so the null branch reuses the delete path already covered by `VideoStateTest`

### Acceptance Criteria

- [x] Every card shows an eye button in the thumbnail's top-left
- [x] Clicking it toggles watched/unwatched without opening the video
- [x] The button reflects state (eye vs eye-slash) and exposes an accessible label
- [x] Watched cards keep the button visible; unwatched cards reveal it on hover
- [x] Works on both the all-videos feed and group feeds

### Tests

- [x] `tests/Feature/VideoStateTest.php::user can mark a video watched`
- [x] `tests/Feature/VideoStateTest.php::user can unmark watched (delete state) by sending null`

---

## [024] Whole video card is one link (native link context menu)

**Status:** `done`
**Mode:** `auto`
**Depends On:** none

### Goal

Right-clicking anywhere on a video card offers link actions for the video ("Open link in new tab", "Copy link address") instead of image actions on the thumbnail, because the entire card is a real anchor to the YouTube URL rather than a `div` with a click handler.

### Scope

- Convert the `VideoCard` root element from `<div>` to `<a href>` targeting the YouTube watch URL
- Make the thumbnail image ignore pointer events so the right-click target is the anchor, not the `<img>`
- Remove the now-redundant `window.open` from both feed pages' `onCardClick`
- Temporarily disable the custom right-click context menu so the native browser menu shows
- NOT in scope: deleting the custom context menu or its markup — it stays behind a flag

### Technical Notes

- Root element is now `<a :href="videoUrl" target="_blank" rel="noopener">` in `resources/js/components/VideoCard.vue`; `videoUrl` is a computed `https://www.youtube.com/watch?v=${video.youtube_video_id}`
- The thumbnail `<img>` carries `pointer-events-none`, so a right-click over the thumbnail resolves to the anchor and Chromium shows link actions rather than image actions
- `onCardClick` in `resources/js/pages/Videos/Feed.vue` and `resources/js/pages/Groups/Show.vue` no longer calls `window.open` — the anchor navigates. It still marks the video watched
- The custom menu is gated by a `CONTEXT_MENU_ENABLED = false` const in `VideoCard.vue`; the `@contextmenu` emit is guarded by it, so `openCtx` (which calls `preventDefault`) never fires and the native menu appears. Flip the const to `true` to restore the custom menu
- Related: `[007]` (background tab on click) is unaffected and still open — `target="_blank"` opens a foreground tab, and no web API can request a background one

### Acceptance Criteria

- [x] Right-clicking a card's thumbnail shows link actions for the video, not image actions
- [x] Left-clicking anywhere on the card opens the video in a new tab exactly once (no double-open)
- [x] The card still marks itself watched on click
- [x] The custom context menu is disabled by a single flag that can be flipped back on

### Tests

Verified by hand in the browser — this is DOM/right-click behaviour with no automated browser suite in the project (`tests/` has Feature and Unit only). The click path's server side is covered by the existing `tests/Feature/VideoStateTest.php`.

---

## [022] WebSub hardening: renewal, backstop, retire sync fetch

**Status:** `done`
**Mode:** `Manual`
**Depends On:** [021]

### Goal

Make push durable and turn the feed controllers into pure DB reads. Renew leases before expiry, close silent-failure holes with a cheap failure-driven backstop, and retire the synchronous in-render RSS fetch (Finding F1).

### Scope

- **Renewal job** (scheduled): re-subscribe channels before lease expiry (~5-10 days). This is the main ongoing bookkeeping
- **Reconciliation backstop** (scheduled but **failure/anomaly-driven**, not freshness-driven): re-poll only when a renewal is detected failed, or when a channel is anomalously silent versus its own posting cadence. Covers dropped pushes (callback down during delivery) and lapsed leases. Do **not** reduce this to "poll only on add" — without the backstop, a dropped push or lapsed lease is invisible, permanent data loss
- **Retire the synchronous in-controller fetch** (Finding F1): controllers become pure DB reads; ingestion happens only via push plus the two poll paths
- **Polling politeness** on the polls that remain: browser-like User-Agent, gzip, and conditional GET (If-None-Match / If-Modified-Since -> 304)
- **Monitoring**: alert on failed renewals and callback downtime

### Technical Notes

- **Prerequisite (now real):** the scheduler must run in production (`php artisan schedule:work`, or the standard `* * * * * php artisan schedule:run` cron). `routes/console.php` schedules `websub:renew` and `websub:backstop` hourly with `withoutOverlapping(50)`
- Renewal (`websub:renew`) re-subscribes anything expiring inside `WEBSUB_RENEW_WITHIN_HOURS` (48h), plus `failed` rows and rows the hub never verified (`expires_at` null). A re-POST is throttled by `WEBSUB_RENEW_RETRY_HOURS` (6h) because the lease only moves when the hub re-verifies, so an unthrottled sweep would re-POST the same subscription every hour
- The hub verification callback resets `renewal_failures` to 0; `WebSubController::verify` now requires an explicit `hub.mode` and clamps an accepted lease to 10 days
- Alerts are aggregated: one log line and one webhook POST per sweep, not per failure, so a hub outage cannot fan out into hundreds of pages. **With `WEBSUB_ALERT_WEBHOOK` unset the alert is a `Log::error` only** — wire log-based alerting or set the webhook in production
- Backstop (`websub:backstop`) reasons, in order: queued callback-downtime recovery, a push that failed to ingest, `failed` subscription, failing renewals, lapsed lease, then anomalous silence. The first two mean an upload is already known missing, so they skip the `WEBSUB_BACKSTOP_MIN_REPOLL_HOURS` pacing gate
- **Dropped-push recovery** works two ways: `WebSubController::receive` stamps `delivery_failed_at` when a push cannot be ingested, and the backstop treats a gap larger than `WEBSUB_BACKSTOP_DOWNTIME_GAP_HOURS` (3h) between its own runs as callback downtime, queueing every channel for one recovery poll (`recovery_due_at`, paced by `WEBSUB_BACKSTOP_LIMIT`). Silence-only detection cannot recover a drop followed by a successful push, which is why these two signals exist
- Cadence for the silence check is the mean gap from one grouped aggregate per chunk (`max`/`min`/`count` of `published_at`), never a per-channel query; a `(channel_id, published_at)` index on `videos` backs it
- Conditional GET: `channels.rss_etag` / `channels.rss_last_modified` are stored and replayed as `If-None-Match` / `If-Modified-Since`; a 304 counts as `not_modified`, not a failure. Origin-supplied validators are stripped of non-printable bytes and dropped over 200 chars, so a hostile header cannot fail the write that also carries `last_fetched_at`
- `GroupFeedController::refresh` (POST, no current frontend caller) is kept as an explicit user-triggered poll: it is not first paint, and it goes through the same polite fetch path. The add-time name lookup in `ChannelResolver` now goes through `Http` with the browser UA and gzip too
- **Accepted, not fixed:** hub POSTs during renewal are serial (10s timeout each), so a very large sweep is slow; at 100k channels a synchronized lease-expiry burst drains over several hours, bounded by `WEBSUB_RENEW_LIMIT`. The command warns when a sweep hits its limit. Pooling those POSTs is the fix if that backlog ever appears
- **Accepted, not fixed:** the callback token is the only authorization on the verification GET (that is the WebSub handshake). A leaked token lets someone mark a subscription active and reset its failure counter; the lease clamp and required `hub.mode` limit the blast radius
- **Live verification still outstanding:** a real dropped push over the tunnel has not been observed. The recovery paths are proven by simulation in `tests/Feature/WebSubBackstopTest.php`, not yet against Google's hub. Redelivery behavior on a callback 5xx also remains unobserved (carried over from `[021]`)
- Retiring F1 removes the in-render fetch that `[006]` and `[010]` reason about: both can now assume first paint is a pure DB read

### Tests

- [x] `tests/Feature/WebSubRenewalTest.php` — a subscription expiring inside the renewal window is re-subscribed at the hub (existing `callback_token`/`secret` reused, status stays usable); one outside the window is left alone
- [x] `tests/Feature/WebSubRenewalTest.php` — a hub rejection increments `renewal_failures` and raises an alert (error log + configured alert webhook)
- [x] `tests/Feature/WebSubBackstopTest.php` — a lapsed lease (expired `expires_at`) and a `failed` subscription are re-polled; a healthy active subscription is not
- [x] `tests/Feature/WebSubBackstopTest.php` — a channel silent far beyond its own posting cadence is re-polled (recovers a dropped push); a channel silent within cadence is not
- [x] `tests/Feature/RssPolitenessTest.php` — polls send the browser User-Agent, `Accept-Encoding: gzip`, and conditional `If-None-Match` / `If-Modified-Since` from stored validators
- [x] `tests/Feature/RssPolitenessTest.php` — the add-time channel-name RSS lookup sends the same browser User-Agent and gzip
- [x] `tests/Feature/RssPolitenessTest.php` — a `304 Not Modified` writes no videos, is not counted as a failure, and refreshes `last_fetched_at`
- [x] `tests/Feature/WebSubRenewalTest.php` — a `failed` subscription is retried; one re-subscribed within the retry window is not re-POSTed; a hub outage raises one aggregated alert, not one per subscription
- [x] `tests/Feature/WebSubRenewalTest.php` — a successful hub verification clears `renewal_failures`
- [x] `tests/Feature/WebSubBackstopTest.php` — a push that fails to ingest flags the channel and is re-polled immediately, then the flag clears
- [x] `tests/Feature/WebSubBackstopTest.php` — a gap between sweeps (callback downtime) queues every channel for one recovery poll; an uninterrupted cadence queues none
- [x] `tests/Feature/WebSubBackstopTest.php` — the cadence check runs one grouped query per chunk, not one per channel
- [x] `tests/Feature/GroupFeedTest.php` / `tests/Feature/AllVideosFeedTest.php` — rendering either feed (shell and deferred partial) sends zero HTTP requests

### Acceptance Criteria

- [x] Leases are renewed before expiry without manual intervention; a failed renewal raises an alert
- [x] A deliberately dropped push (callback offline during delivery) is recovered by the backstop
- [x] Feed controllers perform no network I/O — first paint is a pure DB read
- [x] Remaining polls send a browser UA, gzip, and conditional GET, and honor 304s

---

## [001] Fix video card title truncation / ellipsis overflow

**Status:** `done`
**Mode:** `auto`
**Depends On:** none

### Goal

Video card titles no longer get cut off awkwardly. The line clamp and reserved height behave consistently across all cards.

### Scope

- Reconcile the title clamp with its reserved min-height so titles end cleanly with an ellipsis
- Apply the fix to both feed pages, ideally by extracting the duplicated card markup into a single shared component

### Technical Notes

- Title markup is inline (no shared component) and duplicated in `resources/js/pages/Groups/Show.vue:354` and `resources/js/pages/Videos/Feed.vue:277`
- Current classes: `line-clamp-2 min-h-9 text-[13px] leading-[1.35]`. Note the title clamps to **two** lines, not three; the awkward cut comes from `line-clamp-2` fighting the `min-h-9` reserved height and the `leading-[1.35]` line height
- Extracting the card into one component would let the fix live in a single place

### Acceptance Criteria

- [x] A title exceeding 2 lines renders exactly 2 lines terminated by an ellipsis, with no third-line text or descender clipping visible
- [x] Cards in the same row have identical rendered height (±0px) whether their title is 1 or 2 lines
- [x] The title clamp value and reserved min-height are defined in exactly one place (shared component), and both `Groups/Show.vue` and `Videos/Feed.vue` render titles through it

---

## [002] Persist loaded videos across group/tab switches

**Status:** `done`
**Mode:** `auto`
**Depends On:** none

### Goal

After clicking "Load more", switching to another group and returning keeps the extra loaded videos and expand state instead of resetting to page one.

### Scope

- Cache loaded videos + pagination cursor + older-expanded state per group
- Rehydrate that state on return instead of blindly replacing it
- Apply the same fix to the infinite-scroll feed variant

### Technical Notes

- Root cause: loaded videos live only in component-local `reactive` state (`items`, `nextUrl`, `olderExpanded` in `resources/js/pages/Groups/Show.vue:43-47`), and `videos` is an `Inertia::defer` prop (`app/Http/Controllers/GroupFeedController.php:23`, `app/Http/Controllers/AllVideosFeedController.php:25`)
- Sidebar "tabs" are full Inertia `<Link>` navigations (`resources/js/components/NavGroupFeeds.vue`), so returning re-instantiates the page; the `watch(() => props.videos, ...)` at `Show.vue:49-62` splices `items` back to page one
- `loadMore` (`Show.vue:99-121`) appends via a manual partial fetch that is never stored in props or a store, so it is lost on navigation
- Recommended direction: cache `items` + `nextUrl` + `olderExpanded` per group id (Pinia store or `sessionStorage`) and rehydrate in the watcher; apply the same to `Feed.vue`'s infinite-scroll variant

### Acceptance Criteria

- [x] Loading more to N videos (N > page size), switching to another group, and returning shows the same N videos, in the same order, without a re-fetch to page one
- [x] On return, the `nextUrl` cursor resumes from where it left off (the next "Load more" fetches page N+1, not page 2) and `olderExpanded` matches its pre-navigation value
- [x] Both `Groups/Show.vue` and `Videos/Feed.vue` pass this same round-trip check

---

## [003] Audit available RSS feed fields

**Status:** `done`
**Mode:** `auto`
**Depends On:** none

### Goal

Document every field YouTube's channel RSS feed exposes and which the app currently uses, to inform the video-cap and popularity work.

### Scope

- Inventory all feed-level and per-entry fields
- Record which fields are ingested today vs available but unused
- Note whether a usable popularity signal exists

### Technical Notes

- Parsing happens in `app/Services/RssFetcher.php::ingest()` (`simplexml_load_string`, `yt` and `media` namespaces)
- Findings written to `reference/RSS_FEED_AUDIT.md`
- Key result: the feed provides `media:community/media:statistics @views` and `media:starRating` (average, count) plus `media:description`, `updated`, and author info. None of these are stored today; only `yt:videoId`, `title`, `published`, and one `media:thumbnail` URL are ingested
- There is a usable popularity signal (views + star rating), but only as a point-in-time snapshot at fetch, not a historical trend

### Acceptance Criteria

- [x] `reference/RSS_FEED_AUDIT.md` exists listing all feed fields
- [x] Current usage vs unused fields documented
- [x] Popularity availability noted

---

## [004] Video count cap MVP — latest unwatched only

**Status:** `done`
**Mode:** `auto`
**Depends On:** `[003]`

### Goal

A toggleable mode that shows only the latest unwatched video per channel. Marking it watched does not hide it, but reveals the next available unwatched video from that channel.

### Scope

- Per-user toggle for the cap mode
- Per-channel logic that surfaces the newest not-watched video
- Marking watched reveals that channel's next unwatched video
- Already-watched videos still render per existing behavior

### Technical Notes

- Effectively a per-channel cap of 1 on *unwatched* videos
- Watched state lives in `user_video_states` (`state` enum watched/hidden, keyed by `youtube_video_id`; written by `app/Http/Controllers/VideoStateController.php`)
- Feed queries in `GroupFeedController` / `AllVideosFeedController` order by `published_at DESC, id DESC` and join `user_video_states`
- Decide whether the cap is enforced server-side in the paginated query (a per-channel window, e.g. `ROW_NUMBER`) or client-side over loaded `items`. Server-side is cleaner with cursor pagination

### Acceptance Criteria

- [x] The toggle persists per user and its state survives a page reload
- [x] With the cap on, the feed contains at most one unwatched video per channel (the newest by `published_at`); a channel with zero unwatched videos contributes none
- [x] Marking that video watched removes it and surfaces exactly that channel's next-newest unwatched video (or none if the channel has no more), leaving other channels unchanged
- [x] Toggling off returns the feed to the full unfiltered list (same count and order as with the cap disabled)

---

## [005] Editable per-subscription unwatched video cap

**Status:** `done`
**Mode:** `auto`
**Depends On:** `[004]`

### Goal

Let the user set the max number of unwatched videos shown per subscription (e.g. 2, 5, unlimited), editable per channel, generalizing the MVP's cap-of-1.

### Scope

- Configurable integer cap per subscription with a sensible default and an unlimited option
- UI to edit the value per channel
- Feed respects each channel's cap

### Technical Notes

- Extends `[004]` from a fixed cap of 1 to a configurable integer
- Storage: a nullable `unwatched_cap` per subscription, likely on `user_channel_favorites` or a new per-user-per-channel settings row. Note no dedicated model exists for `user_channel_favorites` today; it is used via raw joins in controllers
- Reuse the `[004]` per-channel windowing, parameterized by the cap
- Optional: expose the popularity signal from `[003]` (views / star rating) as an alternate ordering within the cap ("show top N by views" vs "latest N"), noting it is a fetch-time snapshot

### Acceptance Criteria

- [x] Each subscription stores an `unwatched_cap` that persists across reloads; setting it to a value M limits that channel to at most M unwatched videos in the feed
- [x] The feed enforces per-channel caps simultaneously: with caps of 2 and 5 on two channels, the feed shows at most 2 and at most 5 unwatched videos from them respectively
- [x] A new subscription starts at the documented default cap without manual configuration, and an "unlimited" setting removes the cap entirely (all unwatched videos show)
- [x] The per-channel cap is editable from the UI and the changed value takes effect on the next feed render without a full reload

## [021] WebSub push ingestion (MVP)

**Status:** `done`
**Mode:** `Manual`
**Depends On:** none

### Goal

Prove YouTube WebSub (PubSubHubbub) push end-to-end for real channels: subscribe on channel-add, verify the subscription, receive and signature-verify push payloads, and route them into the existing `ingest` upsert path. Backfill a channel's existing videos with one poll at add time. This replaces the synchronous per-channel polling that blocks first paint and does not scale (naive polling from one server IP draws 429s at ~1,500-2,000 channels; push moves the ceiling to ~100k+).

### Scope

- Public HTTPS **callback controller**: GET handles the hub verification challenge (echo `hub.challenge`); POST receives the Atom fragment, verifies the HMAC against the stored `hub.secret`, then upserts via `RssFetcher::ingest`
- **Subscribe-on-add**: POST to `https://pubsubhubbub.appspot.com/subscribe` with `hub.mode=subscribe`, `hub.topic=<channel feed URL>`, `hub.callback`, and `hub.secret`
- **Poll-on-add backfill**: one `RssFetcher` fetch when a channel is first subscribed (WebSub is forward-only and does not hand over existing videos). Both subscribe and backfill happen at add time
- **Subscription-state migration**: a per-channel row storing topic URL, lease expiry, secret, and last-verified timestamp
- Dispatch subscribe/backfill as **queued jobs** (first `app/Jobs`), reusing the `app/Console/Commands` pattern where a command fits

### Technical Notes

- Google's hub is free, needs no API key, and does **not** consume YouTube Data API quota
- **Signature-verify every push before writing** — video rows are shared across users, so a forged payload could poison shared rows
- **Prerequisite:** a running queue worker in production. None exists today (`QUEUE_CONNECTION=database`, no `app/Jobs`)
- **Dependency:** the production HTTPS host + valid TLS the callback lives on. The whole design rests on this endpoint staying highly available (downtime = silently lost pushes); the hub is best-effort with no SLA
- **Confirm empirically before relying on it:** initial verification timing, lease duration actually granted, and redelivery behavior on callback 5xx. This is why the entry is `Manual`
- Set a **browser-like User-Agent** on the add-time poll; non-browser UAs get throttled harder
- Keep the existing synchronous in-render fetch in place as a fallback during MVP; retiring it (Finding F1) is `[022]`
- Reuses `RssFetcher::ingest` (`app/Services/RssFetcher.php`) as the push/poll write path. See `reference/FEED_PIPELINE_AUDIT.md` (Finding F1) for the pipeline this replaces
- **Out of scope:** channel-ID acquisition for onboarding (Data API lookup, webview scrape, or extension) is orthogonal to push vs. poll and is tracked as a separate future task

### Acceptance Criteria

- [x] Adding a channel subscribes it and backfills its ~15 existing videos via one poll
- [x] The hub verification challenge is answered and the subscription becomes active
- [x] A new upload arrives via push, its HMAC is verified, and it appears in the feed through `ingest`
- [x] A payload with an invalid signature is rejected and writes nothing

Verified live on 2026-08-23 through a Cloudflare Tunnel (`websub.peristalsis.tv`): the hub verified in ~2s and granted a 432000-second (5-day) lease. Runbook and findings are in `WEBSUB_LOCAL_SETUP.md`. Redelivery behavior on a callback 5xx is still unobserved and carries over to `[022]`.

---
