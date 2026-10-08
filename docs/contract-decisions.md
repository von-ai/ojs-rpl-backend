# SIRIDA contract decisions — 1.0.0

Updated 7 October 2026. This document accompanies `docs/openapi.yaml` version
`1.0.0`. It completes the contract-level gaps identified in the
[SRS review](review-srs.md), with **196 planned operations**. It does not implement
the application. All operations explicitly carry `x-implementation-status: planned`.

The final stack, business decisions and source deviations are recorded in
[architecture](architecture.md). See [use cases](use-cases.md),
[integration guide](integration-guide.md) and [migration notes](changelog.md).
Stakeholder approval remains subject to PR review; all operations remain planned.

The SRS detailed UC-01–18 and its scope remain the requirements source. Where it
does not settle an API detail, the choices below are explicit engineering decisions
for this final implementation baseline. Configurable values must be selected during deployment, not silently
invented by a background worker. No payment, APC processing, native app, internal
plagiarism engine or AI layout feature is added.

## Contract gap closure

| Area / SRS | Version 1.0.0 contract |
| --- | --- |
| Registration, UC-01 | Account registration without role selection; verification enables default Reader/Author access, with `joinJournalAsAuthor` for additional journal enrollment. Reviewers are recruited through editor invitations scoped to a submission and review round. |
| Login, UC-02 | `getActiveContext`/`setActiveContext`, self-profile updates, bounded access/session policies, explicit bearer/SSE transport, ORCID connection read and denial callback. |
| Reading/search, UC-03/04 | Public-only issue schemas, explicit current issue, deduplicated view events, safe search suggestions and Dublin Core export. |
| Submission, UC-05/06 | Partial drafts; public sections/guidelines/upload capabilities; completeness checks at submit; authorized file/version history; optional external similarity check create/list/read. |
| Revisions, UC-07 | Revision history, sent-decision feedback, grace-policy fields, immutable revision binding, overdue processing rules. |
| Review invitations, UC-08/11 | Round and assignment histories; extension list/read/editor decision; invitation-only cancellation; same-journal eligibility and conflicts. |
| Evaluation, UC-09 | Versioned fixed rubric and workspace snapshot; partial drafts stay distinct from final required evaluation. |
| Desk review, UC-10 | Assessment history, incomplete-submission completion, ethics case discovery/read/start/resolve. |
| Editorial decisions, UC-12 | Internal history vs author-safe history, due-date requirements for revision decisions, explicit state guards and audited correction boundaries. |
| Copyediting, UC-13 | History, EIC assignment, author approval of the entire locked output set, mediation/escalation lifecycle. |
| Layout, UC-14 | Explicit Layout Editor assignment, version history, final/predecessor checks, creation of ready unpublished article identities. |
| Publication, UC-15 | Explicit current issue selection, schedule failure metadata/recovery, stable DOI allocation and retry rules, public XML export. |
| Users, UC-16 | Journal-scoped membership deletion/deactivation, manager-initiated reset email, reviewer directory administration, self-managed shared identity and separate global account tombstoning. |
| Configuration, UC-17 | Redacted settings history, safe rollback, operator-provisioned integration profiles, immutable test candidates and guarded apply, global audit reads. |
| Proofreading, UC-18 | Version-bound note discovery/read/resolution, production discrepancy escalation, approval invalidation on content changes. |
| Journal administration, §1.3 | Section, page, announcement and journal email-template CRUD; public content projections and a safe template-event/placeholder catalog. |
| Files / NF-02/07 | Single/multipart sessions, resume metadata, signed part targets, abort and complete; purpose/MIME/parent constraints, scan gates and immutable bytes. |
| NF-09/10 | GET/form-POST OAI, Dublin Core XML, Crossref/ORCID boundaries, human vs automated audit identities and platform-level audit visibility. |

## Authorization and identities

Journal permissions require an active, verified identity, a current membership and
the right object ownership or assignment. Platform operations require a separate
system role and do not require a journal membership. A UI context selects an
already-held role; it does not mint a grant. A global context uses a null journal
and `system_administrator`; a journal context must use a `JournalRole`.

Reader/Author enrollment is self-service only for journals open to enrollment.
Journal Managers maintain reviewer directory membership through journal user/role
administration; it only determines eligibility for invitations. Editors invite
reviewers for a specific submission and review round, and acceptance grants access
only to that assignment. There is no self-service reviewer application or registration
role selector. One identity may be an author on one submission and a reviewer on
another; ownership, assignment and conflict checks apply per submission.
Privileged editorial roles remain assigned
through journal membership administration. Initial/global administrator provisioning
is an operator-controlled deployment process, not a public/journal API.

The current `Identifier` is an opaque public string. UUID/ULID is the intended
deployment strategy; internal bigint keys need a stable public mapping before API
implementation. Public identifiers never substitute for object authorization.

Journal Managers may edit journal expertise and membership status, not shared name,
email, affiliation or passwords. Profile name/affiliation are owner-managed. A manager
reset queues the normal reset email; it never reveals a token or lets the manager
choose a password. Membership removal retains the global identity and journal
history. Global deletion is a privileged tombstone, with active-work and last-admin
guards across journals. Published attribution/audit retention is not cascade deletion.

