# Customer Voice Research — YouTube Subscription Feed Pain

**Product context:** Peristalsis — freemium SaaS, RSS/WebSub-powered alternative YouTube subscription feed (group / favorite / filter subs, watch on YouTube). Target: YouTube power users frustrated with the broken subscription feed.

**Method:** 8 WebSearches across Reddit, ResetEra, Hacker News, YouTube/Google support forums, tech press (Android Authority, Piunikaweb, Lifehacker), personal blogs, extension review pages. Quotes captured verbatim with source + date where available. No quotes fabricated.

**Source tiering:**
- **Tier 1 (direct user voice):** Reddit/ResetEra/HN comments, extension store reviews, Google/YouTube Community threads — real users in their own words.
- **Tier 2 (aggregated user voice):** Tech press quoting/summarizing user threads (Piunikaweb, Android Authority, Lifehacker, DroidWin).
- **Tier 3 (vendor/marketing framing):** Competitor product pages and extension descriptions (Feedvault, PocketTube, RSS extractor READMEs) — useful for language, biased.

---

## 1. Pain Hierarchy (ranked by intensity + frequency)

| Rank | Pain | Intensity | Frequency signal |
|------|------|-----------|------------------|
| **1** | **Feed silently drops videos — "I miss uploads from channels I subscribe to"** | 🔴🔴🔴 Highest — described as "horrible," "broken," betrayal of the core promise | "decade-old meme," reported "multiple times a year," threads spanning years |
| **2** | **Algorithm/"Most relevant" hijacks the feed — no longer chronological** | 🔴🔴🔴 Very high — "destroying all value," "defeats the purpose" | Hot in 2026 (Feb rollout), multiple press pieces + HN/Reddit threads |
| **3** | **Shorts forced into the subscription feed, can't be turned off** | 🔴🔴 High — "annoying," "impossible to disable" | Perennial; huge extension ecosystem built solely to hide them |
| **4** | **No way to organize/group subscriptions (folders/tags)** | 🔴🔴 High — power-user specific but deeply felt | Long-standing feature request; large extension market (PocketTube 300k+) |
| **5** | **Layout removed / control taken away (list view, flow=2 killed)** | 🔴🔴 High — "last straw," Premium cancellations | Spiked Dec 2025–Feb 2026 |
| **6** | **Home tab competes with / buries the Subscriptions tab** | 🔴 Medium | Recurring background complaint |

**Root theme tying all six:** *Loss of control.* Users had a simple, deterministic tool ("everything from channels I chose, newest first") and YouTube keeps replacing it with algorithmic decisions they didn't ask for and can't opt out of.

---

## 2. Verbatim Quotes (grouped by theme)

### Theme A — Missing videos / feed unreliability (Pain #1)

> "I have about 100 subscriptions. When I go to the subs tab, it's supposed to show everything new from them. It doesn't though. **There are a ton of videos missing, and it is constantly like this.** I manually check out a channel and find a bunch of videos that I've missed."
> — ResetEra thread, "Why does YouTube not show all my subscription videos? Bug? Intentional?" (Tier 1; thread undated in capture)

> "It's on purpose. All these online services use ai to decide what to show you based on engagement even if you are subscibed/following someone. **It's horrible and theres not much you can do about it** unfortunately."
> — ResetEra reply, same thread (Tier 1)

> "This has been happening at least a year now and **it's totally deliberate. It's why YouTubers tell you to hit the notification button as well as subscribe.**"
> — ResetEra reply, same thread (Tier 1)

> "**YouTube's subscription system is famously unreliable. It is a decade-old meme at this point.** … People I talk to are affected multiple times a year. And, **it doesn't self-correct!** … This is good enough for an algorithmic feed, but not for a personal subscription system."
> — Timo Tijhof, "YouTube in a feed reader is… better?" personal blog, 2025 (Tier 1, technical user)

> "Some subscriptions show videos from 2 or 3 years ago as more recent ones even if in Youtube they have videos from yesterday!"
> — SmartTube GitHub Issue #4859 comment (Tier 1)

### Theme B — Algorithm / "Most relevant" hijack, non-chronological (Pain #2)

> "If anyone has a good solution to **YouTube destroying all value of the Subscriptions page** I'm all open ears."
> — Hacker News thread #47656042 (Tier 1)

> "the subscriptions page now shows videos 'in order', but **the order is wrong.** My current subscription page shows a video from 14 hours ago, then a video from 9 days ago, then one from 5 days ago, then 6 days ago, and then 1 day ago."
> — Hacker News #47656042 comment (Tier 1)

> "**I want to know what's newest** and the time ordered list being deprioritized in the UI and fractured makes that worse. … I do not watch shorts and I don't know how or why they mark things as a priority."
> — Hacker News #47656042 comment (Tier 1)

