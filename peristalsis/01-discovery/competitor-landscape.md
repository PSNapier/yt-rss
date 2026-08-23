# Competitor Landscape — Peristalsis

**Phase:** 3 — Discovery (synthesis)
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (competitors well-identified; traction numbers are vendor claims / ratings counts, not audited DAU/MAU)

---

## Competitive Overview

The space is **moderately crowded but fragmented**, with no dominant paid winner. Threat level: **Medium-High**, but the danger is not a single rival — it's **(1) free good-enough substitutes**, **(2) user inertia**, and **(3) incumbent absorption**. The most important structural insight: **this audience adopts browser extensions 3–1000× more than standalone apps, and pays via donations, not subscriptions.** A standalone paid web SaaS is fighting the grain of how the audience actually adopts and pays.

No competitor has verifiable DAU/MAU — all numbers are vendor claims, install counts, or ratings.

## Competitor Comparison Matrix

| Name | Product | Platform | Pricing | Traction (claimed) | Model | Key Strength | Key Weakness | Grouping/Filter? |
|---|---|---|---|---|---|---|---|---|
| **PocketTube** | Subscription organizer/overlay | Extension (in-page YT) | Free / ~$3.99mo | ~300k users, ~21.5k DAU | Donation/freemium | In-page, native playback, full grouping | Breaks on YT UI changes (top complaint) | Yes |
| **Unhook** | Hide feed/Shorts/recs | Extension | Free (donations) | **1M users, 4.85★** | Donation | Mass adoption; removes clutter | Not an organizer (no grouping) | No |
| **Play** (Marcos Tanaka) | Save/queue + watch | Apple only | Paid app | 4.8★ / 491 ratings, press | Paid app | Best product & press | Apple-only — no web/Android/Windows | Partial |
| **Feedvault** | Standalone chronological grouped feed | Web | ~$99/yr+ (mocked) | Presale (~15 backers), stalled | SaaS | Closest positioning twin | Runs on costly YT Data API; stalled; hostile HN | Yes |
| **YT Zero** | Self-hosted RSS YT feed | Self-host | Free/OSS | 74★ in ~6 wks | OSS | Momentum, dev cadence | Self-host only (DIY users) | Yes |
| **Inoreader** | RSS reader + YT sync | Web/mobile | Free / Pro $7.50–9.99mo | Large (generalist) | SaaS | True auto YT sub sync | Generalist UX; video feels like an article | Yes (folders) |
| **NewsBlur** | RSS reader + YT | Web/mobile | Free / $36/yr | Solo-run, sustainable | SaaS | Proven solo-founder model | Generalist; not YT-native feel | Yes (folders) |
| **FreeTube / NewPipe / Invidious / Piped** | Alt YT front-ends | Desktop/Android/self-host | Free/OSS | Large OSS | OSS | Privacy, no ads | Proxy playback → ToS/blocking treadmill | Some |

## Positioning Map & Whitespace

Two axes: **[in-YouTube overlay ↔ standalone destination]** and **[technical/self-host ↔ zero-setup hosted]**.

- Overlay + zero-setup: **PocketTube, Unhook** (fragile, free).
- Standalone + technical/self-host: **YT Zero, FreeTube, Invidious** (DIY only).
- Standalone + zero-setup hosted: **Feedvault (stalled), Play (Apple-only)**.

**Whitespace = standalone, zero-setup, hosted, cross-platform (web+Android), RSS/WebSub-powered feed for non-technical power users.** Nobody occupies it durably. Feedvault tried and stalled *because it used the YT Data API* (per-user quota + cost forced it offline) — validating Peristalsis's RSS/WebSub architecture as the differentiator.

## Competitor GTM Summary (see raw/competitor-gtm.md, distribution.md)

- **What works:** Chrome Web Store internal-search SEO (40–70% of installs for no-ad extensions; PocketTube ranks by naming itself "YouTube Subscription Manager"); organic Reddit word-of-mouth (Unhook, FreeTube); "alternative/migration" SEO pages (Feedly: 1M→15M owning "Google Reader alternative").
- **Tension:** the #1 channel (CWS) needs an extension surface. Resolution → ship a **thin companion extension** that only ranks in CWS + hands off to the web app (analog: CloudHQ $140K/mo, 14% free→paid via dead-simple extensions). Do **not** build a YouTube-overlay extension (the fragility trap).
- **What's saturated/risky:** HN/PH are one-shot spikes; r/youtube permabans self-promo (reply-to-threads only, aged account, 9:1 ratio).

