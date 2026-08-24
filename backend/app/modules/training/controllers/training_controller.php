<?php

/**
 * MASAR - Training Controller
 *
 * Handles HTTP requests related to training listings.
 *
 * Native PHP - No OOP.
 *
 * Responsibilities:
 * - Receive request data.
 * - Check request method.
 * - Get authenticated user.
 * - Call training service.
 * - Return API response.
 *
 * Business logic belongs to training_service.php.
 * Database logic belongs to training_repository.php.
 */

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';
require_once __DIR__ . '/../../../core/auth/token.php';
require_once __DIR__ . '/../../../shared/functions/authorization.php';

require_once __DIR__ . '/../services/training_service.php';


/*
|--------------------------------------------------------------------------
| Optional Student Context
|--------------------------------------------------------------------------
|
| تحدد هوية الطالب المسجل عند توفر JWT صالح، وتُرجع null للزوار.
| هذا يسمح بفتح صفحات التدريب العامة دون تسجيل دخول، مع الاحتفاظ
| بحالة الطالب إذا كان المستخدم مسجلاً كطالب.
|
*/

function training_controller_student_context(): ?int
{
    if (
        !token_authenticate_request()
    ) {
        return null;
    }

    $user = auth_user();

    if (
        !$user
        ||
        !is_student_role(
            $user['role'] ?? null
        )
    ) {
        return null;
    }

    $student =
        application_repository_find_student_by_user_id(
            (int) $user['id']
        );

    if (!$student) {
        return null;
    }

    return (int) $student['student_id'];
}


/*
|--------------------------------------------------------------------------
| Create Training
|--------------------------------------------------------------------------
|
| إنشاء فرصة تدريب جديدة من قبل الشركة.
| يتحقق من صلاحية المستخدم والدور ويُرسل البيانات إلى الخدمة الأساسية.
|
*/

