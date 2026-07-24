# Peristalsis — Market Size & Unit Economics

> Research date: 2026-07-23. Analyst: market-sizing research (WebSearch, 8 queries / 4 rounds).
> Product: freemium RSS/WebSub-powered alternative YouTube subscription feed. Companion to YouTube (watch on YT itself). Solo technical founder, side project, low budget. Pricing: Free / $5·mo Premium / $25+·mo Patron. Target: YouTube power users w/ large subscription counts, global, English-first.
>
> **Methodology note:** No numbers fabricated. Where hard data is missing, proxy estimates are shown with explicit math and labeled ESTIMATE. TAM/SAM/SOM derived from stated assumptions — change the assumptions, change the answer.

---

## TAM

**Definition:** Universe that could plausibly want an alternative/curated YouTube subscription feed — i.e. YouTube users who actively use subscriptions and have enough of them that feed management is a felt pain.

### Anchor inputs
- YouTube MAU 2026 ≈ **2.7B** (third-party consensus; Google ad-audience proxy 2.53B Jan 2025, 2.58B 2026 per DataReportal). [Tier 2–3]
- Average YouTube user subscribes to only **15–20 channels** (G2, 2025). Feed pain scales with subscription count → most users are NOT in pain; power users are. [Tier 3]
- Algorithm-averse / feed-affinity cohort: **~6% of internet users consume RSS** (Forrester, but this stat is very old — FLAG). ~50M global RSS users (2022, dead source — FLAG). [Tier 3, stale]

### Math (two independent triangulations)
**(A) Top-down from YouTube MAU:**
- Assume **5%** of MAU are heavy-subscription users for whom curation has value (ESTIMATE — no hard figure exists for "heavy subscribers").
- 2.7B × 5% = **135M users** (range 100–160M).
- Cross-check: RSS-affinity 6% × 2.7B ≈ 162M. Same order of magnitude → triangulation holds.

**(B) Monetary TAM — bottom-up:**
- 135M users × low paid conversion 3% × blended ARPU ~$96/yr (mix of $5 & $25 tiers) = **~$389M/yr**.
- Independent cross-check: the **RSS reader software market = $222M–$322M in 2025**, ~6.3–7.4% CAGR to ~$400–570M by 2033 (Verified Market Research / Dataintelo / Verified Market Reports — sources disagree). Peristalsis is a YouTube-specific slice of this. Order of magnitude matches. [Tier 3]

### TAM verdict
- **Users: ~135M** (range 100–160M) YouTube heavy-subscription users globally.
- **Revenue: ~$300–400M/yr** if the entire global feed-tool niche were monetized under freemium economics.
- This is the *ceiling of the whole category*, not a realistic target. It also assumes non-English markets, which the product is not positioned for → SAM cuts hard.

---

## SAM

**Definition:** English-speaking YouTube power users, frustrated with the native subscription experience, who would actually adopt a companion tool.

### Narrowing filters (each multiplicative)
1. **English-first markets.** US ~253M + UK/CA/AU/NZ/IE + English-preferring subsets of India, Philippines, Nigeria, etc. ESTIMATE English-addressable YouTube users ≈ **500M** (range 400–600M).
2. **Power users (not just "heavy").** Hundreds of subs, actively organize. Narrower than the 5% heavy cohort.
3. **Frustrated enough to install + adopt a tool.** Demonstrated by installed base of YouTube-enhancer extensions (see proxies below).

### Demand proxies (installed-base of "will install a YouTube tool" cohort) — STRONG signal
| Tool | Function | Users | Tier / date |
|---|---|---|---|
| SponsorBlock | skip sponsor segments | **2.0M+** (Chrome) + Firefox etc. | Tier 1–2, 2026 |
| Unhook | remove YT distractions/feed | **1.0M+** active | Tier 1–2, 2026-03 |
| PocketTube | **subscription grouping/organizer (closest analog)** | **~21,500 daily users** | Tier 2–3, 2026 |

