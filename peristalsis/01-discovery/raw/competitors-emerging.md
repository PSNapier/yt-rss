# Emerging / Stealth Competitors — YouTube Subscription Feed Space

**Research date:** 2026-07-23
**Analyst goal:** Surface new/recent/upcoming products competing with Peristalsis (freemium SaaS, RSS/WebSub YouTube subscription feed — group/favorite/filter subs, watch on YouTube). Solo founder, target = YouTube power users.

**Method:** 6 WebSearch queries across Product Hunt / Show HN / Reddit / GitHub / awesome-lists / RSS-reader changelogs. Sources tiered:
- **Tier 1** = primary/verifiable (GitHub repo metadata, official changelog, HN thread, Chrome Web Store).
- **Tier 2** = reputable secondary (XDA article, unsubbed.co profile).
- **Tier 3** = aggregators/SEO blogs (easytool.me, myext.info).

> Note: dates/star counts captured from search snapshots on 2026-07-23. Traction numbers are point-in-time and should be re-verified before decisions.

---

## Emerging Products

### 1. YT Zero (Pelski/ytzero) — self-hosted
- **What:** Self-hosted "YouTube inbox." Reads public YouTube RSS feeds, stores locally in SQLite. Chronological subscription inbox, no Google account, no API key. Tags + auto-rules, watch-later buckets (Today/Tonight/Weekend), archive flow, watch progress/history, OPML + Google Takeout import, live/upcoming stream view, SponsorBlock, theater player, OIDC/passkey auth.
- **When launched:** Repo created **2026-06-13**. Very recent (≈6 weeks old at research date).
- **Platform:** Self-hosted (Docker / Bun). AGPL-3.0.
- **Traction:** **74 stars, 4 forks, 74 watchers, 3 open issues** (Tier 1, GitHub). Featured in XDA Developers article (Tier 2). Active: huge refactor commit 2026-07-21 (+2326/−1652 across 60 files), added a DESIGN_SYSTEM.md.
- **Status:** **ACTIVE / rising.** Most feature-complete and most-hyped of the newcomers.
- Source: github.com/Pelski/ytzero (T1); xda-developers.com self-hosted YouTube frontend article (T2). NOTE a **fork** matt-laird/ytzero also exists.

### 2. TubeShelf (samumatic/tubeshelf) — self-hosted
- **What:** Self-hosted chronological subscription feed, no Google account. Multiple subscription lists w/ tags + custom lists, watch tracking, hide unwatched, built-in player w/ progress, watch-later + history, full OPML import/export, OIDC user management. Explicitly positions as NOT a full YouTube frontend (contrast w/ Invidious) — single-purpose feed. Click video → opens on YouTube (same "watch on YouTube" model as Peristalsis).
- **When launched:** Repo created **2025-12-10**. Latest release v1.3.1 (2026-02-19); last push 2026-07-19.
- **Platform:** Self-hosted, TypeScript. AGPL-3.0. Homepage tubeshelf.dev.
- **Traction:** **6 stars, 0 forks, 1 open issue, 2 contributors** (1 is renovate bot). 10 releases though. Self-labeled "early development, expect bugs."
- **Status:** **ACTIVE but tiny.** Maintained (recent pushes/releases) but negligible adoption.
- Source: github.com/samumatic/tubeshelf (T1).

### 3. Feedvault / FeedVault (getfeedvault.com) — commercial web SaaS  ⚠ closest to Peristalsis
- **What:** Standalone hosted web app (not an extension). Group channels into topic **Feeds**, chronological, Shorts stripped **at ingestion** (never fetched), recommendations removed. Plus a **"Studies"** feature — save videos with free-text notes + timestamps (a knowledge-base/learning angle no one else has). Explicitly benchmarks itself vs Unhook / PocketTube / FolderTube. Mobile via web, no extension.
- **When launched:** Show HN posted ~2026 (HN item 47575797). Founder pre-selling: "15 backers and I ship it."
- **Platform:** Web SaaS. Closed source. Stack: Vite + Tailwind, Cloudflare hosting. **Runs on official YouTube Data API** (not RSS).
- **Traction:** **Very low.** HN post = **2 points, 2 comments**. Comments hostile: pushback on pricing ("ridiculous"), demand for open-source/self-host, "won't pay nor hand over my history." Founder admits took prior version offline because API costs didn't justify one user.
- **Status:** **PRE-LAUNCH / stalled.** Funding-gated ("presale"). Not yet shipped publicly at scale.
- Source: getfeedvault.com (T1); news.ycombinator.com/item?id=47575797 (T1).
- **Why it matters most:** Same positioning as Peristalsis (hosted SaaS, group into topic feeds, watch on YouTube, freemium-ish). BUT chose YouTube Data API (per-user cost) instead of RSS/WebSub — this is exactly the cost trap Peristalsis's RSS/WebSub approach avoids. Weak reception + cost problems = opportunity.

