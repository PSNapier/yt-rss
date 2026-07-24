# Peristalsis — Regulatory & Platform-ToS Risk Analysis

**Analyst role:** Regulatory & platform-ToS research analyst
**Date:** 2026-07-23
**Scope:** YouTube ToS/API restrictions, enforcement precedent, RSS/WebSub legality, GDPR/CCPA + payments.
**Convention:** `[Data]` = quoted/paraphrased from primary or reputable source. `[Opinion]` = analyst inference, not a legal conclusion. **Not legal advice.**

**Source tiers:** Tier 1 = primary/official (Google/YouTube docs, statute, EFF/GitHub filings). Tier 2 = reputable press/established orgs (Vice, TorrentFreak, Ars, TechCrunch, Wikipedia, Stripe docs). Tier 3 = vendor blogs/SEO explainers (treat directionally).

---

## Product model (as given)
- Displays user's YouTube subscription feed via **public channel RSS** (`youtube.com/feeds/videos.xml?channel_id=…`) + **WebSub/PubSubHubbub** push.
- **Limited YouTube Data API** use: only resolve `@handles → channel IDs`, self-capped ~9,500 units/day (under the 10,000 default).
- **No scraping.** Playback happens **on youtube.com** (clicks open YouTube).
- Freemium SaaS, paid tiers, global, English-first.

**Critical framing:** Peristalsis touches **three separate legal instruments**, each with different bindingness:
1. **YouTube API Services ToS + Developer Policies** → binds because it uses the Data API (handle resolution). This is the strictest instrument and the one Peristalsis *has agreed to* by getting an API key.
2. **Main YouTube ToS** (`youtube.com/t/terms`) → binds the operator as a YouTube user; governs the RSS-fetching behavior.
3. **Data-protection law** (GDPR/CCPA) + **PCI DSS** (via Stripe) → binds as a data controller/merchant.

---

## 1. YouTube ToS / API Restrictions (exact risky clauses)

### 1.1 The "substitute for YouTube" clause — HIGHEST RISK
**[Data]** Developer Policies (Tier 1, Google for Developers, current 2026):
> "use YouTube API Services to create, offer, or act as a **substitute for, or substantially similar service to, any YouTube Applications**. API Clients **must not mimic or replicate YouTube's core user experiences by recreating features or process flows unless they add significant independent value** or functionality that improves users' interactions with YouTube. **For example, an API Client must not recreate the browse experience** from any YouTube Application without adding significant independent value to that flow."

**[Data]** Compliance guide (Tier 1) restates: "Don't use our API to re-create YouTube (e.g. don't clone, mimic, modify, or reduce standard YouTube features)… Independent value means providing users with added functionality that is not available via the YouTube API today."

**[Opinion]** Peristalsis's core function — showing a user's subscription feed and letting them browse new uploads — **is the YouTube "subscriptions/browse" experience**. This is the single most dangerous clause. The defensibility of the whole product rests on the "significant independent value" carve-out. Feed dedup, cross-device read/watch-state, filtering, notifications, a cleaner ad-free *index* (not ad-free *playback*) are plausible "independent value" arguments, but YouTube reserves sole discretion to judge this.

### 1.2 Monetization rules
**[Data]** Main YouTube ToS (Tier 1, restriction 10): you may not "sell advertising, sponsorships, or promotions on **any page of any website or application that only contains Content from the Service or where Content from the Service is the primary basis for such sales** (for example, selling ads on a webpage where YouTube videos are the main draw)."

**[Data]** Developer Policies (Tier 1) prohibited action: "sell advertising, sponsorships, or promotions **on any page or screen that contains YouTube API Data unless other data, content, or material not obtained from YouTube appears on the same page and offers enough independent value** to justify such sales if the YouTube API Data were removed."

**[Data]** Explicitly **permitted** commercial actions (Tier 1): "Selling an API Client"; "Developing ad-enabled API Clients"; "Placing your own branding… as long as [it] complies with the YouTube Branding Guidelines."

**[Opinion]** Peristalsis charges **subscription fees for the app itself**, not ad sales on YouTube content. "Selling an API Client" is explicitly permitted, so the freemium/paid-tier model is **not per se prohibited**. The risk is not "you monetize" but "your monetized product is a browse-experience substitute" (§1.1). Note the recurring "independent value" test also gates monetization legitimacy.

