# PMMA Helpdesk

Campus IT service request and ticket management system for the Philippine Merchant Marine Academy, Information Technology Services Unit (ITSU).

Requesters submit concerns using an active email in the PMMA directory and track them through numeric ticket references. Authenticated staff manage tickets, technician assignments, findings, catalogs, feedback, and accomplishment reports.

## Documentation

| Document | Contents |
| --- | --- |
| [System documentation](docs/SYSTEM_DOCUMENTATION.md) | Scope, users, features, operating procedures, and limitations |
| [Technical specifications](docs/TECHNICAL_SPECIFICATIONS.md) | Architecture, database, routes, validation, security boundaries, and report format |
| [Process flows](docs/PROCESS_FLOW.md) | Request, tracking, status, feedback, reporting, and directory diagrams |
| [Layout preview](docs/layout-preview.html) | Standalone visual reference for the public portal, ticket wizard, printable report, and staff dashboard |
| [Design notices](HELPDESK_DESIGN_NOTICES.md) | Design origin and bundled asset attribution |

Documentation describes the repository implementation reviewed on **7 November 2026**. It does not certify the configuration or availability of the hosted deployment.

## Layout preview

Open [docs/layout-preview.html](docs/layout-preview.html) in a browser for a self-contained visual sample of the current public and authenticated layout patterns. It is documentation only: its controls are non-functional and it contains no real ticket data.

## Features

- Directory-based request submission with session-based duplicate-submission protection.
- Numeric ticket tracking, session-protected receipts, latest ten requests, and seven-day queue view.
- Staff dashboard with search, filters, charts, technician assignment, and findings.
- Three ticket statuses: `Pending`, `Completed`, and `Cancelled`.
- Employee activation/deactivation and department, technician, and service maintenance.
- Confirmation/status emails and signed, single-response ticket feedback.
- Accomplishment reports with date/technician filters, editable introduction, per-export signatories, and official-template PDF output.
- Responsive DataTables with authenticated search/sort/pagination and server-rendered fallbacks.

## Runtime and local setup

The application uses Laravel 9, Blade, MySQL, and Composer-managed PHP dependencies. Composer declares PHP `^8.0.2`; the configured Windows launcher selects PHP 8.2. Run `composer check-platform-reqs` against the installed lockfile. Node.js/npm are used for Vite builds and JavaScript checks. Python is not required to export PDFs.

### Existing Windows environment

```powershell
.\start-local.cmd
```

Open `http://127.0.0.1:8001`. The launcher expects:

- PHP: `%LOCALAPPDATA%\Programs\PHP\8.2\php.exe`.
- MySQL: `C:\Program Files\MySQL\MySQL Server 8.4\bin`.
- External local MySQL configuration: `%LOCALAPPDATA%\PMMA-Support-Local\my.ini`.

The launcher starts an already provisioned environment. It does not install dependencies or initialize a database. Local credentials and login details belong outside source control under `%LOCALAPPDATA%\PMMA-Support-Local`.

### Another development machine

1. Install PHP/extensions, Composer, and a compatible MySQL server.
2. Run `composer install` and `composer check-platform-reqs`.
3. If `.env` is absent, copy `.env.example` to `.env`. Configure a separate local database, `APP_URL=http://127.0.0.1:8001`, and `MAIL_MAILER=array` to suppress actual email delivery.
4. For a new local installation only, run `php artisan key:generate`.
5. Obtain the approved legacy schema and required directory/service/rating records from the maintainer. **Historical migrations alone do not reproduce the supplied database schema.** See [database specifications](docs/TECHNICAL_SPECIFICATIONS.md#database).
6. Run `php artisan config:clear`, then `php artisan serve --host=127.0.0.1 --port=8001`.

Serve **`public/` as the web document root**. Its entry point delegates to the root bootstrap. Do not expose the project root, environment files, SQL backups, or source ZIPs. Some legacy pages need root `css`, `js`, and `assets` exposed through public asset mappings; the configured Windows environment uses junctions. Active helpdesk assets are in `public/helpdesk`.

`npm ci` and `npm run build` build Vite resources when needed. Active helpdesk layouts load bundled CSS/JavaScript directly, so those files do not require a Vite build.

## Verification

```powershell
php vendor/bin/phpunit
php vendor/bin/phpunit tests/Feature/HelpdeskPresentationTest.php
node tests/Frontend/dashboard-tables.cjs
node tests/Frontend/report-editor.cjs
```

The second command is a focused alternative to the full suite. `phpunit.xml` forces SQLite `:memory:`. Helpdesk feature tests construct an isolated schema and do not migrate the configured MySQL database. DOM checks require `jsdom` through local Node resolution or `%LOCALAPPDATA%\PMMA-Support-Local\dom-tests\node_modules`. They do not replace a visual browser check.

Optional local MySQL integration check:

```powershell
php scripts/test-helpdesk-mysql.php
```

The runner copies table definitions into a disposable database, creates a restricted account, runs helpdesk tests, and cleans up. It needs administrator credentials from `%LOCALAPPDATA%\PMMA-Support-Local\credentials.json` or `HELPDESK_MYSQL_ADMIN_CREDENTIALS`. Use a development database server.

## Reports and operational boundaries

The report renderer imports `resources/reports/ar_blank.pdf` at native size using FPDI/TCPDF and bundled Cambria/Arial fonts. The source template SHA-256 is `f48a60f70b022f43a2b720a3864e4ae5dbaf18e882ebf3026733f4cc106e4eb6`. Current output masks the red sidebar label below the department and updates multipage numbering. Print at **Actual size / 100%**.

Completed tickets are grouped by completion/update day; equivalent descriptions appear once per day. Findings take precedence over concern details. Browser table search does not alter PDF selection. Edited introduction and signatories are request-scoped, with no persistent report archive.

Staff accounts (`users`), requester entries (`employees`), and technician records (`technicians`) are separate. The dashboard uses authentication but has no role hierarchy or technician-only isolation. Public staff self-registration and legacy API routes, including unauthenticated GET mutations, remain in the repository. See [access-control limitations](docs/TECHNICAL_SPECIFICATIONS.md#access-controls-and-limitations) before treating a deployment as a restricted staff system.

There is no priority/SLA engine, attachment workflow, immutable completion timestamp, ticket audit history, database-backed branding, or deployment history. Branding strings are in `config/helpdesk.php`.

## Project layout

```text
app/Http/Controllers/       Portal, dashboard, auth, report, and legacy handlers
app/Models/                 Existing-schema Eloquent models
app/Services/Reports/       PDF renderer and introduction sanitization
config/helpdesk.php         Portal branding
docs/                       System, technical, and process documentation
public/helpdesk/            Active styles, scripts, icons, and libraries
resources/views/helpdesk/   Portal and staff Blade templates
resources/reports/          Official PDF and generated font resources
routes/                     Web, helpdesk, auth, and legacy API definitions
scripts/                    MySQL and PDF verification utilities
tests/                      PHP and JavaScript checks
```

Framework/dependency licenses remain with their respective owners. See [design notices](HELPDESK_DESIGN_NOTICES.md) for presentation assets.
