<?php

/**
 * MASAR - Central Application Constants
 *
 * Single source of truth for fixed application-wide values: roles, statuses,
 * enumerations, limits, codes and date formats.
 *
 * Environment-dependent values (debug, secrets, token lifetimes, URLs) and
 * infrastructure-specific settings (database, mail, uploads) live in
 * `app/config/app.php` and the dedicated configuration files instead of here.
 */

/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/

define('ROLE_STUDENT', 'student');
define('ROLE_COMPANY', 'company');
define('ROLE_ADMIN', 'admin');

/*
|--------------------------------------------------------------------------
| User and Company Statuses
|--------------------------------------------------------------------------
*/

define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');
define('USER_STATUS_PENDING', 'pending');
define('USER_STATUS_BLOCKED', 'blocked');
define('USER_STATUS_REJECTED', 'rejected');
define('USER_STATUS_DELETED', 'deleted');
define('COMPANY_STATUS_PENDING', 'pending');
define('COMPANY_STATUS_APPROVED', 'approved');
define('COMPANY_STATUS_REJECTED', 'rejected');
define('COMPANY_STATUS_SUSPENDED', 'suspended');

/*
|--------------------------------------------------------------------------
| Training Statuses, Types and Modes
|--------------------------------------------------------------------------
|
| TRAINING_TYPE_* is the listing-level type enum
| (shadowing / hands_on / project_based). TRAINING_MODE_* is a separate
| listing-level mode enum (on_site / remote / hybrid). The two vocabularies
| are not interchangeable.

*/

define('TRAINING_STATUS_DRAFT', 'draft');
define('TRAINING_STATUS_PUBLISHED', 'published');
define('TRAINING_STATUS_CLOSED', 'closed');
define('TRAINING_TYPE_SHADOWING', 'shadowing');
define('TRAINING_TYPE_HANDS_ON', 'hands_on');
define('TRAINING_TYPE_PROJECT_BASED', 'project_based');
define('TRAINING_MODE_ON_SITE', 'on_site');
define('TRAINING_MODE_REMOTE', 'remote');
define('TRAINING_MODE_HYBRID', 'hybrid');

/*
|--------------------------------------------------------------------------
| Training Sessions
|--------------------------------------------------------------------------
*/

define('SESSION_STATUS_TRIAL', 'trial');
define('SESSION_STATUS_CONTINUING', 'continuing');
define('SESSION_STATUS_COMPLETED', 'completed');
define('SESSION_STATUS_STOPPED', 'stopped');
define('SESSION_STATUS_CANCELLED', 'cancelled');

/*
|--------------------------------------------------------------------------
| Applications and Rejection Reasons
|--------------------------------------------------------------------------
*/

define('APPLICATION_STATUS_SUBMITTED', 'submitted');
define('APPLICATION_STATUS_ACCEPTED', 'accepted');
define('APPLICATION_STATUS_REJECTED', 'rejected');
define('APPLICATION_STATUS_WITHDRAWN', 'withdrawn');
define('REJECTION_REASON_POSITION_FILLED', 'position_filled');
define('REJECTION_REASON_NOT_SUITABLE', 'not_suitable');
define('REJECTION_REASON_OTHER', 'other');

/*
|--------------------------------------------------------------------------
| Payments, Certificates and Appeals
|--------------------------------------------------------------------------
*/

define('PAYMENT_TYPE_FREE', 'free');
define('PAYMENT_TYPE_PAID', 'paid');
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_PAID', 'paid');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');
define('PAYMENT_STATUS_CANCELLED', 'cancelled');
define('CERTIFICATE_STATUS_PENDING', 'pending');
define('CERTIFICATE_STATUS_APPROVED', 'approved');
define('CERTIFICATE_STATUS_REJECTED', 'rejected');
define('CERTIFICATE_STATUS_REVOKED', 'revoked');
define('APPEAL_STATUS_PENDING', 'pending');
define('APPEAL_STATUS_APPROVED', 'approved');
define('APPEAL_STATUS_REJECTED', 'rejected');

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

define('NOTIFICATION_APPLICATION_ACCEPTED', 'application_accepted');
define('NOTIFICATION_APPLICATION_REJECTED', 'application_rejected');
define('NOTIFICATION_APPLICATION_WITHDRAWN', 'application_withdrawn');
define('NOTIFICATION_TRAINING_CLOSED', 'training_closed');
define('NOTIFICATION_TRAINING_UPDATED', 'training_updated');
define('NOTIFICATION_NEW_MESSAGE', 'new_message');
define('NOTIFICATION_CERTIFICATE_REQUESTED', 'certificate_requested');
define('NOTIFICATION_CERTIFICATE_APPROVED', 'certificate_approved');
define('NOTIFICATION_CERTIFICATE_REJECTED', 'certificate_rejected');
define('NOTIFICATION_CERTIFICATE_REVOKED', 'certificate_revoked');
define('NOTIFICATION_CERTIFICATE_APPEAL', 'certificate_appeal');
define('NOTIFICATION_TRIAL_EXPIRING', 'trial_expiring');

/*
|--------------------------------------------------------------------------
| Files
|--------------------------------------------------------------------------
*/

define('FILE_TYPE_CV', 'cv');
define('FILE_TYPE_CERTIFICATE', 'certificate');
define('FILE_TYPE_PROFILE_IMAGE', 'profile_image');
define('FILE_TYPE_OTHER', 'other');