### 4. YTRSS 2.0 (coffe/ytrss2) — terminal/TUI
- **What:** Minimalist terminal RSS client for YouTube subs. Async fetch 50+ feeds, fuzzy search/filter, watch-later, Shorts toggle, local + private, single binary.
- **When launched:** Repo created **2025-12-23**.
- **Platform:** CLI/TUI, single binary. (Actual code hosted in `quicktube2` repo.)
- **Traction:** **9 stars, 9 watchers, 0 forks.**
- **Status:** ACTIVE-ish, niche. Terminal audience only — not competing for mainstream power users.
- Source: github.com/coffe/ytrss2 (T1).

### 5. YouRSS (essembeh/YouRSS) — self-hosted, minimal
- **What:** Minimal self-hosted YouTube RSS viewer. Add channels via URL (`/@handle1,@handle2`), no account, mark-as-read, light/dark, per-user pages, exposes raw RSS. Shows last 15 videos/channel.
- **When launched:** Not dated in results (older/established minimal project).
- **Platform:** Self-hosted web.
- **Traction:** Stars not surfaced (older repo, appears low/modest).
- **Status:** Maintained minimal tool. Low threat.
- Source: github.com/essembeh/YouRSS (T1). Data gap on stars/dates.

### 6. UnTube (tomfriart/untube) — self-hosted downloader+viewer
- **What:** Docker app that **downloads** videos from followed channels (yt-dlp) for ad-free local playback. Auto-download scheduler, per-channel quality, skip Shorts, auto-delete old, HLS player, feed view newest-first, push notifications.
- **Platform:** Self-hosted Docker (Python/Flask + React).
- **Traction:** Stars not surfaced.
- **Status:** Active but **different category** — archival/download (Tube Archivist-adjacent), not "watch on YouTube." Fragile (yt-dlp breaks every few weeks per own README).
- Source: github.com/tomfriart/untube (T1).

### 7. Youlag (civilblur/Youlag) — FreshRSS extension
- **What:** Extension for FreshRSS giving YouTube feeds a video-centric layout (thumbnails, fullscreen, miniplayer, chapters, mobile swipe). Turns existing RSS reader into a YouTube viewer. No Google account.
- **Platform:** FreshRSS plugin (PHP/JS/SCSS). GPL-3, solo dev.
- **Traction:** **545 stars** (highest of the indie set) — but rides FreshRSS's existing base; niche.
- **Status:** ACTIVE. Adjacent — requires you already self-host FreshRSS.
- Source: unsubbed.co/tools/youlag (T2).

### 8. FolderTube — browser extension  (adjacent, overlay)
- **What:** Chrome extension. Organize subs into custom folders/subfolders, drag-drop, filter feed by folder, mark watched, hide Shorts. Freemium (free tier ~5 folders / 10 videos-day; premium unlimited).
- **When launched:** Listed/updated **June–July 2026** (Chrome Web Store update 2026-07-05). Newer than PocketTube.
- **Platform:** Chrome extension (overlay on YouTube UI).
- **Traction:** Not surfaced precisely; newer/smaller than PocketTube.
- **Status:** ACTIVE, growing. But **overlay model** = breaks on YouTube UI changes (the exact fragility Peristalsis/Feedvault avoid).
- Source: Chrome Web Store (T1); myext.info (T3).

### Incumbents for context (NOT emerging, but adding YouTube features recently)
- **Inoreader** — YouTube channel/playlist follow + **full YouTube subscription sync** (auto import/sync add+remove), Shorts filter, live/Shorts icons, durations. Pro-tier gated. Blog post **2026-01**. (T1)
- **NewsBlur** — New "Add + Discover Sites" page **2026-03-04** with 2,000+ verified YouTube channels as RSS, category browse, "Try" preview. (T1)
- **Feedly** — Follow YouTube channels as sources (older, doc last updated 2023; notes YouTube feed reliability issues). (T1)
- **PocketTube** — Established extension, **323 GitHub stars**, 300,000+ Chrome users, mobile apps, Drive sync. Still updated (Store update 2026-06-13). The incumbent overlay tool. (T1)
- **Open RSS** — third-party service generating reliable YouTube RSS feeds w/ Shorts filter. (T3)

---

## Momentum Assessment

