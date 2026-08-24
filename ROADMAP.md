# Roadmap

<!-- Next task number: [030] -->

## [006] Stop feed reverting to skeleton + scroll reset on tab return

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

Returning to a feed shows the already-loaded cards immediately, keeping scroll position, instead of flashing the loading skeleton and jumping to the top. This covers two triggers: switching to another sidebar feed and back, AND switching to a different **browser tab** and back to the app.

### Scope

- Skip the deferred loading skeleton when cached feed state is already present
- Preserve scroll position across the sidebar navigation round trip
- Ensure returning from another browser tab does not re-trigger the skeleton or reset scroll

### Technical Notes

- `[002]` cached loaded videos in `useFeedCache` and rehydrates them in `Feed.vue:57-62` / `Show.vue`, but the template still wraps the grid in `<Deferred data="videos">` whose `#fallback` (`FeedGridSkeleton`) renders on every navigation because `videos` is an `Inertia::defer` prop that refetches (`Feed.vue:373-376`)
- When cache is present (`hydratedFromCache`), the fallback should not show — render cached `items` directly and let the deferred payload reconcile in the background
- Scroll reset on sidebar nav comes from full Inertia `<Link>` navigations in `NavGroupFeeds.vue` / `NavMain.vue`; consider `preserveScroll` / scroll restoration keyed per feed
- **Browser-tab return**: identify what fires on window focus / `visibilitychange` — an Inertia poll, a `router.reload`, or the deferred prop re-resolving — and make the re-resolution render against the cached view (no skeleton, no scroll jump) rather than replacing `items`. This is a distinct trigger from in-app navigation and must be verified separately
- Applies to both `resources/js/pages/Videos/Feed.vue` and `resources/js/pages/Groups/Show.vue`
- **Build this before `[010]`**: the first-15 auto-load loop must render against cached/hydrated state, not re-trigger this skeleton

### Acceptance Criteria

- [ ] Loading a feed, switching to another, and returning shows cards with no skeleton flash
- [ ] Switching to another browser tab and back to the app shows cards with no skeleton flash and no scroll reset
- [ ] Scroll position on return matches the position before leaving (±a few px)
- [ ] Both `Feed.vue` and `Show.vue` pass both round trips

---

## [007] Open watched video in a background tab (Brave)

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

Clicking a video card opens the YouTube tab in the background so focus stays on the app instead of switching away. Target browser is Brave (Chromium).

### Scope

- Change card-click open behavior so the new tab opens in the background in Brave/Chromium

### Technical Notes

- Not a simple revert: `onCardClick`'s `window.open(url, '_blank', 'noopener')` (`Feed.vue:168-178`, equivalent in `Show.vue`) has been byte-identical since the first commit, and standard `window.open('_blank')` opens a **foreground** tab in Chromium by design. The only behavioral change on this path was `[001]` moving the click from `@click="onCardClick(video)"` on the card div to a Vue `$emit('card-click')` routed to the parent — but that does not restore backgrounding on its own
- Fix: replace `window.open` with a **synthetic `<a>` element clicked programmatically with a ctrl/meta modifier** (`new MouseEvent('click', { ctrlKey: true, metaKey: true, ... })` on an anchor with `target="_blank"` and `rel="noopener"`), which Chromium/Brave interpret as "open in background tab". Keep the click handler synchronous within the user gesture so no popup blocker trips
- Keep the `setState(..., 'watched')` side effect intact
- Verify in Brave during build (it is a browser heuristic, not a guaranteed API)

### Acceptance Criteria

- [ ] Clicking a card in Brave opens the video in a background tab; the app tab keeps focus
- [ ] The video is still marked watched on click
- [ ] The open action stays within the user gesture (no popup-blocker warning)

---

## [008] Persist show/hide-watched state across feeds

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

The show-watched / hide-watched toggle keeps its setting when moving between groups and the all-videos feed, instead of resetting per page.

### Scope

- Persist `showWatched` globally on the user (across all group feeds and the all-videos feed)
- Deliver it as a prop and restore it on mount

### Technical Notes

