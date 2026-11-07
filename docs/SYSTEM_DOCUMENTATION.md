# PMMA Helpdesk — System Documentation

Reviewed: **7 November 2026**. Basis: repository source, bundled schema definitions, and existing tests.

[README](../README.md) · [Technical specifications](TECHNICAL_SPECIFICATIONS.md) · [Process flows](PROCESS_FLOW.md)

## 1. Purpose and scope

PMMA Helpdesk centralizes campus IT concerns handled by ITSU. Each ticket links a requester to a service and records concern details, assignment, status, findings, and request/update dates. Staff review activity, collect feedback, and produce accomplishment reports.

This document describes implemented behavior. Staffing procedures, retention periods, service targets, approval policies, and hosted configuration are not established by the code.

## 2. Users and access

| User or record | Responsibilities and access |
| --- | --- |
| Public visitor | Browse services, view public queue, and track numeric references |
| Directory requester | Submit with an active `employees.email`; no requester password required |
| Cadet | Register a directory entry under `STUDENTS`, then submit requests |
| Authenticated staff | Manage dashboard tickets, catalogs, feedback, reports, and own profile |
| Technician record | Assignment target; separate from a staff login account |
| Maintainer | Provision runtime/schema, configure mail, maintain backups, inspect logs, deploy changes |

The code uses a shared authenticated workspace rather than separate administrator and technician permission tiers. Guest `/register` currently creates and signs in a `users` account without an approval gate. Registration policy must be resolved before describing a deployment as restricted to approved staff.

## 3. Modules

| Module | Function | Main entry |
| --- | --- | --- |
| Public portal | Service cards and latest ten tickets | `/` |
| Catalog | Service search and detail | `/services` |
| Intake | Active-email validation and request submission | `/tickets/create` |
| Tracking | Status, department, technician, dates, concern, findings | `/track` |
| Queue | Up to 90 tickets created in the last seven days, grouped by status | `/queue` |
| Staff workspace | Search/filters, ticket counts/charts, detail/edit | `/dashboard` |
| Settings | Employee, department, technician, service maintenance | `/dashboard/settings` |
| Feedback | Signed survey form and single response per ticket | `/take-survey` |
| Survey results | Rating/feedback records | `/dashboard/surveys` |
| Reports | Completed-ticket grouping and official PDF output | `/report` |
| Accounts | Login, profile/password changes, password recovery | `/login`, `/profile` |

`/feedback` redirects to Microsoft Forms and is separate from the built-in ticket survey.

## 4. Requester procedure

1. Choose a service or open **Submit a Ticket**.
2. Enter an active directory email. Successful lookup shows a valid indication without returning the employee profile to the form.
3. Select a service and enter concern details, up to 10,000 characters. Public requesters cannot assign technicians.
4. Submit. The server validates the email again and creates a `Pending` ticket with the submitting IP.
5. Keep the numeric reference on the receipt. Receipt access requires the originating browser session.
6. Open **Track Ticket** and enter the reference to review progress. Tracking does not require login or the receipt session.

Unknown/inactive emails cannot submit through this intake. Contact ITSU for directory correction. Cadet registration at `/student` requires the `STUDENTS` department to exist, rejects another department, normalizes name to uppercase and email to lowercase, and creates a directory entry rather than a login account.

The request form generates a UUID submission token. Reusing a successfully recorded token in the same session redirects to the existing receipt. This is a session-level duplicate safeguard, not database idempotency across sessions or concurrent requests.

## 5. Staff ticket procedure

1. Sign in at `/login` using a `users` account. The normal destination is `/dashboard`.
2. Filter/search by status, service, department, or technician. Dashboard counts/charts summarize all tickets, not only the displayed page.
3. Open a ticket. Historical tickets retain access to deactivated requester records.
4. Assign/clear a technician, enter findings, select status, and optionally enter an accomplishment date.
5. Save. `updated_at` becomes that date or the current time.
6. For `Completed` or `Cancelled`, the application attempts a status email with a signed survey link. Saving again in either status attempts another email even if the status did not change.

Staff can create a request for an active directory requester and optionally assign a technician during creation.

| Status | Operational meaning | Report inclusion |
| --- | --- | --- |
| `Pending` | Awaiting or undergoing action | Excluded |
| `Completed` | Support action completed | Included when date/technician filters match |
| `Cancelled` | Request cancelled | Excluded |

Any of these statuses may be selected on each save. The code permits reopening or switching between completed/cancelled states. It does not require findings or a technician before completion.

## 6. Catalog maintenance

