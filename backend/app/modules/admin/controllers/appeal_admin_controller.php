<?php

/**
 * MASAR - Appeal Admin Controller
 *
 * Administrative controller responsible for certificate appeal management.
 *
 * Controller
 *     ↓
 * AppealAdminService
 *     ↓
 * CertificateAppealRepository
 */

$service_file =
    __DIR__ . '/../services/appeal_admin_service.php';

if (file_exists($service_file)) {
    require_once $service_file;
}

class AppealAdminController
{
    protected mixed $service = null;

    public function __construct(mixed $service = null)
    {
        $this->service = $service ?? $this->resolveService();
    }

    protected function resolveService(): mixed
    {
        if (class_exists('AppealAdminService')) {
            return new AppealAdminService();
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
                method_exists($this->service, 'getAppeals')
            ) {
                return $this->success(
                    $this->service->getAppeals($filters)
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to load appeals.'
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

        $appealId = $this->getAppealId($request);

        if ($appealId <= 0) {
            return $this->validationError([
                'appeal_id' =>
                    'A valid appeal_id is required.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'show')
            ) {
                return $this->success(
                    $this->service->show($appealId)
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'getAppeal')
            ) {
                return $this->success(
                    $this->service->getAppeal($appealId)
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'find')
            ) {
                return $this->success(
                    $this->service->find($appealId)
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to load appeal.'
            );
        }

        return $this->error(
            'Appeal service is unavailable.'
        );
    }

    public function pending(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $filters = $this->extractFilters($request);

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

            if (
                $this->service !== null &&
                method_exists($this->service, 'getPendingAppeals')
            ) {
                return $this->success(
                    $this->service->getPendingAppeals($filters)
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to load pending appeals.'
            );
        }

        return $this->success([]);
    }

    public function approve(array $request = []): array
    {
        return $this->decisionAction(
            $request,
            'approve'
        );
    }

    public function reject(array $request = []): array
    {
        return $this->decisionAction(
            $request,
            'reject'
        );
    }

    public function review(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $appealId = $this->getAppealId($request);

        if ($appealId <= 0) {
            return $this->validationError([
                'appeal_id' =>
                    'A valid appeal_id is required.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'review')
            ) {
                $result =
                    $this->service->review(
                        $appealId,
                        $this->getAdminContext($request)
                    );

                return $this->actionResult(
                    $result,
                    'Appeal moved to review successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'startReview')
            ) {
                $result =
                    $this->service->startReview(
                        $appealId,
                        $this->getAdminContext($request)
                    );

                return $this->actionResult(
                    $result,
                    'Appeal moved to review successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to review appeal.'
            );
        }

        return $this->error(
            'Appeal review service is unavailable.'
        );
    }

    public function update(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $appealId = $this->getAppealId($request);

        if ($appealId <= 0) {
            return $this->validationError([
                'appeal_id' =>
                    'A valid appeal_id is required.'
            ]);
        }

        $data = $this->extractAppealData($request);

        if (empty($data)) {
            return $this->validationError([
                'data' =>
                    'No appeal data was provided.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'update')
            ) {
                $result =
                    $this->service->update(
                        $appealId,
                        $data
                    );

                return $this->actionResult(
                    $result,
                    'Appeal updated successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'edit')
            ) {
                $result =
                    $this->service->edit(
                        $appealId,
                        $data
                    );

                return $this->actionResult(
                    $result,
                    'Appeal updated successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to update appeal.'
            );
        }

        return $this->error(
            'Appeal update service is unavailable.'
        );
    }

    public function changeStatus(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $appealId = $this->getAppealId($request);

        $status =
            strtolower(
                trim(
                    (string) (
                        $request['status'] ?? ''
                    )
                )
            );

        if ($appealId <= 0) {
            return $this->validationError([
                'appeal_id' =>
                    'A valid appeal_id is required.'
            ]);
        }

        if ($status === '') {
            return $this->validationError([
                'status' =>
                    'Status is required.'
            ]);
        }

        $allowedStatuses = [
            'pending',
            'under_review',
            'approved',
            'rejected',
            'closed'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            return $this->validationError([
                'status' =>
                    'Unsupported appeal status.'
            ]);
        }

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, 'changeStatus')
            ) {
                $result =
                    $this->service->changeStatus(
                        $appealId,
                        $status,
                        $this->getAdminContext($request)
                    );

                return $this->actionResult(
                    $result,
                    'Appeal status updated successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'setStatus')
            ) {
                $result =
                    $this->service->setStatus(
                        $appealId,
                        $status,
                        $this->getAdminContext($request)
                    );

                return $this->actionResult(
                    $result,
                    'Appeal status updated successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to change appeal status.'
            );
        }

        return $this->error(
            'Appeal status service is unavailable.'
        );
    }

    public function bulk(array $request = []): array
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $appealIds =
            $request['appeal_ids'] ?? [];

        $action =
            strtolower(
                trim(
                    (string) (
                        $request['action'] ?? ''
                    )
                )
            );

        if (
            !is_array($appealIds) ||
            empty($appealIds)
        ) {
            return $this->validationError([
                'appeal_ids' =>
                    'At least one appeal_id is required.'
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
            'review',
            'close'
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

        $appealIds =
            array_values(
                array_filter(
                    array_map(
                        'intval',
                        $appealIds
                    ),
                    fn ($id) => $id > 0
                )
            );

        if (empty($appealIds)) {
            return $this->validationError([
                'appeal_ids' =>
                    'No valid appeal IDs were provided.'
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
                        $appealIds,
                        $this->getAdminContext($request)
                    );

                return $this->actionResult(
                    $result,
                    'Bulk appeal action completed successfully.'
                );
            }

            if (
                $this->service !== null &&
                method_exists($this->service, 'bulkAction')
            ) {
                $result =
                    $this->service->bulkAction(
                        $action,
                        $appealIds,
                        $this->getAdminContext($request)
                    );

                return $this->actionResult(
                    $result,
                    'Bulk appeal action completed successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to execute bulk appeal action.'
            );
        }

        return $this->error(
            'Bulk appeal action service is unavailable.'
        );
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
                'Unable to search appeals.'
            );
        }

        return $this->success([]);
    }

    protected function decisionAction(
        array $request,
        string $action
    ): array {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $appealId = $this->getAppealId($request);

        if ($appealId <= 0) {
            return $this->validationError([
                'appeal_id' =>
                    'A valid appeal_id is required.'
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

        if (
            $action === 'reject' &&
            $reason === ''
        ) {
            return $this->validationError([
                'reason' =>
                    'A rejection reason is required.'
            ]);
        }

        $context =
            $this->getAdminContext($request);

        try {
            if (
                $this->service !== null &&
                method_exists($this->service, $action)
            ) {
                $result =
                    $this->service->{$action}(
                        $appealId,
                        $reason,
                        $context
                    );

                return $this->actionResult(
                    $result,
                    $action === 'approve'
                        ? 'Appeal approved successfully.'
                        : 'Appeal rejected successfully.'
                );
            }

            $method =
                $action === 'approve'
                    ? 'approveAppeal'
                    : 'rejectAppeal';

            if (
                $this->service !== null &&
                method_exists($this->service, $method)
            ) {
                $result =
                    $this->service->{$method}(
                        $appealId,
                        $reason,
                        $context
                    );

                return $this->actionResult(
                    $result,
                    $action === 'approve'
                        ? 'Appeal approved successfully.'
                        : 'Appeal rejected successfully.'
                );
            }
        } catch (Throwable $e) {
            return $this->error(
                'Unable to process appeal decision.'
            );
        }

        return $this->error(
            'Appeal decision service is unavailable.'
        );
    }

    protected function extractAppealData(
        array $request
    ): array {
        $source =
            isset($request['data']) &&
            is_array($request['data'])
                ? $request['data']
                : $request;

        $allowed = [
            'certificate_id',
            'user_id',
            'student_id',
            'reason',
            'description',
            'status',
            'admin_note',
            'resolution',
            'reviewed_at'
        ];

        $data = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $source)) {
                $data[$field] = $source[$field];
            }
        }

        return $data;
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
            'certificate_id',
            'user_id',
            'student_id',
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

    protected function getAppealId(
        array $request
    ): int {
        return max(
            0,
            (int) (
                $request['appeal_id']
                ?? $request['id']
                ?? 0
            )
        );
    }

    protected function getAdminContext(
        array $request
    ): array {
        return [
            'admin_id' =>
                (int) (
                    $request['admin_id']
                    ?? $request['user_id']
                    ?? 0
                ),

            'role' =>
                $request['role'] ?? null,

            'ip' =>
                $request['ip'] ?? null,

            'request_id' =>
                $request['request_id'] ?? null
        ];
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
                        $this->getAdminContext($request)
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

function appeal_admin_index(
    array $request = []
): array {
    return
        (new AppealAdminController())
            ->index($request);
}

function appeal_admin_show(
    array $request = []
): array {
    return
        (new AppealAdminController())
            ->show($request);
}

function appeal_admin_pending(
    array $request = []
): array {
    return
        (new AppealAdminController())
            ->pending($request);
}

function appeal_admin_approve(
    array $request = []
): array {
    return
        (new AppealAdminController())
            ->approve($request);
}

function appeal_admin_reject(
    array $request = []
): array {
    return
        (new AppealAdminController())
            ->reject($request);
}

function appeal_admin_review(
    array $request = []
): array {
    return
        (new AppealAdminController())
            ->review($request);
}

function appeal_admin_update(
    array $request = []
): array {
    return
        (new AppealAdminController())
            ->update($request);
}
