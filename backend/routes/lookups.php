<?php

/**
 * MASAR - Lookup Routes
 *
 * Public (unauthenticated) lookup endpoints used by registration:
 * study fields and specializations. Specializations are shared between
 * students (Specialization) and companies (Industry).
 */

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/modules/lookups/controllers/lookup_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/lookups/study-fields' && $method === 'GET') {
    lookup_controller_study_fields();
    return;
}

if ($path === '/api/v1/lookups/specializations' && $method === 'GET') {
    lookup_controller_specializations();
    return;
}

if (preg_match('#^/api/v1/lookups/study-fields/(\d+)/specializations$#', $path, $matches) && $method === 'GET') {
    lookup_controller_specializations_by_field((int) $matches[1]);
    return;
}

response_not_found('Lookup endpoint not found.');
