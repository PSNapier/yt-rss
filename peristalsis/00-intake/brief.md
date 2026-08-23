# Intake Brief — Peristalsis

**Phase:** 1 — Intake
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** High (founder-sourced, primary)

---

## The Idea

**Problem.** YouTube's subscription feed is fundamentally broken for power users:

- Videos cannot be meaningfully sorted or grouped.
- Videos are often shadow-hidden / never surface ("shadow hidden").
- Large subscription lists only surface a slice of what should appear.
- No conscious "mood watching" — the user is at the mercy of the feed and the recommendation algorithm.

**Solution.** Peristalsis — an RSS/WebSub-powered _alternative YouTube subscription service_. Positioning: **"We're your subscription feed; you keep watching on YouTube."** A companion, not a replacement. Capabilities:

- Import/export subscription lists.
- Curate subscriptions into groups (a channel can live in multiple groups).
- Favorite channels so they always stand out.
- Filter feed: videos vs. shorts, watched/unwatched, faves-only, per-channel unwatched cap.
- Share subscription group lists; import/export/merge lists.
- Watch intentionally instead of algorithm-captive. Side effect: a better-trained YouTube algorithm.

**Trigger.** Founder is a 30-year voracious YouTube user who kept fighting the subscription feed as the algorithm changed. Tried other YouTube alternatives — too clunky, constantly breaking. Built an MVP for personal use; reports his own YouTube recommendations flipped from "almost never relevant" to "almost all relevant" after curating a better watch history and no longer missing subscription videos.

**Existing work.** Live MVP (~50% complete) in this repo (`yt-rss`, Laravel + Inertia/Vue). Core built: feed pipeline, groups, favorites, watched-state, per-channel unwatched caps, import/export. Roadmap items [021]–[023] cover the transition from synchronous RSS polling to **WebSub (PubSubHubbub) push** ingestion + Data-API-capped channel-handle resolution.

## The Founder

- 10+ years web development, Laravel specialist.
- 30-year YouTube power user (deep domain empathy + dogfooding).
- Solo founder.
- Side project (time-constrained, low budget/runway).
- **Founder-market fit: Strong** — built it, uses it daily, has a concrete before/after outcome.

## The Market

- **Target customer:** YouTube power users with large subscription counts, frustrated by the feed.
- **Current alternatives / workarounds:** generic RSS aggregators (Feedly, Inoreader), browser extensions, alternative front-ends (Piped/Invidious), manual feed-checking.
- **Direct competitors:** none in this exact format (per founder). Adjacent: RSS readers, extensions, alt front-ends.
- **Geography:** global, English-first web SaaS.

## The Business

- **Model:** Freemium SaaS.
    - Free tier.
    - **$5/mo Premium** — power features (e.g., 100+ subscriptions, import/export).
    - **$25+/mo Super-Supporter** — patron tier.
- **Success horizon:** (not yet quantified — to define in Financial phase.)

## Architecture & ToS Posture (from MVP review)

- Primarily RSS today; transitioning to **WebSub push** — free, no API key, **does not consume YouTube Data API quota**, scales to ~100k+ channels vs. ~1.5–2k ceiling for naive polling.
- **YouTube Data API** used only to resolve @handles → channel IDs, self-capped at 9,500 units/day, with a soft "daily cap reached" message. Not required for core function.
- **No scraping.** Watching happens on YouTube itself (companion model), which is the key argument for ToS compliance and against the "substitute for YouTube" prohibition.

## Hard Questions — Founder's Answers

1. **Complete feeds / shorts filter?** Not worried; if shorts filtering isn't feasible it's dropped. Main goal is keeping shorts out of the video feed.
2. **Moat if Google kills it?** Realistically none. This is _why_ RSS/WebSub was chosen — minimize reliance on Google goodwill (vs. Data API dependence).
3. **Why pay vs. Feedly/Inoreader?** Those feel clunky and unpleasant, killing the "toy + dopamine" experience of YouTube. Peristalsis is the "better mousetrap that feels better to use."
4. **Talked to customers?** Dozens over the years agree the subscription situation is frustrating. (Note: agreement ≠ paid validation.)
5. **What would make you walk away?** Clear precedent Google would kill it immediately, or the current design being against ToS (research suggests it is within ToS). Otherwise founder builds it because he wants to use it.

## Founder Assessment

- **Founder-market fit:** Strong.
- **Motivation durability:** High — intrinsic (builds for own use regardless).
- **Key open validation gap:** willingness to pay (nobody has paid yet; n=1 as a _paying_ user).

---

## Refinement Notes (2026-07-23, post-Phase-4)

Founder sharpened positioning + pricing before Brand phase (not a full pivot — wedge unchanged):
- **Positioning:** "Take back control from the algorithm — **curate your own**." Winning combo = curate-your-own-algorithm + shareable curated groups/novel-channel discovery + a satisfying **Obsidian-style channel graph cloud** (nodes sized by tag-frequency across groups). Sharing/discovery + graph promoted from "deferred experiment" to core pillars = the designed answer to the no-moat red flag.
- **Pricing:** considering all-paid ($1–5) vs. generous-free. Founder cost calc: ~$200/day to serve 100k new video cards/day → marginal cost ≈ $0. Decision (evidence-backed): **generous freemium** — gate power features not sub-count; ~$5/mo Premium; $25 patron as economic engine; reject all-paid ($0-paying audience) and reject $1 (friction trap). Exact price = Phase 8 WTP-test output.

## Refinement Notes (2026-07-23, native mobile)

Founder adds interest in **NativePHP for Mobile** to ship iPhone + Android apps from the same PHP/Laravel codebase.
- **Rationale:** founder-market fit — reuse Laravel expertise, one stack, no separate Swift/Kotlin skillset. Mobile is where YouTube consumption happens; a native-feeling app on phone is arguably *more* aligned with the "feels like YouTube" wedge than a web SaaS.
- **Reality check (deferred, not v1):** NativePHP for Mobile is young/paid and app-store review is a real gate. YouTube-adjacent apps face heavier ToS/store scrutiny (Apple/Google both reject "unofficial YouTube client" framing) — the companion/no-scraping posture matters even more on mobile, and deep-linking out to the official YouTube app (not embedded playback) is the safer pattern. Push/WebSub freshness maps well to mobile push notifications = a genuine premium hook ("new video from a fave" alerts).
- **Scope flag:** adds a third surface (web + iOS + Android) to a solo, time-constrained founder. Treat as **post-PMF expansion / premium differentiator**, not wedge scope. A responsive PWA is the cheaper interim path to "on my phone."

## Flags

**Red Flags:**

- Total platform dependency on YouTube. No moat if Google ships subscription groups or restricts feed/API access. Founder accepts this consciously.

**Yellow Flags:**

- Willingness-to-pay unproven — "it's frustrating" agreement does not equal recurring payment.
- Product is n=1 (built for founder). "Feels better" differentiation is real but subjective and hard to market.
- Free-tier calibration risk: the premium hook (100+ subs) is exactly what the core power user needs; mis-tuned tiers either cannibalize revenue or repel the target user.
- Solo + side-project bandwidth vs. an always-on infrastructure product (WebSub callbacks must be highly available; dropped pushes = silent data loss).

## Sources

- Founder intake interview, 2026-07-23 (primary).
- MVP repository review: `ROADMAP.md`, `ROADMAP_DONE.md` (Tier 1, primary).