## Platform Risk Assessment

- **Highest existential risk: YouTube itself.** If it ships native subscription grouping + a sticky chronological toggle, the wedge collapses. No such feature announced; its engagement/Shorts/ad incentives run opposite — but the **EU DSA** pressures it toward a compliant chronological view (could commoditize the core feature in the EU). [Data/Opinion]
- **API-key revocation** can disable handle-resolution onboarding overnight (mitigate via non-load-bearing API design).
- Incumbents **Inoreader/NewsBlur** already ship YT sync and own distribution — they can deepen YT-native features faster than they can be out-marketed.

## Switching-Cost Analysis

**Inertia is the #1 enemy — bigger than any competitor.** The real default is "tolerate YouTube + tap Not Interested." Migration mechanics are trivial (<5 min via OAuth/OPML); the barrier is the *decision* to adopt a destination outside the YouTube tab. RSS-adjacent users also carry shutdown trauma (Google Reader, Pocket 2025) → churn on instability and bait-and-switch. **Disarm with:** instant payoff (sub-5-min TTV), easy import, and an explicit no-lock-in/export promise.

## Strategic Recommendations

1. **Compete on the hosted, cross-device, zero-setup, RSS/WebSub lane** — the one Feedvault vacated and PocketTube can't reach (it's an overlay). This is also the ToS-safest lane (companion, no proxy).
2. **Differentiate up-stack:** grouping depth, favorites, per-channel caps, completeness ("never miss a video"), cross-device/TV sync, no-tracking. Not "chronological" (commodity).
3. **Ship a thin acquisition extension**, not an overlay — reconciles the founder's (correct) aversion with the #1 channel.
4. **Justify the $25 tier explicitly** — no competitor has a $25 analog; it must carry real perks + an "independent, user-funded, no-ads, no-tracking" narrative or it reads arbitrary.

## Vulnerability Analysis — Where to Win

- **Feedvault:** stalled, API-cost trap, hostile HN, no self-host → beatable on architecture + polish + a real free tier.
- **PocketTube:** fragile overlay (breaks on YT updates) → win on stability (server-side logic) + cross-device.
- **Play:** Apple-only → win the web/Android/Windows majority.
- **Inoreader/NewsBlur:** generalist, "video-as-article" UX → win on YouTube-native *feel* (the founder's core insight).

## Data Gaps

- No audited DAU/MAU or conversion data for any competitor.
- Feedvault live status needs monitoring (moving target).
- CWS conversion for a *pure handoff* extension is unproven — test early.
- SEO keyword volumes inferred from frustration signals, not a keyword tool — verify in Ahrefs/Semrush.

## Strategic Connections

- The extension-adoption reality (here) directly shapes GTM and the MVP surface (see `target-audience.md`, Phase 4 GTM, Phase 6).
- The RSS/WebSub cost advantage (here) is what makes a real free tier + sustainable unit economics possible (see `market-analysis.md`, Phase 7).
- "Never miss a video" completeness beats "chronological" because the latter is commoditizing (see `industry-trends.md`, `target-audience.md` pain hierarchy).

---

## Flags

**Red Flags:**
- Free good-enough substitutes (PocketTube free, Unhook 1M, self-hosted YT Zero) directly undercut WTP.
- The audience adopts extensions & pays by donation — structural mismatch with a standalone paid SaaS.

**Yellow Flags:**
- Incumbents (Inoreader/NewsBlur) ship YT sync and own distribution.
- $25 tier has no market analog — needs strong justification.
- Traction benchmarks are unverified vendor claims.

## Sources
- raw/competitors-direct.md, competitors-indirect.md, competitor-gtm.md, competitors-emerging.md, distribution.md (2026-07-23).
- Chrome Web Store / chrome-stats [T2]; Show HN / Product Hunt threads [T3]; The Verge/MacStories on Play [T2]; ICWSM 2025 on starter-packs [T1/T2].
