<?php

require_once __DIR__ . '/../../../shared/functions/security.php';
require_once __DIR__ . '/../../../shared/functions/audit.php';
require_once __DIR__ . '/../../../core/auth/auth.php';

/**
 * MASAR - Training Admin Service
 *
 * Administrative service for training management.
 *
 * Controller
 *     ↓
 * TrainingAdminService
 *     ↓
 * TrainingRepository / ApplicationRepository
 *     / TrainingSessionRepository
 */

$training_repository_file =
    __DIR__ . '/../../training/repositories/training_repository.php';

$application_repository_file =
    __DIR__ . '/../../training/repositories/application_repository.php';

$session_repository_file =
    __DIR__ . '/../../training/repositories/training_session_repository.php';

if (file_exists($training_repository_file)) {
    require_once $training_repository_file;
}

if (file_exists($application_repository_file)) {
    require_once $application_repository_file;
}

if (file_exists($session_repository_file)) {
    require_once $session_repository_file;
}

class TrainingAdminService
{
    protected mixed $repository = null;

    protected mixed $applicationRepository = null;

    protected mixed $sessionRepository = null;

    public function __construct(
        mixed $repository = null,
        mixed $applicationRepository = null,
        mixed $sessionRepository = null
    ) {
        $this->repository =
            $repository ?? $this->resolveRepository();

        $this->applicationRepository =
            $applicationRepository
            ?? $this->resolveApplicationRepository();

        $this->sessionRepository =
            $sessionRepository
            ?? $this->resolveSessionRepository();
    }

    protected function resolveRepository(): mixed
    {
        if (class_exists('TrainingRepository')) {
            return new TrainingRepository();
        }

        return null;
    }

    protected function resolveApplicationRepository(): mixed
    {
        if (class_exists('ApplicationRepository')) {
            return new ApplicationRepository();
        }

        return null;
    }

    protected function resolveSessionRepository(): mixed
    {
        if (class_exists('TrainingSessionRepository')) {
            return new TrainingSessionRepository();
        }

        return null;
    }

    public function index(
        array $filters = [],
        array $context = []
    ): array {
        return $this->listTrainings(
            $filters,
            $context
        );
    }

