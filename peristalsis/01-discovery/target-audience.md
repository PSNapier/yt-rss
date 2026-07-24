# Target Audience — Peristalsis

**Phase:** 3 — Discovery (synthesis)
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** Medium (pain & language strongly evidenced; WTP and segment sizing thin)

---

## Primary Persona — "The Overwhelmed Curator"

Mainstream YouTube power user, 28–45, follows **~80–300 channels**. Comfortable installing browser extensions and complaining on Reddit/HN, but **will not run a server**. Checks subscriptions ≥5×/week (a daily repeated moment). Treats YouTube as a primary information/entertainment source and is actively frustrated that it hides videos from channels they deliberately chose.

> *"There are a ton of videos missing, and it is constantly like this. I manually check out a channel and find a bunch of videos that I've missed."* — ResetEra [T1 (verbatim user)]

**Why they'll pay (if anyone does):** the pain is acute, daily, and worsened in 2026; they've already shown intent (installing Unhook/PocketTube/userscripts) but can't/won't self-host, so a hosted freemium account is genuinely their best option — *if* it does what a free extension can't (sync, cross-device/TV, WebSub push completeness, resilience to YouTube DOM changes).

## Secondary Persona — "The Info-Junkie / Learner"

Uses YouTube like a library/RSS reader for education, news, niche deep-dives. Overlaps with the productivity/PKM crowd (Readwise, Notion). Values grouping by topic, catching up on a subject, and "an inbox of unwatched videos." Higher intrinsic WTP for organization; fast-follow segment.

## Anti-Persona — Who NOT to Target (for revenue)

- **Self-hosters / r/selfhosted / degoogle purists.** Loyal and great for launch credibility, but they self-host precisely to *avoid paying* and will use YT Zero/FreeTube. Court them for launch buzz, not as a revenue base.
- **Casual/mobile-only viewers** who don't feel the pain and won't switch from the default app.

## Customer Pain Hierarchy (frequency × intensity)

1. **Missing videos — the feed silently drops uploads from subscribed channels.** The durable, #1, longest-standing pain ("famously unreliable… a decade-old meme… doesn't self-correct"). Anchor here. [T1/T2]
2. **Algorithm/"Most relevant" hijack** of chronological order (spiked Feb 2026 rollout — high intensity but possibly transient if YouTube reverts). [T2]
3. **No organization** — "I just want folders. Or tags." [T2]
4. **Shorts clutter** in the subscription feed. [T2]
5. **No cross-device continuity / watched-state** across sessions. [Opinion, from workaround complaints]

## Jobs To Be Done

- **Functional:** "See *everything* from the channels I chose, newest-first, grouped by topic, without Shorts — and let me catch up on one topic at a time."
- **Social:** "Share/discover curated channel sets; be the person with the good sources."
- **Emotional:** "Feel in control and calm, not manipulated by the algorithm; trust I'm not missing things."

## Language Map (use verbatim in copy)

- "everything from channels I follow" / "only the channels you chose"
- "newest to oldest, like it used to be"
- "an inbox of unwatched videos"
- "no algorithm — not what YouTube *thinks* you want"
- "folders. Or tags." / "catch up on one topic"
- Anti-positioning: "the algorithm overlords," "homepage 2.0"
- **Promise cluster: control · completeness · chronological · calm.**

## Buying Behavior & Trust Signals

- Adopt via **free, instant-payoff** trial; freemium > free-trial for this crowd.
- **Trust signals they need:** no-lock-in/export promise, no Google-password harvesting (public RSS, minimal OAuth scope), "independent, ad-free, user-funded," visible longevity (shutdown trauma from Google Reader/Pocket).
- Objections: "I can do this free with an extension / Feedly folders"; "will this shut down?"; "why pay for something YouTube should do?"

## Where to Reach Them (ranked)

1. **Chrome Web Store** (via a thin companion extension) — where the audience already lands; controllable cold-start via title/keywords. [T2]
2. **SEO/content** on "see all subscription videos / hide Shorts / chronological feed" — evergreen, refreshed each time YouTube degrades its feed. [T2/Opinion]
3. **Reddit** — reply-to-problem-threads (r/youtube, r/google, r/firefox, r/browsers, r/productivity); NOT link posts (permaban risk). [T2/T3]
4. **Show HN + Product Hunt** — one-shot velocity spikes with an RSS/de-Google framing. [T3]
5. **Niche newsletters & productivity/privacy creators** (give-first drip). [T3]

## Demand Validation

- **Usage demand: strong.** Unhook 1M users (+67%/23mo), PocketTube ~300k [Data, T2].
- **Monetizable demand: weak-moderate.** Reach leaders monetize by donation; Feedvault gated launch on ~15 backers; paid YT-management only succeeds bundled into broad tools (Inoreader Pro) [Data, T2/T3].
- **Market-validation score: 6/10** — pain validated, WTP not.

## Data Gaps

1. **Zero direct WTP data** for a paid YouTube-feed SaaS — top validation priority (test with 10–20 power users).
2. **Subscriptions-per-user distribution unknown** — sets the free-tier cap; needs a primary survey.
3. Persona sizes are estimates, not measured.
4. Recency skew: some intensity rides the Feb 2026 rollout; separate durable pain (missing videos) from layout-of-the-month.

## Strategic Connections

- Anchor product + copy on **"never miss a video" (completeness)**, the durable #1 pain, not on chronological (commoditizing — see `industry-trends.md`).
- The hosted-account value props (sync, cross-device/TV, WebSub completeness, DOM-breakage immunity) are the paid justification vs. free extensions (see `competitor-landscape.md`, Phase 6 MVP).
- WTP thinness → lead validation with a direct pricing test before over-building (Phase 8).

---

## Flags

**Red Flags:**
- No evidence the target persona will pay recurring for this; strongest fail-predictor present.

**Yellow Flags:**
- Free extensions serve ~80% of the job at $0 with zero migration.
- Pain intensity partly tied to a possibly-reversible 2026 YouTube change.
- Self-hoster enthusiasm can mislead — loud but low-WTP.

## Sources
- raw/customer-voice.md, target-audience.md, demand-signals.md (2026-07-23).
- ResetEra, Android Authority, DroidWin (user voice) [T1/T2]; Chrome Web Store/chrome-stats [T2]; conversion benchmarks (Lenny's/OpenView) [T2].
