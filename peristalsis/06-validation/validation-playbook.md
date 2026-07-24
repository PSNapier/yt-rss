# Validation Playbook — Peristalsis

**Phase:** 8 — Validation
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** High (method); the point is to *replace* Low-confidence assumptions with data

---

## Ordered Experiments (cheapest/fastest → most expensive)

### E1. Willingness-to-Pay interviews + price commitment *(the gate — do first)*
- **Assumption tested:** target power users will *pay* ~$5/mo (the #1 unknown, `confidence-dashboard.md`).
- **How:** recruit 10–20 people matching "Overwhelmed Curator" (Reddit reply-threads, existing MVP users, personal network). 20-min call. Show the live feed. Ask them to *commit* — a real Stripe/LemonSqueezy pre-order link or annual pre-pay, not "would you?".
- **Measure:** # who actually click-and-pay (or enter card), not verbal yes.
- **Validates:** ≥3/10 commit. **Invalidates:** <3/10, or all "I'd use free but not pay."
- **Time/cost:** 1–2 wk, ~$0.

### E2. Subscriptions-per-user survey
- **Assumption:** where to set the free/paid line without repelling the core user.
- **How:** short survey (Tally/Typeform) to the same audience + a Reddit poll: "how many YouTube channels do you follow?" Aim n≥100.
- **Measure:** distribution (median, 75th/90th pct).
- **Validates:** clear breakpoint where power users cluster → sets the power-feature gate.
- **Time/cost:** 1 wk, ~$0.

### E3. Cost-to-serve load test
- **Assumption:** generous free tier doesn't bleed money (`cost-structure.md` open gap).
- **How:** simulate 1k then 10k users' WebSub subscriptions + polling fallback + storage on staging; measure infra + proxy egress cost.
- **Measure:** $/1k active users/mo.
- **Validates:** free-tier cost per user << $0.50. **Invalidates:** aggregate free load forces a hard cap.
- **Time/cost:** 1–2 wk, ~$50–150.

### E4. Landing-page + CWS-extension conversion test
- **Assumption:** the #1 channel (CWS search handoff) converts (`go-to-market.md` unproven mechanic).
- **How:** ship the landing page + a thin companion extension; measure install→signup→activation.
- **Measure:** CWS impression→install %, install→signup %, signup→activation (TTV<5min) %.
- **Validates:** activation ≥60%; extension→web handoff works.
- **Time/cost:** 2–4 wk (also real product work).

### E5. Show HN / Product Hunt launch (demand + WTP-at-scale)
- **Assumption:** broader audience validates concept AND doesn't uniformly reject paid (the Feedvault failure mode).
- **How:** launch with RSS/de-Google + "curate your own algorithm" framing + the graph as the visual hook.
- **Measure:** signups, paid conversions in launch window, sentiment on pricing.
- **Validates:** positive paid signal + no "ridiculous price" pile-on.
- **Time/cost:** 1 day + polish; one-shot.

### E6. Retention cohort + sharing-loop test *(only after E1 passes)*
- **Assumption:** feed retains weekly, and packs/graph drive referral (the moat bet).
- **How:** instrument WAU + 4-week retention; ship shareable packs/graph OG images; measure share→signup.
- **Measure:** 4-wk retention ≥30–40%; viral coefficient.
- **Validates:** retention holds; k>0.3 on sharing.
- **Time/cost:** ongoing post-launch.

## Sequencing Logic
E1 gates everything (don't build more if no one pays). E2–E3 de-risk the free-tier design in parallel. E4–E5 test acquisition. E6 is post-PMF and only worth it once E1 + retention are green. **Ties to kill-criteria** (`kill-criteria.md`) — each experiment has a fail line.

---

## Flags

**Red Flags:**
- If E1 fails, the honest move is to keep Peristalsis as a free personal tool / open-source it — not to push a paid product against the evidence.

**Yellow Flags:**
- Recruiting unbiased WTP subjects is hard; friends/existing users over-report willingness. Use real payment commitment, weight stranger responses higher.

## Sources
- `01-discovery/confidence-dashboard.md`, `research-gate.md`; `02-strategy/go-to-market.md`, `business-model.md`; `05-financial/*`; `references/frameworks.md`.
