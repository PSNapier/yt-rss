# Distribution Channel Strategy — Peristalsis

**Persona:** "The Overwhelmed Curator" (mainstream YT power user, 28–45, 80–300 subs, uses extensions but won't self-host).
**Constraints:** solo founder, ~$0 marketing budget, organic-only.
**Prior findings assumed true:** audience adopts extensions 3–1000x more than standalone apps; pays via donations; #1 pain = "missing videos from subscriptions"; best SEO cluster = "hide youtube shorts / see all subscription videos / chronological feed"; communities = r/youtube, r/rss, r/selfhosted, r/degoogle, HN, Product Hunt.

**Source tiering used below:**
- **Tier 1** = official/primary (Google/Chrome docs, platform data, CWS listings themselves).
- **Tier 2** = data-driven operator studies / benchmark aggregators with methodology.
- **Tier 3** = single-operator blogs, opinion, indie anecdotes.

---

## 1. Channel Ranking by ROI

ROI here = (realistic qualified installs/signups) ÷ (founder-hours + $), weighted by **durability** (does the channel keep paying after the effort?) and **fit** to an extension-native, donation-paying audience.

| Rank | Channel | Effort | Durability | Realistic ROI | Verdict |
|------|---------|--------|-----------|---------------|---------|
| 1 | **Chrome Web Store SEO** (via a thin companion extension) | Med (build once) + low ongoing IF thin | **Very high** (evergreen search) | **Highest** | Primary engine |
| 2 | **SEO / content** ("see all subscription videos", "hide shorts", "chronological feed") | High (compounding) | **Very high** | High (slow burn) | Primary engine |
| 3 | **Reddit** (problem-thread replies, not posts) | Med (ongoing) | Medium | High per-hour | Core, week 1 |
| 4 | **Hacker News** (Show HN, once) | Low (one shot) | Low-tail but archives | High ceiling / low floor | One-shot spike |
| 5 | **Product Hunt** (once) | High prep | Low (48h decay) | Medium (credibility > acquisition) | Badge play |
| 6 | **YouTube creators / newsletters** (partnerships) | High (outreach) | Medium-high | Medium (slow, compounds) | Month 2–3 |
| 7 | **Firefox AMO / Edge Add-ons** | Low (port) | High | Medium | Cheap add-on |

### Rationale

**#1 Chrome Web Store SEO.** For this exact niche, CWS is where demand already lands. Direct analogs live and thrive there: **PocketTube reports 300,000+ users, 4,000+ 5-star reviews**, monetized via **Patreon/BuyMeACoffee donations** — exactly the extension-native, donation-paying behavior prior research flagged (Tier 1, PocketTube CWS listing + pockettube.io, updated Feb 2026). CWS ranking is driven by (a) **relevance** = keywords in title/summary/description and (b) **quality** = ratings + install velocity + active/uninstall ratio (Tier 1, [Chrome Discovery docs](https://developer.chrome.com/docs/webstore/discovery); Tier 2, [Extension Ranker 120k-datapoint study](https://extensionranker.com/blog/chrome-web-store-ranking-patterns)). Critically: **relevance is the one lever you fully control and it's a cold-start advantage** — "you don't need to wait for your user base to grow before you can rank well" (Tier 2, Extension Ranker). Title keyword-matching is the single highest-impact factor (Tier 2, [ExtensionFast 2025 guide](https://www.extensionfast.com/blog/chrome-web-store-seo-complete-ranking-guide-for-2025)).

**#2 SEO / content.** The search demand is real and YouTube keeps *manufacturing* it: YouTube is actively degrading the Subscriptions feed (injecting "most relevant", Shorts, live blocks ahead of the chronological list), generating fresh frustration and search queries (Tier 2/3, [Android Authority test report](https://www.androidauthority.com/youtube-subscription-section-test-3642927/); [ResetEra thread](https://www.resetera.com/threads/anyone-elses-youtube-subscriptions-tab-just-change.1449589/) — users explicitly say "I go to my sub feed to see releases in order… Leave me alone"). Competitors already rank standalone marketing pages on these terms (Tier 3, youtubebookmarkpro.com "chronological feed / Shorts filtering" landing page). Compounds for years; near-zero cash cost; feeds CWS install velocity.

**#3 Reddit.** High per-hour ROI **but only via the reply-to-problem-threads motion, not link posts.** r/youtube **bans self-promotion with immediate permanent ban** (Tier 2, [LeadsRover r/youtube rule check, reviewed Jun 9 2026](https://leadsrover.io/subreddits/r/youtube), quoting the actual rule). 61% of founder subreddits ban promo outright (Tier 2, [OneUp 49-subreddit study 2026](https://oneup.today/blogs/reddit-selfpromo-rules-study-2026)). The durable play: monitor "what tool do you use for X / missing videos" threads and reply helpfully with disclosure. Site-wide 9:1 contribution ratio; 30+ day aged account, 100+ karma before any mention (Tier 2, [OneUp r/SaaS rules](https://oneup.today/blogs/reddit-self-promotion-rules-saas)). r/degoogle, r/selfhosted, r/rss are friendlier to genuine tool mentions than r/youtube.

**#4 Hacker News (Show HN).** High ceiling, low floor. Front-page hit = 10k–30k+ visitors, dev-tool conversion 5–12%; but a flop = "3 upvotes, nothing," and 2025 engagement collapsed (probability of >10 points fell from 62% in 2022 to 11% in 2025) (Tier 2, [dowhatmatter](https://dowhatmatter.com/guides/product-hunt-vs-hacker-news); [causo hub](https://hub.causo.ai/guides/product-hunt-vs-hacker-news-vs-betalist-2026); [DoDataThings](https://dodatathings.dev/blog/launch-platform-roi-the-math-nobody-shares)). **Caveat for Peristalsis:** HN converts hard for *developer/technical* products. Peristalsis is a consumer utility, so temper expectations — but the RSS/WebSub + de-Google angle gives it genuine HN substance if framed as an engineering story, not a launch ad.

**#5 Product Hunt.** Realistic: 1,500–3,000 visitors from a top finish, 1–2% signup, ~24% activation (lowest of major surfaces — "hunter audience samples broadly, commits narrowly") (Tier 2, dowhatmatter; [forkoff 2026 playbook, n=14 launches](https://forkoff.xyz/blog/founder-growth/launch-platforms-beyond-product-hunt-2026)). Featured rate has dropped to ~10% (Tier 2, DoDataThings). Value is the **badge/credibility artifact and backlinks**, not raw acquisition. Worth one launch, low expectations.

**#6 Creator / newsletter partnerships.** "Borrow trust" motion: appear on audiences who already trust the intermediary. Proven bootstrapped path (Boot.dev grew ~$6k→$110k/mo primarily via YouTube/podcast collabs) (Tier 3, [3L3C case study](https://www.3l3c.ai/us/blog/us-startup-marketing-without-vc/bootstrapped-marketing-case-study); Tier 2, [Lenny/Emily Kramer on ecosystem](https://www.lennysnewsletter.com/p/ecosystem-is-the-next-big-growth)). Slow, outreach-heavy — defer to month 2–3 once you have a working product + social proof.

**#7 Firefox AMO + Edge Add-ons.** Nearly free once the Chrome extension exists (analogs like PocketTube, Better Subscriptions, Finitude all cross-list). Extra evergreen discovery for marginal effort.

---

## 2. Extension-as-Funnel Recommendation

**Verdict: YES — ship a *thin* companion extension. But deliberately architect it to avoid the fragile-overlay trap the founder fears.**

### Why yes
- The audience lives in the Chrome Web Store and adopts extensions 3–1000x over standalone apps (prior finding, corroborated by PocketTube's 300k users vs. the near-invisible standalone-app field).
- CWS is a **search + trust + one-click-install distribution channel you don't have to build** — the #1 ranked channel above. A web-app-only strategy forfeits it entirely.
- Analog proof of the exact funnel: **CloudHQ** built a $140K/mo freemium business by using dead-simple Chrome extensions as the front door, capturing emails at install and upselling to the web product; **14% free→paid** (Tier 3, [SaaSGrowthDaily](https://saasgrowthdaily.substack.com/p/how-chrome-extensions-turned-cloudhq)). Standard indie playbook: "start with an extension, add/keep the web app for SEO, payments, and content" (Tier 3, [BestSaaSIdeas](https://bestsaasideas.com/posts/2026/01/micro-saas-built-from-browser-extensions-guide/)).

### The founder's aversion is CORRECT — so don't build that kind of extension
The fear of fragile YouTube-overlay extensions is well-founded. YouTube is an SPA that changes its DOM constantly; overlay extensions require MutationObservers, `yt-navigate-finish` hooks, History-API interception, periodic fallback scans, and defensive selectors — and **still break on YouTube redesigns** (Tier 1/3 evidence: a real commit fixing an extension after "YouTube removed the internal component hierarchy… all card content now lives in a plain #content div" — [intentKeeper commit #56](https://github.com/Olawoyin007/intentKeeper/commit/835018b727765122dd52d2833a6171895c9d0e75); [DEV.to SPA-navigation writeup](https://dev.to/ktg0215/detecting-youtube-spa-navigation-in-a-chrome-extension-content-script-2fi8)). PocketTube's own listing brags "the only extension that works after the latest update" — proof that overlay maintenance is a permanent, brutal treadmill. As a solo founder you will lose that race.

### The recommendation: a "thin funnel" extension, NOT a YouTube-overlay extension
Build an extension whose job is **discovery + handoff**, where the heavy lifting stays server-side in the web app (which is robust: it reads subscriptions/videos via the account, not by scraping YouTube's DOM).

The extension does only three fragile-free things:
1. **Ranks in CWS search** for the money keywords in its title/summary (e.g. *"Chronological Subscription Feed · Hide Shorts · Group Subs"*). This is the whole point — capture the #1 channel.
2. **One-click onboarding:** button opens the Peristalsis web app, triggers OAuth / subscription import, done. No DOM injection into YouTube pages.
3. **Optional lightweight nicety** that doesn't depend on YouTube's internal DOM: a toolbar popup showing your grouped/chronological feed (rendered by *your* web app in the popup or a new tab), and — at most — a single toolbar action. **Avoid injecting UI *into* the YouTube page.** If you ever add an on-page element, keep it to one resilient anchor with graceful degradation, and treat breakage as acceptable (feature, not core).

This captures CWS SEO + one-click install + trust signals **without** signing up for the overlay-maintenance treadmill. If YouTube redesigns tomorrow, your extension and web app keep working because neither depends on YouTube's private DOM structure.

### Funnel mechanics to instrument
- **Attribution across the CWS handoff is lossy** (no referrer/UTM survives "Add to Chrome"). Use a server-generated attribution ID shared between landing-page session and the extension's first event (Tier 2, [Crxlytics](https://www.crxlytics.com/blog/chrome-web-store-conversion-rate)).
- Benchmarks to target (Tier 2, Crxlytics): listing view→install 15–25%; install→first-use 50–75%; D7 retention >30%.
- **Add explicit onboarding** — extensions with onboarding see D1 retention 20–40pts higher, and retained users are 5x likelier to review (feeds CWS ranking) (Tier 2, [ExtensionBooster](https://extensionbooster.net/blog/how-to-get-more-chrome-extension-installs-discovery-funnel-playbook-2026/)).
- **Keep permissions minimal** — broad permissions tank the install-confirm rate (Tier 2, ExtensionBooster). A thin funnel extension needs far fewer permissions than an overlay one — another win.

---

## 3. SEO / Content Plan

**Thesis:** own the "reclaim your subscription feed" query cluster. YouTube's ongoing feed degradation continuously refreshes this demand.

### Keyword clusters (priority order)
1. **Feed-control (highest intent, lowest competition-to-value):** "see all subscription videos", "youtube chronological subscription feed", "youtube subscriptions in order", "missing videos from subscriptions", "youtube not showing all subscription videos".
2. **Shorts removal:** "hide youtube shorts", "remove shorts from subscriptions", "block youtube shorts desktop".
3. **Organize subs:** "group youtube subscriptions", "youtube subscription folders", "organize youtube channels".
4. **De-Google / RSS bridge (HN/degoogle crossover):** "youtube rss feed subscriptions", "follow youtube without account", "youtube websub / pubsubhubbub".

### Content assets (each doubles as a Reddit reply resource + CWS listing keyword source)
- **Pillar page:** "How to see ALL your YouTube subscription videos in chronological order (2026)" → soft CTA to extension/web app. Target cluster #1.
- "How to hide YouTube Shorts on desktop — 3 methods (uBlock filter, extension, Peristalsis)" — include the honest free uBlock CSS method; being genuinely useful earns rankings + Reddit goodwill. Target cluster #2.
- "Why YouTube hides videos from your subscriptions (and how to get them back)" — targets the #1 pain keyword directly.
- Comparison/alternative pages: "PocketTube alternative", "best YouTube subscription managers 2026" (honest teardown — competitors do this; it ranks).
- **Free micro-tool for backlinks:** a no-login "paste your subscriptions / OPML → get a clean chronological view" or an OPML/RSS generator. Free tools accrue backlinks and feed the funnel (Tier 2/3, ecosystem + bootstrapped-marketing sources).

### On-CWS SEO (do this first, it's the cold-start lever)
- Front-load exact-match keywords in the **extension title** (highest-weight factor) and the 132-char summary.
- Ship updates every 60–90 days for "freshness."
- Drive external traffic (Reddit/HN/PH) at launch to spike **install velocity** → ranking feedback loop.
- Prompt for reviews only after the user hits the "wow" moment.

---

## 4. First 90 Days (week-by-week)

**Phase 0 — Foundation (Weeks 1–3): build the thin funnel + seed content**
- W1: Ship web app MVP happy path (OAuth → import subs → chronological grouped feed). Register a **personal** Reddit account NOW and start genuine participation (r/youtube, r/degoogle, r/rss, r/selfhosted) to age it past 30 days / 100 karma before any promotion.
- W2: Build the **thin companion extension** (CWS keyword-optimized title/summary, one-click handoff, minimal permissions, explicit onboarding, attribution ID). Draft the pillar SEO page.
- W3: Publish pillar page + "hide Shorts 3 methods" page. Submit extension to CWS (allow days–weeks for review + the "few months to qualify for featuring" clock to start — Tier 1, Chrome docs). Cross-submit to Firefox AMO + Edge.

**Phase 1 — Ignition (Weeks 4–7): manufacture install velocity**
- W4: Soft launch. Begin the **Reddit reply motion** — answer "missing videos / how do I see all subs / hide shorts" threads with genuinely helpful answers, disclosing your tool only where it fits. No link-drop posts.
- W5: Publish 2 more content pieces. Ship the free micro-tool (OPML/RSS view) for backlinks. Ask early happy users for CWS reviews (velocity + quality signal).
- W6: **Show HN** — frame as an engineering story ("I rebuilt YouTube subscriptions on RSS/WebSub because YouTube keeps hiding my videos"). Accept the low-floor risk; even a modest hit spikes CWS installs. Simultaneously post to r/degoogle / r/selfhosted where the de-Google framing lands.
- W7: Measure install→activation→D7 by source (via attribution ID). Double down on whichever of Reddit/HN/content produced qualified activations.

**Phase 2 — Amplify (Weeks 8–12): launch spike + partnerships**
- W8: **Product Hunt** launch (assets ready: GIF, demo, clear hero). Treat as credibility/backlinks + one velocity spike, not the main event.
- W9–10: Begin **creator/newsletter outreach** — list 25 targets (YouTube "productivity/de-Google/tech tips" creators, RSS/productivity newsletters). Pitch concrete collab: a 5-min "fix your YouTube subs" segment or teardown, with a creator-exclusive landing page. Track with "where did you hear about us" + UTMs.
- W11: Publish comparison/"alternative" pages now that you have social proof. Ship a CWS update (freshness signal) with a small feature from user feedback.
- W12: Review 90-day funnel. Kill low-ROI channels, reinvest founder-hours in the top 2 (expected: CWS SEO + Reddit/content). Plan quarter 2 around the winning motion.

**Sequencing logic:** content + extension listing are built first because they're the durable, compounding assets and the CWS cold-start lever; Reddit runs continuously (account must age); HN/PH are one-shot velocity spikes timed *after* the listing exists so the traffic converts into ranking; partnerships come last because they need a working product + proof to pitch.

---

## 5. Partnership / Creator Opportunities

- **YouTube creators in adjacent niches:** de-Google/privacy channels, "productivity systems", "tech tips", note-taking/PKM creators. Pitch a concrete "fix your broken YouTube subscriptions in 5 minutes" segment, not a sponsorship (Tier 2/3, Boot.dev + Klap ecosystem models). Creator-exclusive landing page per partner.
- **Newsletters:** RSS/indie-web newsletters, productivity newsletters, "cool tools" roundups. Offer a written teardown/migration guide they can run.
- **Complementary extensions/tools:** reciprocal mentions with non-competing YouTube-enhancer or RSS-reader tools (e.g. RSS reader apps that lack good YouTube support).
- **Community leaders:** mods of r/rss, r/degoogle — modmail first, offer a genuinely useful free guide before any promotion (free goodwill, avoids bans).
- **Affiliate/referral consideration:** Klap's 20% lifetime recurring commission created a self-propagating creator ecosystem (Tier 3, Passionbits). For a donation/freemium model this is harder, but a simple referral incentive for creators is worth testing in Q2.

---

## 6. Data Gaps & Risks

1. **CWS conversion math for a *thin funnel* extension is unproven.** Benchmarks (15–25% listing→install) come from utility extensions that deliver value in-extension. A pure "handoff to web app" extension may convert worse at the install→activation step. **Test early.**
2. **Peristalsis is a consumer utility, not a dev tool** — HN conversion benchmarks (5–12%) are for dev tools; realistic HN outcome here is more uncertain. The RSS/WebSub angle is the only thing giving it HN substance.
3. **PocketTube's 300k users are the ceiling proof but also the competitive moat** — it's entrenched, cross-platform, and updates aggressively. Peristalsis's differentiation (server-side robustness, no fragile overlay, RSS/de-Google) needs validation that users care enough to switch.
4. **Actual search volumes not measured** — keyword clusters are inferred from qualitative demand signals (Reddit/forum threads, competitor landing pages), not hard volume data. Run Google Keyword Planner / Ahrefs free tier before committing content order.
5. **YouTube ToS / API risk** — a web app importing subscriptions relies on YouTube Data API (quota limits, ToS on displaying content). WebSub/RSS covers *new uploads* but not full historical feed. Legal/quota constraints could cap the product and thus every channel. **Highest-priority unknown to resolve.**
6. **Attribution across CWS handoff is inherently lossy** — the attribution-ID pattern mitigates but doesn't fully solve per-source LTV measurement.
7. **Reddit account-aging is a hard dependency** — if the founder hasn't already got an aged account, the Reddit channel is effectively delayed ~30 days.

---

*Compiled from 8 web searches, July 2026. Sources tiered inline. No figures fabricated; all quantitative claims carry a citation. Benchmark ranges are directional operator data, not guarantees.*
