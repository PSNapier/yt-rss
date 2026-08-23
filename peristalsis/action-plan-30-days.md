# 30-Day Action Plan — Peristalsis

**Project:** peristalsis
**Date:** 2026-07-23
**Goal of the month:** answer the one question that decides everything — **will target users actually pay?** — while keeping build effort minimal until that's known.

---

## Week 1 — Customer Discovery + Recruiting

**Objective:** line up 12–20 qualified WTP-interview subjects and launch the subs-per-user survey.

1. Write a 1-paragraph recruit blurb; post as **replies** in r/youtube, r/DataHoarder, r/selfhosted, r/productivity problem-threads (NOT link posts — permaban risk). Aged account, give-first.
2. DM 10–15 existing MVP users + 2nd-degree network; ask for a 20-min call. Target ≥15 booked.
3. Launch the **subs-per-user survey** (Tally, questions in `06-validation/experiment-design.md`); seed via the same threads + a Reddit poll. Target n≥100.
4. Prep the interview: live demo environment ready, LemonSqueezy founding-member payment link created (annual discount).

## Week 2 — Willingness-to-Pay Test (the gate)

**Objective:** get real payment behavior from ≥10 qualified users.

1. Run 20-min calls (script in `experiment-design.md`). Watch them import → hit the aha feed.
2. Present pricing; hand over the live payment link; **record click-and-pay vs. verbal-yes.** Don't rescue silence.
3. Log every objection verbatim; tally tier choices.
4. **Decision point:** ≥3/10 real payments → continue. <3/10 → trigger `kill-criteria.md` K1: pause paid build, decide free/open-source path.
5. Close the survey; compute the channel-count distribution → set the free/paid gate line.

## Week 3 — MVP Scoping (only if Week 2 passed)

**Objective:** make the product paid-ready with the minimum work; de-risk the two Critical non-WTP risks.

1. **Reliability floor:** finish WebSub renewal automation + RSS polling fallback + retry + monitoring/alerting (roadmap [021]–[023]).
2. **API-independence:** ship first-class OPML/channel-ID/feed-URL import; confirm handle→ID cache is global/permanent.
3. **ToS/privacy hygiene:** privacy policy linking Google's, YouTube-ToS link, attribution, one-click export/delete. Book a **legal review** of the substitute-clause position.
4. **Cost load test (E3):** simulate 1k→10k users' WebSub load on staging; confirm free-tier cost << $0.50/user/mo (else revisit the gate — K4).
5. Make cross-device sync **legible** in-UI (the invisible paid hook).

## Week 4 — Go/No-Go + Next Phase

**Objective:** decide and set up the launch funnel.

1. Consolidate evidence: WTP result, survey distribution, cost-test, reliability status → update `assumptions-tracker.md`.
2. **Go/no-go against `scorecard.md` conditions + `kill-criteria.md`.** Write the decision down.
3. If GO: build the **landing page** ("curate your own algorithm" + graph hero) and the **thin CWS companion extension** (search-titled, handoff-only) → set up E4 funnel instrumentation.
4. Plan the **Show HN / Product Hunt** launch (graph as the visual hook) for ~Week 6–8.
5. Set the **absorption tripwire:** alert on YouTube blog / DSA filings for native subscription grouping/chronological.

---

## Success Criteria for the Month
- ✅ ≥10 real WTP data points (not verbal).
- ✅ Survey n≥100 → gate line set.
- ✅ Cost-to-serve validated at 10k.
- ✅ A written, evidence-based go/no-go decision — not a vibe.

## What NOT to Do This Month
- ❌ Build the channel graph, sharing/discovery, or the extension **before** the WTP test passes.
- ❌ Launch paid on verbal interest.
- ❌ Add any feature on the `Won't-have` list (multi-platform, overlay, TV app, ranking).

---

*Anchor: a great personal tool that no one pays for is a success as a tool and a failure as a business. This month decides which one Peristalsis is.*
