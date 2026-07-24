# Risk Analysis — Peristalsis

**Phase:** 8 — Validation
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium

*Likelihood: High >60% / Med 20–60% / Low <20%. Impact: Critical / Major / Moderate / Minor (see `references/frameworks.md`).*

---

## Risk Matrix

| # | Risk | Likelihood | Impact | Quadrant |
|---|---|---|---|---|
| R1 | **No one pays** (WTP as weak as proxies) | Med-High | Critical | Address now |
| R2 | **YouTube absorbs the wedge** (native grouping + sticky chronological) | Med | Critical | Monitor + contingency |
| R3 | **API-key revocation / C&D** (Invidious-clause) | Med | Critical | Address now (architecture) |
| R4 | **RSS/WebSub reliability failure** (dropped pushes = missing videos) | Med-High | Major | Address now |
| R5 | **Free good-enough substitutes** cap conversion | High | Major | Accept + differentiate |
| R6 | **Solo bandwidth** can't sustain always-on infra + roadmap | Med-High | Major | Address now |
| R7 | Free-tier cost-to-serve balloons | Low-Med | Major | Contingency (load test) |
| R8 | Sharing/discovery loop never ignites (two-sided) | Med | Moderate | Accept (it's upside) |
| R9 | GDPR/behavioral-profile mishandling | Low | Major | Standard playbook |
| R10 | Brand name SEO/spelling friction | High | Minor | Accept + manage |

## High-Priority Risks — Mitigation & Early Warning

### R1 — No one pays *(the existential business risk)*
- **Mitigation:** run E1 WTP test *before* further investment; keep free personal-tool / open-source as the fallback path; lean revenue on the $25 patron narrative where donation-culture money lands.
- **Early warning:** <3/10 pay in E1; launch-window conversion <0.5%; "won't pay/ridiculous" sentiment (Feedvault echo).

### R2 — YouTube absorbs the wedge
- **Mitigation:** differentiate up-stack (graph, sharing/discovery, cross-device) — the parts YouTube is least likely to copy; companion positioning; don't compete on "chronological" alone.
- **Early warning:** YouTube blog / DSA-compliance filings announcing native subscription grouping or a sticky chronological toggle. **Set a literal watch/alert.**

### R3 — API revocation / C&D
- **Mitigation (architectural, do before paid launch):** cache handle→ID globally/permanently; first-class OPML/channel-ID/feed-URL import so the app runs *without* the API; ToS/privacy hygiene (policy links, attribution); keep playback on YouTube; get legal review.
- **Early warning:** quota warnings, API console notices, any YouTube legal contact. Have the API-independent mode ready to flip on.

### R4 — Reliability failure
- **Mitigation:** WebSub renewal automation + RSS polling fallback + retry + dead-letter monitoring; residential-style egress to dodge datacenter-IP penalty; alert on stale channels.
- **Early warning:** rising fetch-failure rate; user reports of missing videos (trust-killer — treat as sev-1).

### R6 — Solo bandwidth
- **Mitigation:** ruthless scope (MoSCoW `Won't` list); ship reliability before features; time-box (see `projections.md`); automate ops.
- **Early warning:** reliability slipping while chasing features; roadmap stalling; founder burnout signals.

---

## Flags

**Red Flags:**
- Three separate **Critical** risks (R1 no-pay, R2 absorption, R3 revocation) — any one can end the *business* (not the personal tool). R1 is most probable; R3 is most mitigable; R2 is least controllable.

**Yellow Flags:**
- R4 and R6 interact viciously: reliability is the one thing that must not slip, and it's the thing a solo founder is most likely to under-resource.

## Sources
- `01-discovery/market-analysis.md`, `industry-trends.md`, `confidence-dashboard.md`, `research-gate.md`; `raw/regulatory.md`; `02-strategy/*`; `05-financial/*`.
