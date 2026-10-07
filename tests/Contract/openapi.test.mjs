import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import YAML from 'yaml';
import Ajv2020 from 'ajv/dist/2020.js';
import addFormats from 'ajv-formats';

// OPENAPI_FILE permits running the same regressions against the pre-fix contract.
const document = YAML.parseDocument(readFileSync(process.env.OPENAPI_FILE ?? 'docs/openapi.yaml', 'utf8'), { uniqueKeys: true });
assert.deepEqual(document.errors, [], 'OpenAPI YAML must not contain duplicate keys or parse errors');
const api = document.toJS();
const schemas = api.components.schemas;
const methods = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'trace'];
const operations = Object.entries(api.paths).flatMap(([path, item]) =>
    Object.entries(item).filter(([method]) => methods.includes(method)).map(([method, operation]) => ({ path, item, method, operation })));
const getOperation = name => {
    const found = operations.find(({ operation }) => operation.operationId === name);
    assert.ok(found, `Missing operation ${name}`);
    return found.operation;
};
function resolve(value) {
    if (!value?.$ref) return value;
    assert.ok(value.$ref.startsWith('#/'), `Unexpected external reference ${value.$ref}`);
    const result = value.$ref.slice(2).split('/').reduce((node, key) => node?.[key.replaceAll('~1', '/').replaceAll('~0', '~')], api);
    assert.ok(result, `Unresolved reference ${value.$ref}`);
    return resolve(result);
}
const ajv = new Ajv2020({ allErrors: true, strictSchema: true, strictTypes: false, strictRequired: false });
addFormats(ajv);
// OAS annotations do not change JSON Schema validation.
ajv.addKeyword('example');
ajv.addFormat('password', true);
const schemaRoot = {
    $id: 'urn:sirida:contract',
    $defs: JSON.parse(JSON.stringify(schemas).replaceAll('#/components/schemas/', '#/$defs/')),
};
ajv.addSchema(schemaRoot);
const check = (name, value, expected = true) => {
    const validate = ajv.getSchema(`urn:sirida:contract#/$defs/${name}`);
    assert.ok(validate, `Missing schema ${name}`);
    assert.equal(validate(value), expected, `${name}: ${JSON.stringify(validate.errors)}`);
};
const parameters = ({ item, operation }) => [...(item.parameters ?? []), ...(operation.parameters ?? [])].map(resolve);

test('all schemas compile and every local reference resolves', () => {
    for (const name of Object.keys(schemas)) assert.ok(ajv.getSchema(`urn:sirida:contract#/$defs/${name}`), name);
    function walk(node) {
        if (!node || typeof node !== 'object') return;
        if (node.$ref) resolve(node);
        for (const child of Object.values(node)) walk(child);
    }
    walk(api);
});

test('operations have unique IDs and complete path parameters', () => {
    const ids = new Set();
    for (const entry of operations) {
        assert.ok(entry.operation.operationId);
        assert.ok(!ids.has(entry.operation.operationId), entry.operation.operationId);
        ids.add(entry.operation.operationId);
        const expected = [...entry.path.matchAll(/\{([^}]+)\}/g)].map(match => match[1]).sort();
        const actual = parameters(entry).filter(p => p.in === 'path');
        assert.deepEqual(actual.map(p => p.name).sort(), expected, entry.path);
        assert.ok(actual.every(p => p.required === true));
    }
});

test('journal create, role replacement and import payloads reject platform privilege escalation', () => {
    const create = { full_name: 'Test User', email: 'user@example.org', username: 'user', affiliation: 'Test University', roles: ['author'] };
    check('CreateJournalUserRequest', create);
    check('CreateJournalUserRequest', { ...create, roles: ['system_administrator'] }, false);
    check('ReplaceRolesRequest', { roles: ['reader', 'author'], reason: 'Approved by manager' });
    check('ReplaceRolesRequest', { roles: [], reason: 'Revoke membership access' });
    check('ReplaceRolesRequest', { roles: ['system_administrator'], reason: 'Escalate privileges' }, false);
    check('CreateUserImportRequest', { file_id: 'file-1', default_roles: ['reviewer'] });
    check('CreateUserImportRequest', { file_id: 'file-1', default_roles: ['system_administrator'] }, false);
});

