# PMMA Registrar Portal

A Laravel portal template for the Philippine Merchant Marine Academy — Office of the Registrar. It reuses the PMMA Laravel template's colors, typography and local assets. The home page has a branded masthead, navigation and a footer around an empty content area. The `/services` page explains how to request school documents online or through a representative.

## Run locally

Requires PHP 8.2 or later with Laravel's required extensions and Composer.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan serve
```

Open http://127.0.0.1:8000. You can also use a local Herd site with its document root set to `public`. The blank portal requires no database, seed data, Node.js or asset build.

## Extend the portal

- Add pages in `resources/views/registrar`. Extend `layouts.registrar` and fill the `heading` and `content` sections.
- Register pages in `routes/web.php` and update the navigation in `resources/views/layouts/registrar.blade.php`.
- Set `APP_NAME`, `BRAND_ORGANIZATION` and `BRAND_OFFICE` in `.env` to customize labels. Keep shared design rules in `public/assets/css/pmma.css` and Registrar-specific rules in `public/assets/css/registrar.css`.
- The Registrar masthead uses the supplied `public/assets/images/pmma-seal.png`. The three logos on the right come from the [PMMA website masthead](https://web.archive.org/web/20261004220343/https://pmma.edu.ph/) and are also served locally from `public/assets/images`.

The upstream Helpdesk demo remains available to its PHPUnit tests. Its routes, staff creation command and database migrations load only when `APP_ENV=testing` and `HELPDESK_DEMO=true`. The PHPUnit configuration sets both for the existing suite. A normal installation serves the Registrar portal and uses only the base Laravel migrations. [CUSTOMIZATION.md](CUSTOMIZATION.md) and [REUSE_GUIDE.md](REUSE_GUIDE.md) describe the upstream components.

## Prepare a release

Install locked dependencies with `composer install --no-dev --optimize-autoloader`. Set `APP_ENV=production`, `APP_DEBUG=false`, a stable `APP_KEY` and the HTTPS `APP_URL` in the server environment. Serve only the `public` directory and ensure `storage` and `bootstrap/cache` are writable. Use `php artisan optimize` to cache configuration, routes and views. Keep environment files, credentials and runtime data outside Git.

Keep [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md) and the bundled license headers when reusing the template.
