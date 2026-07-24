# Target Audience Research — Peristalsis

**Product:** Freemium SaaS. RSS/WebSub-based alternative YouTube subscription feed (group / favorite / filter subs, watch on YouTube).
**Target framing:** YouTube power users frustrated with the current subscription feed.
**Method:** 7 WebSearch queries, July 2026. Sources cited inline with tier + date. Estimates explicitly labeled `[ESTIMATE]`.

**Source tiers used:**
- **Tier 1** = primary data / platform official / large-sample research
- **Tier 2** = reputable industry/analyst/trade press
- **Tier 3** = blogs, vendor content, community stats aggregators, forum threads (directional)

---

## 0. Key Context Findings (the wedge is real and timely)

The core pain is **actively getting worse in 2025–2026**, which is the single most important finding for timing.

- YouTube removed the Subscriptions **list view** and killed the long-standing `?flow=2` URL workaround (rolling out in waves since Dec 2025). Users forced into grid layout. (Tier 2, PiunikaWeb, 2026-02-03)
- YouTube added a **"Most Relevant" section on top of the Subscriptions feed**, pushing chronological uploads down — "homepage 2.0." Non-chronological ordering, Shorts injected, collaborations from non-subscribed channels appear. (Tier 2, PiunikaWeb, 2026-02-26; Tier 3, DroidWin, 2026)
- Strong organic demand for fixes: Hacker News thread "YouTube destroying all value of the Subscriptions page"; multiple userscripts/extensions exist — **Control Panel for YouTube**, **YSLV userscript** (Chrome/Firefox/Greasemonkey), **Unhook**, uBlock Origin filters. (Tier 1/3, Hacker News #47656042, 2026; GitHub m0x2A/yslv-userscript, 2026)
- One HN user: subscriptions page as an "inbox of unwatched videos"; needs 17 Page-Downs on vanilla YT to see what an extension shows above the fold. → **the JTBD is "inbox for my subs," and existing free tools already prove demand.**

**Implication:** The competitor set that matters most is **free browser extensions/userscripts**, not other paid apps. That is both validation (demand exists) and the central monetization threat (see Data Gaps).

---

## 1. YouTube Power Users — Baseline (Round 1)

### Scale & engagement
- ~2.6–2.7B YouTube monthly active users; ~122M daily users. (Tier 2, Sprout Social / Hootsuite / DataRefs, 2026)
- Avg US daily watch time ~41 min/user. 78% of US users watch YouTube "to learn something new"; 62% watch while multitasking; 40% of Gen Z use it as a primary search engine. (Tier 3, DigitalApplied, 2026)
- Long-form rising: videos ≥20 min grew from 63.6%→67.7% of viewing time (Jan 2024→Dec 2025). (Tier 1, Digital i "YouTube Era" report, June 2026)
- Heavy users cluster: YouTube users without Netflix avg 120 min/day; even heavy Netflix users avg ~110 min/day on YouTube. (Tier 1/2, Digital i via NetInfluencer, 2026)

### Demographics (general platform — power-user-specific skew is an estimate)
- Gender: ~53–54% male / 46–47% female. (Tier 2, Sprout Social / Hootsuite, 2026)
- Largest age band: 25–34 (~21%), then 35–44 (~19%). (Tier 2, Sprout Social, 2026)
- Top countries by users: India (491M), US (253M), Brazil (144M), Indonesia (143M), Mexico (84M). US users skew well-educated. (Tier 2, Hootsuite, 2026)
- `[ESTIMATE]` Power users frustrated with the *feed* skew older-than-median (28–45), more male, higher-income, English-speaking, desktop-comfortable, and technical. Rationale: complaints concentrate on HN/Reddit and manifest as installing extensions/userscripts — a technical act.

### Subscriptions per user
- **No reliable public figure exists** for subscriptions-per-user distribution. `[ESTIMATE]` Frustrated power users likely follow **50–300+ channels** — enough that a chronological feed becomes essential and grid/algorithmic reordering becomes intolerable. Flagged as a **data gap** (see §6).

### Device usage (US, share of watch TIME) — critical strategic finding
| Surface | Share of watch time | Avg session | Trend |
|---|---|---|---|
| Connected TV | ~60% | 19 min | ↑ +8 pts YoY |
| Mobile | ~28% | 8.4 min | ↓ -6 pts YoY |
| Desktop/laptop | ~8% | 14 min | ↓ slightly |
| Tablet | ~4% | 11 min | flat |

(Tier 3, DigitalApplied, 2026; corroborated directionally by Tier 1 Digital i & Tier 2 Variety/Nielsen, 2026)

- **Nuance:** TV dominates *watch time* but that's passive lean-back viewing skewed older (under-35s only 23% CTV; 35+ is 46%). (Tier 2, Variety/TVision, 2026)
- **Curation/triage happens on desktop + mobile**, not TV. Peristalsis's job (organize/filter/decide-what-to-watch) is a **desktop-first, mobile-second** activity even though the *watching* increasingly ends up on TV. This is a "second screen decides, big screen plays" model.

### Overlap with RSS / self-hosters / degoogle / productivity crowd
Adjacent communities are large and growing fast (Tier 3, community stat aggregators RedPulse/GummySearch, July 2026):
- r/privacy ~1.6M
- r/datahoarder ~950K
- r/selfhosted ~802K (+44% YoY)
- r/degoogle ~509K (+67% YoY)
- r/PrivacyGuides ~97K

- The self-hosted RSS ecosystem is active and explicitly YouTube-aware: **FreshRSS** (15K+ GitHub stars, supports WebSub), **CommaFeed**, and newer tools (Readr, FeedFerret, rdrs) that ingest **YouTube channels as RSS via RSSHub**. (Tier 3, GitHub, 2026)
- **Insight:** Technically savvy users *already* pipe YouTube into RSS readers. This proves the concept and reveals the segment — but also means the most technical sliver can self-host for free.

---

## 2. Personas (Round 2)

Three distinct personas emerge. They differ on *motivation*, *technical willingness*, and crucially *willingness to pay*.

### Persona A — "The Overwhelmed Curator" (mainstream power user) ★ likely beachhead
- **Profile:** 28–45, follows 80–300 channels, watches to learn + stay current + entertainment. Comfortable with browser extensions but does NOT want to run a server. Uses YouTube heavily across desktop (triage) → TV/mobile (watch). Not ideologically anti-Google — just wants their feed back.
- **Jobs-to-be-done:** "Show me everything new from channels I follow, in order, so I don't miss things." "Let me group creators (e.g., 'Tech', 'Cooking', 'Priority') and mute noise (Shorts, non-subs, streams)." Treat subs feed as an **inbox to clear**.
- **Pains:** Non-chronological "Most Relevant" reordering; Shorts injected; list view + `flow=2` removed; missing uploads; no way to prioritize/group subscriptions. (Tier 1/2, HN + PiunikaWeb, 2026)
- **Device/context:** Desktop-first for curation; watches on TV/mobile. Peristalsis "watch on YouTube" handoff fits perfectly.
- **Willingness to pay:** **Moderate–High** for a hosted, zero-setup tool that "just works" — *if* the free tier proves value and the paid tier removes a real limit (grouping, filters, unlimited subs, sync). This persona is large and cannot/won't self-host.
- **Where to reach:** YouTube-frustration threads (r/youtube, r/google), extension user bases (Unhook, Control Panel for YouTube, uBlock), productivity newsletters/creators, browser extension stores, Product Hunt.

### Persona B — "The Self-Hoster / Privacy User"
- **Profile:** Technical, ideologically motivated (privacy, degoogle, data ownership). Active in r/selfhosted, r/degoogle, r/privacy, r/datahoarder. Runs Docker, Tailscale, home servers.
- **Jobs-to-be-done:** "Consume YouTube without Google's algorithm/tracking." "Own my subscription data (OPML, export)." "Aggregate YouTube alongside other RSS."
- **Pains:** Algorithmic manipulation, tracking, lock-in, no data portability.
- **Device/context:** Desktop/self-hosted dashboards, often behind private network.
- **Willingness to pay:** **Low for a hosted SaaS** — this segment's default reflex is "I'll self-host FreshRSS + RSSHub for free." They pay for *convenience they can't easily replicate* or donate to projects they trust. High evangelism value, low direct revenue. (Tier 3, GitHub self-hosted RSS ecosystem, 2026)
- **Where to reach:** r/selfhosted, r/degoogle, r/privacy, Hacker News (Show HN), Lobsters, GitHub, PrivacyGuides, Awesome-Selfhosted lists.

### Persona C — "The Info-Junkie / Learner-Researcher"
- **Profile:** Uses YouTube as a primary learning/research tool (78% of US users watch to learn; 40% of Gen Z use it as search). Follows educational/niche creators; high hours-per-account on niche channels (e.g., education/gaming creators avg 40+ hrs/yr/account vs Netflix 0.4). (Tier 1/2, Digital i, 2026; DigitalApplied, 2026)
- **Jobs-to-be-done:** "Never miss uploads from key creators." "Separate 'deep learning' subs from casual." "Filter signal from Shorts/noise." Overlaps heavily with the productivity/PKM crowd (Notion, Readwise, note-taking).
- **Pains:** Feed noise burying substantive long-form; can't prioritize high-value creators.
- **Device/context:** Desktop + mobile; long-form on TV.
- **Willingness to pay:** **Moderate.** Prosumer/productivity buyers pay for outcomes and time saved (see Round 3). Less price-sensitive than B, less mainstream than A.
- **Where to reach:** Productivity YouTubers/newsletters, PKM communities (Notion/Obsidian/Readwise), TLDR/Console.dev-style newsletters, X/Bluesky productivity circles.

---

## 3. Highest-Value Persona / Beachhead

**Target Persona A ("The Overwhelmed Curator") first, with Persona C as the fast-follow expansion.**

Reasoning:
1. **Largest addressable pool** and the pain is *acute and worsening right now* (2026 feed changes). Timing wedge.
2. **Cannot / will not self-host** → a hosted freemium SaaS is genuinely their best option, unlike Persona B who defaults to free self-hosting.
3. **Willing to pay for zero-setup convenience** when value is proven — matches prosumer buying behavior (pay for outcomes, not primitives). (Tier 3, Sandy Diao GTM; Tier 2, G2 2025 Buyer Report)
4. Persona B is a **credibility/distribution channel, not a revenue base** — court them for launch buzz (HN, r/selfhosted) but do not build the business model around them.

**Why they'll pay:** They've already demonstrated intent by installing extensions/userscripts and complaining publicly. They want the outcome ("my chronological, grouped, filtered subs inbox") without technical work. Prosumers "swipe a credit card for value, fast" when the tool owns a **daily repeated moment** and removes steps. Checking subs is exactly that: a ≥5×/week habitual moment. (Tier 3, Sandy Diao; Tier 3, Smin Rana "Why Power Users Pay," 2025)

---

## 4. Buying Behavior & Trust Signals (Round 3)

### How prosumers/power users decide & pay
- Prosumers pay **from day one for outcomes**, comfortable with clear tiered pricing and upgrade paths. Sit in the sweet spot: consumer scale + enterprise-like WTP. (Tier 3, Sandy Diao "Prosumer GTM," 2026)
- Power users pay for **reliable daily cadence / owned moments**, not feature counts. Segment by intensity: power users = ≥5×/week. Willingness to pay tracks a repeated daily moment the product compresses to <2 min. (Tier 3, Smin Rana, 2025)
- 88% of *power users* willing to pay a premium for AI features **if value is clearly demonstrated** (vs ~two-thirds of general buyers). Growing preference for outcome/usage-based pricing. (Tier 2, G2 2025 Buyer Behavior Report)

### Freemium vs Free Trial — the core model decision
Benchmarks (Tier 3, Fungies / DigitalApplied, 2026, citing Lenny Rachitsky, Kyle Poyar, OpenView):
| Model | Signup→Paid conversion | Time to monetize |
|---|---|---|
| Free trial (opt-in) | 14–25% | 12–18 days |
| Free trial (opt-out, card required) | 48–52% | 7–14 days |
| Freemium | 3–5% (avg ~3.7%) | 90–180 days |

Decision variables: **ACV, time-to-value (TTV), cost-to-serve.**
- Freemium wins when: strong viral loop + near-zero marginal cost/user + **sub-5-min TTV**. Below ~$20/mo ACV favors freemium.
- Free trial wins when: setup/complex workflows, ACV >$50/mo, or high cost-to-serve.
- **Recommended hybrid:** full-feature time-limited trial → permanent restricted free tier. Captures trial urgency + freemium funnel width.

**Application to Peristalsis:**
- TTV can be **<5 min** (import subs via OPML/Google, see grouped chronological feed instantly) → freemium viable.
- **BUT** cost-to-serve is a live risk: polling/WebSub for many users' many channels has real infrastructure cost. The "free user isn't free" caveat applies. → **Cap the free tier** (e.g., # subs, # groups, no filters/sync) and price the paid tier on the limits power users actually hit. (Tier 3, DigitalApplied decision matrix, 2026)
- `[ESTIMATE]` Expect freemium free→paid conversion in low single digits (~2–5%); size the funnel accordingly.

### Trust signals this audience requires (skeptical, technical)
- **Peer recommendation** is the #1 signal — outweighs any marketing copy. Word-of-mouth in communities/Discord/Slack. (Tier 3, Skillful.sh; Medium/Geri Mate, 2026)
- **Open-source / local-first / transparency** massively boosts trust, especially on HN and with Persona B. (Tier 2, Okara "Launch on HN," 2026)
- **Instant try, no waitlist / no signup wall / no demo call.** Skeptical audience abandons friction. (Tier 2, Okara, 2026)
- **Honest, technical founder narrative** (how it works, trade-offs, stack) beats corporate language; HN "punishes marketing-speak."
- **Data portability** (OPML import/export) as an explicit anti-lock-in signal — directly reassures Persona B and C.
- **Privacy posture** (no tracking, clear data handling) — table stakes for adjacent communities.
- GitHub stars / Product Hunt badges = weak/SEO-only now, not primary conversion drivers. (Tier 3, youngju.dev launch strategy, 2026)

---

## 5. Channels to Reach Each Persona (Round 4)

### Persona A — Overwhelmed Curator
- **Reddit:** r/youtube, r/google, r/firefox (extension users), r/productivity, r/browsers.
- **Extension ecosystems:** users of Unhook, Control Panel for YouTube, YSLV userscript, uBlock Origin filter lists — reachable via the same GitHub repos/forums/Greasy Fork where they seek fixes.
- **Launch:** Product Hunt (SEO/badge + early adopters), browser extension stores (companion extension as a discovery funnel).
- **Newsletters/creators:** general productivity YouTubers and tech-news newsletters; TLDR (~2M daily).
- **Tactic:** show up in the *exact threads where people complain about the 2026 feed changes* with a genuine solution.

### Persona B — Self-Hoster / Privacy User
- **Reddit:** r/selfhosted (802K), r/degoogle (509K), r/privacy (1.6M), r/datahoarder (950K), r/PrivacyGuides (97K).
- **Hacker News (Show HN)** — best fit for open-source/local-first framing; Tue–Thu 8–11am ET; reply to every comment first 2 hrs; never coordinate upvotes. (Tier 2, Okara, 2026)
- **Lobsters, GitHub, Awesome-Selfhosted, PrivacyGuides.org.**
- **Requires:** open-source component or self-host option + strong data-ownership story, or this segment bounces.

### Persona C — Info-Junkie / Learner
- **Productivity/PKM communities:** Notion, Obsidian, Readwise circles; r/productivity, r/ObsidianMD.
- **Newsletters:** Console.dev (one dev tool/week), TLDR variants, curated productivity newsletters.
- **Creators:** productivity/"second brain"/learning YouTubers and their audiences.
- **X / Bluesky** productivity + learning communities (demos, screenshots, short videos perform).

### Cross-cutting
- **Social listening:** F5bot (free — Reddit/HN/Lobsters) or Octolens to monitor "youtube subscription feed," "flow=2," competitor/extension names, and problem keywords; engage authentically. (Tier 3, Reimer social-listening, 2025)
- **Owned newsletter** = strongest long-term asset (only channel not at mercy of an algorithm). (Tier 3, youngju.dev, 2026)

---

## 6. Data Gaps & Caveats

1. **Subscriptions-per-user distribution: unknown.** No reliable public data on how many channels power users follow. This directly drives free-tier caps and messaging. **Recommend primary research** (survey in target communities). Current 50–300 figure is `[ESTIMATE]`.
2. **Power-user-specific demographics: inferred, not measured.** All demographic figures are platform-wide; the frustrated-power-user skew (older/male/technical/higher-income) is an `[ESTIMATE]`. Validate via survey.
3. **Free extensions are the real competitor & the monetization threat.** Control Panel for YouTube, Unhook, YSLV, uBlock filters solve much of the pain *for free, client-side, no account*. Peristalsis must justify a hosted account + subscription over a free extension. Open question: **what does a hosted service do that a client-side extension cannot?** (Likely answers: cross-device sync, cross-browser/TV, WebSub push, grouping/filtering that doesn't break with YT DOM changes, no maintenance.) — **must be validated.**
4. **Willingness-to-pay magnitude unquantified.** Prosumer WTP is directional (they pay for outcomes); no price-point data for *this specific* product. Recommend pricing experiments / Van Westendorp survey.
5. **Cost-to-serve unmodeled.** Polling/WebSub at scale has real cost; free-tier economics unproven. Model before committing to generous freemium.
6. **ToS / platform risk (not audience, but existential).** Depending heavily on YouTube data (RSS/WebSub/InnerTube) carries dependency + ToS risk; YouTube's own 2026 changes show the platform actively closes workarounds. Note for strategy, not audience.
7. **Device-split data is US-centric** (watch time). Curation-vs-watch device behavior is inferred, not directly measured.
8. **Community sizes** are from Tier-3 stat aggregators (RedPulse/GummySearch) — directional, not audited.

---

### Sources (tier / date)
- PiunikaWeb — YouTube "Most Relevant" / list-view + flow=2 removal (Tier 2, 2026-02-03 & 2026-02-26)
- Hacker News #47656042 — Subscriptions page value destruction thread (Tier 1 primary voice, 2026)
- GitHub — m0x2A/yslv-userscript, FreshRSS, CommaFeed, Readr, FeedFerret, rdrs (Tier 3, 2026)
- DroidWin — fixing old subscription feed (Tier 3, 2026)
- Sprout Social, Hootsuite, DataRefs, ytshark, WhatsTheBigData — YouTube demographics/channel stats (Tier 2/3, 2026)
- DigitalApplied — YouTube Statistics 2026 (device split, learning stats) (Tier 3, 2026)
- Digital i "The YouTube Era" report (Tier 1, June 2026) via NetInfluencer & Variety/TVision/Nielsen (Tier 2, 2026)
- YouTube Blog / Oxford Economics / Livity — EU education study (Tier 1, 2025)
- RedPulse, GummySearch — subreddit sizes (Tier 3, July 2026)
- Sandy Diao "Prosumer GTM" (Tier 3, 2026); Smin Rana "Why Power Users Pay" (Tier 3, 2025)
- G2 2025 Buyer Behavior Report (Tier 2, 2025)
- Fungies.io & DigitalApplied — freemium vs free-trial benchmarks citing Lenny Rachitsky/Kyle Poyar/OpenView (Tier 3, 2026)
- Skillful.sh, Medium/Geri Mate — how devs discover tools (Tier 3, 2026)
- Okara — Launch on Hacker News (Tier 2, 2026); youngju.dev — launch strategy (Tier 3, 2026)
- Reimer.me — developer social listening (Tier 3, 2025)
