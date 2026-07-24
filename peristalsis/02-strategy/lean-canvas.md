# Lean Canvas — Peristalsis

**Phase:** 4 — Strategy
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (problem/solution solid; revenue-side rests on unvalidated WTP)

---

## 1. Problem (top 3)
1. **YouTube silently drops uploads from subscribed channels** — the durable #1 pain (see `01-discovery/target-audience.md` pain hierarchy) [Data, T1].
2. **No organization** — can't group/tag/favorite subscriptions; large sub-lists are unusable.
3. **Algorithm + Shorts hijack the subscription feed** — "Most relevant" block above chronological, Shorts interleaved, no opt-out (H1 2026) [Data, T2].

**Existing alternatives:** free extensions (Unhook 1M, PocketTube 300k), self-hosted (YT Zero, FreeTube), generalist RSS (Inoreader/NewsBlur), or tolerate-it-and-tap-Not-Interested (the real default — inertia).

## 2. Customer Segments
- **Primary:** "The Overwhelmed Curator" — mainstream YT power user, 28–45, ~80–300 channels, installs extensions but won't self-host (see `target-audience.md`).
- **Early adopters:** frustrated power users already using Unhook/PocketTube/userscripts who hit their limits (no sync, no cross-device, DOM breakage).
- **Secondary/fast-follow:** "Info-Junkie/Learner" (uses YT as a library; PKM overlap; higher intrinsic WTP for organization).
- **Anti-segment (not for revenue):** self-hosters/degoogle purists (loud, low-WTP).

## 3. Unique Value Proposition
**"Take back control from the algorithm — curate your own. Every video from the channels *you* chose, grouped your way, on every device, visualized as your own channel graph. You keep watching on YouTube."**

Companion, not replacement. Completeness + organization + cross-device + **curate-your-own-algorithm**, powered by RSS/WebSub (not the costly Data API that stalled the closest competitor).

## 4. Solution (mapped to problems + the three positioning pillars)
1. **Never-miss completeness feed** (RSS/WebSub, nothing hidden) → Problem 1.
2. **Curate your own algorithm** — groups + favorites + per-channel caps + video/Shorts/watched filters → Problems 2 & 3.
3. **Cross-device/TV sync + watched-state** (what a free extension structurally can't do) → the paid hook.
4. **Channel graph cloud** (Obsidian-style; nodes sized/clustered by tag-frequency across groups) → the "dopamine toy" delighter + shareable artifact.
5. **Shareable/mergeable curated groups + channel discovery** ("follow packs") → the growth loop + moat seed.

## 5. Channels
- **Inbound (primary):** thin companion extension ranking in Chrome Web Store search (40–70% of no-ad extension installs come from internal search) [Data, T2]; evergreen SEO ("see all subscription videos / hide Shorts / chronological feed"); Reddit reply-to-problem-threads.
- **One-shot spikes:** Show HN + Product Hunt (RSS/de-Google framing).
- **Do NOT** build a YouTube-overlay extension (fragility trap — contradicts founder's core insight).

## 6. Revenue Streams (rev. — generous freemium, see `business-model.md`)
- **Generous free tier** (near-zero marginal cost ≈ $0; ~$200/day for 100k cards [Assumption]). Gate *power features*, not sub-count.
- **~$5/mo (~$40/yr) Premium** — sync, cross-device/TV, filter presets, smart groups, graph-pro, publish/merge packs. LTV ≈ $62 [Estimate].
- **$25+/mo Super-Supporter** — patron; "independent, ad-free, no-tracking, user-funded." LTV ≈ $500 [Estimate]. **Economic engine**, not the $5 tier.
- **Annual plans** to fight 6–12%/mo churn.
- **Rejected:** all-paid ($1–5) — kneecaps organic growth in a $0-paying audience; $1 micro-pricing is a friction trap. Exact price = Phase 8 WTP-test output.

## 7. Cost Structure
- **Near-zero CAC required** — paid ads won't pencil at $5/mo; organic/content only [Estimate].
- **Hosting/infra:** RSS+WebSub is free of API quota but NOT free to operate — WebSub renewal bookkeeping, polling fallback, residential-style egress to dodge datacenter-IP penalty [Data, T2]. **Cost-to-serve unmodeled — must model before setting free-tier cap.**
- **Solo founder time** (side project — the binding constraint).
- One-time: legal review (ToS position), privacy policy, Stripe/MoR setup.

## 8. Key Metrics (AARRR)
- **Acquisition:** CWS extension installs; SEO organic sessions.
- **Activation:** time-to-value <5 min (import subs → see complete feed). The "aha": "there are videos here YouTube never showed me."
- **Retention:** weekly active return (feed is a daily-repeated moment); free→paid at 30/90 days.
- **Revenue:** paid conversion (target 1.5–3% realistic) [Estimate]; supporter-tier take rate; annual mix.
- **Referral:** shared/mergeable group lists (growth-loop experiment, not a v1 dependency).

## 9. Unfair Advantage
- **Weak/honest:** no durable moat (founder accepts this — total platform dependency Red Flag).
- **Real-but-modest edges:** (1) RSS/WebSub architecture that let a solo dev run a *generous* free tier where API-dependent Feedvault stalled on cost; (2) founder-market fit — 30-yr power user, dogfoods daily, concrete before/after; (3) YouTube-native *feel* + the channel-graph delighter (hard for generalist RSS readers to copy); (4) **shareable curation + discovery network** — the one path with real network effects (promoted from experiment to core pillar); portable/ownership ethos seeds it.

---

## Flags

**Red Flags:**
- No durable moat; total YouTube platform dependency (accepted consciously).
- Revenue side (conversion, WTP, LTV) rests on estimates, not validated data.

**Yellow Flags:**
- Cost-to-serve unmodeled — free tier could bleed money if cap is mis-set.
- $25 supporter tier has no market analog; must be justified by narrative + perks or it reads arbitrary.

## Sources
- `01-discovery/market-analysis.md`, `competitor-landscape.md`, `target-audience.md`, `industry-trends.md`, `confidence-dashboard.md` (2026-07-23).
