# Research Gate — Peristalsis

**Phase:** 3.5 — Go/No-Go Checkpoint
**Project:** peristalsis
**Date:** 2026-07-23
**Signal:** 🟡 **YELLOW LIGHT — proceed, but gate the build on a willingness-to-pay test**

---

## What the Research Found

**Market.** Small, defensible niche — **not** venture-scale. SAM ~$30M [Estimate, Low confidence]; realistic solo Year-1 SOM ~$6K–$54K ARR (midpoint ~$20K) [Estimate]. The closest analog (PocketTube) tops out at ~21.5k DAU after years [Data, T2/T3]. Viable indie/lifestyle business; not fundable.

**Problem.** Real, acute, worsening. YouTube degraded the subscription feed in H1 2026 (killed `flow=2`, removed list view, "Most relevant" above subs, Shorts interleaved, no opt-out) [Data, T2, High confidence]. "Missing videos" is a durable decade-old #1 pain [Data, T1 verbatim users].

**Demand.** Usage demand **strong** (Unhook 1M users +67%/23mo; PocketTube ~300k) [Data, High]. Monetizable demand **weak/unproven** — reach leaders survive on donations; the closest paid twin (Feedvault) gated launch on ~15 backers and drew "won't pay/ridiculous price" HN pushback [Data, Medium].

**Competition.** Moderately crowded, fragmented, no dominant paid winner. Real danger isn't a rival — it's free good-enough substitutes + user inertia + incumbent absorption. Whitespace exists: hosted, zero-setup, cross-platform, RSS/WebSub-powered (the lane Feedvault vacated on API-cost) [Opinion, evidence-backed].

**Timing.** Cautiously favorable for a narrow paid companion; unfavorable for a mass free replacement. Tailwinds: feed degradation, intentional-consumption culture, EU DSA, free infra. Headwinds: platform absorption, contested wedge, infra fragility, ToS revocation.

**Risk.** Overall regulatory/platform risk **HIGH**, driven by YouTube ToS (Invidious "substitute/recreate browse" clause), not statute. Peristalsis is *more* API-exposed than Invidious (it accepted API ToS) but *less* copyright-exposed (no proxying). Mitigable via API-non-load-bearing architecture + "significant independent value" feature depth + playback staying on YouTube.

## Recommendation: YELLOW

The problem is near-certain and the founder fit is strong. The business case has one dominant open question — **will anyone pay?** — and every indirect signal points weak. This is not a stop, because:
- Founder builds it for himself regardless (intrinsic motivation, sunk-cost-proof).
- The MVP is ~50% built; marginal cost to continue is low.
- The architecture (RSS/WebSub) genuinely differentiates from the stalled API-dependent competitor.

But it is not a green light, because committing serious *new* time to monetization/scale before a WTP signal would be building on the weakest part of the evidence.

### What would need to be true to go green
1. ≥3/10 target power users commit to pay ~$5/mo (or pre-pay) in a direct test.
2. Subscriptions-per-user data confirms the free-tier cap bites the right segment without repelling it.
3. Cost-to-serve modeling shows a free tier that doesn't bleed money at 1k–10k users.
4. No imminent signal of native YouTube subscription-grouping/chronological mode.

## Anti-Patterns Watched
- **Solution-looking-for-a-problem:** Not present — problem is well-evidenced and predates the solution.
- **Building in stealth / no customer contact:** Present-risk — founder has "dozens agree it's frustrating" but zero paid validation. The gate condition directly addresses this.
- **Ignoring unit economics:** Addressed — cost-to-serve flagged as a required model before free-tier commitment.
- **Boiling the ocean:** Controlled — brainstorm already deferred multi-platform/extension/open-source to post-PMF.

---

## Decision Options
- **Continue** to full Strategy → Validation (Phases 4–8), carrying the YELLOW conditions as the spine of Phase 8.
- **Pivot** — none indicated by research; wedge is sound, only monetization is unproven.
- **Stop** — not recommended given low marginal cost + intrinsic founder motivation.

**Awaiting founder decision.**
