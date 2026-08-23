# Confidence Dashboard — Peristalsis

**Phase:** 3 — Discovery (meta-synthesis)
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** N/A (this file rates the confidence of the others)

---

## Overview

The research rests on **solid ground for the problem and shaky ground for the business.** The *pain* (broken subscription feed, missing videos, worsening in H1 2026) and *usage demand* (Unhook 1M users, PocketTube ~300k) are well-evidenced across multiple independent, dated, mostly Tier-2 sources. The *platform-ToS risk* is grounded in Tier-1 policy text + a direct precedent (Invidious). Everything about **whether this is a viable paid business** — market size, willingness-to-pay, conversion, cost-to-serve — is **thin, estimated, or absent.** The single most important number (WTP for a paid YouTube-feed SaaS) has **zero direct evidence** and every proxy points weak (donation-funded leaders, Feedvault's 15-backer bar). Decisions should treat problem-severity as near-certain and revenue-viability as an open, high-priority validation question.

## Claim-Level Confidence

| Claim | Source Tier | # Corroborating | Confidence | Data Age |
|---|---|---|---|---|
| YouTube subscription feed degraded in H1 2026 (killed `flow=2`, list view removed, "Most relevant" above subs, Shorts interleaved, no opt-out) | T2 | 3–4 independent | **High** | 2026-02/03 |
| Usage demand is large (Unhook 1M users, +67%/23mo) | T1/T2 (Chrome store + chrome-stats) | 2 | **High** | 2026-03 |
| "Missing videos" is the durable #1 pain (decade-old, doesn't self-correct) | T1/T2 (verbatim users) | 3+ | **High** | 2026 (pain is long-standing) |
| Platform-ToS is the existential risk; "substitute/recreate browse" clause | T1 (Google policy text) + T2 (Invidious C&D) | 4 (Vice/TorrentFreak/gHacks/GitHub) | **High** | Policy current 2026; precedent 2023 `[STALE but structural]` |
| RSS + WebSub are free, zero-quota, officially documented | T1 (Google docs) | 2 | **High** | 2026 |
| RSS endpoint fragile (outages Dec25–May26, datacenter-IP penalty) | T2 | 2 (wprssaggregator + FreeTube GH) | **Medium** | 2025–26 |
| Willingness-to-pay is weak / donation-culture dominates | T1/T3 (HN + vendor pages) | 3 (Unhook, PocketTube, Feedvault HN) | **Medium** (strong signal, indirect) | 2026 |
| Market is small niche, not venture-scale (PocketTube ~21.5k DAU ceiling) | T2/T3 | 1–2 | **Medium** | 2026 |
| Extensions out-adopt standalone apps 3–1000× in this space | T1/T2 (install counts) | 2 | **Medium** | 2026 |
| Freemium conversion realistic 1.5–3% here | T2 benchmarks + [Estimate] | 2 | **Medium** | 2024–26 |
| TAM ~$300–400M / SAM ~$30M | T3 report-sellers + [Estimate] | triangulated, weak | **Low** | 2024–25 base `[borderline STALE]` |
| Digital-wellbeing market CAGRs | T3 SEO report-sellers | many, conflicting | **Low** (direction only) | 2025 |
| Beachhead = US; NA trial conversion 14.5%, +34% ARPU | T2 | 1–2 | **Low-Medium** | 2026 |
| Direct-competitor retention/churn | — | 0 | **None** (data gap) | — |
| Direct WTP for a paid YouTube-feed SaaS | — | 0 | **None** (top gap) | — |

## Highest-Confidence Findings (build on these)

1. **The problem is real, acute, and worsened right before launch.** Multiple independent 2026 sources + verbatim user voice + 7-figure adjacent tool adoption.
2. **Free infrastructure exists and is documented** (RSS/WebSub, zero quota) — the technical premise is sound.
3. **Platform-ToS is the binding existential risk** — grounded in Tier-1 policy text and the on-point Invidious precedent.

## Lowest-Confidence Findings (verify before betting on them)

1. **All market-size dollar figures** — Tier-3 report-sellers, conflicting, some stale. Do not use in a pitch. [Estimate only]
2. **Willingness-to-pay** — no direct data; every proxy is indirect and points weak.
3. **Digital-wellbeing CAGRs** — direction real, magnitudes untrustworthy.
4. **Subscriptions-per-user distribution** — unknown, yet it sets the free-tier cap that decides both revenue and cost-to-serve.

## Critical Unknowns (could change the strategy)

1. **Will the target persona pay recurring for this?** — the make-or-break. Everything downstream (pricing, tiers, whether to build at all) hinges on it.
2. **Will YouTube ship native subscription grouping + sticky chronological?** — absorption risk that could gut the wedge; no signal either way.
3. **Cost-to-serve at scale** (WebSub renewal, polling fallback, reliability egress) — unmodeled; determines whether a free tier is sustainable or bleeds money.
4. **Does commercial RSS consumption + the companion model actually clear the ToS "significant independent value" bar?** — untested legal ambiguity; no precedent for RSS-based *commercial* feed readers.

## Recommendations — What to Verify First

1. **Run a direct WTP test** (10–20 target power users; price-anchored, not "would you use this"). Highest-priority, cheapest, most decision-relevant. → Phase 8.
2. **Survey subscriptions-per-user** among the target segment to calibrate the free-tier cap. → Phase 8.
3. **Model cost-to-serve** for 1k / 10k users on RSS+WebSub before committing free-tier limits. → Phase 7.
4. **Get counsel** on the ToS "substitute/independent-value" position before paid launch (raw/regulatory.md §6). Cheap relative to the existential downside.
5. **Set an absorption tripwire:** monitor YouTube blog/DSA filings; a native grouping+chronological mode is a documented kill-criterion input. → Phase 8.

---

## Flags

**Red Flags:**
- The single most decision-relevant number (WTP) has **zero direct evidence** and all proxies point weak.
- Market-size figures are Tier-3/stale — the "how big is this" question is effectively unanswered by hard data.

**Yellow Flags:**
- Several "High usage" signals (install counts) are not the same as retained active users — no churn/retention data exists for any competitor.
- Some pain intensity rides a possibly-reversible Feb 2026 YouTube change; separate durable pain from layout-of-the-month.

## Sources
- All raw files in `01-discovery/raw/` (this project, 2026-07-23), synthesized in `market-analysis.md`, `competitor-landscape.md`, `target-audience.md`, `industry-trends.md`.
