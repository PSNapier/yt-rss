# Roadmap

<!-- Next task number: [044] -->

## [031] Scale ingestion past one server IP

**Status:** `freezer`
**Depends On:** [029]

### Goal

Ingestion keeps working when the channel table is large enough that a full 30-minute sweep no longer fits inside the safe request rate of a single server IP. Deferred on purpose: at 193 channels [029]'s poll-everything sweep is correct and this is all complexity for no gain. This item exists so the analysis is not re-derived from scratch when it is needed, and so [029] can stay simple without pretending the ceiling does not exist.

### Scope

- Measuring the actual per-IP request ceiling, which is currently unknown
- Cadence-tiered and demand-ordered channel selection
- The YouTube Data API as a second, IP-independent request budget
- Distributed polling across owned servers
- **Not in scope:** residential proxy rotation. See the reasoning below

### Technical Notes

**Trigger for unfreezing:** a [029] sweep hitting `POLL_MAX_PER_SWEEP`, sustained block signals from the [029] detector, or a deliberate decision to take the app multi-tenant. Not before.

**The central unknown.** `reference/YOUTUBE_RSS_RATE_LIMITS.md` establishes that no per-IP RSS ceiling is published, and that the `~1,500-2,000 channels` figure inherited into [021] is unsourced and probably misattributed from a FreeTube post describing a different endpoint under an explicit RSS exemption. Every scaling decision below multiplies a number nobody has measured. **Measure it first:** ramp the [029] sweep's request rate stepwise on production and watch the [029] block detector. That converts the central unknown into a number, and it is cheap. Everything else here is guesswork until it is done.

**Why a fixed budget beats a staleness threshold at scale.** Poll-everything makes the outbound request rate grow linearly with channel count. A fixed hourly budget with rotation makes it a constant you set, and freshness degrades gracefully instead of the request rate exploding. At a 200/hour budget:

| Channels | Refresh interval | Requests/day |
| --- | --- | --- |
| 193 | ~1 hour | ~4,600 |
| 1,000 | ~5 hours | ~4,600 |
| 10,000 | ~2 days | ~4,600 |
| 50,000 | ~10 days | ~4,600 |

The 50,000 row is why a flat budget alone is not the answer either: RSS returns only the ~15 most recent videos per channel, so a 10-day interval starts losing uploads outright on active channels, not merely delaying them.

**Cadence tiering is the largest single lever, and it costs no infrastructure.** Flat rotation gives a dormant channel the same slot as a daily uploader. Derive an expected upload interval per channel from its own video history and poll proportional to it. A rough 50,000-channel model:

| Tier | Share | Poll every | Requests/day |
| --- | --- | --- | --- |
| Hot, 2+ uploads/week | 10% (5,000) | 2 hours | 60,000 |
| Warm, weekly | 25% (12,500) | 12 hours | 25,000 |
| Cold, monthly | 40% (20,000) | 3 days | 6,700 |
| Dormant, 6 months silent | 25% (12,500) | 7 days | 1,800 |
| **Total** | 50,000 | | **~93,500/day** |

Roughly 13x cheaper than flat 30-minute polling of the same table, while leaving hot channels far fresher than any flat rotation would.

**Demand ordering, not demand-driven fetching.** Polling a channel only when a user loads a feed containing it scales request volume with active attention rather than catalog size, which is a genuinely better scaling property. Fetching *during* the request is not: a feed with 200 stale channels blocks first paint for tens of seconds, which is why [021] removed exactly that behaviour and [022] made feed reads pure DB. Demand is also bursty in the worst possible shape, since a morning login spike is precisely the burst pattern that draws an IP block, whereas a budget smooths by construction.

The synthesis is to keep the budget as the rate governor and let demand drive **priority**: a feed load records want on the channel rows (a cheap non-blocking write, gated by the fetch TTL so repeat loads do not pile up), and the sweep selects `ORDER BY demand DESC, last_fetched_at ASC`. Feed reads stay pure DB, the queue absorbs the spike so the cost of a burst is latency rather than a block, and an empty demand queue means a sweep that costs nothing. A **floor** stays underneath it, polling every channel at least once every N days regardless of demand, so an unviewed channel does not accumulate permanent gaps against the ~15-item RSS cap.

