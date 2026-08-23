# Pricing & Willingness-to-Pay Research — Peristalsis

**Role:** Pricing & WTP research analyst
**Date:** 2026-07-23
**Status:** Discovery / raw research (highest-priority risk area)
**Method:** 7 WebSearches + competitor pricing supplied in brief. All external figures cited with source + tier + date. Estimates and inferences explicitly labeled `[ESTIMATE]` or `[INFERENCE]`.

## Source tiering key

- **Tier 1** — Primary/first-party (vendor pricing pages, company blogs, peer-reviewed).
- **Tier 2** — Reputable benchmark aggregators / practitioner data (RevenueCat, a16z, Lenny's, OpenView-derived).
- **Tier 3** — Secondary blogs, SEO content, marketing sites (directional only, treat with caution).

---

## Context recap

- Product: freemium SaaS, RSS/WebSub-based YouTube subscription feed. Watch YouTube subs as a clean feed without the algorithm.
- Founder pricing hypothesis: **Free** + **$5/mo Premium** (100+ subs, import/export) + **$25+/mo super-supporter/patron**.
- Audience: YouTube power users (prosumer, personal wallet).
- Competitor cluster (from brief): PocketTube ~$3.99/mo, Inoreader Pro $7.50–9.99/mo, Feedvault $99/yr+ (perceived too high). Most cluster **$3–8/mo**.
- Free good-enough substitutes exist: PocketTube free, self-hosted YT Zero. **This is the central WTP threat.**

---

## ROUND 1 — WTP for consumer/prosumer utility subscriptions

**Finding 1.1 — Prosumer B2C sits in a $5–30/mo band; impulse-buy ceiling is ~$20.**
Indie B2C/prosumer SaaS sweet spot is **$5–30/mo per user**; median Pro tier 2026 = **$19/user/mo**. Starter $5–12, Pro $15–29, Power $49–99. (Better Launch, *SaaS Pricing Strategy for Indie Founders*, 2026 — Tier 3). Consumer-subscription ARPU band independently corroborated at **$5–30/mo with high price sensitivity** (Subscribe & Conquer, 2026 — Tier 3).

**Finding 1.2 — For personal-wallet buyers, $9 often beats higher prices on NET revenue.**
Matched experiments cited: prosumer conversion drops **4–6x between $9 and $29** ($9 ≈ 3.2% landing-to-paid, $29 ≈ 0.7%), while support/infra cost per customer is ~constant → lower price frequently wins net revenue for individual buyers. $29–49 only wins when a **business** expenses it. "The buyer matters more than the product." (Digital Dashboard Hub, 2026 — Tier 3, aggregating Paddle/a16z/OpenView/ProfitWell). `[Directional — self-reported case data, not audited.]`

**Finding 1.3 — Developers/technical audiences convert at roughly HALF the rate of non-technical.**
Median free-to-paid for developer-focused companies = **5%, half** of non-developer companies. (Lenny's Newsletter / *What is a good free-to-paid conversion*, 2023 — Tier 2). **Directly relevant**: "YouTube power user who wants RSS" skews technical/tinkerer — the exact crowd that self-hosts free alternatives (YT Zero) rather than pays. This depresses expected conversion.

**Finding 1.4 — Only 23% of startups do formal WTP research; those that do price 19% higher with 14% lower first-year churn.** (Stealth Agents citing OpenView Partners 2025 — Tier 2/3). Consumer/prosumer **median ACV ≈ $180/yr** (~$15/mo blended). Implication: cheapest high-value test is asking 10 target users directly before launch.

**Round 1 takeaway:** This audience realistically pays in the **$3–8/mo** band. A **$5 core is well-placed** — at/just below the impulse ceiling and consistent with the competitor cluster. Pushing the core above ~$8–9 risks the 4–6x conversion cliff, especially given free substitutes.

---

## ROUND 2 — Patron / "pay more to support" tier evidence

**Finding 2.1 — Kagi is the strongest positive precedent, and it validates the $25 NUMBER.**
Kagi tiers: **$5/mo** (Starter), **$10/mo** (Professional), **$25/mo Ultimate** — Ultimate explicitly framed as *"Highest level support for Kagi's mission"* plus flagship AI models. Whole company is *"fully funded by its users... you're not just paying for a service; you're helping build a better web."* (kagi.com/pricing, 2026 — Tier 1). **This is a live, successful $5 / $10 / $25 three-tier ladder for a prosumer utility** — nearly identical shape to the founder's hypothesis. **BUT**: Kagi's $25 tier bundles *real incremental value* (frontier LLMs, research mode), not pure altruism.

**Finding 2.2 — Pinboard: pure "support me" ask works, but as MODEST annual pricing, not a premium multiple.**
Pinboard = **$22/yr** basic, **$39/yr** archiving; founder explicitly asks legacy free users to convert "as a token of your support" if getting >$1.80/mo value. (pinboard.in/faq + convert appeal — Tier 1). Note the framing: support = *paying the normal low price at all*, NOT paying 5x. Also flags the **two-population problem**: mixing free-forever and paying users "created feelings of annoyance in the paying group... general confusion about pricing." Relevant cannibalization warning.

**Finding 2.3 — Signal-style "pay to support" only fully works as nonprofit/donation, not commercial tier.**
Signal runs **entirely on donations**, goal = "supported by small donors... modest contributions from people who care." No pricing tiers. (signal.org blog + Wikipedia — Tier 1). `[INFERENCE]`: Pure altruistic support at scale generally requires a mission/nonprofit narrative; a for-profit RSS utility has a weaker "save the world" hook than encrypted messaging or ad-free search.

**Finding 2.4 — "Name your price" / supporter tiers are a proven, simple pattern for indie apps.**
Standard implementation = multiple products ($1.99 → $19.99) mapped to one entitlement; slider guides toward anchors. "Works especially well for indie apps, creator tools, open-source projects, and products where users genuinely want to support ongoing development." (RevenueCat, *name your price paywall*, 2026 — Tier 2). Tip-jar / one-time patron purchases are low-effort to add (Nami ML, Stripe PWYW — Tier 1/2). No public conversion % given → **[DATA GAP]** on what fraction actually pick above-anchor.

**Finding 2.5 — Decoy/anchor effect is real but weaker than folklore, and does NOT guarantee patron uptake.**
Classic Economist result: decoy shifted premium share 32%→84% (Ariely/MIT — Tier 1 experiment). BUT replication work: average lift across 6 categories only **~9 percentage points**; dynamic decoys **~22% relative** lift in SaaS. Effect is context-dependent, not a reliable lever. (Atticus Li replication review; Product Philosophy; Journal of Consumer Research — Tier 1/3). `[INFERENCE]`: A $25 tier will *anchor* $5 to look cheap (useful even if few buy $25), but you cannot bank on meaningful direct $25 revenue.

**Round 2 verdict on $25:** **Viable as a tier, NOT viable as a revenue pillar — and only if it carries real incremental value, not pure altruism.** Kagi proves $5/$10/$25 shape works; it does *not* prove that pure "support us" at 5x core converts. Expected patron uptake `[ESTIMATE, low confidence]`: **~1–3% of paying users**, plausibly lower for a non-mission utility. Treat $25 revenue as a bonus + anchor, not as base-case MRR.

---

## ROUND 3 — Freemium tier design (avoiding cannibalization)

**Finding 3.1 — Freemium free-to-paid benchmark is 2–5%; developer/utility skews to the LOW end.**
Consensus band **2–5%**, median ~2.1%; "GOOD" self-serve = 3–5%, "GREAT" = 6–8%. ~20% of products convert <2.5%; utilities/large-TAM tolerate low rates on volume. (Lenny's/OpenView 2023; Airbridge 2026; Crazy Egg; SaaS Factor — Tier 2/3). Combined with Finding 1.3 (developers convert half), **plan for ~1.5–3% free→paid** `[ESTIMATE]`.

**Finding 3.2 — Hybrid model (usage cap + feature gate) converts best.**
Case study: feature-only 3.8% → hybrid/PLG 7.4% free→paid; freemium-acquired customers showed 127% NRR. (American Impact Review "CloudMetrics" case, 2026 — Tier 3, anonymized). RevenueCat + a16z + Monetizely all recommend **hybrid**: usage limits on the core experience + a few premium-only features → two upgrade triggers ("need more of what I use" + "want something new"). (Tier 2).

**Finding 3.3 — Set the cap at the 70th–80th percentile of free-user usage; free tier should deliver ~40–50% of value.**
Calibrate limits to where "casual use flips into serious use." Dropbox nudges upgrade at ~75% of quota; Slack caps history not messages. Contextual in-app prompts at the limit convert **3–5x** better than generic banners. **Never retroactively move features from free to paid — grandfather instead** (Pinboard's two-population pain, Finding 2.2). (RevenueCat, Monetizely, a16z — Tier 2).

**Finding 3.4 — The "100 subs" cap is the right lever, but the threshold needs validation.**
`[INFERENCE]` A subscription-count cap is a clean usage limit that scales with how "power-user" someone is — exactly the dimension correlated with WTP. **Risk**: if the median target user follows <100 channels, the cap never bites and free cannibalizes paid. YouTube power users often follow **hundreds** of channels, so 100 may be well-placed — but this is the single most important number to validate. **[DATA GAP]**: actual subscription-count distribution of the target audience.

**Round 3 takeaway:** Use a **hybrid free tier**: usage cap (channel count) + import/export as a premium feature gate. Set the cap so ~70–80% of real usage fits free but committed power users clearly overflow.

---

## ROUND 4 — Anchoring, bundling, annual pricing, churn

**Finding 4.1 — Annual plans cut churn 50–80% and lift LTV 25–90%.**
Multiple sources: annual reduces monthly-equivalent churn by ~half to ~80% (Eightx 2026; Zibly/OpenView; Subscription Index — Tier 2/3). Buffer real data: monthly ~7% churn / 14-mo lifetime vs annual ~2.4% / 40-mo lifetime → **~90% higher LTV** for annual. (Zibly citing Buffer — Tier 2).

**Finding 4.2 — Standard annual discount = 15–20% (≈ "2 months free"). Below 15% not compelling; above 25% attracts non-renewers/erodes margin.** Offer annual *alongside* monthly, present as monthly-equivalent, convert monthly users at tenure milestones (e.g., after 3 payments / positive NPS). (Subscription Index, Recurx, Monetizely — Tier 2/3).

**Finding 4.3 — Consumer subscription churn is structurally high (5–12%/mo) vs B2B (1–3%).** One-tap cancel + personal wallet. Top retention lever for consumer = **onboarding + habit formation** (make the feed a daily habit), not customer success. (Subscribe & Conquer — Tier 3). `[INFERENCE]` A daily-open feed reader is well-suited to habit formation — leans on the strongest available consumer-retention lever.

**Finding 4.4 — Value framing.** Feed/productivity tools justify price via time saved + "escape the algorithm/ads" narrative (cf. Kagi's "pay with wallet instead of your life"). `[INFERENCE]` Peristalsis can anchor against the *cost of the algorithm* (attention, wasted time) rather than against $3.99 competitors, but competitor cluster still caps realistic ask.

---

## OUTPUT

### WTP Assessment — what this audience realistically pays

- Realistic core WTP: **$3–8/mo**, with **$5 near the sweet spot** (impulse-buy ceiling ~$20; competitor cluster $3–8). Founder's $5 core is **well-calibrated**.
- Audience is **technical/tinkerer** → converts at ~half the rate of general prosumers, and has **free substitutes** (PocketTube free, self-hosted YT Zero). Expect **low free→paid (~1.5–3%)** `[ESTIMATE]` and price resistance above ~$8–9.
- Biggest WTP threat is **not competitor price — it's "free is good enough."** Paid must deliver a clear power-user overflow (channel count, organization, export/backup, cross-device sync) that free substitutes don't.

### Recommended Price Architecture

| Tier | Monthly | Annual (≈17% off) | Boundary / contents |
|---|---|---|---|
| **Free** | $0 | — | Up to **~50–100 channels** (validate against real distribution), core algorithm-free feed, basic read/unread. Habit-forming, feels complete for casual users. |
| **Premium** | **$5/mo** | **$50/yr** (~$4.17/mo) | Unlimited/high channel cap, **OPML import/export**, cross-device sync, folders/filters, backup. Hybrid gate = usage cap + premium-only features. Target 40–60% of paying users here. |
| **Supporter/Patron** | **$25/mo** | **$250/yr** | Everything in Premium **+ real extras** (early features, priority support, badge/flair, roadmap vote). Framed "fund independent, ad-free tooling." |

Notes:
- **Annual: 15–20% off, offered alongside monthly**, shown as monthly-equivalent. Convert monthly→annual after ~3 successful payments.
- Consider a **one-time "tip jar" / lifetime supporter** option (RevenueCat name-your-price pattern) as a lower-friction alternative to a recurring $25.
- Keep exactly **3 tiers**; the $25 also serves as a **price anchor** making $5 feel trivial (even if few buy it).

### Patron-Tier Viability — will $25 work? (honest)

**Yes as a tier, no as a revenue pillar.**
- Precedent is real: **Kagi runs a live $5/$10/$25 prosumer ladder** where $25 = "support the mission" + flagship features (Tier 1). This directly validates the *shape and the number*.
- **But**: Kagi's $25 carries genuine incremental value (frontier AI). Pure altruistic "pay 5x to support" at scale is a **nonprofit/donation pattern** (Signal), not a commercial tier.
- Expected uptake **~1–3% of paying users** `[ESTIMATE, low confidence — DATA GAP]`. Do **not** model base-case MRR on it.
- **Requirement to make it work:** load the $25 tier with real perks (early access, priority support, cosmetic flair, roadmap influence) + an authentic "independent, no-algorithm, no-ads, funded by users" narrative. Without that, $25 is decoration.

### Cannibalization Risk & mitigation

- **Primary risk:** free tier (and free external substitutes) satisfies most users → conversion stalls. Utility tools + generous free = classic freemium trap.
- **Secondary risk (Pinboard lesson):** mixing free-forever and paying users breeds resentment/confusion if boundary shifts.
- **Mitigations:**
  1. **Hybrid gate**: channel-count cap **+** premium-only features (import/export, sync, backup). Two upgrade triggers.
  2. **Calibrate cap to 70–80th percentile** of real free usage so committed power users overflow — validate the number, don't guess.
  3. **Contextual upgrade prompt at the cap** (3–5x better than banners), e.g. at ~75% of limit.
  4. **Never retroactively paywall** existing free features; grandfather.
  5. Gate the features free substitutes lack (reliable backup/export, cross-device sync) — turn "free is good enough" into "free lacks the thing I now depend on."

### Churn-reduction levers

1. **Annual plans** (15–20% off) — biggest single lever, cuts churn ~50–80%, ~90% LTV uplift (Buffer). Convert at tenure milestones.
2. **Habit formation** — daily-open feed is ideal; drive daily active use (notifications, "new since last visit," morning digest). Top consumer retention play.
3. **Data lock-in via value** (not hostility): backup/archive, folders, watch-later, cross-device — the more organized their feed, the higher switching cost.
4. **Founder-narrative loyalty** (indie, user-funded, no algorithm/ads) — supports the supporter tier and reduces price-driven churn.
5. Contextual re-engagement + win-back on lapse; refund-friendly policy to reduce chargeback/resentment.

### Data Gaps (must validate before committing pricing)

1. **[HIGH] Subscription-count distribution of target audience** — determines whether a ~100-channel cap actually bites. The single most important unknown for cannibalization.
2. **[HIGH] Direct WTP test** — ask 10–20 target power users what they'd pay (cheapest, highest-ROI test; only 23% of startups do it and they price 19% higher).
3. **[MED] Real patron-tier uptake %** — no public conversion data found for "support" tiers on for-profit utilities; the $25 uptake estimate is low-confidence.
4. **[MED] Free-substitute switching friction** — how many of the target audience already self-host YT Zero / use PocketTube free, and what would make them pay.
5. **[LOW] Elasticity between $5 and $8** for this specific audience — worth an A/B once traffic exists.
6. **[LOW] Platform-tax exposure** — if distributed via app stores, 15–30% cut compresses the $5 tier economics materially (Subscribe & Conquer). Confirm web-first billing.

---

## Source list

- Better Launch — SaaS Pricing Strategy for Indie Founders (2026) — Tier 3
- Digital Dashboard Hub — Bootstrapped SaaS $9 vs $29 (2026) — Tier 3
- Getmonetizely — Van Westendorp; Annual billing; Free-tier design (2025/26) — Tier 3
- Stealth Agents — Startup Pricing 2026 (cites OpenView 2025) — Tier 3/2
- Subscribe & Conquer — SaaS vs Consumer Subs (2026) — Tier 3
- Kagi — pricing page + FAQ + Doolpa review (2026) — Tier 1
- Pinboard — FAQ + "request for old-timers" convert appeal — Tier 1
- Signal — "Privacy is Priceless" blog + Wikipedia — Tier 1
- RevenueCat — Freemium tier design; Name-your-price paywall (2026) — Tier 2
- a16z — Three challenges with freemium — Tier 2
- Rework / Atticus Li — Freemium trap / model design (2026) — Tier 3
- Lenny's Newsletter / OpenView — Good free-to-paid conversion (2023) — Tier 2
- Airbridge; Crazy Egg; SaaS Factor — freemium vs trial benchmarks — Tier 2/3
- American Impact Review — CloudMetrics freemium case (2026) — Tier 3
- Eightx; Zibly (Buffer/OpenView); Subscription Index; Recurx — annual vs monthly churn — Tier 2/3
- Atticus Li; Product Philosophy; Outlier Report (Ariely/JCR) — decoy effect — Tier 1/3
