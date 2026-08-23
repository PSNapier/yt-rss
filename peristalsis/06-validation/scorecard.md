# Idea Scorecard — Peristalsis

**Phase:** 8 — Validation
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium

---

| Dimension | Score (1-10) | Rationale |
|---|---|---|
| **Problem severity** | 8 | Real, acute, worsening in H1 2026 (YT killed `flow=2`, "Most relevant" above subs, Shorts injected). "Missing videos" is a durable decade-old #1 pain [Data, High]. |
| **Market size** | 4 | Small niche, not venture-scale. SAM ~$30M [Estimate, Low]; PocketTube ceiling ~21.5k DAU; Y1 SOM ~$6–54K ARR. Fine for indie, not fundable. |
| **Competitive advantage** | 4 | No durable moat (platform-dependent). Real-but-modest edges: RSS/WebSub cost architecture, founder-fit, graph delighter, sharing-network *seed*. Absorbable by YouTube. |
| **Feasibility** | 7 | ~50% built, dogfooded, low-cost architecture, solo-runnable. Docked for reliability-at-scale + always-on infra vs. solo bandwidth. |
| **Business model clarity** | 5 | Model is clear (generous freemium + $25 patron engine); *viability* is not — hinges on unvalidated WTP + low $5 ARPU + high churn. |
| **Founder-market fit** | 9 | 30-yr power user, built it, uses daily, concrete before/after outcome, Laravel-expert. Among the strongest possible for this problem. |
| **Timing** | 6 | Genuine tailwinds (feed degradation, intentional-consumption, DSA, free infra) offset by absorption risk + contested wedge + no funding wave. |
| **Overall** | **6** | Conditional. Strong problem + founder fit; weak market size + moat + unproven monetization. |

## Verdict — 🟡 CONDITIONAL (6/10)

**Proceed, but as a lean, time-boxed, validation-first indie project — not a venture, and not a big paid bet before the WTP test.**

The honest picture: Peristalsis is a **near-certain great personal/free tool** and an **unproven business.** The problem is real and the founder fit is exceptional, but three things cap it: (1) the market is provably small, (2) there's no durable moat against YouTube absorption, and (3) — most decisively — **every willingness-to-pay signal in the research points weak** (donation-culture leaders, Feedvault's 15-backer bar, HN price hostility).

What makes CONDITIONAL the right call rather than NO-GO: the **downside is capped** (no external capital, ~hundreds/mo costs, founder builds it anyway) and the **upside is a real indie income** (~$40–90K ARR by Y2–Y3 in better scenarios). That asymmetry justifies proceeding — *if disciplined*.

**The conditions that must hold (else stop the paid ambition):**
1. **E1 WTP test passes** (≥3/10 real payments) — the master gate. Do this before building more.
2. Free-tier cost-to-serve stays negligible at 10k users (load test).
3. Reliability floor holds — a feed that drops videos has no reason to exist.
4. No native YouTube grouping/chronological signal.

Build order: finish reliability + ToS/API-independence hygiene → run E1 → only then invest in the graph, extension, and sharing loop. Treat the founder's intrinsic motivation as fuel for durability but guard against it masking a "great tool, no business" outcome (see `kill-criteria.md`).

**One-line:** *Great tool, exceptional founder fit, small market, no moat, unproven monetization — validate WTP cheaply and fast before betting hours on the paid business.*

---

## Flags

**Red Flags:**
- Monetization unproven and all proxies weak — the deciding risk.
- No moat + small market caps the ceiling even if WTP works.

**Yellow Flags:**
- Intrinsic founder motivation can obscure honest go/no-go on the *business* (vs. the tool).
- Scores on market/moat/model would need active validation to move; they're currently evidence-based estimates.

## Sources
- All Phase 1–7 deliverables; `01-discovery/research-gate.md`, `confidence-dashboard.md`; `references/honesty-protocol.md` (scoring guide).
