# Market Analysis — Peristalsis

**Phase:** 3 — Discovery (synthesis)
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (demand well-evidenced; market sizing rests on labeled estimates, WTP thin)

---

## Executive Summary

The pain Peristalsis solves is large, real, and worsening in 2026 — YouTube actively degraded its native subscription feed (removed list view / `?flow=2`, injected a "Most relevant" algorithmic block above chronological subs, interleaved Shorts). Usage demand is validated at scale (Unhook browser extension: **1,000,000 users, +67% in ~23 months** [Data, Chrome Web Store/chrome-stats 2026]). However, **monetizable** demand is the central uncertainty: the highest-reach tools in this space (Unhook 1M, PocketTube ~300k) survive on **donations, not subscriptions**, and the closest paid SaaS twin (Feedvault) stalled its launch on ~15 backers. The market is a **small, defensible niche**, not a venture-scale opportunity: SAM ~$30M [Estimate], realistic solo Year-1 SOM ~$6K–$54K ARR (midpoint ~$20K) [Estimate]. This is a viable indie/lifestyle business, not a fundable startup.

## Market Size

| Layer | Figure | Basis | Confidence |
|---|---|---|---|
| **TAM** | ~135M heavy-subscription YouTube users / ~$300–400M/yr category | 5% of 2.7B MAU [Estimate]; triangulated vs. RSS-reader software market $222–322M (2025) [Data, T3 report-sellers] | Low–Medium |
| **SAM** | ~15M English-speaking frustrated power users / ~$30M/yr | 500M English YT users × ~3% [Estimate] | Low |
| **SOM (Y1)** | ~$6K–$54K ARR, midpoint ~$20K (200–400 payers) | Solo founder + organic channels; matches indie "$1–5K MRR year one" band [Estimate] | Medium |

**Sobering ceiling:** PocketTube — the closest analog (subscription organizer) — reports only **~21.5k daily users** after years [Data, T2/T3]. Revealed demand for the *organizer* job (vs. the *Shorts-blocker* job Unhook serves) is far smaller than the raw complaint volume suggests.

## Growth Trajectory & Maturity

- **Maturity: growing, early-commoditizing.** Basic "chronological + no-Shorts feed" is now a commodity (Unhook, PocketTube, self-hosted YT Zero, Inoreader/NewsBlur native sync all deliver it). [Data/Opinion]
- **Drivers:** ongoing YouTube feed degradation; de-influencing / intentional-consumption culture [Data, T1+academic]; EU DSA mandating non-personalized feed options [Data, T1].
- **Headwinds:** the audience's structural resistance to paying; free good-enough substitutes; incumbent absorption risk.

## Unit Economics Benchmarks (feeds Phase 7)

- Freemium free→paid: **2–5% typical; 1.5–3% realistic here** (technical/tinkerer crowd converts ~half the rate of general prosumers, and free substitutes exist) [Data/Estimate].
- Consumer/prosumer (<$500 ACV) churn: **6–12%/mo** [Data]. Only 5.3% of sub-$10/mo products retain >85% [Data].
- CAC: organic/content only. **Paid ads will not pencil at $5/mo** — must stay near-zero CAC [Estimate].
- LTV: $5/mo customer ≈ **$62**; $25 supporter ≈ **$500** [Estimate]. Economics concentrate in the supporter tier + low-churn annual power users.

## Regulatory Summary (see `industry-trends.md` and raw/regulatory.md)

