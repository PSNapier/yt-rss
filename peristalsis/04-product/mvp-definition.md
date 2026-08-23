# MVP Definition — Peristalsis

**Phase:** 6 — Product
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (core validated by dogfooding; some scope is new/unbuilt)

---

## Core Hypothesis the MVP Tests

**Primary:** *Power users with 80–300 subscriptions will adopt — and a meaningful minority will pay for — a hosted, reliable, complete, curate-your-own-algorithm subscription feed, when a free extension does ~80% of the job at $0.* (The WTP question from `01-discovery/confidence-dashboard.md`.)

**Secondary:** *The channel graph + shareable packs create enough delight/discovery to drive organic word-of-mouth* (the moat-seed / growth-loop bet).

## Current Build State (~50% done, dogfooded)
Already built: feed pipeline, groups (multi-membership), favorites, watched-state, per-channel unwatched caps, import/export. In progress: WebSub push transition + Data-API-capped handle resolution (roadmap [021]–[023]).

## Must-Have (v1.0 — the completeness + curation core)
1. **Complete subscription feed** — RSS/WebSub ingestion, nothing hidden, chronological. *(built)*
2. **Import subscriptions** — Google OAuth (`subscriptions.list`) **+** OPML/channel-ID/feed-URL (API-independent path). *(import built; ensure non-API path exists — ToS resilience, see `raw/regulatory.md` mitigation #2)*
3. **Groups + favorites + per-channel caps** — curate your own algorithm. *(built)*
4. **Filters** — video/Shorts, watched/unwatched, faves-only. *(built)*
5. **Server-side watched-state = cross-device by default** — same account, any browser. *(built; this is already the core paid differentiator vs. extensions)*
6. **WebSub push ingestion** — completeness + reliability at scale. *(in progress [021]–[023])*
7. **ToS/privacy hygiene** — privacy policy linking Google's, YouTube-ToS link, attribution, one-click data export/delete. *(pre-launch requirement, cheap, closes known C&D triggers)*
8. **Reliability floor** — WebSub renewal + RSS polling fallback + retry (dropped push = the exact "missing video" pain we exist to kill).

## Nice-to-Have (v1.1 — the delight + growth pillars)
- **Channel graph cloud** (Obsidian-style, tag-frequency-weighted) — signature delighter + shareable artifact. *High brand value; not required to test core WTP.*
- **Filter presets / "moods"** — Premium depth.
- **Shareable/mergeable curated packs + discovery** — growth loop (two-sided; gate behind retention).
- **Thin CWS companion extension** (search-acquisition handoff, NOT overlay).

## Explicitly Out of Scope (v1.0 — prevent creep)
- Multi-platform (Twitch/podcasts/newsletters) — post-PMF (brainstorm V3).
- YouTube-overlay extension — the fragility trap (contradicts founding insight).
- Native TV app — stretch; web-responsive first.
- Any recommendation/algorithmic ranking — contradicts positioning **and** raises ToS "recreate browse" risk.
- Selling/aggregating data, ads — off the table (values + ToS).

## Success Criteria (what would validate)
- **Activation:** ≥60% of new signups import subs and reach a populated feed within 5 min (the aha: "videos here YouTube never showed me").
- **Retention:** ≥30–40% weekly-active return at 4 weeks (feed = daily-repeated moment).
- **WTP (the gate):** ≥3/10 target users in a direct test commit ~$5/mo (from `research-gate.md` green condition).
- **Delight/virality:** measurable share actions on graph/packs once shipped (secondary).

---

## Flags

**Red Flags:**
- The MVP's hardest requirement is non-functional: **reliability**. A feed that drops videos fails at its one job, and always-on infra vs. solo bandwidth is the core execution risk.

**Yellow Flags:**
- Cross-device sync (the main paid hook) is "invisible" value — must be made legible in-product or users won't perceive the reason to pay.
- Channel graph is scoped as v1.1, but it's central to brand/marketing — sequencing tension (don't let it delay the WTP test).

## Sources
- `02-strategy/*` (all), `01-discovery/target-audience.md`, `confidence-dashboard.md`, `research-gate.md`; `raw/regulatory.md`; repo `ROADMAP.md` [021]–[023].