test('global roles are represented separately from journal memberships', () => {
    const membership = { journal_id: 'journal-1', journal_name: 'Journal', roles: ['author'], permissions: [], status: 'active' };
    check('Membership', membership);
    check('Membership', { ...membership, roles: ['system_administrator'] }, false);
    for (const name of ['MeResponse', 'AuthTokenResponse']) {
        const data = schemas[name].properties.data;
        assert.ok(data.required.includes('system_roles'));
        assert.equal(data.properties.system_roles.items.$ref, '#/components/schemas/SystemRole');
    }
    assert.equal(schemas.JournalUserResource.properties.attributes.properties.membership.$ref, '#/components/schemas/Membership');
});

test('registration accepts an account without role selection and rejects legacy role requests', () => {
    const registration = { full_name: 'Test Author', email: 'author@example.org', username: 'author', password: 'TestPassword123!', affiliation: 'University' };
    check('RegisterRequest', registration);
    for (const role of ['author', 'reviewer', 'system_administrator']) {
        check('RegisterRequest', { ...registration, requested_role: role }, false);
        check('RegisterRequest', { ...registration, requested_role: role, journal_id: 'journal-1' }, false);
    }
    check('RegisterRequest', { ...registration, journal_id: 'journal-1' }, false);
    check('RegisterRequest', { ...registration, roles: ['reviewer'] }, false);
    const { username, ...withoutUsername } = registration;
    check('RegisterRequest', withoutUsername, false);
});

test('reviewer recruitment uses editorial assignments instead of self-service applications', () => {
    assert.ok(!Object.keys(api.paths).some(path => path.includes('/reviewer-applications')));
    assert.ok(!Object.keys(schemas).some(name => name.includes('ReviewerApplication')));
    assert.equal(api.components.parameters.ApplicationId, undefined);
    assert.equal(schemas.ApplicationDecisionRequest, undefined);
    assert.deepEqual(getOperation('assignReviewers')['x-required-roles'], ['section_editor', 'editor_in_chief']);
    assert.deepEqual(getOperation('acceptReviewAssignment')['x-workflow'].allowed_states, ['invited']);
    assert.equal(getOperation('acceptReviewAssignment')['x-workflow'].resulting_state, 'accepted');
});

test('login and refresh return usable tokens without request-only annotations or cache storage', () => {
    for (const name of ['access_token', 'refresh_token']) {
        assert.notEqual(schemas.AuthTokens.properties[name].writeOnly, true, name);
        assert.equal(schemas.AuthTokens.properties[name].readOnly, true, name);
    }
    for (const name of ['login', 'refreshAccessToken']) {
        const headers = getOperation(name).responses['200'].headers;
        assert.equal(resolve(headers['Cache-Control']).schema.const, 'no-store');
    }
    assert.equal(resolve(api.components.responses.Unauthorized.headers['WWW-Authenticate']).schema.type, 'string');
    assert.ok(getOperation('login').responses['423'].headers['Retry-After']);
});

test('reviewer authorization cannot return the identity-bearing submission schema', () => {
    assert.ok(!getOperation('getSubmission')['x-required-roles'].includes('reviewer'));
    const invitation = {
        id: 'assignment-1', type: 'review_assignment', attributes: {
            review_round_id: 'round-1', reviewer_id: 'reviewer-1', submission_id: 'submission-1',
            title: 'Screened title', abstract: 'Screened abstract', status: 'invited',
            response_due_at: '2026-10-10T00:00:00Z', review_due_at: '2026-10-20T00:00:00Z', version: 1,
        },
    };
    check('ReviewAssignmentResource', invitation);
    for (const field of ['author_id', 'contributors', 'manuscript_file_id', 'file_name']) {
        check('ReviewAssignmentResource', { ...invitation, attributes: { ...invitation.attributes, [field]: 'private' } }, false);
    }
    assert.ok(getOperation('listReviewAssignments')['x-required-roles'].includes('reviewer'));
    assert.ok(getOperation('getReviewAssignment')['x-required-roles'].includes('reviewer'));
});

test('review workspace is blind while preserving the originating reviewer draft', () => {
    const workspace = { data: { assignment_id: 'assignment-1', manuscript_file_id: 'anonymous-file-1', title: 'Title', abstract: 'Abstract', deadline: '2026-10-20T00:00:00Z', guidelines: 'Guidelines', draft: null }, meta: { request_id: 'request-1' } };
    workspace.data.rubric = { id: 'rubric-1', version: 1, criteria: { originality: 'Originality', methodology: 'Methodology', clarity: 'Clarity', significance: 'Significance' }, minimum_score: 1, maximum_score: 5, guidelines: 'Review fairly.' };
    check('ReviewWorkspaceResponse', workspace);
    for (const key of ['author_id', 'contributors', 'other_reviewers', 'source_object_key']) {
        check('ReviewWorkspaceResponse', { ...workspace, data: { ...workspace.data, [key]: 'private' } }, false);
    }
    assert.match(getOperation('getReviewerWorkspace').description, /accepted/);
    assert.match(getOperation('downloadPrivateFile').description, /after accepting/);
});