- `showWatched` is component-local `ref(true)` in `Feed.vue:43` and `Show.vue`, so it resets on every navigation
- **Persist server-side**, mirroring the existing `capEnabled` pattern: a column on the user, a POST route like `FeedCapController` (`feed.cap()` in `Feed.vue:155-166`), and the value delivered as a shared prop to the feed pages. Do **not** use `localStorage` — this matches the cap toggle and syncs across devices
- Toggling posts the new value (`preserveScroll`, `preserveState`) and updates local state optimistically; both `Feed.vue` and `Show.vue` read the same prop
- Pairs with `[019]` (starred-only), which uses the identical mechanism — build them together

### Acceptance Criteria

- [ ] Toggling hide-watched, navigating to another feed, and returning keeps hide-watched active
- [ ] The setting survives a full page reload and appears on a second device/browser after login
- [ ] The same persisted value applies to every group feed and the all-videos feed

---

## [009] Unify sidebar button highlighting (Cherry accent border)

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

Both sidebar sections (main nav and group feeds) use the same active-state highlight: a Cherry red accent border on a transparent background.

### Scope

- Apply one shared **border-only** active-highlight style (Cherry accent border, no fill) to both `NavMain` and `NavGroupFeeds`
- Drop the current solid `bg-cherry` fill on the group-feeds active item

### Technical Notes

- `NavGroupFeeds.vue:39-43` currently uses a solid `bg-cherry` fill for the active item; `NavMain.vue:23-27` relies on the default `SidebarMenuButton` `is-active` styling with no Cherry treatment
- Target: **border-only** — a Cherry accent border with the normal (transparent) background, applied identically to both. Remove the solid fill entirely; when active items lose the white-on-Cherry treatment, revert their text/badge colors to the normal foreground values
- Extract the active class set into one place (a shared const or small wrapper) so both sections stay in sync
- Cherry is available as `border-cherry` / the `--cherry` CSS var

### Acceptance Criteria

- [ ] Active items in both sidebar sections render an identical Cherry accent border with no solid fill
- [ ] Inactive items in both sections share identical styling
- [ ] The active-style definition lives in one place, not duplicated per section

---

## [010] Guarantee first 15 videos without Load more

**Status:** `todo`
**Mode:** `auto`
**Depends On:** [006]

### Goal

Each feed shows at least 15 videos on first load regardless of how they fall across Today / Yesterday / Earlier this week / Older, without the user clicking Load more.

### Scope

- After mount, auto-load pages until at least 15 cards are visible (or the cursor is exhausted)
- Confirm the time-bucketing keeps showing them even when the recent buckets are empty

### Technical Notes

- Buckets are computed client-side in `Feed.vue:248-279` from whatever `items` were loaded; empty buckets are filtered out, so an "Older" bucket already renders — the real constraint is how many videos are loaded
- **Approach: client-side auto-load loop.** Keep the existing page size but call `loadMore()` (`Feed.vue:184-218`) repeatedly after mount until the count of *visible* cards (after the `showWatched`/starred filters) is ≥15 or `nextUrl` is null. Guard against infinite loops when the cursor is exhausted
- This handles per-channel cap thinning (`[004]/[005]`): with the cap on, a page can yield few visible cards, so the loop keeps pulling
- **Depends on `[006]`**: the loop must render against the cache-aware/hydrated view so it does not re-trigger the loading skeleton or thrash requests. Sequence `[006]` first

### Acceptance Criteria

- [ ] A fresh feed with ≥15 available videos shows ≥15 cards without clicking Load more
- [ ] Videos from Older buckets appear when Today/Yesterday/Week are sparse
- [ ] Holds with the per-channel cap both on and off
- [ ] With fewer than 15 total available, the loop stops at the cursor end without spinning

---

## [011] Fold group management into Subscriptions, remove Groups page

**Status:** `todo`
**Mode:** `Manual`
**Depends On:** none

### Goal

Group create / rename / delete / management lives inside the Subscriptions page, and the standalone Groups index page is removed.

### Scope

- Add a group-management section to `Subscriptions.vue` (create, rename, delete groups)
- Remove `Groups/Index.vue` and its route/nav entry
- Redirect or repoint any links that pointed at the groups index (sidebar "+", "Manage channels" anchors)

### Technical Notes

