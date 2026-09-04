<?php

/**
 * MASAR - Admin Controller
 *
 * Main controller for administrative operations.
 *
 * Controller
 *     ↓
 * AdminService
 *     ↓
 * AdminRepository
 */


$service_file =
    __DIR__ .
    '/../services/admin_service.php';

if (file_exists($service_file)) {
    require_once $service_file;
}


class AdminController
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
                'AdminService'
            )
        ) {

            return new AdminService();
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'dashboard'
                )
            ) {

                return $this->success(
                    $this->service->dashboard(
                        $this->buildContext(
                            $request
                        )
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getDashboard'
                )
            ) {

                return $this->success(
                    $this->service->getDashboard(
                        $this->buildContext(
                            $request
                        )
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load admin dashboard.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    public function statistics(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'statistics'
                )
            ) {

                return $this->success(
                    $this->service->statistics(
                        $this->buildContext(
                            $request
                        )
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getStatistics'
                )
            ) {

                return $this->success(
                    $this->service->getStatistics(
                        $this->buildContext(
                            $request
                        )
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load statistics.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Overview
    |--------------------------------------------------------------------------
    */

    public function overview(
        array $request = []
    ): array {

        return $this->dashboard(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Health
    |--------------------------------------------------------------------------
    */

    public function health(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'health'
                )
            ) {

                return $this->success(
                    $this->service->health(
                        $this->buildContext(
                            $request
                        )
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to check system health.'
            );
        }


        return $this->success(
            [
                'status' =>
                    'ok'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activity
    |--------------------------------------------------------------------------
    */

    public function activity(
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
                    'activity'
                )
            ) {

                return $this->success(
                    $this->service->activity(
                        $filters
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getActivity'
                )
            ) {

                return $this->success(
                    $this->service->getActivity(
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load admin activity.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Logs
    |--------------------------------------------------------------------------
    */

    public function logs(
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
                    'logs'
                )
            ) {

                return $this->success(
                    $this->service->logs(
                        $filters
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getLogs'
                )
            ) {

                return $this->success(
                    $this->service->getLogs(
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load admin logs.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    public function permissions(
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


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'permissions'
                )
            ) {

                return $this->success(
                    $this->service->permissions(
                        $userId
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getPermissions'
                )
            ) {

                return $this->success(
                    $this->service->getPermissions(
                        $userId
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load permissions.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    public function roles(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'roles'
                )
            ) {

                return $this->success(
                    $this->service->roles()
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getRoles'
                )
            ) {

                return $this->success(
                    $this->service->getRoles()
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load roles.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Assign Role
    |--------------------------------------------------------------------------
    */

    public function assignRole(
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
            $this->getRequestInt(
                $request,
                'user_id'
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
                    'assignRole'
                )
            ) {

                $result =
                    $this->service->assignRole(
                        $userId,
                        $role
                    );


                return $this->actionResult(
                    $result,
                    'Role assigned successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to assign role.'
            );
        }


        return $this->error(
            'Role assignment service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Role
    |--------------------------------------------------------------------------
    */

    public function removeRole(
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
            $this->getRequestInt(
                $request,
                'user_id'
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
                    'removeRole'
                )
            ) {

                $result =
                    $this->service->removeRole(
                        $userId,
                        $role
                    );


                return $this->actionResult(
                    $result,
                    'Role removed successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to remove role.'
            );
        }


        return $this->error(
            'Role removal service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    public function settings(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'settings'
                )
            ) {

                return $this->success(
                    $this->service->settings()
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getSettings'
                )
            ) {

                return $this->success(
                    $this->service->getSettings()
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load admin settings.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Settings
    |--------------------------------------------------------------------------
    */

    public function updateSettings(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $settings =
            $request['settings']
            ?? $request;


        if (
            !is_array($settings)
        ) {

            return $this->validationError(
                [
                    'settings' =>
                        'Settings must be an array.'
                ]
            );
        }


        try {

            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'updateSettings'
                )
            ) {

                $result =
                    $this->service->updateSettings(
                        $settings
                    );


                return $this->actionResult(
                    $result,
                    'Settings updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update admin settings.'
            );
        }


        return $this->error(
            'Settings service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Execute Generic Action
    |--------------------------------------------------------------------------
    */

    public function action(
        array $request = []
    ): array {

        if (
            !$this->isAuthorized(
                $request
            )
        ) {

            return $this->unauthorized();
        }


        $action =
            trim(
                (string) (
                    $request['action']
                    ?? ''
                )
            );


        if (
            $action === ''
        ) {

            return $this->validationError(
                [
                    'action' =>
                        'Action is required.'
                ]
            );
        }


        /*
         * Prevent arbitrary method execution.
         */

        $allowedActions = [
            'dashboard',
            'statistics',
            'health',
            'activity',
            'logs',
            'permissions',
            'roles'
        ];


        if (
            !in_array(
                $action,
                $allowedActions,
                true
            )
        ) {

            return $this->error(
                'Unsupported admin action.'
            );
        }


        return match ($action) {

            'dashboard' =>
                $this->dashboard(
                    $request
                ),

            'statistics' =>
                $this->statistics(
                    $request
                ),

            'health' =>
                $this->health(
                    $request
                ),

            'activity' =>
                $this->activity(
                    $request
                ),

            'logs' =>
                $this->logs(
                    $request
                ),

            'permissions' =>
                $this->permissions(
                    $request
                ),

            'roles' =>
                $this->roles(
                    $request
                ),

            default =>
                $this->error(
                    'Unsupported admin action.'
                )
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    protected function isAuthorized(
        array $request
    ): bool {

        /*
         * If the service owns authorization,
         * let it decide.
         */

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

        /*
         * Authorization is handled by the authentication middleware.
         * Absence of a locally-computed role must NOT automatically
         * expose admin methods. The middleware already validated
         * that the caller is an administrator.
         */

        return false;
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
            'type',
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
    | User ID
    |--------------------------------------------------------------------------
    */

    protected function getUserId(
        array $request
    ): int {

        return $this->getRequestInt(
            $request,
            'user_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Request Integer
    |--------------------------------------------------------------------------
    */

    protected function getRequestInt(
        array $request,
        string $key
    ): int {

        return max(
            0,
            (int) (
                $request[$key]
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
            is_array($result)
        ) {

            return [
                'success' =>
                    true,

                'message' =>
                    $message,

                'data' =>
                    $result
            ];
        }


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
    | Success Response
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
    | Error Response
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

function admin_dashboard(
    array $request = []
): array {

    return
        (new AdminController())
            ->dashboard(
                $request
            );
}


function admin_statistics(
    array $request = []
): array {

    return
        (new AdminController())
            ->statistics(
                $request
            );
}


function admin_health(
    array $request = []
): array {

    return
        (new AdminController())
            ->health(
                $request
            );
}


function admin_activity(
    array $request = []
): array {

    return
        (new AdminController())
            ->activity(
                $request
            );
}


function admin_logs(
    array $request = []
): array {

    return
        (new AdminController())
            ->logs(
                $request
            );
}