test('a new submission can be saved before metadata and guideline consent are complete', () => {
    check('CreateSubmissionRequest', { title: 'Working title' });
    check('CreateSubmissionRequest', { author_guidelines_accepted: false });
    check('CreateSubmissionRequest', {}, false);
    check('CreateSubmissionRequest', { title: 'Working title', status: 'published' }, false);
    assert.match(getOperation('submitSubmission').description, /complete|non-empty/);
    assert.deepEqual(getOperation('completeIncompleteSubmission')['x-workflow'].allowed_states, ['incomplete']);
});

test('public issues reject drafts and editorial readiness information', () => {
    const issue = { id: 'issue-1', type: 'issue', attributes: { journal_id: 'journal-1', volume: '1', number: '1', year: 2026, title: 'Issue', status: 'published', articles: [] } };
    check('PublicIssueResource', issue);
    check('PublicIssueResource', { ...issue, attributes: { ...issue.attributes, status: 'draft' } }, false);
    for (const field of ['readiness_errors', 'scheduled_at', 'ready_to_publish', 'publication_failure_code']) {
        check('PublicIssueResource', { ...issue, attributes: { ...issue.attributes, [field]: 'private' } }, false);
    }
    for (const name of ['listPublicIssues', 'getPublicIssue', 'getCurrentPublicIssue']) {
        assert.match(getOperation(name).responses['200'].content['application/json'].schema.$ref, /PublicIssue/);
    }
});

test('every paginated collection requires pagination totals and links', () => {
    for (const { operation } of operations) {
        if (!(operation.parameters ?? []).map(resolve).some(p => p.name === 'page')) continue;
        const envelope = resolve(operation.responses['200'].content['application/json'].schema);
        assert.ok(envelope.required.includes('links'), operation.operationId);
        assert.ok(resolve(envelope.properties.meta).required.includes('pagination'), operation.operationId);
    }
});

test('upload purpose binds MIME and existing parent before a dependent resource is created', () => {
    const upload = { journal_id: 'journal-1', resource_type: 'submission', resource_id: 'submission-1', purpose: 'galley_final', file_name: 'galley.pdf', content_type: 'application/pdf', size: 1024, checksum_sha256: 'a'.repeat(64), mode: 'single' };
    check('CreateUploadSessionRequest', upload);
    check('CreateUploadSessionRequest', { ...upload, resource_type: 'galley' }, false);
    check('CreateUploadSessionRequest', { ...upload, purpose: 'primary_manuscript', content_type: 'text/html' }, false);
    check('CreateUploadSessionRequest', { ...upload, purpose: 'reviewer_annotation', resource_type: 'review' });
    check('CreateUploadSessionRequest', { ...upload, purpose: 'reviewer_annotation', resource_type: 'submission' }, false);
    check('CreateUploadSessionRequest', { ...upload, arbitrary_storage_key: 'private/object' }, false);
    assert.ok(getOperation('getUploadSession'));
    assert.ok(getOperation('abortUploadSession'));
});

test('single and multipart completion payloads are unambiguous and bounded', () => {
    const part = { part_number: 1, etag: 'etag-1', size: 1024, checksum_sha256: 'a'.repeat(64) };
    check('CompleteUploadRequest', { mode: 'single', etag: 'etag-1' });
    check('CompleteUploadRequest', { mode: 'multipart', parts: [part] });
    check('CompleteUploadRequest', { mode: 'single', etag: 'etag-1', parts: [part] }, false);
    check('CompleteUploadRequest', { mode: 'multipart', parts: [] }, false);
    check('CompleteUploadRequest', { mode: 'multipart', parts: [{ ...part, part_number: 5 }] }, false);
    check('UploadPartRequest', { part_number: 1, size: 5242881, checksum_sha256: 'a'.repeat(64) }, false);
    assert.match(getOperation('completeUploadSession').description, /unique, contiguous and complete/);
});