- Groups index today: `resources/js/pages/Groups/Index.vue` (create/rename via `window.prompt`, delete via `window.confirm`, plus import/export). Group feed view `Groups/Show.vue` stays
- Entry points to update: `NavGroupFeeds.vue:21-27` "+" → point at `/subscriptions#group-management` (deep-link + scroll to the new section); `Subscriptions.vue:396-401` and empty states link to `groupRoutes.index()` → repoint to the in-page section; `Groups/Index.vue:135-139` is removed
- Give the new group-management section an anchor id (e.g. `group-management`) with `scroll-mt` so the deep-link lands cleanly, matching the existing `subscription-group-${id}` anchor pattern
- Import/Export lives in both pages today (`AllGroupsImportExportController`) — keep it available on Subscriptions
- Manual because it is a page-structure/IA change with routing and design decisions; sets up `[016]` and `[017]`

### Acceptance Criteria

- [ ] Groups can be created, renamed, and deleted from the Subscriptions page
- [ ] The standalone Groups index page and its route are gone
- [ ] No dead links remain to the removed page (sidebar "+", manage-channels anchors, empty states)

---

## [012] Replace subscription card Remove text with trash icon

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

The per-subscription Remove control is a trash-can icon button instead of the word "Remove".

### Scope

- Swap the "Remove" text button for a Heroicons trash icon in both subscription list views

### Technical Notes

- Two identical Remove buttons exist: `Subscriptions.vue:701-708` (alpha view) and `857-864` (by-group view)
- Use `TrashIcon` from `@heroicons/vue/24/outline`; keep `startRemove(channel)` wiring and the confirm dialog
- Preserve accessibility: add `aria-label="Remove subscription"` since the visible text is going away

### Acceptance Criteria

- [ ] Both views show a trash icon button in place of the "Remove" text
- [ ] The button keeps its destructive styling and still opens the confirm dialog
- [ ] The icon button has an accessible label

---

## [013] Always show the channel ID input in Add form

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

The "Add by channel ID" input is always visible in the Add-to-groups section, not hidden behind a collapsible.

### Scope

- Un-collapse the manual channel ID input so it renders inline at all times

### Technical Notes

- Currently wrapped in `<Collapsible v-model:open="idFallbackOpen">` with a trigger button in `Subscriptions.vue:484-543`
- Remove the collapsible wrapper and the `idFallbackOpen` state; keep the form, hint, validation, and `submitIdForm` logic
- The chevron icons (`ChevronDownIcon`/`ChevronUpIcon`) imports may become unused

### Acceptance Criteria

- [ ] The channel ID input and its Add button are visible without any toggle
- [ ] Adding by ID still works and validates group selection
- [ ] No leftover collapsible trigger or unused state remains

---

## [014] Single/multi group-select toggle in Add form

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

An icon-button toggle beside "Add to groups" switches between multi-select (current behavior) and single-select, where picking a new group replaces the current one instead of adding to it.

### Scope

- Add an icon-button toggle next to the "Add to groups" label
- In single-select mode, selecting a group replaces the current selection

### Technical Notes

- Selection state is `addGroupIds` (`Subscriptions.vue:64`) toggled by `toggleAddGroup` (`67-76`), which always adds/removes
- Add a `multiSelect` ref; when false, `toggleAddGroup` should set `addGroupIds` to `[groupId]` (or clear if re-clicking the selected one)
- Place the toggle inline with the `<Label>Add to groups</Label>` at `409-410`; pick a pair of Heroicons that read as multi vs single (e.g. squares vs single square)

### Acceptance Criteria

- [ ] A visible toggle sits next to the "Add to groups" label
- [ ] In single mode, clicking a second group switches selection to just that group
- [ ] In multi mode, behavior is unchanged (multiple groups selectable)

---

## [015] Swap TikTok hero icon for a film camera icon

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

The All Videos feed hero shows a film/video camera icon instead of the TikTok note glyph.

### Scope

- Replace the inline TikTok SVG in the All Videos hero with a Heroicons film/camera icon

### Technical Notes

- The TikTok path is a hand-rolled inline `<svg>` in `Feed.vue:302-312`
- `FilmIcon` and `VideoCameraIcon` are already imported/registered via `lib/groupIcons.ts`; use one from `@heroicons/vue/24/solid` or `/outline` to match the white-on-Cherry hero chip
- Keep the surrounding white rounded chip container and Cherry color

### Acceptance Criteria

- [ ] The All Videos hero shows a film/camera icon, no TikTok glyph
- [ ] Icon sizing/color matches the existing hero chip treatment

---

## [016] Edit group icon from group management