    public function listTrainings(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $filters =
            $this->normalizeTrainingFilters(
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
                'Unable to load trainings.'
            );
        }
    }

    public function show(
        int $trainingId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $trainingId =
            $this->validateId(
                $trainingId,
                'training ID'
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
                    $trainingId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'findById'
                )
            ) {
                return $this->repository->findById(
                    $trainingId
                );
            }

            if (
                method_exists(
                    $this->repository,
                    'getById'
                )
            ) {
                return $this->repository->getById(
                    $trainingId
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load training.'
            );
        }

        return null;
    }

    public function update(
        int $trainingId,
        array $data,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $trainingId =
            $this->validateId(
                $trainingId,
                'training ID'
            );

        $data =
            $this->sanitizeTrainingData(
                $data
            );

        if (empty($data)) {
            throw new InvalidArgumentException(
                'No valid training data was provided.'
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
                        $trainingId,
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
                        $trainingId,
                        $data
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'training_updated',
                $trainingId,
                $context,
                [
                    'fields' =>
                        array_keys($data)
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update training.'
            );
        }
    }

    public function activate(
        int $trainingId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $trainingId,
            'active',
            $context
        );
    }

    public function deactivate(
        int $trainingId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $trainingId,
            'inactive',
            $context
        );
    }

    public function close(
        int $trainingId,
        array $context = []
    ): mixed {

        $this->assertAdmin($context);

        $trainingId =
            $this->validateId(
                $trainingId,
                'training ID'
            );


        $result =
            $this->changeStatus(
                $trainingId,
                'closed',
                $context
            );


        if (
            $this->applicationRepository !== null &&
            method_exists(
                $this->applicationRepository,
                'rejectPendingByTraining'
            )
        ) {

            $this->applicationRepository->rejectPendingByTraining(
                $trainingId
            );
        }


        $this->logAdminAction(
            'training_closed',
            $trainingId,
            $context,
            [
                'status' => 'closed'
            ]
        );


        return $result;
    }

    public function changeStatus(
        int $trainingId,
        string $status,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $trainingId =
            $this->validateId(
                $trainingId,
                'training ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        $allowedStatuses = [
            'draft',
            'pending',
            'active',
            'inactive',
            'closed',
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
                'Unsupported training status.'
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
                        $trainingId,
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
                        $trainingId,
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
                        $trainingId,
                        [
                            'status' => $status
                        ]
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'training_status_changed',
                $trainingId,
                $context,
                [
                    'status' => $status
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change training status.'
            );
        }
    }

    public function delete(
        int $trainingId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);
        $currentUser = function_exists('auth_user') ? auth_user() : null;
        security_require_admin_reauth(is_array($currentUser) ? $currentUser : null, $context, 'training_delete');

        $trainingId =
            $this->validateId(
                $trainingId,
                'training ID'
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
                        $trainingId
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'softDelete'
                )
            ) {
                $result =
                    $this->repository->softDelete(
                        $trainingId
                    );
            } else {
                return false;
            }

            $this->logAdminAction(
                'training_deleted',
                $trainingId,
                $context
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to delete training.'
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
            $this->normalizeTrainingFilters(
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
                    'searchTrainings'
                )
            ) {
                return (array)
                    $this->repository
                        ->searchTrainings(
                            $query,
                            $filters
                        );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to search trainings.'
            );
        }

        return [];
    }

    public function listApplications(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $filters =
            $this->normalizeApplicationFilters(
                $filters
            );

        if ($this->applicationRepository === null) {
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
                    $this->applicationRepository,
                    'getAll'
                )
            ) {
                $result =
                    $this->applicationRepository
                        ->getAll(
                            $filters
                        );
            } elseif (
                method_exists(
                    $this->applicationRepository,
                    'all'
                )
            ) {
                $result =
                    $this->applicationRepository
                        ->all(
                            $filters
                        );
            } elseif (
                method_exists(
                    $this->applicationRepository,
                    'paginate'
                )
            ) {
                $result =
                    $this->applicationRepository
                        ->paginate(
                            $filters
                        );
            }

            return $this->normalizeListResult(
                $result,
                $filters
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load training applications.'
            );
        }
    }

    public function showApplication(
        int $applicationId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $applicationId =
            $this->validateId(
                $applicationId,
                'application ID'
            );

        if (
            $this->applicationRepository === null
        ) {
            return null;
        }

        try {
            if (
                method_exists(
                    $this->applicationRepository,
                    'find'
                )
            ) {
                return
                    $this->applicationRepository
                        ->find(
                            $applicationId
                        );
            }

            if (
                method_exists(
                    $this->applicationRepository,
                    'findById'
                )
            ) {
                return
                    $this->applicationRepository
                        ->findById(
                            $applicationId
                        );
            }

            if (
                method_exists(
                    $this->applicationRepository,
                    'getById'
                )
            ) {
                return
                    $this->applicationRepository
                        ->getById(
                            $applicationId
                        );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load application.'
            );
        }

        return null;
    }

    public function approveApplication(
        int $applicationId,
        array $context = []
    ): mixed {
        return $this->changeApplicationStatus(
            $applicationId,
            'approved',
            $context
        );
    }

    public function rejectApplication(
        int $applicationId,
        string $reason = '',
        array $context = []
    ): mixed {
        if (trim($reason) === '') {
            throw new InvalidArgumentException(
                'A rejection reason is required.'
            );
        }

        return $this->changeApplicationStatus(
            $applicationId,
            'rejected',
            $context,
            $reason
        );
    }

    public function pendingApplication(
        int $applicationId,
        array $context = []
    ): mixed {
        return $this->changeApplicationStatus(
            $applicationId,
            'pending',
            $context
        );
    }

    public function changeApplicationStatus(
        int $applicationId,
        string $status,
        array $context = [],
        string $note = ''
    ): mixed {
        $this->assertAdmin($context);

        $applicationId =
            $this->validateId(
                $applicationId,
                'application ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        $allowedStatuses = [
            'pending',
            'approved',
            'rejected',
            'cancelled',
            'completed'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported application status.'
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

        if (
            $this->applicationRepository === null
        ) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->applicationRepository,
                    'changeStatus'
                )
            ) {
                $result =
                    $this->applicationRepository
                        ->changeStatus(
                            $applicationId,
                            $status,
                            $note
                        );
            } elseif (
                method_exists(
                    $this->applicationRepository,
                    'setStatus'
                )
            ) {
                $result =
                    $this->applicationRepository
                        ->setStatus(
                            $applicationId,
                            $status,
                            $note
                        );
            } elseif (
                method_exists(
                    $this->applicationRepository,
                    'update'
                )
            ) {
                $data = [
                    'status' => $status
                ];

                if ($note !== '') {
                    $data['admin_note'] =
                        $note;
                }

                $result =
                    $this->applicationRepository
                        ->update(
                            $applicationId,
                            $data
                        );
            } else {
                return false;
            }

            $this->logAdminAction(
                'application_status_changed',
                $applicationId,
                $context,
                [
                    'status' => $status,
                    'note' => $note
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change application status.'
            );
        }
    }

    public function listSessions(
        array $filters = [],
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $filters =
            $this->normalizeSessionFilters(
                $filters
            );

        if ($this->sessionRepository === null) {
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
                    $this->sessionRepository,
                    'getAll'
                )
            ) {
                $result =
                    $this->sessionRepository
                        ->getAll(
                            $filters
                        );
            } elseif (
                method_exists(
                    $this->sessionRepository,
                    'all'
                )
            ) {
                $result =
                    $this->sessionRepository
                        ->all(
                            $filters
                        );
            } elseif (
                method_exists(
                    $this->sessionRepository,
                    'paginate'
                )
            ) {
                $result =
                    $this->sessionRepository
                        ->paginate(
                            $filters
                        );
            }

            return $this->normalizeListResult(
                $result,
                $filters
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load training sessions.'
            );
        }
    }

    public function showSession(
        int $sessionId,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $sessionId =
            $this->validateId(
                $sessionId,
                'session ID'
            );

        if ($this->sessionRepository === null) {
            return null;
        }

        try {
            if (
                method_exists(
                    $this->sessionRepository,
                    'find'
                )
            ) {
                return
                    $this->sessionRepository
                        ->find(
                            $sessionId
                        );
            }

            if (
                method_exists(
                    $this->sessionRepository,
                    'findById'
                )
            ) {
                return
                    $this->sessionRepository
                        ->findById(
                            $sessionId
                        );
            }

            if (
                method_exists(
                    $this->sessionRepository,
                    'getById'
                )
            ) {
                return
                    $this->sessionRepository
                        ->getById(
                            $sessionId
                        );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load training session.'
            );
        }

        return null;
    }

    public function changeSessionStatus(
        int $sessionId,
        string $status,
        array $context = []
    ): mixed {
        $this->assertAdmin($context);

        $sessionId =
            $this->validateId(
                $sessionId,
                'session ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        $allowedStatuses = [
            'scheduled',
            'active',
            'completed',
            'cancelled',
            'postponed'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported session status.'
            );
        }

        if ($this->sessionRepository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->sessionRepository,
                    'changeStatus'
                )
            ) {
                $result =
                    $this->sessionRepository
                        ->changeStatus(
                            $sessionId,
                            $status
                        );
            } elseif (
                method_exists(
                    $this->sessionRepository,
                    'setStatus'
                )
            ) {
                $result =
                    $this->sessionRepository
                        ->setStatus(
                            $sessionId,
                            $status
                        );
            } elseif (
                method_exists(
                    $this->sessionRepository,
                    'update'
                )
            ) {
                $result =
                    $this->sessionRepository
                        ->update(
                            $sessionId,
                            [
                                'status' => $status
                            ]
                        );
            } else {
                return false;
            }

            $this->logAdminAction(
                'training_session_status_changed',
                $sessionId,
                $context,
                [
                    'status' => $status
                ]
            );

            return $result;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change session status.'
            );
        }
    }

    public function statistics(
        array $context = []
    ): array {
        $this->assertAdmin($context);

        $default = [
            'trainings_total' => 0,
            'trainings_active' => 0,
            'trainings_closed' => 0,
            'applications_total' => 0,
            'applications_pending' => 0,
            'applications_approved' => 0,
            'applications_rejected' => 0,
            'sessions_total' => 0,
            'sessions_completed' => 0,
            'sessions_cancelled' => 0
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
                    (array)
                    $this->repository
                        ->getStatistics();

                return array_merge(
                    $default,
                    $stats
                );
            }

            if (
                $this->repository !== null &&
                method_exists(
                    $this->repository,
                    'statistics'
                )
            ) {
                $stats =
                    (array)
                    $this->repository
                        ->statistics();

                return array_merge(
                    $default,
                    $stats
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load training statistics.'
            );
        }

        if ($this->repository !== null) {
            $default['trainings_total'] =
                $this->countTrainings(
                    [],
                    $context
                );

            $default['trainings_active'] =
                $this->countTrainings(
                    ['status' => 'active'],
                    $context
                );

            $default['trainings_closed'] =
                $this->countTrainings(
                    ['status' => 'closed'],
                    $context
                );
        }

        if ($this->applicationRepository !== null) {
            $default['applications_total'] =
                $this->countApplications(
                    [],
                    $context
                );

            $default['applications_pending'] =
                $this->countApplications(
                    ['status' => 'pending'],
                    $context
                );

            $default['applications_approved'] =
                $this->countApplications(
                    ['status' => 'approved'],
                    $context
                );

            $default['applications_rejected'] =
                $this->countApplications(
                    ['status' => 'rejected'],
                    $context
                );
        }

        if ($this->sessionRepository !== null) {
            $default['sessions_total'] =
                $this->countSessions(
                    [],
                    $context
                );

            $default['sessions_completed'] =
                $this->countSessions(
                    ['status' => 'completed'],
                    $context
                );

            $default['sessions_cancelled'] =
                $this->countSessions(
                    ['status' => 'cancelled'],
                    $context
                );
        }

        return $default;
    }

    public function countTrainings(
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
                    'countTrainings'
                )
            ) {
                return max(
                    0,
                    (int)
                    $this->repository
                        ->countTrainings(
                            $filters
                        )
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to count trainings.'
            );
        }

        return 0;
    }

    public function countApplications(
        array $filters = [],
        array $context = []
    ): int {
        $this->assertAdmin($context);

        if (
            $this->applicationRepository === null
        ) {
            return 0;
        }

        try {
            if (
                method_exists(
                    $this->applicationRepository,
                    'count'
                )
            ) {
                return max(
                    0,
                    (int)
                    $this->applicationRepository
                        ->count(
                            $filters
                        )
                );
            }

            if (
                method_exists(
                    $this->applicationRepository,
                    'countApplications'
                )
            ) {
                return max(
                    0,
                    (int)
                    $this->applicationRepository
                        ->countApplications(
                            $filters
                        )
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to count applications.'
            );
        }

        return 0;
    }

    public function countSessions(
        array $filters = [],
        array $context = []
    ): int {
        $this->assertAdmin($context);

        if ($this->sessionRepository === null) {
            return 0;
        }

        try {
            if (
                method_exists(
                    $this->sessionRepository,
                    'count'
                )
            ) {
                return max(
                    0,
                    (int)
                    $this->sessionRepository->count(
                        $filters
                    )
                );
            }

            if (
                method_exists(
                    $this->sessionRepository,
                    'countSessions'
                )
            ) {
                return max(
                    0,
                    (int)
                    $this->sessionRepository
                        ->countSessions(
                            $filters
                        )
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to count sessions.'
            );
        }

        return 0;
    }

    protected function normalizeTrainingFilters(
        array $filters
    ): array {
        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'company_id',
            'trainer_id',
            'category_id',
            'from',
            'to',
            'sort',
            'order'
        ];

        return $this->normalizeFilters(
            $filters,
            $allowed
        );
    }

    protected function normalizeApplicationFilters(
        array $filters
    ): array {
        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'training_id',
            'student_id',
            'company_id',
            'from',
            'to',
            'sort',
            'order'
        ];

        return $this->normalizeFilters(
            $filters,
            $allowed
        );
    }

    protected function normalizeSessionFilters(
        array $filters
    ): array {
        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'training_id',
            'trainer_id',
            'from',
            'to',
            'sort',
            'order'
        ];

        return $this->normalizeFilters(
            $filters,
            $allowed
        );
    }

    protected function normalizeFilters(
        array $filters,
        array $allowed
    ): array {
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
                (int)
                (
                    $result['page'] ?? 1
                )
            );

        $result['limit'] =
            min(
                100,
                max(
                    1,
                    (int)
                    (
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
                (int)
                (
                    $filters['page'] ?? 1
                )
            );

        $limit =
            min(
                100,
                max(
                    1,
                    (int)
                    (
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

    protected function sanitizeTrainingData(
        array $data
    ): array {
        $allowed = [
            'title',
            'description',
            'category_id',
            'company_id',
            'trainer_id',
            'capacity',
            'price',
            'start_date',
            'end_date',
            'location',
            'status',
            'requirements',
            'duration',
            'duration_hours',
            'image',
            'attachment'
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
            (int)
            (
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
            'target_id' => $targetId,
            'admin_id' =>
                $this->getAdminId(
                    $context
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
                    (string)
                    (
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
            !$this->isAuthorized(
                $context
            )
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

    public function getApplicationRepository(): mixed
    {
        return $this->applicationRepository;
    }

    public function setApplicationRepository(
        mixed $repository
    ): self {
        $this->applicationRepository =
            $repository;

        return $this;
    }

    public function getSessionRepository(): mixed
    {
        return $this->sessionRepository;
    }

    public function setSessionRepository(
        mixed $repository
    ): self {
        $this->sessionRepository =
            $repository;

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function training_admin_list(
    array $filters = [],
    array $context = []
): array {
    return
        (new TrainingAdminService())
            ->listTrainings(
                $filters,
                $context
            );
}

if (!function_exists('training_admin_show')) {
    function training_admin_show(
        int $trainingId,
        array $context = []
    ): mixed {
        return
            (new TrainingAdminService())
                ->show(
                    $trainingId,
                    $context
                );
    }
}

if (!function_exists('training_admin_update')) {
    function training_admin_update(
        int $trainingId,
        array $data,
        array $context = []
    ): mixed {
        return
            (new TrainingAdminService())
                ->update(
                    $trainingId,
                    $data,
                    $context
                );
    }
}

function training_admin_activate(
    int $trainingId,
    array $context = []
): mixed {
    return
        (new TrainingAdminService())
            ->activate(
                $trainingId,
                $context
            );
}

function training_admin_deactivate(
    int $trainingId,
    array $context = []
): mixed {
    return
        (new TrainingAdminService())
            ->deactivate(
                $trainingId,
                $context
            );
}

function training_admin_close(
    int $trainingId,
    array $context = []
): mixed {
    return
        (new TrainingAdminService())
            ->close(
                $trainingId,
                $context
            );
}

function training_admin_list_applications(
    array $filters = [],
    array $context = []
): array {
    return
        (new TrainingAdminService())
            ->listApplications(
                $filters,
                $context
            );
}

function training_admin_approve_application(
    int $applicationId,
    array $context = []
): mixed {
    return
        (new TrainingAdminService())
            ->approveApplication(
                $applicationId,
                $context
            );
}

function training_admin_reject_application(
    int $applicationId,
    string $reason,
    array $context = []
): mixed {
    return
        (new TrainingAdminService())
            ->rejectApplication(
                $applicationId,
                $reason,
                $context
            );
}

function training_admin_list_sessions(
    array $filters = [],
    array $context = []
): array {
    return
        (new TrainingAdminService())
            ->listSessions(
                $filters,
                $context
            );
}
