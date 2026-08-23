# Business Model — Peristalsis

**Phase:** 4 — Strategy
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Low-Medium (structure sound; every unit-economics input is an estimate pending validation)

---

## Revenue Model (rev. 2026-07-23 — founder pricing input)

**Freemium SaaS, generous free tier — chosen deliberately over all-paid.** Rationale below.

| Tier | Price | Gates | Purpose |
|---|---|---|---|
| **Free** | $0 | **Generous** — full core feed, groups, favorites, hide-watched, graph (view). Cap on *power features*, not aggressively on sub-count | Acquisition + word-of-mouth + fuel for the sharing/discovery loop; load-bearing for a donation-culture audience |
| **Premium** | **~$5/mo (~$40/yr)** | Cross-device/TV sync, filter presets/"moods," smart groups, graph-pro (interactive/large), publish/merge shareable packs | Functional paid hook |
| **Super-Supporter** | **$25+/mo** | All Premium + patron badge, "independent, user-funded, no-ads, no-tracking" narrative, early features | **Economic engine** — where donation-culture money actually lands |

Push **annual** to fight 6–12%/mo churn. **Exact price + gate lines = Phase 8 WTP-test output, not a guess now.**

### Why generous-freemium, not all-paid or $1 (evidence)
- **Marginal cost ≈ $0.** Founder calc: ~$200/day to serve 100k new video cards/day [Assumption — verify in Phase 7 cost-to-serve]. Near-zero marginal cost argues for maximizing reach via a generous free tier (Anderson, *Free*).
- **All-paid kneecaps the only growth engine.** This audience pays competitors $0 (Unhook/PocketTube donation-funded); HN hostile to paid (Feedvault). A paywall throttles organic/CWS/Reddit acquisition (see `demand-signals.md`).
- **$1 base is a trap.** Payment friction ≈ same at $1 and $5, revenue 1/5. If charging, charge ~$5.
- **"Monetize elsewhere" levers are closed:** no ads (ToS + anti-tracking audience), no data sale (positioning + ToS). So "elsewhere" collapses to donations/patron + premium features = this freemium shape.
- **Revenue concentrates in the $25 supporter tier + annual power users**, not the $5 tier (see `market-analysis.md`). The generous free tier is a *distribution* decision; the supporter tier is the *revenue* decision.

**Free-tier design (not a hard sub-cap):** because cost is near-zero, gate on *power/convenience* (sync, sharing-publish, graph-pro), not on how many channels a user can follow. This avoids repelling the core power user (whose whole problem is having many subs) while still giving clear paid reasons. **Validate the exact free/paid line with the subs-per-user survey + WTP test** (Phase 8).

## Unit Economics (all [Estimate] — see `market-analysis.md`)

| Metric | Value | Basis |
|---|---|---|
| Free→paid conversion | **1.5–3%** | Technical/tinkerer crowd converts ~½ general prosumer rate; free substitutes exist [Data/Estimate] |
| Monthly churn | **6–12%** | Consumer/prosumer <$500 ACV band [Data]; only 5.3% of sub-$10 products retain >85% |
| CAC | **~$0 target** | Paid ads don't pencil at $5/mo; organic/content/CWS-search only |
| LTV — $5 tier | **~$62** | $5 × ~12.4mo effective life [Estimate] |
| LTV — $25 tier | **~$500** | $25 × longer patron life [Estimate] |
| Realistic Y1 SOM | **~$6K–$54K ARR** (mid ~$20K) | Solo + organic; indie "$1–5K MRR yr one" band |

**Cost-to-serve (the gap):** RSS/WebSub avoids API quota cost but incurs WebSub renewal ops, polling fallback, and residential-egress to dodge datacenter-IP penalties [Data, T2]. **Unmodeled.** A free tier without a cost model is an open-ended liability. Model 1k/10k-user cost before committing (Phase 7).

## Scalability

- **Technical:** RSS/WebSub scales to ~100k+ channels vs. ~1.5–2k for naive polling; handle→ID resolution cached globally/permanently so Data API quota scales with *distinct channels*, not users → likely never hits the 9,500 self-cap (see `raw/regulatory.md` mitigation #1).
- **Business:** near-zero-CAC organic growth is slow but cheap — fits a bootstrapped side project, not a blitzscale.
- **Bottleneck:** solo founder bandwidth vs. always-on infra (dropped WebSub pushes = silent data loss = the exact "missing videos" pain the product exists to kill). Reliability is existential to the value prop.

## Dependencies & Key Partnerships

- **YouTube (existential):** RSS/WebSub endpoints + Data API for handle resolution. Mitigate via API-non-load-bearing design + OPML/channel-ID import (operate even if key revoked).
- **Payments:** **Merchant-of-Record (Lemon Squeezy first, Paddle past ~$500K ARR)** — non-EU seller owes EU VAT from first sale; MoR (~5%+$0.50) offloads global VAT/GST (see `market-analysis.md`). Confirm MoR approves a YouTube-adjacent product. Fallback: Stripe Checkout (SAQ-A) + manual VAT.
- **Hosting:** residential-style egress / proxy for RSS reliability.
- **No revenue-share or platform partnership with YouTube** exists or is expected.

## Model Viability Judgment (feeds final dashboard)

**RISKY.** Unit economics *can* work for a lifestyle/indie business under reasonable-but-optimistic assumptions (near-zero CAC, supporter-tier concentration, annual plans controlling churn). They **break** if: WTP proves as weak as proxies suggest, or free-tier cost-to-serve isn't contained. Not broken, not clearly viable — hinges on two unvalidated inputs (WTP, cost-to-serve).

---

## Flags

**Red Flags:**
- Revenue depends on converting an audience that pays direct competitors $0.
- Cost-to-serve unmodeled — free tier could be an unbounded liability.

**Yellow Flags:**
- Economics lean on the $25 supporter tier, which has no market analog (adoption unknown).
- Churn band (6–12%/mo) is brutal; annual-plan mix must be high or LTV collapses.
- MoR approval for a YouTube-adjacent product not confirmed.

## Sources
- `01-discovery/market-analysis.md`, `demand-signals.md` (raw), `competitor-landscape.md`; `raw/regulatory.md`, `raw/geographic-entry.md` (2026-07-23).
