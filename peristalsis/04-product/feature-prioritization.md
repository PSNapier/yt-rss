# Feature Prioritization — Peristalsis

**Phase:** 6 — Product
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (RICE inputs are [Estimate]; effort from a solo-dev lens)

---

## RICE Scores

Reach = share of early users/quarter affected (relative 1–10). Impact: 3=massive…0.25=minimal. Confidence: %. Effort: person-weeks (solo). `RICE = (R×I×C)/E`.

| Feature | Reach | Impact | Conf | Effort (wk) | RICE | Build state |
|---|---|---|---|---|---|---|
| WebSub push + reliability floor (renewal/polling fallback/retry) | 10 | 3 | 0.9 | 4 | **6.75** | in progress |
| Complete chronological feed | 10 | 3 | 1.0 | 0 | **built** | done |
| Groups + favorites + caps | 9 | 2 | 1.0 | 0 | **built** | done |
| Import (OPML/channel-ID non-API path) | 8 | 2 | 0.9 | 1 | **14.4** | partial |
| ToS/privacy hygiene (policy, links, export/delete) | 10 | 2 | 1.0 | 1 | **20.0** | to do |
| Cross-device sync made *legible* in UI | 8 | 2 | 0.8 | 1 | **12.8** | partial |
| Thin CWS companion extension (acquisition) | 9 | 2 | 0.6 | 3 | **3.6** | to do |
| Channel graph cloud (view) | 6 | 1.5 | 0.7 | 3 | **2.1** | to do |
| Filter presets / "moods" (Premium) | 5 | 1 | 0.7 | 2 | **1.75** | to do |
| Shareable packs + discovery | 6 | 2 | 0.4 | 6 | **0.8** | to do |
| Native TV app | 3 | 1 | 0.5 | 8 | **0.19** | defer |

**Read:** cheap-but-critical hygiene (ToS/privacy, non-API import) scores highest by RICE — do first. WebSub/reliability is high-impact and already underway. The graph and sharing score low on pure RICE (effort-heavy, lower confidence) but carry **strategic value beyond RICE** (brand/moat) — sequence them deliberately, not by RICE alone.

## MoSCoW (launch cut)

- **Must:** complete feed *(done)*; groups/favorites/caps *(done)*; import incl. non-API path; WebSub + reliability floor; cross-device sync legible; ToS/privacy hygiene.
- **Should:** thin CWS extension; channel graph (view-only); filter presets.
- **Could:** shareable packs + discovery (behind retention gate); graph-pro (interactive/large); currency display for pricing.
- **Won't (this time):** multi-platform; overlay extension; native TV app; any algorithmic ranking; ads/data features.

## Dependencies
- WebSub reliability **blocks** the completeness promise → blocks WTP test credibility.
- Non-API import **de-risks** ToS revocation → should precede paid launch.
- Cross-device legibility **enables** the Premium pitch → before turning on paid tiers.
- Retention data **gates** the sharing/discovery build (don't build the two-sided loop before core retains).

## Recommended Build Order
1. **Finish WebSub + reliability floor** (in progress) — the completeness core must be trustworthy.
2. **ToS/privacy hygiene + non-API import** (1–2 wk, cheap, unblocks paid launch + de-risks revocation).
3. **Make cross-device sync legible** + wire pricing/MoR — enables the WTP test.
4. **Run the WTP test** (Phase 8) *before* building further.
5. If green → **channel graph (view)** for delight/marketing, then **thin CWS extension** for acquisition.
6. After retention proven → **shareable packs + discovery** (the moat loop).

---

## Flags

**Red Flags:**
- Highest strategic-value features (graph, sharing) score lowest on RICE — risk of either under-building the moat or over-building it before WTP is proven. Sequencing (WTP test as gate) is the mitigation.

**Yellow Flags:**
- CWS extension confidence is low (handoff-conversion unproven) yet it's the #1 channel — validate its conversion early and cheaply.
- Effort estimates are solo-dev gut [Estimate]; reliability work often expands.

## Sources
- `04-product/mvp-definition.md`, `02-strategy/go-to-market.md`, `business-model.md`; `01-discovery/competitor-landscape.md`, `research-gate.md`; `references/frameworks.md` (RICE/MoSCoW).
