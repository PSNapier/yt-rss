# Peristalsis — Demand Signals Research

**Research goal:** Quantify demand signals — is there measurable, rising demand for an RSS/WebSub-style YouTube subscription-feed alternative targeting YouTube power users?

**Method:** 10 web searches (Jul 2026). Sources tiered:
- **Tier 1** — primary/authoritative (Chrome Web Store, GitHub, Hacker News, official pricing pages).
- **Tier 2** — reputable aggregators/press (chrome-stats, Zapier, XDA, RedPulse, GummySearch).
- **Tier 3** — vendor marketing/blogs (self-reported).

> Honesty note: Exact Google Trends index values (0–100 trajectory) could NOT be retrieved via search — Trends requires the interactive UI or a scraper. Search-trend direction below is inferred from corroborating qualitative/press signals, and flagged as a data gap. No numbers fabricated.

---

## 1. Search Trend Signals (direction)

Could not pull raw Google Trends index numbers programmatically (see Data Gaps). Indirect directional signals:

| Query / theme | Direction | Evidence |
|---|---|---|
| "youtube rss" / "youtube rss feed" | **Rising (inferred)** | Multiple fresh 2026-dated explainer articles being published targeting the term (wprssaggregator.com "YouTube RSS Feed... (2026)"). Content supply tracks search demand. **Tier 2/3**, dated 2026. |
| "chronological youtube feed" / "hide youtube shorts" | **Rising (inferred)** | Root driver is real: YouTube Subscriptions tab now "mixes in Shorts and is no longer reliably chronological" (getfeedvault.com, 2026). New tools launching specifically around this pain (YT Zero created 2026-06-13). **Tier 1/3.** |
| "organize youtube subscriptions" / "youtube subscription manager" | **Stable–rising** | Established category with mature incumbents (PocketTube since ~2019, still growing to 300K users). Ongoing PH launches (Velty). **Tier 1/2.** |

**Verdict on search trends:** Directionally positive but **UNQUANTIFIED**. Treat as weak-confidence until real Trends export obtained.

---

## 2. Revealed Demand (installs / stars / subreddit size)

### Browser extensions (revealed demand = actual installs)
| Tool | Users | Ratings | Notes | Source (tier/date) |
|---|---|---|---|---|
| **Unhook** (remove YT recs/Shorts) | **1,000,000** active users | 4.85 avg, 4,016 reviews (store shows 54.4K ratings) | Grew 600K (Apr 2024) → 1M (Mar 2026). Adjacent (distraction removal, not RSS), but proves mass appetite to escape YT algorithm. | Chrome Web Store + chrome-stats **T1/T2**, 2026-03 |
| **PocketTube** (subscription manager) | **300,000** users | 4,000+ 5-star reviews | Direct competitor. Growth 200K→300K over ~recent period. Also on iOS/Android + Patreon. | Chrome Web Store / pockettube.io **T1/T3**, 2026-02/04 |

**Unhook growth is the strongest single quantified signal:** +400K users in ~23 months (≈+67%), landing at 7-figure install base for "escape the YouTube algorithm."

### GitHub (revealed demand from self-hosters)
| Repo | Stars | Created | Signal |
|---|---|---|---|
| **YT Zero** (Pelski/ytzero) | **74** | 2026-06-13 | Direct concept match (RSS-based chronological subscription inbox, no Google account). Picked up press (XDA). Very new — trajectory unknown but early traction + media within weeks. **T1.** |
| franklioxygen/MyTube | **1,013** | 2025-03 | Adjacent (downloader + subs + private RSS links). Broader scope. **T1.** |
| TubeShelf, YouRSS, ytcs | 1–low | 2021–2026 | Long tail of hobbyist RSS-YT tools. Confirms persistent itch, small individual reach. **T1.** |

**Read:** A *steady stream* of independent devs keep rebuilding this exact tool (2021→2026). Signals durable, unsolved pain — but each project's star count is small; no runaway OSS hit yet.

### Reddit communities (audience reachability, not direct demand)
| Subreddit | Members | Growth | Source (tier/date) |
|---|---|---|---|
| r/DataHoarder | ~950,065 | — | RedPulse **T2**, 2026-07-19 |
| r/selfhosted | ~802,000 | **+244K (+43.8%)** | GummySearch **T2**, 2026-07-16 |
| r/degoogle | ~447,385 | — | RedPulse **T2**, 2026-07-15 |

**Read:** Large, fast-growing adjacent audiences (r/selfhosted +43.8%). These are reachable channels, not proof of demand for *this* product. Could not retrieve counts for r/youtube or specific high-upvote "give me a chronological feed" threads (data gap).

---

## 3. Launch / Waitlist Signals

