# Roadmap

<!-- Next task number: [044] -->

## [013] Always show the channel ID input in Add form

**Status:** `next`
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

**Status:** `next`
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

**Status:** `next`
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

**Status:** `next`
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

**Status:** `next`
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

**Status:** `next`
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

## [030] Remove WebSub entirely

**Status:** `next`
**Depends On:** [029]

### Goal

WebSub is gone from the codebase. [028] proved YouTube's publisher does not reliably ping its own hub for our feeds: 191 of 193 subscriptions had never delivered, and nothing on our side can fix that. Once [029] makes polling the guaranteed path, every line of WebSub code is vestigial, and vestigial code is a standing invitation to debug a system that was never going to work.

**Must land after [029].** Feed reads are pure DB, and the only scheduled ingestion today is `websub:renew` / `websub:backstop` (`routes/console.php:23-31`). Removing WebSub before the poll sweep exists leaves zero automatic ingestion.

### Scope

- Delete the WebSub controller, services, model, enum, commands, factory, and tests
- Delete the routes, scheduler entries, config block, and env keys
- Drop the `channel_subscriptions` table via a new forward migration
- Remove subscribe-on-add from the subscription flow
- Keep the [028] investigation writeup as the record of why

### Technical Notes

**Reference commit.** `eeee0c4` (`feat: WebSub push ingestion (MVP), archive [021]`) is the first WebSub commit; `c6a1666` is the last commit before it. If push is ever revisited, diff against those rather than resurrecting dead code. Everything worth keeping from the WebSub era, the conditional-GET columns and the pure-DB feed reads, stays.

**Code to delete:**

| Path | Note |
| --- | --- |
| `app/Http/Controllers/WebSubController.php` | Hub verification + delivery callback |
| `app/Services/WebSubSubscriber.php` | |
| `app/Services/WebSubAlerter.php` | Renewal-failure webhook, fired 0 times during the outage |
| `app/Models/ChannelSubscription.php` | |
| `app/Enums/WebSubSubscriptionStatus.php` | |
| `app/Console/Commands/RenewWebSubLeasesCommand.php` | |
| `app/Console/Commands/WebSubBackstopCommand.php` | Redundant once the sweep covers every channel on rotation |
| `app/Console/Commands/SubscribeMissingWebSubCommand.php` | Shipped in [027] |
| `database/factories/ChannelSubscriptionFactory.php` | |
| `tests/Feature/WebSubCallbackTest.php` | |
| `tests/Feature/WebSubRenewalTest.php` | |
| `tests/Feature/WebSubBackstopTest.php` | |
| `tests/Feature/WebSubSubscribeOnAddTest.php` | |
| `tests/Feature/WebSubSubscribeMissingTest.php` | |
| `WEBSUB_LOCAL_SETUP.md` | Local tunnel setup notes |

**Wiring to unpick:** the callback routes in `routes/web.php` and their CSRF exemption in `bootstrap/app.php`; both `Schedule::command('websub:…')` entries in `routes/console.php`; the `websub` block in `config/services.php` (`callback_base`, `alert_webhook`, `backstop_min_silence_hours`, `backstop_min_repoll_hours`, lease and renewal settings) and the matching `.env.example` keys; subscribe-on-add in `SubscriptionController`; WebSub references in `ChannelResolver`, `Channel`, and the "push-driven (WebSub)" comments in the two feed controllers.

**Database.** Add a new forward migration dropping `channel_subscriptions`. Do **not** delete `2026_07_24_000811_create_channel_subscriptions_table.php` or the two later `add_*_to_channel_subscriptions` migrations: rewriting migration history breaks `migrate` against the existing production database. The `channels` conditional-GET migration (`2026_08_23_171925_add_conditional_get_to_channels_table.php`) stays, since [029] depends on it.

**Keep `reference/WEBSUB_DELIVERY_INVESTIGATION.md`.** It is the evidence for the decision, and the reason nobody should try this again without new information from Google.

### Tests

The five `WebSub*Test.php` files are deleted, not rewritten. Verification is that the remaining suite passes and ingestion still works:

- The full Pest suite passes with no WebSub references remaining
- A repo-wide grep for `websub` / `pubsubhubbub` returns only `reference/WEBSUB_DELIVERY_INVESTIGATION.md`, `ROADMAP_DONE.md`, and the `peristalsis/` business docs
- Adding a subscription still succeeds with no subscription side effect
- `channels:poll` from [029] still ingests after the removal

### Acceptance Criteria