> "It now has categories like 'most relevant'. **I don't want YouTube deciding what's relevant, I want my feed for subscriptions, shown live first and then newest to oldest like it used to be.** I also don't want to see shorts from accounts I don't follow on my subscription page, nor headlines/news."
> — user quoted by DroidWin, "How to Fix/Get Back the Old YouTube Subscription Feed" (Tier 2)

> "Now it's such a mess that instead of fixing it, they instead force-implemented suggested videos at the top. **It totally defeats the purpose of having a subscriptions page.**"
> — user quoted by DroidWin (Tier 2)

> "people do not want the Subscriptions [page] turning into a second Home page … a general loss of that clean 'everything from channels I follow' [feed]." Users feel "the Subscriptions page is starting to feel like **'homepage 2.0.'**"
> — Piunikaweb, 2026-02-26 (Tier 2, summarizing Reddit threads)

### Theme C — Shorts intrusion (Pain #3)

> "I got caught by Shorts thumbnails and watch one, two, four and **I have lost a quarter of an hour in the blink of an eye.**"
> — Johan Bleuzen blog (Tier 1)

> "It's **impossible to disable Shorts on YouTube permanently**" (native), only reducible via extensions / desktop-mode hacks.
> — Buzzvoice guide summarizing the common user finding (Tier 2)

> Trying YouTube's own "Not interested" on Shorts "is like [drinking from] a firehose."
> — Lifehacker, "How to Hide YouTube Shorts From Your Feed" (Tier 2)

### Theme D — No grouping / organization (Pain #4)

> "My YouTube subs are a mess because I'm interested in a wide variety of topics and videos. No sorting method can tame that mess. … **I just want folders. Or tags. Or some sort — any sort — of manual organization that doesn't rely on the algorithm overlords.**"
> — Android Authority, "As a YouTube Premium subscriber, this is the only new feature I want" (Tier 2, first-person op-ed by power user)

> "**I don't want to keep missing out on great videos because the algorithm didn't surface them for me. I just want folders so I can dig into one topic and catch up on all of it.**"
> — Android Authority, same piece (Tier 2)

> Users on the extension: "the filter at the top stopped working … it always shows ALL of my subscribed channels regardless of which filters are toggled."
> — PocketTube Firefox review (Tier 1) — shows demand + fragility of extension solutions

### Theme E — Control removed / "last straw" (Pain #5)

> "[I tried ?flow=2 and] **it just displays that boring grid layout.**"
> — Reddit user quoted by Piunikaweb, 2026-02-03 (Tier 2)

> "One commenter said they **cancelled their Premium subscription over this specific update, calling it the last straw** after years of bad UI decisions."
> — Piunikaweb, 2026-02-03 (Tier 2)

> "i cancelled youtube premium when they removed dislike count … i felt that I was getting a inferior product once they removed that tool from me."
> — asklemmy thread (Tier 1) — pattern of control-removal driving churn

---

## 3. Language Map (exact words/phrases for marketing copy)

**How users describe what they want (use these verbatim):**
- "everything from channels I follow" / "everything from channels I chose"
- "newest to oldest like it used to be" / "shown live first and then newest to oldest"
- "I just want folders. Or tags."
- "dig into one topic and catch up on all of it"
- "an inbox of unwatched videos" (HN — strong framing: feed = inbox)
- "See every video in chronological order, not what YouTube thinks you want" (RSS extractor)
- "clean, chronological feed of only the channels you chose" (Feedvault framing)
- "no algorithm" / "check your feed when you want, not when YouTube wants"

**How users describe the pain (use in problem/agitation copy):**
- "a ton of videos missing, and it is constantly like this"
- "the order is wrong"
- "it totally defeats the purpose of having a subscriptions page"
- "homepage 2.0" / "a second Home page"
- "YouTube deciding what's relevant" / "the algorithm overlords"
- "famously unreliable … decade-old meme"
- "it doesn't self-correct"
- "the last straw"
- "boring grid layout"

**Emotional register:** frustration + resignation ("not much you can do"), betrayal ("defeats the purpose," "destroying all value"), desire for *control* and *simplicity*, nostalgia ("like it used to be"). Copy should promise **control, completeness, chronological order, and calm** — the opposite of "algorithm overlords."

---

## 4. Current Workarounds & complaints about them

