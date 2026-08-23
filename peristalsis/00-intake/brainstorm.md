# Brainstorm — Peristalsis

**Phase:** 2 — Brainstorm
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (exploratory; not yet research-validated)

---

## Purpose

Explore the idea space before locking scope, so we don't prematurely converge on the first framing. Each variation is scored for what's exciting, what's risky, and how it changes the competitive landscape. Convergence recommendation at the end.

---

## V1 — Core: Companion Subscription Feed (the current MVP)

RSS/WebSub feed with groups, favorites, filters, per-channel caps, import/export. Watching stays on YouTube.

- **Exciting:** Already built ~50%. Clear personal validation. Lowest ToS risk (no scraping, companion model). Solves the founder's real pain.
- **Risky:** WTP unproven. Free RSS aggregators technically overlap. No moat vs. Google.
- **Landscape shift:** Competes on *experience quality* ("feels like YouTube") rather than raw capability. Differentiator is UX polish + YouTube-native feel, not features RSS readers lack.

## V2 — "Feed OS" / Power-User Curation Studio

Lean hard into the power user: saved filter presets ("moods"), smart groups (rules: e.g., "tech channels posting >10min this week"), scheduled digests, keyboard-driven, bulk triage, watch-later queues, cross-device sync.

- **Exciting:** Strong premium justification. "Mood watching" becomes a first-class, ownable concept. Hard for a generic RSS reader to match.
- **Risky:** Feature creep for a solo dev. Narrows TAM to hardcore users. Complexity fights the "feels effortless" promise.
- **Landscape shift:** Moves away from RSS readers toward a category of one: "intentional viewing tool." Defensible via depth + taste.

## V3 — Multi-Platform Intentional Feed

Same model, but aggregate subscriptions across YouTube + (later) Twitch, podcasts, Nebula, RSS, newsletters — one intentional "things I follow" feed.

- **Exciting:** Bigger TAM. Reduces single-platform (Google) dependency — a direct answer to the top red flag. "Own your subscriptions" moat grows with each platform.
- **Risky:** Massive scope. Each platform is its own integration + ToS maze. Dilutes the sharp YouTube-power-user wedge. Solo bandwidth killer.
- **Landscape shift:** Competes with Feedly/Inoreader directly (their home turf) instead of carving a YouTube niche. Better as a *later* expansion than a v1.

## V4 — Shareable Curation / Social Discovery

Center the *sharing* feature: public curated group lists ("follow packs"), creators/curators publish their subscription bundles, users clone and merge. Discovery layer on top of curation.

- **Exciting:** Built-in viral/growth loop (shared lists = acquisition). Content-marketing engine ("The 20 channels every X should follow"). Network effects — a real moat candidate.
- **Risky:** Two-sided cold-start. Discovery is a different product than personal curation. Could dilute focus. Moderation surface.
- **Landscape shift:** Turns a utility into a network. If it works, it's the only durable moat in the set. High risk, high reward.

## V5 — "Watch Less, Better" / Digital Wellbeing Angle

Reframe from "manage subscriptions" to "reclaim your attention from the algorithm." Digest emails, intentional sessions, watch budgets, no infinite scroll, calm UX.

- **Exciting:** Rides the digital-wellbeing / anti-algorithm cultural wave. Distinct emotional positioning. PR-friendly narrative.
- **Risky:** "Wellbeing" audience may not pay; wants to use YouTube *less* (smaller LTV). Tension with "toy + dopamine" UX the founder values.
- **Landscape shift:** Competes with Freedom/one-sec/screen-time tools, not RSS readers. Positioning play more than a product change.

## V6 — Browser Extension / Overlay (meet users in YouTube)

Instead of (or alongside) a separate site, inject grouping/filtering directly into youtube.com via extension.

- **Exciting:** Zero context-switch, lowest friction, distribution via Chrome Web Store search. Users never leave YouTube.
- **Risky:** Highest ToS/technical fragility — DOM scraping, breaks on every YouTube redesign (exactly the "constantly breaking" problem the founder rejected in competitors). Store policy risk.
- **Landscape shift:** Competes with existing YT-enhancer extensions (PocketTube, Unhook, etc.) — a crowded, fragile space. Contradicts the founder's core insight.

