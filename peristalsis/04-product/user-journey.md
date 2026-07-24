# User Journey — Peristalsis

**Phase:** 6 — Product
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium ([Opinion], grounded in persona + demand research)

---

## End-to-End Journey (The Overwhelmed Curator)

### 1. Discovery
- **Touchpoint:** searches CWS ("chronological YouTube feed / hide Shorts") or hits an SEO page / Reddit reply after YouTube degrades the feed again.
- **Emotion:** frustration + skepticism ("another tool that'll break?").
- **Drop-off risk:** doesn't believe it's different from PocketTube/Unhook. → *Mitigate:* lead with "server-side, never breaks on YouTube redesigns, cross-device."

### 2. Sign-up / Import
- **Touchpoint:** free account; import via Google OAuth (minimal scope) **or** OPML/channel-ID.
- **Emotion:** mild anxiety ("do I trust it with my Google account?").
- **Drop-off risk:** OAuth fear. → *Mitigate:* offer the no-OAuth OPML path prominently; state "public RSS, no password, minimal scope."

### 3. First Populated Feed — **the Aha Moment**
- **Touchpoint:** feed fills with every recent upload, chronological, grouped.
- **Emotion:** relief + surprise — *"there are videos here YouTube never showed me."*
- **This is the value moment.** Target: reach it in **<5 min**. → *Mitigate drop-off:* fast import, sensible default grouping, no empty-state dead-ends.

### 4. Curation (habit-forming)
- **Touchpoint:** builds groups, favorites, sets caps, filters Shorts; opens the **channel graph** and sees their media diet.
- **Emotion:** satisfaction/control — the "dopamine toy" pays off.
- **Drop-off risk:** curation feels like chores. → *Mitigate:* make it playful (graph), fast (bulk actions), optional (works fine ungrouped).

### 5. Regular Usage (retention)
- **Touchpoint:** returns ≥5×/week; catches up by topic/group; watched-state follows across devices.
- **Emotion:** calm competence; trust builds.
- **Drop-off risk:** a single missed/dropped video breaks trust in "completeness." → *Mitigate:* reliability floor is non-negotiable (see `mvp-definition.md`).

### 6. Upgrade Decision
- **Touchpoint:** hits a Premium reason — wants sync legibility, filter presets, or to publish a pack; or simply wants to fund it.
- **Emotion:** "is this worth ~$5?" (the WTP crux).
- **Drop-off risk:** free does enough. → *Mitigate:* make cross-device value legible; frame $25 patron as "fund independence," not "unlock feature."

### 7. Advocacy (growth loop)
- **Touchpoint:** shares a curated pack or a graph screenshot ("the 20 channels every X should follow").
- **Emotion:** pride ("I'm the person with the good sources").
- **Drop-off risk:** no easy/attractive share surface. → *Mitigate:* make packs + graph inherently shareable (OG images, embeds).

## Journey Map (compact)

| Stage | User goal | Emotion | Key risk | Metric |
|---|---|---|---|---|
| Discovery | Find a feed that won't break | Skeptical | Seems like existing tools | CWS/SEO CTR |
| Import | Get my subs in safely | Anxious | OAuth fear | Import completion % |
| **Aha** | See everything | **Relief/surprise** | Empty/slow first feed | TTV <5 min |
| Curation | Organize my way | Satisfied | Feels like chores | Groups created |
| Usage | Keep up calmly | Calm/trusting | One dropped video | WAU / 4-wk retention |
| Upgrade | Decide if worth paying | Uncertain | Free is enough | Free→paid % |
| Advocacy | Share good taste | Proud | No share surface | Shares/pack clones |

## The Aha Moment (explicit)
**"There are videos here that YouTube never showed me."** First populated, complete feed. Everything before it is friction to minimize; everything after depends on reliability keeping the promise true.

---

## Flags

**Red Flags:**
- Trust is binary on completeness — one visibly dropped video can end retention. The journey's success rests on the reliability floor.

**Yellow Flags:**
- The upgrade step converts an audience that pays $0 elsewhere — the whole journey can succeed on engagement and still fail on revenue (the WTP gap).
- OAuth anxiety could throttle the import step; the non-API path must be first-class, not hidden.

## Sources
- `01-discovery/target-audience.md` (persona, aha language, buying behavior), `demand-signals.md`; `02-strategy/value-proposition.md`, `positioning.md`; `04-product/mvp-definition.md`.
