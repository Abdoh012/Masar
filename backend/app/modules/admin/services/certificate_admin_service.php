<?php

/**
 * MASAR - Certificate Admin Service
 *
 * Administrative operations for certificates and certificate appeals.
 *
 * Controller
 *     ↓
 * CertificateAdminService
 *     ↓
 * CertificateRepository
 * CertificateAppealRepository
 */

$certificate_repository_file =
    __DIR__ . '/../../certificates/repositories/certificate_repository.php';

$certificate_appeal_repository_file =
    __DIR__ . '/../../certificates/repositories/certificate_appeal_repository.php';

if (file_exists($certificate_repository_file)) {
    require_once $certificate_repository_file;
}

if (file_exists($certificate_appeal_repository_file)) {
    require_once $certificate_appeal_repository_file;
}

class CertificateAdminService
{
    protected mixed $repository = null;

    protected mixed $appealRepository = null;

    public function __construct(
        mixed $repository = null,
        mixed $appealRepository = null
    ) {
        $this->repository =
            $repository ?? $this->resolveRepository();

        $this->appealRepository =
            $appealRepository
            ?? $this->resolveAppealRepository();
    }

    protected function resolveRepository(): mixed
    {
        if (class_exists('CertificateRepository')) {
            return new CertificateRepository();
        }

        return null;
    }

    protected function resolveAppealRepository(): mixed
    {
        if (class_exists('CertificateAppealRepository')) {
            return new CertificateAppealRepository();
        }

        return null;
    }

    public function index(
        array $filters = [],
        array $context = []
    ): array {
        return $this->listCertificates(
            $filters,
            $context
        );
    }

    public function listCertificates(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $filters =
            $this->normalizeCertificateFilters(
                $filters
            );

        if ($this->repository === null) {
            return [
                'items' => [],
                'pagination' =>
                    $this->emptyPagination(
                        $filters
                    )
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
                'Unable to load certificates.'
            );
        }
    }

    public function show(
        int $certificateId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $certificateId =
            $this->validateId(
                $certificateId,
                'certificate ID'
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
                    $certificateId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'findById'
                )
            ) {
                return $this->repository->findById(
                    $certificateId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'getById'
                )
            ) {
                return $this->repository->getById(
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
            $this->normalizeCertificateFilters(
                $filters
            );

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
                    'searchCertificates'
                )
            ) {
                return (array)
                    $this->repository->searchCertificates(
                        $query,
                        $filters
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to search certificates.'
            );
        }