### 1.3 Playback / gating restriction
**[Data]** Developer Policies (Tier 1): "API Clients **must not charge users to watch content in an embedded YouTube player**… must not otherwise gate access to a video by requiring a user to take an action other than clicking the play button."

**[Opinion]** Peristalsis sends users to youtube.com to watch (no embedded gated player) → this specific clause is **low risk** *as long as* no paid tier gates actual video viewing. Keep playback on YouTube.

### 1.4 Attribution / branding requirements
**[Data]** API Services ToS §10 (Tier 1): limited, revocable license to display YouTube Brand Features "only in accordance with the YouTube Branding Guidelines." §10.3: "All API Clients must provide **proper attribution**… YouTube reserves the right to terminate your license to display the YouTube Brand Features at any time." §11: "You will **not remove, obscure, or alter** any… copyright, trademark, or other proprietary rights notices; or falsify or delete any author attributions."
**[Data]** Developer Policies: "API Clients must display a **link to YouTube's Terms of Service** (`youtube.com/t/terms`), and… state in their own terms of use that, by using those API Clients, users are agreeing to be bound by the YouTube Terms of Service."

### 1.5 Storage / caching / data-handling rules (API Data only)
**[Data]** Developer Policies principle 4 (Tier 1): "Don't store user data indefinitely, and provide a clear, straightforward process for them to delete data… do not… request, collect, or store users' YouTube login credentials."
**[Data]** "Refreshing, Storing, and Displaying API Data": API Clients may store authorization tokens and certain Authorized Data "for as long as is necessary provided that [it] is used for purposes consistent with the specific consent granted by an **active user**." (YouTube generally requires stored non-authorized API data to be refreshed/expired within ~30 days — verify exact current window; see Data Gaps.)
**[Data]** Data Aggregation clause: "Do not aggregate API Data… to gain insights into YouTube's usage, revenue, or any other aspects of YouTube's business."
**[Data]** One-project rule (Tier 3 corroborated, reflects Developer Policies): "exactly one (1) API Project per API Client, and that API Project must not be used for any other API Client." Sharding projects to multiply quota = policy breach → revocation risk.

**[Opinion]** Because Peristalsis only uses the API to resolve handle→channel ID (public metadata, not user-authorized data), most storage-of-Authorized-Data exposure is **low**. Channel IDs are stable and arguably not personal data. But caching them long-term should be documented; avoid building any "aggregate insights" feature on API data.

### 1.6 Quota reality
**[Data]** (Tier 3, multiple 2026 sources; corroborates Tier 1 quota calculator): Data API is free but capped at **10,000 units/day/project**; **cannot be purchased**; more requires the Audit & Quota Extension Form (manual review, weeks–months, can be denied). `search.list` = 100 calls/day separate bucket. Sharding projects violates ToS.
**[Opinion]** Self-cap at 9,500 is prudent but **fragile at scale**. Handle-resolution should be cached aggressively (one lookup per channel ever, not per user) so quota scales with *distinct channels*, not *users*. Growth beyond the free quota forces the audit process — which itself scrutinizes the substitute-clause compliance. **Quota is both an ops risk and a compliance chokepoint.**

---

## 2. Enforcement Precedent

### 2.1 Invidious — direct, on-point precedent (Tier 2)
**[Data]** June 2023: YouTube Legal sent a **cease-and-desist** to Invidious (open-source alt front-end), giving 7 days to shut down, alleging violation of "YouTube API Services Terms of Service and Developer Policies." Cited failures: **no link to YouTube ToS**, not "clearly" explaining user-data handling, and "mimic[king] or replicat[ing] core user experiences… unless they add significant independent value." (Sources: Vice 2023-06-15, TorrentFreak 2023-06-09, gHacks 2023-06-10, Wikipedia.)
**[Data]** Outcome: Invidious **did not comply**, argued it doesn't use the official API (it scrapes) so never agreed to those terms. **YouTube did not pursue further legal action** (GitHub issue #3872, developer statement).
**[Opinion]** This is the closest analog and the clearest warning: YouTube's *opening move is the C&D citing the substitute clause + privacy-policy/ToS-link failures*. Peristalsis is **more exposed than Invidious to the API ToS** (Invidious's defense was "we don't use the API"; Peristalsis *does* use it, so it *has* agreed and *can't* use that defense). Peristalsis is **less exposed on copyright** (no proxying/streaming of video).

