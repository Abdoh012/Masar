<?php

/**
 * MASAR - Training Service
 *
 * Business logic for training opportunities.
 *
 * Native PHP - No OOP.
 *
 * Controller
 *     ↓
 * Service
 *     ↓
 * Repository
 *     ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/training_repository.php';
require_once __DIR__ . '/../repositories/application_repository.php';
require_once __DIR__ . '/../validators/training_validator.php';

require_once __DIR__ . '/../../../core/database/transaction.php';


/*
|--------------------------------------------------------------------------
| Create Training
|--------------------------------------------------------------------------
|
| Company creates a new training opportunity.
|
*/

function training_service_create(
    int $user_id,
    array $data
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Data
    |--------------------------------------------------------------------------
    */

    $validation =
        training_validator_create(
            $data
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'message' =>
                'Training data is invalid.',

            'errors' =>
                $validation['errors'],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Company Ownership / Profile
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company Approval
    |--------------------------------------------------------------------------
    */

    if (
        isset($company['approval_status'])
        &&
        $company['approval_status'] !== 'approved'
    ) {

        return [

            'success' => false,

            'message' =>
                'Your company must be approved before creating training opportunities.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Data
    |--------------------------------------------------------------------------
    */

    $training_data = [

        'company_id' =>
            (int) $company['company_id'],

        'title' =>
            trim(
                $data['title']
            ),

        'description' =>
            trim(
                $data['description']
            ),

        'training_type' =>
            isset($data['training_type'])
                ? trim($data['training_type'])
                : null,

        'work_mode' =>
            isset($data['work_mode'])
                ? trim($data['work_mode'])
                : null,

        'location' =>
            isset($data['location'])
                ? trim($data['location'])
                : null,

        'start_date' =>
            $data['start_date'] ?? null,

        'end_date' =>
            $data['end_date'] ?? null,

        'application_deadline' =>
            $data['application_deadline'] ?? null,

        'capacity' =>
            isset($data['capacity'])
                ? (int) $data['capacity']
                : null,

        'paid' =>
            isset($data['paid'])
                ? (int) $data['paid']
                : 0,

        'salary' =>
            isset($data['salary'])
                ? $data['salary']
                : null,

        'employment_possible' =>
            isset($data['employment_possible'])
                ? (int) $data['employment_possible']
                : 0,

        'status' =>
            'draft'
    ];


    /*
    |--------------------------------------------------------------------------
    | Create + Specialization Inheritance
    |--------------------------------------------------------------------------
    |
    | The listing row and its inherited specialization relationships are
    | written inside one database transaction: the company's current
    | company_specializations are copied into training_specializations,
    | so the company profile stays the single source of truth. If any
    | step fails, the whole creation is rolled back and no orphan
    | training_listings row remains.
    |
    */

    try {

        $training_id = db_transaction(
            function () use ($company, $training_data) {

                $new_training_id =
                    training_repository_create(
                        $training_data
                    );

                if (
                    !$new_training_id
                ) {
                    throw new RuntimeException(
                        'Unable to create training opportunity.'
                    );
                }

                $company_specialization_ids =
                    training_repository_get_company_specialization_ids(
                        (int) $company['company_id']
                    );

                if (!empty($company_specialization_ids)) {

                    training_repository_replace_specializations(
                        (int) $new_training_id,
                        $company_specialization_ids
                    );
                }

                return (int) $new_training_id;
            }
        );

    } catch (Throwable $exception) {

        return [

            'success' => false,

            'message' =>
                'Unable to create training opportunity.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Created Training
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            (int) $training_id
        );

    if ($training) {

        $created_specializations =
            training_repository_get_specializations_by_training_ids(
                [(int) $training_id]
            );

        $training['specializations'] =
            $created_specializations[(int) $training_id] ?? [];
    }


    return [

        'success' => true,

        'message' =>
            'Training opportunity created successfully.',

        'data' =>
            $training,

        'status_code' => 201

    ];
}


/*
|--------------------------------------------------------------------------
| Find Training
|--------------------------------------------------------------------------
*/

function training_service_find(
    int $training_id,
    ?int $student_id = null
): array {

    if (
        $training_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid training ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );


    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Only Publicly Available Training
    |--------------------------------------------------------------------------
    |
    | Draft / rejected / deleted opportunities should not
    | be exposed through the public endpoint.
    |
    */

    $public_statuses = [

        'published',

        'open',

        'active'

    ];


    if (
        isset($training['status'])
        &&
        !in_array(
            $training['status'],
            $public_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity is not available.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Enriched Details
    |--------------------------------------------------------------------------
    */

    $details =
        training_repository_find_with_company(
            $training_id
        );

    $training_ids = [
        $training_id
    ];

    $skills =
        training_repository_get_skills_by_training_ids(
            $training_ids
        );

    $specializations =
        training_repository_get_specializations_by_training_ids(
            $training_ids
        );

    $details['skills'] =
        $skills[$training_id] ?? [];

    $details['specializations'] =
        $specializations[$training_id] ?? [];

    $details['questions'] =
        training_repository_get_questions(
            $training_id
        );

    $details['has_applied'] = false;
    $details['application'] = null;
    $details['is_saved'] = false;


    /*
    |--------------------------------------------------------------------------
    | Student Context
    |--------------------------------------------------------------------------
    |
    | When a student views the training, include their saved
    | state and any existing application status.
    |
    */

    if (
        $student_id !== null
        &&
        $student_id > 0
    ) {

        $details['is_saved'] =
            training_repository_is_saved(
                $student_id,
                $training_id
            );

        $application =
            application_repository_find_student_application(
                $student_id,
                $training_id
            );

        if ($application) {

            $details['has_applied'] = true;

            $details['application'] = [

                'id' =>
                    (int) $application['id'],

                'status' =>
                    $application['status'] === 'submitted'
                        ? 'pending'
                        : $application['status'],

                'applied_at' =>
                    $application['applied_at']

            ];
        }
    }


    return [

        'success' => true,

        'message' =>
            'Training opportunity retrieved successfully.',

        'data' =>
            $details,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| List Training Opportunities
|--------------------------------------------------------------------------
*/

function training_service_list(
    array $filters = [],
    ?int $student_id = null
): array {

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;


    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    /*
    |--------------------------------------------------------------------------
    | Maximum Limit
    |--------------------------------------------------------------------------
    */

    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Normalize Filters
    |--------------------------------------------------------------------------
    */

    $normalized_filters = [

        'specialization_id' =>
            !empty($filters['specialization_id'])
                ? (int) $filters['specialization_id']
                : null,

        'skill_id' =>
            !empty($filters['skill_id'])
                ? (int) $filters['skill_id']
                : null,

        'training_type' =>
            !empty($filters['training_type'])
                ? trim(
                    $filters['training_type']
                )
                : null,

        'work_mode' =>
            !empty($filters['work_mode'])
                ? trim(
                    $filters['work_mode']
                )
                : null,

        'paid' =>
            isset($filters['paid'])
            &&
            $filters['paid'] !== ''
                ? (int) $filters['paid']
                : null,

        'employment_possible' =>
            isset($filters['employment_possible'])
            &&
            $filters['employment_possible'] !== ''
                ? (int) $filters['employment_possible']
                : null,

        'company_id' =>
            !empty($filters['company_id'])
                ? (int) $filters['company_id']
                : null,

        'city' =>
            !empty($filters['city'])
                ? trim(
                    $filters['city']
                )
                : null,

    ];


    /*
    |--------------------------------------------------------------------------
    | Student Context
    |--------------------------------------------------------------------------
    */

    if (
        $student_id !== null
        &&
        $student_id > 0
    ) {

        $normalized_filters['student_id'] =
            $student_id;


        /*
        |--------------------------------------------------------------------------
        | Specialization-Based Company Matching
        |--------------------------------------------------------------------------
        |
        | Authenticated students only see trainings from companies whose
        | registered industry (company_specializations) matches the
        | student's specialization. Matching is done on the specialization,
        | never on the broad study field.
        |
        */

        $student_specialization_id =
            training_repository_get_student_specialization_id(
                (int) $student_id
            );


        if ($student_specialization_id === null) {

            /*
            | Student has no specialization: no company can match, so an
            | empty result set is returned using the regular response
            | shape (no error).
            |
            */

            return [

                'success' => true,

                'message' =>
                    'Training opportunities retrieved successfully.',

                'data' => [

                    'items' => [],

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $limit,

                        'total' => 0,

                        'total_pages' => 0,

                        'has_next_page' => false,

                        'has_previous_page' => false

                    ]

                ],

                'status_code' => 200

            ];
        }


        $normalized_filters['match_company_specialization_ids'] =
            [$student_specialization_id];
    }


    /*
    |--------------------------------------------------------------------------
    | Sort
    |--------------------------------------------------------------------------
    */

    $allowed_sorts = [
        'newest',
        'oldest',
        'title',
        'name',
        'deadline',
        'relevance'
    ];

    $sort =
        isset($filters['sort'])
            ? strtolower(
                trim(
                    (string) $filters['sort']
                )
            )
            : 'newest';

    if (
        !in_array(
            $sort,
            $allowed_sorts,
            true
        )
    ) {

        $sort = 'newest';
    }


    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $items =
        training_repository_get_public_list(
            $normalized_filters,
            $limit,
            $offset,
            $sort
        );


    /*
    |--------------------------------------------------------------------------
    | Enrich Items
    |--------------------------------------------------------------------------
    */

    if (!empty($items)) {

        $training_ids = array_map(
            function ($item) {
                return (int) $item['id'];
            },
            $items
        );

        $skills_by_training =
            training_repository_get_skills_by_training_ids(
                $training_ids
            );

        $specializations_by_training =
            training_repository_get_specializations_by_training_ids(
                $training_ids
            );

        foreach ($items as $index => $item) {

            $item_id =
                (int) $item['id'];

            $items[$index]['skills'] =
                $skills_by_training[$item_id] ?? [];

            $items[$index]['specializations'] =
                $specializations_by_training[$item_id] ?? [];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        training_repository_count_public(
            $normalized_filters
        );


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Training opportunities retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Update Training
|--------------------------------------------------------------------------
|
| Company can update its own training opportunity.
|
*/

function training_service_update(
    int $user_id,
    int $training_id,
    array $data
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate ID
    |--------------------------------------------------------------------------
    */

    if (
        $training_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid training ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );


    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to update this training opportunity.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Update
    |--------------------------------------------------------------------------
    */

    $validation =
        training_validator_update(
            $data,
            $training
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'message' =>
                'Training data is invalid.',

            'errors' =>
                $validation['errors'],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Editing Closed Training
    |--------------------------------------------------------------------------
    */

    $locked_statuses = [

        'closed',

        'completed',

        'cancelled',

        'deleted'

    ];


    if (
        isset($training['status'])
        &&
        in_array(
            $training['status'],
            $locked_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'This training opportunity can no longer be updated.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed Fields
    |--------------------------------------------------------------------------
    */

    $allowed_fields = [

        'title',

        'description',

        'training_type',

        'work_mode',

        'location',

        'start_date',

        'end_date',

        'application_deadline',

        'capacity',

        'paid',

        'salary',

        'employment_possible'

    ];


    $update_data = [];


    foreach (
        $allowed_fields as $field
    ) {

        if (
            array_key_exists(
                $field,
                $data
            )
        ) {

            $update_data[$field] =
                $data[$field];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Strings
    |--------------------------------------------------------------------------
    */

    $string_fields = [

        'title',

        'description',

        'training_type',

        'work_mode',

        'location'

    ];


    foreach (
        $string_fields as $field
    ) {

        if (
            array_key_exists(
                $field,
                $update_data
            )
            &&
            $update_data[$field] !== null
        ) {

            $update_data[$field] =
                trim(
                    (string)
                    $update_data[$field]
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    if (
        empty($update_data)
    ) {

        return [

            'success' => false,

            'message' =>
                'No training data was provided for update.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    $updated =
        training_repository_update(
            $training_id,
            $update_data
        );


    if (
        !$updated
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to update training opportunity.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Specialization Synchronization
    |--------------------------------------------------------------------------
    |
    | Keeps the training's specialization relationships consistent with
    | the company's current company_specializations after a successful
    | update. Existing rows are replaced with the company's current set
    | inside one transaction; a company without specializations leaves
    | the training with zero specialization rows.
    |
    */

    try {

        db_transaction(
            function () use ($company, $training_id) {

                $company_specialization_ids =
                    training_repository_get_company_specialization_ids(
                        (int) $company['company_id']
                    );

                training_repository_replace_specializations(
                    (int) $training_id,
                    $company_specialization_ids
                );

                return true;
            }
        );

    } catch (Throwable $exception) {

        return [

            'success' => false,

            'message' =>
                'Unable to update training opportunity.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Training
    |--------------------------------------------------------------------------
    */

    $updated_training =
        training_repository_find_by_id(
            $training_id
        );


    return [

        'success' => true,

        'message' =>
            'Training opportunity updated successfully.',

        'data' =>
            $updated_training,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Publish Training
|--------------------------------------------------------------------------
*/

function training_service_publish(
    int $user_id,
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );


    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to publish this training opportunity.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company Approval
    |--------------------------------------------------------------------------
    */

    if (
        isset($company['approval_status'])
        &&
        $company['approval_status'] !== 'approved'
    ) {

        return [

            'success' => false,

            'message' =>
                'Your company must be approved before publishing training opportunities.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Current Status
    |--------------------------------------------------------------------------
    */

    if (
        isset($training['status'])
        &&
        $training['status'] !== 'draft'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only draft training opportunities can be published.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Before Publishing
    |--------------------------------------------------------------------------
    */

    $validation =
        training_validator_publish(
            $training
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity cannot be published.',

            'errors' =>
                $validation['errors'],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Publish
    |--------------------------------------------------------------------------
    */

    $published =
        training_repository_publish(
            $training_id
        );


    if (
        !$published
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to publish training opportunity.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Training
    |--------------------------------------------------------------------------
    */

    $updated_training =
        training_repository_find_by_id(
            $training_id
        );


    return [

        'success' => true,

        'message' =>
            'Training opportunity published successfully.',

        'data' =>
            $updated_training,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Close Training
|--------------------------------------------------------------------------
|
| Closing a training opportunity can affect pending
| applications, so the service handles the transaction.
|
*/

function training_service_close(
    int $user_id,
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );


    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to close this training opportunity.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    $closable_statuses = [

        'published',

        'open',

        'active'

    ];


    if (
        isset($training['status'])
        &&
        !in_array(
            $training['status'],
            $closable_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'This training opportunity cannot be closed.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Close
    |--------------------------------------------------------------------------
    */

    $closed =
        training_repository_close(
            $training_id
        );


    if (
        !$closed
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to close training opportunity.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Training
    |--------------------------------------------------------------------------
    */

    $updated_training =
        training_repository_find_by_id(
            $training_id
        );


    return [

        'success' => true,

        'message' =>
            'Training opportunity closed successfully.',

        'data' =>
            $updated_training,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Delete Training
|--------------------------------------------------------------------------
|
| Only draft training opportunities can be deleted.
|
*/

function training_service_delete(
    int $user_id,
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );


    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to delete this training opportunity.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Only Draft Can Be Deleted
    |--------------------------------------------------------------------------
    */

    if (
        isset($training['status'])
        &&
        $training['status'] !== 'draft'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only draft training opportunities can be deleted.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    $deleted =
        training_repository_delete(
            $training_id
        );


    if (
        !$deleted
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to delete training opportunity.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    return [

        'success' => true,

        'message' =>
            'Training opportunity deleted successfully.',

        'data' => null,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Save Training For Student
|--------------------------------------------------------------------------
*/

function training_service_save(
    int $user_id,
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Training ID
    |--------------------------------------------------------------------------
    */

    if (
        $training_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid training ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Student Profile
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );

    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Training Must Exist And Be Published
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );

    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }

    if (
        ($training['status'] ?? null)
        !==
        'published'
    ) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity is not available.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    $saved =
        training_repository_saved_add(
            (int) $student['student_id'],
            $training_id
        );

    if (!$saved) {

        return [

            'success' => false,

            'message' =>
                'Unable to save training opportunity.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    return [

        'success' => true,

        'message' =>
            'Training opportunity saved successfully.',

        'data' => [

            'training_id' =>
                $training_id,

            'is_saved' => true

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Unsave Training For Student
|--------------------------------------------------------------------------
*/

function training_service_unsave(
    int $user_id,
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Training ID
    |--------------------------------------------------------------------------
    */

    if (
        $training_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid training ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Student Profile
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );

    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Unsave
    |--------------------------------------------------------------------------
    */

    $removed =
        training_repository_saved_remove(
            (int) $student['student_id'],
            $training_id
        );


    return [

        'success' => true,

        'message' =>
            $removed
                ? 'Training opportunity removed from saved list.'
                : 'Training opportunity was not in the saved list.',

        'data' => [

            'training_id' =>
                $training_id,

            'is_saved' => false

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Get Student Saved Trainings
|--------------------------------------------------------------------------
*/

function training_service_saved_list(
    int $user_id,
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Get Student Profile
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );

    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }

    $student_id =
        (int) $student['student_id'];


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;

    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;

    if ($page < 1) {
        $page = 1;
    }

    if ($limit < 1) {
        $limit = 20;
    }

    if ($limit > 100) {
        $limit = 100;
    }

    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    $normalized_filters = [

        'student_id' =>
            $student_id,

        'saved_only' => true,

        'company_id' =>
            !empty($filters['company_id'])
                ? (int) $filters['company_id']
                : null,

        'training_type' =>
            !empty($filters['training_type'])
                ? trim(
                    $filters['training_type']
                )
                : null,

        'work_mode' =>
            !empty($filters['work_mode'])
                ? trim(
                    $filters['work_mode']
                )
                : null,

        'paid' =>
            isset($filters['paid'])
            &&
            $filters['paid'] !== ''
                ? (int) $filters['paid']
                : null,

        'keyword' =>
            !empty($filters['keyword'])
                ? trim(
                    $filters['keyword']
                )
                : null,

    ];


    /*
    |--------------------------------------------------------------------------
    | Sort
    |--------------------------------------------------------------------------
    */

    $allowed_sorts = [
        'newest',
        'oldest',
        'title',
        'name',
        'deadline',
        'relevance'
    ];

    $sort =
        isset($filters['sort'])
            ? strtolower(
                trim(
                    (string) $filters['sort']
                )
            )
            : 'newest';

    if (
        !in_array(
            $sort,
            $allowed_sorts,
            true
        )
    ) {

        $sort = 'newest';
    }


    /*
    |--------------------------------------------------------------------------
    | Get Saved Trainings
    |--------------------------------------------------------------------------
    */

    $items =
        training_repository_get_public_list(
            $normalized_filters,
            $limit,
            $offset,
            $sort
        );


    /*
    |--------------------------------------------------------------------------
    | Enrich Items
    |--------------------------------------------------------------------------
    */

    if (!empty($items)) {

        $training_ids = array_map(
            function ($item) {
                return (int) $item['id'];
            },
            $items
        );

        $skills_by_training =
            training_repository_get_skills_by_training_ids(
                $training_ids
            );

        $specializations_by_training =
            training_repository_get_specializations_by_training_ids(
                $training_ids
            );

        foreach ($items as $index => $item) {

            $item_id =
                (int) $item['id'];

            $items[$index]['skills'] =
                $skills_by_training[$item_id] ?? [];

            $items[$index]['specializations'] =
                $specializations_by_training[$item_id] ?? [];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        training_repository_count_public(
            $normalized_filters
        );


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Saved training opportunities retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}
