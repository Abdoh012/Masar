<?php

require_once __DIR__ . '/../../../shared/functions/security.php';
require_once __DIR__ . '/../../../shared/functions/audit.php';
require_once __DIR__ . '/../../../core/auth/auth.php';
// Auth repository is needed to set users' email_verified_at when company becomes active
$auth_repository_file = __DIR__ . '/../../auth/repositories/auth_repository.php';
if (file_exists($auth_repository_file)) {
    require_once $auth_repository_file;
}

/**
 * MASAR - Company Admin Service
 *
 * Administrative service for company management.
 *
 * Controller
 *     ↓
 * CompanyAdminService
 *     ↓
 * CompanyRepository / CompanyApprovalRepository
 */

$company_repository_file =
    __DIR__ . '/../../companies/repositories/company_repository.php';

$approval_repository_file =
    __DIR__ . '/../../companies/repositories/company_approval_repository.php';

if (file_exists($company_repository_file)) {
    require_once $company_repository_file;
}

if (file_exists($approval_repository_file)) {
    require_once $approval_repository_file;
}

class CompanyAdminService
{
    protected mixed $repository = null;

    protected mixed $approvalRepository = null;

    public function __construct(
        mixed $repository = null,
        mixed $approvalRepository = null
    ) {
        $this->repository =
            $repository ?? $this->resolveRepository();

        $this->approvalRepository =
            $approvalRepository
            ?? $this->resolveApprovalRepository();
    }

    protected function resolveRepository(): mixed
    {
        if (class_exists('CompanyRepository')) {
            return new CompanyRepository();
        }

        return null;
    }

    protected function resolveApprovalRepository(): mixed
    {
        if (class_exists('CompanyApprovalRepository')) {
            return new CompanyApprovalRepository();
        }

        return null;
    }

    /**
     * List companies for administration.
     */
    public function index(
        array $filters = [],
        array $context = []
    ): array {
        return $this->listCompanies(
            $filters,
            $context
        );
    }

