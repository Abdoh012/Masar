<?php

/**
 * MASAR - Certificate Admin Controller
 *
 * Administrative controller responsible for certificate management.
 *
 * Controller
 *     ↓
 * CertificateAdminService
 *     ↓
 * CertificateRepository / CertificateAppealRepository
 */

$service_file =
    __DIR__ . '/../services/certificate_admin_service.php';

if (file_exists($service_file)) {
    require_once $service_file;
}

class CertificateAdminController
{
    protected mixed $service = null;

    public function __construct(mixed $service = null)
    {
        $this->service = $service ?? $this->resolveService();
    }

    protected function resolveService(): mixed
    {
        if (class_exists('CertificateAdminService')) {
            return new CertificateAdminService();
        }

        return null;
    }

    public function index(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $filters = $this->extractFilters($request);

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'index')
            ) {
                return $this->success(
                    $this->service->index($filters)
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'list')
            ) {
                return $this->success(
                    $this->service->list($filters)
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'getCertificates')
            ) {
                return $this->success(
                    $this->service->getCertificates($filters)
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to load certificates.'
            );
        }

        return $this->success([]);
    }

    public function list(array $request = []): array
    {
        return $this->index($request);
    }

    public function show(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $certificateId = $this->getCertificateId($request);

        if ($certificateId <= 0) {
            return $this->validationError([
                'certificate_id' =>
                    'A valid certificate_id is required.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'show')
            ) {
                return $this->success(
                    $this->service->show($certificateId)
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'getCertificate')
            ) {
                return $this->success(
                    $this->service->getCertificate($certificateId)
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'find')
            ) {
                return $this->success(
                    $this->service->find($certificateId)
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to load certificate.'
            );
        }

        return $this->error(
            'Certificate service is unavailable.'
        );
    }

    public function issue(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $data = $this->extractCertificateData($request);

        $validation = $this->validateCertificateData(
            $data,
            true
        );

        if (!$validation['valid']) {
            return $this->validationError(
                $validation['errors']
            );
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'issue')
            ) {
                $result =
                    $this->service->issue($data);

                return $this->actionResult(
                    $result,
                    'Certificate issued successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'create')
            ) {
                $result =
                    $this->service->create($data);

                return $this->actionResult(
                    $result,
                    'Certificate issued successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to issue certificate.'
            );
        }

        return $this->error(
            'Certificate issue service is unavailable.'
        );
    }

    public function update(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $certificateId =
            $this->getCertificateId($request);

        if ($certificateId <= 0) {
            return $this->validationError([
                'certificate_id' =>
                    'A valid certificate_id is required.'
            ]);
        }

        $data =
            $this->extractCertificateData($request);

        $validation =
            $this->validateCertificateData(
                $data,
                false
            );

        if (!$validation['valid']) {
            return $this->validationError(
                $validation['errors']
            );
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'update')
            ) {
                $result =
                    $this->service->update(
                        $certificateId,
                        $data
                    );

                return $this->actionResult(
                    $result,
                    'Certificate updated successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'edit')
            ) {
                $result =
                    $this->service->edit(
                        $certificateId,
                        $data
                    );

                return $this->actionResult(
                    $result,
                    'Certificate updated successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to update certificate.'
            );
        }

        return $this->error(
            'Certificate update service is unavailable.'
        );
    }

    public function revoke(array $request = []): array
    {
        return $this->statusAction(
            $request,
            'revoke',
            'revoked'
        );
    }

    public function restore(array $request = []): array
    {
        return $this->statusAction(
            $request,
            'restore',
            'active'
        );
    }

    public function approve(array $request = []): array
    {
        return $this->approvalAction(
            $request,
            'approve'
        );
    }

    public function reject(array $request = []): array
    {
        return $this->approvalAction(
            $request,
            'reject'
        );
    }

    public function pending(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $filters =
            $this->extractFilters($request);

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'pending')
            ) {
                return $this->success(
                    $this->service->pending($filters)
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'getPending')
            ) {
                return $this->success(
                    $this->service->getPending($filters)
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to load pending certificates.'
            );
        }

        return $this->success([]);
    }

    public function search(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $query =
            trim(
                (string) (
                    $request['query']
                    ?? $request['search']
                    ?? ''
                )
            );

        if ($query === '') {
            return $this->validationError([
                'query' =>
                    'Search query is required.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'search')
            ) {
                return $this->success(
                    $this->service->search(
                        $query,
                        $this->extractFilters($request)
                    )
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to search certificates.'
            );
        }

        return $this->success([]);
    }

    public function bulk(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $certificateIds =
            $request['certificate_ids'] ?? [];

        $action =
            trim(
                (string) (
                    $request['action'] ?? ''
                )
            );

        if (
            !is_array($certificateIds) ||
            empty($certificateIds)
        ) {
            return $this->validationError([
                'certificate_ids' =>
                    'At least one certificate_id is required.'
            ]);
        }

        if ($action === '') {
            return $this->validationError([
                'action' =>
                    'Bulk action is required.'
            ]);
        }

        $allowedActions = [
            'approve',
            'reject',
            'revoke',
            'restore',
            'delete'
        ];

        if (
            !in_array(
                $action,
                $allowedActions,
                true
            )
        ) {
            return $this->validationError([
                'action' =>
                    'Unsupported bulk action.'
            ]);
        }

        $certificateIds =
            array_values(
                array_filter(
                    array_map(
                        'intval',
                        $certificateIds
                    ),
                    fn ($id) => $id > 0
                )
            );

        if (empty($certificateIds)) {
            return $this->validationError([
                'certificate_ids' =>
                    'No valid certificate IDs were provided.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'bulk')
            ) {
                $result =
                    $this->service->bulk(
                        $action,
                        $certificateIds
                    );

                return $this->actionResult(
                    $result,
                    'Bulk certificate action completed successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'bulkAction')
            ) {
                $result =
                    $this->service->bulkAction(
                        $action,
                        $certificateIds
                    );

                return $this->actionResult(
                    $result,
                    'Bulk certificate action completed successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to execute bulk certificate action.'
            );
        }

        return $this->error(
            'Bulk certificate action service is unavailable.'
        );
    }

    protected function approvalAction(
        array $request,
        string $action
    ): array {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $certificateId =
            $this->getCertificateId($request);

        if ($certificateId <= 0) {
            return $this->validationError([
                'certificate_id' =>
                    'A valid certificate_id is required.'
            ]);
        }

        $reason =
            trim(
                (string) (
                    $request['reason']
                    ?? $request['note']
                    ?? ''
                )
            );

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, $action)
            ) {
                $result =
                    $this->service->{$action}(
                        $certificateId,
                        $reason
                    );

                return $this->actionResult(
                    $result,
                    'Certificate approval status updated successfully.'
                );
            }

            $method =
                $action === 'approve'
                    ? 'approveCertificate'
                    : 'rejectCertificate';

            if (
                $this->service !== null &&
                method_exists($this->service, $method)
            ) {
                $result =
                    $this->service->{$method}(
                        $certificateId,
                        $reason
                    );

                return $this->actionResult(
                    $result,
                    'Certificate approval status updated successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to update certificate approval.'
            );
        }

        return $this->error(
            'Certificate approval service is unavailable.'
        );
    }

    protected function statusAction(
        array $request,
        string $action,
        string $status
    ): array {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $certificateId =
            $this->getCertificateId($request);

        if ($certificateId <= 0) {
            return $this->validationError([
                'certificate_id' =>
                    'A valid certificate_id is required.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, $action)
            ) {
                $result =
                    $this->service->{$action}(
                        $certificateId
                    );

                return $this->actionResult(
                    $result,
                    'Certificate status updated successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'setStatus')
            ) {
                $result =
                    $this->service->setStatus(
                        $certificateId,
                        $status
                    );

                return $this->actionResult(
                    $result,
                    'Certificate status updated successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'changeStatus')
            ) {
                $result =
                    $this->service->changeStatus(
                        $certificateId,
                        $status
                    );

                return $this->actionResult(
                    $result,
                    'Certificate status updated successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to update certificate status.'
            );
        }

        return $this->error(
            'Certificate status service is unavailable.'
        );
    }

    protected function extractCertificateData(
        array $request
    ): array {
        $source =
            isset($request['data']) &&
            is_array($request['data'])
                ? $request['data']
                : $request;

        $allowed = [
            'user_id',
            'student_id',
            'training_id',
            'certificate_number',
            'certificate_type',
            'title',
            'issue_date',
            'expiry_date',
            'status',
            'verification_code',
            'file_id',
            'notes'
        ];

        $data = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $source)) {
                $data[$field] = $source[$field];
            }
        }

        return $data;
    }

    protected function validateCertificateData(
        array $data,
        bool $creating
    ): array {
        $errors = [];

        if (
            $creating &&
            empty($data['user_id']) &&
            empty($data['student_id'])
        ) {
            $errors['user_id'] =
                'A user_id or student_id is required.';
        }

        if (
            $creating &&
            empty($data['training_id'])
        ) {
            $errors['training_id'] =
                'A training_id is required.';
        }

        if (
            isset($data['user_id']) &&
            $data['user_id'] !== '' &&
            (int) $data['user_id'] <= 0
        ) {
            $errors['user_id'] =
                'A valid user_id is required.';
        }

        if (
            isset($data['student_id']) &&
            $data['student_id'] !== '' &&
            (int) $data['student_id'] <= 0
        ) {
            $errors['student_id'] =
                'A valid student_id is required.';
        }

        if (
            isset($data['training_id']) &&
            $data['training_id'] !== '' &&
            (int) $data['training_id'] <= 0
        ) {
            $errors['training_id'] =
                'A valid training_id is required.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function extractFilters(
        array $request
    ): array {
        $filters =
            $request['filters'] ?? [];

        if (!is_array($filters)) {
            $filters = [];
        }

        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'approval_status',
            'user_id',
            'student_id',
            'training_id',
            'from',
            'to',
            'sort',
            'order'
        ];

        $result = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $filters)) {
                $result[$key] = $filters[$key];
            } elseif (array_key_exists($key, $request)) {
                $result[$key] = $request[$key];
            }
        }

        return $result;
    }

    protected function getCertificateId(
        array $request
    ): int {
        return max(
            0,
            (int) (
                $request['certificate_id']
                ?? $request['id']
                ?? 0
            )
        );
    }

    protected function isAuthorized(
        array $request
    ): bool {
        if (
            $this->service !== null &&
            method_exists(
                $this->service,
                'isAuthorized'
            )
        ) {
            try {
                return (bool)
                    $this->service->isAuthorized(
                        $this->buildContext($request)
                    );
            } catch (Throwable $e) {
                return false;
            }
        }

        if (array_key_exists('is_admin', $request)) {
            return (bool) $request['is_admin'];
        }

        $role =
            strtolower(
                trim(
                    (string) (
                        $request['role'] ?? ''
                    )
                )
            );

        return in_array(
            $role,
            [
                'admin',
                'administrator',
                'super_admin',
                'superadmin'
            ],
            true
        );
    }

    protected function buildContext(
        array $request
    ): array {
        return [
            'user_id' =>
                (int) (
                    $request['user_id'] ?? 0
                ),

            'role' =>
                $request['role'] ?? null,

            'ip' =>
                $request['ip'] ?? null,

            'request_id' =>
                $request['request_id'] ?? null
        ];
    }

    protected function actionResult(
        mixed $result,
        string $message
    ): array {
        if ($result === false) {
            return $this->error(
                'Operation failed.'
            );
        }

        return [
            'success' => true,
            'message' => $message,
            'data' => $result
        ];
    }

    protected function success(
        mixed $data = []
    ): array {
        return [
            'success' => true,
            'data' => $data
        ];
    }

    protected function error(
        string $message,
        array $errors = []
    ): array {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }

    protected function validationError(
        array $errors
    ): array {
        return [
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $errors
        ];
    }

    protected function unauthorized(): array
    {
        return [
            'success' => false,
            'message' => 'Unauthorized.',
            'code' => 403
        ];
    }

    public function getService(): mixed
    {
        return $this->service;
    }

    public function setService(
        mixed $service
    ): self {
        $this->service = $service;

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function certificate_admin_index(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->index($request);
}

function certificate_admin_show(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->show($request);
}

function certificate_admin_issue(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->issue($request);
}

function certificate_admin_update(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->update($request);
}

function certificate_admin_approve(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->approve($request);
}

function certificate_admin_reject(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->reject($request);
}

function certificate_admin_revoke(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->revoke($request);
}

function certificate_admin_restore(
    array $request = []
): array {
    return
        (new CertificateAdminController())
            ->restore($request);
}