> **Key reality-check:** PocketTube is the near-exact analog (organize YT subscriptions into groups). Its **~21.5k daily** users after years in-market is a sobering ceiling on demand for the *specific* "manage my subs" job-to-be-done. SponsorBlock/Unhook are larger because they solve a sharper, more universal pain (ads/distraction) than "organize subscriptions."

### Math
- 500M English YT users × **3% frustrated power-user fraction** (ESTIMATE) = **15M users** (range 10–20M).
- Monetary SAM: 15M × 3% paid conversion × ~$72/yr blended ARPU = **~$32M/yr** ARR potential if the entire English power-user feed-tool niche were fully monetized.

### SAM verdict
- **Users: ~15M** (range 10–20M).
- **Revenue: ~$30M/yr** category potential.
- Caveat: the PocketTube proxy suggests the *actively-seeking* sub-set may be closer to the low hundreds-of-thousands, not 15M. 15M is "could be persuaded"; the "actively looking right now" number is far smaller.

---

## SOM

**Definition:** Realistically obtainable in Year 1 given a solo technical founder, low budget, and organic-only channels (build-in-public, Reddit, HN, niche communities, SEO).

### Binding constraint
Distribution, not code. Indie benchmarks are blunt: **median time to $10K MRR is 12–24 months** for products that get there *at all*; **year-one realistic is $1–5K MRR**, and many indie products reach **$0**. The "0→$10K in 8 weeks" stories are outliers (existing audience). [Tier 2–3]

### Bottom-up Y1 math
1. **Organic free signups Y1:** niche tool, good build-in-public + a few Reddit/HN hits. ESTIMATE **5,000–20,000 free users** (midpoint ~10,000). PocketTube's ~21.5k *daily* after years caps optimism.
2. **Freemium conversion:** typical 2–5%; but "utility tool w/ good-enough free tier" skews **LOW → 2–3%** (freemium cannibalization). 
3. **Blended ARPU:** most pick $5/mo, few pick $25+/mo. ~$6–8/mo = **$72–96/yr**.

| Scenario | Free users | Conv. | Payers | ARPU/yr | ARR | ~MRR exit |
|---|---|---|---|---|---|---|
| Pessimistic | 5,000 | 2% | 100 | $60 | **~$6K** | ~$500 |
| Midpoint | 10,000 | 2.5% | 250 | $84 | **~$21K** | ~$1.75K |
| Optimistic | 20,000 | 3% | 600 | $90 | **~$54K** | ~$4.5K |

### SOM verdict
- **~$6K–$54K ARR in Year 1, midpoint ~$20K ARR (~200–400 paying customers, ~$1.5–2K MRR exit).**
- These are **gross** figures. Consumer/prosumer monthly churn 6–12% means net retained revenue is materially lower — the midpoint could net closer to $10–15K realized.
- Matches the indie "year-one realistic $1–5K MRR" band.

---

## Unit Economics Benchmarks