## V7 — Open-Source Core + Hosted Convenience

Open-source the app (self-host free); monetize hosting + premium sync/sharing. "Own your feed, literally."

- **Exciting:** Trust + community + the ultimate answer to platform dependency ("even if I disappear, you keep it"). Aligns with RSS/indie-web ethos of the likely early audience. Contributor leverage for a solo dev.
- **Risky:** Open source ≠ revenue. Self-hosters rarely convert. Support burden. Hosting margins thin.
- **Landscape shift:** Attracts the technical early-adopter core (Reddit, HN, r/selfhosted) cheaply, but monetization is indirect.

## V8 — "Simplest Possible" (tracer)

Strip to one thing: **a clean chronological, grouped subscription feed that never hides videos.** No caps, no sharing, no filters beyond groups + hide-watched. One-sentence pitch.

- **Exciting:** Fastest to a payable, marketable product. Easiest to explain. Matches the sharpest pain ("I miss videos / can't group").
- **Risky:** Maybe too thin to charge for vs. free RSS readers. Under-monetizes the power user.
- **Landscape shift:** Wedge-first. Land on the core pain, expand into V2 depth once retained.

---

## Analysis — Cross-Cutting Insights

1. **The wedge is V1+V8: "chronological, grouped, nothing-hidden feed that feels like YouTube."** That's the sharp, believable pain and the founder's proven outcome.
2. **The premium justification lives in V2 (power depth) and V4 (sharing).** Free RSS readers can copy features; they can't easily copy *taste + YouTube-native feel + a curation network*.
3. **The moat question (top red flag) is answered — partially — by V3 (multi-platform), V4 (network effects), and V7 (open source / ownership).** None are v1 material, but they inform positioning now: emphasize *portability and ownership* ("import/export/merge, your lists are yours") from day one, because that's the seed of every durable-moat path.
4. **V6 (extension) contradicts the founder's founding insight** (alt tools break constantly on YouTube changes). Avoid as primary; possibly a thin acquisition surface later.
5. **V5 (wellbeing) is a positioning lens, not a separate product** — worth borrowing the "reclaim attention from the algorithm" narrative without adopting the low-LTV wellbeing audience wholesale.

## Convergence — Recommended Refined Idea

**Peristalsis = the intentional YouTube subscription feed that feels like YouTube (V1/V8 wedge) + a clear power-user premium tier (V2 depth) + shareable/portable curation as the growth loop and moat seed (V4 + ownership ethos from V7).**

Concretely:
- **v1 scope (wedge):** grouped chronological feed, nothing hidden, favorites, hide-watched, per-channel caps, import/export. (Mostly built.)
- **Premium (V2):** mood/filter presets, smart groups, cross-device sync, larger sub limits — the paid depth.
- **Growth loop (V4/V7):** shareable/mergeable group lists + "your lists are portable and yours" as both marketing and the beginning of a network/ownership moat.
- **Positioning lens (V5):** "watch what *you* chose, not what the algorithm pushed" — attention-reclaim narrative without the low-LTV wellbeing trap.
- **Explicitly deferred:** multi-platform (V3), extension (V6), full open-source (V7 core) — revisit post-PMF.

This keeps the solo-dev scope tight while pointing every early decision (portability, sharing, feel) toward the few paths that could become a moat.

---

## Flags

**Red Flags:**
- Every durable-moat variation (V3/V4/V7) materially increases scope for a solo, time-constrained founder. Chasing a moat too early could sink the wedge.

**Yellow Flags:**
- V2 premium depth risks feature creep vs. the "effortless" promise — discipline required on what's free vs. paid.
- V4 (sharing/network) has a two-sided cold-start; treat as a growth *experiment*, not a v1 dependency.

## Sources
- Founder intake, 2026-07-23 (primary).
- Analytical judgment [Opinion]; not yet market-validated (see Phase 3).
