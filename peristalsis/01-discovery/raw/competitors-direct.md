# Direct / Near-Direct Competitors — Peristalsis

**Research date:** 2026-07-23
**Method:** WebSearch, 6+ query rounds (product sites, Chrome/Firefox stores, App Store, MacStories, HN, Reddit-sourced review mining, pricing comparison blogs).
**Tier key:** Tier 1 = primary source (vendor site, app store, founder statement). Tier 2 = reputable press/review (MacStories, PopSci, PiunikaWeb). Tier 3 = aggregators/comparison blogs/SEO content (SaaSHub, Readless, keep.md, howtofixpro).

> Peristalsis definition for scoring: RSS/WebSub-powered *standalone* alt subscription feed; group/favorite/filter YouTube subs; watch **on YouTube** (companion). Freemium free/$5/$25.

---

## The competitive landscape (3 buckets)

1. **Overlay extensions** — patch youtube.com in place (PocketTube, Unhook, FolderTube). Fragile to YT UI changes.
2. **Standalone alt feeds** — separate surface you open instead of YouTube (Feedvault, FocusedFeed, Play, newsagent.fyi). **← Peristalsis lives here.**
3. **General RSS readers w/ YouTube support** — Feedly, Inoreader, NewsBlur. YouTube is a side feature.
4. **Privacy alt-clients** — FreeTube, NewPipe, Invidious, Piped, LibreTube. Replace YouTube entirely (not companion).

---

## 1. PocketTube — the incumbent overlay
- **Product:** Browser extension + mobile apps. Organizes subs into groups/tags/collections (one channel → many groups), custom icons, per-group feed, filter/sort by length/date/type, mark-as-watched, dead-channel detection, multi-unsubscribe, AI auto-tags, notifications per group, "Video Deck" view. Integrates *inside* the YouTube layout. [Tier 1: pockettube.io; Chrome/Firefox store, 2026-07-05]
- **Platform:** Chrome, Firefox, Android, iOS. Overlay-based (not standalone).
- **Pricing:** Freemium. Premium ~$3–3.99/mo via Patreon; unlocks nested groups, unlimited tags, priority support. [Tier 3: SaaSHub; Tier 1: Firefox listing Patreon link]
- **Traction:** "Trusted by 300,000+ users" (vendor claim). Firefox rating 4.1★. Chrome install base large but exact number not surfaced. Actively updated (v18.7.1, updated within hours of 2026-07-05). [Tier 1]
- **Model:** Solo/small-team, Patreon-funded freemium. Bootstrapped.
- **Strengths:** Mature, feature-rich, largest install base, cross-platform, cheap, seamless in-YT integration, active dev.
- **Weaknesses (mined reviews):** Breaks on YouTube UI updates (recurring complaint); filter buttons stop working with many channels ("always shows ALL"); groups fail to load/require repeated reloads; only shows month-old videos; Google Drive sync disconnects (conflicts w/ uBlock/Stylus); slows YouTube dramatically; "hides collab videos"; "lots of upsells, UI is a mess, not intuitive"; unsubscribe doesn't remove from group. [Tier 1: Firefox reviews, 2026-07]
- **Grouping/favoriting/filtering?** YES — full. This is the feature-parity benchmark.

## 2. Feedvault (getfeedvault.com) — closest philosophical twin
- **Product:** **Standalone web app** (explicitly "not an extension"). Chronological subscription feed, group channels into topic "Feeds," Shorts blocked *at ingestion* (never fetched), no recommendations. "Studies" = save videos w/ free-text notes + timestamp (library-building). RSS-first ingestion under the hood. [Tier 1: getfeedvault.com]
- **Platform:** Web (mobile + desktop, no install). Watch on YouTube.
- **Pricing:** No monthly. 6mo $59 / 1yr $99 / lifetime $499. 7-day trial, 30-day refund. [Tier 1]
- **Traction:** **Essentially pre-launch / near-zero.** Founder on HN (item 47575797) ran a *pre-sale*: "15 backers and I ship it." Took it offline earlier because API costs didn't justify one user. Solo founder, YouTube Data API cost problem explicitly flagged. [Tier 1: HN launch thread]
- **Model:** Solo founder, one-time/lifetime pricing, bootstrapped, self-funding via presale.
- **Strengths:** Cleanest positioning ("subscription feed YouTube stopped giving you"), standalone = survives YT UI changes, Studies/notes is a genuine differentiator, Shorts-at-ingestion.
- **Weaknesses:** No traction, pricing widely mocked on HN ("ridiculous," "more interested in money than problem"), not open source (HN wanted self-host), runs on **YouTube Data API (quota + per-user cost)** not pure RSS → scaling economics risk. No free tier (trial only).
- **Grouping/favoriting/filtering?** YES grouping + save/notes. Less granular *filtering* than PocketTube.

