<?php

/**
 * MASAR - User Admin Controller
 *
 * Administrative controller responsible for user management.
 *
 * Controller
 *     ↓
 * UserAdminService
 *     ↓
 * UserRepository / AdminRepository
 */


$service_file =
    __DIR__ .
    '/../services/user_admin_service.php';

if (file_exists($service_file)) {
    require_once $service_file;
}


class UserAdminController
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
                'UserAdminService'
            )
        ) {

            return new UserAdminService();
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Index / List Users
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
                    'getUsers'
                )
            ) {

                return $this->success(
                    $this->service->getUsers(
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load users.'
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
    | Show User
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


        $userId =
            $this->getUserId(
                $request
            );


        if (
            $userId <= 0
        ) {

            return $this->validationError(
                [
                    'user_id' =>
                        'A valid user_id is required.'
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
                        $userId
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getUser'
                )
            ) {

                return $this->success(
                    $this->service->getUser(
                        $userId
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
                        $userId
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load user.'
            );
        }


        return $this->error(
            'User service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create User
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
            $this->extractUserData(
                $request
            );


        $validation =
            $this->validateUserData(
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
                    'User created successfully.'
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
                    'User created successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to create user.'
            );
        }


        return $this->error(
            'User creation service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Alias
    |--------------------------------------------------------------------------
    */

    public function create(
        array $request = []
    ): array {

        return $this->store(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update User
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


        $userId =
            $this->getUserId(
                $request
            );


        if (
            $userId <= 0
        ) {

            return $this->validationError(
                [
                    'user_id' =>
                        'A valid user_id is required.'
                ]
            );
        }


        $data =
            $this->extractUserData(
                $request
            );


        $validation =
            $this->validateUserData(
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
                        $userId,
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'User updated successfully.'
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
                        $userId,
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'User updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update user.'
            );
        }


        return $this->error(
            'User update service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete User
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


        $userId =
            $this->getUserId(
                $request
            );


        if (
            $userId <= 0
        ) {

            return $this->validationError(
                [
                    'user_id' =>
                        'A valid user_id is required.'
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
                        $userId
                    );


                return $this->actionResult(
                    $result,
                    'User deleted successfully.'
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
                        $userId
                    );


                return $this->actionResult(
                    $result,
                    'User deleted successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to delete user.'
            );
        }


        return $this->error(
            'User deletion service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate User
    |--------------------------------------------------------------------------
    */

    public function activate(
        array $request = []
    ): array {

        return $this->changeStatus(
            $request,
            'activate',
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deactivate User
    |--------------------------------------------------------------------------
    */

    public function deactivate(
        array $request = []
    ): array {

        return $this->changeStatus(
            $request,
            'deactivate',
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Suspend User
    |--------------------------------------------------------------------------
    */

    public function suspend(
        array $request = []
    ): array {

        return $this->changeStatus(
            $request,
            'suspend',
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Change Status
    |--------------------------------------------------------------------------
    */

    protected function changeStatus(
        array $request,
        string $action,
        bool $active
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $userId =
            $this->getUserId(
                $request
            );


        if (
            $userId <= 0
        ) {

            return $this->validationError(
                [
                    'user_id' =>
                        'A valid user_id is required.'
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
                        $userId
                    );


                return $this->actionResult(
                    $result,
                    'User status updated successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'setStatus'
                )
            ) {

                $status =
                    $active
                        ? 'active'
                        : 'inactive';


                $result =
                    $this->service->setStatus(
                        $userId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'User status updated successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'updateStatus'
                )
            ) {

                $status =
                    $active
                        ? 'active'
                        : 'inactive';


                $result =
                    $this->service->updateStatus(
                        $userId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'User status updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update user status.'
            );
        }


        return $this->error(
            'User status service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    public function resetPassword(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $userId =
            $this->getUserId(
                $request
            );


        if (
            $userId <= 0
        ) {

            return $this->validationError(
                [
                    'user_id' =>
                        'A valid user_id is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'resetPassword'
                )
            ) {

                $result =
                    $this->service->resetPassword(
                        $userId
                    );


                return $this->actionResult(
                    $result,
                    'Password reset successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to reset user password.'
            );
        }


        return $this->error(
            'Password reset service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Change Role
    |--------------------------------------------------------------------------
    */

    public function changeRole(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $userId =
            $this->getUserId(
                $request
            );


        $role =
            trim(
                (string) (
                    $request['role']
                    ?? ''
                )
            );


        if (
            $userId <= 0 ||
            $role === ''
        ) {

            return $this->validationError(
                [
                    'user_id' =>
                        'A valid user_id is required.',

                    'role' =>
                        'A valid role is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'changeRole'
                )
            ) {

                $result =
                    $this->service->changeRole(
                        $userId,
                        $role
                    );


                return $this->actionResult(
                    $result,
                    'User role updated successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'setRole'
                )
            ) {

                $result =
                    $this->service->setRole(
                        $userId,
                        $role
                    );


                return $this->actionResult(
                    $result,
                    'User role updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to change user role.'
            );
        }


        return $this->error(
            'Role management service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify User
    |--------------------------------------------------------------------------
    */

    public function verify(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $userId =
            $this->getUserId(
                $request
            );


        if (
            $userId <= 0
        ) {

            return $this->validationError(
                [
                    'user_id' =>
                        'A valid user_id is required.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'verify'
                )
            ) {

                $result =
                    $this->service->verify(
                        $userId
                    );


                return $this->actionResult(
                    $result,
                    'User verified successfully.'
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'verifyUser'
                )
            ) {

                $result =
                    $this->service->verifyUser(
                        $userId
                    );


                return $this->actionResult(
                    $result,
                    'User verified successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to verify user.'
            );
        }


        return $this->error(
            'User verification service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Users
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
                'Unable to search users.'
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


        $userIds =
            $request['user_ids']
            ?? [];


        $action =
            trim(
                (string) (
                    $request['action']
                    ?? ''
                )
            );


        if (
            !is_array($userIds) ||
            empty($userIds)
        ) {

            return $this->validationError(
                [
                    'user_ids' =>
                        'At least one user_id is required.'
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
            'activate',
            'deactivate',
            'suspend',
            'verify',
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


        $userIds =
            array_values(
                array_filter(
                    array_map(
                        'intval',
                        $userIds
                    ),
                    fn ($id) =>
                        $id > 0
                )
            );


        if (
            empty($userIds)
        ) {

            return $this->validationError(
                [
                    'user_ids' =>
                        'No valid user IDs were provided.'
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
                        $userIds
                    );


                return $this->actionResult(
                    $result,
                    'Bulk action completed successfully.'
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
                        $userIds
                    );


                return $this->actionResult(
                    $result,
                    'Bulk action completed successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to execute bulk action.'
            );
        }


        return $this->error(
            'Bulk action service is unavailable.'
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
                $this->getUserId(
                    $request
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
    | Extract User Data
    |--------------------------------------------------------------------------
    */

    protected function extractUserData(
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
            'name',
            'first_name',
            'last_name',
            'email',
            'username',
            'phone',
            'role',
            'status',
            'password',
            'is_active',
            'is_verified'
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
    | Validate User Data
    |--------------------------------------------------------------------------
    */

    protected function validateUserData(
        array $data,
        bool $creating
    ): array {

        $errors = [];


        if (
            $creating
        ) {

            if (
                empty(
                    trim(
                        (string) (
                            $data['name']
                            ?? ''
                        )
                    )
                ) &&
                empty(
                    trim(
                        (string) (
                            $data['username']
                            ?? ''
                        )
                    )
                )
            ) {

                $errors['name'] =
                    'User name or username is required.';
            }


            if (
                empty(
                    trim(
                        (string) (
                            $data['email']
                            ?? ''
                        )
                    )
                )
            ) {

                $errors['email'] =
                    'Email is required.';
            }


            if (
                !empty(
                    $data['email']
                    ?? ''
                ) &&
                !filter_var(
                    $data['email'],
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $errors['email'] =
                    'A valid email is required.';
            }


            if (
                empty(
                    $data['password']
                    ?? ''
                )
            ) {

                $errors['password'] =
                    'Password is required.';
            }
        } else {

            if (
                isset(
                    $data['email']
                ) &&
                $data['email'] !== '' &&
                !filter_var(
                    $data['email'],
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $errors['email'] =
                    'A valid email is required.';
            }
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
            'role',
            'sort',
            'order',
            'from',
            'to'
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
    | User ID
    |--------------------------------------------------------------------------
    */

    protected function getUserId(
        array $request
    ): int {

        return max(
            0,
            (int) (
                $request['user_id']
                ?? $request['id']
                ?? 0
            )
        );
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
    | Get Service
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

function user_admin_index(
    array $request = []
): array {

    return
        (new UserAdminController())
            ->index(
                $request
            );
}


function user_admin_show(
    array $request = []
): array {

    return
        (new UserAdminController())
            ->show(
                $request
            );
}


function user_admin_store(
    array $request = []
): array {

    return
        (new UserAdminController())
            ->store(
                $request
            );
}


function user_admin_update(
    array $request = []
): array {

    return
        (new UserAdminController())
            ->update(
                $request
            );
}


function user_admin_delete(
    array $request = []
): array {

    return
        (new UserAdminController())
            ->destroy(
                $request
            );
}
