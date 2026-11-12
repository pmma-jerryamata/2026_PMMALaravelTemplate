# PMMA Systems Laravel Template

A reusable Laravel application starter extracted from the PMMA Helpdesk visual system. Same navy / blue / orange palette, typography, public portal, staff dashboard, mobile sidebar, expandable tables, forms, dialogs and charts. Generic logo and branding defaults; no production data, environment credentials or default user accounts.

The working ticket module is included as a reference implementation. This is a source-code starter, not a no-code app builder: replace its domain models, controllers, routes and migrations to build another type of application.

## Start a new app (Windows / Herd)

Requirements: PHP 8.2+ with PDO MySQL and standard Laravel extensions, Composer, and MySQL/MariaDB. SQLite is used for automated tests.

1. Create a new repository from this template, or clone this repository into Herd using your new project name.
2. Open a terminal in the project folder.
3. Run `composer install`.
4. Copy `.env.example` to `.env`: `Copy-Item .env.example .env` in PowerShell.
5. Set APP_NAME, APP_URL, BRAND_ORGANIZATION and database connection values in `.env`.
6. Create a NEW empty database matching DB_DATABASE. Do not point a new starter at an existing production database.
7. Run `php artisan key:generate`.
8. Run `php artisan migrate`.
9. Run `php artisan helpdesk:create-staff` to create your own login. It asks for your name, email and password. Sign in using the email. No default password is seeded.
10. Open the Herd URL for the project folder, or run `php artisan serve`.

The current layouts load ready-to-use assets from `public/assets`; Node/Vite is not needed to run these layouts. If you start using the Vite entrypoints, install npm dependencies and run `npm run build` separately.

## Rename and brand

- `.env`: APP_NAME="My Application", BRAND_ORGANIZATION="Your Organization", APP_REFERENCE_PREFIX=APP.
- `config/branding.php`: organization and ticket-reference prefix configuration.
- `app/Models/SystemSetting.php`: default application subtitle, support tagline and footer credit. APP_NAME is used until overridden in System Settings.
- System Settings > General: database-based branding overrides for the running app.
- `public/assets/images/app-logo.svg`: replace with your own logo. Used by portal, dashboard and login layouts.
- `resources/views/layouts/portal.blade.php`, `dashboard.blade.php`, `auth.blade.php`: shared layouts. Adapt visible headings/navigation for your app.
- `public/assets/css/pmma.css`: shared UI styles and design tokens. Filename is intentionally retained so asset links remain valid; it contains no account data.
- `public/assets/css/dashboard-theme.css`: staff dashboard theme and mobile drawer.

After changing `.env` or config on a cached installation, run `php artisan optimize:clear`.

## Latest reusable components

The starter now includes grouped navigation, stable sidebar sizing, collapsible filters, fullscreen request creation with issue choices, public request totals, service reordering, back-to-top, persistent table position, and server-side rate limiting with durable duplicate-submit protection. See [REUSE_GUIDE.md](REUSE_GUIDE.md) for the component map, migration requirements and patterns for other modules.

## Replace business functions

See [CUSTOMIZATION.md](CUSTOMIZATION.md) for the layout/component map and module replacement checklist. Fresh migrations include sample office/service catalogs from the original campus workflow. They are sample configuration, not live database exports. Replace these before the first migration for another organization, or manage them in System Settings. Historical migration filenames are intentionally retained.

The test-only DevUserSeeder can only run when APP_ENV=testing. DatabaseSeeder creates no accounts. Never ship test credentials as a production login.

## Verify

Run `php artisan test`. Existing tests document the sample ticket module. Update those tests as you replace domain behavior. Dependencies, local environment files, databases, session files, logs, uploads and generated caches should stay outside version control; the included `.gitignore` files exclude these runtime files.

## Template versions

Track template source changes directly in Git. Use version tags such as `v1.0.0` for releases instead of committing ZIP snapshots. Increase the patch version for fixes, the minor version for backward-compatible additions, and the major version for breaking changes. Document setup or migration changes in the release notes so projects using the template can review updates before applying them.

## Production

Use a separate production `.env` with APP_ENV=production, APP_DEBUG=false, HTTPS APP_URL and real database credentials. Install with `composer install --no-dev --optimize-autoloader`, generate a fresh APP_KEY once on first setup, run migrations, then cache config/views as appropriate. Keep APP_KEY stable for later updates. Point the domain document root ONLY to `public`, and make storage and bootstrap/cache writable by the PHP process. Production subdirectory deployments require their own RewriteBase and URL/session-path configuration; IONOS-specific deployment edits from the original app are not packaged.

Code deployment does not copy local database rows to production. Back up before deliberate data transfers. Never upload `.env` or database dumps to GitHub.

## Attribution

Keep [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md) and the bundled vendor license headers when reusing this design. A template copy does not grant rights to the original institution's branding; this starter uses a generic logo.
