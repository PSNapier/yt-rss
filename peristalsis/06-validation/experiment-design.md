# Experiment Design — Top 3 — Peristalsis

**Phase:** 8 — Validation
**Project:** peristalsis
**Date:** 2026-07-23
**Confidence:** High (method)

---

## Experiment 1 — Willingness-to-Pay (the gate)

**Hypothesis:** ≥30% of qualified "Overwhelmed Curator" users will make a real payment commitment (~$5/mo or annual pre-pay) after experiencing the live feed.

**Method:**
1. Recruit 12–20 qualified users (follow ≥80 channels, frustrated with YT feed). Sources: Reddit reply-threads, existing MVP users, 2nd-degree network. **Weight strangers higher than friends.**
2. 20-min call: watch them import + reach the aha feed. Note reactions verbatim.
3. Present pricing. Offer a **live payment link** (LemonSqueezy) — founding-member annual at a discount.
4. Record who actually enters payment vs. verbal yes.

**Metrics:** real-payment rate; verbal-vs-actual gap; tier chosen; objections (language).

**Success:** ≥3/10 real payments. **Fail:** <3/10 or only "free-yes, paid-no."

**Timeline:** 2 weeks. **Cost:** ~$0.

**Interview script (core questions):**
- "Walk me through how you keep up with YouTube subscriptions today."
- "What's the most annoying part?" *(listen for missing-videos / organization)*
- *(after demo)* "What would you do with this tomorrow?"
- "It's $5/mo, no ads, no tracking, funded by users. Here's the link — want in as a founding member?" *(watch behavior, don't rescue silence)*
- If no: "What would have to be true for this to be worth $5?"

---

## Experiment 2 — Subs-per-user Survey

**Hypothesis:** power users cluster at a channel count high enough that gating *power features* (not sub-count) is the right free/paid line.

**Method:** Tally/Typeform + Reddit poll (r/youtube, r/DataHoarder, r/selfhosted). One core question ("how many YouTube channels do you follow?") + 2 qualifiers (feed frustration; would-organize). Target n≥100.

**Metrics:** distribution (median, 75th/90th pct); correlation of high-count with frustration.

**Success:** clear power-user cluster → informs gate design. **Fail:** flat/low distribution → generous free tier fully cannibalizes; rethink gating.

**Timeline:** 1 week. **Cost:** ~$0.

**Survey questions:**
1. How many YouTube channels do you subscribe to? (`<20 / 20–50 / 50–100 / 100–300 / 300+`)
2. How often does YouTube's subscription feed frustrate you? (1–5)
3. Would you organize subscriptions into groups if it were easy? (Y/N/already try to)
4. *(optional)* Would you pay for a tool that fixed it? (Free-only / ~$5/mo / $25 to support / no)

---

## Experiment 3 — Landing Page + CWS Extension Conversion

**Hypothesis:** a thin CWS companion extension (search-discovered, hands off to web app) converts installs→activated users at a viable rate.

**Method:**
1. Landing page leading on "curate your own algorithm" + graph visual + completeness copy (language from `target-audience.md`).
2. Thin companion extension titled for CWS search ("YouTube Subscriptions Feed — chronological, grouped, no Shorts"). **Handoff only — no overlay.**
3. Instrument: CWS impression→install→signup→activation (TTV<5min).

**Metrics:** install rate; install→signup %; **signup→activation ≥60%**; TTV.

**Success:** activation ≥60% and handoff works. **Fail:** users install extension but don't cross to the web app (handoff broken) → rethink acquisition surface.

**Timeline:** 2–4 weeks (doubles as product work). **Cost:** ~$0 + $5 CWS dev fee.

---

## Templates Provided
- Interview script (E1, above) — use as-is.
- Survey (E2, above) — paste into Tally.
- Landing copy outline (E3): Headline "Curate your own algorithm" → subhead → graph hero image → 3 benefit bullets (complete / grouped / cross-device) → "you keep watching on YouTube" trust line → email capture / install CTA.

---

## Flags

**Red Flags:**
- E1 must use *real payment behavior*; verbal WTP in this space is systematically inflated (everyone agrees it's frustrating; almost no one pays — the core research finding).

**Yellow Flags:**
- Reddit self-promo rules can block survey/recruit posts — use reply-threads and aged accounts.

## Sources
- `06-validation/validation-playbook.md`, `assumptions-tracker.md`; `01-discovery/target-audience.md`, `demand-signals.md`; `02-strategy/go-to-market.md`.