## 3. Play (Marcos Tanaka / Loop Apps) — the polished Apple-native one
- **Product:** Watch-later + YouTube channel following. Chronological algo-free sub feed → inbox, swipe to queue/dismiss. Folders for channels, per-channel filters, hide Shorts. Transcribe/summarize/AI, notes/annotations. Supports YouTube, Vimeo, Nebula. [Tier 1: App Store; Tier 2: MacStories, PopSci]
- **Platform:** Apple only — iPhone/iPad/Mac/Apple TV/Vision Pro. iCloud sync. **No Android/Windows.**
- **Pricing:** $2.99 one-time (Basic) + Play Premium $2.99/mo / $19.99/yr / $99.99 lifetime (Channels, folders, per-channel filters, transcripts, AI gated here). 7-day trial. [Tier 1/2]
- **Traction:** 4.8★, 491 ratings (US App Store). Critically acclaimed (The Verge, Daring Fireball, MacStories Selects 2024). [Tier 1/2]
- **Model:** Established indie dev (portfolio: MusicHarbor, MusicBox), subscription + one-time. Sustainable.
- **Strengths:** Best-in-class polish/UX, strong press, multi-source (Nebula/Vimeo), in-app watch, AI, loyal Apple audience.
- **Weaknesses:** Apple-exclusive (huge TAM gap — no web/Android/Windows), watch-later heritage means it's a queue app first, feed second; grouping is folders (flat).
- **Grouping/favoriting/filtering?** YES — folders + per-channel filters + hide Shorts.

## 4. FocusedFeed (focusedfeed.app) — mobile-first grouped feed
- **Product:** Sign in w/ Google, auto-imports subs. Group into topic categories (drag/drop, reorderable), latest-uploads-by-category, no recs/autoplay. AI summaries. Chrome extension filters YT homepage by category. Watch on YouTube. Subs stay on YouTube. [Tier 1: focusedfeed.app]
- **Platform:** iOS app (free on App Store) + web + Chrome extension. Phone-first.
- **Pricing:** Free ("no account required to browse"). Monetization unclear (AI summaries likely future paid). [Tier 1]
- **Traction:** No numbers surfaced. Data gap. Appears early-stage.
- **Model:** Unknown/unclear. Likely solo/indie.
- **Strengths:** Mobile-first, clean grouping w/ drag-reorder, free, hybrid app+extension, uses your real subs.
- **Weaknesses:** Google-login dependency (uses YT API not pure RSS → quota risk), no clear business model, unproven traction, thin filtering vs PocketTube.
- **Grouping/favoriting/filtering?** YES grouping + reorder. Filtering lighter.

## 5. Inoreader — RSS reader w/ real YouTube sync
- **Product:** Full RSS reader. **Syncs YouTube subscriptions** (auto folder, adds/removes as you sub/unsub on YT), Shorts filter, live/duration icons, folders/rules/filters. YouTube is one of many source types. [Tier 1: inoreader.com blog 2026-01; pricing page]
- **Platform:** Web, iOS, Android.
- **Pricing:** Free (150 feeds, no YT sync). Supporter $4.99/mo. **YouTube sync + filters require Pro $7.50/mo annual ($90/yr) / $9.99 monthly.** [Tier 1/3]
- **Traction:** Large established RSS player (exact users not surfaced; among top 2 RSS readers w/ Feedly). [Tier 3]
- **Model:** Mature SaaS, profitable, add-on based.
- **Strengths:** True YT sub sync (auto), powerful filtering/rules, cross-platform, includes AI, established/trusted, most feature-rich free RSS tier.
- **Weaknesses:** YouTube gated behind Pro; RSS-reader mental model (not YouTube-native UX); overkill/generalist; watch experience is a link-out, no video-centric UI.
- **Grouping/favoriting/filtering?** YES via folders + rules (generalist, powerful but not YT-tailored).

