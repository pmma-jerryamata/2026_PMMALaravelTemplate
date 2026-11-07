<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Local serving

Serve this application with `public` as the document root. The [public entry point](public/index.php) delegates to the existing root bootstrap, allowing `php artisan serve --host=127.0.0.1 --port=8000` without exposing root configuration files or SQL backups. Configure a separate local database before serving; do not use hosting credentials for local testing. Set `MAIL_MAILER=array` locally to prevent real email delivery.

For the configured Windows development machine, run `start-local.cmd` from this directory. It starts the local MySQL Community database and serves the application at `http://127.0.0.1:8001` using PHP 8.2. The launcher expects PHP under `%LOCALAPPDATA%\Programs\PHP\8.2` and MySQL Community under `C:\Program Files\MySQL\MySQL Server 8.4`.

The local database is `pmma_support_local`. Database configuration, logs, local login details (`login-details.txt`), and the original hosting environment backup are stored under `%LOCALAPPDATA%\PMMA-Support-Local`, outside this repository. The local `.env` uses a separate application key and database account, with email delivery disabled. Local junctions under `public` serve the root `css`, `js`, and `assets` directories, so edits to `css/styles.css` are reflected when refreshing the page.

## Helpdesk design adaptation

The active portal and staff workspace use the design assets and Blade layouts from `pmma-helpdesk.zip`, adapted to this application's existing Laravel 9 models. Assets are under `public/helpdesk`, templates under `resources/views/helpdesk`, and the database-to-interface adapter is `HelpdeskController`. Design attribution is retained in `HELPDESK_DESIGN_NOTICES.md`.

Service categories come from `tasks`. Requests use `supports` and the existing employee, department, technician, and survey relationships. Tracking uses the original numeric ticket IDs; the only ticket statuses are `Pending`, `Completed`, and `Cancelled`. Requesters must use an existing active employee email. Existing email notifications and cadet registration are retained.

Request-form email lookup returns only `{"valid": true}` on success. It never returns directory names, departments, employee IDs, or other profile fields. The form shows a check inside the email textbox and does not render requester name/department fields, including for prefilled email links. Ticket submission still resolves the employee internally on the server and validates the email again.

Unsupported features from the ZIP are omitted: priority, separate ticket subject, department code and activation, extended technician profiles and login accounts, ticket timelines, activity logs, database-backed branding, and deployment history. No database migration or record conversion is needed. No ZIP database, environment, framework, or vendor files are installed.

Run the focused integration tests with `php vendor/bin/phpunit tests/Feature/HelpdeskPresentationTest.php`. These tests explicitly use an isolated SQLite in-memory database and never migrate or write to the configured MySQL database.

Run the entire suite with `php vendor/bin/phpunit`; `phpunit.xml` forces SQLite memory for test safety. For integration checks against the installed MySQL engine and the actual legacy table definitions, run `php scripts/test-helpdesk-mysql.php`. This copies table definitions only, creates a disposable test database with a restricted account, runs the helpdesk tests, and removes the test database/account afterward. The runner reads local administrator credentials from `%LOCALAPPDATA%\PMMA-Support-Local\credentials.json`, or the path in `HELPDESK_MYSQL_ADMIN_CREDENTIALS`.

Admin entry points `/admin`, `/admin/index`, `/admin.php`, and `/admin/index.php` lead to the authenticated dashboard. Staff sign-in is at `/login`; it uses an existing `users.email` and password. Feedback submissions use signed POST requests, retain the existing ratings tables, and accept one response per ticket. Layout assets use a modification-based version to refresh updated styles automatically.

## Dashboard tables and mobile layout

All staff tables (tickets, employee/department/technician/service settings, surveys, and report entries) use the bundled DataTables 2.3.4 assets. Tickets, settings, and surveys search, sort, and paginate the full dataset through authenticated, read-only `/dashboard/tables/*` endpoints. Sorting uses server-defined column allowlists, and requests are limited to 100 rows per page. Reports search/sort the already filtered date groups locally; table search never changes the official PDF export filters or document format.

On mobile, table rows become labeled cards with a separate Sort by/direction control, avoiding horizontal scrolling. Labels are reapplied after every DataTables draw. Settings navigation wraps into a two-column grid on phones. Standard server-rendered tables and pagination remain the fallback when JavaScript is unavailable.

Run `node tests/Frontend/dashboard-tables.cjs` for DOM integration checks against the actual bundled DataTables scripts. The test requires `jsdom` in local Node resolution or under `%LOCALAPPDATA%\PMMA-Support-Local\dom-tests\node_modules`. These checks simulate DOM interactions; they do not substitute for a visual browser check.

## Official report and print format

All report PDF exports and print previews use the original `resources/reports/ar_blank.pdf` as an unscaled page background. Do not recreate, replace, reposition, or restyle its letterhead, sidebar, logos, revision text, or margins. The only permitted template change is the page-number area for a multi-page document. The template SHA-256 is `f48a60f70b022f43a2b720a3864e4ae5dbaf18e882ebf3026733f4cc106e4eb6`.

The body follows the supplied filled AR reference: Cambria 12 pt for headings, dates, narrative and signatures; Arial 11.04 pt for accomplishments; original 595.44 × 841.68 pt paper; body left edge 108.02 pt and table right edge 561.96 pt. `App\Services\Reports\AccomplishmentPdf` owns these measurements and pagination. Reuse this document template and renderer for future report/print exports instead of printing application HTML or designing another letterhead. Generated font resources are bundled under `resources/reports/fonts`; no Python runtime is required to export PDFs.

Reports use `Completed` tickets grouped by completion/update date, with the ticket findings (or concern details when findings are empty) and existing ticket/department references. The date range and technician are selectable. Prepared-by/noted-by names and positions are entered per export and are never stored in the database. `/report/pdf` serves the PDF inline for printing, or as a download with `download=1`. Print at Actual size / 100% to preserve the form dimensions.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