- [ ] Every file in the deletion table is gone
- [ ] The WebSub routes, CSRF exemption, scheduler entries, `config/services.php` block, and `.env.example` keys are removed
- [ ] Adding a subscription no longer attempts a hub subscription, and succeeds
- [ ] A new forward migration drops `channel_subscriptions`; the original create and alter migrations are left in place
- [ ] The `channels` conditional-GET columns and the pure-DB feed reads are untouched
- [ ] `reference/WEBSUB_DELIVERY_INVESTIGATION.md` is retained
- [ ] A repo-wide grep for `websub` / `pubsubhubbub` hits only that doc, `ROADMAP_DONE.md`, and `peristalsis/`
- [ ] The full Pest suite passes
- [ ] `channels:poll` still ingests new videos on production after the removal

---

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

## [040] Icon-only feed hero buttons that never overlap the title

**Status:** `next`
**Depends On:** none
**Spec:** none

### Goal

Feed hero toggle buttons never overlap the hero title, on any feed and any title length. The buttons drop their text labels and show icons only, with the explanation moved into a tooltip.

### Scope

- All Videos, group feed, and Shorts heroes
- Remove the text label from every hero button (cap, starred, watched, Manage channels, Shorts toggles). Icon only, square tap target
- Each button gets a `Tooltip` that states the current state and what a click does. Keep `aria-label` and `aria-pressed`
- Title block truncates with an ellipsis when it still runs out of room. Buttons never shrink or overlap
- NOT in scope: mobile hero restacking (owned by [039]), the stats card (owned by [042])

### Technical Notes

**User flows:**

- **User:** Hero buttons on `/feed`, `/groups/{group}`, `/shorts`. Hover a button to read its tooltip, click to toggle.

**Critical files:**

- `resources/js/pages/Groups/Show.vue` - modify. Hero at lines ~427-547. Overlap source: the title `div` is `min-w-0 flex-1` but the `h1` has no `truncate`, so long names render under the buttons.
- `resources/js/pages/Videos/Feed.vue` - modify. Hero buttons at lines ~400-468.
- `resources/js/pages/Videos/Shorts.vue` - modify. Hero buttons at lines ~344-390.
- `resources/js/components/ui/tooltip/` - pattern to follow. Existing shadcn tooltip, already used by `NavMain.vue` and `NavGroupFeeds.vue`.

**Details:**

- The three heroes duplicate the same button markup. Extract one `HeroToggleButton.vue` (icon, pressed state, tooltip text, optional `href`) so the three pages stay in sync
- Button container gets `shrink-0`. Add `truncate` to the `h1`
- Drop the native `title` attributes once the tooltip replaces them

### Acceptance Criteria

- [ ] Hero buttons on all three feeds render icons only, with no text label [MANUAL]
- [ ] Hovering each hero button shows a tooltip describing its state [MANUAL]
- [ ] A group with a 60-character name does not overlap the buttons at 1280px width. The title truncates [MANUAL]
- [ ] Hero buttons keep their toggle behavior and `aria-pressed` state [MANUAL]

---

## [041] Pointer cursor on every link and button, site-wide

**Status:** `next`
**Depends On:** none
**Spec:** none

### Goal

Every clickable element on the site shows the pointer (finger) cursor. A project rule in `CLAUDE.md` keeps new code consistent.

### Scope

- One global base-layer CSS rule in `resources/css/app.css` covering `a[href]`, `button`, `[role="button"]`, `[role="menuitem"]`, `[role="tab"]`, `[role="option"]`, `[role="checkbox"]`, `[role="switch"]`, `summary`, `label[for]`, `select`, and `input` of type `checkbox`, `radio`, `submit`, `button`, `reset`, `file`
- Disabled elements (`:disabled`, `[aria-disabled="true"]`, `[data-disabled]`) use `cursor: not-allowed`
- Clickable non-semantic elements (for example video cards) become `<button>`/`<a>` or carry `role="button"` so the rule catches them
- Add a rule to `CLAUDE.md`: every link and button uses the pointer cursor via the global rule, and custom clickable elements must be a `<button>`, an `<a>`, or carry `role="button"`
- NOT in scope: an `AGENTS.md` file (none exists in the repo)

### Technical Notes

**User flows:**

- **User:** Any page. Hovering any link, button, menu item, toggle, or card shows the pointer cursor.

**Critical files:**

- `resources/css/app.css` - modify. Tailwind 4 preflight sets `button { cursor: default }`, which is the root cause. Add the rule in `@layer base` so utility classes still override it.
- `CLAUDE.md` - modify. Add the cursor rule under a short "UI rules" section.
- `resources/js/components/VideoCard.vue` - check. Confirm the card click target is a link or button.
- `resources/js/components/ui/` - check. shadcn primitives (dropdown items, select, checkbox, sidebar buttons) use `role` attributes the rule must cover.