**Status:** `todo`
**Mode:** `Manual`
**Depends On:** [011]

### Goal

A group's icon can be changed from the group-management section, choosing from the full Heroicons set.

### Scope

- Add an icon picker to group management
- Persist the chosen icon and reflect it in the sidebar and group feed hero

### Technical Notes

- `ChannelGroup` already has an `icon` column (`#[Fillable(['user_id', 'name', 'icon'])]`, migration `2026_05_24_214413_add_icon_to_channel_groups_table.php`) and `resolveGroupIcon` maps names to components in `lib/groupIcons.ts`
- **Approach: full `@heroicons/vue/24/outline` set in a searchable grid.** Store the **raw icon name** (e.g. `RocketLaunchIcon`), not the current curated keys
- **Back-compat:** existing groups store legacy curated keys (`Braces`, `Movie`, …). `resolveGroupIcon` must resolve BOTH the full set and the legacy `ICON_REGISTRY` keys (keep the existing map as a fallback), or run a one-time data migration mapping old keys → raw names. Resolve-both is lower risk
- **Bundle:** importing the full icon set is large — lazy-load the picker grid (dynamic import) so it does not bloat the main feed bundle
- Persist via the group update route (`ChannelGroupController`); the picker lives in the group-management section from `[011]`

### Acceptance Criteria

- [ ] Group management offers a searchable icon picker exposing the full Heroicons outline set
- [ ] Selecting an icon persists (as a raw icon name) and updates the sidebar icon and feed hero
- [ ] Groups created before this change (legacy icon keys) still render their icon correctly

---

## [017] Group color: sidebar icon + feed hero background

**Status:** `todo`
**Mode:** `Manual`
**Depends On:** [016]

### Goal

Each group can be assigned a color that drives its sidebar icon color and the feed hero background.

### Scope

- Add a `color` attribute to groups with a color picker in group management
- Apply the color to the sidebar icon and the group feed hero gradient/background

### Technical Notes

- No `color` column exists yet — add a migration and add `color` to `ChannelGroup` fillable (currently `['user_id', 'name', 'icon']`)
- Sidebar icon renders in `NavGroupFeeds.vue:46`; group hero lives in `Groups/Show.vue` (mirrors the Cherry gradient hero in `Feed.vue:288-312`)
- **Approach: curated swatch set (~8–12 presets).** Define a preset table (a shared TS map) where each preset carries its own tuned hero gradient (top/deep stops) and sidebar icon color, all chosen so white hero text stays legible. Store the swatch key or its hex. No free-form picker, so no runtime contrast logic needed
- Default hero stays Cherry when no color is set
- Build the color picker alongside the `[016]` icon picker so icon + color are edited in one place in the group-management section

### Acceptance Criteria

- [ ] A group color can be chosen from the preset swatches and persists
- [ ] The sidebar icon for that group renders in its color
- [ ] The group feed hero background reflects the preset gradient, with legible white text
- [ ] A group with no color set falls back to the Cherry hero

---

## [018] Show publish date on video cards

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

Each video card shows its publish date floated to the right of the channel row, with the channel name truncating via ellipsis when it is too long.

### Scope

- Add a publish-date element to the card footer, right-aligned opposite the channel info
- Ensure the channel name truncates so the date always has room

### Technical Notes

- Card footer is the channel row in `VideoCard.vue:73-84`; `video.published_at` is already on the `Video` interface (`VideoCard.vue:15`)
- Layout: make the channel name/avatar a flexible truncating group and the date a `shrink-0` element on the right (the channel `<span>` already has `truncate`)
- **Format: compact relative age** — `3d`, `2w`, `5mo` (fall back to a coarse unit for older). Apply via a small helper; both `Feed.vue` and `Show.vue` render through this shared `VideoCard`, so one change covers both

### Acceptance Criteria

- [ ] Cards show the publish date right-aligned in the channel row, in relative format (e.g. `3d`, `2w`)
- [ ] A long channel name truncates with an ellipsis and does not push the date off the card
- [ ] Change appears on both the all-videos and group feeds via `VideoCard`

---

## [019] Starred-only toggle on feeds (persisted)

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

A "show favorited only" toggle on the feed pages filters cards to favorited channels, and the setting persists across navigation and reloads.

### Scope

- Add a starred-only toggle to the feed hero controls
- Filter the bucketed feed to favorited channels when on
- Persist the setting like `[008]`'s show-watched toggle