## Privacy boundaries

Reviewers discover screened invitation title/abstract/deadlines before acceptance.
Only accepted eligible assignments can access the round-pinned anonymized manuscript.
The regular submission representation remains unavailable through reviewer authority.
This also applies to file metadata, filenames, downloads, notifications and email.

Authors read only sent decisions and released anonymous comments/annotations through
`listAuthorDecisions`. Internal decision notes, reviewer identities and confidential
comments never enter that schema. Reviewers may read their own confidential draft
comments; other reviewers cannot. Public contributors omit private contact email
and internal account identifiers. Public issues cannot contain draft status,
scheduling/readiness details or failure diagnostics.

List endpoints enforce the same per-object visibility as detail reads. A role listed
on a route is necessary but insufficient. Hidden resources return 404; forbidden
known actions return 403. Signed URLs and token responses use `no-store` and must
not be logged or streamed. Public view/download statistics use opaque visit/session
identifiers and rate limiting; they do not expose individual visitors.

## Workflow and concurrency

`x-workflow` documents state guards for core transitions; outcome maps cover
decision- and galley-stage-dependent results. It is documentation to implement and
test, not executable policy. Never accept an arbitrary client `status` to skip a
workflow step. Validate the persisted state, membership, ownership and referenced
files in the same transaction as the transition, audit record and durable outbox.

`x-concurrency-resource` names the GET resource supplying a strong ETag for each
conditional command. Command URLs share the declared aggregate version. Check the
validator transactionally: missing required condition is 428, stale is 412, illegal
workflow is 409. Re-fetch the aggregate after a command. An exact completed
idempotency replay is returned after current authorization and before evaluating
the now-changed workflow version. Scope keys to actor/journal/operation and retain
fingerprints/results at least 24 hours. Never replay another actor's private result.

Review draft creation uses `If-None-Match: *`; replacement uses `If-Match`. Clients
must not send both or neither. Draft saves accept incomplete scores/comments. Final
review requires the full rubric fields, recommendation and conflict certification.
Version 1 supports double-blind review only; it must not advertise a single-blind
mode while its workspace schema deliberately excludes author identities.
The fixed 1–5 rubric has mandatory originality/methodology and optional
clarity/significance; journal guideline edits create new snapshots. Existing rounds
keep the old rubric and exact manuscript version.

Opening a round alone does not invite reviewers. Invitation creation moves eligible
submissions into review and must be atomic for the batch. Reject duplicate live
assignments, self/co-author conflicts and invalid deadline order. Cancellation is
limited to an unanswered invitation under the SRS rule; an accepted review is not
silently reassigned. An extension request changes nothing until its editor decision.
The final deadline policy is normative in [use cases](use-cases.md): expiry uses
database UTC after locking, including equality; late draft/save/submit requires
a future approved extension. Scheduler delay never authorizes a late write.
Closed rounds block further review writes; deadline events use a durable deduplicated outbox.

Revision requests require a due date. The Journal Manager must explicitly choose
the grace period before enabling automatic withdrawal. Preserve the request's
policy snapshot. At deadline: notify/escalate and mark overdue; at deadline plus
configured grace: withdraw only if automation is enabled and the same request is
still unresolved. A concurrent revision submit and timeout must lock/check the same
state so a completed revision cannot be withdrawn by a stale job. This does not
establish a product-approved grace duration; it prevents guessing one.

The EIC assigns Copyeditors. Approval binds every output file/checksum, not one file
from a multi-file set. Changed content invalidates author approval. The EIC assigns
the Layout Editor; proofreading is assigned to an active journal Proofreader.
Notes bind to exact files/checksums. Only that Proofreader clears corrections.
Content discrepancies open a production escalation; resuming copyediting invalidates
downstream approvals and requires a fresh author/proof cycle. A final galley must
reference its proofread predecessor and receive separate EIC final approval.

Publication requires a non-empty issue of ready same-journal articles with approved
final PDF galleys (HTML/XML are optional companions). Publication and outbox writes
commit together. Notifications and Crossref XML deposits run after commit; a provider
failure cannot roll back published articles. DOI identity remains stable across
retries/redeposits and public `doi_status` distinguishes pending/failed registration.
Scheduled jobs recheck readiness; failures leave content unpublished and record safe
diagnostics for an editor to correct and retry. Current issue selection is explicit.

## Upload protocol

Upload routes now start at `/api/v1/upload-sessions`, avoiding ambiguity with
`/api/v1/files/{file_id}/download`. IDs refer to immutable uploaded bytes. Replacing
bytes creates a new ID; `version` tracks metadata/scan state, not overwritten bytes.

| Purpose | Existing parent / authorization |
| --- | --- |
| Primary/supplementary manuscript | Owning author's submission |
| Revision/response letter | Owning author's revision draft |
| Reviewer annotation | Accepted review assignment (`resource_type=review`) |
| Copyedited manuscript | Assigned copyediting round |
| Galley draft/final | Assigned submission, before a galley row exists |
| User import | Journal; `resource_id` equals `journal_id`, Journal Manager only |
| Ethics evidence | Submission; EIC only |

