# PMMA Helpdesk — Process Flows

Reviewed: **7 November 2026**. Diagrams describe active helpdesk behavior. Legacy handlers can follow different rules; see [technical limitations](TECHNICAL_SPECIFICATIONS.md#access-controls-and-limitations).

[README](../README.md) · [System documentation](SYSTEM_DOCUMENTATION.md) · [Technical specifications](TECHNICAL_SPECIFICATIONS.md)

Diagrams use Mermaid. View in a Markdown viewer with Mermaid support; accompanying prose/tables provide text explanations.

## 1. System context

```mermaid
flowchart LR
    Requester[Requester or cadet] -->|Submit / track| Portal[Public portal]
    Staff[Authenticated staff] -->|Review / assign / update| Dashboard[Staff workspace]
    Portal --> App[Laravel application]
    Dashboard --> App
    App <-->|Directory / tickets / feedback| DB[(MySQL)]
    App -->|Confirmation / status / signed link| Mail[Mail transport]
    Mail --> Requester
    App --> PDF[Accomplishment PDF]
    PDF --> Staff
```

Requesters, technician records, and staff credentials are separate. Assignment does not grant login access or limit ticket editing to that technician.

## 2. Request submission

```mermaid
flowchart TD
    A[Open form / choose service] --> B[Enter directory email]
    B --> C[POST requester lookup]
    C --> D{Active email exists?}
    D -->|No| E[Show error / contact ITSU]
    E --> B
    D -->|Yes| F[Show valid indication only]
    F --> G[Enter concern / submit UUID token]
    G --> H[Validate active email again / service / concern / token]
    H --> I{Valid?}
    I -->|No| J[Return field errors]
    J --> G
    I -->|Yes| K{Token already stored in session?}
    K -->|Yes| L[Redirect to existing receipt]
    K -->|No| M[Resolve employee / create Pending support / record IP]
    M --> N[Store token mapping and receipt flag]
    N --> O[Attempt confirmation email]
    O --> P{Mail failed?}
    P -->|Yes| Q[Log error / retain saved ticket]
    P -->|No| R{Staff creation route?}
    Q --> R
    R -->|Yes| S[Open staff ticket detail]
    R -->|No| T[Show session-protected receipt]
```

| Stage | Result |
| --- | --- |
| Lookup | Confirms active email without returning directory profile fields |
| Validation | Rejects invalid input; public assignment of a technician is prohibited |
| Duplicate check | Successful token already in this session redirects instead of inserting |
| Save | Creates `Pending` with employee/service references and IP; staff may assign technician |
| Notification | Synchronous mail attempted after saving; delivery failure does not undo ticket |

The token check is session-scoped, not a database uniqueness guarantee. Repeated tokens follow the receipt path even if originally submitted through the staff route.

## 3. Tracking and queue

```mermaid
flowchart TD
    A[Keep numeric ticket reference] --> B[Open Track Ticket]
    B --> C[Submit positive integer reference]
    C --> D{Ticket exists?}
    D -->|No| E[Show no matching ticket]
    D -->|Yes| F[Show status / service / department / technician / dates / concern / findings]
    G[Open queue] --> H[Select tickets created in last seven days]
    H --> I[Sort by latest update / limit to 90]
    I --> J[Group by status and display]
```

Receipt access requires the originating session; public tracking does not verify ownership. The home page separately loads the latest ten tickets without the queue's seven-day creation filter.

## 4. Staff ticket handling

```mermaid
flowchart TD
    A[Open workspace] --> B{Authenticated?}
    B -->|No| C[Sign in]
    C --> B
    B -->|Yes| D[Search / filter / open ticket]
    D --> E[Set status / technician / findings / optional accomplishment date]
    E --> F{Validation succeeds?}
    F -->|No| E
    F -->|Yes| G[Save fields and updated_at]
    G --> H{Status is Pending?}
    H -->|Yes| K[Return to detail with success]
    H -->|No| I{Employee relation available?}
    I -->|No| K
    I -->|Yes| J[Attempt status email with signed survey link]
    J --> L[Log failure if any / keep saved update]
    L --> K
```

The optional accomplishment date is written to `updated_at`; otherwise current time is used. A non-pending save attempts email even without a status change.

### Status transitions

```mermaid
stateDiagram-v2
    [*] --> Pending: Create
    Pending --> Completed: Staff save
    Pending --> Cancelled: Staff save
    Completed --> Pending: Staff save
    Cancelled --> Pending: Staff save
    Completed --> Cancelled: Staff save
    Cancelled --> Completed: Staff save
```

Saving the same status is also allowed. Completed/cancelled are not enforced terminal states. Findings and technician are optional even at completion.

## 5. Signed feedback

```mermaid
sequenceDiagram
    participant R as Requester
    participant W as Web middleware
    participant S as Survey controller
    participant D as Database
    R->>W: GET signed survey URL with taskId
    W->>W: Validate signature
    W->>S: Valid request
    S->>D: Check ticket and existing feedback
    alt Feedback already exists
        S-->>R: Already taken message
    else No feedback
        S-->>R: Form with signed POST URL
        R->>W: POST rating and optional feedback
        W->>W: Validate CSRF and signature
        W->>S: Valid submission
        S->>S: Validate rating / use signed query support_id
        S->>D: Begin transaction and lock support row
        S->>D: Check result again
        alt Still no result
            S->>D: Insert overall satisfaction response
        else Result now exists
            S->>S: Skip duplicate insert
        end
        S->>D: Commit
        S-->>R: Redirect home with message
    end
```

Invalid signatures stop before controller execution. A posted ticket ID cannot replace the signed query. The transaction/parent lock guards the active path against repeat responses; the legacy schema has no unique ticket-response constraint. Save does not require a completed ticket. Links do not expire by default or prove individual recipient identity.

## 6. Accomplishment report

```mermaid
flowchart TD
    A[Signed-in staff opens report] --> B[Choose dates / optional technician]
    B --> C[Validate inputs]
    C --> D[Query Completed tickets by effective update date]
    D --> E[Sort / group by day]
    E --> F[Choose findings or fallback description]
    F --> G[Deduplicate normalized descriptions per day]
    G --> H[Display report entries]
    H --> I[Optional title / introduction and signatories]
    I --> J[Preview or download]
    J --> K[Revalidate / requery current records]
    K --> L[Sanitize introduction / load template and fonts]
    L --> M[Paginate accomplishments and signatures]
    M --> N[Render first-page introduction and continuation tables]
    N --> O[Apply current sidebar and page-number overlays]
    O --> P[Return PDF inline or attachment]
```

| Stage | Rule |
| --- | --- |
| Eligibility | `Completed` within inclusive `COALESCE(updated_at, created_at)` dates; optional technician match |
| Description | Findings → concern → service name → generic completion text |
| Grouping | Effective update day; equivalent text once per day |
| Editable text | Request-scoped, sanitized; fixed renderer fonts |
| Output | Original page size, current overlays, no persistent report archive |

Browser table search/sort does not alter PDF filters. Export queries current records again, so changes after viewing may affect output. Signatory fields label the document; they do not record an approval transaction.

## 7. Catalog maintenance

```mermaid
flowchart TD
    A[Authenticated staff opens Settings] --> B[Choose catalog]
    B --> C[Create or edit]
    C --> D[Validate type-specific fields]
    D --> E{Valid?}
    E -->|No| C
    E -->|Yes| F{Employee?}
    F -->|Yes| G[Set deleted_at from active flag / set update time]
    F -->|No| H[Save catalog fields without automatic timestamps]
    G --> H
    H --> I[Return to catalog tab]
```

Deactivation prevents new active-directory submissions while preserving historical ticket associations. Related catalog labels are resolved from current records, not snapshotted per ticket.

## 8. Cadet directory registration

```mermaid
flowchart TD
    A[Open student form] --> B[Enter name / email / student department]
    B --> C[Uppercase name / lowercase email]
    C --> D[Validate unique email and STUDENTS department]
    D --> E{Valid?}
    E -->|No| B
    E -->|Yes| F[Create employee directory entry]
    F --> G[Return with success]
    G --> H[Use email for request submission]
```

The `STUDENTS` department must exist. This registration does not create a staff user or password.
