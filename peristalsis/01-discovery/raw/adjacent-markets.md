# Peristalsis — Adjacent Markets & Whitespace Research

_Research date: 2026-07-23. Analyst: adjacent-markets / whitespace research. Method: 7 web searches, drilled. Sources tiered: **T1** = primary/company/academic; **T2** = reputable press/analyst; **T3** = market-report/aggregator estimate (treat with caution)._

Startup context: **Peristalsis** — freemium SaaS, RSS/WebSub-based YouTube subscription feed (group/favorite/filter subs, watch on YouTube). Solo founder. Wedge = YouTube power users. Expansion candidates: (A) multi-platform aggregation, (B) shareable follow-packs / curation network, (C) open-source + hosted, (D) digital-wellbeing angle.

---

## Adjacent Markets

### 1. Multi-platform feed aggregation ("one feed for everything")

**Size / signal**
- Total RSS-reader market ~**$222.5M (2025)** → projected $401.4M by 2034, 6.8% CAGR. Small, slow-growing. (T3, dataintelo, 2026)
- Category leader **Feedly**: ~**15M registered users**, but only **~$7.3–7.8M ARR**, ~60–66 employees, ~$21.8M valuation. (T3, GetLatka/Growjo, Sep 2025). i.e. leader monetizes <$0.50/registered user/yr → free-to-paid conversion is brutal.
- **Inoreader** (power-user #2, Bulgaria/Innologica): ~**$300K ARR**, 6 employees, ~5.1M monthly visits, skews Korea/Japan. Tiny but profitable-looking solo-ish shop. (T3, LinkedIn/growjo, 2026)
- Feedly Pro passed 60K paying subs back in 2015 (T2, historical). Conversion has always been the ceiling.

**Key players:** Feedly (AI/enterprise), Inoreader (automation/power), NewsBlur (solo, open-source), Feedbin (paid-only $5/mo, powers 3rd-party clients), Readwise Reader (all-in-one read-later + RSS, $96/yr, no free tier), NetNewsWire/Reeder (clients).

**Success/failure lessons**
- **Artifact** (Instagram founders, AI news aggregator) — **shut down Jan 2024, ~13 months**. Only ~444K downloads vs SmartNews' 2M same period. Killed by: (1) competing with free defaults (Apple News / Google News), (2) feature creep — drifted news-reader → Twitter-clone, diluting the wedge, (3) AI/chatbots eroding "secondary news app" demand. Founders' verdict: **"market opportunity isn't big enough."** (T2, TechCrunch/Appfigures, Jan 2024)
- Repeated lesson: aggregation is a **feature people love but won't pay much for**; the leader tops out at single-digit-millions ARR. It is a **lifestyle-business ceiling, not a venture ceiling**.
- Post-Google-Reader (2013) proved the demand is real but the willingness-to-pay is thin and the market doesn't grow fast.

### 2. Curation / sharing networks (shareable lists, follow-packs)

**Size / signal**
- **Nuzzel** (surfaced links shared by your network) — beloved by journalists/VCs, took $5–6M VC, **never reached mass-market ad scale**; pivoted to enterprise media-monitoring, then absorbed by Scroll (2018) → shut down **May 2021** when Twitter bought Scroll. John Gruber: "would have paid $25–50/yr" — but too few would. (T1/T2, blog.nuzzel.com, Business Insider, Daring Fireball)
- **Bluesky starter packs** (academic, T1, ICWSM 2025 / Lancaster / arXiv): 335K+ packs in first 6 months, up to **43% of daily follows** during migration peaks, ~20% of all follows over study window. Members got **+85% followers, +60% posting**. BUT: only **0.95% of users ever created** a pack, **~6.25% were members**, and only **~86K people (of 30M) signed up via a pack (~4% of packs ever used for signup)**. (T1, bluefacts.app / arXiv 2501.11605)
- Caveat from research: packs **reinforce existing large accounts**; deepen popularity inequality. Curation concentrates, doesn't democratize.

**Key players:** Twitter/X Lists (feature, never a standalone business), Nuzzel (dead), Bluesky starter packs (feature inside a network), Goodreads (curation w/ network effects, acquired by Amazon).

**Success/failure lessons**
- Curation is a **growth/onboarding mechanic, not a standalone product or a business**. It solves cold-start for a *host network*; it does not monetize on its own.
- Nuzzel is the cautionary tale: high engagement from a narrow elite ≠ revenue. Curation networks retain a **small, high-value niche** but struggle to monetize or scale.
- Standalone curation has **no moat** — X, Bluesky fold it in as a free feature.

### 3. Self-host / open-source (FreshRSS, Miniflux, Invidious, Piped, NewsBlur)

**Size / signal (audience)**
- **FreshRSS**: ~**15.4K GitHub stars**, 370 contributors, active (release May 2026), AGPL-3.0, PHP. (T1, GitHub, Jun 2026)
- **Miniflux**: ~**9.5K stars**, effectively one core dev (Frédéric Guillot), Apache-2.0, Go. Hosted via PikaPods ~$1.19/mo. (T1, GitHub)
- **NewsBlur**: solo dev (Samuel Clay), 14+ yrs, MIT, open-source **and** hosted. Freemium: free 64 sites → Premium **$36/yr** → Archive **$99/yr** → Pro **$29/mo**. Self-host gives premium free. Cited as a "sustainable indie business." (T1, newsblur.com)
- **Invidious / Piped** (YouTube frontends): Invidious ~18.7K stars. **Strategic warning:** YouTube began aggressive IP-level blocking in 2024; public Invidious instances collapsed from dozens → ~3, Piped ~15. Project now says "host at home." Constant whack-a-mole (403s, IPv6 rotators, CAPTCHAs). (T2/T1, selfhosting.sh, Techrights, GitHub iv-org #4045, 2024–2026)

**Success/failure lessons**
- Self-host audience is **real, loyal, privacy-motivated** (r/selfhosted, XDA) but **small and low-willingness-to-pay** — they self-host precisely to avoid paying. Monetization = a thin hosted tier for the subset who don't want infra hassle ("$3/mo for Samuel to deal with infrastructure nonsense").
- **NewsBlur is the proof-of-concept for Peristalsis' exact model**: solo founder, open-source + hosted freemium, RSS, sustainable (not venture-scale).
- **Invidious/Piped are the red flag for anything touching un-sanctioned YouTube access.** Peristalsis' RSS/WebSub + "watch on YouTube" approach stays on the right side of this — do **not** drift toward proxying/embedding YouTube playback, or inherit the whack-a-mole.

---

## Expansion Path Assessment (ranked for a solo founder)

Ranking criteria: build cost, defensibility, monetization, platform risk, fit with YouTube power-user wedge.

**#1 — (C) Open-source + hosted freemium.** Best risk/reward. NewsBlur/Miniflux prove a solo dev sustains it. Low platform risk (RSS/WebSub is sanctioned). Builds trust + distribution (GitHub stars = free top-of-funnel) with a natural paid tier (hosting convenience, higher limits, sync). Doesn't require winning a market — just a few thousand payers. **Recommended core model, not a separate "expansion."**

**#2 — (A) Multi-platform aggregation (Twitch / podcasts / newsletters / RSS).** Natural adjacency and clearly demanded, BUT the whole category ceiling is low (~$222M market; leader = ~$7M ARR). Risk: **becoming Feedly-lite** and drowning among Feedly/Inoreader/Readwise. Only worth it as **incremental depth for the existing wedge** (YouTube power users who also follow Twitch/podcasts), NOT as a repositioning to "one feed for everything." Artifact's lesson: don't dilute the wedge chasing breadth.

**#3 — (D) Digital-wellbeing angle.** Cheap to add (it's positioning + a few features: no algorithm, chronological, filtering, "time well spent"). Strong narrative fit — Inoreader, Miniflux, XDA all lean on "calmer internet / off the algorithm." Weak as a standalone driver (nobody pays for wellbeing alone), strong as **differentiating message layered on top of #1**. Low cost, low standalone value → use as marketing spine.

**#4 — (B) Curation / follow-packs network.** Worst standalone bet. Nuzzel died; curation monetizes nowhere; incumbents give it away free; Bluesky data shows <1% create and only ~6% engage. HOWEVER — as a **free growth/onboarding mechanic** (shareable "YouTube starter packs" of channels), it's cheap virality and cold-start help, exactly what starter packs did for Bluesky. **Use as acquisition loop, never as the product or revenue.**

---

## Whitespace Opportunities

1. **"NewsBlur for YouTube."** The open-source + hosted freemium indie model is proven for text RSS but **no one owns the YouTube-native version.** Peristalsis' wedge is genuinely uncrowded — Feedly/Inoreader treat YouTube as one of many feed types, not the hero. Defensible via focus + community, not tech.
2. **Power-user filtering/grouping on YouTube specifically.** YouTube's own subscription feed is weak at grouping/filtering/muting. This is the concrete pain incumbents underserve. Deepen here before going broad.
3. **Curation-as-growth, not product.** Shareable channel "packs" (Bluesky-starter-pack mechanic) as a **free viral onboarding loop** into the hosted app. Whitespace = nobody has applied the starter-pack growth trick to YouTube subscription bundles.
4. **The "sanctioned" lane.** Invidious/Piped burn out fighting YouTube. Peristalsis using RSS/WebSub + "watch on YouTube" occupies the **durable, unblocked** middle: algorithm-free control **without** violating YouTube ToS or inheriting the blocking treadmill. That constraint is itself the moat.
5. **Wellbeing positioning as free differentiation** vs. algorithmic YouTube — cheap message layer that resonates with the exact power-user/privacy crowd that self-hosts.

**Where there is NO whitespace:** generic "one feed for everything" (Feedly owns mindshare, ceiling is low), and standalone curation networks (dead category, no moat, no revenue).

---

## Data Gaps

- **No hard willingness-to-pay data for YouTube-feed users specifically.** All conversion data is text-RSS (Feedly ~<0.5% ARPU-per-registered-user). Need direct signal (landing-page/pricing test) that YT power users pay more than text-RSS users.
- **Feedly/Inoreader revenue figures are T3 estimates** (GetLatka/Growjo/LinkedIn), not audited. Directionally fine, not precise.
- **RSS market-size report is T3** (dataintelo) — vendor market-report, treat as order-of-magnitude only.
- **NewsBlur revenue not public** — "sustainable" is inferred from 14-yr survival + pricing, not disclosed numbers. Unknown how many payers a solo YT-focused clone could realistically reach.
- **Bluesky starter-pack data is from a host network** (solves cold-start for Bluesky itself). Unclear how well the mechanic transfers to a standalone SaaS with no social graph to seed.
- **No data on YouTube API/quota cost or ToS risk** for a scaled hosted service pulling many users' subscription feeds — this is a material unquantified operational + platform risk for the hosted model. Needs primary investigation.
- **Twitch/podcast/newsletter aggregation demand from YT users** is assumed, not measured.
