<?php

/**
 * MASAR - User Admin Service
 *
 * Administrative service for user management.
 *
 * Controller
 *     ↓
 * UserAdminService
 *     ↓
 * UserRepository
 */

$user_repository_file =
    __DIR__ . '/../../users/repositories/user_repository.php';

require_once __DIR__ . '/../../../shared/functions/security.php';
require_once __DIR__ . '/../../../shared/functions/audit.php';
require_once __DIR__ . '/../../../core/auth/auth.php';

if (file_exists($user_repository_file)) {
    require_once $user_repository_file;
}

class UserAdminService
{
    protected mixed $repository = null;

    public function __construct(mixed $repository = null)
    {
        $this->repository =
            $repository ?? $this->resolveRepository();
    }

    protected function resolveRepository(): mixed
    {
        if (class_exists('UserRepository')) {
            return new UserRepository();
        }

        return null;
    }

    public function index(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        return $this->listUsers(
            $filters,
            $context
        );
    }

    public function listUsers(
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
                'Unable to load users.'
            );
        }
    }

    public function show(
        int $userId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $userId =
            $this->validateId(
                $userId,
                'user ID'
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
                    $userId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'findById'
                )
            ) {
                return $this->repository->findById(
                    $userId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'getById'
                )
            ) {
                return $this->repository->getById(
                    $userId
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load user.'
            );
        }

        return null;
    }

    public function update(
        int $userId,
        array $data,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $userId =
            $this->validateId(
                $userId,
                'user ID'
            );

        $data =
            $this->sanitizeUpdateData(
                $data
            );

        if (empty($data)) {
            throw new InvalidArgumentException(
                'No valid user data was provided.'
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
                        $userId,
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
                        $userId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'user_updated',
                $userId,
                $context,
                [
                    'fields' =>
                        array_keys($data)
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update user.'
            );
        }
    }

    public function activate(
        int $userId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $userId,
            'active',
            $context
        );
    }

    public function deactivate(
        int $userId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $userId,
            'inactive',
            $context
        );
    }

    public function suspend(
        int $userId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $userId,
            'suspended',
            $context
        );
    }

    public function changeStatus(
        int $userId,
        string $status,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);
        $currentUser = function_exists('auth_user') ? auth_user() : null;
        security_require_admin_reauth(is_array($currentUser) ? $currentUser : null, $context, 'user_status_change');

        $userId =
            $this->validateId(
                $userId,
                'user ID'
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
                'Unsupported user status.'
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
                        $userId,
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
                        $userId,
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
                        $userId,
                        [
                            'status' => $status
                        ]
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'user_status_changed',
                $userId,
                $context,
                [
                    'status' => $status
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change user status.'
            );
        }
    }

    public function delete(
        int $userId,
        array $context = []
    ): mixed {
        $this->assertSuperAdminOrAdmin(
            $context
        );
        $currentUser = function_exists('auth_user') ? auth_user() : null;
        security_require_admin_reauth(is_array($currentUser) ? $currentUser : null, $context, 'user_delete');

        $userId =
            $this->validateId(
                $userId,
                'user ID'
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
                        $userId
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'softDelete'
                )
            ) {
                $result =
                    $this->repository->softDelete(
                        $userId
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'user_deleted',
                $userId,
                $context
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to delete user.'
            );
        }
    }

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
                    'searchUsers'
                )
            ) {
                return (array)
                    $this->repository->searchUsers(
                        $query,
                        $filters
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to search users.'
            );
        }

        return [];
    }

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
                    'countUsers'
                )
            ) {
                return max(
                    0,
                    (int) $this->repository->countUsers(
                        $filters
                    )
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to count users.'
            );
        }

        return 0;
    }

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
                'blocked' => 0
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
                'Unable to load user statistics.'
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

            'blocked' =>
                $this->count(
                    ['status' => 'blocked'],
                    $context
                )
        ];
    }

    public function bulkStatus(
        array $userIds,
        string $status,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $userIds =
            $this->normalizeIds(
                $userIds
            );

        if (empty($userIds)) {
            throw new InvalidArgumentException(
                'No valid user IDs were provided.'
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
                'Unsupported user status.'
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
                        $userIds,
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
                        $userIds,
                        [
                            'status' => $status
                        ]
                    );
            } else {
                $result = 0;

                foreach ($userIds as $userId) {
                    $current =
                        $this->changeStatus(
                            $userId,
                            $status,
                            $context
                        );

                    if ($current !== false) {
                        $result++;
                    }
                }
            }

            $this->logAdminAction(
                'users_bulk_status_changed',
                null,
                $context,
                [
                    'user_ids' => $userIds,
                    'status' => $status
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update users status.'
            );
        }
    }

    public function resetPassword(
        int $userId,
        string $password,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $userId =
            $this->validateId(
                $userId,
                'user ID'
            );

        if (
            strlen($password) < 8
        ) {
            throw new InvalidArgumentException(
                'Password must contain at least 8 characters.'
            );
        }

        if ($this->repository === null) {
            return false;
        }

        $hash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        try {
            if (
                method_exists(
                    $this->repository,
                    'resetPassword'
                )
            ) {
                $result =
                    $this->repository->resetPassword(
                        $userId,
                        $hash
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'updatePassword'
                )
            ) {
                $result =
                    $this->repository->updatePassword(
                        $userId,
                        $hash
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'update'
                )
            ) {
                $result =
                    $this->repository->update(
                        $userId,
                        [
                            'password' => $hash
                        ]
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'user_password_reset',
                $userId,
                $context
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to reset user password.'
            );
        }
    }

    protected function normalizeFilters(
        array $filters
    ): array {
        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'role',
            'email',
            'phone',
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
            max(
                1,
                (int) (
                    $filters['limit'] ?? 20
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
            'first_name',
            'last_name',
            'email',
            'phone',
            'role',
            'status',
            'avatar',
            'is_verified',
            'email_verified_at',
            'phone_verified_at'
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
                (int) (
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
            // Logging must not break the main admin operation.
        }
    }

    public function isAuthorized(
        array $context = []
    ): bool {
        if (
            !empty($context['is_admin'])
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

    protected function assertSuperAdminOrAdmin(
        array $context
    ): void {
        $this->assertAdmin($context);
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
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function user_admin_list(
    array $filters = [],
    array $context = []
): array {
    return
        (new UserAdminService())
            ->listUsers(
                $filters,
                $context
            );
}

if (!function_exists('user_admin_show')) {
    function user_admin_show(
        int $userId,
        array $context = []
    ): mixed {
        return
            (new UserAdminService())
                ->show(
                    $userId,
                    $context
                );
    }
}

if (!function_exists('user_admin_update')) {
    function user_admin_update(
        int $userId,
        array $data,
        array $context = []
    ): mixed {
        return
            (new UserAdminService())
                ->update(
                    $userId,
                    $data,
                    $context
                );
    }
}

function user_admin_activate(
    int $userId,
    array $context = []
): mixed {
    return
        (new UserAdminService())
            ->activate(
                $userId,
                $context
            );
}

function user_admin_deactivate(
    int $userId,
    array $context = []
): mixed {
    return
        (new UserAdminService())
            ->deactivate(
                $userId,
                $context
            );
}

function user_admin_suspend(
    int $userId,
    array $context = []
): mixed {
    return
        (new UserAdminService())
            ->suspend(
                $userId,
                $context
            );
}

if (!function_exists('user_admin_delete')) {
    function user_admin_delete(
        int $userId,
        array $context = []
    ): mixed {
        return
            (new UserAdminService())
                ->delete(
                    $userId,
                    $context
                );
    }
}
