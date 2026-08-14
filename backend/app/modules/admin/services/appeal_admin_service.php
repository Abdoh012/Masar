<?php

/**
 * MASAR - Appeal Admin Service
 *
 * Administrative service for managing certificate appeals.
 *
 * Controller
 *     ↓
 * AppealAdminService
 *     ↓
 * CertificateAppealRepository
 * CertificateRepository
 */

$appeal_repository_file =
    __DIR__ . '/../../certificates/repositories/certificate_appeal_repository.php';

$certificate_repository_file =
    __DIR__ . '/../../certificates/repositories/certificate_repository.php';

if (file_exists($appeal_repository_file)) {
    require_once $appeal_repository_file;
}

if (file_exists($certificate_repository_file)) {
    require_once $certificate_repository_file;
}

class AppealAdminService
{
    protected mixed $repository = null;

    protected mixed $certificateRepository = null;

    public function __construct(
        mixed $repository = null,
        mixed $certificateRepository = null
    ) {
        $this->repository =
            $repository ?? $this->resolveRepository();

        $this->certificateRepository =
            $certificateRepository
            ?? $this->resolveCertificateRepository();
    }

    protected function resolveRepository(): mixed
    {
        if (class_exists('CertificateAppealRepository')) {
            return new CertificateAppealRepository();
        }

        return null;
    }

    protected function resolveCertificateRepository(): mixed
    {
        if (class_exists('CertificateRepository')) {
            return new CertificateRepository();
        }

        return null;
    }

    public function index(
        array $filters = [],
        array $context = []
    ): array {
        return $this->listAppeals(
            $filters,
            $context
        );
    }

    public function listAppeals(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $filters =
            $this->normalizeFilters($filters);

        if ($this->repository === null) {
            return [
                'items' => [],
                'pagination' =>
                    $this->emptyPagination($filters)
            ];
        }

        try {
            $result = null;

            if (
                method_exists(
                    $this->repository,
                    'getAll'
                )
            ) {
                $result =
                    $this->repository->getAll(
                        $filters
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'all'
                )
            ) {
                $result =
                    $this->repository->all(
                        $filters
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'paginate'
                )
            ) {
                $result =
                    $this->repository->paginate(
                        $filters
                    );
            }

            return $this->normalizeListResult(
                $result,
                $filters
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load appeals.'
            );
        }
    }

    public function show(
        int $appealId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $appealId =
            $this->validateId(
                $appealId,
                'appeal ID'
            );

        if ($this->repository === null) {
            return null;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'find'
                )
            ) {
                return $this->repository->find(
                    $appealId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'findById'
                )
            ) {
                return $this->repository->findById(
                    $appealId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'getById'
                )
            ) {
                return $this->repository->getById(
                    $appealId
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load appeal.'
            );
        }

