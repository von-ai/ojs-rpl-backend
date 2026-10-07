# SIRIDA backend and API contract review

**Current baseline: 1.0.0 (196 planned operations).** Registration no longer accepts
role selection or creates reviewer applications. Reviewers are invited by editors
for a specific submission/review round; directory membership alone grants no manuscript
access. The historical reviewer-application recommendations below are superseded.
See [architecture](architecture.md),
[use cases](use-cases.md) and [changelog](changelog.md). The dated draft reviews
below are historical; final design decisions do not imply stakeholder approval.

**Follow-up, 7 October 2026:** draft.3 now defines 201 planned operations and closes
the contract gaps below. See [current contract decisions and gap closure](contract-decisions.md)
for the updated behavior, 37 contract regressions and remaining runtime/deployment
gates. The findings, traceability and test counts below are the historical draft.2
review; they do not describe gaps still missing from draft.3.

Reviewed on 6 October 2026 against `main` commit
[`87a13f3`](https://github.com/von-ai/ojs-rpl-backend/tree/87a13f3).

## Verdict and scope

**The backend is not yet an implementation of the proposal.** It is a Laravel
scaffold plus a substantial contract-first OpenAPI draft. At the reviewed commit,
`php artisan route:list --json` exposes `/`, `/up` and framework storage routes;
none of the 102 operations in the original OpenAPI document is registered.
There are no domain controllers, journal/workflow models, authorization policies,
or domain migrations. The two existing tests check a boolean and the welcome page.
Neither their success nor OpenAPI validity proves that authentication, peer-review
privacy or publication works.

The draft already models most main flows: separate submissions and public articles,
explicit workflow commands, clean-file scanning, immutable sent decisions, copyedit
approval, galley checksums, publication outbox and Crossref retries. However, the
contract had privacy, privilege, interoperability and client-workflow gaps.
This PR fixes those contract defects and adds repeatable validation; it does not
claim to deliver the entire editorial application. `1.0.0-draft.2` has 114 planned
operations, all still awaiting runtime implementation.

## Sources and interpretation

- [SIRIDA proposal/SRS](https://docs.google.com/document/d/1bWldGLdB9Ek2bgmJtyupGi5yA7C1E409w6Gs65p__OM/),
  **Proposal** tab, especially scope §1.3, detailed use cases §3.2.1 and NF-01–10
  §3.2.2. Use-case titles and detailed headings take precedence in this review when
  the SRS's internal cross-reference numbers disagree.
- [Partner meeting notes, 16 September 2026](https://docs.google.com/document/d/10xW2fS-EfoSJnsHYmCIoMZm_T48a1GOLHD2JnpNoTBo/):
  Laravel/SQL, Blade/Livewire preference, Kominfo-local hosting and no Node server.
  These are deployment constraints to resolve with the team; a REST contract alone
  does not require a Node production runtime.
- [OpenAPI 3.1.1](https://spec.openapis.org/oas/v3.1.1.html#schema-object):
  response/request annotations and JSON Schema dialect.
- [HTTP conditional requests](https://www.rfc-editor.org/rfc/rfc9110.html#section-13.1.1)
  and [428 Precondition Required](https://www.rfc-editor.org/rfc/rfc6585.html#section-3).
- [OWASP object-level authorization](https://api-security.owasp.org/editions/2023/en/0xa1-broken-object-level-authorization/):
  check each referenced object, not just an actor's role.
- [OAI-PMH 2.0 HTTP transport](https://www.openarchives.org/OAI/openarchivesprotocol.html#HTTPRequests):
  repositories must accept GET and form-encoded POST.

The prior ERD guide is supporting design work, not a substitute for approved
requirements. No credentials, entire source documents or personal project-member
details are copied into this report. Product choices not settled by the sources
are listed below instead of silently treated as approved requirements.

## Findings corrected in this PR

Severity here describes the consequence **if implemented from the original
contract**, not a verified exploit in an already running backend.

| Priority | Original defect and impact | Correction |
| --- | --- | --- |
| P1 | Journal user creation, role replacement and imports accepted `system_administrator` through the shared `Role` enum. A journal manager could be implemented with platform privilege escalation. | Separate `JournalRole` and `SystemRole`; represent global roles explicitly in login/current-user responses. Imports must also reject platform roles in file rows. |
| P1 | `getSubmission` admitted reviewers while its response required `author_id` and contributors. Removing those fields would violate the schema; keeping them breaks double-blind review. | Exclude reviewer authorization from that representation. Provide safe invitation reads and a separate accepted-reviewer workspace. Require matching object/assignment access on file metadata and downloads. |
| P1 | Login/refresh response tokens were marked `writeOnly`, so direction-aware generators could omit the very tokens clients need. Cache behavior was unspecified. | Response tokens are `readOnly`; token responses use `Cache-Control: no-store`. Clarify active/verified accounts, five-failure lockout, security email, JWT lifetime, refresh rotation and revocation. |
| P2 | Authors had no typed read operation for sent decisions and reviewer feedback, even though UC-07 requires them to prepare a revision. Reusing internal review/decision schemas risks leaking confidential comments. | Add `listAuthorDecisions` with closed author-only schemas and anonymous released feedback. Internal notes and reviewer/assignment identifiers are invalid in that projection. |
| P2 | Public articles reused the private `Contributor` schema, including required contact email and optional internal ID. | Introduce `PublicContributor` without contact email or internal IDs. |
| P2 | Reviewers could accept/decline an assignment but could not discover their invitations, title, abstract and deadline beforehand. Workspace always required a manuscript ID. | Add assignment list/detail previews; manuscript workspace and download remain gated by acceptance. |
| P2 | `ReviewDraftRequest` required a complete evaluation; draft reads could not represent partially entered scores. | Allow partial draft scores/comments, while keeping full validation on final submission and submitted review responses. |
| P2 | Many commands required `If-Match` with no readable validator or defined aggregate; stale preconditions were collapsed into 409. | Add missing detail GETs and ETags, map each conditional command using `x-concurrency-resource`, document 412/428, and protect initial draft creation with `If-None-Match: *`. |
| P2 | Review rounds did not identify the exact manuscript being evaluated. | Require a clean immutable `manuscript_file_id` and document its anonymized derivative for reviewers. |
| P2 | User imports required a pre-existing file, but upload authorization excluded journal managers and implied a pre-existing import ID. | Permit journal managers to upload journal-scoped import inputs before creating the asynchronous import. Clarify purpose/MIME/ownership validation. |
| P2 | Issue composition required article IDs, but only published articles were discoverable; the contract did not describe when an unpublished article is created. | Final galley approval creates/updates a stable unpublished article. Add an authorized ready-article list; empty draft composition is allowed, empty publication is forbidden. |
| P2 | OAI-PMH exposed only GET. | Add form-encoded POST, preserve XML protocol errors and describe verb-specific validation. |
| P2 | Journal user `status` and deactivation could be interpreted as global account changes. Role replacement returned a global profile with no membership information. | Return a journal-user projection with membership and roles, constrain journal status changes and protect the last active Editor in Chief. |
| P2 | No contract CI existed; fresh-checkout `composer test` failed because `APP_KEY` was absent. | Add schema lint, positive/negative contract regressions and read-only CI jobs. Set a clearly test-only encryption key in `phpunit.xml`; production key generation is unaffected. |
| P2 | `composer.json` advertised PHP 8.3, but the committed Symfony 8 dependencies require PHP ≥8.4.1. | Align the declared minimum and setup documentation with the existing locked dependencies; no package versions are upgraded. Confirm that hosting can provide this runtime. |

Other consistency corrections: authenticated operations declare 401/403;
idempotent operations declare 409; conditional operations declare 412/428;
shared error responses carry correlation/cache headers and 401 carries a bearer
challenge. Import routes move from `/users/imports` to `/user-imports` to remove
overlap with `/users/{user_id}/...`. These are breaking draft-contract corrections;
frontend mocks and generated clients must regenerate from draft.2.

## Historical draft.2 traceability and remaining implementation work

**Every row below is unimplemented at runtime.** “Covered” describes contract
coverage only. The remaining column also identifies contract/product gaps that
this corrective PR does not claim to close.

| SRS use case | Contract coverage after correction | Remaining work or decision |
| --- | --- | --- |
| UC-01 Registration | Register, verify, resend; default Reader/Author; no registration role selection | Implement verification delivery/expiry and journal membership provisioning. Reviewer recruitment uses editor invitations scoped to a submission/review round; the former self-service application recommendation is superseded. |
| UC-02 Login | Email/username login, logout, refresh, reset, current context | Implement hashing, account-keyed lockout across aliases, security email, JWT validation/revocation and role-context selection. Decide absolute session/refresh lifetime; SRS fixes access-token maximum at 24h. |
| UC-03 Read/download | Public metadata, safe contributors, citations, galley downloads | Enforce published-only visibility and galley ownership, deduplicate download statistics, handle missing objects. Define reading-statistics behavior. |
| UC-04 Search | Query, year/volume/issue/section/author/keyword filters and pagination | Implement indexing and failure handling; related-article/alternative-query response is still unspecified. |
| UC-05 New submission | Draft metadata/contributors, upload/scan, final submit | Implement same-journal section selection, draft persistence and completeness checks at submit. Section/guideline discovery and optional author similarity check still need contracts. |
| UC-06 Track status | Submission list/detail, safe timeline, sent author decisions | Add workflow policies, safe notifications and version-history discovery. |
| UC-07 Revision | Draft/update/read/submit revision with response letter and decision feedback | Add revision-list/version-history reads. Agree grace period and automatic withdrawal policy before implementing scheduler. |
| UC-08 Respond to review | Discover/read invitations, accept/decline, request extension, gated workspace | Add editor approval/decline of extension requests; implement invitation expiry/no-response escalation. |
| UC-09 Review evaluation | Partial drafts, final submission, separate confidential/author fields | Implement immutable round/rubric versions, deadline enforcement and escalation; choose rubric configuration policy. |
| UC-10 Desk review | Assess, pass, request completion, reject, assign editor, ethics escalation | Add assessment history/discovery and ethics investigation lifecycle; define resubmission of incomplete records precisely. |
| UC-11 Assign reviewers | Directory, pinned round, invitations, reminders, cancel | Implement eligibility, conflicts, deadlines and batch transaction behavior; add round/history reads and cancellation boundaries. |
| UC-12 Editorial decision | Summary, draft/read/update/send, audited override, author-safe reads | Enforce review threshold/authorized override, sent immutability, reason logging and revision deadline rules. |
| UC-13 Copyediting | Start/read/update, request author approval, approve or request changes | Implement assignment and approved-file binding; add copyedit history/list and mediation/escalation workflow. |
| UC-14 Layout | Version registration/read, final approval, ready-article creation | Add explicit Layout Editor assignment and galley history/list; validate predecessor proof/final checksums. |
| UC-15 Publish issue | Ready articles, issue composition, schedule/publish/cancel, Crossref retry | Implement atomic publication/outbox and retry workers. Define current-issue selection, DOI ownership and uniqueness, schedule failure recovery. |
| UC-16 Users/roles | Scoped membership create/read/update/deactivate/roles/import | Define and implement account deletion vs membership removal and active-submission retention. Add manager-initiated reset and reviewer directory administration; manuscript access still requires an accepted assignment. All last-editor guards must run transactionally. |
| UC-17 System settings | Read/update, integration test, rollback | Add settings version-list/read, tested-candidate binding before save, system-level audit reads. Validate secrets/provider configuration; never trust client-supplied arbitrary integration URLs. |
| UC-18 Proofreading | Assignment via proof command, notes, approve/return and exact file checksum | Add notes/history reads, resolution and escalation back to Copyeditor; invalidate obsolete approval on replacement. |

Additional scope in §1.3: journal pages, announcements and email-template
administration do not yet have CRUD contracts. Keep these in the backlog rather
than treating UC-01–18 tag coverage as proof of complete scope coverage.

## Non-functional requirements and unresolved decisions

| SRS requirement | Review result / acceptance gate |
| --- | --- |
| NF-01 Performance | No measured evidence. General-operation target is <3s at 500 concurrent users. The transfer target is internally inconsistent: 20 MB over 1 Mbps needs about 160s of payload time alone, so <10s cannot hold at that minimum bandwidth. Clarify bandwidth/measurement boundary in the SRS. |
| NF-02 Security | Contract boundaries improved; no auth/policy implementation exists. Test object access across journals, mixed roles, invitation states, original/anonymized files, token replay and revocation. Hash passwords with bcrypt/Argon2; TLS provides transport encryption—the SRS wording must not imply passwords are reversibly encrypted. |
| NF-03 Scalability | No load-test evidence for 1,000 concurrent users/degradation target. Test actual hosting/queue/database configuration. |
| NF-04 Usability | Backend-only review cannot establish usability, <10-minute submission or WCAG 2.1 AA. Requires representative user/browser testing. |
| NF-05 Availability | No uptime, recovery or failover evidence. Define monitoring, dependency readiness and alerting before claiming 99.5% uptime. |
| NF-06 Compatibility | Requires frontend/browser/responsive testing, separate from contract validity. |
| NF-07 Maintainability | This PR adds contract validation and CI. Two scaffold tests are not domain coverage; ≥70% core-code coverage remains unverified until core code exists. |
| NF-08 Backups | Implement and test daily database/file backups, 30-day retention, RPO≤24h, RTO≤4h and quarterly restores on actual infrastructure. |
| NF-09 Interoperability | GET/POST OAI transport described; XML/XSD harvesting, Crossref sandbox deposits and ORCID linking still require integration tests. |
| NF-10 Audit | Journal audit schema exists. Implement append-only events transactionally with actor/time/action/reason; sanitize secrets and private reviewer data. Add system/automated actor representation and global audit access. |

Team decisions before implementation:

1. Resolve SRS Next.js/S3/AWS/WebSocket architecture versus partner Blade/Livewire,
   Kominfo/shared-hosting constraints. Confirm PostgreSQL availability, PHP version,
   queue workers/scheduler, object storage/scanner and whether long-lived SSE is
   supported. Node in CI/build tooling does not imply Node on the production server.
2. Confirm the private/public identifier strategy. The contract specifies UUID/ULID,
   but the current Laravel user migration uses bigint. A public-ID mapping or
   deliberate migration is needed before implementing API resources.
3. Decide multi-journal author provisioning, global-admin provisioning and who may
   edit shared identity fields. Journal-manager scope must not silently expand to
   other journals/global accounts.
4. Fix review/revision deadlines, grace periods, minimum-review exceptions, rubric
   versions and assignment ownership. Do not infer unspecified grace durations.
5. Define authenticated browser transport (JWT storage, refresh/session policy,
   cross-origin CORS/CSRF decisions, SSE authentication without query-string tokens).
   Confirm ORCID provider-supported OAuth parameters and callback error/cancel paths.
6. Complete upload resumption: the old contract mentions multipart but has no
   part-URL/list/abort protocol. Either specify this or scope the first implementation
   explicitly to a retryable single upload; do not claim resumable multipart works.
7. Keep payment/APC out of version 1 per SRS scope. Existing access-model enum values
   are policy metadata, not an implemented entitlement or payment system.

## Historical draft.2 validation and limits

Run from a clean checkout:

```sh
composer install --no-interaction --prefer-dist
composer validate --strict
composer test
vendor/bin/pint --test
npm ci --ignore-scripts
npm run check:openapi
```

Local result: OpenAPI lint passed without warnings; 17 contract tests and the two
Laravel scaffold tests passed. Running the same contract suite against the original
`87a13f3` specification produced 14 failures and 3 passes. The original fresh-checkout
Laravel suite failed for missing APP_KEY; the test-only key fixes that setup failure.
Composer's locked dependency audit reported no advisories on the review date.

The contract tests compile every schema, resolve references, validate examples and
exercise positive/negative payloads for privilege separation, privacy, incomplete
drafts, upload limits and OAI transport. They also check that conditional commands
have readable validators and documented failures. They **do not** execute planned
endpoints or prove their eventual authorization/transaction behavior.

Release gates still include endpoint/contract integration tests, PostgreSQL
constraints and migrations, competing-write/idempotency tests, fake clock deadline
tests, file scanning/access tests, external sandbox tests, dependency/security
reviews, load tests and backup restore drills. CI in this PR validates the current
scaffold and contract, not these future release gates.