test('terminal upload sessions cannot return a reusable signed target', () => {
    const session = { data: { id: 'upload-1', type: 'upload_session', attributes: { file_id: 'file-1', mode: 'single', status: 'completed', expires_at: '2026-10-07T00:00:00Z', version: 1 } }, meta: { request_id: 'request-1' } };
    Object.assign(session.data.attributes, { size: 1024, checksum_sha256: 'a'.repeat(64), content_type: 'application/pdf', file_name: 'manuscript.pdf' });
    check('UploadSessionResponse', session);
    check('UploadSessionResponse', { ...session, data: { ...session.data, attributes: { ...session.data.attributes, upload_url: 'https://storage.example.org/private' } } }, false);
    const active = { ...session.data.attributes, status: 'pending_upload', upload_url: 'https://storage.example.org/upload', method: 'PUT', required_headers: {} };
    check('UploadSessionResponse', { ...session, data: { ...session.data, attributes: active } });
});

test('copyedit approvals bind the whole output set and final galley requires a predecessor', () => {
    check('CopyeditApprovalRequest', { files: [{ file_id: 'file-1', checksum_sha256: 'a'.repeat(64) }, { file_id: 'file-2', checksum_sha256: 'b'.repeat(64) }] });
    check('CopyeditApprovalRequest', { files: [] }, false);
    check('CopyeditApprovalRequest', { file_id: 'file-1', checksum_sha256: 'a'.repeat(64) }, false);
    assert.equal(getOperation('approveCopyeditAsAuthor').requestBody.content['application/json'].schema.$ref, '#/components/schemas/CopyeditApprovalRequest');
    check('CreateGalleyRequest', { file_id: 'file-1', format: 'pdf', stage: 'draft' });
    check('CreateGalleyRequest', { file_id: 'file-1', format: 'pdf', stage: 'final' }, false);
    check('CreateGalleyRequest', { file_id: 'file-1', format: 'pdf', stage: 'final', supersedes_galley_id: 'proof-1' });
});

test('journal managers cannot mutate a global profile through membership administration', () => {
    check('UpdateJournalUserRequest', { expertise: ['Environment'], status: 'active' });
    for (const field of ['full_name', 'email', 'affiliation', 'password', 'system_roles']) {
        check('UpdateJournalUserRequest', { [field]: 'new value' }, false);
    }
    check('UpdateProfileRequest', { full_name: 'Account Owner' });
    assert.deepEqual(getOperation('deleteSystemUser')['x-required-roles'], ['system_administrator']);
    assert.deepEqual(getOperation('removeJournalMembership')['x-required-roles'], ['journal_manager']);
    assert.match(getOperation('removeJournalMembership').description, /active submissions/);
});

test('active context distinguishes global administrator from a journal role', () => {
    check('ActiveContext', { journal_id: 'journal-1', role: 'author' });
    check('ActiveContext', { journal_id: null, role: 'system_administrator' });
    check('ActiveContext', { journal_id: 'journal-1', role: 'system_administrator' }, false);
    check('ActiveContext', { journal_id: null, role: 'journal_manager' }, false);
});

test('extension decisions require a reason and restricted actors', () => {
    check('ExtensionDecisionRequest', { decision: 'decline', reason: 'Issue deadline' });
    check('ExtensionDecisionRequest', { decision: 'decline' }, false);
    assert.ok(!getOperation('decideReviewExtension')['x-required-roles'].includes('reviewer'));
});

test('integration testing cannot accept arbitrary hosts or apply unbound raw secrets', () => {
    check('IntegrationCandidateRequest', { configuration_profile_id: 'profile-1', credential_ref: 'credentials/crossref-1' });
    check('IntegrationCandidateRequest', { configuration_profile_id: 'profile-1', credential_ref: 'credentials/crossref-1', url: 'http://127.0.0.1/admin' }, false);
    check('TestIntegrationRequest', { candidate_id: 'candidate-1' });
    check('TestIntegrationRequest', { candidate_settings: { url: 'http://169.254.169.254' } }, false);
    check('UpdateSystemSettingsRequest', { integration_secrets: { crossref: 'raw-secret' } }, false);
    check('ApplyIntegrationRequest', { candidate_id: 'candidate-1', reason: 'Verified in sandbox' });
    check('ApplyIntegrationRequest', { reason: 'No test' }, false);
    assert.match(getOperation('applyTestedIntegration').description, /passed, unexpired/);
});