### 2.2 youtube-dl / RIAA (Tier 1/2)
**[Data]** Oct 2020: RIAA filed DMCA §1201 (anti-circumvention) takedown at GitHub; repo removed. Nov 2020: after EFF letter arguing youtube-dl merely reads public JavaScript "signature" like a browser and circumvents no DRM, **GitHub reinstated** it and created a $1M developer defense fund. (EFF 2020-11, GitHub Blog, Ars, TechCrunch.)
**[Opinion]** Relevant only tangentially: shows (a) copyright/§1201 is the weapon when *content is downloaded* — Peristalsis doesn't download, so §1201 exposure is minimal; (b) takedowns can be reversed with legal backing. Not a strong shield for a *commercial* SaaS.

### 2.3 NewPipe / Piped (Tier 2)
**[Data]** NewPipe team publicly monitored the Invidious C&D and noted they had "closely monitored… youtube-dl" but saw "no imminent legal threat." No confirmed successful shutdown of these scraper-based clients via API-ToS in the sources reviewed.
**[Opinion]** Pattern: YouTube sends C&Ds and applies quota/API-key revocation pressure; **full litigation against these projects is rare** (they're non-commercial, distributed, often EU-hosted). A **revenue-generating, incorporated SaaS is a far more attractive and reachable enforcement target** than an anonymous OSS collective.

### 2.4 Enforcement toolkit observed
**[Data/Opinion]** Realistic escalation ladder: (1) **API key/credential revocation** or quota curtailment (fastest, unilateral, per ToS §3.1 "for any violation"); (2) cease-and-desist letter; (3) trademark/brand-feature license termination; (4) app-store takedown requests; (5) litigation (rare). For Peristalsis, **(1) is the existential risk** — losing the key kills handle-resolution overnight.

---

## 3. RSS / WebSub Legality

### 3.1 Is consuming the public channel RSS feed against ToS?
**[Data]** `youtube.com/feeds/videos.xml?channel_id=…` is a **public static Atom file** (Kevin Cox, Tier 2/3; YouTube push-notifications guide, Tier 1). It is served without authentication.
**[Data]** The **API Services ToS** governs "API Data… provided through the YouTube API Services." The RSS feed is arguably *not* delivered "through the YouTube API Services," so the API ToS's substitute/storage clauses **may not directly bind RSS consumption**. `[Opinion]` — this is an untested argument, not a settled conclusion.
**[Data]** However, the **main YouTube ToS** (Tier 1) restriction 3: "access the Service using any **automated means (such as robots, botnets or scrapers)** except (a) … public search engines, in accordance with… robots.txt; or (b) with YouTube's prior written permission." Restriction 1 limits use of "any part of the Service" to what's "expressly authorized by the Service." Restriction 9: content is for "personal, non-commercial use."

**[Opinion]** Two-sided read:
- **Pro-permitted:** RSS feeds exist precisely to be consumed by feed readers/automated clients; YouTube publishes them and documents the WebSub subscription flow. A feed reader fetching a feed is the feed's intended use, not "scraping."
- **Anti:** A *commercial* service programmatically polling feeds for many users is "automated means" + arguably fails "personal, non-commercial use," and none of restriction-3's exceptions (search engine / written permission) apply. There is **no explicit ToS clause that blesses third-party commercial RSS consumption.**
- **Net [Opinion]:** RSS consumption is a **grey zone, medium risk** — safer than scraping, but not clearly "permitted," and the "personal, non-commercial use" line is the weak point for a paid product.

### 3.2 WebSub / PubSubHubbub
**[Data]** YouTube documents WebSub push under the **YouTube Data API docs** (`developers.google.com/youtube/v3/guides/push_notifications`, Tier 1): subscribe via the public Google hub `pubsubhubbub.appspot.com` with topic `youtube.com/xml/feeds/videos.xml?channel_id=…`; hub POSTs Atom notifications to your callback.
**[Data]** Practitioner caveats (Tier 2/3, Kevin Cox / youtube/api-samples #177): YouTube's WebSub is non-standard — push payload contains only video/channel IDs + partial data; feed is cached; **you often must fall back to polling anyway**; subscriptions expire and must be renewed.
**[Opinion]** Because WebSub is documented *within the API guide*, YouTube could argue it's part of "YouTube API Services," pulling it under the stricter API ToS (incl. substitute clause). The hub is a general-purpose Google-owned hub with no separate paid terms. **Medium risk**; operationally, expect to still poll RSS, so build for both.

---

## 4. Data Privacy (GDPR / CCPA) + Payments

### 4.1 GDPR (Tier 2/3 explainers + statute)
**[Data]** GDPR applies to processing personal data of **any EU/UK resident regardless of company size/location** — "a single EU user" triggers it. No revenue threshold. Fines up to **€20M or 4% of global turnover**.
**[Data]** Core obligations for a SaaS controller: (a) **lawful basis** per Art. 6 (contract for account/service; consent for marketing/non-essential cookies; legitimate interest documented); (b) **Art. 13/14 privacy notice** (identity, data categories, purposes+bases, retention, sub-processors, transfer safeguards, rights); (c) **data-subject rights** (access/rectify/erase/restrict/portability/object) honored within ~30 days; (d) **DPAs (Art. 28)** with every sub-processor (hosting, Stripe, email); (e) **breach notification** to supervisory authority within **72 hours**; (f) EU/UK **representative** if no EU establishment; (g) valid **cross-border transfer mechanism** (SCCs) for US hosting.

**[Opinion]** Peristalsis stores **account, email, subscription list, and watched-state** → all personal data; the subscription list is arguably **sensitive-adjacent behavioral/profile data** (reveals interests, possibly special-category inferences e.g. political/religious channels). This raises the bar: data minimization and clear purpose limitation matter. **Watched-state + subscription list = a behavioral profile**, the kind regulators scrutinize.

### 4.2 CCPA/CPRA (Tier 2/3)
**[Data]** Applies to businesses meeting a threshold — commonly **~$26.6M annual revenue**, or 100k+ consumers/households, or 50%+ revenue from selling data. **Most early-stage startups are below threshold**, but must comply once scaled.
**[Data]** Obligations when covered: privacy policy disclosing collection/sharing; **"Do Not Sell or Share My Personal Information"** opt-out + honor **Global Privacy Control (GPC)** signals; rights to know/delete/correct; **2026 ADMT (automated decision-making) notice rules** and cybersecurity-audit obligations for larger processors; treat under-16 data as sensitive.

**[Opinion]** As long as Peristalsis **doesn't sell/share** the subscription/watch data, CCPA burden is light early on. Do **not** monetize the behavioral data (ad targeting/selling) — that would flip it into "sale/share" territory and materially raise CCPA exposure. Recommend a single privacy policy with clearly labeled GDPR + CCPA sections.

### 4.3 YouTube's own privacy requirements (compounds §1)
**[Data]** API Services ToS + Developer Policies (Tier 1): each API Client must have a **published privacy policy** that references/links the **Google Privacy Policy**, notifies users it uses YouTube API Services, and explains what user info + API Data it accesses/stores/shares. Failure to do this was **explicitly cited in the Invidious C&D**.
**[Opinion]** This is cheap to satisfy and its absence is a known trigger — do it before launch.

### 4.4 Payments / Stripe PCI (Tier 1/2)
**[Data]** Stripe is **PCI DSS Level 1** certified for *its* infrastructure; **compliance is not transferable** — merchant retains obligations based on integration. **Stripe Checkout (redirect)** or **Elements in iframe mode** → **SAQ A** (~22 controls, annual self-attestation, ASV scan if public-facing IPs). Elements with custom JS / server-side redirect logic may escalate to **SAQ A-EP** (~191 controls) or **SAQ D** if card data touches servers.
**[Data]** Under **PCI DSS 4.0.1** (current 2026), even SAQ-A merchants must satisfy **§6.4.3** (authorized script inventory on payment pages) and **§11.6.1** (detect unauthorized changes to headers/scripts). Keep a **signed AoC** from Stripe + written responsibility contract (Req 12.8).
**[Opinion]** Use **Stripe Checkout (hosted redirect)** to stay in SAQ A and keep card data entirely off Peristalsis servers. This is the lowest-effort compliant path. Still must handle §6.4.3/§11.6.1 on the checkout-adjacent pages.

---

## 5. Regulatory Risk Level

| Area | Level | Reasoning |
|---|---|---|
| **YouTube substitute/"recreate browse" clause** | **HIGH** | Peristalsis's core = the subscription/browse experience. Directly the clause YouTube cited vs Invidious. Survival depends on discretionary "independent value" judgment held by YouTube. |
| **API key revocation** | **HIGH (impact) / MED (likelihood)** | Unilateral, per ToS §3.1, kills handle-resolution instantly. Commercial target = attractive. Mitigated by minimal API footprint. |
| **RSS consumption legality** | **MEDIUM** | Grey zone; "personal, non-commercial use" clause is the weak point for a paid product. No clause blesses commercial 3rd-party RSS. But intended-use argument is reasonable; no scraping. |
| **WebSub** | **MEDIUM** | Documented under API guide → may fall under API ToS. Operationally unreliable; not a legal shield. |
| **Monetization per se** | **LOW–MED** | "Selling an API Client" explicitly permitted. Risk only via substitute-clause linkage, not fees themselves. |
| **Copyright / §1201** | **LOW** | No video download/proxy/embed-gating; playback stays on YouTube. |
| **GDPR** | **MEDIUM** | Applies from user #1 in EU; behavioral profile (subs+watch-state) raises bar; but standard SaaS controls suffice. Large fines make non-compliance material. |
| **CCPA** | **LOW (now) / MED (scaled)** | Below threshold early; rises with scale; stays low iff data not sold/shared. |
| **PCI (Stripe)** | **LOW** | SAQ A via hosted Checkout; well-trodden path. |

### Overall: **HIGH**
Driven almost entirely by **platform-ToS (YouTube), not statutory regulation.** The privacy/payments side is routine and solvable with known playbooks. The existential risk is that YouTube deems Peristalsis a **substitute for the YouTube browse experience** and revokes the API key and/or issues a C&D — exactly the Invidious pattern, against a target that (unlike Invidious) *has* agreed to the API ToS and *is* commercial.

---

## 6. Mitigations

**Reduce API dependency (shrink the kill-switch surface):**
1. **Cache handle→channel-ID globally and permanently.** One resolution per channel *ever*, shared across all users. Quota then scales with distinct channels, not users → likely never hit 9,500.
2. **Offer channel-ID / feed-URL / OPML import** so users can add channels *without* any API call → path to operate even if the API key is revoked. `[Opinion]` This is the single most important resilience move: make the API optional, not load-bearing.
3. Never shard projects to multiply quota (explicit ToS breach).

**Defend the substitute clause (build the "independent value" record):**
4. Ship features **not available in YouTube's own UI**: cross-device unified read/watch-state, dedup across subscriptions, powerful filtering/muting, digest/notification scheduling, "inbox-zero" feed workflow. Document these as the independent value.
5. **Keep all playback on youtube.com** (already the plan). Never embed-gate, never charge to watch, never strip YouTube ads. Frame Peristalsis as an **index/reader that drives traffic *to* YouTube**, not a replacement viewer.
6. Do **not** replicate YouTube's recommendation/algorithmic browse; stay a chronological/organizational tool — reinforces "different, additive" positioning.

**ToS/branding hygiene (cheap, closes known C&D triggers):**
7. Publish a **privacy policy** that: links Google's Privacy Policy, states the app uses YouTube API Services, states users agree to YouTube ToS, and enumerates data collected/stored/shared.
8. Display a **link to YouTube ToS** and comply with **YouTube Branding Guidelines** (proper attribution, don't alter YouTube marks/notices).
9. Don't build any "YouTube insights/aggregate metrics" feature on API data (aggregation clause).

**Privacy / payments:**
10. **Data minimization**: store channel IDs (not full metadata long-term); treat subscription list + watch-state as behavioral profile — clear purpose limitation, no ad-targeting/sale.
11. GDPR: lawful basis map, Art. 13 notice, self-serve **account+data deletion**, DPAs with hosting/Stripe/email, 72-hr breach process, SCCs for US transfer, EU representative if no EU entity.
12. CCPA: single policy w/ labeled sections; honor GPC; add "Do Not Sell/Share" (even if you don't sell — cheap insurance); **commit to never selling behavioral data**.
13. Payments: **Stripe Checkout (hosted redirect)** → SAQ A; keep signed Stripe AoC; satisfy PCI 4.0.1 §6.4.3 + §11.6.1 (script/header monitoring) on payment pages.

**Strategic / contingency:**
14. **Consider requesting written permission / higher quota via the Audit form early** — but note the audit *scrutinizes substitute-clause compliance*, so only apply once independent-value features exist. `[Opinion]` double-edged; sequencing matters.
15. Maintain an **API-independent operating mode** and a documented pivot (self-hosted / OPML / user-provided keys) as a business-continuity plan against key revocation.
16. **Get a real lawyer** to review the ToS position and privacy docs before paid launch. This report is not legal advice.

---

## 7. Data Gaps

1. **Exact current API-Data storage/refresh window** (commonly cited as ~30 days for non-authorized cached API data) — not verbatim-confirmed from the live Developer Policies text in this pass. Verify §III.L "derived metrics and data storage" and the "Refreshing, Storing, and Displaying API Data" section directly.
2. **Whether channel IDs count as "API Data" subject to storage limits** once obtained via `search.list`/`channels.list` for handle resolution — ambiguous; affects mitigation #1. `[Opinion]` likely low-sensitivity but unconfirmed.
3. **Definitive answer on whether RSS feed consumption falls under API ToS vs only main ToS** — untested; no authoritative statement found. This is the pivotal legal ambiguity for the model.
4. **WebSub classification** — whether YouTube treats hub-delivered notifications as "YouTube API Services." Unconfirmed.
5. **No evidence found of YouTube enforcement specifically against RSS-based *commercial* feed readers** — absence of precedent cuts both ways (untested tolerance vs. untested risk).
6. **Branding Guidelines specifics** (exact attribution/logo rules) not fully extracted — review `developers.google.com/youtube/terms/branding-guidelines` before UI finalization.
7. **CCPA threshold nuance & 2026 ADMT rules** applicability to subscription-feed features — confirm with counsel if any ranking/personalization is added.
8. **Quota-extension approval odds for a "feed reader/browse" use case** — unknown; Tier-3 sources say weeks–months and can be denied, but no data specific to this product category.

---

### Source list (with tier + date)
- YouTube API Services ToS — developers.google.com (Tier 1, current 2026)
- YouTube API Services **Developer Policies** — developers.google.com (Tier 1, current 2026) — *substitute clause, privacy, storage, aggregation, commercial use*
- Complying with YouTube's Developer Policies (guide) — developers.google.com (Tier 1)
- Main **YouTube Terms of Service** — youtube.com/t/terms (Tier 1; PDF dated 2023-12-15) — *automated-means restriction, non-commercial use, ad-sale restriction*
- Subscribe to Push Notifications (WebSub guide) — developers.google.com (Tier 1)
- Kevin Cox, "YouTube's Wonky WebSub" 2021-12-16 (Tier 2/3); youtube/api-samples issue #177 (Tier 2)
- Invidious C&D coverage: Vice 2023-06-15, TorrentFreak 2023-06-09, gHacks 2023-06-10 (Tier 2); Wikipedia "Invidious" (Tier 2); GitHub issue iv-org/invidious#3872 (Tier 1 primary)
- youtube-dl/RIAA: EFF 2020-11 + EFF letter PDF (Tier 1), GitHub Blog 2020-11 (Tier 1), Ars Technica 2020-11, TechCrunch 2020-11-16 (Tier 2)
- YouTube API pricing/quota: OutlierKit, Blotato, Phyllo — 2026 (Tier 3); YouTube Quota Calculator (Tier 1)
- GDPR/CCPA: Andrew S. Bosin LLC (Tier 3), ShieldKey, ComplyKit, GDPR Scout, toslawyer.com — 2026 (Tier 3)
- Stripe PCI: Stripe Integration Security Guide (Tier 1); cside.com, secusyasv, pcicompliance.com, paytia — 2026 (Tier 2/3)