### Technical Notes

- `channel_is_favorite` is already on each `Video` (`Feed.vue:24`, used for star display in `VideoCard.vue:53-63`), so filtering is client-side alongside the existing `showWatched` filter in `buckets` (`Feed.vue:253-255`)
- Add the toggle next to the existing hero buttons (`Feed.vue:327-370`) using the star icons already available from `@heroicons/vue`
- **Persist server-side**, identical mechanism to `[008]` (user column + POST route like `FeedCapController`, delivered as a prop). Build alongside `[008]`; apply to both `Feed.vue` and `Show.vue`

### Acceptance Criteria

- [ ] A starred-only toggle appears on the feed hero controls
- [ ] Enabling it shows only cards from favorited channels
- [ ] The setting survives navigation, a full reload, and appears on a second device after login, on both feeds

---

## [020] Keep users logged in for 90 days

**Status:** `todo`
**Mode:** `auto`
**Depends On:** none

### Goal

Logged-in users stay authenticated for up to 90 days instead of the current default session window.

### Scope

- Extend the effective login duration to 90 days

### Technical Notes

- `config/session.php:35` sets `'lifetime' => (int) env('SESSION_LIFETIME', 120)` (minutes) — 120 min today
- **Approach: long session lifetime.** Set `SESSION_LIFETIME=129600` (90 days). Confirmed low-risk: driver is `database` (`config/session.php:21`), `expire_on_close` is already `false` (`:37`), and GC deletes rows only past `lifetime` — so a 90-day lifetime keeps sessions the full 90 days. Pure config change, no remember-me cookie needed
- Set it in `.env` (and `.env.example`); confirm the default in `config/session.php` if a fallback matters

### Acceptance Criteria

- [ ] A logged-in user remains authenticated after being idle well beyond the old 120-minute window
- [ ] `SESSION_LIFETIME` is set to 129600 in `.env` / `.env.example`
- [ ] Sessions still expire at ~90 days, not indefinitely

---

## [023] Add channels by URL or @handle (YouTube Data API, quota-capped)

**Status:** `todo`
**Mode:** `Manual`
**Depends On:** none

### Goal

Let a user add a channel by pasting a YouTube channel URL or an @handle, not only a raw UC id. Resolution uses the YouTube Data API only where needed, self-capped at 9500 units/day with a soft "daily new-channel cap reached, try again soon" message when exhausted. Supersedes [013] (the smart input replaces the always-visible id-only field). This is the channel-ID acquisition work deferred by [021].

### Scope

- One smart add-input in Subscriptions.vue accepting a UC id, a channel URL, or an @handle
- Backend input sniffing that routes to a free local parse or a counted Data API resolve
- A nullable `handle` column on `channels` to cache handle -> channel_id and dedupe repeat adds for free
- A durable daily API-usage counter (DB row keyed by Pacific date) and a soft cap at 9500
- The cap message on API-requiring adds only; free-path adds still succeed when capped

### Technical Notes