test('human and automated audit actors have distinct required identities', () => {
    const event = { id: 'event-1', type: 'audit_log', attributes: { journal_id: null, actor_id: null, effective_role: null, actor_type: 'system', system_actor: 'publication_scheduler', action: 'issue.published', resource_type: 'issue', resource_id: 'issue-1', request_id: 'request-1', occurred_at: '2026-10-07T00:00:00Z' } };
    check('AuditLogResource', event);
    check('AuditLogResource', { ...event, attributes: { ...event.attributes, actor_id: 'forged-user' } }, false);
    check('AuditLogResource', { ...event, attributes: { ...event.attributes, actor_type: 'user', actor_id: 'user-1', effective_role: 'system_administrator', system_actor: null } });
    check('AuditLogResource', { ...event, attributes: { ...event.attributes, actor_type: 'user', system_actor: null } }, false);
    assert.deepEqual(getOperation('listSystemAuditLogs')['x-required-roles'], ['system_administrator']);
});

test('revision decisions require a due date while accept/reject do not', () => {
    check('CreateEditorialDecisionRequest', { decision: 'accept', note_for_author: 'Accepted.' });
    check('CreateEditorialDecisionRequest', { decision: 'major_revision', note_for_author: 'Revise.' }, false);
    check('CreateEditorialDecisionRequest', { decision: 'major_revision', note_for_author: 'Revise.', revision_due_at: '2026-11-01T00:00:00Z' });
    check('OverrideDecisionRequest', { corrected_decision: 'minor_revision', note_for_author: 'Revise.', reason: 'Correct previous decision' }, false);
});

test('session and password policies have achievable bounded values', () => {
    check('UpdateSystemSettingsRequest', { access_token_minutes: 15, session_max_minutes: 1440, password_min_length: 12 });
    check('UpdateSystemSettingsRequest', { access_token_minutes: 1441 }, false);
    check('UpdateSystemSettingsRequest', { password_min_length: 129 }, false);
    check('UpdateJournalSettingsRequest', { revision_grace_days: null, auto_withdraw_overdue_revisions: false });
    check('UpdateJournalSettingsRequest', { revision_grace_days: -1 }, false);
});

test('journal content CRUD is private and published projections reject draft content', () => {
    for (const kind of ['Section', 'JournalPage', 'Announcement', 'EmailTemplate']) {
        for (const action of ['create', 'update', 'delete']) assert.deepEqual(getOperation(`${action}${kind}`)['x-required-roles'], ['journal_manager']);
    }
    const page = { id: 'page-1', type: 'journal_page', attributes: { slug: 'about', title: 'About', body_html: '<p>About this journal.</p>', published: true } };
    check('PublicJournalPageResource', page);
    check('PublicJournalPageResource', { ...page, attributes: { ...page.attributes, published: false } }, false);
    assert.ok(getOperation('getSubmissionPolicy').security.length === 0);
});

test('public page navigation uses metadata and slug detail supplies published content', () => {
    const list = getOperation('listPublicJournalPages');
    const detail = getOperation('getPublicJournalPage');
    const listMedia = list.responses['200'].content['application/json'];
    const detailMedia = detail.responses['200'].content['application/json'];
    assert.deepEqual(list.security, []);
    assert.deepEqual(detail.security, []);
    check('PublicJournalPagesListResponse', listMedia.example);
    check('PublicJournalPageResponse', detailMedia.example);
    const summary = listMedia.example.data[0];
    assert.deepEqual(Object.keys(summary.attributes).sort(), ['slug', 'title']);
    check('PublicJournalPageSummaryResource', {
        ...summary, attributes: { ...summary.attributes, body_html: '<p>Unexpected content</p>' },
    }, false);
    const page = detailMedia.example.data;
    check('PublicJournalPageResponse', {
        ...detailMedia.example, data: { ...page, attributes: { ...page.attributes, published: false } },
    }, false);
    check('PublicJournalPageResponse', { data: summary, meta: { request_id: 'test' } }, false);
    assert.ok(detail.responses['404']);
    const entry = operations.find(({ operation }) => operation.operationId === 'getPublicJournalPage');
    assert.equal(entry.path, '/api/v1/public/journals/{journal_slug}/pages/{page_slug}');
    assert.deepEqual(parameters(entry).filter(p => p.in === 'path').map(p => p.name).sort(), ['journal_slug', 'page_slug']);
});

test('all detailed SRS use cases have traceable operations and no endpoint claims implementation', () => {
    const covered = new Set(operations.flatMap(({ operation }) => operation['x-use-cases'] ?? []));
    for (let i = 1; i <= 18; i++) assert.ok(covered.has(`UC-${String(i).padStart(2, '0')}`));
    for (const { operation } of operations) {
        assert.equal(operation['x-implementation-status'], 'planned', operation.operationId);
        assert.ok(operation['x-non-functional-requirements'].includes('NF-07'));
    }
});