| Metric | Benchmark | Relevance to Peristalsis | Source / Tier |
|---|---|---|---|
| **Freemium free→paid conversion** | Typical **2–5%**; median 8% (bimodal: 25% of products <2.5%, 25% at 10–15%); consumer "good-enough free tier" skews **low end** | Utility tool → expect **2–3%** | OpenView/ChartMogul/Kyle Poyar Growth Unhinged 2026 [Tier 2] |
| **Reverse-trial (free→timed premium trial)** | ~8% median | Lever to lift conversion | Growth Unhinged 2026 [Tier 2] |
| **Monthly churn, consumer/prosumer <$500 ACV** | **6–12%/mo** (53–79%/yr) | This is the killer metric — $5/mo tier lives here | ChurnTools 2026 [Tier 3] |
| **Monthly churn, B2C <$10/mo** | 60–80% annual logo churn; only **5.3%** of sub-$10/mo products hit >85% gross retention | $5/mo Premium is structurally high-churn | knowledgelib.io / ChartMogul 2026 [Tier 2–3] |
| **ARPU, B2C SaaS** | **$5–50/mo**, high price sensitivity | $5 Premium at floor; $25 Patron is the margin-maker | Multiple 2026 [Tier 2–3] |
| **LTV:CAC (B2C <$10/mo)** | **1.5–2.5:1** (weak); payback 1–3 mo | Thin — only works with ~$0 CAC (organic) | knowledgelib.io 2026 [Tier 2] |
| **CAC — organic/content/SEO** | $290–$942 (B2B); **B2C organic ~$135** vs paid ~$197 | Must stay near-zero; paid ads will not pencil at $5/mo | Multiple 2026 [Tier 2–3] |
| **CAC — referrals** | $141–$200; 3–5× cheaper than paid | Best viable channel besides content | 2026 [Tier 2] |
| **Solo-founder net margin** | **70–90%** ($50–100/mo infra vs revenue; Stripe 2.9%+30¢ biggest cost) | Favorable — survival is the game, not margin | boilerplatehub / SoftwareSeni 2026 [Tier 3] |
| **Time to $10K MRR (bootstrapped)** | Median **12–24 mo**; top performers 6–9 mo (existing audience) | Set expectations: Y1 is validation, not scale | Multiple 2026 [Tier 2–3] |

**Implied LTV for Peristalsis $5/mo customer @ 8%/mo churn:** avg lifetime = 1/0.08 = 12.5 mo → LTV ≈ $62.50 gross (less Stripe fees). At $25 Patron @ lower churn (~5%) → ~20 mo → LTV ~$500. **The Patron tier and low-churn power users are where the economics actually live.**

---

## Market Headwinds & Risks

**Top 3 (ranked):**

1. **Willingness-to-pay gap — "useful ≠ paid-for."** People pay when a product saves significant time on a frequent task, makes money, or removes real risk/anxiety. "Organize my YouTube subs" is convenient, not critical, and free alternatives (native YT, PocketTube free, Unhook) are "good enough." Failed-SaaS analyses repeatedly cite intermittent pain + existing free workarounds as the #1 monetization killer. **Validation test: are target users *already paying* to solve this? Almost certainly no → red flag.** [scoutr.dev, saasopportunities 2026, Tier 3 but consistent]

2. **$5/mo = the "wrong customer" price + brutal consumer churn.** The 312-micro-SaaS study: $9/mo pricing selects for individuals who churn 8–12%/mo, dispute charges, expect consumer onboarding, and leave one-star reviews. To hit $10K MRR at $5/mo you need **2,000 payers**; at $79/mo you'd need 127. Consumer churn 6–12%/mo means you refill a leaking bucket forever. The $25+ Patron tier partly mitigates but is a small slice.

3. **Freemium cannibalization + tiny proven niche.** A capable free tier caps conversion at the low end (2–3%). PocketTube's ~21.5k daily users shows the *specific* "manage subs" demand is small in absolute terms. SponsorBlock/Unhook prove YouTube-tool demand exists — but for *sharper* pains (ads, distraction) than curation.

**Secondary risks:**
- **Platform dependency.** Built on YouTube RSS/WebSub feeds + watching on YouTube. Google can throttle/remove per-channel RSS feeds or change WebSub at any time → existential single-point-of-failure. (Not a market-size figure, but the biggest strategic risk.)
- **RSS is a niche, flat-to-slowly-growing category** (~6% CAGR), not a wave to ride.
- **Solo-founder distribution ceiling.** Constraint is time/distribution; support load caps many indie tools ~$30K MRR without a hire.
- **Expansion revenue is weak in consumer** (NRR 70–95%); plan on cohort survival, not upsell.

---

## Source Quality Assessment