    public function listCompanies(
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
                'Unable to load companies.'
            );
        }
    }

    /**
     * Get one company.
     */
    public function show(
        int $companyId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $companyId =
            $this->validateId(
                $companyId,
                'company ID'
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
                    $companyId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'findById'
                )
            ) {
                return $this->repository->findById(
                    $companyId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'getById'
                )
            ) {
                return $this->repository->getById(
                    $companyId
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load company.'
            );
        }

        return null;
    }

    /**
     * Update company.
     */
    public function update(
        int $companyId,
        array $data,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $companyId =
            $this->validateId(
                $companyId,
                'company ID'
            );

        $data =
            $this->sanitizeUpdateData($data);

        if (empty($data)) {
            throw new InvalidArgumentException(
                'No valid company data was provided.'
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
                        $companyId,
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
                        $companyId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'company_updated',
                $companyId,
                $context,
                [
                    'fields' =>
                        array_keys($data)
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update company.'
            );
        }
    }

    /**
     * Approve company.
     */
    public function approve(
        int $companyId,
        string $note = '',
        array $context = []
    ): mixed {
        return $this->processApproval(
            $companyId,
            'approved',
            $note,
            $context
        );
    }

    /**
     * Reject company.
     */
    public function reject(
        int $companyId,
        string $reason = '',
        array $context = []
    ): mixed {
        if (trim($reason) === '') {
            throw new InvalidArgumentException(
                'A rejection reason is required.'
            );
        }

        return $this->processApproval(
            $companyId,
            'rejected',
            $reason,
            $context
        );
    }

    /**
     * Put company back into pending status.
     */
    public function pending(
        int $companyId,
        array $context = []
    ): mixed {
        return $this->processApproval(
            $companyId,
            'pending',
            '',
            $context
        );
    }

    /**
     * Process company approval status.
     */
    protected function processApproval(
        int $companyId,
        string $status,
        string $note,
        array $context
    ): mixed {
        $this->assertAdmin($context);
        $currentUser = function_exists('auth_user') ? auth_user() : null;
        security_require_admin_reauth(is_array($currentUser) ? $currentUser : null, $context, 'company_approval');

        $companyId =
            $this->validateId(
                $companyId,
                'company ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        $allowedStatuses = [
            'pending',
            'approved',
            'rejected'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported company approval status.'
            );
        }

        $adminId =
            $this->getAdminId($context);

        try {
            $approvalResult = null;

            if ($this->approvalRepository !== null) {
                if (
                    method_exists(
                        $this->approvalRepository,
                        'updateStatus'
                    )
                ) {
                    $approvalResult =
                        $this->approvalRepository
                            ->updateStatus(
                                $companyId,
                                $status,
                                $note,
                                $adminId
                            );
                } elseif (
                    method_exists(
                        $this->approvalRepository,
                        'setStatus'
                    )
                ) {
                    $approvalResult =
                        $this->approvalRepository
                            ->setStatus(
                                $companyId,
                                $status,
                                $note,
                                $adminId
                            );
                } elseif (
                    method_exists(
                        $this->approvalRepository,
                        'create'
                    )
                ) {
                    $approvalResult =
                        $this->approvalRepository->create([
                            'company_id' =>
                                $companyId,

                            'admin_id' =>
                                $adminId,

                            'status' =>
                                $status,

                            'note' =>
                                $note,

                            'created_at' =>
                                date('Y-m-d H:i:s')
                        ]);
                }
            }

            $companyResult = null;

            if ($this->repository !== null) {
                if (
                    method_exists(
                        $this->repository,
                        'changeApprovalStatus'
                    )
                ) {
                    $companyResult =
                        $this->repository
                            ->changeApprovalStatus(
                                $companyId,
                                $status
                            );
                } elseif (
                    method_exists(
                        $this->repository,
                        'setApprovalStatus'
                    )
                ) {
                    $companyResult =
                        $this->repository
                            ->setApprovalStatus(
                                $companyId,
                                $status
                            );
                } elseif (
                    method_exists(
                        $this->repository,
                        'update'
                    )
                ) {
                    $companyResult =
                        $this->repository->update(
                            $companyId,
                            [
                                'approval_status' =>
                                    $status
                            ]
                        );
                }
            }

            $this->logAdminAction(
                'company_approval_' . $status,
                $companyId,
                $context,
                [
                    'status' => $status,
                    'note' => $note
                ]
            );

            // If the company was approved or the status transitioned to active elsewhere,
            // ensure the associated user's email_verified_at is set when the company becomes active.
            // Note: Only run this when the status being processed is 'approved' (approval) or
            // when changeStatus is used to set operational status to 'active'. For approval flow
            // the caller may separately set company operational status; here we only act on
            // explicit 'approved' approval handling by leaving the status handling to approval flow.

            return [
                'company' =>
                    $companyResult,

                'approval' =>
                    $approvalResult,

                'status' =>
                    $status
            ];
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to process company approval.'
            );
        }
    }

    /**
     * Activate company.
     */
    public function activate(
        int $companyId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $companyId,
            'active',
            $context
        );
    }

    /**
     * Deactivate company.
     */
    public function deactivate(
        int $companyId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $companyId,
            'inactive',
            $context
        );
    }

    /**
     * Suspend company.
     */
    public function suspend(
        int $companyId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $companyId,
            'suspended',
            $context
        );
    }

    /**
     * Change company operational status.
     */
    public function changeStatus(
        int $companyId,
        string $status,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);
        $currentUser = function_exists('auth_user') ? auth_user() : null;
        security_require_admin_reauth(is_array($currentUser) ? $currentUser : null, $context, 'company_status_change');

        $companyId =
            $this->validateId(
                $companyId,
                'company ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        $allowedStatuses = [
            'active',
            'inactive',
            'suspended',
            'blocked'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported company status.'
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
                        $companyId,
                        $status
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'setStatus'
                )
            ) {
                $result =
                    $this->repository->setStatus(
                        $companyId,
                        $status
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'update'
                )
            ) {
                $result =
                    $this->repository->update(
                        $companyId,
                        [
                            'status' => $status
                        ]
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'company_status_changed',
                $companyId,
                $context,
                [
                    'status' => $status
                ]
            );

            // If the company operational status becomes active, set the associated user's
            // email_verified_at timestamp to record when the company was activated.
            if ($status === 'active') {
                try {
                    $company = company_repository_find_by_id($companyId);
                    if (is_array($company) && !empty($company['user_id'])) {
                        // auth repository is required at top of file; set email verified timestamp
                        if (function_exists('auth_repository_set_email_verified_at')) {
                            auth_repository_set_email_verified_at((int) $company['user_id']);
                        }
                    }
                } catch (Throwable $ignore) {
                    // Do not fail the status change if setting the timestamp fails; just log.
                    if (function_exists('security_log_event')) {
                        security_log_event('company_activation_email_verified_set_failed', [
                            'company_id' => $companyId,
                            'exception' => get_class($ignore),
                            'message' => $ignore->getMessage()
                        ]);
                    }
                }
            }

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change company status.'
            );
        }
    }

    /**
     * Delete company.
     */
    public function delete(
        int $companyId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);
        $currentUser = function_exists('auth_user') ? auth_user() : null;
        security_require_admin_reauth(is_array($currentUser) ? $currentUser : null, $context, 'company_delete');

        $companyId =
            $this->validateId(
                $companyId,
                'company ID'
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
                        $companyId
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'softDelete'
                )
            ) {
                $result =
                    $this->repository->softDelete(
                        $companyId
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'company_deleted',
                $companyId,
                $context
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to delete company.'
            );
        }
    }

    /**
     * Get pending companies.
     */
    public function getPending(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $filters['approval_status'] =
            'pending';

        return $this->listCompanies(
            $filters,
            $context
        );
    }

    /**
     * Search companies.
     */
    public function search(
        string $query,
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $query =
            trim($query);

        if ($query === '') {
            return [];
        }

        $filters =
            $this->normalizeFilters(
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
                    'searchCompanies'
                )
            ) {
                return (array)
                    $this->repository
                        ->searchCompanies(
                            $query,
                            $filters
                        );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to search companies.'
            );
        }

        return [];
    }

    /**
     * Count companies.
     */
    public function count(
        array $filters = [],
        array $context = []
    ): int {
        $this->assertAdmin($context);

        $filters =
            $this->normalizeFilters(
                $filters
            );

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
                    (int) $this->repository->count(
                        $filters
                    )
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'countCompanies'
                )
            ) {
                return max(
                    0,
                    (int) $this->repository
                        ->countCompanies(
                            $filters
                        )
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to count companies.'
            );
        }

        return 0;
    }

    /**
     * Company statistics.
     */
    public function statistics(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        if ($this->repository === null) {
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'suspended' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0
            ];
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getStatistics'
                )
            ) {
                return (array)
                    $this->repository
                        ->getStatistics();
            }

            if (
                method_exists(
                    $this->repository,
                    'statistics'
                )
            ) {
                return (array)
                    $this->repository
                        ->statistics();
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load company statistics.'
            );
        }

        return [
            'total' =>
                $this->count([], $context),

            'active' =>
                $this->count(
                    ['status' => 'active'],
                    $context
                ),

            'inactive' =>
                $this->count(
                    ['status' => 'inactive'],
                    $context
                ),

            'suspended' =>
                $this->count(
                    ['status' => 'suspended'],
                    $context
                ),

            'pending' =>
                $this->count(
                    ['approval_status' => 'pending'],
                    $context
                ),

            'approved' =>
                $this->count(
                    ['approval_status' => 'approved'],
                    $context
                ),

            'rejected' =>
                $this->count(
                    ['approval_status' => 'rejected'],
                    $context
                )
        ];
    }

    /**
     * Bulk status update.
     */
    public function bulkStatus(
        array $companyIds,
        string $status,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $companyIds =
            $this->normalizeIds(
                $companyIds
            );

        if (empty($companyIds)) {
            throw new InvalidArgumentException(
                'No valid company IDs were provided.'
            );
        }

        $status =
            strtolower(
                trim($status)
            );

        if (
            !in_array(
                $status,
                [
                    'active',
                    'inactive',
                    'suspended',
                    'blocked'
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported company status.'
            );
        }

        if ($this->repository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'bulkStatus'
                )
            ) {
                $result =
                    $this->repository->bulkStatus(
                        $companyIds,
                        $status
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'bulkUpdate'
                )
            ) {
                $result =
                    $this->repository->bulkUpdate(
                        $companyIds,
                        [
                            'status' => $status
                        ]
                    );
            } else {
                $result = 0;

                foreach (
                    $companyIds
                    as $companyId
                ) {
                    $current =
                        $this->changeStatus(
                            $companyId,
                            $status,
                            $context
                        );

                    if ($current !== false) {
                        $result++;
                    }
                }
            }

            $this->logAdminAction(
                'companies_bulk_status_changed',
                null,
                $context,
                [
                    'company_ids' =>
                        $companyIds,

                    'status' =>
                        $status
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update companies status.'
            );
        }
    }

    /**
     * Bulk approval action.
     */
    public function bulkApproval(
        array $companyIds,
        string $status,
        string $note = '',
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $companyIds =
            $this->normalizeIds(
                $companyIds
            );

        if (empty($companyIds)) {
            throw new InvalidArgumentException(
                'No valid company IDs were provided.'
            );
        }

        $status =
            strtolower(
                trim($status)
            );

        if (
            !in_array(
                $status,
                [
                    'pending',
                    'approved',
                    'rejected'
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported approval status.'
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

        $results = [];

        foreach (
            $companyIds
            as $companyId
        ) {
            try {
                $results[$companyId] =
                    $this->processApproval(
                        $companyId,
                        $status,
                        $note,
                        $context
                    );
            } catch (Throwable $e) {
                $results[$companyId] = [
                    'success' => false,
                    'message' =>
                        $e->getMessage()
                ];
            }
        }

        return $results;
    }

    protected function normalizeFilters(
        array $filters
    ): array {
        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'approval_status',
            'owner_id',
            'industry',
            'city',
            'country',
            'from',
            'to',
            'sort',
            'order'
        ];

        $result = [];

        foreach ($allowed as $key) {
            if (
                array_key_exists(
                    $key,
                    $filters
                )
            ) {
                $result[$key] =
                    $filters[$key];
            }
        }

        $result['page'] =
            max(
                1,
                (int) (
                    $result['page'] ?? 1
                )
            );

        $result['limit'] =
            min(
                100,
                max(
                    1,
                    (int) (
                        $result['limit'] ?? 20
                    )
                )
            );

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
                (int) (
                    $filters['page'] ?? 1
                )
            );

        $limit =
            min(
                100,
                max(
                    1,
                    (int) (
                        $filters['limit'] ?? 20
                    )
                )
            );

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => 0,
            'pages' => 0
        ];
    }

    protected function sanitizeUpdateData(
        array $data
    ): array {
        $allowed = [
            'name',
            'legal_name',
            'commercial_name',
            'email',
            'phone',
            'website',
            'description',
            'industry',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'logo',
            'tax_number',
            'registration_number',
            'status'
        ];

        $result = [];

        foreach ($allowed as $field) {
            if (
                array_key_exists(
                    $field,
                    $data
                )
            ) {
                $result[$field] =
                    $data[$field];
            }
        }

        return $result;
    }

    protected function normalizeIds(
        array $ids
    ): array {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $ids
                    ),
                    fn ($id) => $id > 0
                )
            )
        );
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

    protected function getAdminId(
        array $context
    ): int {
        return max(
            0,
            (int) (
                $context['admin_id']
                ?? $context['user_id']
                ?? 0
            )
        );
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

            'target_id' =>
                $targetId,

            'admin_id' =>
                $this->getAdminId(
                    $context
                ),

            'metadata' =>
                $metadata,

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
            // Logging must not break the main operation.
        }
    }

    public function isAuthorized(
        array $context = []
    ): bool {
        if (
            !empty(
                $context['is_admin']
            )
        ) {
            return true;
        }

        $role =
            strtolower(
                trim(
                    (string) (
                        $context['role'] ?? ''
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

    protected function assertAdmin(
        array $context
    ): void {
        if (
            !$this->isAuthorized($context)
        ) {
            throw new RuntimeException(
                'Unauthorized administrative access.'
            );
        }
    }

    public function getRepository(): mixed
    {
        return $this->repository;
    }

    public function setRepository(
        mixed $repository
    ): self {
        $this->repository =
            $repository;

        return $this;
    }

    public function getApprovalRepository(): mixed
    {
        return $this->approvalRepository;
    }

    public function setApprovalRepository(
        mixed $repository
    ): self {
        $this->approvalRepository =
            $repository;

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function company_admin_list(
    array $filters = [],
    array $context = []
): array {
    return
        (new CompanyAdminService())
            ->listCompanies(
                $filters,
                $context
            );
}

if (!function_exists('company_admin_show')) {
    function company_admin_show(
        int $companyId,
        array $context = []
    ): mixed {
        return
            (new CompanyAdminService())
                ->show(
                    $companyId,
                    $context
                );
    }
}

if (!function_exists('company_admin_update')) {
    function company_admin_update(
        int $companyId,
        array $data,
        array $context = []
    ): mixed {
        return
            (new CompanyAdminService())
                ->update(
                    $companyId,
                    $data,
                    $context
                );
    }
}

if (!function_exists('company_admin_approve')) {
    function company_admin_approve(
        int $companyId,
        string $note = '',
        array $context = []
    ): mixed {
        return
            (new CompanyAdminService())
                ->approve(
                    $companyId,
                    $note,
                    $context
                );
    }
}

if (!function_exists('company_admin_reject')) {
    function company_admin_reject(
        int $companyId,
        string $reason,
        array $context = []
    ): mixed {
        return
            (new CompanyAdminService())
                ->reject(
                    $companyId,
                    $reason,
                    $context
                );
    }
}

function company_admin_activate(
    int $companyId,
    array $context = []
): mixed {
    return
        (new CompanyAdminService())
            ->activate(
                $companyId,
                $context
            );
}

function company_admin_deactivate(
    int $companyId,
    array $context = []
): mixed {
    return
        (new CompanyAdminService())
            ->deactivate(
                $companyId,
                $context
            );
}

function company_admin_suspend(
    int $companyId,
    array $context = []
): mixed {
    return
        (new CompanyAdminService())
            ->suspend(
                $companyId,
                $context
            );
}