| Signal | Detail | Source (tier/date) |
|---|---|---|
| **Feedvault** (closest direct competitor) | Show-HN pre-sale: founder set bar at **"15 backers and I ship it"** — i.e. product gated on finding just 15 paying users. Runs on YouTube Data API (per-user cost is the constraint). | Hacker News #47575797 **T1**, 2026 |
| Feedvault HN reception | **Mixed-to-negative on paying:** top comments — *"I'm certainly not going to pay for this nor hand out my history. I'd consider self hosting"* and *"Those prices are ridiculous."* Demand for the *concept* (open-source interest) but resistance to the *paid SaaS* framing. | Hacker News **T1**, 2026 |
| **Velty** (PH launch) | YouTube subscription organizer, folders + filters. Product Hunt upvotes visible in single digits (~2 on the page captured). Low launch energy. | Product Hunt **T2**, ~1yr ago |
| **PocketTube** (PH) | Established PH presence, multiple launches (Subscription Manager, Playlist Manager), positive testimonials. | Product Hunt **T2**, 2025 |

**Read:** Direct paid-SaaS launches are **weak/small** (Feedvault needing only 15 backers; Velty low single-digit upvotes). The extension model (PocketTube/Unhook) massively outperforms the standalone-app model on adoption.

---

## 4. Purchase / Willingness-to-Pay Signals

| Evidence | WTP signal | Source (tier/date) |
|---|---|---|
| **Inoreader Pro** — $7.50/mo annual ($90/yr) or $9.99/mo. **YouTube subscription sync is Pro-only.** | People *do* pay for RSS incl. YT sync; but it's bundled into a broad RSS reader, not YT-specific. Public testimonials: *"happy to pay a few bucks for tools that don't suck."* | inoreader.com/pricing, Readless, Zapier **T1/T2**, 2026-06 |
| **Feedvault** — $99/yr or $499 lifetime | Priced aggressively high; HN pushback ("ridiculous"). WTP at that price unproven. | getfeedvault.com **T3**, 2026 |
| **PocketTube** — free + Patreon / "buy me a coffee" / Pro | Monetizes 300K users via donations + pro tier. Donation model implies **soft/low WTP** — users won't pay mandatory subscription, but some tip. | pockettube.io / Chrome store **T1/T3**, 2026 |
| **Unhook** — free, PayPal donations | 1M users, zero mandatory payment. Donation-only. **Strong usage, weak monetization.** | unhook.app **T1**, 2026 |

**Read:** WTP is the **weakest** part of the demand picture. The only clear paid success (Inoreader) sells a *general* RSS product where YT is one feature. YT-specific tools with the largest reach (Unhook 1M, PocketTube 300K) survive on **donations**, not subscriptions — evidence that the specific audience resists paying for this as a standalone product.

---

## 5. Overall Demand Verdict

**MODERATE (leaning moderate-weak on monetizable demand).**

Why:
- **Usage demand is real and large.** Unhook's 1M users (+67% in ~2yr) and PocketTube's 300K prove millions actively want to escape YouTube's algorithm/Shorts. The underlying pain (Subs tab no longer chronological, Shorts injected) is confirmed and worsening.
- **Supply-side validation is persistent.** Independent devs keep building the exact RSS-chronological-feed tool (YT Zero, TubeShelf, YouRSS, ytcs, MyTube) across 2021–2026 → durable, unsolved itch.
- **BUT willingness-to-pay is weak and unproven for the standalone/RSS framing.** The biggest tools monetize via donations, not subscriptions. The closest paid SaaS (Feedvault) gated launch on just 15 backers and drew "won't pay / ridiculous price" pushback on HN. Paid success only appears when YT is bundled into a broad tool (Inoreader Pro).
- **Search-trend direction is positive but unquantified** (see gaps).

**Implication for Peristalsis:** Demand to *use* is strong; demand to *pay* is the risk. Freemium is the right instinct, but the free tier must carry the load (extension-style distribution like Unhook) and paid conversion should be validated early against the Feedvault cautionary signal. Consider extension distribution over standalone-app, given extensions dominate adoption 3–1000x here.

---

## 6. Data Gaps

1. **Raw Google Trends index values** — not retrievable via search; need interactive Trends export (property=Web and property=YouTube) for "youtube rss", "hide youtube shorts", "chronological youtube feed", "organize youtube subscriptions" over 2022–2026 to confirm slope/magnitude. Directional inference only right now.
2. **Specific Reddit thread demand** — no captured upvote counts on "please give me a chronological YT feed" threads, nor r/youtube subscriber count. Community *size* captured; community *intent* not quantified.
3. **Feedvault actual backer/conversion count** — only the 15-backer *threshold* known, not whether it was hit or exceeded.
4. **Unhook/PocketTube paid conversion rates & revenue** — donation model means no public MRR; can't size actual dollars.
5. **Chrome Web Store install *trend* granularity** — have two snapshots for Unhook (2024, 2026); lack month-by-month curve. PocketTube trajectory (200K→300K) undated interval.
6. **Churn / retention** for any competitor — entirely absent. Install counts ≠ active retained users.

---

*Compiled Jul 2026. All figures as reported by cited sources on the dates shown; no values estimated or fabricated. Where a number was not found, it is marked as a gap rather than guessed.*
