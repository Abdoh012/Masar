<?php
define('ROLE_STUDENT', 'student');
define('ROLE_COMPANY', 'company');
define('ROLE_ADMIN', 'admin');
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');
define('USER_STATUS_PENDING', 'pending');
define('COMPANY_STATUS_PENDING', 'pending');
define('COMPANY_STATUS_APPROVED', 'approved');
define('COMPANY_STATUS_REJECTED', 'rejected');
define('COMPANY_STATUS_SUSPENDED', 'suspended');
define('TRAINING_STATUS_DRAFT', 'draft');
define('TRAINING_STATUS_PUBLISHED', 'published');
define('TRAINING_STATUS_CLOSED', 'closed');
define('TRAINING_TYPE_SHADOWING', 'shadowing');
define('TRAINING_TYPE_HANDS_ON', 'hands_on');
define('TRAINING_TYPE_PROJECT_BASED', 'project_based');
define('TRAINING_MODE_ON_SITE', 'on_site');
define('TRAINING_MODE_REMOTE', 'remote');
define('TRAINING_MODE_HYBRID', 'hybrid');
define('PAYMENT_TYPE_FREE', 'free');
define('PAYMENT_TYPE_PAID', 'paid');
define('APPLICATION_STATUS_SUBMITTED', 'submitted');
define('APPLICATION_STATUS_ACCEPTED', 'accepted');
define('APPLICATION_STATUS_REJECTED', 'rejected');
define('APPLICATION_STATUS_WITHDRAWN', 'withdrawn');
define('REJECTION_REASON_POSITION_FILLED', 'position_filled');
define('REJECTION_REASON_NOT_SUITABLE', 'not_suitable');
define('REJECTION_REASON_OTHER', 'other');
define('SESSION_STATUS_TRIAL', 'trial');
define('SESSION_STATUS_CONTINUING', 'continuing');
define('SESSION_STATUS_COMPLETED', 'completed');
define('SESSION_STATUS_STOPPED', 'stopped');
define('SESSION_STATUS_CANCELLED', 'cancelled');
define('CERTIFICATE_STATUS_PENDING', 'pending');
define('CERTIFICATE_STATUS_APPROVED', 'approved');
define('CERTIFICATE_STATUS_REJECTED', 'rejected');
define('CERTIFICATE_STATUS_REVOKED', 'revoked');
define('APPEAL_STATUS_PENDING', 'pending');
define('APPEAL_STATUS_APPROVED', 'approved');
define('APPEAL_STATUS_REJECTED', 'rejected');
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_PAID', 'paid');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');
define('PAYMENT_STATUS_CANCELLED', 'cancelled');
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
define('FILE_TYPE_CV', 'cv');
define('FILE_TYPE_CERTIFICATE', 'certificate');
define('FILE_TYPE_PROFILE_IMAGE', 'profile_image');
define('FILE_TYPE_OTHER', 'other');
define('FILE_EXTENSION_PDF', 'pdf');
define('FILE_EXTENSION_DOC', 'doc');
define('FILE_EXTENSION_DOCX', 'docx');
define('DEFAULT_PAGE', 1);
define('DEFAULT_PER_PAGE', 20);
define('MAX_PER_PAGE', 100);
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
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('MIN_TRIAL_PERIOD_DAYS', 7);
define('CERTIFICATE_CODE_LENGTH', 12);
define('PASSWORD_RESET_TOKEN_EXPIRATION_MINUTES', 60);
define('REFRESH_TOKEN_EXPIRATION_DAYS', 30);
define('MAX_APPLICATION_MESSAGE_LENGTH', 2000);
define('MAX_MESSAGE_LENGTH', 5000);
define('MAX_BIO_LENGTH', 1000);
define('MAX_TRAINING_DESCRIPTION_LENGTH', 5000);

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
];

foreach ($shared_enum_files as $shared_enum_file) {
	if (is_file($shared_enum_file)) {
		require_once $shared_enum_file;
	}
}