The declared MIME is only a precheck; inspect actual bytes, size and checksums and
scan before use. The API accepts at most 20 MiB. The initial allowlist intentionally
limits supplementary/evidence files to PDF/DOC/DOCX as for manuscripts; additional
research-data formats need an explicit validated policy extension. HTML/XML galleys
must be sanitized, prevent active-script/external-entity execution, and be served
with appropriate content isolation, MIME and disposition.

`mode=single` returns a signed PUT target. Multipart sessions use fixed 5 MiB parts
(at most four at the current file limit; only the last may be shorter). GET session
returns declared size/checksum and reconciled completed parts. Request a signed
part URL with part number, size and checksum; renewal uses a new idempotency key
without changing pinned bytes. Completion supplies the contiguous unique part set;
server-side storage verification must check all parts, total size, whole checksum
and actual MIME before queuing scanning. Completion is not scan approval.

Abort/complete races are serialized. Aborted/expired sessions cannot be reused,
terminal responses cannot contain upload targets, and incomplete storage parts are
cleaned up. Capability discovery reports only modes actually supported by the
deployed storage adapter; unsupported multipart requests return 409. The contract
does not claim that the current scaffold or shared hosting provides that adapter.

## Configuration, browser transport and external protocols

Access/session limits are explicit configuration bounded to 24 hours. An access
token cannot outlive the session. Refresh rotation cannot extend its absolute
deadline. Revoke/re-evaluate sessions on password reset, deactivation or permission
changes. The contract's password-length range remains 12–128; deployment policy
cannot exceed what the request schemas accept.

The API uses explicit bearer headers and does not accept ambient cookie auth.
A Blade/BFF client may proxy through its own HttpOnly/Secure/SameSite session with
CSRF defenses. Browser fetch streaming supplies the SSE Authorization header;
native EventSource cannot. Never put tokens in URLs. Restrict CORS to configured
origins, expose ETag/Location/Retry-After/X-Request-ID, terminate expired streams and
use `resync_required` when replay retention expires. Notifications remain readable
via REST when streaming infrastructure is unavailable.

ORCID authorization starts with POST `/api/v1/auth/orcid/authorize`, since it creates
server-side state. Linking uses a server-side authorization-code exchange, registered HTTPS
redirects and expiring single-use state bound to the initiator. The callback accepts
exactly one of success `code` or denial `error`; both require state validation.
PKCE is conditional on verified provider/client support. A manually entered ORCID
string is not verified identity. See the [ORCID authentication tutorial](https://info.orcid.org/documentation/api-tutorials/api-tutorial-get-and-authenticated-orcid-id/).

Integration endpoints accept operator-approved profile IDs and credential references,
not arbitrary host URLs/raw credentials. The immutable candidate must pass a test
against the exact credential/configuration version and remain unexpired before apply.
The apply transaction consumes that test result and versions settings. Connection
profiles/egress/secret-manager bindings are deployment configuration; the API does
not provision them. Settings rollback restores non-secret values as a new version
and cannot resurrect stale credentials or bypass current integration validation.

Journal templates allow only catalogued workflow events and safe placeholders,
never code evaluation. Authentication/security templates remain operator-managed.
Sanitize journal page/announcement HTML and restrict publication to approved content.

OAI accepts GET and `application/x-www-form-urlencoded` POST. Verb-specific bad
arguments, unsupported metadata prefixes and invalid resumption tokens return OAI
XML protocol errors with HTTP 200, not generic REST JSON validation errors. Validate
date granularity and exclusive resumption-token combinations; publish only public
metadata. Direct Dublin Core export follows the same visibility rules. See
[OAI-PMH transport and arguments](https://www.openarchives.org/OAI/openarchivesprotocol.html).

Merge PATCH follows [RFC 7396](https://www.rfc-editor.org/rfc/rfc7396.html): omit to
keep a value, use null only where the patch schema permits removal, then validate
the complete merged resource. Required stored fields cannot be removed. Common REST
errors use `Problem`, correlation IDs and safe messages; never return stack traces,
raw provider responses or credentials. Paginated lists require totals and links.

## Validation and remaining release gates

`npm run check:openapi` validates OpenAPI structure/references/examples and runs
contract tests, including negative privilege, privacy, upload, state, pagination,
session, integration-candidate and content cases. All pass locally. The two Laravel
scaffold tests, Composer validation and Pint remain separate baseline checks.

This completes the reviewed API-definition gaps, not runtime development or proof
of security/performance. Hosting availability, storage/scan adapters, SMTP delivery,
ORCID/Crossref sandbox behavior, transaction races, scheduler/outbox reliability,
PostgreSQL constraints, ≥70% core-code coverage, browser usability and backup restore
drills still require implementation and measurement. NF-01's 20 MB/<10s at 1 Mbps
target remains physically inconsistent and needs an SRS correction; no API schema
can satisfy that bandwidth claim. The [historical review](review-srs.md) records the
original evidence and deployment constraints.
