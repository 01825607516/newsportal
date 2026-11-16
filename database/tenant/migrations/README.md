# Tenant migrations

Plain `.sql` files, applied to **every** tenant database exactly once, in file-name order.

- `database/tenant/schema.sql` is the frozen baseline. Do not edit it for new changes.
- A schema change = a new file here, e.g. `2026_11_20_000001_add_slug_to_news_common_info.sql`.
- New tenants get the baseline and then all files here (during provisioning).
- Existing tenants get them with `php artisan tenants:migrate` (this also refreshes the views).
- Each file may hold several statements separated by `;`.
- `SELECT *` views are frozen at creation time, so after adding a column to a table the view must
  be recreated: `tenants:migrate` does that, because it re-runs `database/tenant/views.sql`.
