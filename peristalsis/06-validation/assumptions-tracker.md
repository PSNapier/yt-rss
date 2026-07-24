# Assumptions Tracker — Peristalsis

**Phase:** 8 — Validation
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** N/A (tracking artifact — update as experiments run)

---

| # | Assumption | Confidence | How to test | Status |
|---|---|---|---|---|
| A1 | Target power users will **pay ~$5/mo** | **Low** | E1 WTP interviews + real payment commitment | Untested |
| A2 | Blended ARPU reaches ~$7.25 (15%+ pick $25 tier) | Low | E1 tier-choice + launch data | Untested |
| A3 | Free→paid conversion 1.5–3% | Low | E4/E5 live funnel | Untested |
| A4 | Monthly churn ≤8% with annual mix | Low | E6 retention cohort | Untested |
| A5 | Power users follow 80–300 channels (sets free gate) | Medium | E2 survey | Untested |
| A6 | Free-tier cost-to-serve << $0.50/user/mo | Low-Med | E3 load test | Untested |
| A7 | CWS extension→web handoff converts | Low | E4 | Untested |
| A8 | Activation (TTV) achievable <5 min | Medium | E4 instrumentation | Untested |
| A9 | 4-week retention ≥30–40% | Low-Med | E6 | Untested |
| A10 | RSS/WebSub reliable enough that videos aren't dropped | Medium | E3 + prod monitoring | Partial (dogfood n=1) |
| A11 | Companion model + independent-value features clear the ToS bar | Low | Legal review | Untested |
| A12 | YouTube won't ship native grouping/chronological near-term | Low | Monitor YT blog/DSA filings | Untested (unknowable) |
| A13 | Channel graph + sharing drive referral (k>0.3) | Low | E6 | Untested |
| A14 | Problem is real, acute, worsening | **High** | Already evidenced (Phase 3) | **Validated** [Data] |
| A15 | Usage demand exists at scale | **High** | Unhook 1M / PocketTube 300k | **Validated** [Data] |

## Priority Order to Resolve
1. **A1/A2/A3 (WTP + ARPU + conversion)** — resolve first; everything downstream depends on them.
2. **A5/A6 (free-tier design + cost)** — de-risk in parallel; cheap.
3. **A10/A11 (reliability + legal)** — before paid launch.
4. **A7/A8/A9 (funnel + retention)** — during launch.
5. **A12/A13 (absorption + moat loop)** — ongoing monitoring / post-PMF.

## Status Legend
Untested · Testing · Validated · Invalidated. Update after each experiment; mirror changes into `research-gate.md` conditions and `kill-criteria.md`.

---

## Flags

**Red Flags:**
- The two highest-leverage assumptions (A1 WTP, A3 conversion) are both **Low** confidence and **Untested** — the plan is currently built on its weakest points.

**Yellow Flags:**
- A12 (YouTube absorption) is effectively unknowable in advance — can only be monitored, not tested.

## Sources
- `01-discovery/confidence-dashboard.md`, `research-gate.md`; `02-strategy/*`; `05-financial/*`; `06-validation/validation-playbook.md`.
