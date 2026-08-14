<?php

/**
 * MASAR - Company Admin Controller
 *
 * Administrative controller responsible for company management.
 *
 * Controller
 *     ↓
 * CompanyAdminService
 *     ↓
 * CompanyRepository / CompanyApprovalRepository
 */


$service_file =
    __DIR__ .
    '/../services/company_admin_service.php';

if (file_exists($service_file)) {
    require_once $service_file;
}


class CompanyAdminController
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
                'CompanyAdminService'
            )
        ) {

            return new CompanyAdminService();
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Index / List Companies
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
                    'getCompanies'
                )
            ) {

                return $this->success(
                    $this->service->getCompanies(
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load companies.'
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
    | Show Company
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


        $companyId =
            $this->getCompanyId(
                $request
            );


        if (
            $companyId <= 0
        ) {

            return $this->validationError(
                [
                    'company_id' =>
                        'A valid company_id is required.'
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
                        $companyId
                    )
                );
            }


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'getCompany'
                )
            ) {

                return $this->success(
                    $this->service->getCompany(
                        $companyId
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
                        $companyId
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load company.'
            );
        }


        return $this->error(
            'Company service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Company
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
            $this->extractCompanyData(
                $request
            );


        $validation =
            $this->validateCompanyData(
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
                    'Company created successfully.'
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
                    'Company created successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to create company.'
            );
        }


        return $this->error(
            'Company creation service is unavailable.'
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
    | Update Company
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


        $companyId =
            $this->getCompanyId(
                $request
            );


        if (
            $companyId <= 0
        ) {

            return $this->validationError(
                [
                    'company_id' =>
                        'A valid company_id is required.'
                ]
            );
        }


        $data =
            $this->extractCompanyData(
                $request
            );


        $validation =
            $this->validateCompanyData(
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
                        $companyId,
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'Company updated successfully.'
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
                        $companyId,
                        $data
                    );


                return $this->actionResult(
                    $result,
                    'Company updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update company.'
            );
        }


        return $this->error(
            'Company update service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Company
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


        $companyId =
            $this->getCompanyId(
                $request
            );


        if (
            $companyId <= 0
        ) {

            return $this->validationError(
                [
                    'company_id' =>
                        'A valid company_id is required.'
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
                        $companyId
                    );


                return $this->actionResult(
                    $result,
                    'Company deleted successfully.'
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
                        $companyId
                    );


                return $this->actionResult(
                    $result,
                    'Company deleted successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to delete company.'
            );
        }


        return $this->error(
            'Company deletion service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve Company
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
    | Reject Company
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
    | Pending Companies
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


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    'pendingCompanies'
                )
            ) {

                return $this->success(
                    $this->service->pendingCompanies(
                        $filters
                    )
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to load pending companies.'
            );
        }


        return $this->success(
            []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Suspend Company
    |--------------------------------------------------------------------------
    */

    public function suspend(
        array $request = []
    ): array {

        return $this->statusAction(
            $request,
            'suspend',
            'suspended'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate Company
    |--------------------------------------------------------------------------
    */

    public function activate(
        array $request = []
    ): array {

        return $this->statusAction(
            $request,
            'activate',
            'active'
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


        $companyId =
            $this->getCompanyId(
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
            $companyId <= 0
        ) {

            return $this->validationError(
                [
                    'company_id' =>
                        'A valid company_id is required.'
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
                        $companyId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Company status updated successfully.'
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
                        $companyId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Company status updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to change company status.'
            );
        }


        return $this->error(
            'Company status service is unavailable.'
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


        $companyId =
            $this->getCompanyId(
                $request
            );


        if (
            $companyId <= 0
        ) {

            return $this->validationError(
                [
                    'company_id' =>
                        'A valid company_id is required.'
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
                        $companyId,
                        $reason
                    );


                return $this->actionResult(
                    $result,
                    'Company approval status updated successfully.'
                );
            }


            $method =
                $action === 'approve'
                    ? 'approveCompany'
                    : 'rejectCompany';


            if (
                $this->service !== null &&
                method_exists(
                    $this->service,
                    $method
                )
            ) {

                $result =
                    $this->service->{$method}(
                        $companyId,
                        $reason
                    );


                return $this->actionResult(
                    $result,
                    'Company approval status updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update company approval.'
            );
        }


        return $this->error(
            'Company approval service is unavailable.'
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


        $companyId =
            $this->getCompanyId(
                $request
            );


        if (
            $companyId <= 0
        ) {

            return $this->validationError(
                [
                    'company_id' =>
                        'A valid company_id is required.'
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
                        $companyId
                    );


                return $this->actionResult(
                    $result,
                    'Company status updated successfully.'
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
                        $companyId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Company status updated successfully.'
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
                        $companyId,
                        $status
                    );


                return $this->actionResult(
                    $result,
                    'Company status updated successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to update company status.'
            );
        }


        return $this->error(
            'Company status service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Companies
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
                'Unable to search companies.'
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


        $companyIds =
            $request['company_ids']
            ?? [];


        $action =
            trim(
                (string) (
                    $request['action']
                    ?? ''
                )
            );


        if (
            !is_array($companyIds) ||
            empty($companyIds)
        ) {

            return $this->validationError(
                [
                    'company_ids' =>
                        'At least one company_id is required.'
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
            'activate',
            'deactivate',
            'suspend',
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


        $companyIds =
            array_values(
                array_filter(
                    array_map(
                        'intval',
                        $companyIds
                    ),
                    fn ($id) =>
                        $id > 0
                )
            );


        if (
            empty($companyIds)
        ) {

            return $this->validationError(
                [
                    'company_ids' =>
                        'No valid company IDs were provided.'
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
                        $companyIds
                    );


                return $this->actionResult(
                    $result,
                    'Bulk company action completed successfully.'
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
                        $companyIds
                    );


                return $this->actionResult(
                    $result,
                    'Bulk company action completed successfully.'
                );
            }

        } catch (Throwable $e) {

            return $this->error(
                'Unable to execute bulk company action.'
            );
        }


        return $this->error(
            'Bulk company action service is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Extract Company Data
    |--------------------------------------------------------------------------
    */

    protected function extractCompanyData(
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
            'legal_name',
            'commercial_name',
            'registration_number',
            'tax_number',
            'email',
            'phone',
            'website',
            'address',
            'city',
            'country',
            'description',
            'status',
            'approval_status',
            'owner_id',
            'representative_id'
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
    | Validate Company Data
    |--------------------------------------------------------------------------
    */

    protected function validateCompanyData(
        array $data,
        bool $creating
    ): array {

        $errors = [];


        if (
            $creating &&
            empty(
                trim(
                    (string) (
                        $data['name']
                        ?? $data['legal_name']
                        ?? ''
                    )
                )
            )
        ) {

            $errors['name'] =
                'Company name is required.';
        }


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


        if (
            isset(
                $data['owner_id']
            ) &&
            $data['owner_id'] !== '' &&
            (int) $data['owner_id'] <= 0
        ) {

            $errors['owner_id'] =
                'A valid owner_id is required.';
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
            'city',
            'country',
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
    | Company ID
    |--------------------------------------------------------------------------
    */

    protected function getCompanyId(
        array $request
    ): int {

        return max(
            0,
            (int) (
                $request['company_id']
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

function company_admin_index(
    array $request = []
): array {

    return
        (new CompanyAdminController())
            ->index(
                $request
            );
}


function company_admin_show(
    array $request = []
): array {

    return
        (new CompanyAdminController())
            ->show(
                $request
            );
}


function company_admin_store(
    array $request = []
): array {

    return
        (new CompanyAdminController())
            ->store(
                $request
            );
}


function company_admin_update(
    array $request = []
): array {

    return
        (new CompanyAdminController())
            ->update(
                $request
            );
}


function company_admin_delete(
    array $request = []
): array {

    return
        (new CompanyAdminController())
            ->destroy(
                $request
            );
}


function company_admin_approve(
    array $request = []
): array {

    return
        (new CompanyAdminController())
            ->approve(
                $request
            );
}


function company_admin_reject(
    array $request = []
): array {

    return
        (new CompanyAdminController())
            ->reject(
                $request
            );
}