- **Overall risk: HIGH — driven by YouTube platform ToS, not statute.**
- Critical clause: YouTube API Developer Policies — API Clients "must not recreate the browse experience … without adding significant independent value." This is the exact clause cited in YouTube's **June 2023 cease-and-desist to Invidious** [Data, T2 Vice/TorrentFreak]. Peristalsis is *more* exposed than Invidious because it uses the Data API (handle resolution) and has thus accepted the API ToS.
- Enforcement move #1 = **unilateral API-key revocation** [Data, ToS §3.1].
- GDPR/CCPA + payments (Stripe SAQ-A / Merchant-of-Record) are routine and solvable.
- **Mitigations (now architectural requirements):** make the Data API non-load-bearing (cache handle→ID permanently; support OPML/feed-URL/channel-ID import so revocation isn't fatal); build a documented "significant independent value" record (cross-device sync, filtering, dedup, digests); keep all playback on youtube.com. *Not legal advice — get counsel before paid launch.*

## Geographic Analysis (see raw/geographic-entry.md)

- **Beachhead: United States**, launched as an ungated English funnel also capturing UK/CA/AU/IE/NZ. US has highest trial conversion (14.5% NA vs 7.6–10.2% elsewhere) and +34% ARPU [Data, T2]. India is bigger but wrong for a *paid* beachhead (price sensitivity) [Data].
- **Payments: use a Merchant-of-Record (Lemon Squeezy first, Paddle past ~$500K ARR), not raw Stripe** — non-EU sellers owe EU VAT from the first sale with no threshold; MoR (~5%+$0.50) offloads global VAT/GST [Data, T2]. Confirm MoR approval for a YouTube-adjacent product.
- **Localization: English-only at launch is correct.** Do currency display first (~19–30% lift); translate only when a non-English country hits ~10–20% of signups [Estimate].

## Timing Assessment — Why Now?

**Cautiously favorable for a narrow, paid, power-user companion; unfavorable for a mass free feed replacement.** [Opinion, evidence-backed]

- **Tailwinds:** YouTube feed degraded measurably in H1 2026 (the exact pain, worsened, right before launch); intentional-consumption culture; EU DSA normalizing chronological feeds; RSS/WebSub is free, zero-quota, officially documented.
- **Headwinds:** platform-absorption risk (if YouTube ships native sub-grouping + sticky chronological toggle, the wedge shrinks — the DSA even pressures them to); Artifact cautionary tale (famous founders + capital died on no mainstream pull + feature creep); the wedge "just chronological" is already taken.

## Data Gaps (aggregated)

1. **No hard "YouTube power user" count** — TAM/SAM hinge on labeled estimates.
2. **No direct WTP evidence** for a paid YouTube-feed SaaS — the single most important unknown.
3. **Subscriptions-per-user distribution unknown** — decides whether a free-tier channel cap bites (Phase 8 survey target).
4. **Cost-to-serve at scale unmodeled** (WebSub/polling isn't free) — free tier must be capped or it bleeds money.
5. Market $ figures are Tier-3 report-sellers — directional only; don't use in a pitch without primary sourcing.
6. Google Trends index values couldn't be pulled — trend direction inferred, not quantified.

## Strategic Connections

- The small SAM + brutal WTP (here) means the **$25 supporter tier and annual plans are the economic engine**, not the $5 tier (see `target-audience.md` demand section and Phase 7).
- The HIGH ToS risk makes the grouping/filtering/sync feature depth a **legal necessity, not just product polish** (see `competitor-landscape.md` differentiation and Phase 6 MVP).
- Commoditization of "chronological + no-Shorts" means positioning must rest on the *organizer + completeness + cross-device + no-tracking* jobs, not on chronological alone (see `industry-trends.md`).

---

## Flags

**Red Flags:**
- Monetizable demand unproven; the reach leaders in this space monetize by donation, not subscription. "Useful ≠ paid-for."
- Existential platform dependency + HIGH ToS risk (Invidious-precedent clause); API-key revocation can disable onboarding overnight.
- Market is provably small (PocketTube ~21.5k DAU ceiling); not venture-scale.

**Yellow Flags:**
- Core feature ("chronological + no-Shorts") is commoditizing; differentiation must move up-stack.
- Incumbent absorption risk (YouTube native groups / DSA-driven chronological mode).
- Market-size figures rest on stale/low-tier sources.

## Sources
- raw/market-size.md, raw/trends.md, raw/regulatory.md, raw/geographic-entry.md, raw/demand-signals.md, raw/adjacent-markets.md (this project, 2026-07-23).
- Chrome Web Store / chrome-stats (Unhook, PocketTube) [T2]. Vice/TorrentFreak on Invidious C&D [T2]. YouTube API Developer Policies [T1]. RSS-reader market reports [T3]. Trial-conversion/ARPU benchmarks [T2].
