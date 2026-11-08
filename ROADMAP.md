# Roadmap

<!-- Next task number: [044] -->

## [031] Scale ingestion past one server IP

**Status:** `freezer`
**Depends On:** [029]

### Goal

Ingestion keeps working when the channel table is large enough that a full 30-minute sweep no longer fits inside the safe request rate of a single server IP. Deferred on purpose: at 193 channels [029]'s poll-everything sweep is correct and this is all complexity for no gain. This item exists so the analysis is not re-derived from scratch when it is needed, and so [029] can stay simple without pretending the ceiling does not exist.

### Scope

- Measuring the actual per-IP request ceiling, which is currently unknown
- Cadence-tiered and demand-ordered channel selection
- The YouTube Data API as a second, IP-independent request budget
- Distributed polling across owned servers
- **Not in scope:** residential proxy rotation. See the reasoning below

### Technical Notes

**Trigger for unfreezing:** a [029] sweep hitting `POLL_MAX_PER_SWEEP`, sustained block signals from the [029] detector, or a deliberate decision to take the app multi-tenant. Not before.

**The central unknown.** `reference/YOUTUBE_RSS_RATE_LIMITS.md` establishes that no per-IP RSS ceiling is published, and that the `~1,500-2,000 channels` figure inherited into [021] is unsourced and probably misattributed from a FreeTube post describing a different endpoint under an explicit RSS exemption. Every scaling decision below multiplies a number nobody has measured. **Measure it first:** ramp the [029] sweep's request rate stepwise on production and watch the [029] block detector. That converts the central unknown into a number, and it is cheap. Everything else here is guesswork until it is done.

**Why a fixed budget beats a staleness threshold at scale.** Poll-everything makes the outbound request rate grow linearly with channel count. A fixed hourly budget with rotation makes it a constant you set, and freshness degrades gracefully instead of the request rate exploding. At a 200/hour budget:

| Channels | Refresh interval | Requests/day |
| --- | --- | --- |
| 193 | ~1 hour | ~4,600 |
| 1,000 | ~5 hours | ~4,600 |
| 10,000 | ~2 days | ~4,600 |
| 50,000 | ~10 days | ~4,600 |

The 50,000 row is why a flat budget alone is not the answer either: RSS returns only the ~15 most recent videos per channel, so a 10-day interval starts losing uploads outright on active channels, not merely delaying them.

**Cadence tiering is the largest single lever, and it costs no infrastructure.** Flat rotation gives a dormant channel the same slot as a daily uploader. Derive an expected upload interval per channel from its own video history and poll proportional to it. A rough 50,000-channel model:

| Tier | Share | Poll every | Requests/day |
| --- | --- | --- | --- |
| Hot, 2+ uploads/week | 10% (5,000) | 2 hours | 60,000 |
| Warm, weekly | 25% (12,500) | 12 hours | 25,000 |
| Cold, monthly | 40% (20,000) | 3 days | 6,700 |
| Dormant, 6 months silent | 25% (12,500) | 7 days | 1,800 |
| **Total** | 50,000 | | **~93,500/day** |

Roughly 13x cheaper than flat 30-minute polling of the same table, while leaving hot channels far fresher than any flat rotation would.

**Demand ordering, not demand-driven fetching.** Polling a channel only when a user loads a feed containing it scales request volume with active attention rather than catalog size, which is a genuinely better scaling property. Fetching *during* the request is not: a feed with 200 stale channels blocks first paint for tens of seconds, which is why [021] removed exactly that behaviour and [022] made feed reads pure DB. Demand is also bursty in the worst possible shape, since a morning login spike is precisely the burst pattern that draws an IP block, whereas a budget smooths by construction.

The synthesis is to keep the budget as the rate governor and let demand drive **priority**: a feed load records want on the channel rows (a cheap non-blocking write, gated by the fetch TTL so repeat loads do not pile up), and the sweep selects `ORDER BY demand DESC, last_fetched_at ASC`. Feed reads stay pure DB, the queue absorbs the spike so the cost of a burst is latency rather than a block, and an empty demand queue means a sweep that costs nothing. A **floor** stays underneath it, polling every channel at least once every N days regardless of demand, so an unviewed channel does not accumulate permanent gaps against the ~15-item RSS cap.

**The Data API is a separate budget, and cheaper than [023] assumes.** `playlistItems.list` against a channel's uploads playlist costs **1 unit** and returns up to 50 videos (confirmed against Google's quota-cost documentation, 2026-08-24). The free 10,000 units/day is therefore ~10,000 channel checks/day, and granted quota increases are commonly 50,000-100,000. Two properties matter more than the volume: the quota is **per project, not per IP**, so it is an entirely independent ceiling with no block risk; and a unit quota tolerates bursts that an IP ceiling does not, which makes it the natural home for demand spikes while RSS carries the floor. The 50,000-channel tiered model above, ~93,500 checks/day, fits inside a single granted quota increase with no proxies and no server fleet.

The audit is the constraint, not the quota: reported waits run from weeks to several months, and denials cluster on vague use cases, missing privacy policies, and anything resembling bulk download. Apply well before the capacity is needed.

**Distributed polling is legitimate but is a later rung.** N owned servers each polling a disjoint channel slice gives N times the per-IP limit and is ordinary horizontal scaling, spreading real load rather than concealing it. Three caveats: it multiplies a ceiling nobody has measured; N droplets from one provider are N addresses in ranges that already score worse than residential and are trivially correlated by ASN, so spread across providers and regions or the gain is well under N; and it carries real operational weight in slice assignment, result shipping, per-node health, and partial-failure semantics.

**Residential proxy rotation is out.** It is evasion rather than architecture, defeating an anti-abuse decision deliberately applied to us by making traffic appear to come from unrelated home users. It is also a bad engineering bet: an arms race against Google's anti-abuse team that breaks without warning, poisons any future relationship with YouTube, and rests on a supply chain that is independently ethically compromised.

**Escalation ladder, in order:**

1. Measure the real per-IP ceiling
2. Cadence tiering plus demand ordering, with a floor underneath
3. Data API `playlistItems.list` for the hot tier, RSS for the cold tier
4. Data API quota increase application, applied for early
5. Distributed polling across owned servers, mixed providers and regions

**Related:** `reference/YOUTUBE_RSS_RATE_LIMITS.md` sections 5, 7, and 8 are the evidence base for all of the above. Section 6 records a platform-wide `videos.xml` outage over 13-17 February 2026, which is the standing argument for holding a second ingestion path that does not touch that endpoint, and which [030] should weigh before deleting WebSub outright.

### Acceptance Criteria

- [ ] The per-IP request ceiling is measured on production and recorded as a number, replacing the unsourced `~1,500-2,000` figure
- [ ] Channel selection is tiered by observed upload cadence rather than treating every channel alike
- [ ] Feed loads raise a channel's poll priority without blocking the request, and feed reads stay pure DB
- [ ] A floor guarantees every channel is polled at least once every N days regardless of demand
- [ ] Outbound request rate stays bounded by a value we set, whatever the channel count or demand spike
- [ ] The 15-item RSS cap never silently drops an upload at the configured tier intervals

---