        return null;
    }

    public function search(
        string $query,
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $filters =
            $this->normalizeFilters($filters);

        if ($this->repository === null) {
            return [];
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'search'
                )
            ) {
                return (array)
                    $this->repository->search(
                        $query,
                        $filters
                    );
            }

            if (
                method_exists(
                    $this->repository,
                    'searchAppeals'
                )
            ) {
                return (array)
                    $this->repository->searchAppeals(
                        $query,
                        $filters
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to search appeals.'
            );
        }

        return [];
    }

    public function approve(
        int $appealId,
        string $note = '',
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $appealId,
            'approved',
            $note,
            $context
        );
    }

    public function reject(
        int $appealId,
        string $reason,
        array $context = []
    ): mixed {
        $reason = trim($reason);

        if ($reason === '') {
            throw new InvalidArgumentException(
                'A rejection reason is required.'
            );
        }

        return $this->changeStatus(
            $appealId,
            'rejected',
            $reason,
            $context
        );
    }

    public function review(
        int $appealId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $appealId,
            'under_review',
            '',
            $context
        );
    }

    public function pending(
        int $appealId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $appealId,
            'pending',
            '',
            $context
        );
    }

    public function cancel(
        int $appealId,
        string $reason = '',
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $appealId,
            'cancelled',
            trim($reason),
            $context
        );
    }

    public function changeStatus(
        int $appealId,
        string $status,
        string $note = '',
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $appealId =
            $this->validateId(
                $appealId,
                'appeal ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        $allowedStatuses = [
            'pending',
            'under_review',
            'approved',
            'rejected',
            'cancelled'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported appeal status.'
            );
        }

        if (
            $status === 'rejected' &&
            trim($note) === ''
        ) {
            throw new InvalidArgumentException(
                'A rejection reason is required.'
            );
        }

        if ($this->repository === null) {
            return false;
        }

        try {
            $result = false;

            if (
                method_exists(
                    $this->repository,
                    'changeStatus'
                )
            ) {
                $result =
                    $this->repository->changeStatus(
                        $appealId,
                        $status,
                        $note
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'setStatus'
                )
            ) {
                $result =
                    $this->repository->setStatus(
                        $appealId,
                        $status,
                        $note
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'update'
                )
            ) {
                $data = [
                    'status' => $status
                ];

                if ($note !== '') {
                    $data['admin_note'] = $note;
                }

                $result =
                    $this->repository->update(
                        $appealId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'appeal_status_changed',
                $appealId,
                $context,
                [
                    'status' => $status,
                    'note' => $note
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change appeal status.'
            );
        }
    }

    public function update(
        int $appealId,
        array $data,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $appealId =
            $this->validateId(
                $appealId,
                'appeal ID'
            );

        $data =
            $this->sanitizeAppealData(
                $data
            );

        if (empty($data)) {
            throw new InvalidArgumentException(
                'No valid appeal data was provided.'
            );
        }

        if ($this->repository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'update'
                )
            ) {
                $result =
                    $this->repository->update(
                        $appealId,
                        $data
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'edit'
                )
            ) {
                $result =
                    $this->repository->edit(
                        $appealId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'appeal_updated',
                $appealId,
                $context,
                [
                    'fields' =>
                        array_keys($data)
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update appeal.'
            );
        }
    }

    public function delete(
        int $appealId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $appealId =
            $this->validateId(
                $appealId,
                'appeal ID'
            );

        if ($this->repository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'delete'
                )
            ) {
                $result =
                    $this->repository->delete(
                        $appealId
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'softDelete'
                )
            ) {
                $result =
                    $this->repository->softDelete(
                        $appealId
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'appeal_deleted',
                $appealId,
                $context
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to delete appeal.'
            );
        }
    }

    public function getCertificate(
        int $certificateId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $certificateId =
            $this->validateId(
                $certificateId,
                'certificate ID'
            );

        if ($this->certificateRepository === null) {
            return null;
        }

        try {
            if (
                method_exists(
                    $this->certificateRepository,
                    'find'
                )
            ) {
                return
                    $this->certificateRepository
                        ->find(
                            $certificateId
                        );
            }

            if (
                method_exists(
                    $this->certificateRepository,
                    'findById'
                )
            ) {
                return
                    $this->certificateRepository
                        ->findById(
                            $certificateId
                        );
            }

            if (
                method_exists(
                    $this->certificateRepository,
                    'getById'
                )
            ) {
                return
                    $this->certificateRepository
                        ->getById(
                            $certificateId
                        );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load certificate.'
            );
        }

        return null;
    }

    public function statistics(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $stats = [
            'total' => 0,
            'pending' => 0,
            'under_review' => 0,
            'approved' => 0,
            'rejected' => 0,
            'cancelled' => 0
        ];

        if ($this->repository === null) {
            return $stats;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getStatistics'
                )
            ) {
                return array_merge(
                    $stats,
                    (array)
                    $this->repository
                        ->getStatistics()
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'statistics'
                )
            ) {
                return array_merge(
                    $stats,
                    (array)
                    $this->repository
                        ->statistics()
                );
            }

            foreach (
                [
                    'pending',
                    'under_review',
                    'approved',
                    'rejected',
                    'cancelled'
                ] as $status
            ) {
                $stats[$status] =
                    $this->count(
                        ['status' => $status],
                        $context
                    );
            }

            $stats['total'] =
                $this->count(
                    [],
                    $context
                );

            return $stats;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load appeal statistics.'
            );
        }
    }

    public function count(
        array $filters = [],
        array $context = []
    ): int {
        $this->assertAdmin($context);

        if ($this->repository === null) {
            return 0;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'count'
                )
            ) {
                return max(
                    0,
                    (int)
                    $this->repository->count(
                        $filters
                    )
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'countAppeals'
                )
            ) {
                return max(
                    0,
                    (int)
                    $this->repository
                        ->countAppeals(
                            $filters
                        )
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to count appeals.'
            );
        }

        return 0;
    }

    protected function normalizeFilters(
        array $filters
    ): array {
        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'certificate_id',
            'student_id',
            'training_id',
            'company_id',
            'from',
            'to',
            'sort',
            'order'
        ];

        $result = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $filters)) {
                $result[$key] = $filters[$key];
            }
        }

        $result['page'] =
            max(
                1,
                (int) ($result['page'] ?? 1)
            );

        $result['limit'] =
            min(
                100,
                max(
                    1,
                    (int) ($result['limit'] ?? 20)
                )
            );

        return $result;
    }

    protected function sanitizeAppealData(
        array $data
    ): array {
        $allowed = [
            'certificate_id',
            'student_id',
            'subject',
            'message',
            'reason',
            'status',
            'admin_note',
            'reviewed_by',
            'reviewed_at'
        ];

        $result = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $result[$field] = $data[$field];
            }
        }

        return $result;
    }

    protected function normalizeListResult(
        mixed $result,
        array $filters
    ): array {
        if (is_array($result)) {
            if (
                isset($result['items']) ||
                isset($result['data'])
            ) {
                $items =
                    $result['items']
                    ?? $result['data']
                    ?? [];

                return [
                    'items' =>
                        is_array($items)
                            ? $items
                            : [],
                    'pagination' =>
                        $result['pagination']
                        ?? $this->emptyPagination(
                            $filters
                        )
                ];
            }

            return [
                'items' => $result,
                'pagination' =>
                    $this->emptyPagination(
                        $filters
                    )
            ];
        }

        return [
            'items' => [],
            'pagination' =>
                $this->emptyPagination(
                    $filters
                )
        ];
    }

    protected function emptyPagination(
        array $filters
    ): array {
        $page =
            max(
                1,
                (int) ($filters['page'] ?? 1)
            );

        $limit =
            min(
                100,
                max(
                    1,
                    (int) ($filters['limit'] ?? 20)
                )
            );

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => 0,
            'pages' => 0
        ];
    }

    protected function validateId(
        int $id,
        string $label
    ): int {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                "A valid {$label} is required."
            );
        }

        return $id;
    }

    public function isAuthorized(
        array $context = []
    ): bool {
        if (!empty($context['is_admin'])) {
            return true;
        }

        $role =
            strtolower(
                trim(
                    (string)
                    ($context['role'] ?? '')
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

    protected function assertAdmin(
        array $context
    ): void {
        if (!$this->isAuthorized($context)) {
            throw new RuntimeException(
                'Unauthorized administrative access.'
            );
        }
    }

    protected function logAdminAction(
        string $action,
        ?int $targetId,
        array $context,
        array $metadata = []
    ): void {
        if ($this->repository === null) {
            return;
        }

        $payload = [
            'action' => $action,
            'target_id' => $targetId,
            'admin_id' =>
                (int)
                (
                    $context['admin_id']
                    ?? $context['user_id']
                    ?? 0
                ),
            'metadata' => $metadata,
            'created_at' =>
                date('Y-m-d H:i:s')
        ];

        try {
            if (
                method_exists(
                    $this->repository,
                    'logAdminAction'
                )
            ) {
                $this->repository
                    ->logAdminAction($payload);
            } elseif (
                method_exists(
                    $this->repository,
                    'logActivity'
                )
            ) {
                $this->repository
                    ->logActivity($payload);
            }
        } catch (Throwable $e) {
            // Logging must never break the main operation.
        }
    }

    public function getRepository(): mixed
    {
        return $this->repository;
    }

    public function setRepository(
        mixed $repository
    ): self {
        $this->repository = $repository;

        return $this;
    }

    public function getCertificateRepository(): mixed
    {
        return $this->certificateRepository;
    }

    public function setCertificateRepository(
        mixed $repository
    ): self {
        $this->certificateRepository = $repository;

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function appeal_admin_list(
    array $filters = [],
    array $context = []
): array {
    return
        (new AppealAdminService())
            ->listAppeals(
                $filters,
                $context
            );
}

function appeal_admin_show(
    int $appealId,
    array $context = []
): mixed {
    return
        (new AppealAdminService())
            ->show(
                $appealId,
                $context
            );
}

function appeal_admin_approve(
    int $appealId,
    string $note = '',
    array $context = []
): mixed {
    return
        (new AppealAdminService())
            ->approve(
                $appealId,
                $note,
                $context
            );
}

function appeal_admin_reject(
    int $appealId,
    string $reason,
    array $context = []
): mixed {
    return
        (new AppealAdminService())
            ->reject(
                $appealId,
                $reason,
                $context
            );
}

function appeal_admin_review(
    int $appealId,
    array $context = []
): mixed {
    return
        (new AppealAdminService())
            ->review(
                $appealId,
                $context
            );
}

function appeal_admin_pending(
    int $appealId,
    array $context = []
): mixed {
    return
        (new AppealAdminService())
            ->pending(
                $appealId,
                $context
            );
}
