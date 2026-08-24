<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/student.php';
require_once __DIR__ . '/../app/core/middleware/company.php';
require_once __DIR__ . '/../app/modules/training/controllers/application_controller.php';
require_once __DIR__ . '/../app/modules/files/services/file_upload_service.php';

/*
 * ==========================================================================
 * Application API Routes
 * ==========================================================================
 *
 * POST /api/v1/applications
 * — Apply for a training
 *
 * GET /api/v1/applications/my
 * — Get my applications
 *
 * GET /api/v1/applications/{id}
 * — Get application details
 *
 * GET /api/v1/applications/{id}/cv
 * — Download application CV
 *
 * GET /api/v1/applications
 * — Get company applications
 *
 * POST /api/v1/applications/withdraw
 * — Withdraw application
 *
 * POST /api/v1/applications/accept
 * — Accept application
 *
 * POST /api/v1/applications/reject
 * — Reject application
 */

$path = request_path();
$method = request_method();

// POST /api/v1/applications — Apply for a training
if ($path === '/api/v1/applications' && $method === 'POST') {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $is_multipart = str_contains(strtolower($content_type), 'multipart/form-data');

    if ($is_multipart) {
        // === Multipart/form-data: application data + CV file in one request ===

        // Authenticate student
        $user = auth_user();
        if (!$user) {
            response_unauthorized(
                'Authentication is required.'
            );
            return;
        }

        if (
            !isset($user['role'])
            ||
            !is_student_role($user['role'] ?? null)
        ) {
            response_forbidden(
                'Only students can apply for training opportunities.'
            );
            return;
        }

        // Resolve application fields from POST data (multipart form-data)
        $full_name = request_input('full_name') ?? '';
        $email = request_input('email') ?? '';
        $phone = request_input('phone') ?? '';
        $address = request_input('address') ?? '';
        $city = request_input('city') ?? '';
        $training_id = (int) (request_input('training_id') ?? 0);
        $university_id = request_input('university_id') ?? null;
        $academic_year = request_input('academic_year') ?? '';
        $applicant_type = request_input('applicant_type') ?? 'student';
        $message = request_input('message') ?? '';
        $why_interested = request_input('why_interested') ?? '';
        $what_to_learn = request_input('what_to_learn') ?? '';
        $skills = request_input('skills') ?? [];

        // Resolve uploaded CV file
        $cv_file = null;
        if (isset($_FILES['cv']) && is_array($_FILES['cv'])) {
            $cv_file = $_FILES['cv'];
        }

        // Upload CV using existing file upload logic
        $uploaded_file_id = 0;
        $cv_file_record = null;

        if ($cv_file !== null) {
            $upload_options = [
                'user_id' => $user['id'],
                'type' => 'cv',
                'category' => 'cv',
                'visibility' => 'private',
            ];
            $upload_result = file_upload_service_upload($cv_file, $upload_options);
            if (is_array($upload_result)) {
                $uploaded_file_id = (int)($upload_result['id'] ?? 0);
                $cv_file_record = $upload_result;
            } else {
                response_error(
                    'Unable to upload CV file.',
                    422
                );
                return;
            }
        }

        // Prepare application data with cv_file_id from uploaded CV
        $application_data = [
            'training_id' => $training_id,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'university_id' => $university_id,
            'academic_year' => $academic_year,
            'applicant_type' => $applicant_type,
            'message' => $message,
            'why_interested' => $why_interested,
            'what_to_learn' => $what_to_learn,
            'skills' => $skills,
            'cv_file_id' => $uploaded_file_id > 0 ? $uploaded_file_id : 0,
        ];

        // Create application via service
        $result = application_service_create(
            (int) $user['id'],
            (int) $training_id,
            $application_data
        );

        // If application creation failed after CV was uploaded, clean up the file
        if (
            !$result['success']
            && $uploaded_file_id > 0
            && $cv_file_record !== null
        ) {
            @unlink($cv_file_record['path'] ?? '');
            $relative_path = file_upload_service_relative_path($cv_file_record['path'] ?? '');
            if ($relative_path) {
                @unlink($relative_path);
            }
        }

        // Response is handled by the service's return value pattern;
        // the controller will process $result and send appropriate HTTP response.
        // But since we're at the top level of this included file, we need to
        // explicitly send the response. Let's check the result and respond.
        if ($result['success']) {
            response_created(
                $result['data'] ?? null,
                $result['message'] ?? 'Application submitted successfully.'
            );
        } else {
            response_error(
                $result['message'] ?? 'Unable to submit application.',
                $result['status_code'] ?? 400,
                $result['errors'] ?? []
            );
        }

        return; // Stop processing this route
    }

    // === Backward compatibility: raw JSON with cv_file_id ===
    // Call the original controller function
    application_controller_create();
    return;
}

// GET /api/v1/applications/my — Get my applications
if ($path === '/api/v1/applications/my' && $method === 'GET') {
    middleware_student();
    application_controller_my_applications();
    return;
}

/*
 * Download the CV of an application. Authorized by the application service
 * (owning student, owning company via Application → Training → Company, or
 * an administrator).
 */
// GET /api/v1/applications/{id}/cv — Download application CV
if (preg_match('#^/api/v1/applications/([0-9]+)/cv$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    $result = application_controller_cv((int) $matches[1]);
    if (!empty($result['download']) && !empty($result['path']) && is_file($result['path'])) {
        header('Content-Type: ' . ($result['mime_type'] ?? 'application/octet-stream'));
        header('Content-Length: ' . (int) ($result['size'] ?? filesize($result['path'])));
        header('Content-Disposition: attachment; filename="' . addslashes($result['filename'] ?? basename($result['path'])) . '"');
        readfile($result['path']);
        exit;
    }
    response_error('Unable to download CV.', 400);
    return;
}

/*
 * Application detail is available to the owning student, the owning company
 * (via the training) and administrators. The controller/service enforce the
 * role-based access, so only plain authentication is applied here.
 */
// GET /api/v1/applications/{id} — Get application details
if (preg_match('#^/api/v1/applications/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    application_controller_show((int) $matches[1]);
    return;
}

// GET /api/v1/applications — Get company applications
if ($path === '/api/v1/applications' && $method === 'GET') {
    middleware_company();
    application_controller_company_applications();
    return;
}

// POST /api/v1/applications/withdraw — Withdraw application
if ($path === '/api/v1/applications/withdraw' && $method === 'POST') {
    middleware_student();
    application_controller_withdraw();
    return;
}

// POST /api/v1/applications/accept — Accept application
if ($path === '/api/v1/applications/accept' && $method === 'POST') {
    middleware_company();
    application_controller_accept();
    return;
}

// POST /api/v1/applications/reject — Reject application
if ($path === '/api/v1/applications/reject' && $method === 'POST') {
    middleware_company();
    application_controller_reject();
    return;
}

response_not_found('Application endpoint not found.');