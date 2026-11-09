# Reusing the design

## Visual system

| Element | Value / location |
| --- | --- |
| Institutional blue | #0032a0 |
| Deep navy | #10264b |
| Accent orange | #ff8c12 |
| Orange hover | #ffad42 |
| Main typeface | Inter, Segoe UI, Arial, sans-serif |
| Body / workspace | White and pale blue surfaces |
| Cards | Thin borders, 10–12px rounded corners, restrained shadows |
| Primary actions | Orange background, navy text |
| Mobile breakpoint | 768px |

Inter uses the existing font stack; it falls back to Segoe UI/Arial when Inter is unavailable. No new external font dependency was added.

## Reusable pieces

- Public layout: `resources/views/layouts/portal.blade.php`.
- Staff sidebar, account header, dialogs: `resources/views/layouts/dashboard.blade.php`.
- Login layout: `resources/views/layouts/auth.blade.php` and `resources/views/auth/login.blade.php`.
- Dashboard metric cards and analytics: `resources/views/dashboard/index.blade.php` and `analytics.blade.php`.
- Generic editor and mobile drawer behavior: `public/assets/js/dashboard.js`.
- Mobile tables: `public/assets/js/ticket-tables.js` and `.ticket-responsive-table` styles. Only one details row stays expanded per table. Current column priorities and action handlers are tailored to the included ticket module; update the indices and endpoints when adapting another schema.
- Icon component: `resources/views/components/action-icon.blade.php`; Bootstrap Icons are already bundled.
- Shared typography, buttons, forms and cards: `public/assets/css/pmma.css`.
- Dashboard surfaces, sidebar and responsive rules: `public/assets/css/dashboard-theme.css`.

## New module workflow

1. Keep the shared layouts and CSS. Start a new page with `@extends('layouts.dashboard')`, a title section and a content section.
2. Replace sidebar navigation in the dashboard layout and public navigation in the portal layout.
3. Define your app's entities and migrations, then route/controller actions. Preserve authentication, authorization, CSRF validation and server-side input validation.
4. Replace sample ticket views and their controllers with your own module. Remove obsolete routes, policies, analytics and migrations together; do not delete the models while leaving controllers that still reference them.
5. Replace sample department/service seed data before creating a fresh database. Never rewrite already-run production migrations; add new migrations for deployed systems.
6. Update JSON field schemas in dashboard.js if using the generic editor. Adapt table heading/index mappings in ticket-tables.js to your new table schema.
7. Preserve accessible labels, keyboard behavior and mobile touch targets.
8. Test login, permissions, forms, empty states and tables at 320px, 390px, 768px and desktop widths.

## Included sample behavior

Ticket submission, receipt and clipboard support; tracking and newest-first timeline; sequential references; staff actions; optional Action Taken for Closed tickets; confirmed staff ticket deletion; departments/services; technician profiles; activity logging; analytics; mobile sidebar and accordion details.

These features illustrate the layout in a working application. Adapt the logic and public text to the next app rather than doing a blind global replacement of class names or CSS filenames.

## Package contents

Included: current source files (including local edits), Composer lockfile, sample config/migrations, tests, static assets and third-party notices.

Excluded: .git, .env, production backups, database rows/dumps, staff accounts, passwords, uploaded photos, storage runtime data, compiled views/config, vendor, node_modules, and machine-specific VS Code settings.