**The Data API is a separate budget, and cheaper than [023] assumes.** `playlistItems.list` against a channel's uploads playlist costs **1 unit** and returns up to 50 videos (confirmed against Google's quota-cost documentation, 2026-08-24). The free 10,000 units/day is therefore ~10,000 channel checks/day, and granted quota increases are commonly 50,000-100,000. Two properties matter more than the volume: the quota is **per project, not per IP**, so it is an entirely independent ceiling with no block risk; and a unit quota tolerates bursts that an IP ceiling does not, which makes it the natural home for demand spikes while RSS carries the floor. The 50,000-channel tiered model above, ~93,500 checks/day, fits inside a single granted quota increase with no proxies and no server fleet.

The audit is the constraint, not the quota: reported waits run from weeks to several months, and denials cluster on vague use cases, missing privacy policies, and anything resembling bulk download. Apply well before the capacity is needed.

**Distributed polling is legitimate but is a later rung.** N owned servers each polling a disjoint channel slice gives N times the per-IP limit and is ordinary horizontal scaling, spreading real load rather than concealing it. Three caveats: it multiplies a ceiling nobody has measured; N droplets from one provider are N addresses in ranges that already score worse than residential and are trivially correlated by ASN, so spread across providers and regions or the gain is well under N; and it carries real operational weight in slice assignment, result shipping, per-node health, and partial-failure semantics.

**Residential proxy rotation is out.** It is evasion rather than architecture, defeating an anti-abuse decision deliberately applied to us by making traffic appear to come from unrelated home users. It is also a bad engineering bet: an arms race against Google's anti-abuse team that breaks without warning, poisons any future relationship with YouTube, and rests on a supply chain that is independently ethically compromised.

**Escalation ladder, in order:**

1. Measure the real per-IP ceiling
2. Cadence tiering plus demand ordering, with a floor underneath
3. Data API `playlistItems.list` for the hot tier, RSS for the cold tier
4. Data API quota increase application, applied for early
5. Distributed polling across owned servers, mixed providers and regions

**Related:** `reference/YOUTUBE_RSS_RATE_LIMITS.md` sections 5, 7, and 8 are the evidence base for all of the above. Section 6 records a platform-wide `videos.xml` outage over 13-17 February 2026, which is the standing argument for holding a second ingestion path that does not touch that endpoint, and which [030] should weigh before deleting WebSub outright.

### Acceptance Criteria

- [ ] The per-IP request ceiling is measured on production and recorded as a number, replacing the unsourced `~1,500-2,000` figure
- [ ] Channel selection is tiered by observed upload cadence rather than treating every channel alike
- [ ] Feed loads raise a channel's poll priority without blocking the request, and feed reads stay pure DB
- [ ] A floor guarantees every channel is polled at least once every N days regardless of demand
- [ ] Outbound request rate stays bounded by a value we set, whatever the channel count or demand spike
- [ ] The 15-item RSS cap never silently drops an upload at the configured tier intervals

---

## [039] Mobile audit: top bar, drop-down nav drawer, touch-friendly feeds

**Status:** `next`
**Depends On:** [009], [012], [015], [017], [018]
**Spec:** none

### Goal

Below 768px, every page is fully usable on a phone held one-handed. A plain, non-sticky top bar carries the logo and site name on the left and a hamburger on the right. Tapping it drops down a drawer with every nav option as simple right-aligned text, close to the thumb. Feed cards expose their actions without hover or right-click. Built after the visual items it depends on so nothing gets restyled twice.

### Scope

- Mobile top bar (below 768px only): logo + "Peristalsis" left, linked to the landing page, hamburger right. Scrolls away with the page, never sticky or fixed
- Drop-down drawer that expands down from the top bar. Reuses the existing nav (main nav, group feeds, user menu) restyled as right-aligned plain text, not a separate mobile-only menu
- Drawer closes three ways: the hamburger turns into an X, tapping outside, and picking a link
- Card actions on touch: watched toggle always visible, plus a small ⋯ button that opens the existing Mark watched / Mark unwatched / Hide menu
- Feed hero on mobile: smaller icon and title, filter buttons drop to their own row underneath
- Audit every page at phone width and fix overflow, cramped tap targets, and clipped controls: feeds, group feeds, Shorts, Subscriptions, Home, settings, and auth (login, register, password reset, 2FA, verify email)
- NOT in scope: desktop and tablet (768px and up) layout, the sticky feed date headers (they stay sticky), reduced-motion handling, a long-press gesture

