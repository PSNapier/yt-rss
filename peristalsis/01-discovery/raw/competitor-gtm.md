# Competitor Go-To-Market Research: How Comparable Tools Acquired Users

**Focus:** How indie/prosumer + YouTube-enhancer tools get users on a $0 marketing budget.
**Startup context:** Peristalsis — freemium RSS/WebSub-based YouTube subscription feed alternative. Solo founder, near-zero budget, organic/content/community only.
**Date compiled:** 2026-07-23
**Method:** 7 WebSearch queries. Sources tagged by Tier (T1 = primary/original source e.g. founder post, HN thread, official docs, first-party stats; T2 = reputable secondary journalism/analytics; T3 = SEO blogs/aggregators, treat as directional only).

---

## Source Ledger

| # | Source | Tier | Date | Use |
|---|--------|------|------|-----|
| S1 | unhookextension.com (first-party) | T1 | undated (accessed 2026-07) | Unhook = 500K users, word-of-mouth + community advocacy, "free forever, no account" |
| S2 | RedPulse subreddit stats r/newpipe | T2/T3 | 2026-07-19 | r/newpipe ~30,338 subs; audience = tech/privacy; promo downvoted, authenticity key |
| S3 | github.com/FreeTubeApp/FreeTube | T1 | created 2018, accessed 2026-07 | FreeTube 21,248 GitHub stars; feature set incl. subscription "Profiles" |
| S4 | garyghost blog (FreeTube founder history, quoting founder) | T3 (quotes T1) | 2025-04 | FreeTube grew via posting the project online + community stepping in during Invidious transition |
| S5 | Yahoo/Business Insider "How Feedly got 15M users" | T2 | 2013 | Feedly 1M→15M via Google Reader shutdown, one-click migration |
| S6 | FiguringOutWithAI growth teardown | T3 | undated | "[X] alternative" SEO page must exist BEFORE shutdown; top 3 results = 70% traffic; congregate where displaced users are |
| S7 | VentureBeat | T2 | 2013 | Feedly 4M→12M in 3 months post-announcement; one-click importer |
| S8 | Computerworld | T2 | 2013 | 500K users in 48h; new-user forum + migration tips page |
| S9 | Fueler.io Readwise stats | T3 | 2026 | Readwise ~4M users, ~$14M ARR, no VC; growth via creator partnerships, API ecosystem, product updates (Reader launch = +50% DAU/6mo) |
| S10 | HN "Show HN: FeedMe" #43557024 | T1 | ~2024 | Solo dev RSS reader; only **3 points, 2 comments** — near-zero HN traction |
| S11 | HN "Show HN: simple RSS reader" #41305272 | T1 | 2024-08-20 | **68 points, 21 comments** — decent traction; commenters note YouTube feed support, truncated-feed complaints |
| S12 | HN "Show HN: Lighthouse RSS" #41993985 | T1 | 2024-10-30 | 8 points; users want browser extension / read-later |
| S13 | HN "Show HN: Kindly RSS e-ink" #42954856 | T1 | 2025-02-05 | 12 points; "making my own RSS reader" = HN rite of passage; niche unmet need drives builds |
| S14 | 9to5Google | T2 | 2025-05-12 | YouTube TV forces Shorts shelf on top of subscriptions, **cannot be turned off** |
| S15 | AndroidHeadlines | T2 | 2025-05 | Same; widespread Reddit frustration |
| S16 | SmartTube GitHub issues #4422/#4278/#4859 | T1 | 2025-2026 | Users manually cross-check web to find **missing subscription videos**; "hide shorts" breaks feed. Confirms core pain. |
| S17 | indie10k.com | T3 | 2025-08-07 | Side projects fail on business/distribution, not code; "build it and they'll come" myth |
| S18 | Indie Hackers "50 failed solo projects" | T1/T2 | undated | 81% no acquisition plan post-launch; 64% validated in echo chambers; 58% underpriced |
| S19 | ogblocks.dev | T3 | undated | Underpricing attracts high-support free-tier users; pre-sell before building |
| S20 | Indie Hackers "Why Indie Founders Fail" | T1/T2 | undated | PH/HN treated as silver bullet = failure; "distribution is part of the product" |
| S21 | Crxlytics "Chrome Web Store SEO" | T3 | undated | **40–70% of installs for no-ad extensions come from CWS internal search**; top 3 = 60–80% of query volume; install velocity (recent) is the key signal |
| S22 | ExtensionRanker CWS ranking | T3 (cites Google docs) | undated | CWS sorts on metadata match + ratings + downloads-vs-uninstalls over time |
| S23 | PocketTube pockettube.io + chrome-stats.com | T1/T2 | 2026-03 | 300K+ users, ~21K daily; keyword-stuffed title "PocketTube: YouTube Subscription Manager"; paid features behind Patreon |