- Accepted forms: raw `UC…` and `/channel/UC…` URLs parse locally (free, no tick); `@handle` and `/@handle` URLs use Data API `forHandle` (1 unit); `/user/legacyname` uses `forUsername` (1 unit). `/c/` custom URLs are rejected with "paste the @handle instead" (no 100-unit search.list, no scraping).
- Counter ticks **per real Data API call**, including calls returning zero results. Route **every** Data API request through one counted client — including the name-enrichment fallback `ChannelResolver::lookupChannelName()` (`ChannelResolver.php:148-165`), so no spend escapes the budget. Prefer the id+snippet that `forHandle`/`forUsername` already returns so name enrichment needs no extra unit.
- `ChannelResolver::fromHandle()` (`ChannelResolver.php:45-84`) already exists but throws if no key; extend it to also handle `/user/` and to persist the resolved `handle`. `fromChannelId()` (`:21-38`) stays the free path; add a URL-sniffing entrypoint that extracts `UC…` from `/channel/…` URLs and dispatches the rest.
- Counter storage: a small table (e.g. `youtube_api_usage` with `date_pt`, `units_used`) with an atomic increment, keyed by `America/Los_Angeles` date to match Google's midnight-Pacific reset. Follow the per-user persistence pattern of `FeedCapController` / `UserChannelCap` (`updateOrCreate` with a sentinel) as the closest existing template, but this counter is **global**, not per-user.
- Cap behavior: check remaining budget **before** an API call. If a resolution would need the API and budget is exhausted, reject with the cap message surfaced as a `value` validation error (matching how `SubscriptionController::store` rethrows resolver failures at `:85-87`). Free-path (`UC…`, `/channel/…`) and cached-handle adds bypass the check.
- Migration: add nullable `handle` (indexed) to `channels` (`2026_05_04_170004_create_channels_table.php` schema; model fillable at `Channel.php:12`). Backfill is unnecessary — handle populates lazily on next resolve.
- Frontend: replace the "Add by channel ID" collapsible (`Subscriptions.vue:484-543`) with one always-visible input; `submitIdForm` (`:163-178`) posts to `subscriptions.store` (`POST /subscriptions`) with a single `value`. Backend derives `mode` from the value shape (drop the client-sent `mode` reliance, or keep `mode` but add an `auto` branch). Keep the group-selection guard.
- **Prerequisite:** a real `YOUTUBE_API_KEY` / `services.youtube.api_key` provisioned against a Google Cloud project with the Data API enabled. `fromHandle()` throws without it.
- **Manual** because it depends on external API-key provisioning and needs empirical verification of `forHandle`/`forUsername` behavior against live channels.

### Acceptance Criteria

- [ ] Pasting a `/channel/UC…` URL or a raw `UC…` id adds the channel with zero Data API units spent and no counter tick
- [ ] Pasting an `@handle`, `/@handle` URL, or `/user/…` URL resolves via the Data API, spends exactly one unit, ticks the counter, and stores the handle
- [ ] Re-adding a previously resolved `@handle` is a free local lookup (no unit, no tick)
- [ ] A `/c/` custom URL is rejected with a message directing the user to the @handle
- [ ] When the counter reaches 9500, an API-requiring add is refused with "daily new-channel cap reached, try again soon", while a `UC…`/`/channel/…` add still succeeds
- [ ] The counter is stored durably and resets at midnight America/Los_Angeles

---

## [029] Make polling the backbone, demote WebSub to accelerator

**Status:** `todo`
**Mode:** `Manual`
**Depends On:** [028]

### Goal

Videos keep arriving on a predictable schedule whether or not Google's hub delivers anything, and a delivery drought becomes visible instead of silent. [028] proved YouTube's publisher does not reliably ping its own hub for our feeds, so push is unreliable by nature and nothing on our side can fix it. This item makes polling the guaranteed path and leaves WebSub in place as a free accelerator for the cases where it does fire.

### Scope

- An hourly stalest-first poll budget, so a push outage costs hours of staleness rather than everything
- A drought signal, so "push is dead" is a reportable state rather than a tinker session
- The `Pending` backstop coverage hole from [028]
- The `channels.rss_url` deviation check from [028]
- **Not in scope:** any change to the subscribe or receive path. [028] proved both correct

### Technical Notes

**Keep WebSub, demote it.** No change to the subscribe or receive path. `ChannelSubscription::callbackUrl()` derives the URL from `services.websub.callback_base` (currently null, falling back to `APP_URL` = `https://peristalsis.tv`) at call time rather than storing it, so a later host change is picked up by the next renewal automatically.

**Poll floor: a fixed hourly budget, stalest-first.** Ingestion is push-only today. `RssFetcher::fetchForChannels` is reached only from the backstop, and the backstop is failure-driven, gated behind `backstop_min_silence_hours` (48) and `backstop_min_repoll_hours` (6). Every failure signal read healthy during the [028] outage, so the backstop was a no-op while ~95% of uploads went missing.

Add a scheduled sweep polling the **N most-stale channels each hour** by `last_fetched_at`, N being a configured budget. Deliberately a budget, **not** "poll everything older than X hours": a budget makes the outbound request rate a constant you set, independent of channel count, while a staleness threshold grows linearly with channel count and walks straight back into the 429 ceiling [021] was built to escape. Freshness degrades gracefully instead of the request rate exploding. At a 200/hour budget:

| Channels | Refresh interval | Requests/day |
| --- | --- | --- |
| 193 (today) | ~1 hour | ~4,600 |
| 1,000 | ~5 hours | ~4,600 |
| 2,000 | ~10 hours | ~4,600 |
| 10,000 | ~2 days | ~4,600 |