test('workflow guards refer to known states and always document a conflict response', () => {
    for (const { operation } of operations) {
        const flow = operation['x-workflow'];
        if (!flow) continue;
        const states = resolve(flow.state_schema).enum;
        for (const state of flow.allowed_states) assert.ok(states.includes(state), `${operation.operationId}: ${state}`);
        const outcomes = [flow.resulting_state, ...Object.values(flow.result_by_decision ?? {}), ...Object.values(flow.result_by_stage ?? {})].filter(Boolean);
        for (const state of outcomes) assert.ok(states.includes(state), `${operation.operationId}: ${state}`);
        assert.ok(operation.responses['409']);
    }
});

test('GET operations never accept request bodies and public reads never inherit bearer security', () => {
    for (const { path, method, operation } of operations) {
        if (method === 'get') assert.equal(operation.requestBody, undefined, operation.operationId);
        if (path.startsWith('/api/v1/public/') || path === '/oai') assert.deepEqual(operation.security, [], operation.operationId);
        if (path.startsWith('/api/v1/system/')) assert.deepEqual(operation['x-required-roles'], ['system_administrator'], operation.operationId);
    }
});

test('OAuth callback can represent denial while preserving mandatory state binding', () => {
    const callback = getOperation('handleOrcidCallback');
    const params = callback.parameters.map(resolve);
    assert.equal(params.find(p => p.name === 'state').required, true);
    assert.notEqual(params.find(p => p.name === 'code').required, true);
    assert.ok(params.some(p => p.name === 'error'));
    assert.match(callback.description, /exactly one of code or error/);
    assert.ok(getOperation('getOrcidConnection'));
    assert.ok(api.paths['/api/v1/auth/orcid/authorize'].post);
    assert.equal(api.paths['/api/v1/auth/orcid/authorize'].get, undefined);
});

test('journal templates cannot override authentication/security email and mononyms remain valid', () => {
    check('EmailTemplateEvent', 'review.invitation');
    check('EmailTemplateEvent', 'account.password_reset', false);
    assert.ok(getOperation('listEmailTemplateEvents'));
    check('Contributor', { family_name: 'Adrian', email: 'author@example.org', affiliation: 'University', sequence: 1, corresponding: true });
    check('PublicContributor', { family_name: 'Adrian', affiliation: 'University', sequence: 1, corresponding: true });
    check('CreateReviewRoundRequest', { review_type: 'single_blind', minimum_completed_reviews: 1, manuscript_file_id: 'file-1' }, false);
});

test('author feedback rejects confidential comments and reviewer identifiers', () => {
    const feedback = { reviewer_label: 'Reviewer A', comments_for_author: 'Clarify the method.', annotation_file_ids: [] };
    const decision = { id: 'decision-1', type: 'author_decision', attributes: { submission_id: 'submission-1', decision: 'minor_revision', note_for_author: 'Please revise.', sent_at: '2026-10-06T00:00:00Z', reviews: [feedback] } };
    check('AuthorDecisionResource', decision);
    for (const key of ['internal_note', 'confidential_comments_for_editor', 'reviewer_id']) {
        check('AuthorDecisionResource', { ...decision, attributes: { ...decision.attributes, [key]: 'private' } }, false);
    }
    for (const key of ['reviewer_id', 'assignment_id', 'confidential_comments_for_editor']) {
        check('AuthorReviewFeedback', { ...feedback, [key]: 'private' }, false);
    }
    assert.deepEqual(getOperation('listAuthorDecisions')['x-required-roles'], ['author']);
});

test('public author metadata does not disclose submission contact email', () => {
    const contributor = { given_name: 'Author', family_name: 'Example', affiliation: 'University', sequence: 1, corresponding: true };
    check('PublicContributor', contributor);
    check('PublicContributor', { ...contributor, email: 'private@example.org' }, false);
    check('PublicContributor', { ...contributor, id: 'internal-user-id' }, false);
    assert.equal(schemas.ArticleAttributes.properties.contributors.items.$ref, '#/components/schemas/PublicContributor');
});

