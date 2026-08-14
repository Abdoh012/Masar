<?php

/**
 * MASAR - Training Admin Controller
 *
 * Administrative controller responsible for training management.
 *
 * Controller
 *     ↓
 * TrainingAdminService
 *     ↓
 * TrainingRepository / ApplicationRepository / TrainingSessionRepository
 */


$service_file =
    __DIR__ .
    '/../services/training_admin_service.php';

if (file_exists($service_file)) {
    require_once $service_file;
}


class TrainingAdminController
{
    protected mixed $service = null;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        mixed $service = null
    ) {

        $this->service =
            $service
            ?? $this->resolveService();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Service
    |--------------------------------------------------------------------------
    */

    protected function resolveService(): mixed
    {
        if (
            class_exists(
                'TrainingAdminService'
            )
        ) {

            return new TrainingAdminService();
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $filters =
            $this->extractFilters(
                $request
            );


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'index'
                )
            ) {

                return $this->success(
                    $this->service->index(
                        $filters
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'list'
                )
            ) {

                return $this->success(
                    $this->service->list(
                        $filters
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getTrainings'
                )
            ) {

                return $this->success(
                    $this->service->getTrainings(
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load trainings.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | List Alias
    |--------------------------------------------------------------------------
    */

    public function list(
        array $request = []
    ): array {

        return $this->index(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Training
    |--------------------------------------------------------------------------
    */

    public function show(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        if (
            $trainingId <= 0
        ) {

            return $this->validationError(
                [
                    'training_id' =>
                        'A valid training_id is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'show'
                )
            ) {

                return $this->success(
                    $this->service->show(
                        $trainingId
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getTraining'
                )
            ) {

                return $this->success(
                    $this->service->getTraining(
                        $trainingId
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'find'
                )
            ) {

                return $this->success(
                    $this->service->find(
                        $trainingId
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load training.'
            );
        }


        return $this->error(
            'Training service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Training
    |--------------------------------------------------------------------------
    */

    public function store(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $data =
            $this->extractTrainingData(
                $request
            );


        $validation =
            $this->validateTrainingData(
                $data,
                true
            );


        if (
            !$validation['valid']
        ) {

            return $this->validationError(
                $validation['errors']
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'create'
                )
            ) {

                $result =
                    $this->service->create(
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'Training created successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'store'
                )
            ) {

                $result =
                    $this->service->store(
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'Training created successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to create training.'
            );
        }


        return $this->error(
            'Training creation service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Training
    |--------------------------------------------------------------------------
    */

    public function update(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        if (
            $trainingId <= 0
        ) {

            return $this->validationError(
                [
                    'training_id' =>
                        'A valid training_id is required.'
                ]
            );
        }


        $data =
            $this->extractTrainingData(
                $request
            );


        $validation =
            $this->validateTrainingData(
                $data,
                false
            );


        if (
            !$validation['valid']
        ) {

            return $this->validationError(
                $validation['errors']
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'update'
                )
            ) {

                $result =
                    $this->service->update(
                        $trainingId,
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'Training updated successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'edit'
                )
            ) {

                $result =
                    $this->service->edit(
                        $trainingId,
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'Training updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update training.'
            );
        }


        return $this->error(
            'Training update service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Training
    |--------------------------------------------------------------------------
    */

    public function destroy(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        if (
            $trainingId <= 0
        ) {

            return $this->validationError(
                [
                    'training_id' =>
                        'A valid training_id is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'delete'
                )
            ) {

                $result =
                    $this->service->delete(
                        $trainingId
                    );


                return $this->actionResult(
                    $result,
                    'Training deleted successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'destroy'
                )
            ) {

                $result =
                    $this->service->destroy(
                        $trainingId
                    );


                return $this->actionResult(
                    $result,
                    'Training deleted successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to delete training.'
            );
        }


        return $this->error(
            'Training deletion service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve Training
    |--------------------------------------------------------------------------
    */

    public function approve(
        array $request = []
    ): array {

        return $this->approvalAction(
            $request,
            'approve'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject Training
    |--------------------------------------------------------------------------
    */

    public function reject(
        array $request = []
    ): array {

        return $this->approvalAction(
            $request,
            'reject'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pending Trainings
    |--------------------------------------------------------------------------
    */

    public function pending(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $filters =
            $this->extractFilters(
                $request
            );


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'pending'
                )
            ) {

                return $this->success(
                    $this->service->pending(
                        $filters
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getPending'
                )
            ) {

                return $this->success(
                    $this->service->getPending(
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load pending trainings.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Publish Training
    |--------------------------------------------------------------------------
    */

    public function publish(
        array $request = []
    ): array {

        return $this->statusAction(
            $request,
            'publish',
            'published'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Unpublish Training
    |--------------------------------------------------------------------------
    */

    public function unpublish(
        array $request = []
    ): array {

        return $this->statusAction(
            $request,
            'unpublish',
            'draft'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Close Training
    |--------------------------------------------------------------------------
    */

    public function close(
        array $request = []
    ): array {

        return $this->statusAction(
            $request,
            'close',
            'closed'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Change Status
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        $status =
            strtolower(
                trim(
                    (string) (
                        $request['status']
                        ?? ''
                    )
                )
            );


        if (
            $trainingId <= 0
        ) {

            return $this->validationError(
                [
                    'training_id' =>
                        'A valid training_id is required.'
                ]
            );
        }


        if (
            $status === ''
        ) {

            return $this->validationError(
                [
                    'status' =>
                        'Status is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'changeStatus'
                )
            ) {

                $result =
                    $this->service->changeStatus(
                        $trainingId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Training status updated successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'setStatus'
                )
            ) {

                $result =
                    $this->service->setStatus(
                        $trainingId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Training status updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to change training status.'
            );
        }


        return $this->error(
            'Training status service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Applications
    |--------------------------------------------------------------------------
    */

    public function applications(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        $filters =
            $this->extractFilters(
                $request
            );


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'applications'
                )
            ) {

                return $this->success(
                    $this->service->applications(
                        $trainingId,
                        $filters
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getApplications'
                )
            ) {

                return $this->success(
                    $this->service->getApplications(
                        $trainingId,
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load training applications.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Sessions
    |--------------------------------------------------------------------------
    */

    public function sessions(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        $filters =
            $this->extractFilters(
                $request
            );


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'sessions'
                )
            ) {

                return $this->success(
                    $this->service->sessions(
                        $trainingId,
                        $filters
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getSessions'
                )
            ) {

                return $this->success(
                    $this->service->getSessions(
                        $trainingId,
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load training sessions.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    public function search(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

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


        if (
            $query === ''
        ) {

            return $this->validationError(
                [
                    'query' =>
                        'Search query is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'search'
                )
            ) {

                return $this->success(
                    $this->service->search(
                        $query,
                        $this->extractFilters(
                            $request
                        )
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to search trainings.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Bulk Action
    |--------------------------------------------------------------------------
    */

    public function bulk(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingIds =
            $request['training_ids']
            ?? [];


        $action =
            trim(
                (string) (
                    $request['action']
                    ?? ''
                )
            );


        if (
            !is_array($trainingIds) ||
            empty($trainingIds)
        ) {

            return $this->validationError(
                [
                    'training_ids' =>
                        'At least one training_id is required.'
                ]
            );
        }


        if (
            $action === ''
        ) {

            return $this->validationError(
                [
                    'action' =>
                        'Bulk action is required.'
                ]
            );
        }


        $allowedActions = [
            'approve',
            'reject',
            'publish',
            'unpublish',
            'close',
            'delete'
        ];


        if (
            !in_array(
                $action,
                $allowedActions,
                true
            )
        ) {

            return $this->validationError(
                [
                    'action' =>
                        'Unsupported bulk action.'
                ]
            );
        }


        $trainingIds =
            array_values(
                array_filter(
                    array_map(
                        'intval',
                        $trainingIds
                    ),
                    fn ($id) =>
                        $id > 0
                )
            );


        if (
            empty($trainingIds)
        ) {

            return $this->validationError(
                [
                    'training_ids' =>
                        'No valid training IDs were provided.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'bulk'
                )
            ) {

                $result =
                    $this->service->bulk(
                        $action,
                        $trainingIds
                    );


                return $this->actionResult(
                    $result,
                    'Bulk training action completed successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'bulkAction'
                )
            ) {

                $result =
                    $this->service->bulkAction(
                        $action,
                        $trainingIds
                    );


                return $this->actionResult(
                    $result,
                    'Bulk training action completed successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to execute bulk training action.'
            );
        }


        return $this->error(
            'Bulk training action service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Approval Action
    |--------------------------------------------------------------------------
    */

    protected function approvalAction(
        array $request,
        string $action
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        if (
            $trainingId <= 0
        ) {

            return $this->validationError(
                [
                    'training_id' =>
                        'A valid training_id is required.'
                ]
            );
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
                method_exists(
                    $this->service,
                    $action
                )
            ) {

                $result =
                    $this->service->{$action}(
                        $trainingId,
                        $reason
                    );


                return $this->actionResult(
                    $result,
                    'Training approval status updated successfully.'
                );
            }


            $method =
                $action === 'approve'
                    ? 'approveTraining'
                    : 'rejectTraining';


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    $method
                )
            ) {

                $result =
                    $this->service->{$method}(
                        $trainingId,
                        $reason
                    );


                return $this->actionResult(
                    $result,
                    'Training approval status updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update training approval.'
            );
        }


        return $this->error(
            'Training approval service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status Action
    |--------------------------------------------------------------------------
    */

    protected function statusAction(
        array $request,
        string $action,
        string $status
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $trainingId =
            $this->getTrainingId(
                $request
            );


        if (
            $trainingId <= 0
        ) {

            return $this->validationError(
                [
                    'training_id' =>
                        'A valid training_id is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    $action
                )
            ) {

                $result =
                    $this->service->{$action}(
                        $trainingId
                    );


                return $this->actionResult(
                    $result,
                    'Training status updated successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'setStatus'
                )
            ) {

                $result =
                    $this->service->setStatus(
                        $trainingId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Training status updated successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'changeStatus'
                )
            ) {

                $result =
                    $this->service->changeStatus(
                        $trainingId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Training status updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update training status.'
            );
        }


        return $this->error(
            'Training status service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Extract Training Data
    |--------------------------------------------------------------------------
    */

    protected function extractTrainingData(
        array $request
    ): array {

        $source =
            isset(
                $request['data']
            ) &&
            is_array(
                $request['data']
            )
                ? $request['data']
                : $request;


        $allowed = [
            'title',
            'name',
            'description',
            'company_id',
            'trainer_id',
            'category_id',
            'location',
            'capacity',
            'start_date',
            'end_date',
            'application_deadline',
            'status',
            'approval_status',
            'price',
            'is_online',
            'online_url'
        ];


        $data = [];


        foreach (
            $allowed
            as $field
        ) {

            if (
                array_key_exists(
                    $field,
                    $source
                )
            ) {

                $data[$field] =
                    $source[$field];
            }
        }


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Training Data
    |--------------------------------------------------------------------------
    */

    protected function validateTrainingData(
        array $data,
        bool $creating
    ): array {

        $errors = [];


        if (
            $creating &&
            empty(
                trim(
                    (string) (
                        $data['title']
                        ?? $data['name']
                        ?? ''
                    )
                )
            )
        ) {

            $errors['title'] =
                'Training title is required.';
        }


        if (
            isset(
                $data['company_id']
            ) &&
            $data['company_id'] !== '' &&
            (int) $data['company_id'] <= 0
        ) {

            $errors['company_id'] =
                'A valid company_id is required.';
        }


        if (
            isset(
                $data['capacity']
            ) &&
            $data['capacity'] !== '' &&
            (int) $data['capacity'] < 1
        ) {

            $errors['capacity'] =
                'Capacity must be greater than zero.';
        }


        if (
            isset(
                $data['price']
            ) &&
            $data['price'] !== '' &&
            !is_numeric(
                $data['price']
            )
        ) {

            $errors['price'] =
                'Price must be numeric.';
        }


        return [
            'valid' =>
                empty($errors),

            'errors' =>
                $errors
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    protected function extractFilters(
        array $request
    ): array {

        $filters =
            $request['filters']
            ?? [];


        if (
            !is_array($filters)
        ) {

            $filters = [];
        }


        $allowed = [
            'page',
            'limit',
            'search',
            'status',
            'approval_status',
            'company_id',
            'trainer_id',
            'category_id',
            'from',
            'to',
            'sort',
            'order'
        ];


        $result = [];


        foreach (
            $allowed
            as $key
        ) {

            if (
                array_key_exists(
                    $key,
                    $filters
                )
            ) {

                $result[$key] =
                    $filters[$key];

            } elseif (
                array_key_exists(
                    $key,
                    $request
                )
            ) {

                $result[$key] =
                    $request[$key];
            }
        }


        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    protected function getTrainingId(
        array $request
    ): int {

        return max(
            0,
            (int) (
                $request['training_id']
                ?? $request['id']
                ?? 0
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

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
                        $this->buildContext(
                            $request
                        )
                    );

            } catch (Throwable $e) {

                return false;
            }
        }


        if (
            array_key_exists(
                'is_admin',
                $request
            )
        ) {

            return
                (bool)
                $request['is_admin'];
        }


        $role =
            strtolower(
                trim(
                    (string) (
                        $request['role']
                        ?? ''
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


    /*
    |--------------------------------------------------------------------------
    | Context
    |--------------------------------------------------------------------------
    */

    protected function buildContext(
        array $request
    ): array {

        return [
            'user_id' =>
                (int) (
                    $request['user_id']
                    ?? 0
                ),

            'role' =>
                $request['role']
                ?? null,

            'ip' =>
                $request['ip']
                ?? null,

            'request_id' =>
                $request['request_id']
                ?? null
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Action Result
    |--------------------------------------------------------------------------
    */

    protected function actionResult(
        mixed $result,
        string $message
    ): array {

        if (
            $result === false
        ) {

            return $this->error(
                'Operation failed.'
            );
        }


        return [
            'success' =>
                true,

            'message' =>
                $message,

            'data' =>
                $result
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    protected function success(
        mixed $data = []
    ): array {

        return [
            'success' =>
                true,

            'data' =>
                $data
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Error
    |--------------------------------------------------------------------------
    */

    protected function error(
        string $message,
        array $errors = []
    ): array {

        return [
            'success' =>
                false,

            'message' =>
                $message,

            'errors' =>
                $errors
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validation Error
    |--------------------------------------------------------------------------
    */

    protected function validationError(
        array $errors
    ): array {

        return [
            'success' =>
                false,

            'message' =>
                'Validation failed.',

            'errors' =>
                $errors
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Unauthorized
    |--------------------------------------------------------------------------
    */

    protected function unauthorized(): array
    {
        return [
            'success' =>
                false,

            'message' =>
                'Unauthorized.',

            'code' =>
                403
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Service Access
    |--------------------------------------------------------------------------
    */

    public function getService(): mixed
    {
        return $this->service;
    }


    /*
    |--------------------------------------------------------------------------
    | Set Service
    |--------------------------------------------------------------------------
    */

    public function setService(
        mixed $service
    ): self {

        $this->service =
            $service;

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function training_admin_index(
    array $request = []
): array {

    return
        (new TrainingAdminController())
            ->index(
                $request
            );
}


function training_admin_show(
    array $request = []
): array {

    return
        (new TrainingAdminController())
            ->show(
                $request
            );
}


function training_admin_store(
    array $request = []
): array {

    return
        (new TrainingAdminController())
            ->store(
                $request
            );
}


function training_admin_update(
    array $request = []
): array {

    return
        (new TrainingAdminController())
            ->update(
                $request
            );
}


function training_admin_delete(
    array $request = []
): array {

    return
        (new TrainingAdminController())
            ->destroy(
                $request
            );
}


function training_admin_approve(
    array $request = []
): array {

    return
        (new TrainingAdminController())
            ->approve(
                $request
            );
}


function training_admin_reject(
    array $request = []
): array {

    return
        (new TrainingAdminController())
            ->reject(
                $request
            );
}
