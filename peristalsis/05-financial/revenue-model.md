# Revenue Model — Peristalsis

**Phase:** 7 — Financial
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Low-Medium (every input is a labeled [Estimate]; WTP unvalidated)

> All figures are **[Estimate]** unless tagged [Data]. Pre-revenue projections for a solo bootstrapped side project; treat as scenario math, not forecast.

---

## Pricing Strategy (rationale in `02-strategy/business-model.md`)

| Tier | Price | Annual | Notes |
|---|---|---|---|
| Free | $0 | — | Generous; gates power features, not sub-count |
| Premium | $5/mo | ~$40/yr (33% off) | ARPU anchor |
| Super-Supporter | $25/mo | ~$250/yr | Economic engine; patron narrative |

**Benchmark check:** B2C/prosumer ARPU healthy range $10–50/mo; free→paid 2–5% [Data, industry-benchmarks]. Peristalsis's **$5 ARPU sits at the low edge** and **1.5–3% conversion is below-norm** — flagged: this is a *low-ARPU, low-conversion* consumer product, so **volume or the $25 tier must carry it**. The $25 tier lifts blended ARPU materially if even a small fraction adopt it.

## Blended ARPU (illustrative mix)
Assume of paying users: 85% Premium ($5), 15% Supporter ($25).
Blended ARPU ≈ 0.85×$5 + 0.15×$25 = **$7.25/mo** [Estimate]. This is the single most leverage-heavy assumption — a 15%→25% supporter shift pushes ARPU to ~$9.

## Year-1 Revenue Projections (base case)

Assumptions: organic growth only; free signups ramp 200→800/mo; 2% free→paid; 8%/mo blended churn; ARPU $7.25.

| Month | Free signups (cum.) | Paying (net) | MRR | Notes |
|---|---|---|---|---|
| M1 | 300 | 6 | ~$44 | launch |
| M3 | 1,300 | ~24 | ~$174 | CWS + SEO warming |
| M6 | 3,500 | ~62 | ~$450 | HN/PH spike absorbed |
| M9 | 6,500 | ~105 | ~$761 | |
| M12 | 10,500 | ~150 | ~$1,088 | **~$13K ARR** |

**Y1 ≈ $13K ARR / ~150 payers** — inside the research SOM band ($6K–$54K, mid ~$20K; see `01-discovery/market-analysis.md`). Base case lands below midpoint because churn (8%) eats a low-ARPU base.

## 3-Year Trajectory (base)

| Year | Cum. free | Payers (EoY) | ARR | Comment |
|---|---|---|---|---|
| Y1 | ~10.5K | ~150 | ~$13K | validation year |
| Y2 | ~35K | ~500 | ~$44K | if retention + supporter mix improve |
| Y3 | ~80K | ~1,000 | ~$90K | approaches PocketTube-scale reach ceiling |

Y3 ~$90K ARR = a solid **lifestyle/indie** outcome for a solo dev; **not** venture-scale (consistent with gate + market analysis).

## Sensitivity Analysis (±30% on key drivers, Y1 ARR)

| Driver | −30% | Base | +30% |
|---|---|---|---|
| Conversion (2%→1.4%/2.6%) | ~$9K | $13K | ~$17K |
| ARPU ($7.25 → $5.08/$9.43) | ~$9K | $13K | ~$17K |
| Churn (8% → 10.4%/5.6%) | ~$10K | $13K | ~$18K |
| Signup volume −30%/+30% | ~$9K | $13K | ~$17K |

**Compounding:** best-case alignment of all four ≈ **~$35–45K ARR Y1**; worst-case ≈ **~$4–6K ARR**. The swing is dominated by conversion × ARPU — i.e. **the WTP question decides everything.** No amount of the other levers rescues a product no one pays for.

---

## Flags

**Red Flags:**
- Every revenue number rests on the single unvalidated assumption (WTP/conversion). If real conversion is <1%, the model produces coffee money, not a business.

**Yellow Flags:**
- $5 ARPU at the low edge of viable; economics depend on the $25 supporter mix, which is itself unproven (no market analog).
- 8% monthly churn on a low-ARPU base severely caps LTV — annual-plan mix must be high.

## Sources
- `02-strategy/business-model.md`, `01-discovery/market-analysis.md`, `confidence-dashboard.md`; `references/industry-benchmarks.md` (B2C prosumer). All projections [Estimate].