| Source | Claim used | Tier | Date | Flag |
|---|---|---|---|---|
| DataReportal / Google ad tools | YT ad audience 2.53B (2025), 2.58B (2026) | **T2** (Google-derived) | 2025–26 | OK |
| Neal Mohan / Cannes Lions | 1B+ hrs/day, TV watch | **T1** (official) | 2025 | OK |
| demandsage / globalmediainsight / therehankadri / resourcera | YT MAU 2.58–2.83B | **T3** (SEO stat aggregators) | 2026 | Use as range, not precise |
| G2 (via talks.co) | Avg user subs 15–20 channels | **T3** | 2025 | OK-ish |
| Forrester (via InformationWeek) | 6% internet users consume RSS | **T3** | ~2008 orig | **FLAG: very old (>10yr)** |
| "50M RSS users" (via andrewblackman/readless) | 50M global RSS users | **T3** | 2022, dead source | **FLAG: >18mo + unverifiable** |
| Verified Market Research / Dataintelo / Verified Market Reports | RSS reader mkt $222–322M, 6.3–7.4% CAGR | **T3** (syndicated market research) | 2025 | Sources disagree; directional only |
| chrome-stats / Chrome Web Store | SponsorBlock 2M, Unhook 1M | **T1–2** (platform-reported) | 2026 | Strong |
| PocketTube (via chrome-stats) | ~21,500 daily users | **T2–3** | 2026 | Best analog; strong signal |
| Kyle Poyar / Growth Unhinged / ChartMogul / ProductLed | Freemium conversion 2–5%, median 8% | **T2** | 2026 | Strong |
| OpenView Product Benchmarks | Freemium 1–10% band | **T2** | 2022–24 | Slightly aging but industry-standard |
| ChurnTools | Consumer churn 6–12%/mo | **T3** | 2026 | Directional |
| Recurly Research | DTC churn ~6.5% | **T2** | 2025–26 | Reputable |
| knowledgelib.io | B2C <$10/mo 60–80% annual churn; 5.3% >85% retention | **T2–3** | 2026 | Directional |
| SaaS CAC reports (deepresearch.ninja, proven-saas, saashero, saasultra, ltvcacbook) | CAC by channel | **T2–3** | 2025–26 | Multiple agree → confident on ranges |
| scoutr.dev / saasopportunities | WTP gap, failure modes | **T3** (analysis essays) | 2025–26 | Opinion but internally consistent w/ data |
| bigideasdb / softwareseni / boilerplatehub / monolit / unbuiltlab | Indie MRR timelines | **T3** | 2026 | Consistent across sources |

**Overall:** YouTube MAU and extension-user counts are reliable. RSS penetration stats are **weak/stale** (biggest data-quality problem). Unit-economics benchmarks are well-corroborated (T2). Market-research report figures are T3 and should be treated as directional order-of-magnitude only.

---

## Data Gaps

1. **No hard figure for "YouTube power users" / heavy-subscription-count users.** The 5% assumption is an ESTIMATE. Google does not publish subscription-count distributions. This is the single largest driver of TAM/SAM uncertainty.
2. **No reliable current RSS-user count.** The "50M" and "6%" figures are stale (2022 / ~2008) and trace to dead/secondary sources. Actual 2026 RSS penetration is unknown.
3. **English-first YouTube user count is an ESTIMATE** (~500M). No direct source segments YT MAU by content language preference.
4. **No direct willingness-to-pay data for YouTube subscription-management tools specifically.** PocketTube (free/freemium) usage is the best proxy; its paid-conversion and revenue are not public.
5. **PocketTube MAU (vs daily) and revenue unknown** — would sharpen SAM materially.
6. **Freemium conversion for consumer *utility* tools specifically** (vs B2B SaaS) is thinly sourced; used low-end of general benchmarks.
7. **Churn for a companion/utility tool at $5/mo** is inferred from the broad consumer/prosumer band, not a direct comp.
8. **No data on YouTube's RSS/WebSub reliability/roadmap** — the key platform-risk input is unmeasurable from outside.

**Recommended cheap validations before building further:** (a) landing-page smoke test → measure email-capture conversion; (b) Reddit/HN "would you pay?" probes in r/youtube, r/DataHoarder, RSS communities; (c) scrape/estimate PocketTube + similar community sizes; (d) confirm current per-channel YouTube RSS feed availability.
