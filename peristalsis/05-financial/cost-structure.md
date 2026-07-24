# Cost Structure — Peristalsis

**Phase:** 7 — Financial
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Low-Medium (cost-to-serve anchored on one founder estimate; needs load-test validation)

> All figures **[Estimate]** unless [Data]. Solo bootstrapped side project.

---

## Fixed Costs (monthly, early stage)

| Item | Est. cost/mo | Notes |
|---|---|---|
| Hosting (app + DB) | $20–60 | Laravel/Inertia; single VPS/managed tier early |
| Egress / residential proxy for RSS reliability | $20–100 | Datacenter IPs get penalized on YouTube RSS (see `industry-trends.md`); proxy is a real, recurring line |
| Domain, email, monitoring | $15–30 | uptime monitoring is not optional for an always-on feed |
| **Total fixed** | **~$55–190/mo** | Trivial — the point of the RSS/WebSub architecture |

## Variable / Cost-to-Serve (the key unknown)

- **Founder anchor [Assumption]:** ~$200/day to serve **100,000 new video cards/day** → marginal cost ≈ **$0.002/card**. At that rate, per-user cost is negligible.
- **What's missing from that number:** WebSub subscription renewals (per-channel, ~10-day expiry, no batch → N renewals continuously), polling fallback when push is unreliable, storage of feed/watched-state, and proxy egress. These scale with **distinct channels** and **active users**, not just card volume.
- **[Estimate] cost-to-serve per active user:** likely **$0.02–0.15/mo** at small scale — well under the $5 price. The risk isn't per-user cost; it's **aggregate free-tier load** (a generous free tier × many free users × always-on renewal/polling).
- **Verdict:** marginal cost is genuinely low (validates generous freemium), **but** the free tier is an open-ended commitment that must be **load-tested at 1k/10k users before launch** (open data gap from `confidence-dashboard.md`).

## One-Time Costs

| Item | Est. | Notes |
|---|---|---|
| Legal review (ToS position + privacy docs) | $500–2,500 | **Recommended before paid launch** (Invidious-clause exposure — `raw/regulatory.md`) |
| Privacy policy / ToS drafting | $0–500 | template + review |
| MoR / payments setup (Lemon Squeezy) | $0 | ~5%+$0.50 per txn, taken from revenue |
| Brand/logo (graph-hero visual) | $0–500 | founder can self-do |
| **Total one-time** | **~$500–4,000** | dominated by legal (worth it vs. existential downside) |

## Payment Processing (variable, from revenue)
- Merchant-of-Record ~**5% + $0.50/txn** (Lemon Squeezy) — offloads global VAT/GST [Data, T2]. On a $5 sale ≈ $0.75 fee → **~15% effective take** on the $5 tier (heavy!), ~7% on annual $40, ~4% on $25/mo. **Annual + higher tiers materially improve net margin** — another reason to push them.

## Break-Even

- **Cash break-even is trivially low:** fixed ~$55–190/mo. At $7.25 blended ARPU net of ~10% fees (~$6.53), break-even ≈ **~9–30 paying users** [Estimate]. The base case clears this by ~M2–M3.
- **The real "break-even" is time, not money:** solo founder opportunity cost. The product covers its cash costs quickly; whether it's *worth the founder's hours* is the actual bar (see `projections.md` funding/runway framing).

## Gross Margin
- Software margin **~80–90%** before payment fees, **~70–80% after** (fees are the main drag at low ARPU) [Estimate]. Within the healthy SaaS band (70–85% [Data, benchmarks]) — the low-cost architecture is the model's genuine strength.

---

## Flags

**Red Flags:**
- The generous free tier is an unbounded liability until cost-to-serve is load-tested; "cheap per card" ≠ "cheap in aggregate" with always-on WebSub renewal.

**Yellow Flags:**
- Payment fees take ~15% of the $5 tier — pushes economics toward annual + the $25 tier.
- Reliability/proxy egress is a recurring cost that grows with distinct channels, easy to under-budget.

## Sources
- `01-discovery/market-analysis.md`, `industry-trends.md`, `confidence-dashboard.md`; `raw/regulatory.md`, `raw/geographic-entry.md`; founder cost estimate 2026-07-23; `references/industry-benchmarks.md`.
