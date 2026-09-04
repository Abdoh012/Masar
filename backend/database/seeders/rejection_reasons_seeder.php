<?php

/**
 * MASAR - Rejection Reasons Seeder
 *
 * Seeds standardized rejection reasons used by training
 * applications and administrative workflows.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

function seed_rejection_reasons(PDO $pdo): void
{
    $rejectionReasons = [
        [
            'code' => 'INCOMPLETE_PROFILE',
            'name' => 'Incomplete profile',
            'name_ar' => 'الملف الشخصي غير مكتمل',
        ],
        [
            'code' => 'INSUFFICIENT_SKILLS',
            'name' => 'Insufficient required skills',
            'name_ar' => 'المهارات المطلوبة غير كافية',
        ],
        [
            'code' => 'SKILL_MISMATCH',
            'name' => 'Skills do not match the training requirements',
            'name_ar' => 'المهارات لا تتوافق مع متطلبات التدريب',
        ],
        [
            'code' => 'ACADEMIC_MISMATCH',
            'name' => 'Academic background does not match the requirements',
            'name_ar' => 'المؤهل الدراسي لا يتوافق مع المتطلبات',
        ],
        [
            'code' => 'SPECIALIZATION_MISMATCH',
            'name' => 'Specialization does not match the training requirements',
            'name_ar' => 'التخصص لا يتوافق مع متطلبات التدريب',
        ],
        [
            'code' => 'INSUFFICIENT_EXPERIENCE',
            'name' => 'Insufficient experience',
            'name_ar' => 'الخبرة غير كافية',
        ],
        [
            'code' => 'EXPERIENCE_MISMATCH',
            'name' => 'Experience does not match the training requirements',
            'name_ar' => 'الخبرة لا تتوافق مع متطلبات التدريب',
        ],
        [
            'code' => 'POSITION_FILLED',
            'name' => 'Training position has already been filled',
            'name_ar' => 'تم شغل فرصة التدريب بالفعل',
        ],
        [
            'code' => 'TRAINING_CLOSED',
            'name' => 'Training opportunity is no longer available',
            'name_ar' => 'فرصة التدريب لم تعد متاحة',
        ],
        [
            'code' => 'APPLICATION_DEADLINE',
            'name' => 'Application was submitted after the deadline',
            'name_ar' => 'تم تقديم الطلب بعد الموعد النهائي',
        ],
        [
            'code' => 'DUPLICATE_APPLICATION',
            'name' => 'Duplicate application',
            'name_ar' => 'طلب تقديم مكرر',
        ],
        [
            'code' => 'MISSING_DOCUMENTS',
            'name' => 'Required documents are missing',
            'name_ar' => 'المستندات المطلوبة غير موجودة',
        ],
        [
            'code' => 'INVALID_DOCUMENTS',
            'name' => 'Submitted documents are invalid',
            'name_ar' => 'المستندات المقدمة غير صالحة',
        ],
        [
            'code' => 'VERIFICATION_FAILED',
            'name' => 'Information verification failed',
            'name_ar' => 'فشل التحقق من البيانات',
        ],
        [
            'code' => 'ELIGIBILITY_NOT_MET',
            'name' => 'Eligibility requirements were not met',
            'name_ar' => 'لم يتم استيفاء شروط الأهلية',
        ],
        [
            'code' => 'ATTENDANCE_REQUIREMENT',
            'name' => 'Unable to meet attendance requirements',
            'name_ar' => 'عدم القدرة على الالتزام بمتطلبات الحضور',
        ],
        [
            'code' => 'SCHEDULE_CONFLICT',
            'name' => 'Schedule conflict',
            'name_ar' => 'تعارض في المواعيد',
        ],
        [
            'code' => 'CAPACITY_REACHED',
            'name' => 'Training capacity has been reached',
            'name_ar' => 'تم الوصول إلى الحد الأقصى لعدد المتدربين',
        ],
        [
            'code' => 'COMPANY_REQUIREMENTS',
            'name' => 'Company-specific requirements were not met',
            'name_ar' => 'لم يتم استيفاء متطلبات الشركة الخاصة',
        ],
        [
            'code' => 'POLICY_VIOLATION',
            'name' => 'Policy or eligibility violation',
            'name_ar' => 'مخالفة للسياسات أو شروط الأهلية',
        ],
        [
            'code' => 'BEHAVIORAL_CONCERNS',
            'name' => 'Behavioral or professional concerns',
            'name_ar' => 'ملاحظات سلوكية أو مهنية',
        ],
        [
            'code' => 'COMMUNICATION_ISSUES',
            'name' => 'Communication issues',
            'name_ar' => 'مشكلات في التواصل',
        ],
        [
            'code' => 'INACCURATE_INFORMATION',
            'name' => 'Inaccurate information was provided',
            'name_ar' => 'تم تقديم بيانات غير دقيقة',
        ],
        [
            'code' => 'INELIGIBLE_STATUS',
            'name' => 'Applicant status is not eligible',
            'name_ar' => 'حالة المتقدم غير مؤهلة',
        ],
        [
            'code' => 'OTHER',
            'name' => 'Other reason',
            'name_ar' => 'سبب آخر',
        ],
    ];

    $sql = "
        INSERT INTO rejection_reasons (
            code,
            name,
            name_ar
        )
        VALUES (
            :code,
            :name,
            :name_ar
        )
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            name_ar = VALUES(name_ar)
    ";

    $statement = $pdo->prepare($sql);

    foreach ($rejectionReasons as $reason) {
        $statement->execute([
            ':code' => $reason['code'],
            ':name' => $reason['name'],
            ':name_ar' => $reason['name_ar'],
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| CLI Entry Point
|--------------------------------------------------------------------------
*/

if (PHP_SAPI === 'cli') {
    try {
        $pdo->beginTransaction();

        seed_rejection_reasons($pdo);

        $pdo->commit();

        echo "Rejection reasons seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Rejection reasons seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
