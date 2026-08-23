# Peristalsis — Partnerships, Integration & Channel-Partner Map

**Prepared:** 2026-07-23
**Scope:** Realistic partnership/integration/channel plays for a **solo founder** shipping a freemium RSS/WebSub YouTube-subscription feed (group/favorite/filter subs, watch on YouTube). Beachhead: mainstream YouTube power users who won't self-host. Growth-loop hypothesis: shareable/mergeable "follow-pack" channel bundles.

**Source tiering used below:**
- **Tier 1** — primary/authoritative (platform docs, official product blogs, peer-reviewed research).
- **Tier 2** — reputable secondary (established news/industry research).
- **Tier 3** — practitioner/marketing blogs (directional, not authoritative; useful for tactics).

---

## 1. Creator / Influencer Partnerships

### Specific creator types worth targeting (highest fit → lower)
Peristalsis is a *workflow* tool, so target creators whose audiences already ask "what tool do you use for X." Prioritize by workflow fit, not raw reach.

1. **Workflow/productivity reviewers** — Notion, Obsidian, Zapier, "second brain," PKM, read-later creators. Their audiences already manage information feeds and hate the YouTube subscription bell. Best native fit.
2. **Privacy / de-Google / degoogled tech channels** — cover RSS, Fediverse, self-hosting-adjacent tools. Peristalsis's "watch on YouTube without the algorithm" angle resonates. Note: many in this niche skew toward self-hosted solutions, so lead with the "no-self-host" convenience angle to differentiate.
3. **Indie-hacker / SaaS / "cool tools" builder channels** — will cover a solo-founder story + product demo. Doubles as founder credibility.
4. **Comparison / "tool of the week" channels** — already comparing tools in-category (RSS readers, feed apps). Native sponsor slot potential.
5. **Newsletter authors (treated as creators)** — see §3; often higher ROI than video for a niche utility.

*(Creator-type taxonomy — workflow reviewers, role-specific educators, builder channels, comparison channels — from ReachLit "YouTube Creator Outreach" playbook, Tier 3, undated 2026.)*

### How indie tools land these cheaply (the actual playbook)
The consistent advice across sources: **organic > editorial pitch > paid sponsorship**, in that order for both conversion and cost.

