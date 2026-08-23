# WebSub local setup (cloudflared + Cloudflare)

Google's PubSubHubbub hub must reach a **public HTTPS** callback. Herd `.test` is local-only, so use a Cloudflare Tunnel for MVP testing. Staging/prod use the Forge VPS `APP_URL` (leave `WEBSUB_CALLBACK_BASE` unset there).

## Prerequisites

- Domain purchased (any registrar)
- [Cloudflare](https://dash.cloudflare.com) account
- [cloudflared](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/) installed
- Laravel Herd serving this app (e.g. `https://yt-rss.test`)

## 1. Point domain DNS at Cloudflare

1. Add the domain in Cloudflare Dashboard → **Add a site**.
2. Choose Free plan.
3. At your registrar, replace nameservers with the two Cloudflare nameservers shown.
4. Wait until Cloudflare marks the zone **Active**.

## 2. Create a tunnel

```bash
cloudflared tunnel login
cloudflared tunnel create yt-rss-websub
```

Note the tunnel UUID printed. Credentials land in:

- Windows: `%USERPROFILE%\.cloudflared\<UUID>.json`
- macOS/Linux: `~/.cloudflared/<UUID>.json`

## 3. Config file

Create `%USERPROFILE%\.cloudflared\config.yml` (adjust paths/hostname):

```yaml
tunnel: <UUID>
credentials-file: C:\Users\<you>\.cloudflared\<UUID>.json

ingress:
  - hostname: websub.peristalsis.tv
    service: https://yt-rss.test
    originRequest:
      noTLSVerify: true
      originServerName: yt-rss.test
      httpHostHeader: yt-rss.test
  - service: http_status:404
```

`noTLSVerify: true` is fine for Herd's local cert. Do **not** use that against production origins.

`httpHostHeader` is required: Herd routes by `Host`, and without it cloudflared forwards `websub.peristalsis.tv`, which Herd does not serve. `originServerName` makes the TLS SNI match Herd's local certificate.

Because the app then sees `Host: yt-rss.test`, keep `APP_URL=http://yt-rss.test` and set `WEBSUB_CALLBACK_BASE` to the public tunnel origin (step 6) so callback URLs point at the tunnel, not at `.test`.

## 4. DNS route for the hostname

```bash
cloudflared tunnel route dns yt-rss-websub websub.peristalsis.tv
```

## 5. Run the tunnel

```bash
cloudflared tunnel run yt-rss-websub
```

Confirm `https://websub.peristalsis.tv/up` returns Laravel's health response.

## 6. App env

In `.env`:

```env
WEBSUB_CALLBACK_BASE=https://websub.peristalsis.tv
```

Optional overrides (defaults are fine):

```env
WEBSUB_HUB_URL=https://pubsubhubbub.appspot.com/subscribe
# WEBSUB_USER_AGENT=Mozilla/5.0 ...
```

Then clear config cache if you use it:

```bash
php artisan config:clear
```

Callback URLs become: `https://websub.peristalsis.tv/websub/{token}`.

## 7. Live verification checklist (Slice 3)

With tunnel running and `WEBSUB_CALLBACK_BASE` set:

1. Add a real channel by UC id on Subscriptions.
2. Confirm `channel_subscriptions` row: `status=pending`, then after hub challenge → `active`, `lease_seconds` / `expires_at` filled.
3. Confirm ~15 existing videos backfilled (one poll).
4. Publish (or wait for) a new upload on that channel; confirm a new `videos` row without opening the feed (or after feed refresh).
5. Record for `[022]`:
   - Time from subscribe POST → challenge GET
   - Actual `lease_seconds` granted
   - What happens if callback returns 5xx (redelivery?)

### Findings (fill during live run)

Live run 2026-08-23, channel `UCHMaWSgPAVlLohSt1MSZ1ag` (Dejojotheawsome), tunnel `websub.peristalsis.tv`.

| Observation | Value |
|---|---|
| Verification timing | ~2s from subscribe POST to challenge GET (row created 17:11:42, `last_verified_at` 17:11:44) |
| Lease seconds granted | 432000 (5 days) |
| 5xx redelivery behavior | not yet observed |
| Backfill | 15 feed entries, 6 stored, 9 skipped as Shorts (expected: `ingest` filters `/shorts/` alternate hrefs) |
| Push delivery of a new upload | not yet observed (waiting on a real upload) |

Implication for `[022]`: a 5-day lease means renewal must run well inside 5 days. A daily renewal sweep that re-subscribes anything expiring within ~2 days gives two retry opportunities before a lease lapses.

## Forge / production

- Set `APP_URL` to the public HTTPS site.
- Leave `WEBSUB_CALLBACK_BASE` empty (falls back to `APP_URL`), or set it explicitly to the public origin.
- Ensure `/websub/*` is reachable without auth (already outside the `auth` middleware group; CSRF-exempt).
- Queue worker not required for MVP (subscribe/backfill run sync at add-time).