- **The space is heating up in 2024–2026, heavily on the self-hosted / open-source side.** At least 5 net-new indie repos created within ~8 months (TubeShelf Dec-2025, YTRSS2 Dec-2025, YT Zero Jun-2026), plus a new extension (FolderTube) and a pre-launch SaaS (Feedvault).
- **Clear breakout:** **YT Zero** — 74★ in ~6 weeks + press (XDA) + aggressive dev cadence + already spawned a fork. It is the momentum leader and defines the current "no-Google-account, RSS-based, chronological inbox" pattern.
- **Everything else is low-traction:** TubeShelf 6★, YTRSS2 9★, Feedvault 2 HN points. Youlag (545★) is higher but tethered to FreshRSS.
- **Incumbent RSS readers (Inoreader, NewsBlur) are actively investing in YouTube-native UX in 2026** — this is the more strategically significant momentum than any single indie repo, because they own distribution.
- **Dominant pattern = self-hosted + public RSS + no Google account + "watch on YouTube."** Peristalsis's technical approach (RSS/WebSub, watch on YouTube) is validated by the field — but so is the risk that it's becoming table stakes among free/OSS tools.

---

## Threat Level to Peristalsis

**Overall: MODERATE — no single newcomer is an existential threat today, but the category is crowding fast and the freemium-hosted lane has a direct rival.**

| Competitor | Threat | Why |
|---|---|---|
| **Feedvault** | **HIGH (direct)** but weak execution | Same model as Peristalsis (hosted SaaS, topic feeds, watch on YouTube, freemium). BUT: pre-launch/stalled, hostile HN reception, chose costly YouTube Data API (Peristalsis's RSS/WebSub is cheaper/more scalable), no self-host = users pushing back. Beatable. |
| **YT Zero** | **MEDIUM-HIGH (indirect)** | Momentum leader, feature-rich, press. But **self-hosted only** — targets technical DIY users, not the freemium mainstream. Sets a high free/OSS feature bar Peristalsis's paid tier must clear. |
| **Inoreader / NewsBlur** | **MEDIUM** | Own distribution + polished YouTube sync + huge existing user base. If they nail YouTube-power-user UX, they subsume the niche. But YouTube is one feature among many for them, not the core product. |
| **TubeShelf / YTRSS2 / YouRSS / UnTube** | **LOW** | Tiny traction, niche audiences (early-stage / terminal / download-archival / minimal). |
| **PocketTube / FolderTube** | **LOW-MEDIUM** | Big install base (PocketTube 300k+), but **overlay/extension model** = fragile on YT UI changes, keeps user inside YouTube. Different value prop. |

**Key structural advantages that reduce threat to Peristalsis:**
1. RSS/**WebSub** (push) vs API-polling → lower cost + near-real-time. Feedvault's API-cost complaint is the proof point.
2. Hosted freemium fills the gap between "self-host it yourself" (YT Zero/TubeShelf) and "closed pre-sale SaaS" (Feedvault).
3. "Watch on YouTube" (not download/archive) keeps it ToS-safer than UnTube/Tube Archivist.

---

## Whitespace Still Open

- **Zero-setup hosted freemium for non-technical power users.** The strongest products (YT Zero, TubeShelf) are self-hosted — a wall for mainstream users. Feedvault tried hosted but stalled on cost/pricing/trust. **This lane is effectively open.**
- **Cheap, scalable ingestion via WebSub/RSS** while offering a hosted multi-user product. No hosted competitor is publicly doing WebSub-push at scale; Feedvault explicitly hit the API-cost wall.
- **Grouping + filtering as first-class, cross-device, without an extension.** Overlay tools (PocketTube/FolderTube) do grouping but break on UI changes and are desktop-browser-bound. A standalone responsive web app owns this.
- **Trust / privacy positioning.** HN commenters distrust closed SaaS that "wants my history." A freemium tool that reads only public RSS (no Google login, no watch-history harvesting) can market on this directly.
- **Contested (NOT open):** basic chronological + no-Shorts + no-algorithm feed is now commodity (every tool has it). Peristalsis must differentiate on grouping/filtering depth, cross-device hosted convenience, or a knowledge layer (cf. Feedvault "Studies" — an idea worth noting).

---

## Data Gaps

- **Star/date data missing** for YouRSS and UnTube (traction not surfaced) — verify on GitHub before ranking.
- **No Product Hunt launch data** confirmed directly — searches surfaced GitHub/HN, not a PH listing. Should check producthunt.com directly for 2025–2026 "YouTube feed" launches.
- **Feedvault current status unknown** — is the presale funded / did it ship? HN thread was tiny and recent; monitor getfeedvault.com.
- **FolderTube exact user count / reviews** not captured (only "newer than PocketTube").
- **Reddit** not directly sampled (r/selfhosted, r/youtube, r/rss threads) — likely richer signal on real user demand; recommend a dedicated pass.
- **Traction = point-in-time snapshots (2026-07-23).** Star counts for fast-movers (YT Zero) will drift; re-check before any strategic decision.
- **No pricing data** captured for any commercial competitor beyond "Feedvault presale" and "FolderTube freemium" — needed for positioning Peristalsis's paywall.
- Some sources are **Tier 3 SEO/aggregator** (easytool.me, myext.info) — treat their claims as leads, not facts.
