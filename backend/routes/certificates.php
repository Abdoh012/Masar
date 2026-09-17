<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/auth.php';
require_once __DIR__ . '/../app/modules/certificates/controllers/certificate_controller.php';

/*
 * ==========================================================================
 * Certificate API Routes
 * ==========================================================================
 *
 * Lifecycle: eligible -> pending (request) -> issued (confirm) -> revoked
 *
 * GET /api/v1/certificates
 * — List certificates scoped to the authenticated user
 *
 * GET /api/v1/certificates/eligible
 * — Trainings a student/company may request a certificate for
 *
 * GET /api/v1/certificates/pending
 * — Pending certificate requests for the authenticated student (status = pending)
 *
 * GET /api/v1/certificates/issued
 * — Issued certificates for the authenticated user (status = issued)
 *
 * GET /api/v1/certificates/revoked
 * — Revoked certificates for the authenticated user (status = revoked)
 *
 * POST /api/v1/certificates
 * — Request a certificate (creates a PENDING row)
 *
 * GET /api/v1/certificates/{id}
 * — View a certificate (numbers hidden until issued)
 *
 * POST /api/v1/certificates/{id}/confirm
 * — Company/admin confirms a pending request (-> issued)
 *
 * POST /api/v1/certificates/{id}/revoke
 * — Company/admin revokes an issued certificate
 */

$path = request_path();
$method = request_method();

// GET /api/v1/certificates — List my certificates
if ($path === '/api/v1/certificates' && $method === 'GET') {
    middleware_auth();
    certificate_controller_index();
    return;
}

// GET /api/v1/certificates/eligible — Trainings I can request a certificate for.
// Registered before the /{id} regex route so "eligible" is never an id.
if ($path === '/api/v1/certificates/eligible' && $method === 'GET') {
    middleware_auth();
    certificate_controller_eligible();
    return;
}

// GET /api/v1/certificates/pending — Pending certificate requests for the authenticated student.
// Registered before the /{id} regex route so "pending" is never treated as an ID.
if ($path === '/api/v1/certificates/pending' && $method === 'GET') {
    middleware_auth();
    certificate_controller_pending();
    return;
}

// GET /api/v1/certificates/issued — Issued certificates for the authenticated user.
// Registered before the /{id} regex route so "issued" is never treated as an ID.
if ($path === '/api/v1/certificates/issued' && $method === 'GET') {
    middleware_auth();
    certificate_controller_issued();
    return;
}

// GET /api/v1/certificates/revoked — Revoked certificates for the authenticated user.
// Registered before the /{id} regex route so "revoked" is never treated as an ID.
if ($path === '/api/v1/certificates/revoked' && $method === 'GET') {
    middleware_auth();
    certificate_controller_revoked();
    return;
}

// GET /api/v1/certificates/search — Search certificates.
// Registered before the /{id} regex route.
if ($path === '/api/v1/certificates/search' && $method === 'GET') {
    middleware_auth();
    certificate_controller_search();
    return;
}

// GET /api/v1/certificates/statistics — Certificate statistics.
// Registered before the /{id} regex route.
if ($path === '/api/v1/certificates/statistics' && $method === 'GET') {
    middleware_auth();
    certificate_controller_statistics();
    return;
}

// POST /api/v1/certificates — Request a certificate (student/company/admin)
if ($path === '/api/v1/certificates' && $method === 'POST') {
    middleware_auth();
    certificate_controller_request();
    return;
}

// GET /api/v1/certificates/{id}/verify — Verify a certificate
if (preg_match('#^/api/v1/certificates/([0-9]+)/verify$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    certificate_controller_verify((int) $matches[1]);
    return;
}

// POST /api/v1/certificates/{id}/confirm — Approve a pending request
if (preg_match('#^/api/v1/certificates/([0-9]+)/confirm$#', $path, $matches) && $method === 'POST') {
    middleware_auth();
    certificate_controller_confirm((int) $matches[1]);
    return;
}

// POST /api/v1/certificates/{id}/revoke — Revoke an issued certificate
if (preg_match('#^/api/v1/certificates/([0-9]+)/revoke$#', $path, $matches) && $method === 'POST') {
    middleware_auth();
    certificate_controller_revoke((int) $matches[1]);
    return;
}

// GET /api/v1/certificates/{id} — View certificate details
if (preg_match('#^/api/v1/certificates/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    certificate_controller_show((int) $matches[1]);
    return;
}

response_not_found('Certificate endpoint not found.');