### Technical Notes

**User flows:**

- **User (phone):** Top bar on any signed-in page. Tap the hamburger to drop down the nav, tap "All Videos", "Shorts", "Subscriptions", a group, or the account menu to go there. The drawer closes on navigation, on tap outside, or on the X.
- **User (phone):** Video card on `/feed` or a group feed. Tap the watched toggle, or tap ⋯ to Mark watched, Mark unwatched, or Hide.
- **Visitor (phone):** `/login`, `/register`, and the other auth screens. Fill in and submit forms without horizontal scrolling or clipped buttons.

**Critical files:**

- `resources/js/components/AppSidebar.vue` - modify. Its `SidebarTrigger` (line 65) is the only open control on mobile and lives inside the sheet it opens, so a closed sheet can never be reopened. This is the "can't reach options" bug.
- `resources/js/layouts/app/AppSidebarLayout.vue` - modify. Mount the mobile top bar and drawer here, above the page slot.
- `resources/js/components/ui/sidebar/SidebarProvider.vue` - entry point. `isMobile` is `(max-width: 768px)` (line 22). Reuse it for the breakpoint rather than adding a second query.
- `resources/js/components/VideoCard.vue` - modify. The watched toggle is `opacity-0 group-hover:opacity-100` (line ~72) and the menu fires on `@contextmenu` only (line 59). iOS Safari never fires contextmenu on long-press.
- `resources/js/pages/Videos/Feed.vue` - modify. Hero row at lines 289-327, card menu at 438-470. Mirror every change in `resources/js/pages/Groups/Show.vue` (menu wired at line 551).

**Details:**

- Mobile nav today renders `Sidebar` as a `Sheet` (`ui/sidebar/Sidebar.vue:33`). The drop-down drawer replaces that sheet below 768px. Desktop keeps the current collapsible sidebar untouched
- Drawer animates down from the bar. Don't add `motion-reduce:` or `prefers-reduced-motion` handling (global rule)
- `NavMain`, `NavGroupFeeds`, `NavUser` feed the drawer content. They will already carry the [009] Cherry active border and [017] group colors by the time this is built. Right-align them in the drawer without forking them
- `AppSidebarHeader` (breadcrumb header) only renders when breadcrumbs exist. Check it doesn't stack awkwardly under the new top bar
- Touch detection for card actions: prefer CSS (`pointer-coarse:` / `@media (hover: none)`) over JS so the toggle and ⋯ button show on phones and stay hover-only on desktop
- Workflow: explore each page with `playwright-cli` at a phone viewport (e.g. 390x844), then fix. Record per-page audit findings and fixes in this section as they land

### Acceptance Criteria

- [ ] Below 768px a non-sticky top bar shows the logo + name on the left and a hamburger on the right. It scrolls away with the page [MANUAL]
- [ ] Tapping the hamburger drops down a drawer listing every nav option, right-aligned [MANUAL]
- [ ] The drawer can be reopened after closing, and closes via the X, tap outside, and link navigation [MANUAL]
- [ ] From inside a feed on a phone, every nav destination is reachable [MANUAL]
- [ ] On a touch viewport, the watched toggle and ⋯ menu are visible on cards, and the menu can mark watched, unwatched, and hide [MANUAL]
- [ ] Feed and group feed heroes stack compactly with filters on their own row on a phone [MANUAL]
- [ ] No audited page (signed-in and auth) scrolls horizontally at phone width [MANUAL]
- [ ] Desktop (768px and up) sidebar and layout are unchanged [MANUAL]
- [ ] Drawer animation feels smooth and the right-aligned text sits within easy thumb reach [MANUAL]

---

## [043] Unsubscribe-select mode on feeds