        return [];
    }

    public function issue(
        int $certificateId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $certificateId,
            'issued',
            $context
        );
    }

    public function revoke(
        int $certificateId,
        string $reason = '',
        array $context = []
    ): mixed {
        if (trim($reason) === '') {
            throw new InvalidArgumentException(
                'A revocation reason is required.'
            );
        }

        return $this->changeStatus(
            $certificateId,
            'revoked',
            $context,
            $reason
        );
    }

    public function suspend(
        int $certificateId,
        string $reason = '',
        array $context = []
    ): mixed {
        if (trim($reason) === '') {
            throw new InvalidArgumentException(
                'A suspension reason is required.'
            );
        }

        return $this->changeStatus(
            $certificateId,
            'suspended',
            $context,
            $reason
        );
    }

    public function restore(
        int $certificateId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $certificateId,
            'issued',
            $context
        );
    }

    public function changeStatus(
        int $certificateId,
        string $status,
        array $context = [],
        string $reason = ''
    ): mixed {
        $this->assertAdmin($context);

        $certificateId =
            $this->validateId(
                $certificateId,
                'certificate ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        $allowedStatuses = [
            'pending',
            'issued',
            'revoked',
            'suspended',
            'expired',
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
                'Unsupported certificate status.'
            );
        }

        if (
            in_array(
                $status,
                ['revoked', 'suspended'],
                true
            ) &&
            trim($reason) === ''
        ) {
            throw new InvalidArgumentException(
                'A reason is required for this status.'
            );
        }

        if ($this->repository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'changeStatus'
                )
            ) {
                $result =
                    $this->repository->changeStatus(
                        $certificateId,
                        $status,
                        $reason
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'setStatus'
                )
            ) {
                $result =
                    $this->repository->setStatus(
                        $certificateId,
                        $status,
                        $reason
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

                if ($reason !== '') {
                    $data['admin_reason'] = $reason;
                }

                $result =
                    $this->repository->update(
                        $certificateId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'certificate_status_changed',
                $certificateId,
                $context,
                [
                    'status' => $status,
                    'reason' => $reason
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change certificate status.'
            );
        }
    }

    public function update(
        int $certificateId,
        array $data,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $certificateId =
            $this->validateId(
                $certificateId,
                'certificate ID'
            );

        $data =
            $this->sanitizeCertificateData(
                $data
            );

        if (empty($data)) {
            throw new InvalidArgumentException(
                'No valid certificate data was provided.'
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
                        $certificateId,
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
                        $certificateId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'certificate_updated',
                $certificateId,
                $context,
                [
                    'fields' =>
                        array_keys($data)
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update certificate.'
            );
        }
    }

    public function delete(
        int $certificateId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $certificateId =
            $this->validateId(
                $certificateId,
                'certificate ID'
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
                        $certificateId
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'softDelete'
                )
            ) {
                $result =
                    $this->repository->softDelete(
                        $certificateId
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'certificate_deleted',
                $certificateId,
                $context
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to delete certificate.'
            );
        }
    }

    public function listAppeals(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $filters =
            $this->normalizeAppealFilters(
                $filters
            );

        if ($this->appealRepository === null) {
            return [
                'items' => [],
                'pagination' =>
                    $this->emptyPagination(
                        $filters
                    )
            ];
        }

        try {
            $result = null;

            if (
                method_exists(
                    $this->appealRepository,
                    'getAll'
                )
            ) {
                $result =
                    $this->appealRepository->getAll(
                        $filters
                    );
            } elseif (
                method_exists(
                    $this->appealRepository,
                    'all'
                )
            ) {
                $result =
                    $this->appealRepository->all(
                        $filters
                    );
            } elseif (
                method_exists(
                    $this->appealRepository,
                    'paginate'
                )
            ) {
                $result =
                    $this->appealRepository->paginate(
                        $filters
                    );
            }

            return $this->normalizeListResult(
                $result,
                $filters
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load certificate appeals.'
            );
        }
    }

    public function showAppeal(
        int $appealId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $appealId =
            $this->validateId(
                $appealId,
                'appeal ID'
            );

        if ($this->appealRepository === null) {
            return null;
        }

        try {
            if (
                method_exists(
                    $this->appealRepository,
                    'find'
                )
            ) {
                return
                    $this->appealRepository->find(
                        $appealId
                    );
            }

            if (
                method_exists(
                    $this->appealRepository,
                    'findById'
                )
            ) {
                return
                    $this->appealRepository->findById(
                        $appealId
                    );
            }

            if (
                method_exists(
                    $this->appealRepository,
                    'getById'
                )
            ) {
                return
                    $this->appealRepository->getById(
                        $appealId
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load certificate appeal.'
            );
        }

        return null;
    }

    public function approveAppeal(
        int $appealId,
        string $note = '',
        array $context = []
    ): mixed {
        return $this->changeAppealStatus(
            $appealId,
            'approved',
            $note,
            $context
        );
    }

    public function rejectAppeal(
        int $appealId,
        string $reason,
        array $context = []
    ): mixed {
        if (trim($reason) === '') {
            throw new InvalidArgumentException(
                'A rejection reason is required.'
            );
        }

        return $this->changeAppealStatus(
            $appealId,
            'rejected',
            $reason,
            $context
        );
    }

    public function pendingAppeal(
        int $appealId,
        array $context = []
    ): mixed {
        return $this->changeAppealStatus(
            $appealId,
            'pending',
            '',
            $context
        );
    }

    public function changeAppealStatus(
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

        if ($this->appealRepository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->appealRepository,
                    'changeStatus'
                )
            ) {
                $result =
                    $this->appealRepository->changeStatus(
                        $appealId,
                        $status,
                        $note
                    );
            } elseif (
                method_exists(
                    $this->appealRepository,
                    'setStatus'
                )
            ) {
                $result =
                    $this->appealRepository->setStatus(
                        $appealId,
                        $status,
                        $note
                    );
            } elseif (
                method_exists(
                    $this->appealRepository,
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
                    $this->appealRepository->update(
                        $appealId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'certificate_appeal_status_changed',
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

    public function statistics(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $stats = [
            'certificates_total' => 0,
            'certificates_pending' => 0,
            'certificates_issued' => 0,
            'certificates_revoked' => 0,
            'certificates_suspended' => 0,
            'certificates_expired' => 0,
            'appeals_total' => 0,
            'appeals_pending' => 0,
            'appeals_approved' => 0,
            'appeals_rejected' => 0
        ];

        try {
            if (
                $this->repository !== null &&
                method_exists(
                    $this->repository,
                    'getStatistics'
                )
            ) {
                $stats =
                    array_merge(
                        $stats,
                        (array)
                        $this->repository
                            ->getStatistics()
                    );
            } elseif (
                $this->repository !== null &&
                method_exists(
                    $this->repository,
                    'statistics'
                )
            ) {
                $stats =
                    array_merge(
                        $stats,
                        (array)
                        $this->repository
                            ->statistics()
                    );
            }

            if (
                $this->appealRepository !== null &&
                method_exists(
                    $this->appealRepository,
                    'getStatistics'
                )
            ) {
                $stats =
                    array_merge(
                        $stats,
                        (array)
                        $this->appealRepository
                            ->getStatistics()
                    );
            } elseif (
                $this->appealRepository !== null &&
                method_exists(
                    $this->appealRepository,
                    'statistics'
                )
            ) {
                $stats =
                    array_merge(
                        $stats,
                        (array)
                        $this->appealRepository
                            ->statistics()
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load certificate statistics.'
            );
        }

        return $stats;
    }

    protected function normalizeCertificateFilters(
        array $filters
    ): array {
        return $this->normalizeFilters(
            $filters,
            [
                'page',
                'limit',
                'search',
                'status',
                'student_id',
                'training_id',
                'company_id',
                'certificate_number',
                'from',
                'to',
                'sort',
                'order'
            ]
        );
    }

    protected function normalizeAppealFilters(
        array $filters
    ): array {
        return $this->normalizeFilters(
            $filters,
            [
                'page',
                'limit',
                'search',
                'status',
                'certificate_id',
                'student_id',
                'from',
                'to',
                'sort',
                'order'
            ]
        );
    }

    protected function normalizeFilters(
        array $filters,
        array $allowed
    ): array {
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

    protected function sanitizeCertificateData(
        array $data
    ): array {
        $allowed = [
            'certificate_number',
            'student_id',
            'training_id',
            'company_id',
            'issue_date',
            'expiry_date',
            'status',
            'certificate_type',
            'verification_code',
            'grade',
            'score',
            'file_path',
            'metadata'
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

    public function getAppealRepository(): mixed
    {
        return $this->appealRepository;
    }

    public function setAppealRepository(
        mixed $repository
    ): self {
        $this->appealRepository = $repository;

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function certificate_admin_list(
    array $filters = [],
    array $context = []
): array {
    return
        (new CertificateAdminService())
            ->listCertificates(
                $filters,
                $context
            );
}

function certificate_admin_show(
    int $certificateId,
    array $context = []
): mixed {
    return
        (new CertificateAdminService())
            ->show(
                $certificateId,
                $context
            );
}

function certificate_admin_issue(
    int $certificateId,
    array $context = []
): mixed {
    return
        (new CertificateAdminService())
            ->issue(
                $certificateId,
                $context
            );
}

function certificate_admin_revoke(
    int $certificateId,
    string $reason,
    array $context = []
): mixed {
    return
        (new CertificateAdminService())
            ->revoke(
                $certificateId,
                $reason,
                $context
            );
}

function certificate_admin_list_appeals(
    array $filters = [],
    array $context = []
): array {
    return
        (new CertificateAdminService())
            ->listAppeals(
                $filters,
                $context
            );
}

function certificate_admin_approve_appeal(
    int $appealId,
    string $note = '',
    array $context = []
): mixed {
    return
        (new CertificateAdminService())
            ->approveAppeal(
                $appealId,
                $note,
                $context
            );
}

function certificate_admin_reject_appeal(
    int $appealId,
    string $reason,
    array $context = []
): mixed {
    return
        (new CertificateAdminService())
            ->rejectAppeal(
                $appealId,
                $reason,
                $context
            );
}