**Details:**

- Remove redundant `cursor-pointer` utilities only where they add noise. Do not churn every file
- Audit with `playwright-cli`: hover a sample of each control type on `/feed`, `/groups/{group}`, `/shorts`, `/subscriptions`, settings, and auth pages

### Acceptance Criteria

- [ ] Buttons, links, dropdown menu items, checkboxes, and sidebar items compute `cursor: pointer` [MANUAL]
- [ ] Disabled buttons compute `cursor: not-allowed` [MANUAL]
- [ ] `CLAUDE.md` states the pointer cursor rule
- [ ] Hover audit across all pages finds no clickable element without the pointer [MANUAL]

---

## [042] Feed stats card: Most Watched + Channel count, actually populated

**Status:** `next`
**Depends On:** none
**Spec:** none

### Goal

Every feed shows a stats card under the hero with real data. The left 2/3 cell shows the most watched channel by three measures. The right 1/3 cell shows the feed's channel count. The placeholder dashes are gone.

### Scope

- Replace the group feed's Channels / Starred / Last sync card with a 2-cell card
- Add the same card to All Videos and Shorts, scoped to each feed's channels
- **Most watched (2/3 width):** three sub-columns, each with an icon, a short label, the channel avatar and name, and its number:

  | Sub-column | Measure | Number shown |
  | --- | --- | --- |
  | All time | Most videos marked watched | watched count |
  | 30 days | Most watched in the last 30 days | watched count |
  | Ratio | Highest watched / ingested videos, min 5 watched | percent |

- **Channels (1/3 width):** icon + count of channels in the feed, with the starred count as secondary text
- Empty states: a sub-column with no qualifying channel shows "No watches yet"
- Ties break on the most recent watch
- NOT in scope: clickable stats, charts, a last-sync value

### Technical Notes

**User flows:**

- **User:** Stats card on `/feed`, `/groups/{group}`, `/shorts`. Read which channel they watch most and how many channels the feed holds.

**Critical files:**

- `app/Http/Controllers/GroupFeedController.php` - modify. Add a `stats` prop beside `videos` (line ~27).
- `app/Http/Controllers/AllVideosFeedController.php` - modify. Same, using `$subscribedChannelIds`.
- `app/Http/Controllers/ShortsFeedController.php` - modify. Same, Shorts-only videos.
- `app/Models/UserVideoState.php` - entry point. `state = 'watched'` rows keyed by `youtube_video_id`. Use `updated_at` as the watch time for the 30-day window.
- `resources/js/pages/Groups/Show.vue` - modify. Placeholder card at lines ~549-590.
- `resources/js/pages/Videos/Feed.vue`, `resources/js/pages/Videos/Shorts.vue` - modify. Add the card under the hero.

**Details:**

- Put the three queries in one service (for example `App\Services\FeedStats`) taking a user and a channel-id set, returning `{ channel_count, starred_count, most_watched: { all_time, last_30_days, ratio } }`
- Join `user_video_states` to `videos` on `youtube_video_id`, group by `channel_id`. One query per measure, no N+1
- Serve `stats` as `Inertia::defer` in its own group so it does not block the video payload
- Extract the card into `FeedStatsCard.vue` shared by the three pages. Heroicons: `EyeIcon` for all time, `CalendarDaysIcon` for 30 days, `ChartPieIcon` for ratio, `RectangleStackIcon` for channels
- Channel names truncate inside their sub-column

### Acceptance Criteria

- [ ] Group, All Videos, and Shorts feeds return a `stats` prop with channel count, starred count, and the three most-watched results
      `tests/Feature/FeedStatsTest.php::it returns stats on each feed`
- [ ] All time picks the channel with the most watched videos in the feed, ties broken by latest watch
      `tests/Feature/FeedStatsTest.php::it picks the all time most watched channel`
- [ ] 30 days ignores watches older than 30 days
      `tests/Feature/FeedStatsTest.php::it limits the 30 day measure`
- [ ] Ratio skips channels under 5 watched videos
      `tests/Feature/FeedStatsTest.php::it requires a minimum for ratio`
- [ ] Stats count only channels in the current feed and the current user's watches
      `tests/Feature/FeedStatsTest.php::it scopes stats to the feed and user`
- [ ] The card renders 2/3 most watched and 1/3 channels with icons, and no dash placeholders [MANUAL]

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
