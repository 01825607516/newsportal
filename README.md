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