For scale: [021] put the 429 threshold at naive polling of ~1,500-2,000 channels, roughly 96,000 requests/day on the old 30-minute TTL. A fixed 4,600/day is about 5% of that. Conditional GET is already implemented (`RssFetcher::pollHeaders` sends `If-None-Match` / `If-Modified-Since`, 304s handled at `RssFetcher.php:76-88`), so most of the budget returns 304 with no body transferred.

**Drought detection.** Nothing surfaces "push is dead" as a state. `services.websub.alert_webhook` fires only on renewal failure, which stayed at 0 throughout the outage. Add a `websub:health` command (or extend the backstop) reporting counts by status, oldest and newest `last_delivery_at`, and the never-delivered count, warning loudly when install-wide deliveries flatline over N hours. Current production is a live known-bad fixture to build against: 191 of 193 have never delivered.

**Backstop coverage hole.** Add a rule to `WebSubBackstopCommand::reasonToRepoll` treating a subscription `Pending` beyond a small multiple of `renew_retry_hours` as a re-poll candidate. Today it falls through both `leaseHasLapsed` (short-circuits on `status !== Active`) and `silenceAnomaly` (needs 3 uploads plus 48 hours of silence).

**`rss_url` deviation.** Surface any channel whose stored `channels.rss_url` differs from the canonical form built from `channel_id`. Currently 0 of 193 deviate, but `Channel::rssUrl()` prefers the stored column, so a legacy odd row reproduces the [028] signature exactly and invisibly. Folding the check into the health command is the cheap option.

**Manual** because the budget has to be tuned against live 429 behaviour and verified over a full day of production traffic.

### Verification (no automated tests)

This item ships no `### Tests` section, deliberately. Every open criterion is a claim about live third-party behaviour over time, and a Pest test can only assert against a fake of that behaviour, which is exactly the thing [028] proved we cannot model. The outage was invisible precisely because every in-process signal read healthy: a suite mocking the hub and the RSS endpoint would have stayed green through all 20 hours of it.

| Criterion type | Why a test cannot carry it | How it is verified instead |
| --- | --- | --- |
| Poll budget holds under real load | The 429 ceiling is YouTube's, unpublished, observed only in production; a faked client proves the code issues N requests, not that N is safe | Run the sweep on production, watch failure counts and response codes across a full day |
| No channel exceeds the refresh interval | Depends on real timing across hours of scheduler ticks | Query `max(now() - last_fetched_at)` across all channels after a day of sweeps |
| Drought detection warns correctly | The condition it detects is "a third party silently stopped doing something" | Run the command against known-bad production state, available now: 191 of 193 `never_delivered` |
| Uploads land within one interval | Requires a real upload on a real channel | Cross-check a new video's `published_at` against its ingest time |

The mechanically testable parts (stalest-first ordering, budget cap arithmetic, the `Pending` rule) are small additions to paths already covered by the five existing `tests/Feature/WebSub*Test.php` files, and those must keep passing. If the sweep or the `Pending` rule later grows real branching logic, add tests then. Writing them now would encode assumptions about hub behaviour that [028] just disproved.

### Acceptance Criteria

- [ ] A scheduled hourly sweep polls the N stalest channels by `last_fetched_at`, N being a configured budget
- [ ] The outbound request rate stays constant as channel count grows, so freshness degrades with scale but the 429 ceiling is never approached
- [ ] The sweep runs a full day on production with no sustained 429s and no elevated fetch failures
- [ ] After a day of sweeps, no channel's `last_fetched_at` is older than the configured refresh interval
- [ ] An upload on a channel whose hub page reads "Content received: n/a" reaches the feed within one refresh interval
- [ ] The WebSub subscribe and receive paths are unchanged and still ingest when the hub does deliver, with the five existing `WebSub*Test.php` files still passing
- [ ] A subscription stuck in `Pending` past the retry window is a backstop re-poll candidate
- [ ] One command reports counts by status, oldest and newest `last_delivery_at`, and the never-delivered count, so a drought needs no tinker session to see
- [ ] That command warns loudly against current production state, where 191 of 193 subscriptions have never delivered
- [ ] Any channel whose stored `channels.rss_url` deviates from the canonical `channel_id` form is surfaced, closing the latent variant of the topic-mismatch failure
