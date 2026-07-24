# Go-to-Market — Peristalsis

**Phase:** 4 — Strategy
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (channels evidenced; conversion at each stage unproven)

---

## Launch Strategy (where / how / to whom first)

- **Beachhead:** United States, as an ungated English funnel (also captures UK/CA/AU/IE/NZ) — highest trial conversion (NA 14.5% vs 7.6–10.2%) and +34% ARPU [Data, T2, see `market-analysis.md`]. English-only; currency display before any translation.
- **First audience:** frustrated Unhook/PocketTube/userscript users who've hit extension limits (no sync, DOM breakage). They already self-identify as having the pain and installing tools.
- **Sequence:** (1) real free tier live + thin CWS companion extension; (2) SEO content seeded; (3) one-shot Show HN + Product Hunt when the product is polished; (4) Reddit reply-drip ongoing.

## First 100 Customers Plan
1. **Chrome Web Store companion extension** titled for search ("YouTube Subscriptions Feed / Chronological / No Shorts") — hand-off to web app, **not** an overlay. CWS internal search = 40–70% of no-ad installs [Data, T2].
2. **Reply-to-problem-threads** on r/youtube, r/google, r/firefox, r/browsers, r/productivity when users complain about the feed (NOT link posts — permaban risk; aged account, 9:1 value ratio).
3. **Show HN** with RSS/de-Google framing; **Product Hunt** launch. Expect one-shot spikes, not durable flow.
4. **Direct outreach** to 10–20 power users for the WTP test (doubles as Phase 8 validation + first paying users).

## Growth Channels (ranked by expected impact × cost)

| Rank | Channel | Cost | Why | Risk |
|---|---|---|---|---|
| 1 | CWS companion extension (search) | Low | Where the audience lands; controllable cold-start | Handoff-extension CWS conversion unproven — test early |
| 2 | Evergreen SEO ("see all subscription videos / hide Shorts / chronological") | Low (time) | Refreshes every time YouTube degrades feed | Keyword volumes inferred, not measured (verify Ahrefs) |
| 3 | Reddit reply-drip | Low (time) | High-intent, targeted | Self-promo bans; slow |
| 4 | Show HN + Product Hunt | Low | Velocity spike + backlinks | One-shot; HN hostile to paid (Feedvault precedent) |
| 5 | Niche newsletters / productivity+privacy creators | Med | Give-first drip; aligned audience | Slow to arrange |
| — | Paid ads | — | **Excluded** — won't pencil at $5/mo (near-zero-CAC constraint) | — |

## Partnerships & Ecosystem
- **Merchant-of-Record** (Lemon Squeezy) — payments + global VAT/GST (see `business-model.md`).
- **Creators/curators** as list-publishers (the V4 shareable-list growth loop) — treat as a later *experiment*, not a launch dependency (two-sided cold-start).
- No YouTube partnership possible/expected.

## Growth Loop (now a core pillar — founder refinement 2026-07-23)
Shareable/mergeable curated group lists ("follow packs") + channel discovery + the **channel graph** as a shareable artifact → shared pack/graph = acquisition + content-marketing engine ("the 20 channels every X should follow") → network/ownership moat seed. This is the designed answer to the no-moat red flag. **Still sequence the two-sided build after core-feed retention holds**, but treat the shareable graph/pack as a first-class marketing surface from launch (screenshots/embeds are inherently viral for a "dopamine toy" visual).

## Timeline & Milestones
| Phase | Milestone | Gate |
|---|---|---|
| Now | Finish MVP (WebSub transition, roadmap [021]–[023]); model cost-to-serve | Free tier sustainable at 1k/10k users |
| +2–4 wk | **WTP test** with 10–20 power users | ≥3/10 commit ~$5/mo → else pause monetization |
| +4–8 wk | CWS extension live; SEO seeded; ToS/privacy hygiene done | First 100 free users; activation <5 min TTV |
| +8–12 wk | Show HN + PH launch; paid tiers on | First paying + supporter-tier signal |
| +3–6 mo | Retention data in; decide on growth-loop experiment | Weekly-active retention holds → build sharing |

## Positioning in Market (recap — see `positioning.md`)
Lead with **completeness + organization + cross-device + companion**, never "chronological" alone. Language verbatim from customers: "everything from channels I follow," "an inbox of unwatched videos," "no algorithm."

---

## Flags

**Red Flags:**
- Every channel is organic/slow; no paid-acquisition lever exists to force growth if organic stalls.

**Yellow Flags:**
- CWS handoff-extension conversion is unproven (the #1 channel rests on an untested mechanic).
- HN/PH are one-shot and historically hostile to paid tools in this space (Feedvault).
- Growth loop (sharing) has two-sided cold-start — keep it an experiment.

## Sources
- `01-discovery/competitor-landscape.md` (GTM), `target-audience.md` (channels), `market-analysis.md` (geo/payments), `industry-trends.md`; `raw/distribution.md`, `raw/competitor-gtm.md` (2026-07-23).