test('a reviewer can save partial work but cannot submit an incomplete evaluation', () => {
    check('ReviewDraftRequest', { scores: { originality: 4 } });
    check('ReviewDraftRequest', { comments_for_author: 'Work in progress' });
    check('ReviewDraftRequest', {}, false);
    check('SubmitReviewRequest', { scores: { originality: 4 } }, false);
    const complete = { scores: { originality: 4, methodology: 3 }, comments_for_author: 'Good approach.', confidential_comments_for_editor: '', recommendation: 'minor_revision', certify_no_conflict: true };
    check('SubmitReviewRequest', complete);
    check('SubmitReviewRequest', { ...complete, certify_no_conflict: false }, false);
    const review = { id: 'review-1', type: 'review', attributes: { assignment_id: 'assignment-1', status: 'draft', version: 1, scores: { originality: 4 } } };
    check('ReviewResource', review);
    check('ReviewResource', { ...review, attributes: { ...review.attributes, status: 'submitted' } }, false);
});

test('every conditional mutation has a readable ETag and documented precondition failures', () => {
    for (const entry of operations) {
        if (!parameters(entry).some(p => p.name === 'If-Match')) continue;
        const operation = entry.operation;
        const target = api.paths[operation['x-concurrency-resource']]?.get;
        assert.ok(target, `${operation.operationId}: no concurrency GET`);
        assert.ok(target.responses['200'].headers?.ETag, `${operation.operationId}: GET missing ETag`);
        assert.equal(operation.responses['412']?.$ref, '#/components/responses/PreconditionFailed', operation.operationId);
        assert.equal(operation.responses['428']?.$ref, '#/components/responses/PreconditionRequired', operation.operationId);
        for (const role of operation['x-required-roles']) {
            assert.ok(target['x-required-roles'].includes(role) || target['x-required-roles'].includes('authenticated'), `${operation.operationId}: ${role} cannot read validator`);
        }
    }
    assert.ok(getOperation('saveReviewDraft').parameters.map(resolve).some(p => p.name === 'If-None-Match' && p.schema.const === '*'));
});

test('secured operations declare authentication and authorization failures; idempotent writes declare conflict', () => {
    for (const entry of operations) {
        const operation = entry.operation;
        if ((operation.security ?? api.security).length) {
            assert.ok(operation.responses['401'], operation.operationId);
            assert.ok(operation.responses['403'], operation.operationId);
        }
        if (parameters(entry).some(p => p.name === 'Idempotency-Key')) assert.ok(operation.responses['409'], operation.operationId);
    }
});

test('a manager can upload an import without an import operation already existing', () => {
    const upload = { journal_id: 'journal-1', resource_type: 'journal', resource_id: 'journal-1', purpose: 'user_import', file_name: 'users.csv', content_type: 'text/csv', size: 128, checksum_sha256: 'a'.repeat(64) };
    check('CreateUploadSessionRequest', upload);
    check('CreateUploadSessionRequest', { ...upload, resource_type: 'user_import' }, false);
    check('CreateUploadSessionRequest', { ...upload, content_type: 'application/pdf' }, false);
    check('CreateUploadSessionRequest', { ...upload, size: 20971521 }, false);
    for (const name of ['createUploadSession', 'completeUploadSession']) assert.ok(getOperation(name)['x-required-roles'].includes('journal_manager'));
});

test('a review round pins a manuscript and unpublished article IDs can be discovered', () => {
    const round = { review_type: 'double_blind', minimum_completed_reviews: 2, manuscript_file_id: 'file-1' };
    check('CreateReviewRoundRequest', round);
    const { manuscript_file_id, ...unpinned } = round;
    check('CreateReviewRoundRequest', unpinned, false);
    const ready = getOperation('listReadyArticles');
    assert.notDeepEqual(ready.security ?? api.security, []);
    assert.ok(!ready['x-required-roles'].includes('public'));
    check('ReplaceIssueArticlesRequest', { articles: [] });
    assert.match(getOperation('publishIssue').description, /non-empty/);
});

test('OAI-PMH supports both GET and form POST with matching wire arguments and XML', () => {
    const get = api.paths['/oai'].get;
    const post = api.paths['/oai'].post;
    assert.ok(post, 'OAI-PMH requires POST');
    assert.deepEqual(get.security, []);
    assert.deepEqual(post.security, []);
    const input = resolve(post.requestBody.content['application/x-www-form-urlencoded'].schema);
    assert.deepEqual(Object.keys(input.properties).sort(), get.parameters.map(p => p.name).sort());
    assert.ok(get.responses['200'].content['application/xml']);
    assert.ok(post.responses['200'].content['application/xml']);
    check('OaiPmhRequest', { verb: 'ListRecords', metadataPrefix: 'oai_dc' });
    check('OaiPmhRequest', { verb: 'ListRecords', resumptionToken: 'opaque-token' });
});