## 6. Feedly — biggest RSS reader, weakest YT focus
- **Product:** Largest RSS reader. Supports YouTube channel feeds (add by URL). Pivoted toward monitoring/threat/market intelligence for teams. [Tier 3: keep.md, Readless]
- **Platform:** Web, iOS, Android.
- **Pricing:** Free (100 sources / 3 feeds). Pro $6.99/mo (or ~$5/mo annual). Pro+ $12.99/mo (Leo AI, boards). Enterprise custom. [Tier 3]
- **Traction:** Largest RSS reader by userbase (claim). Focus drifting to enterprise/analysts. [Tier 3]
- **Model:** VC-backed SaaS, enterprise-tilted.
- **Strengths:** Brand, scale, clean UI, AI (Leo).
- **Weaknesses:** YouTube is an afterthought (no sub sync like Inoreader), AI gated to Pro+, consumer YT power-user is not the target anymore. Generic.
- **Grouping/favoriting/filtering?** Partial — folders yes; no YT-specific grouping/Shorts filtering surfaced.

## 7. NewsBlur — cheapest RSS reader, YouTube supported
- **Product:** Open-source RSS reader w/ "intelligence training" (per-story filtering). Subscribe to YouTube channels ("nicer than notifications"). River-of-news, self-hostable. [Tier 1: newsblur.com]
- **Platform:** Web, iOS, Android. Self-host (MIT).
- **Pricing:** Free (64 feeds). Premium $36/yr (~$3/mo, 1,024 feeds). Premium Archive $99/yr (AI, archive). Premium Pro $29/mo. [Tier 1]
- **Traction:** Long-running (pre-Google-Reader-shutdown), solo-run by Samuel Clay; modest but durable. Exact users not surfaced. [Tier 3]
- **Model:** Indie, subscription + self-host, sustainable/profitable.
- **Strengths:** Cheapest paid RSS, self-hostable, training/filters, durable, YouTube supported.
- **Weaknesses:** RSS-reader UX (not YT-native), YouTube secondary, dated feel, no grouping tuned for YT, no video watch UX.
- **Grouping/favoriting/filtering?** Partial — folders + trainable filters (generalist).

