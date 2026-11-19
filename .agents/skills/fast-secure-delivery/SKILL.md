---
name: fast-secure-delivery
description: "Use for fast, focused software changes with economical ChatGPT/Codex token usage, concise responses, minimal relevant verification, and verified production deployment through SSH."
---

# Fast, Secure Delivery

AGENT INSTRUCTIONS:
For application changes, follow the fast-secure-delivery skill:
keep work scoped and concise, conserve tokens, perform minimal
risk-appropriate verification, and deploy verified application changes
through the approved SSH workflow. Report deployment blockers honestly.

## Priorities

1. Deliver the requested change completely and correctly.
2. Keep work fast, focused, secure, and economical in token usage.
3. Deploy application changes to the configured production environment
   through SSH after the required checks pass.
4. Be concise and honest about results and limitations.

## Scope and token conservation

- Do only work needed for the current request.
- Avoid unrelated cleanup, refactoring, dependency changes, and tooling.
- Read only relevant files and bounded sections.
- Reuse information already available; do not repeatedly inspect it.
- Prefer precise searches over dumping large files or logs.
- Handle small tasks directly. Delegate only substantial independent work.
- Do not ask another agent to re-check work without a concrete reason.
- Make reasonable decisions using existing project conventions.
- Ask only when a decision materially changes scope, behavior,
  security, cost, or destructive consequences.
- Preserve unrelated uncommitted changes.
- Do not create planning documents or reports unless requested.

## Implementation

- Make surgical but complete changes.
- Reuse existing components, libraries, and conventions.
- Fix defects introduced by or tightly coupled to the change.
- Do not fix unrelated pre-existing issues.
- Do not install dependencies unless required and approved.
- Update directly related existing documentation when behavior changes.
- Do not add comments that merely repeat the code.

## Minimal verification

- Do not run broad or full test suites by default.
- Start with basic relevant security/access checks and syntax checks.
- For protected functionality, verify unauthorized access is refused.
- Enforce authorization on the server, not only by hiding UI elements.
- Check input validation and output escaping where affected.
- Never expose credentials, tokens, private keys, or personal data.
- Run additional focused regression tests only when required by
  higher-priority project instructions or justified by the change's risk.
- Use existing tooling. Do not add test frameworks for ordinary changes.
- Documentation/skill-only changes require no application tests.
- Do not claim a check passed unless it actually ran successfully.
- Clearly distinguish local checks from live production checks.

## Production deployment through SSH

After an application update passes the required checks, deploy the
related changes through the project's approved SSH workflow.

### Preconditions

- Confirm the intended production host, account, application path,
  runtime executable, and live document root.
- Use existing approved SSH configuration and credentials.
- Never guess a production target or silently deploy to another host.
- Respect environment approval requirements and deployment restrictions.
- If access or approval is missing, report the blocker.
- Never print secrets or upload local environment files.

### Connection security

- Keep strict SSH host-key verification enabled.
- Use the approved identity and known-hosts configuration.
- Prefer noninteractive authentication for automated commands.
- Never bypass host verification to make a deployment succeed.
- Do not register keys or alter authentication without authorization.
- Do not hang indefinitely waiting for a password prompt.

### Safe publication

1. Identify an explicit allowlist of related changed application files.
2. Exclude secrets, local state, logs, databases, unrelated changes,
   and development-only files.
3. Back up affected remote files outside the public document root.
4. Require the backup to succeed before overwriting files.
5. Stage uploads safely and verify their integrity.
6. Publish only the approved files, preferably using atomic replacement.
7. Apply required migrations only after reviewing their data impact.
8. Clear or rebuild only relevant application caches.
9. Preserve production configuration, uploads, accounts, and data.
10. Clean up deployment staging files after verification.

Do not perform destructive resets, worker takeovers, source-data syncs,
database restores, account changes, or document-root changes as an
incidental part of an ordinary deployment.

### Live verification

- Compare deployed file hashes or equivalent release identifiers.
- Check syntax/runtime compatibility where relevant.
- Confirm the application responds successfully.
- Verify relevant protected endpoints refuse unauthorized access.
- Test authenticated behavior only with approved existing access.
- Do not create production users or change data just to test.
- If live authenticated checks are unavailable, state that limitation.
- An upload completing is not sufficient proof of deployment success.

### Failure handling

- Stop on deployment or verification failure.
- Restore affected application files from the verified backup when safe.
- Handle database recovery separately; never blindly reverse migrations.
- Report the actual state, blocker, and recovery outcome.
- Never claim production is updated when verification failed.

## Communication

- Keep progress updates short and useful.
- After every task that changes files, include a short file-by-file summary
  in the final reply. List each file you edited, added, deleted, or renamed,
  with its path or a clickable link.
- For each file, explain what you did and what it changes in simple words
  a 10-year-old could understand. Use the user's language and explain any
  technical term you need to use.
- When relevant, also report the following.
  - Which checks passed.
  - Whether production deployment was verified.
  - Any blocker or required next action.
- Do not repeat logs or expose sensitive configuration.
- Never invent test results, deployment status, or guarantees.
- Mark the work complete only when the requested outcome is verified,
  or clearly explain a genuine blocker.

## Project-specific configuration

Discover these from approved project configuration; do not store
secrets in this skill:

- Production SSH host and port.
- Approved SSH account and identity reference.
- Verified known-hosts configuration.
- Application/release path and public document root.
- Runtime executable and version.
- Required build and migration commands.
- Health URL and protected endpoints for smoke checks.
- Backup location and rollback procedure.

## Documentation-only exception

Changes limited to skills, instructions, or documentation do not require
an application deployment unless those files are themselves a requested
production deliverable.

## Instruction precedence

Follow higher-priority platform and project requirements. This skill
does not bypass required tests, approvals, security controls, or
production-change restrictions.