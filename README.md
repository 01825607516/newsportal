# Phoenix Project Management (Laravel + Vue)

This project serves the existing Vue 3 Phoenix dashboard through Laravel 12. Laravel owns the HTTP routes and Blade shell; Vue mounts the dashboard inside that shell.

## Setup

Requirements: PHP 8.2+, Composer, and Node.js 20.19+ or 22.12+.

```sh
composer install
copy .env.example .env
php artisan key:generate
npm install
npm run build
php artisan serve
```

Open `/` or `/dashboard/project-management`. For development, run `npm run dev` in one terminal and `php artisan serve` in another.

Phoenix files remain under `v1.23.0/`; the build script copies `assets/` and `vendors/` into Laravel's public directory.

## Multi-tenant database (one MySQL database per portal)

When a portal signs up (`/signup`) `TenantProvisioner` creates `<TENANT_DB_PREFIX><portal id>`, imports
`database/tenant/schema.sql`, applies `database/tenant/migrations/*.sql`, (re)creates the views in
`database/tenant/views.sql` and seeds the first rows via `App\Services\TenantSeeder`.

| Task | How |
| --- | --- |
| Repair / create one tenant DB | Admin panel > portal > "Create / repair tenant database" |
| Add or change a view | edit `database/tenant/views.sql` (use `CREATE OR REPLACE VIEW`), run `php artisan tenants:sync-views` |
| Change a table | add a file to `database/tenant/migrations/`, run `php artisan tenants:migrate` |
| Change a MAIN-database table | add a `.sql` file in `database/main/` (see `database/main/README.md`; never run `artisan migrate`) |
| Create an admin | `php artisan admin:create email password "Name"` |

Reusing this for another system: replace `schema.sql` and `views.sql`, point `tenancy.seeder` to your
own seeder class, and set a unique `TENANT_DB_PREFIX` per environment. The tenant DB user needs
CREATE / DROP DATABASE and CREATE VIEW privileges. A tenant DB that is only half imported and
holds no data is wiped and imported again; one that holds data is never touched automatically.
