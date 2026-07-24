# Positioning — Peristalsis

**Phase:** 4 — Strategy
**Project:** peristalsis
**Date:** 2026-07-23 (rev. 2026-07-23 — founder refinement: "curate your own algorithm")
**Confidence:** Medium (angle sharp; defensibility now partly designed-in via network + graph)

*Using April Dunford's framework (see `references/frameworks.md`).*

---

## Core Idea (founder refinement)

**"Take back control from the algorithm — curate your own."** Three pillars form the winning combo:

1. **Curate your own algorithm** — you decide what surfaces (groups, favorites, filters, caps), not YouTube's ranking. The intentional-viewing frame, made concrete.
2. **Share curated groups + discover novel channels** — publish/clone/merge curated group lists ("follow packs"); a discovery layer for finding channels you'd never get algorithmically. This is the growth loop **and** the moat seed (network effects), promoted from "later experiment" to a core pillar.
3. **A satisfying UI dopamine toy** — signature feature: an **Obsidian-style channel graph cloud** where channels are nodes sized/clustered by how often they're tagged across your groups. Makes curation visible, playful, and shareable — the "toy + dopamine" quality the founder values and generic RSS readers utterly lack.

## 1. Competitive Alternatives
- Tolerate YouTube + "Not Interested" (inertia — #1 enemy).
- Free extensions: Unhook (hide clutter, 1M), PocketTube (organize, 300k) — free, fragile, no cross-device, **no discovery/graph**.
- Self-hosted: YT Zero, FreeTube — DIY only.
- Generalist RSS: Inoreader/NewsBlur — video-as-article, no YouTube feel, no curation-sharing.
- Feedvault — stalled, API-cost trapped.

## 2. Unique Attributes
- RSS/WebSub hosted service — no per-user API cost; enables a **generous free tier** (marginal cost ≈ $0 per founder calc).
- Server-side logic — immune to YouTube DOM redesigns that break overlays.
- Cross-device + TV sync of watched-state.
- Completeness by construction (WebSub + RSS = nothing silently dropped).
- **Channel graph cloud** — tag-frequency-weighted visualization; no competitor has this.
- **Shareable/mergeable curated groups + channel discovery** — a curation *network*, not a solo utility.
- Companion model — playback on youtube.com (no proxy) → lowest ToS/blocking risk.
- Portable / no-lock-in (OPML/channel-ID/feed-URL).

## 3. Value
| Attribute | Customer value |
|---|---|
| Curate-your-own ranking | "I decide what I see — I'm not the algorithm's product" |
| Channel graph cloud | "I can *see* my media diet; curating is satisfying, not a chore" |
| Shareable groups + discovery | "I find great channels from people I trust, and I'm the curator others follow" |
| RSS/WebSub, no API cost | A free tier that won't get pulled offline by quota cost |
| Server-side logic | "It doesn't break when YouTube redesigns" |
| Cross-device/TV sync | "My feed + watched-state follow me everywhere" |
| Completeness | "I never miss a video from a channel I chose" |
| Companion (no proxy) | "It won't get shut down like the scraper apps" |

## 4. Target Customer
**The Overwhelmed Curator** (see `target-audience.md`) — but the sharing/discovery + graph pillars widen the wedge slightly toward the **Info-Junkie/Learner** (topic-based catch-up, PKM overlap, the crowd most likely to enjoy a knowledge-graph view and to publish/follow curated packs).

## 5. Market Category
**New sub-category:** "**intentional YouTube feed — curate your own algorithm.**" The "companion" mechanic (watch on YouTube) remains the ToS/positioning backbone; "curate your own algorithm" is the *emotional* hook layered on top. Not an RSS reader, not a front-end, not "just chronological."

## Positioning Statement
> **For** YouTube power users tired of an algorithm that hides videos and decides for them, **Peristalsis is** an intentional subscription feed **that** lets you curate your own algorithm — group and filter every channel you chose, see your whole media diet as a channel graph, and share curated packs to discover new channels — **unlike** free extensions and generalist RSS readers, **because** it's server-side and RSS/WebSub-powered, so it never breaks, never drops a video, and you keep watching on YouTube.

## Positioning Assessment: **Clear (with a designed-in moat seed)**
The three-pillar combo turns the "no moat" red flag into a strategy: the **graph** is a taste/delight differentiator, and **shareable curation + discovery** is the only path in the research set with real network effects. Still not immune to YouTube absorption — but harder to copy than "chronological," and the network/graph are slow-to-clone.

---

## Flags

**Red Flags:**
- Absorption risk persists (YouTube native grouping/chronological) — but the graph + curation-network are the parts YouTube is *least* likely to replicate.

**Yellow Flags:**
- Sharing/discovery has a two-sided cold-start — powerful if it works, dead weight if it doesn't. Sequence it after core-feed retention.
- The graph is a delighter, not a job-to-be-done; must not delay the completeness/organization core (scope discipline for a solo dev).

## Sources
- `01-discovery/competitor-landscape.md`, `target-audience.md`, `industry-trends.md`, `market-analysis.md`; `00-intake/brainstorm.md` (V2/V4/V7); founder refinement 2026-07-23.