## 8. Privacy alt-clients (FreeTube / NewPipe / Invidious / Piped / LibreTube) — NOT companion
- **Product:** Full YouTube *replacements* — watch in-app, block ads, local subs, SponsorBlock. Not "watch on YouTube." [Tier 2: PrivacyTools.io; Tier 3: dev.to, sumguy.com]
- **Platform:** FreeTube (desktop), NewPipe/LibreTube (Android), Invidious/Piped (self-host web).
- **Pricing:** Free / open-source / donation.
- **Traction:** Large communities (Invidious 18.7K GitHub stars) but **degrading hard** — YouTube's aggressive blocking. Public Invidious instances collapsed dozens → 3; Piped 15. [Tier 3, 2026-03]
- **Model:** OSS/donation.
- **Strengths:** Free, private, ad-free, no Google account.
- **Weaknesses:** **Constantly breaking** (recurring feed failures — NewPipe issues #13512/#13529, May 2026; "YouTube changed something again"; SABR enforcement; 360p caps). Self-host burden. Not a companion product. Legal/ToS grey area.
- **Grouping/favoriting/filtering?** Basic subs, limited grouping. Different value prop (privacy/replacement, not organization/companion).

**Near-direct honorable mention — newsagent.fyi:** Waitlist-stage. RSS + YouTube + podcasts + Bluesky + Reddit in one chronological feed. £4/mo, £40/yr, £150 lifetime. Multi-source aggregator, not YT-focused. Unproven. [Tier 1]

---

## Comparison Matrix

| Name | Product | Platform | Pricing | Traction | Model | Key Strength | Key Weakness | Grouping/Filter? |
|---|---|---|---|---|---|---|---|---|
| **PocketTube** | In-YT overlay subs organizer | Chrome/FF/iOS/Android | Free / ~$3.99mo | 300k+ users (claim), 4.1★ | Patreon freemium, solo | Mature, largest base, cheap | Breaks on YT updates; filters fail at scale; UI mess | YES (full) |
| **Feedvault** | Standalone chrono feed + notes | Web | $59/6mo, $99/yr, $499 life | ~Pre-launch (presale) | Solo, one-time, bootstrap | Cleanest positioning, Studies/notes | Zero traction, pricing mocked, YT API cost risk | YES (group + notes) |
| **Play** | Watch-later + chrono feed | Apple only | $2.99 + $2.99mo/$19.99yr | 4.8★ 491 ratings, big press | Indie sub, sustainable | Best polish/UX, press, multi-source | Apple-only (no web/Android) | YES (folders + per-ch) |
| **FocusedFeed** | Grouped topic feed | iOS/web/Chrome ext | Free | Unknown (early) | Unclear/indie | Mobile-first grouping, free | No biz model, YT API dep, unproven | YES (group + reorder) |
| **Inoreader** | RSS reader + YT sub sync | Web/iOS/Android | Free / Pro $7.50mo | Top-2 RSS reader | Mature SaaS | True auto YT sub sync + rules | YT gated to Pro; generalist UX | YES (folders/rules) |
| **Feedly** | RSS reader (YT side feature) | Web/iOS/Android | Free / $6.99mo / $12.99 | Largest RSS reader | VC SaaS, enterprise tilt | Brand, scale, AI | YT afterthought, no sub sync | Partial |
| **NewsBlur** | RSS reader (YT supported) | Web/iOS/Android/self-host | Free / $36yr / $99yr | Modest, durable | Indie sub + OSS | Cheapest, self-host, filters | RSS UX, YT secondary, dated | Partial |
| **Alt-clients** | Full YT replacement | Desktop/Android/self-host | Free/OSS | Large but degrading | OSS/donation | Free, private, ad-free | Constantly breaking; not companion | Basic |

---

## Positioning gaps Peristalsis could own

1. **Standalone + RSS/WebSub (not YouTube Data API).** Feedvault & FocusedFeed rely on the YouTube Data API → quota/per-user cost that killed Feedvault's solo economics. **RSS/WebSub ingestion has near-zero marginal cost and no quota** → Peristalsis can sustainably offer a real free tier where Feedvault can't. This is a structural moat vs the closest twins.
2. **Cross-platform web + companion.** Play is the polished leader but **Apple-only**. No standalone, well-designed, *web-based* grouped feed with real traction exists. Web = every device, no install (unlike PocketTube's fragile overlay).
3. **Survives YouTube UI changes.** Every overlay (PocketTube, Unhook, FolderTube) breaks repeatedly — the #1 review complaint. Standalone RSS-based feed is immune. Lead the messaging with "never breaks when YouTube changes."
4. **Companion, not replacement.** Alt-clients (NewPipe etc.) fight YouTube and lose (blocking, SABR). Peristalsis sends users *to* YouTube → no ToS war, no playback breakage, keeps creator monetization intact.
5. **Sane free tier + fair pricing.** Feedvault ($99/yr, $499 life) got mocked on HN. PocketTube ~$3.99. Peristalsis free/$5/$25 needs the free tier to be genuinely useful (grouping) with $5 as the natural upgrade. **The $25 tier has no obvious analog** — must be justified (many channels? teams? API/power features?) or it looks arbitrary.
6. **Timing tailwind:** YouTube actively degrading the native sub feed in 2026 — "Most relevant" block on top, list view + flow=2 removed, iOS scroll bug, Shorts interleaving. Active, dated, documented user anger (PiunikaWeb Feb 2026, multiple Reddit threads). The wedge is real and worsening.

## Threat assessment (most → least)
1. **PocketTube** — incumbent, 300k+ base, cheap, feature-complete grouping. But structurally fragile (overlay).
2. **Play** — best product & brand, but Apple-only leaves web/Android wide open.
3. **Feedvault** — closest positioning twin & messaging; low threat *now* (no traction, bad economics) but validates the exact concept — watch it.
4. **Inoreader** — credible if a power user already lives in RSS; auto YT sub sync is strong.

## Data Gaps (flag — not fabricated)
- **Actual paying-user / revenue numbers:** none public for any competitor. PocketTube's "300k+" is unverified vendor claim; no DAU/MAU anywhere.
- **PocketTube Chrome install count / rating count:** not surfaced (Firefox 4.1★ only).
- **FocusedFeed:** business model, traction, team size — all unknown.
- **Feedly/Inoreader/NewsBlur total user counts:** only relative ("largest," "top-2"), no hard numbers.
- **Conversion rates / free→paid** for any freemium competitor: unknown → can't benchmark Peristalsis funnel.
- **Feedvault post-presale status:** whether it hit 15 backers / actually shipped — unconfirmed as of research date.
- **Reddit primary threads:** review sentiment mined via aggregated store reviews + PiunikaWeb's Reddit summaries, not direct Reddit API pulls. Direct r/youtube thread quotes would strengthen weakness claims.