---

## ROUND 1 — Channels That Worked for Analogs

**YouTube enhancer extensions (Unhook, SponsorBlock, PocketTube, DeArrow):**
- **Word-of-mouth + community advocacy** is the dominant engine. Unhook (500K users) explicitly attributes growth to being "recommended by digital wellness communities and productivity experts," not paid marketing [S1, T1].
- **Chrome Web Store internal search SEO** is the silent workhorse. For extensions with no ad spend, 40–70% of installs come from CWS's own search box (not Google) [S21, T3]. Ranking driver = **recent install velocity + ratings + low uninstall rate** [S21/S22]. PocketTube's title is literally the target keyword: "PocketTube: YouTube Subscription Manager" — metadata-match SEO in action [S23].
- **"Free forever, no account required"** lowers install friction and fuels velocity → ranking (Unhook) [S1].

**FOSS clients (FreeTube, NewPipe):**
- Growth = **post the project publicly + let the community carry it**. FreeTube founder released a rough v1 online in 2018, and "the community started to step in and help out," with progress accelerating during the Invidious API crisis [S4]. GitHub itself is a discovery channel: 21K stars [S3].
- Live in **privacy/FOSS subreddits** (r/newpipe ~30K, adjacent r/fossdroid, r/degoogle, r/selfhosted, r/DataHoarder) [S2].

**RSS readers (Feedly, NewsBlur, Readwise, Miniflux):**
- **Feedly's defining growth event = a competitor shutdown (Google Reader, 2013).** 1M→15M users; 500K in 48h; 4M→12M in 3 months [S5/S7/S8]. Mechanism: one-click migration importer + owning the "Google Reader alternative" search result + a new-user feedback forum [S6/S8].
- **Readwise (~$14M ARR, no VC)** grows via product-led expansion (Reader launch = +50% DAU in 6 months), creator partnerships, and an API/integration ecosystem — durable but slower than a shutdown spike [S9].

---

## ROUND 2 — Where the Audience Lives