/*
 * A student's OWN academic certificate document.
 *
 * NOTE: this is the literal member of the files.type enum
 * ('cv','profile_image','certificate_attachment','other'). FILE_TYPE_CERTIFICATE
 * above is 'certificate', which is NOT a member of that enum, so it must not
 * be used for a files row. 'certificate_attachment' is reused deliberately:
 * it is the existing enum member for "a certificate document belonging to a
 * user" and it is otherwise unused, which avoids widening the enum. It is
 * unrelated to the MASAR-issued `certificates` table, which requires a
 * training and a completed session.
 */
define('FILE_TYPE_ACADEMIC_CERTIFICATE', 'certificate_attachment');

/*
 * Lifetime of a signed profile-image URL, in seconds. The URL is only used by
 * browser <img> tags, which cannot send a Bearer header; every other file
 * access still goes through the normal Bearer + ownership path.
 */
define('FILE_SIGNED_URL_TTL', 60 * 60);
define('FILE_EXTENSION_PDF', 'pdf');
define('FILE_EXTENSION_DOC', 'doc');
define('FILE_EXTENSION_DOCX', 'docx');

/*
|--------------------------------------------------------------------------
| Authentication Tokens and Security Limits
|--------------------------------------------------------------------------
*/

define('PASSWORD_RESET_TOKEN_EXPIRATION_MINUTES', 60);
define('REFRESH_TOKEN_EXPIRATION_DAYS', 30);
define('MIN_TRIAL_PERIOD_DAYS', 7);
define('CERTIFICATE_CODE_LENGTH', 12);

/*
|--------------------------------------------------------------------------
| Validation and Business Limits
|--------------------------------------------------------------------------
*/

define('USERNAME_MIN_LENGTH', 3);
define('USERNAME_MAX_LENGTH', 30);
define('USERNAME_PATTERN', '/^[A-Za-z][A-Za-z0-9_.]{2,29}$/');

define('ACADEMIC_CERTIFICATE_MAX_SIZE', 5 * 1024 * 1024);
define('ACADEMIC_CERTIFICATE_EXTENSIONS', ['pdf', 'png', 'jpg', 'jpeg']);
define('ACADEMIC_CERTIFICATE_MIMES', [
    'application/pdf',
    'image/png',
    'image/jpeg',
]);

define('PROFILE_IMAGE_MAX_SIZE', 5 * 1024 * 1024);
define('PROFILE_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('PROFILE_IMAGE_MIMES', [
    'image/jpeg',
    'image/png',
    'image/webp',
]);

define('PROFILE_VIEW_RECENT_LIMIT', 6);
define('STUDENT_MAX_SKILLS', 50);
define('STUDENT_MAX_EDUCATIONS', 20);
define('MAX_APPLICATION_MESSAGE_LENGTH', 2000);
define('MAX_MESSAGE_LENGTH', 5000);
define('MAX_BIO_LENGTH', 1000);
define('MAX_TRAINING_DESCRIPTION_LENGTH', 5000);

/*
|--------------------------------------------------------------------------
| Legacy Pagination Constants
|--------------------------------------------------------------------------
|
| Deprecated aliases kept for backward compatibility. The authoritative
| pagination source is the central application configuration
| (`app/config/app.php` -> `pagination`).

*/

define('DEFAULT_PAGE', 1);
define('DEFAULT_PER_PAGE', 20);
define('MAX_PER_PAGE', 100);

/*
|--------------------------------------------------------------------------
| HTTP Status Codes and Error Codes
|--------------------------------------------------------------------------
*/

define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_TOO_MANY_REQUESTS', 429);
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('ERROR_VALIDATION', 'VALIDATION_ERROR');
define('ERROR_UNAUTHORIZED', 'UNAUTHORIZED');
define('ERROR_FORBIDDEN', 'FORBIDDEN');
define('ERROR_NOT_FOUND', 'NOT_FOUND');
define('ERROR_ALREADY_EXISTS', 'ALREADY_EXISTS');
define('ERROR_INVALID_REQUEST', 'INVALID_REQUEST');
define('ERROR_SERVER', 'SERVER_ERROR');

/*
|--------------------------------------------------------------------------
| Date and Time Formats
|--------------------------------------------------------------------------
*/

define('TIME_FORMAT', 'H:i:s');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

/*
 * Load the canonical shared enums after legacy constants are defined.
 * Enum constants are guarded with defined() checks, so existing API names
 * remain backward compatible while their validation helpers become global.
 */

$shared_enum_files = [
    __DIR__ . '/../shared/enums/user_roles.php',
    __DIR__ . '/../shared/enums/user_statuses.php',
    __DIR__ . '/../shared/enums/company_statuses.php',
    __DIR__ . '/../shared/enums/training_statuses.php',
    __DIR__ . '/../shared/enums/training_types.php',
    __DIR__ . '/../shared/enums/training_modes.php',
    __DIR__ . '/../shared/enums/application_statuses.php',
    __DIR__ . '/../shared/enums/appeal_statuses.php',
    __DIR__ . '/../shared/enums/certificate_statuses.php',
    __DIR__ . '/../shared/enums/payment_statuses.php',
    __DIR__ . '/../shared/enums/payment_types.php',
    __DIR__ . '/../shared/enums/notification_types.php',
    __DIR__ . '/../shared/enums/rejection_reasons.php',
    __DIR__ . '/../shared/enums/training_session_statuses.php',
    __DIR__ . '/../shared/enums/academic_statuses.php',
];

foreach ($shared_enum_files as $shared_enum_file) {
    if (is_file($shared_enum_file)) {
        require_once $shared_enum_file;
    }
}
