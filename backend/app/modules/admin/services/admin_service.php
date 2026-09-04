<?php

/**
 * MASAR - Admin Service
 *
 * Main administrative service.
 *
 * Controller
 *     ↓
 * AdminService
 *     ↓
 * AdminRepository
 *
 * This service handles:
 * - Dashboard statistics
 * - Administrative overview
 * - User/company/training/certificate counts
 * - Recent activity
 * - System health summary
 * - Admin-level permissions/context
 */

$repository_file =
    __DIR__ . '/../repositories/admin_repository.php';

if (file_exists($repository_file)) {
    require_once $repository_file;
}

class AdminService
{
    protected mixed $repository = null;

    public function __construct(mixed $repository = null)
    {
        $this->repository =
            $repository ?? $this->resolveRepository();
    }

    protected function resolveRepository(): mixed
    {
        if (class_exists('AdminRepository')) {
            return new AdminRepository();
        }

        return null;
    }

    /**
     * Get complete admin dashboard.
     */
    public function dashboard(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        return [
            'statistics' =>
                $this->statistics($context),

            'recent_activity' =>
                $this->recentActivity($context),

            'pending' =>
                $this->pendingSummary($context),

            'system' =>
                $this->systemSummary($context)
        ];
    }

    /**
     * Alias for dashboard().
     */
    public function index(
        array $context = []
    ): array {
        return $this->dashboard($context);
    }

    /**
     * Get administrative statistics.
     */
    public function statistics(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        if ($this->repository === null) {
            return $this->emptyStatistics();
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getStatistics'
                )
            ) {
                return $this->normalizeStatistics(
                    $this->repository->getStatistics()
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'statistics'
                )
            ) {
                return $this->normalizeStatistics(
                    $this->repository->statistics()
                );
            }

