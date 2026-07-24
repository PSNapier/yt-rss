# Peristalsis — Startup Design Dossier

**Project:** peristalsis
**Date:** 2026-07-23
**Verdict:** 🟡 **CONDITIONAL (6/10)** — proceed as a lean, validation-first indie project; run the willingness-to-pay test before betting serious hours on the paid business.

---

## Overview

Peristalsis is an **intentional YouTube subscription feed** — a companion (not a replacement) that shows every video from the channels you chose, grouped and filtered your way, on every device, powered by RSS/WebSub instead of the costly YouTube Data API. Positioning: **"take back control from the algorithm — curate your own,"** with two differentiating pillars: an **Obsidian-style channel graph** (your media diet, visualized) and **shareable curated packs + channel discovery** (the growth loop and only real moat seed). You keep watching on YouTube.

## Key Findings

- **Problem is real and worsening** [Data, High]. YouTube degraded the subscription feed in H1 2026 (killed `?flow=2`, "Most relevant" block above subs, Shorts interleaved, no opt-out). "Missing videos" is a durable decade-old #1 pain.
- **Usage demand is large; monetizable demand is weak** [Data]. Unhook (1M users) and PocketTube (300k) prove appetite — but both survive on *donations*, and the closest paid twin (Feedvault) stalled at a 15-backer bar with "won't pay" HN pushback.
- **Market is a small niche, not venture-scale** [Estimate]. SAM ~$30M; realistic Y1 ~$6–54K ARR; Y2–Y3 ~$40–90K in better scenarios. A viable indie/lifestyle business.
- **No durable moat** — total YouTube platform dependency. Real edges: RSS/WebSub cost architecture, exceptional founder-market fit, the graph delighter, and the curation-network seed.
- **Cost is the genuine strength** — near-zero marginal cost (~$200/day per 100k video cards), 70–80% margin, funding-free.

## Strategic Positioning

New sub-category: *"intentional YouTube feed — curate your own algorithm."* Companion mechanic (watch on YouTube) is both the emotional hook and the ToS defense. Lead on **completeness + organization + cross-device + the graph** — never "chronological" alone (commoditized). Generous freemium; the **$25 patron tier is the economic engine**, not the $5 tier.

## Top 3 Risks & Mitigations

1. **No one pays (Critical, Med-High).** → Run the WTP test (E1) with real payment commitments *before* building more; keep free/open-source as fallback; lean on the patron narrative.
2. **YouTube absorbs the wedge (Critical, Med).** → Differentiate up-stack (graph, sharing, cross-device — least-copyable); monitor YT blog/DSA filings as a live tripwire.
3. **API C&D / key revocation (Critical, Med).** → Make the Data API non-load-bearing (global handle-ID cache + OPML/feed-URL import so the app runs without it); ToS/privacy hygiene; legal review before paid launch.

## Confidence Summary (what we know vs. guess)

- **Solid (High):** the problem, its worsening, and large usage demand.
- **Grounded (Medium):** competitive landscape, platform-ToS risk, positioning angle, cost profile.
- **Thin/guessed (Low):** willingness-to-pay, conversion, ARPU, market-size dollars, retention — *every revenue input.* See `01-discovery/confidence-dashboard.md`.

## Anti-Patterns Detected

- **Building in stealth / no paid validation:** present-risk — "dozens agree it's frustrating" but zero paid validation. Directly addressed by the Phase-8 WTP gate.
- **Intrinsic-motivation blind spot:** the founder builds it for himself regardless — great for durability, a liability for honest go/no-go. `kill-criteria.md` forces the "tool vs. business" distinction.
- *Controlled:* boiling-the-ocean (multi-platform/extension/OSS deferred), unit-economics-ignorance (modeled), solution-looking-for-a-problem (problem well-evidenced, predates solution).

## Document Index

- **Intake:** `00-intake/brief.md`, `brainstorm.md`
- **Discovery:** `01-discovery/market-analysis.md`, `competitor-landscape.md`, `target-audience.md`, `industry-trends.md`, `confidence-dashboard.md`, `research-gate.md`, `verification-report.md` (+ `raw/`)
- **Strategy:** `02-strategy/lean-canvas.md`, `value-proposition.md`, `business-model.md`, `positioning.md`, `go-to-market.md`
- **Brand:** `03-brand/mission-vision-values.md`, `tone-of-voice.md`, `brand-personality.md`
- **Product:** `04-product/mvp-definition.md`, `feature-prioritization.md`, `user-journey.md`
- **Financial:** `05-financial/revenue-model.md`, `cost-structure.md`, `projections.md`
- **Validation:** `06-validation/validation-playbook.md`, `risk-analysis.md`, `assumptions-tracker.md`, `experiment-design.md`, `kill-criteria.md`, `scorecard.md`
- **Next steps:** `action-plan-30-days.md`
- **Tracker:** `PROGRESS.md`

---

*Radical-honesty note: this dossier is designed to help a decision, not validate the idea. Peristalsis is a great tool with an exceptional founder fit and an unproven business. The cheap, fast WTP test resolves the one question that matters most.*
