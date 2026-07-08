# YouTube RSS Feed Audit

What YouTube's channel RSS feed exposes, and what this app currently ingests.

- **Feed URL:** `https://www.youtube.com/feeds/videos.xml?channel_id={UC…}` (built in `Channel::rssUrl()`, overridable via a stored `rss_url`).
- **Parser:** `app/Services/RssFetcher.php::ingest()`, using `simplexml_load_string` with the `yt` and `media` namespaces.
- **Format:** Atom 1.0 with the `yt:` (YouTube) and `media:` (Media RSS) namespace extensions.

## Feed-level fields

| Field | Notes | Used today |
| --- | --- | --- |
| `<title>` | Channel name | Yes — updates `channels.name` |
| `<yt:channelId>` | The `UC…` channel id | No |
| `<author><name>` / `<author><uri>` | Channel author + URL | No |
| `<published>` | Channel creation date | No |
| `<link>` | Channel page + self feed URL | No |

## Per-`<entry>` (video) fields

| Field | Notes | Used today |
| --- | --- | --- |
| `<yt:videoId>` | Video id (required; entries without it are skipped) | Yes — `videos.youtube_video_id` |
| `<yt:channelId>` | Owning channel id | No |
| `<title>` | Video title | Yes — `videos.title` |
| `<published>` | Publish timestamp | Yes — `videos.published_at` (falls back to `now()`) |
| `<updated>` | Last-updated timestamp | No |
| `<author><name>` / `<author><uri>` | Author name + channel URL | No |
| `<link rel="alternate" href>` | Watch or `/shorts/` URL | Partially — read only to detect Shorts, which are then deleted and skipped; the URL itself is not stored |
| `<media:group><media:title>` | Duplicate of `<title>` | No |
| `<media:group><media:content url/type/width/height>` | Video stream URL + dimensions | No |
| `<media:group><media:thumbnail url/width/height>` | Thumbnail | Partially — only the `url` is stored (`videos.thumbnail_url`); dimensions ignored |
| `<media:group><media:description>` | Full video description | No |
| `<media:group><media:community><media:starRating count average min max>` | Like/rating signal | No |
| `<media:group><media:community><media:statistics views>` | View count | No |

## Currently stored vs available-but-unused

**Stored** (`videos` table): `youtube_video_id`, `title`, `thumbnail_url`, `published_at`, plus the `channel_id` FK.

**Available but unused:** `yt:channelId`, `updated`, author name/uri, watch URL, `media:content`, thumbnail dimensions, `media:description`, `media:starRating` (rating), `media:statistics` (views).

## Popularity signal

Yes, a popularity signal exists per entry:

- `media:statistics @views` — total view count.
- `media:starRating @average` / `@count` — rating average and number of ratings.

**Caveat:** these are point-in-time snapshots captured at each fetch. The feed carries no historical series, so any "trending" / velocity metric (views gained per day, etc.) would require the app to store successive snapshots itself and compute the delta. A single fetch only supports "most viewed / best rated right now," not "rising."

This informs roadmap `[005]`: an optional "top N by views" ordering within a per-subscription cap is feasible, but only as an absolute snapshot unless view history is persisted.