            return $this->collectStatistics();
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load admin statistics.'
            );
        }
    }

    /**
     * Collect statistics from repository methods
     * when a single statistics method does not exist.
     */
    protected function collectStatistics(): array
    {
        $statistics = [];

        $statistics['users'] =
            $this->repositoryCount([
                'countUsers',
                'getUserCount'
            ]);

        $statistics['companies'] =
            $this->repositoryCount([
                'countCompanies',
                'getCompanyCount'
            ]);

        $statistics['trainings'] =
            $this->repositoryCount([
                'countTrainings',
                'getTrainingCount'
            ]);

        $statistics['applications'] =
            $this->repositoryCount([
                'countApplications',
                'getApplicationCount'
            ]);

        $statistics['sessions'] =
            $this->repositoryCount([
                'countTrainingSessions',
                'getTrainingSessionCount'
            ]);

        $statistics['certificates'] =
            $this->repositoryCount([
                'countCertificates',
                'getCertificateCount'
            ]);

        $statistics['appeals'] =
            $this->repositoryCount([
                'countAppeals',
                'getAppealCount'
            ]);

        return $statistics;
    }

    /**
     * Get pending administrative items.
     */
    public function pendingSummary(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        if ($this->repository === null) {
            return [
                'companies' => 0,
                'trainings' => 0,
                'applications' => 0,
                'certificates' => 0,
                'appeals' => 0
            ];
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getPendingSummary'
                )
            ) {
                return (array)
                    $this->repository
                        ->getPendingSummary();
            }

            if (
                method_exists(
                    $this->repository,
                    'pendingSummary'
                )
            ) {
                return (array)
                    $this->repository
                        ->pendingSummary();
            }

            return [
                'companies' =>
                    $this->repositoryCount([
                        'countPendingCompanies',
                        'getPendingCompanyCount'
                    ]),

                'trainings' =>
                    $this->repositoryCount([
                        'countPendingTrainings',
                        'getPendingTrainingCount'
                    ]),

                'applications' =>
                    $this->repositoryCount([
                        'countPendingApplications',
                        'getPendingApplicationCount'
                    ]),

                'certificates' =>
                    $this->repositoryCount([
                        'countPendingCertificates',
                        'getPendingCertificateCount'
                    ]),

                'appeals' =>
                    $this->repositoryCount([
                        'countPendingAppeals',
                        'getPendingAppealCount'
                    ])
            ];
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load pending summary.'
            );
        }
    }

    /**
     * Get recent administrative activity.
     */
    public function recentActivity(
        array $context = [],
        int $limit = 20
    ): array {
        $this->assertAdmin($context);

        $limit = $this->normalizeLimit($limit);

        if ($this->repository === null) {
            return [];
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getRecentActivity'
                )
            ) {
                return (array)
                    $this->repository
                        ->getRecentActivity($limit);
            }

            if (
                method_exists(
                    $this->repository,
                    'recentActivity'
                )
            ) {
                return (array)
                    $this->repository
                        ->recentActivity($limit);
            }

            return [];
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load recent activity.'
            );
        }
    }

    /**
     * Get system summary.
     */
    public function systemSummary(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        return [
            'status' => 'operational',

            'timestamp' =>
                date('Y-m-d H:i:s'),

            'php_version' =>
                PHP_VERSION,

            'environment' =>
                $this->getEnvironment()
        ];
    }

    /**
     * Get environment safely.
     */
    protected function getEnvironment(): string
    {
        $environment =
            $_ENV['APP_ENV']
            ?? $_SERVER['APP_ENV']
            ?? 'production';

        return (string) $environment;
    }

    /**
     * Get a list of administrators.
     */
    public function getAdministrators(
        array $context = [],
        array $filters = []
    ): array {
        $this->assertSuperAdminOrAdmin($context);

        if ($this->repository === null) {
            return [];
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getAdministrators'
                )
            ) {
                return (array)
                    $this->repository
                        ->getAdministrators($filters);
            }

            if (
                method_exists(
                    $this->repository,
                    'listAdministrators'
                )
            ) {
                return (array)
                    $this->repository
                        ->listAdministrators($filters);
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load administrators.'
            );
        }

        return [];
    }

    /**
     * Get a single administrator.
     */
    public function getAdministrator(
        int $adminId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        if ($adminId <= 0) {
            throw new InvalidArgumentException(
                'A valid admin ID is required.'
            );
        }

        if ($this->repository === null) {
            return null;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'findAdministrator'
                )
            ) {
                return $this->repository
                    ->findAdministrator($adminId);
            }

            if (
                method_exists(
                    $this->repository,
                    'getAdministrator'
                )
            ) {
                return $this->repository
                    ->getAdministrator($adminId);
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load administrator.'
            );
        }

        return null;
    }

    /**
     * Create an administrative activity log.
     */
    public function logActivity(
        array $activity,
        array $context = []
    ): bool {
        $this->assertAdmin($context);

        $activity =
            $this->normalizeActivity(
                $activity,
                $context
            );

        if ($this->repository === null) {
            return true;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'logActivity'
                )
            ) {
                return (bool)
                    $this->repository
                        ->logActivity($activity);
            }

            if (
                method_exists(
                    $this->repository,
                    'createActivityLog'
                )
            ) {
                return (bool)
                    $this->repository
                        ->createActivityLog($activity);
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to log admin activity.'
            );
        }

        return true;
    }

    /**
     * Search administrative records.
     */
    public function search(
        string $query,
        array $context = [],
        array $filters = []
    ): array {
        $this->assertAdmin($context);

        $query = trim($query);

        if ($query === '') {
            return [];
        }

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
                    'globalSearch'
                )
            ) {
                return (array)
                    $this->repository->globalSearch(
                        $query,
                        $filters
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to perform admin search.'
            );
        }

        return [];
    }

    /**
     * Get admin activity logs.
     */
    public function activityLogs(
        array $context = [],
        array $filters = []
    ): array {
        $this->assertAdmin($context);

        if ($this->repository === null) {
            return [];
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getActivityLogs'
                )
            ) {
                return (array)
                    $this->repository
                        ->getActivityLogs($filters);
            }

            if (
                method_exists(
                    $this->repository,
                    'activityLogs'
                )
            ) {
                return (array)
                    $this->repository
                        ->activityLogs($filters);
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load activity logs.'
            );
        }

        return [];
    }

    /**
     * Check whether the current context is authorized.
     * Authorization is handled by the authentication middleware.
     * This method must NOT trust role or is_admin from request context.
     */
    public function isAuthorized(
        array $context = []
    ): bool {

        /*
         * The authentication middleware (middleware_admin) already validated
         * that the caller is an administrator. Absence of a locally-computed
         * role must NOT automatically expose admin methods.
         */

        return false;
    }

    /**
     * Check for admin access.
     */
    protected function assertAdmin(
        array $context
    ): void {
        if (!$this->isAuthorized($context)) {
            throw new RuntimeException(
                'Unauthorized administrative access.'
            );
        }
    }

    /**
     * Check for admin/super-admin access.
     */
    protected function assertSuperAdminOrAdmin(
        array $context
    ): void {
        $this->assertAdmin($context);
    }

    /**
     * Normalize repository statistics.
     */
    protected function normalizeStatistics(
        mixed $statistics
    ): array {
        if (!is_array($statistics)) {
            return $this->emptyStatistics();
        }

        $defaults =
            $this->emptyStatistics();

        foreach ($statistics as $key => $value) {
            $defaults[$key] = $value;
        }

        return $defaults;
    }

    /**
     * Default statistics structure.
     */
    protected function emptyStatistics(): array
    {
        return [
            'users' => 0,
            'companies' => 0,
            'trainings' => 0,
            'applications' => 0,
            'sessions' => 0,
            'certificates' => 0,
            'appeals' => 0
        ];
    }

    /**
     * Call the first available repository count method.
     */
    protected function repositoryCount(
        array $methods
    ): int {
        if ($this->repository === null) {
            return 0;
        }

        foreach ($methods as $method) {
            if (
                method_exists(
                    $this->repository,
                    $method
                )
            ) {
                return max(
                    0,
                    (int) $this->repository->{$method}()
                );
            }
        }

        return 0;
    }

    /**
     * Normalize pagination limit.
     */
    protected function normalizeLimit(
        int $limit
    ): int {
        if ($limit < 1) {
            return 20;
        }

        return min($limit, 100);
    }

    /**
     * Normalize activity payload.
     */
    protected function normalizeActivity(
        array $activity,
        array $context
    ): array {
        if (
            empty($activity['admin_id']) &&
            !empty($context['admin_id'])
        ) {
            $activity['admin_id'] =
                (int) $context['admin_id'];
        }

        if (
            empty($activity['user_id']) &&
            !empty($context['user_id'])
        ) {
            $activity['user_id'] =
                (int) $context['user_id'];
        }

        if (
            empty($activity['ip']) &&
            !empty($context['ip'])
        ) {
            $activity['ip'] =
                $context['ip'];
        }

        if (
            empty($activity['created_at'])
        ) {
            $activity['created_at'] =
                date('Y-m-d H:i:s');
        }

        return $activity;
    }

    /**
     * Repository getter.
     */
    public function getRepository(): mixed
    {
        return $this->repository;
    }

    /**
     * Repository setter.
     */
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

function admin_dashboard(
    array $context = []
): array {
    return
        (new AdminService())
            ->dashboard($context);
}

function admin_statistics(
    array $context = []
): array {
    return
        (new AdminService())
            ->statistics($context);
}

function admin_pending_summary(
    array $context = []
): array {
    return
        (new AdminService())
            ->pendingSummary($context);
}

function admin_recent_activity(
    array $context = [],
    int $limit = 20
): array {
    return
        (new AdminService())
            ->recentActivity(
                $context,
                $limit
            );
}