test('all documented JSON media examples validate against their schemas', () => {
    function walk(node) {
        if (!node || typeof node !== 'object') return;
        for (const [mediaType, media] of Object.entries(node.content ?? {})) {
            if (!mediaType.includes('json') || !media.schema) continue;
            const schema = JSON.parse(JSON.stringify(media.schema).replaceAll('#/components/schemas/', 'urn:sirida:contract#/$defs/'));
            const validate = ajv.compile(schema);
            const examples = [...(media.example === undefined ? [] : [media.example]), ...Object.values(media.examples ?? {}).map(x => resolve(x).value)];
            for (const example of examples) assert.ok(validate(example), JSON.stringify(validate.errors));
        }
        for (const child of Object.values(node)) walk(child);
    }
    walk(api);
});

test('deadline transitions and guards cannot reference missing states, fields or error responses', () => {
    const policy = api['x-deadline-policy'];
    assert.equal(policy.expires_when, 'now >= due_at');
    assert.equal(policy.clock, 'database_utc_after_lock');
    assert.equal(policy.write_enforcement, 'independent_of_scheduler');
    const states = schemas.ReviewAssignmentStatus.enum;
    const fields = schemas.ReviewAssignmentResource.properties.attributes.properties;
    for (const transition of policy.transitions) {
        assert.ok(states.includes(transition.from));
        assert.ok(states.includes(transition.to));
        assert.ok(!policy.terminal_states.includes(transition.from));
        assert.equal(fields[transition.deadline_field].$ref, '#/components/schemas/Timestamp');
        assert.ok(transition.recipients.includes('assigned_section_editor'));
    }
    for (const id of ['acceptReviewAssignment', 'declineReviewAssignment', 'saveReviewDraft', 'submitReview']) {
        const operation = getOperation(id);
        const guard = operation['x-deadline-guard'];
        assert.equal(guard.comparison, 'now < due_at', id);
        assert.equal(guard.clock, policy.clock, id);
        assert.ok(fields[guard.field], id);
        const code = api['x-error-codes'][guard.error_code];
        assert.ok(operation.responses[String(code.status)], id);
    }
    for (const id of ['saveReviewDraft', 'submitReview']) {
        assert.deepEqual(getOperation(id)['x-workflow'].allowed_states, ['accepted']);
    }
    assert.equal(policy.late_review_submission, 'approved_extension_required');
});

test('automatic withdrawal cannot be enabled on stored settings without an explicit integer grace', () => {
    const settings = { name: 'Journal', language: 'id', timezone: 'Asia/Makassar', review_type: 'double_blind', minimum_completed_reviews: 2, access_model: 'diamond_open_access', version: 1 };
    check('JournalSettings', settings);
    check('JournalSettings', { ...settings, auto_withdraw_overdue_revisions: false, revision_grace_days: null });
    check('JournalSettings', { ...settings, auto_withdraw_overdue_revisions: true }, false);
    check('JournalSettings', { ...settings, auto_withdraw_overdue_revisions: true, revision_grace_days: null }, false);
    check('JournalSettings', { ...settings, auto_withdraw_overdue_revisions: true, revision_grace_days: 0 });
});

test('integration guide request fixtures validate against the actual operation input schemas', () => {
    const examples = JSON.parse(readFileSync('docs/request-examples.json', 'utf8'));
    for (const { operationId, body } of examples) {
        const request = resolve(getOperation(operationId).requestBody);
        const schema = request.content['application/json'].schema;
        assert.ok(schema.$ref, operationId);
        check(schema.$ref.split('/').at(-1), body);
    }
});

test('final baseline distinguishes planned implementation and links to existing guidance', () => {
    const baseline = api['x-contract-status'];
    assert.equal(api.info.version, baseline.version);
    assert.equal(baseline.implementation, 'planned');
    for (const file of baseline.documents) assert.ok(readFileSync(file, 'utf8').length > 0, file);
    const guide = readFileSync('docs/use-cases.md', 'utf8');
    for (let i = 1; i <= 18; i++) {
        const uc = `UC-${String(i).padStart(2, '0')}`;
        assert.ok(guide.includes(uc), uc);
        assert.ok(operations.some(({ operation }) => operation['x-use-cases']?.includes(uc)), uc);
    }
});