| Workaround | What users say | Complaint / weakness |
|-----------|----------------|----------------------|
| **Native RSS feeds** (`youtube.com/feeds/videos.xml?channel_id=…`) into Feedly/Inoreader/NewsBlur/Miniflux | "See every video in chronological order, not what YouTube thinks you want"; "check your feed when you want, not when YouTube wants" | Manual setup: must dig channel IDs out of page source, build OPML by hand; bare feeds only show a linked title (no thumbnail/description) unless reader enhances; RSS only tracks new uploads going forward. Wesley Moore wrote a whole scripting post just to export subs. **High friction = opportunity for Peristalsis.** |
| **Browser extensions to hide Shorts/Most-relevant** (Unhook, ShortsBlocker, YouTube Subscriptions Cleaner, Control Panel for YouTube) | Popular; "hides them" | Break every time YouTube changes the DOM ("If YouTube updates their DOM and the selectors break, feel free to open a PR"); "Browser extensions like Unhook … might not work now"; desktop-only, don't sync to mobile/TV. |
| **Grouping extensions** (PocketTube 300k+ users, FolderTube, YT Sub Organiser) | Clearly strong demand for folders/tags/collections | Fragile: "the filter at the top stopped working … always shows ALL of my subscribed channels"; break after YouTube layout updates; tied to one browser; some reviewers call them "garbage" when filters fail. |
| **Manual channel checking** | "I manually check out a channel and find a bunch of videos that I've missed" | Tedious, defeats purpose of subscriptions; "its dumb that you have to do that though." |
| **Per-channel notification bell** | Recommended by creators as the fix | "its dumb that you have to do that"; notification spam; doesn't scale to 100+ subs. |
| **Dedicated web-app alternatives** (Feedvault, YT Zero, lurkkit, vFeed) + clients (FreeTube, NewPipe, LibreTube, ReVanced) | "rebuilds the subscription feed you actually wanted — chronological, grouped, no Shorts" | These are Peristalsis's **direct competitors** — validates the category; differentiate on WebSub freshness, grouping/filtering UX, watch-on-YouTube, freemium pricing. |
| **CSS via uBlock / Stylebot** (`##ytd-rich-section-renderer:has-text(Most relevant)`) | Power users share selectors | Requires technical skill; brittle; per-device. |

**Meta-complaint about all workarounds:** they are brittle band-aids fighting YouTube's DOM/algorithm. Users want something durable that doesn't break on every YouTube update — the RSS/WebSub approach (data layer, not DOM layer) is the durability angle Peristalsis should lean on.

---

## 5. Desired Outcomes (in users' language)

Users repeatedly describe the *same* ideal solution:
1. **Completeness** — "everything from channels I follow," nothing silently dropped, "it doesn't self-correct" → they want a feed that *does*.
2. **Chronological order** — "newest to oldest like it used to be," "I want to know what's newest," strict reverse-chron.
3. **No algorithm / no Shorts / no recommendations** — "not what YouTube thinks you want," "no algorithm overlords."
4. **Organization** — "folders. Or tags," "dig into one topic and catch up on all of it," "grouped by topic."
5. **Feed-as-inbox** — "an inbox of unwatched videos and everything else is hidden" (mark-as-watched, catch-up semantics).
6. **Cross-device durability** — sync across devices; not tied to one browser; doesn't break on YouTube UI changes.
7. **Still watch on YouTube** — most want to *keep watching on YouTube*, just fix discovery (validates Peristalsis's "watch on YouTube" model — no need to re-host video).

Explicit wishlist quote: *"I just want folders … manual organization that doesn't rely on the algorithm overlords."* and *"my Subscriptions page acts like an inbox of unwatched videos and everything else is hidden."*

---

## 6. Data Gaps

- **Exact dates missing** for ResetEra/HN/Reddit comments (threads captured without per-comment timestamps). Should re-visit source URLs to pin dates before quoting publicly.
- **Reddit primary threads not directly opened** — most Reddit voice came via press aggregation (Tier 2). Recommend pulling raw r/youtube, r/DataHoarder, r/selfhosted threads directly for stronger Tier 1 quotes.
- **Quantification** — no hard numbers on % of users affected or search-volume trends; "multiple times a year," "300k+ users," "decade-old meme" are qualitative. Needs Google Trends / keyword volume for sizing.
- **Willingness to pay** — zero data on whether these frustrated users will *pay* (freemium critical question). Competitors (Feedvault, PocketTube Patreon) exist but conversion/pricing tolerance unknown. **Biggest gap for a freemium SaaS thesis.**
- **Segment split** — power users (100+ subs, org-focused) vs. casual (just want chronological) not quantified; may need different messaging.
- **Mobile/TV pain** — signals suggest mobile app feed is *more* aggressively reordered than desktop, but user voice thinner here; worth targeted research since a web app may not solve mobile/TV natively.
- **Recency skew** — much intensity is tied to the Feb 2026 "Most relevant" rollout; unclear how much is durable pain vs. a spike that fades if YouTube reverts.

---

*Sources consulted:* ResetEra, Hacker News (#47656042), Timo Tijhof blog (2025), SmartTube GitHub #4859, Android Authority (subscription folders op-ed; TV experiment piece), Piunikaweb (2026-02-03, 2026-02-26), DroidWin, Lifehacker, Buzzvoice, Johan Bleuzen blog, Marco Heine blog, Wesley Moore blog (2024), PocketTube (site + Firefox reviews), youtube-rss-extractor / YouTube Subscriptions Cleaner READMEs, Feedvault, asklemmy/Lemmy, Yahoo Tech (Premium price-hike backlash).
