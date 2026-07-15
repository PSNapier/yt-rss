# Feed Pipeline Audit

A descriptive + diagnostic walkthrough of how the two feed pages (**All Videos** and **Group** feeds) fetch, render, and cache their videos, end to end. Purpose: a shared mental model before building the pending feed roadmap tasks ([006], [008], [010], [019]).

Findings are called out inline as **⚠ Finding** and collected in [§7](#7-diagnostic-summary). This document describes the system as it stands and flags issues; it does **not** prescribe fixes.

> Line references reflect the code at the time of writing. Treat them as anchors, not guarantees.

---

## 1. System map

Both feeds follow the same high-level path. The only synchronous work on the initial request is a tiny page shell; the video list is deferred and resolves in a second round trip.

```
Browser
  │  GET /feed  (or /groups/{id})
  ▼
Controller  (AllVideosFeedController / GroupFeedController)
  │  renders Inertia page:
  │    • capEnabled   → sync prop (immediate)
  │    • videos       → Inertia::defer(...)  (NOT in first response)
  ▼
Inertia issues a 2nd partial request for `videos`
  ▼
Deferred callback runs:
  1. if no ?cursor  → RssFetcher refreshes stale channels (BLOCKING network I/O)
  2. Video query (+ per-user joins, + optional cap scope)
  3. cursorPaginate() → { data, next_page_url, ... }
  ▼
Vue page (Feed.vue / Groups/Show.vue)
  │  • hydrate `items` from in-memory useFeedCache (if returning)
  │  • else apply deferred payload to `items`
  │  • persist `items` back to cache (deep watch)
  ▼
Client-side time bucketing (Today / Yesterday / Earlier this week / Older)
  ▼
VideoCard grid   +   infinite scroll / "Load more" → fetch(next_page_url)
```

The pipeline has four layers: **RSS ingestion** (§2), **data model + cap scope** (§3), **controllers + defer boundary** (§4), and **Vue render + cache** (§5–6). Inertia's deferred-prop mechanics sit between the last two (§4).

---

## 2. RSS ingestion layer

**File:** `app/Services/RssFetcher.php`

The fetcher pulls YouTube RSS XML for a set of channels and upserts videos.

- **Entry points:** `fetchForGroup(ChannelGroup)` delegates to `fetchForChannels(Collection<Channel>)` (`:34`, `:45`). Both accept a `force` flag.
- **Staleness gate** (`isStale`, `:101`): a channel is skipped unless `last_fetched_at` is null or older than the **30-minute** TTL (`$ttlMinutes = 30`, `:19`). `force: true` bypasses this (used by the group "refresh" button).
- **Batched HTTP** (`:58-65`): stale channels are chunked (`poolChunkSize = 20`) and fetched concurrently via `Http::pool`, with a **2s connect** / **3s total** timeout each (`:21-22`). On success, `last_fetched_at` is stamped (`:82`).
- **Ingest** (`ingest`, `:115`): parses the Atom/RSS XML with `simplexml`, resolves the `yt:` and `media:` namespaces, backfills the channel title from `<title>` (`:132-138`), and upserts each entry via `Video::updateOrCreate(['youtube_video_id' => …], …)` (`:169`). Missing `videoId` entries are skipped.

**⚠ Finding F1 — ingestion blocks first paint.** The fetch is triggered *synchronously inside the deferred render callback* on the first (cursorless) load (see §4). There is no queue or background job. First feed paint therefore waits on live RSS network I/O for every stale channel (bounded by the 3s-per-request timeout and pool concurrency, but still on the critical path). Relevant to any task that wants a fast first render ([006], [010]).

**⚠ Finding F2 — a read path performs destructive deletes.** During `ingest`, any entry whose alternate link contains `/shorts/` triggers a hard delete of both the `Video` and its `UserVideoState` rows (`:150-156`):

```php
if ($this->alternateHrefIsShort($alternateHref)) {
    UserVideoState::query()->where('youtube_video_id', $videoId)->delete();
    Video::query()->where('youtube_video_id', $videoId)->delete();
    continue;
}
```

This is a write (and a *destructive* one) executed as a side effect of rendering a feed. If a video is later reclassified, or if watch-state was recorded before it appeared as a Short, that state is silently lost.

---

## 3. Data model + cap scope

### Per-user overlay tables

The feed query is a `videos` scan left-joined against three per-user tables so one shared video row can carry user-specific state:

| Table | Carries | Joined as |
|---|---|---|
| `user_video_states` | `state` = `watched` \| `hidden` \| (null = unwatched) | `user_video_states` |
| `user_channel_favorites` | channel starred? → `channel_is_favorite` | `ucf` |
| `user_channel_caps` | per-channel unwatched cap | `ucc` (only when cap on) |

Plus a scalar `feed_cap_enabled` flag on the user, surfaced as the `capEnabled` prop.

### The cap scope

**File:** `app/Models/Video.php` — `scopeUnwatchedCappedPerChannel($userId)` (`:31-65`)

When the cap is on, the feed shows **all watched videos** plus, per channel, only the **N newest unwatched** videos. N is `COALESCE(ucc.cap, DEFAULT_CAP)` where `UserChannelCap::DEFAULT_CAP = 1` (`app/Models/UserChannelCap.php:15`), and `0` means unlimited.

The "newest N unwatched" test is a **correlated subquery per candidate row** — it counts how many *newer* unwatched videos exist for the same channel and keeps the row only if that count `< cap` (`:52-59`):

```sql
(select count(*) from videos as v2
   left join user_video_states as uvs2
     on uvs2.youtube_video_id = v2.youtube_video_id and uvs2.user_id = ?
  where v2.channel_id = videos.channel_id
    and uvs2.state is null
    and (v2.published_at > videos.published_at
      or (v2.published_at = videos.published_at and v2.id > videos.id))
) < COALESCE(ucc.cap, 1)
```

**⚠ Finding F3 — cap cost scales per-row.** This correlated subquery runs for every unwatched candidate row, with no called-out supporting index. It's fine at current data sizes, but [010]'s proposed auto-load loop repeatedly re-queries under the cap (each page can yield very few visible cards once thinned), so the cost compounds. Worth measuring before building that loop.

---

## 4. Controllers, query, and the defer boundary

**Files:** `app/Http/Controllers/AllVideosFeedController.php`, `app/Http/Controllers/GroupFeedController.php`

Both controllers render an Inertia page with two props:

- `capEnabled` — synchronous, available on first paint.
- `videos` — wrapped in `Inertia::defer(fn () => …)`, so it is **excluded from the initial response** and resolved by a second partial request.

Inside the deferred callback (identical shape in both):

1. **Conditional RSS refresh** — `if (! $request->filled('cursor'))` refreshes RSS (`AllVideos:30`, `GroupFeed:28`). This is the guard that makes the fetch happen on first load but **not** on subsequent paginate requests (which carry a `?cursor`).
2. **The query** — selects a fixed column list including the raw `ucf.channel_id IS NOT NULL as channel_is_favorite` expression (cast to bool in the model), left-joins the per-user tables, filters out `hidden` (`state IS NULL OR state != 'hidden'`), applies `->when($capEnabled, …unwatchedCappedPerChannel)`, orders by `published_at desc, id desc`, and calls `cursorPaginate()->withQueryString()`.

The two controllers differ in their scope of channels (all subscribed vs. one group's join) and in RSS entry point (`fetchForChannels` vs. `fetchForGroup`).

**⚠ Finding F4 — duplicated query with a page-size divergence.** The two deferred queries are near-verbatim copies, but the page sizes differ:

- All Videos → `cursorPaginate(24)` (`AllVideosFeedController.php:64`)
- Group → `cursorPaginate(15)` (`GroupFeedController.php:63`)

This is undocumented and asymmetric. It directly affects [010] ("guarantee first 15 without Load more"): the group feed delivers exactly 15 per page while the all-feed delivers 24, so any "first 15" logic behaves differently per feed, especially once the cap thins results.

---

## 5. Inertia transport + deferred mechanics

`Inertia::defer` produces a **two-phase load**:

1. **Phase 1** — the initial navigation returns the page with `capEnabled` (and `group`) but *no* `videos` key.
2. **Phase 2** — Inertia automatically fires a partial reload requesting only the deferred prop. On the client this is wrapped by `<Deferred data="videos">`, which shows its `#fallback` (`FeedGridSkeleton`, count 8) until `videos` resolves.

**Pagination.** `cursorPaginate()` yields `{ data, next_cursor, prev_cursor, next_page_url }`. The `next_page_url` embeds the cursor and (via `withQueryString`) preserves query params.

**Load-more is hand-rolled, not routed through Inertia.** Both pages implement `loadMore()` as a raw `fetch(nextUrl)` with manually-set Inertia partial headers (`Feed.vue:184-218`, `Show.vue:211-245`):

```
X-Inertia: true
X-Inertia-Partial-Component: Videos/Feed   (or Groups/Show)
X-Inertia-Partial-Data: videos
X-Inertia-Version: <page.version>
```

It reads `json.props.videos`, pushes `data.data` into `items`, and advances `nextUrl`. This bypasses the Inertia router entirely (no history entry, no shared-prop reconciliation) — a deliberate choice to append rather than replace, but it means load-more responses skip Inertia's normal prop-merge lifecycle.

---

## 6. Vue render, cache, and infinite scroll

This is the heart of the mental model, and the layer where the two pages have **diverged** despite starting from the same template.

### 6.1 Shared skeleton

Both pages:

- Hold `items = reactive<Video[]>([])` as the single source of truth for rendering.
- Restore from the module-scoped `useFeedCache` on mount, then persist back via a **deep watcher** on `[items, nextUrl, …]` (`Feed.vue:85-95`, `Show.vue:111-123`).
- Ignore the deferred phase-2 payload when the cache already restored state (the `hydratedFromCache` flag, below).
- Time-bucket `items` client-side into **Today / Yesterday / Earlier this week / Older**, with a `showWatched` filter applied first (`Feed.vue:248-279`, `Show.vue:266-297`). Empty buckets are filtered out.
- Fire optimistic `setState(watched|hidden|null)` that mutates `items` immediately, POSTs to the state route, and — **only when the cap is on** — follows with `router.reload({ only: ['videos'] })` because a state change alters which video each channel surfaces (`Feed.vue:117-152`, `Show.vue:146-181`).

### 6.2 The cache

**File:** `resources/js/composables/useFeedCache.ts`

A module-scoped `Map<string, FeedCacheEntry>` keyed by feed identity (`'all'`, `'group:{id}'`). It survives Inertia SPA `<Link>` navigations because the JS runtime stays alive, but a **full page reload wipes it** (`clearFeedCache` also exists for cap edits). `saveFeedCache` stores shallow copies of each item to decouple from reactive state (`:19-30`).

### 6.3 The cache-vs-deferred handshake (the core quirk)

On a cached return, the page restores `items` from cache and sets `hydratedFromCache = true`. When the deferred **page-one** payload then arrives, the `watch(() => props.videos)` handler sees the flag, clears it, and **returns without applying the payload** (`Feed.vue:64-82`, `Show.vue:90-108`):

```js
if (hydratedFromCache.value) {
    hydratedFromCache.value = false;
    return;   // ← deferred page-one is discarded
}
applyItems(v.data, v.next_page_url);
```

This is intentional: it preserves infinite-scroll progress (you might have loaded 100 videos; page one would clobber you back to ~24).

**⚠ Finding F5 — returning to a feed shows stale data.** The flip side of the handshake: because the fresh phase-2 payload is dropped, any videos ingested server-side since you last visited are **invisible until a full page reload** clears the cache. This is the deliberate tradeoff introduced by [002] and inherited by [006] — it is a design consequence, not only a bug.

**⚠ Finding F6 — skeleton flash on every return ([006]).** `<Deferred data="videos">` renders its `FeedGridSkeleton` fallback whenever `videos` re-resolves, which is on *every* navigation to the page — even when `items` is already populated from cache. The cache hydration fills `items` but does nothing to suppress the `<Deferred>` fallback, so the user sees a skeleton flash over already-known content. Any fix must render the cached grid *outside* the `<Deferred>` fallback gate.

**⚠ Finding F7 — tab-return trigger is UNVERIFIED.** There is **no** `visibilitychange` / `focus` handler, polling, or `WhenVisible` anywhere in either page's `<script>`. The `onMounted` hooks register only click/scroll listeners for the context menu (`Feed.vue:220-232`, `Show.vue:247-250`). What actually re-triggers the skeleton and scroll reset when returning from another **browser tab** (part of [006]) is not evident in the code and must be confirmed empirically — it may be Inertia's default behavior, a browser bfcache interaction, or nothing at all. Do not assume a mechanism; measure it first.

### 6.4 Where the two pages diverge

Despite the shared skeleton, the load-more UX is **not** the same:

| Aspect | `Feed.vue` (All Videos) | `Groups/Show.vue` (Group) |
|---|---|---|
| Load more | **Automatic** — `IntersectionObserver` on a `sentinel` div triggers `loadMore()` on scroll (`:180-232`, `:434`) | **Manual** — a "Load more" button; **no observer at all** (`:559-568`) |
| "Older" bucket | Always rendered inline | **Collapsible** — hidden behind a "Show older videos (N)" button via `olderExpanded` / `displayBuckets` (`:299-340`) |
| Cache key | Constant `'all'`, read once at setup (`:57`) | Computed `group:{id}`, **watched** so switching groups without a remount re-hydrates (`:57`, `:71-88`) |
| `olderExpanded` persistence | Always saved as `false` (`:92`) | Persisted per feed (`:111-123`) |
| Page size (from controller) | 24 | 15 |

**⚠ Finding F8 — the "shared" feed pages have drifted.** They began as copies but now differ in load-more model (auto infinite-scroll vs. manual button), older-bucket handling, and cache-key logic. Tasks that need to touch both ([006], [008], [010], [019]) currently require editing two non-identical components in parallel, which is where per-feed regressions creep in.

**⚠ Finding F9 — filter state is component-local.** `showWatched` is a plain `ref(true)` in both pages (`Feed.vue:43`, `Show.vue:53`), so it resets on every navigation — unlike `capEnabled`, which is server-persisted on the user. This is exactly the gap [008] (persist show-watched) and [019] (starred-only) intend to close by mirroring the `capEnabled` pattern.

---

## 7. Diagnostic summary

| # | Finding | Where | Impact | Roadmap |
|---|---|---|---|---|
| F1 | RSS fetch blocks first paint (sync, in-render, no queue) | `RssFetcher.php` via controllers `:28-34` / `:26-31` | Slow first render on stale feeds | [006], [010] |
| F2 | Destructive delete of Video + UserVideoState on `/shorts/` during ingest | `RssFetcher.php:150-156` | Silent data/state loss on a read path | — |
| F3 | Per-row correlated subquery in cap scope, no index called out | `Video.php:52-59` | Cost compounds under [010]'s auto-load loop | [010] |
| F4 | Duplicated query; page size 24 (all) vs 15 (group) | `AllVideosFeedController.php:64`, `GroupFeedController.php:63` | "First 15" behaves differently per feed | [010] |
| F5 | Deferred payload discarded on cached return → stale data until full reload | `Feed.vue:64-82`, `Show.vue:90-108` | New videos invisible on return | [002], [006] |
| F6 | `<Deferred>` fallback skeleton flashes over cached content every navigation | `Feed.vue:373`, `Show.vue:494` | Skeleton flash on return | [006] |
| F7 | No visibilitychange/focus/poll handler — tab-return trigger unknown | both pages' `onMounted` | Can't fix [006] tab-return without measuring first | [006] |
| F8 | Feed.vue and Show.vue have drifted (infinite-scroll vs. manual, older-collapse, cache key) | `Feed.vue:180-232`, `Show.vue:299-340` | Two non-identical components to edit in tandem | [006], [008], [010], [019] |
| F9 | `showWatched` is component-local, resets on navigation | `Feed.vue:43`, `Show.vue:53` | Filter doesn't persist | [008], [019] |

---

## 8. Sequence appendix

The three lifecycles that matter, side by side.

### First visit (no cache)

```mermaid
sequenceDiagram
    participant B as Browser
    participant C as Controller
    participant R as RssFetcher
    participant DB as Database
    participant V as Vue page

    B->>C: GET /feed
    C-->>B: page shell (capEnabled, NO videos)
    Note over V: <Deferred> shows FeedGridSkeleton
    B->>C: partial request (videos)
    C->>R: fetchForChannels() [no cursor → refresh]
    R->>DB: upsert videos (BLOCKING, F1)
    C->>DB: query + cap scope + cursorPaginate(24/15)
    DB-->>C: page 1 data
    C-->>V: videos payload
    Note over V: applyItems(); persist to cache
    V-->>B: bucketed grid
```

### Return to a cached feed (SPA nav)

```mermaid
sequenceDiagram
    participant B as Browser
    participant Cache as useFeedCache
    participant C as Controller
    participant V as Vue page

    B->>V: navigate back to feed
    V->>Cache: getFeedCache(key)
    Cache-->>V: items + cursor (hydratedFromCache = true)
    Note over V: <Deferred> STILL shows skeleton (F6)
    B->>C: partial request (videos) [no cursor → RSS refresh again]
    C-->>V: fresh page-one payload
    Note over V: hydratedFromCache → DISCARD payload (F5)
    Note over V: cached items remain; new server videos NOT shown
```

### Load more

```mermaid
sequenceDiagram
    participant V as Vue page
    participant C as Controller
    participant DB as Database

    Note over V: IntersectionObserver (Feed) or button (Show)
    V->>C: fetch(next_page_url) + X-Inertia-Partial headers
    Note over C: ?cursor present → NO RSS refresh
    C->>DB: query + cursorPaginate (next page)
    DB-->>C: next page data
    C-->>V: json.props.videos
    Note over V: items.push(...data); advance nextUrl; persist
```