- **r/youtube** — primary pain surface (subscription feed complaints, Shorts hate). Highest topical intent.
- **r/DataHoarder, r/selfhosted, r/degoogle, r/privacy, r/fossdroid** — FOSS/privacy YouTube-alternative users; overlap with NewPipe/FreeTube crowd [S2].
- **r/rss** — small but perfectly on-topic (RSS-native audience).
- **r/productivity, r/getdisciplined** — the Unhook/"reclaim attention" framing lands here [S1, S10].
- **r/chrome, r/browsers, r/YouShouldKnow** — where extensions get recommended organically.
- **Hacker News** — mixed for RSS readers. A polished "simple & powerful RSS reader" hit 68 pts / 21 comments [S11]; but most solo RSS Show HNs stall at 3–12 points [S10/S12/S13]. HN reception depends on a sharp differentiated angle, not "another RSS reader."
- **GitHub** — discovery channel for open/self-hostable tools (stars = social proof) [S3].
- **Product Hunt** — indie makers repeatedly report launches "buried within hours," near-zero traffic [S18, S20, and the itslaunched founder's PH complaint in S-HN]. Treat as a checkpoint, not an engine.

---

## ROUND 3 — SEO / Keyword Opportunities

High-intent, frustration-driven terms this audience actually searches. Demand is **directional** (no keyword tool run — see Data Gaps):

| Keyword cluster | Intent | Evidence of demand | Rough demand |
|---|---|---|---|
| "youtube subscription feed not showing all videos" | Bug-frustration, high intent | Recurring SmartTube issues + web cross-checking behavior [S16] | Med, very high intent |
| "hide youtube shorts" / "remove shorts from subscriptions" | Feature-seeking | Forced Shorts shelf 2025, "cannot be turned off," widespread Reddit anger [S14/S15] | **High + rising (2025 catalyst)** |
| "organize youtube subscriptions" / "youtube subscription manager" / "group youtube subscriptions" | Solution-seeking | PocketTube 300K users ranks on this exact phrase [S23] | High, commercial intent |
| "youtube chronological subscription feed" | Solution-seeking | Algorithm-vs-chronological is a common complaint | Med |
| "google reader alternative" style play → "youtube subscriptions rss" / "rss feed for youtube channel" | Migration/power-user | HN commenter posted the `youtube.com/feeds/videos.xml?channel_id=` trick unprompted [S11] | Low volume, extremely high intent |
| "distraction free youtube" / "youtube without recommendations" | Productivity | Unhook's whole positioning [S1] | High |

**SEO play (from Feedly teardown):** build the **"[X] alternative"** landing page BEFORE any catalyst, so it's indexed and jumps page-2→page-1 when demand spikes. Top 3 results capture ~70% of clicks [S6]. Peristalsis analog: pre-rank pages for "youtube subscription feed alternative" / "see all your youtube subscriptions" and be positioned for the next YouTube UI change that angers users (the Shorts shelf is a live, ongoing catalyst [S14]).

---

## ROUND 4 — Product Hunt / Show HN Playbook

**Show HN:**
- Winners lead with a **sharp, specific differentiator + a personal problem story**, not "I built an RSS reader." The 68-pt post had polish + a clear niche; the 3-pt post was generic [S10 vs S11].
- HN wants: self-hostable option, open standards/interop, privacy, no lock-in, handles information overload. YouTube-in-RSS is explicitly a wanted feature (commenters brought it up unprompted) [S11/S13].
- Honesty wins: "solo dev, launched today, want feedback" framing is normal and accepted [S10].
- Timing/title/first-comment matter; a mediocre reception ≠ dead product, but don't bet the launch on it.

**Product Hunt:**
- Repeated indie reports: launches "buried under dozens of others within hours," near-zero durable traffic [S18/S20]. It's a **one-time social-proof badge + backlink**, not an acquisition channel. Do it once, cheaply, don't build strategy around it.

**Better than either single launch:** the Feedly model — **be the obvious, frictionless migration path** the moment YouTube frustrates users, with the ranking page already built [S5/S6].

---

## What to Avoid (Failure Patterns)

1. **"Build it and they will come."** #1 indie failure mode. 81% of failed solo projects had no post-launch acquisition plan [S18]; distribution must be part of the product from day zero [S17/S20].
2. **Validating in echo chambers.** 64% "validated" via friendly feedback, no one paid [S18]. Talk to strangers in r/youtube, not friends.
3. **Underpricing.** 58% underpriced; cheap free/low tiers attract the highest-support, lowest-value users [S18/S19]. $5/mo needs 1,000 payers for $5K MRR — brutal for a solo founder [S19].
4. **Treating PH/HN as a silver bullet** [S20]. One-shot launches ≠ growth engine.
5. **Overbuilding before validation** (multi-tier pricing, enterprise features, custom infra) instead of shipping an embarrassing v1 [S17/S19/S20].
6. **Promoting like a marketer in FOSS/privacy communities.** Self-serving posts get downvoted; authenticity + rapid bug fixes = trust [S2].
7. **Platform-dependence risk (not GTM but flagged):** every YouTube-scraper tool (SmartTube, NewPipe, FreeTube) suffers recurring breakage from YouTube API changes [S16]. Peristalsis's RSS/WebSub approach is more stable *and* is a marketing differentiator — but the same "feed missing videos" bug (YouTube's RSS only returns ~15 recent items, no Shorts) will hit; set expectations.

---

## Data Gaps

- **No hard keyword volume numbers.** Demand estimates are inferred from frustration signals, not a keyword tool (Ahrefs/Semrush/Google Keyword Planner). *Recommend: run the ROUND 3 terms through a real tool before committing SEO effort.*
- **No SponsorBlock-specific growth history found** — inferred from category patterns (S1 covers Unhook, general community advocacy). SponsorBlock's actual install curve/attribution not sourced.
- **Miniflux / NewsBlur specific acquisition data not found** — grouped under general RSS-reader patterns.
- **Product Hunt outcomes are anecdotal** (indie-maker complaints), not a dataset of launch results for feed-reader tools specifically.
- **CWS SEO sources are T3 analytics blogs** citing Google docs; directionally reliable, but exact ranking weights are Google-internal and unconfirmed.
- **Readwise growth stats (S9) are T3** and should be corroborated before citing externally.
- **No data on paid-conversion rates** for freemium YouTube tools (PocketTube's Patreon model noted but no conversion %).
