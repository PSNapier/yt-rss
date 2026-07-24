# Value Proposition — Peristalsis

**Phase:** 4 — Strategy
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (pains/gains strongly evidenced; WTP for the relief unproven)

---

## Value Proposition Canvas

### Customer Profile (The Overwhelmed Curator — see `01-discovery/target-audience.md`)

**Jobs-to-be-done**
- *Functional:* "See everything from the channels I chose, newest-first, grouped by topic, without Shorts — catch up one topic at a time."
- *Social:* "Be the person with the good sources; share curated channel sets."
- *Emotional:* "Feel in control and calm, not manipulated; trust I'm not missing things."

**Pains (ranked)**
1. Missing videos — feed silently drops uploads [Data, T1].
2. Algorithm/"Most relevant" hijacks chronological order [Data, T2].
3. No organization (folders/tags/favorites).
4. Shorts clutter.
5. No cross-device continuity / watched-state.

**Gains**
- Required: completeness (never miss), chronological.
- Expected: grouping, hide-watched, no Shorts.
- Desired: cross-device/TV sync, filter presets/"moods," shareable lists.
- Delighter: "there are videos here YouTube never showed me" (the aha).

### Value Map (Peristalsis)

**Products & Services:** hosted RSS/WebSub subscription feed; groups + favorites + per-channel caps; video/Shorts/watched filters; cross-device/TV sync + watched-state; **channel graph cloud** (tag-frequency-weighted); **shareable/mergeable curated packs + channel discovery**; import/export/merge (OPML/channel-ID/feed-URL).

**Pain Relievers (mapped)**
- Missing videos → WebSub push + RSS completeness ("nothing hidden") — **the anchor**.
- Algorithm hijack → strictly chronological, no ranking, no injected recs.
- No organization → groups (a channel in multiple), favorites, per-channel unwatched caps.
- Shorts clutter → video/Shorts filter.
- No continuity → server-side watched-state synced across web + Android + TV.

**Gain Creators (mapped)**
- Cross-device/TV sync + resilience to YouTube DOM changes → what a free extension structurally *can't* do (the paid justification).
- Filter presets/smart groups → "curate your own algorithm" / "mood watching" as a first-class concept (Premium depth).
- **Channel graph cloud** → makes your media diet visible; curation becomes a satisfying "dopamine toy" (social + emotional jobs) that RSS readers can't touch.
- **Shareable packs + discovery** → "be the person with the good sources" (social job) + find novel channels from trusted curators (the growth loop + moat seed).
- Import/export/merge + no-lock-in → disarms shutdown trauma (Google Reader/Pocket) and the "will this die?" objection.

**Fit:** Relievers hit the top-2 durable pains (completeness, organization); the graph + sharing/discovery create social/emotional gains no competitor offers; sync/cross-device supply the *paid* differentiation free extensions lack. The gap in fit is **price/WTP**, not function.

## One-Sentence Value Prop
**"Take back control from the algorithm — curate your own. Every video from the channels you chose, grouped your way, on every device, mapped as your own channel graph. You keep watching on YouTube."**

## Proof Points & Credibility Signals
- **Architecture proof:** RSS/WebSub runs where API-dependent Feedvault stalled on cost (see `competitor-landscape.md`).
- **Founder proof:** 30-year power user; his own YouTube recs flipped "almost never relevant → almost all relevant" after curating with the MVP (dogfood outcome) [Assumption — n=1, needs external replication].
- **Trust signals to lead with:** no Google-password harvesting (public RSS + minimal OAuth scope), no ads, no tracking, one-click export/no-lock-in, "independent + user-funded."
- **Language to use verbatim** (from `target-audience.md`): "everything from channels I follow," "newest to oldest, like it used to be," "an inbox of unwatched videos," "no algorithm."

## Key Objections & Responses
| Objection | Response |
|---|---|
| "I do this free with an extension / Feedly folders." | Extensions break on YouTube redesigns and can't sync across devices/TV; Peristalsis is server-side + cross-device. |
| "Will this shut down?" | One-click export, OPML/channel-ID import, no lock-in; API is non-load-bearing by design. |
| "Why pay for what YouTube should do?" | YouTube's incentives run opposite (engagement/Shorts/ads); a companion funded by *you*, not advertisers, is the point. |

---

## Flags

**Red Flags:**
- The differentiating gains (sync/cross-device) are real but may not be worth recurring payment to a crowd that pays competitors $0 (donation culture) — the core WTP risk.

**Yellow Flags:**
- Founder outcome proof is n=1; "feels better" is subjective and hard to market.
- Leading on "chronological" is commoditized — lead on completeness + organization instead (see `industry-trends.md`).

## Sources
- `01-discovery/target-audience.md`, `competitor-landscape.md`, `market-analysis.md`, `industry-trends.md` (2026-07-23).