- **Give product, not money, first.** Get free (generous) access into the creator's own workflow. If they genuinely use it, they mention it unprompted — costs nothing and converts best. *(UxerWave, Tier 3, undated 2026; SaaSify, Tier 3, undated 2026.)*
- **Build a 10–20 creator shortlist**, not a blast. Filter on: recent videos show the exact job Peristalsis improves; comments show tool-intent questions; stable (non-viral) view counts; past sponsored videos didn't tank. *(ReachLit, Tier 3, 2026.)*
- **Personalized short pitch** that (1) names a specific video, (2) states the workflow improved in one sentence, (3) suggests one concrete segment angle, (4) states you're open to affiliate/free-access/test, (5) asks if they want the brief. *(ReachLit, Tier 3, 2026.)*
- **Reduce their workload:** ship a media kit — 50-word blurb, logo, screenshots, a creator-specific landing page ("Exclusive for [Channel] viewers"), and a UTM/tracking link. *(SaaSify, Tier 3, 2026.)*
- **Brief goals, not scripts.** Over-produced/scripted reads underperform; audiences detect ads and bounce. *(Digiday+ Research, Tier 2, 2026 — quoting YouTube's own partnerships lead.)*
- **Cost reality for good-fit indie tools:** dollar cost is often **zero** when the product genuinely fits the creator's workflow; a single niche mention typically yields ~50–200 qualified visitors and ~5–25 signups. *(plugyourbuild "Bootstrapped Founder's Guide," Tier 3, 2026.)*
- **Nurture for repeat mentions:** share conversion data (signups generated) back to the creator. *(SaaSify, Tier 3, 2026.)*

**Solo-founder caution:** creator outreach is a slow, relationship-driven grind (the ReachLit 14-day cadence is per *batch*, and press outreach payoff is measured in "a year of consistent outreach" per plugyourbuild). Budget it as a background drip, not a launch spike.

---

## 2. Integration Opportunities (ranked by effort ÷ value)

Peristalsis already sits on the RSS/WebSub rails, which is the strategic moat here: **interop is cheap because the formats are standardized.**

| # | Integration | Effort | Value | Notes |
|---|---|---|---|---|
| 1 | **OPML import** (subscription list ingest) | Low | High | OPML is *the* standard for moving feed lists between readers; every major reader exports it. Lets users onboard their entire YouTube-subs-as-OPML or migrate from Inoreader/Feedly in one file. Table-stakes + a real onboarding accelerant. |
| 2 | **OPML export** (anti-lock-in trust signal) | Low | Med-High | Cheap to build; directly counters "won't self-host but fears lock-in." Also the mechanical basis for follow-packs (§4). |
| 3 | **YouTube channel RSS as native input** | Low | High | `youtube.com/feeds/videos.xml?channel_id=…` is the documented feed; WebSub push via `pubsubhubbub.appspot.com` is **free (zero API quota)**, near-real-time, no OAuth for public uploads. This is core product, but confirms integrations with any RSS tool are trivial. |
| 4 | **"Subscribe to an OPML URL" (dynamic/live OPML)** | Med | High | Dave Winer's pattern, implemented by Inoreader: subscribe to a *remote OPML URL* that syncs as it changes. **This is the technical primitive for mergeable follow-packs** (§4) — a pack = a hosted OPML/RSS URL others subscribe to. |
| 5 | **Output feeds (RSS/JSON) out of Peristalsis** | Low-Med | Med | Let a user's filtered/grouped feed be consumed by other tools (WordPress, Mailchimp, dashboards). Makes Peristalsis a *source*, not just a sink — enables §4 loops. |
| 6 | **Read-later / Notion ecosystem** | Med | Med | No native Notion↔RSS link exists; today it's done via Zapier/n8n/custom scripts (e.g. `notion-rss` GitHub project). Opportunity: a clean "send to Notion / Readwise Reader / Pocket-style" export. Lower priority — adjacent to core (watching on YouTube), not central. |
| 7 | **Zapier / IFTTT / n8n connector** | Med-High | Med | Inoreader's model: automation platforms extend reach without you building each integration. High effort for a solo founder (app review/maintenance); defer until there's pull. |
| 8 | **Browser userscript / extension companion** | Med | Med-High | See §3 — Greasy Fork community. A lightweight "Export my YouTube subs → Peristalsis" or on-page "add to Peristalsis" userscript is a distribution wedge, not just an integration. |

*(OPML-as-standard + dynamic OPML subscriptions: Inoreader blog, Tier 1, 2014 + 2026; correctfeed help, Tier 3, undated. YouTube WebSub/PubSubHubbub mechanics, free quota, 10-day lease: Google for Developers YouTube Data API docs, Tier 1, undated; RapidDev, Tier 3, May 2026. Notion-RSS via automation only: tristan-mcinnis/notion-rss GitHub, Tier 1 primary repo, 2026. Output feeds/RSS/JSON/OPML + Zapier/IFTTT/n8n: Inoreader blog, Tier 1, Jan 2026.)*

**Platform-risk note (security/durability):** the entire integration stack depends on YouTube continuing to publish per-channel Atom feeds + WebSub. WebSub does **not** cover livestream start/end, deletions, or view counts — only public uploads and title/description changes. Any feature promising live-stream or private-video coverage requires OAuth polling of `videos.list` (quota-limited). Don't over-promise real-time completeness. *(Google docs, Tier 1; SheepReaper/nmac README, Tier 1 repo, 2026; RapidDev, Tier 3, 2026.)*

---

## 3. Community / Newsletter Partnerships

### Newsletters (often the best ROI per hour for a niche utility)
- **Niche newsletters are the single largest source of indie-product press in 2026.** A 5k-subscriber niche list beats a 50k general one. *(plugyourbuild, Tier 3, 2026; SaaSify, Tier 3, 2026.)*
- **TLDR-style dev/tech newsletters** offer native ad placements to targeted tech audiences; sponsorship is low-risk but converts worst of the three paths — lead with a *free ungated resource* (e.g., a free OPML→feed converter, a "best productivity YouTube channels" pack) rather than a sales pitch. *(Reddit/newsletter synthesis, Tier 3, 2026.)*
- **Target list to build:** productivity, PKM/second-brain, privacy/de-Google, indie-hacker, and RSS-revival newsletters. Read the last ~10 issues; pitch the format they already run (tool-of-the-week, Q&A, etc.). *(SaaSify, Tier 3, 2026.)*

### Reddit (high value, high landmine density — read rules per-sub)
Study of 49 founder-pitched subreddits: **39% ban self-promo outright, 22% allow only under 9:1, 37% case-by-case, and exactly one openly welcomes it (r/SideProject).** *(OneUp Today study, Tier 3, 2026.)*

- **Safe launch/share subs:** r/SideProject (welcomes it), r/indiehackers (once, `SHOW IH` flair, for feedback), r/SaaS ("Share Your SaaS" threads; note main-sub self-promo capped once/60 days as of Apr 2026). *(ReplyGenius, Tier 3, 2026; OneUp, Tier 3, 2026.)*
- **Audience subs (engage, don't drop links):** r/rss, r/youtube, r/datahoarder, r/selfhosted (caveat: self-host crowd is partly counter-positioned to Peristalsis's hosted pitch), r/productivity, r/Notion (promo confined to weekly thread). Provide genuine help; disclose "I built this"; put links in the **first comment, not the post body**; keep ≤10% promotional. *(GrowReddit, Tier 3, 2026; MediaFast link-policy matrix, Tier 3, 2026.)*
- **Mechanics that get you auto-removed:** URLs in body from low-karma/new accounts, domain blocklists, karma minimums enforced silently by AutoModerator; shadowbans give no notice. *(GrowReddit, Tier 3, 2026.)*
- **Tooling:** F5Bot (free) for keyword alerts on *problem* keywords (not product name) to find organic reply opportunities. *(ReplyGenius, Tier 3, 2026.)*

### Discords / forums
- Productivity, PKM (Obsidian/Notion), and RSS/self-hosting Discords — same 90/10 + disclosure etiquette. Treat as long-term reputation, not extraction.

### Userscript / power-user communities (Greasy Fork)
- Greasy Fork is the active 2026 hub for YouTube power-user scripts (needs Tampermonkey/Violentmonkey). **Direct-adjacent projects already exist**, e.g. **`FeedTube`** ("renders YouTube channel RSS as a fast full-page feed — subscriptions, caching, Shorts filter, playlists") and `PlaylistPlus` (export/import playlists as JSON). *(Greasy Fork, Tier 1, 2026.)*
- **Implication:** this community both *validates demand* and contains a *lightweight competitor pattern*. Play: publish a small companion userscript ("Export YouTube subs → Peristalsis" / on-page "add to pack") that funnels power users to the hosted app — meets them where they are and sidesteps the "won't self-host" objection by being the easy hosted upgrade from a fiddly script.

---

## 4. Follow-Pack Curator Strategy (the growth-loop bet)

### The analog: Bluesky Starter Packs (strong evidence this loop works)
Starter packs = curated lists (up to 150 accounts + 3 feeds) followable in one click, with an auto-generated share image, link, and QR code. Built to solve the cold-start problem. *(Bluesky official blog, Tier 1, Jun 2024.)*

Measured impact (peer-reviewed, dataset of 25M users / 335k packs):
- **Up to 43% of daily follow actions** came from one-click pack follows during peak migration periods; **~20% of all follows** over the full study window. *(ICWSM 2025 study; Lancaster University summary, Tier 1/Tier 2, 2025.)*
- Members of packs got **up to 85% more followers** and posted **60% more**. *(Lancaster, Tier 2, 2025.)*
- **335k packs created in first 6 months** — creators adopted it fast because it made them "connectors." *(Lancaster, Tier 2, 2025.)*
- **Downside/caution:** packs *reinforce existing popularity* rather than create new communities, and spam networks abused them for fake followers. *(Lancaster, Tier 2, 2025.)*

### How it maps to Peristalsis
A "follow-pack" = a curated bundle of YouTube channels. **Technically this is a hosted OPML/RSS URL** (see §2 #4–5): a creator/curator publishes a pack; a new user clicks once to subscribe to all channels; if the pack is a *live* OPML subscription, updates propagate (creator adds a channel → subscribers get it). This is the same primitive Inoreader already ships for dynamic OPML. *(Inoreader, Tier 1, 2014/2026.)*

### Can creators publish official bundles as partnership + loop? Yes — and it's the strongest fit.
- **Creator-as-curator partnership:** a YouTuber publishes an *official* "channels I watch / recommend" pack. It's free content for them, positions them as a connector (proven Bluesky incentive), and each pack is a **branded acquisition surface** for Peristalsis (share link + auto-preview image → new signups → who then make/share their own packs).
- **Growth-loop shape:** user imports subs → curates/forks a pack → shares link (with preview image) → recipient one-click subscribes → signs up → curates/shares. Mergeable packs add a second loop (combine two packs → new shareable artifact).
- **Design lessons borrowed from Bluesky's data:**
  - Ship an **auto-generated preview image + short link + QR** for every pack (Bluesky attributes shareability partly to this).
  - **Auto-generate a starter pack** for new users ("make one for me") to lower creation friction.
  - **Freshness matters** — stale packs get ignored; nudge curators to update; live-OPML sync handles this automatically.
  - **Guard against the abuse/inequality failure mode:** spam packs and rich-get-richer dynamics hurt trust. Add basic anti-spam (rate limits, report), and consider surfacing *smaller/niche* packs to avoid pure popularity reinforcement.
  - **Discovery gap to plan around:** Bluesky packs originally *didn't appear in search* — growth came from off-platform sharing. Don't rely on in-app discovery alone; the pack link must travel on Reddit/Discord/newsletters/YouTube descriptions.

**Verdict:** the follow-pack loop is the most defensible growth mechanism here because (a) it rides Peristalsis's existing RSS/OPML rails at low build cost, (b) it has direct empirical precedent (Bluesky), and (c) it converts creator partnerships (§1) and community sharing (§3) into a compounding acquisition surface instead of one-off mentions.

---

## 5. Quick Wins vs Long-Term

### Quick wins (weeks, low effort, solo-doable)
- **OPML import + export** (interop + anti-lock-in). §2 #1–2.
- **Launch on r/SideProject** (only sub that welcomes promo) + r/indiehackers `SHOW IH` + r/SaaS share thread. §3.
- **Free ungated micro-tool** as a newsletter/Reddit lead magnet (e.g., "export your YouTube subs to OPML" or a public "best channels" pack). §3.
- **Set up F5Bot** on problem keywords for organic Reddit reply opportunities. §3.
- **Ship a Greasy Fork companion userscript** ("subs → Peristalsis") to tap an already-validated power-user pool. §3.
- **v1 follow-pack = shareable OPML URL + auto preview image.** Minimal loop live early. §4.

### Long-term (months, relationship/compounding)
- **Creator relationship drip** — 10–20 workflow/privacy/indie creators, give-first, nurture for organic + affiliate mentions. Payoff measured in ~a year. §1.
- **Live/mergeable follow-packs + creator-official packs** as the flagship growth loop. §4.
- **Automation connectors** (Zapier/IFTTT/n8n) and **read-later/Notion export** once there's demand pull. §2 #6–7.
- **Recurring niche-newsletter presence** (sponsorship + editorial), reinvesting proven UTM data. §3.

---

## 6. Data Gaps

- **No hard conversion data specific to utility/RSS apps** — the 50–200 visitors / 5–25 signups per niche mention figures are from a Tier 3 bootstrapper blog and generalized across categories; unverified for this niche.
- **Follow-pack transfer is analogy, not proof.** Bluesky data is a *social network* (following = the core action). Peristalsis packs drive *subscription import*, a weaker viral action (recipient must also want a new tool). The 43%/85% figures should not be assumed to transfer.
- **YouTube ToS / brand-usage risk unassessed** — using "YouTube" in marketing, scraping channel metadata beyond the public Atom feed, and the API Services Terms limits were not researched here. Needs a dedicated compliance pass before building creator-facing pack features.
- **Named target lists not compiled** — specific creators, exact newsletters (TLDR editions/rates), and specific Discords were not enumerated; only categories + selection criteria. Requires a follow-up sourcing pass.
- **Reddit rules are volatile** — the 49-sub study is a 2026 snapshot; per-sub live rules must be re-checked before each post.
- **Competitive intensity in userscript space** — `FeedTube` and similar already exist; their traction/user counts weren't quantified.
- **Mergeable-pack demand is unvalidated** — no evidence users *want* to merge packs (vs. just follow one); an assumption to test cheaply.

---

## Source list

- Google for Developers — *Subscribe to Push Notifications (YouTube Data API)* — **Tier 1**, undated.
- Inoreader blog — *OPML subscriptions* (2014) & *Connect tools and distribute content* (Jan 2026) — **Tier 1**.
- Bluesky — *Introducing Starter Packs* — **Tier 1**, Jun 2024.
- Ascigil et al. — *Bootstrapping Social Networks: Lessons from Bluesky Starter Packs*, ICWSM 2025 — **Tier 1** (peer-reviewed).
- Greasy Fork — YouTube userscript listings (FeedTube, PlaylistPlus, etc.) — **Tier 1** (primary catalog), 2026.
- tristan-mcinnis/notion-rss; SheepReaper/nmac — GitHub repos — **Tier 1** (primary), 2026.
- Lancaster University — starter-packs study press summary — **Tier 2**, 2025.
- Digiday+ Research — *2026 guide to creator marketing* — **Tier 2**, 2026.
- ReachLit, UxerWave, SaaSify, plugyourbuild — creator/newsletter outreach playbooks — **Tier 3**, 2026.
- GrowReddit, ReplyGenius, OneUp Today, MediaFast, redditgrowthdb — Reddit self-promo rules/tactics — **Tier 3**, 2026.
- RapidDev — YouTube WebSub automation guide — **Tier 3**, May 2026.
- correctfeed — Inoreader OPML export help — **Tier 3**, undated.