function training_controller_create(): void
{

    $method = request_method();

    if ($method !== 'POST') {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can create training opportunities.'
        );

        return;
    }

    $data = request_json();

    $result =
        training_service_create(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to create training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_created(
        $result['data'],
        $result['message'] ?? 'Training opportunity created successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Training By ID
|--------------------------------------------------------------------------
|
| عرض تفاصيل فرصة تدريب واحدة بناءً على معرفها.
| تُستخدم هذه الدالة في صفحة تفاصيل التدريب.
|
*/

function training_controller_show( int $training_id = 0 ): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'GET') {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    if ($training_id <= 0) {

        $training_id =
            request_get_int(
                'id'
            );
    }


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_find(
            $training_id,
            training_controller_student_context()
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Training opportunity not found.',
            $result['status_code'] ?? 404,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data']
    );
}


/*
|--------------------------------------------------------------------------
| Get Training List
|--------------------------------------------------------------------------
|
| عرض قائمة فرص التدريب المتاحة للجميع.
| تدعم البحث والتصفية والفرز وال分页، وتُستخدم في الصفحة الرئيسية.
|
*/

function training_controller_index(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'GET') {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Query Parameters
    |--------------------------------------------------------------------------
    */

    $filters = [

        'training_type' =>
            request_get(
                'training_type'
            ),

        'work_mode' =>
            request_get(
                'work_mode'
            ),

        'paid' =>
            request_get(
                'paid'
            ),

        'employment_possible' =>
            request_get(
                'employment_possible'
            ),

        'company_id' =>
            request_get_int(
                'company_id'
            ),

        'specialization_id' =>
            request_get_int(
                'specialization_id'
            ),

        'skill_id' =>
            request_get_int(
                'skill_id'
            ),

        'city' =>
            request_get(
                'city'
            ),

        'sort' =>
            request_get(
                'sort',
                'newest'
            ),

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Training List
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_list(
            $filters,
            training_controller_student_context()
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to retrieve training opportunities.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data']
    );
}


/*
|--------------------------------------------------------------------------
| Update Training
|--------------------------------------------------------------------------
|
| تعديل فرصة تدريب موجودة تابعة للشركة.
| يتم التحقق من صلاحية الشركة ووجود التدريب قبل تحديثه.
|
*/

function training_controller_update(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'PUT' && $method !== 'PATCH') {

        response_method_not_allowed(
            'Only PUT or PATCH method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can update training opportunities.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    $training_id =
        request_get_int(
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data = request_json();


    /*
    |--------------------------------------------------------------------------
    | Update Training
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_update(
            (int) $user['id'],
            $training_id,
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to update training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'],
        $result['message'] ?? 'Training opportunity updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Publish Training
|--------------------------------------------------------------------------
|
| نشر فرصة تدريب من الحالة المسودة إلى الحالة العامة.
| بعد النشر تصبح متاحة للطلاب للبحث والتقديم.
|
*/

function training_controller_publish(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'POST') {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can publish training opportunities.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    $training_id =
        request_get_int(
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Publish
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_publish(
            (int) $user['id'],
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to publish training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'] ?? null,
        $result['message'] ?? 'Training opportunity published successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Close Training
|--------------------------------------------------------------------------
|
| إغلاق فرصة تدريب يدويًا من قبل الشركة.
| يساعد هذا على توقف استقبال الطلبات الجديدة، بينما تتم معالجة
| الطلبات المعلقة عبر خدمة إغلاق التدريب الخاصة بالنظام.
|
*/

function training_controller_close(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'POST') {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can close training opportunities.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    $training_id =
        request_get_int(
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Close Training
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_close(
            (int) $user['id'],
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to close training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'] ?? null,
        $result['message'] ?? 'Training opportunity closed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Delete Training
|--------------------------------------------------------------------------
|
| حذف فرصة تدريب موجودة إذا كانت في حالة المسودة أو لا يسمح بها.
| تُستخدم هذه العملية بشكل خاص لحذف الفرص غير المنشورة.
|
*/

function training_controller_delete(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'DELETE') {

        response_method_not_allowed(
            'Only DELETE method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can delete training opportunities.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    $training_id =
        request_get_int(
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_delete(
            (int) $user['id'],
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to delete training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        null,
        $result['message'] ?? 'Training opportunity deleted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Save Training
|--------------------------------------------------------------------------
|
| حفظ فرصة تدريب في قائمة الطالب المحفوظة.
| تُستخدم هذه الدالة عندما يضيف الطالب فرصة تدريب إلى مفضلاته.
|
*/

function training_controller_save( int $training_id = 0 ): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'POST') {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    if ($training_id <= 0) {

        $training_id =
            request_get_int(
                'id'
            );
    }

    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_save(
            (int) auth_id(),
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to save training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'] ?? null,
        $result['message'] ?? 'Training opportunity saved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Unsave Training
|--------------------------------------------------------------------------
|
| إزالة فرصة تدريب من قائمة الطالب المحفوظة.
| تُستخدم عندما يريد الطالب حذف فرصة من مفضلاته.
|
*/

function training_controller_unsave( int $training_id = 0 ): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'DELETE') {

        response_method_not_allowed(
            'Only DELETE method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    if ($training_id <= 0) {

        $training_id =
            request_get_int(
                'id'
            );
    }

    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Unsave
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_unsave(
            (int) auth_id(),
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to remove saved training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'] ?? null,
        $result['message'] ?? 'Training opportunity removed from saved list.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Saved Trainings
|--------------------------------------------------------------------------
|
| عرض كل فرص التدريب المحفوظة للطالب.
| تُستخدم في شاشة المفضلة أو المحفوظات للطالب.
|
*/

function training_controller_saved(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'GET') {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Query Parameters
    |--------------------------------------------------------------------------
    */

    $filters = [

        'company_id' =>
            request_get_int(
                'company_id'
            ),

        'training_type' =>
            request_get(
                'training_type'
            ),

        'work_mode' =>
            request_get(
                'work_mode'
            ),

        'paid' =>
            request_get(
                'paid'
            ),

        'keyword' =>
            request_get(
                'keyword'
            ),

        'sort' =>
            request_get(
                'sort',
                'newest'
            ),

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Saved Trainings
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_saved_list(
            (int) auth_id(),
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to retrieve saved training opportunities.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data']
    );
}