**Status:** `next`
**Depends On:** [040]
**Spec:** none

### Goal

On any feed, the user turns on an unsubscribe mode, clicks videos to select their channels, then removes them all at once. A confirm modal lists the channels and the feeds each one leaves, and lets the user drop channels from the batch or narrow each one to the current group before confirming.

### Scope

- New icon-only hero toggle (per [040]) on All Videos, group feeds, and Shorts: "Unsubscribe mode"
- In the mode, clicking a video toggles selection of its **channel**, not the video. Every card from a selected channel shows a selected state (Cherry ring + check badge). Clicking does not open the video. Card hover actions and the context menu are off
- A sticky action bar shows the selected channel count, "Clear", and "Remove subscriptions"
- "Remove subscriptions" opens a confirm modal:
  - One row per selected channel: avatar, name, and the groups it belongs to
  - Per-row remove (X) to drop the channel from the batch
  - Per-row scope control on group feeds: "Everywhere" (default) or "This group only". On All Videos and Shorts, scope is always everywhere and the control is hidden
  - Summary of affected feeds (each group that loses at least one channel)
  - Confirm is disabled when the list is empty
- On confirm, one request applies the batch, removed channels' videos leave the feed, cache entries for affected feeds are cleared, and the mode exits
- Leaving the page or turning the mode off clears the selection
- NOT in scope: undo, the Subscriptions page

### Technical Notes

**User flows:**

- **User:** Hero toggle on `/feed`, `/groups/{group}`, `/shorts`. Turn on unsubscribe mode, click videos to select or deselect channels, click "Remove subscriptions".
- **User:** Confirm modal. Remove rows, set each to everywhere or this group, confirm or cancel.

**Critical files:**

- `app/Http/Controllers/SubscriptionController.php` - modify. `destroy` (line 215) detaches one channel from all user groups. Add a bulk action next to it.
- `routes/web.php` - modify. Add bulk `DELETE subscriptions` named `subscriptions.destroy-many`.
- `resources/js/pages/Groups/Show.vue`, `resources/js/pages/Videos/Feed.vue`, `resources/js/pages/Videos/Shorts.vue` - modify. Mode state, card click interception, action bar, modal.
- `resources/js/components/VideoCard.vue` - modify. Add `selectable` and `selected` props. Selected styling, suppress open on click.
- `resources/js/components/ui/dialog/` - pattern to follow for the confirm modal.

**Details:**

- Bulk request body: `items: [{ channel_id, scope: 'everywhere' | 'group', group_id? }]`. Validate in a Form Request: every channel sits in one of the user's groups, `group_id` belongs to the user and is required when `scope = group`. Wrap in a transaction
- Share the detach logic between `destroy` and the bulk action so the two paths do not drift
- The modal needs each selected channel's group names. Ship a per-page `channelGroups` map (`channel_id => [{id, name}]`) once rather than adding groups to every video in the payload
- After success, invalidate the client feed cache (cache-first render from `3f05b59`) for every affected feed
- Extract the mode and modal into a `useUnsubscribeMode` composable and `UnsubscribeConfirmDialog.vue` shared by the three pages

### Acceptance Criteria

- [ ] Bulk endpoint removes each channel everywhere or from one group per its scope
      `tests/Feature/BulkUnsubscribeTest.php::it removes channels by scope`
- [ ] Bulk endpoint rejects channels or groups the user does not own
      `tests/Feature/BulkUnsubscribeTest.php::it rejects foreign channels and groups`
- [ ] Single `DELETE subscriptions/{channel}` behavior is unchanged
      `tests/Feature/BulkUnsubscribeTest.php::it keeps single destroy behavior`
- [ ] In the mode, clicking a video selects its channel and marks every card from that channel. Clicking again deselects [MANUAL]
- [ ] In the mode, clicking a video does not open it [MANUAL]
- [ ] The modal lists channels with their groups, allows removing rows, and disables confirm when empty [MANUAL]
- [ ] On a group feed, a row set to "This group only" leaves the channel in its other groups [MANUAL]
- [ ] After confirm, removed channels' videos leave the feed and the mode exits [MANUAL]

---
