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

test('default registration needs no privileged role; reviewer application is journal-scoped', () => {
    const registration = { full_name: 'Test Author', email: 'author@example.org', username: 'author', password: 'TestPassword123!', affiliation: 'University' };
    check('RegisterRequest', registration);
    check('RegisterRequest', { ...registration, requested_role: 'reviewer' }, false);
    check('RegisterRequest', { ...registration, requested_role: 'reviewer', journal_id: 'journal-1' });
    check('RegisterRequest', { ...registration, requested_role: 'system_administrator' }, false);
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
    check('ReviewWorkspaceResponse', workspace);
    for (const key of ['author_id', 'contributors', 'other_reviewers', 'source_object_key']) {
        check('ReviewWorkspaceResponse', { ...workspace, data: { ...workspace.data, [key]: 'private' } }, false);
    }
    assert.match(getOperation('getReviewerWorkspace').description, /accepted/);
    assert.match(getOperation('downloadPrivateFile').description, /after accepting/);
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