Open **Settings**, select a catalog, then create/edit and save.

| Catalog | Required information and behavior |
| --- | --- |
| Employees | Name, email, department, active flag; uniqueness checked in active form validation |
| Departments | Name; no activation control |
| Technicians | Name and unique email; no automatic user-account creation |
| Services | Name, description (`content`), icon; stored in `tasks` |

Employee deactivation sets `deleted_at`; reactivation clears it. It blocks new active-directory submissions while retaining historical associations. Active settings provide create/edit and employee activation/deactivation, not general hard deletion for all catalogs.

## 7. Feedback procedure

1. Open the signed survey link from the status email.
2. If a result already exists for the ticket, the form reports that feedback was already taken.
3. Select an available satisfaction rating and optionally enter feedback up to 255 characters.
4. Submit using the signed form action. The server takes the ticket ID from the signed query, not a posted replacement.
5. A transaction locks the ticket, checks again for an existing response, and inserts an overall satisfaction result with `rating_measure_id=0` if none exists.

Staff review results through **Surveys**. Historical rating-measure data remains readable. Signed links identify a ticket, do not prove the recipient's identity, and are generated without an expiry parameter. The survey save handler does not require the ticket to be `Completed`.

## 8. Accomplishment report procedure

1. Open **Accomplishment Report** while signed in.
2. Set an inclusive date range and optional technician. By default the period starts on day 1 for dates 1–15, or day 16 afterward, and ends today.
3. Apply filters. Only `Completed` tickets are selected using `updated_at`, with `created_at` as a null fallback.
4. Review daily groups. Description preference is findings, concern details, service name, then generic completion text. Equivalent descriptions ignoring case/repeated whitespace appear once per day.
5. Edit the title/introduction if needed. Formatting is sanitized; renderer fonts stay fixed. Clearing the editor restores the default introduction.
6. Enter prepared-by/noted-by names and positions for this export.
7. Preview/download the PDF and print at **Actual size / 100%**.

Table search/sort affects the browser table only, not the PDF filters. Signatories and introduction are request-scoped, with no report settings/history table. Export queries current records again. Later ticket/date/directory edits can change a regenerated document; reports are not immutable snapshots.

The original PDF is imported unscaled. Current output masks the red sidebar label below the department and adjusts multipage numbering. The source template remains unchanged. Introduction appears on the first page; continuation tables start below the letterhead. Empty results do not generate invented accomplishments.

## 9. Operations and maintenance

Use the [README](../README.md#runtime-and-local-setup) for local startup. A maintainer should use the following procedure for hosted operation:

1. Provision the runtime and approved schema. Historical migrations do not fully reconstruct the legacy database; do not use `migrate:fresh` as a repair step.
2. Serve `public/`, configure application URL/HTTPS and production debug settings, and allow writes to required runtime directories such as `storage` and `bootstrap/cache`.
3. Preserve an existing production `APP_KEY`. Back up deployment configuration outside source control.
4. Configure/test the mail transport. Active ticket notifications use synchronous sends; failures are logged after records are saved, without automatic retry tracking.
5. Back up the database and deployment resources/configuration through the managed institutional process. Test restoration in an isolated environment. No backup schedule/retention policy is defined by this repository.
6. Monitor `storage/logs`, disk space, database connectivity, and mail failures. Keep credentials and requester data out of public troubleshooting reports.
7. Run relevant existing checks after changes; visually verify mobile tables and report printing when those areas change.

| Symptom | Check/action |
| --- | --- |
| Email rejected | Verify email and active directory entry; cadets need the `STUDENTS` department |
| Saved ticket has no email | Inspect transport/logs; do not create another ticket merely to resend mail |
| Receipt 404 | Use original session or track by reference |
| Survey 403 | Use complete unchanged signed URL; confirm URL scheme/host and preserved key |
| Report missing ticket | Check `Completed`, effective update date, technician filter |
| PDF appearance differs | Check template hash, fonts, renderer changes, and print scale |
| Launcher fails | Check fixed PHP/MySQL paths and external `my.ini`; it is not an installer |

## 10. Known boundaries

There is no priority/SLA engine, attachment workflow, approval workflow, technician-specific isolation, ticket audit log, immutable completion date, report archive, database-backed branding, or deployment history in the active adaptation.

Public tracking exposes concern details/findings. Email validation checks directory membership rather than email ownership. Legacy APIs expose additional reads/mutations outside the new staff authentication group. See [technical access-control inventory](TECHNICAL_SPECIFICATIONS.md#access-controls-and-limitations) before deployment or extension.
