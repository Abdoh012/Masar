<?php

/**
 * MASAR - Application Card Functions
 *
 * Shared helpers for shaping the application card responses returned by
 * the student-facing endpoints (the Applied, Accepted and Rejected tabs).
 */

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../modules/training/repositories/application_repository.php';
require_once __DIR__ . '/../../modules/training/services/training_service.php';


/*
|--------------------------------------------------------------------------
| Format Application Timestamp (ISO-8601)
|--------------------------------------------------------------------------
|
| Converts a naive database datetime (stored in the application timezone)
| into a machine-readable ISO-8601 string that carries the timezone offset,
| e.g. "2026-09-08T03:20:20+03:00". The frontend formats it for display.
|
*/

function application_iso8601(
    ?string $datetime
): ?string {

    if (
        $datetime === null
        ||
        trim($datetime) === ''
    ) {
        return null;
    }

    $value =
        trim($datetime);

    $timezone =
        new DateTimeZone(
            (string) (
                getenv('APP_TIMEZONE')
                ?: 'Africa/Cairo'
            )
        );

    $date_time =
        DateTime::createFromFormat(
            'Y-m-d H:i:s',
            (string) substr($value, 0, 19),
            $timezone
        );

    if ($date_time === false) {
        return null;
    }

    return $date_time->format('c');
}


/*
|--------------------------------------------------------------------------
| Application Status Message
|--------------------------------------------------------------------------
|
| Deterministic user-facing message for a card's canonical status. The raw
| status value is never changed; this only provides the human-readable
| sentence shown next to it.
|
*/

function application_status_message(
    mixed $status
): string {

    switch (
        strtolower(
            trim(
                (string) $status
            )
        )
    ) {

        case 'submitted':
        case 'pending':
            return 'Your application has been submitted and is waiting for review.';

        case 'accepted':
            return 'Congratulations! Your application has been accepted.';

        case 'rejected':
            return 'Unfortunately, your application was not accepted this time.';

        case 'withdrawn':
            return 'You have withdrawn this application.';

        default:
            return 'Your application is being processed.';
    }
}


/*
|--------------------------------------------------------------------------
| Applied Application Card
|--------------------------------------------------------------------------
|
| Transforms one raw application/training row into the clean frontend card
| consumed by the Applied tab. Deliberately excludes application PII and
| detail fields.
|
*/

function application_applied_card(
    array $item
): array {

    $is_paid =
        (bool) ($item['is_paid'] ?? false);

    return [
        'id' =>
            (int) ($item['id'] ?? 0),

        'training_id' =>
            (int) ($item['training_id'] ?? 0),

        'training_title' =>
            (string) ($item['training_title'] ?? ''),

        'status' =>
            'Applied',

        'specialization' =>
            (string) ($item['specialization_name'] ?? ''),

        'company_name' =>
            (string) ($item['company_name'] ?? ''),

        'company_logo' =>
            $item['company_logo'] ?? null,

        'training_type' =>
            (string) ($item['training_type'] ?? ''),

        'method' =>
            (string) ($item['mode'] ?? ''),

        'is_paid' =>
            $is_paid,

        /*
        | Business rule: the "May lead to hire" badge is driven by the paid
        | flag so the frontend shows it only for paid trainings.
        */
        'may_lead_to_hire' =>
            $is_paid,

        'applied_at' =>
            application_iso8601(
                $item['applied_at'] ?? null
            ),

        'starts_at' =>
            application_iso8601(
                $item['starts_at'] ?? null
            ),

        'ends_at' =>
            application_iso8601(
                $item['ends_at'] ?? null
            ),

        'duration' =>
            training_calculate_duration(
                $item['starts_at'] ?? null,
                $item['ends_at'] ?? null
            ),

        'remaining_days' =>
            training_calculate_remaining_days(
                $item['ends_at'] ?? null
            ),

        'status_message' =>
            application_status_message(
                $item['status'] ?? 'submitted'
            ),

        'can_withdraw' =>
            in_array(
                strtolower(
                    (string) ($item['status'] ?? '')
                ),
                ['submitted', 'pending'],
                true
            ),
    ];
}


/*
|--------------------------------------------------------------------------
| Applied Application Cards (Batch)
|--------------------------------------------------------------------------
|
| Enriches the service items with the training card fields and returns them
| shaped as Applied cards.
|
*/

function application_applied_cards(
    array $items
): array {

    if (empty($items)) {
        return [];
    }

    $training_ids = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        if ($training_id > 0) {
            $training_ids[$training_id] = true;
        }
    }

    $card_fields =
        empty($training_ids)
            ? []
            : application_repository_get_training_card_fields_by_ids(
                array_keys($training_ids)
            );

    $cards = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        $merged =
            is_array($card_fields[$training_id] ?? null)
                ? array_merge($item, $card_fields[$training_id])
                : $item;

        $cards[] =
            application_applied_card($merged);
    }

    return $cards;
}


/*
|--------------------------------------------------------------------------
| Free Trial Days Remaining
|--------------------------------------------------------------------------
|
| Counts down the free trial of a paid training. The trial is at its full
| value until the training starts, then decrements by day and never goes
| below 0 (0 once the trial period is over). Returns null when the training
| is not paid (no trial period).
|
*/

function application_free_trial_days_remaining(
    ?int $trial_days,
    ?string $starts_at
): ?int {

    if (
        $trial_days === null
        ||
        $trial_days <= 0
        ||
        empty($starts_at)
    ) {
        return null;
    }

    $timezone =
        new DateTimeZone(
            (string) (
                getenv('APP_TIMEZONE')
                ?: 'Africa/Cairo'
            )
        );

    $start =
        DateTime::createFromFormat(
            'Y-m-d H:i:s',
            (string) substr($starts_at, 0, 19),
            $timezone
        );

    if ($start === false) {
        return null;
    }

    $start->setTime(0, 0, 0);

    $today =
        new DateTime(
            'now',
            $timezone
        );

    $today->setTime(0, 0, 0);

    if ($today < $start) {
        return $trial_days;
    }

    $elapsed_days =
        (int) $start->diff($today)->days;

    return max(0, $trial_days - $elapsed_days);
}


/*
|--------------------------------------------------------------------------
| Daily Motivational Message
|--------------------------------------------------------------------------
|
| Returns a short deterministic encouragement shown on accepted training
| cards. The message is picked from a fixed pool keyed by the application
| timezone date, so every student sees the same message on a given day
| (no external AI or per-user content).
|
*/

function application_daily_motivational_message(): string {

    $messages = [
        'Small daily steps build lasting skills - keep going.',
        'Today is a great day to sharpen your craft.',
        'Consistency beats intensity - show up for yourself today.',
        'Stay curious and the right doors will open.',
        'Every expert was once a beginner - keep practicing.',
        'Focus on progress, not perfection, today.',
        'Your dedication today shapes your career tomorrow.',
        'Great things are built one focused session at a time.',
        'Treat today as another opportunity to grow.',
    ];

    $timezone =
        new DateTimeZone(
            (string) (
                getenv('APP_TIMEZONE')
                ?: 'Africa/Cairo'
            )
        );

    $today =
        new DateTime(
            'now',
            $timezone
        );

    $index =
        ((int) $today->format('Ymd'))
        % count($messages);

    return $messages[$index];
}


/*
|--------------------------------------------------------------------------
| Accepted Application Card
|--------------------------------------------------------------------------
|
| Transforms one raw application/training row into the clean frontend card
| consumed by the Accepted tab. Deliberately excludes application PII and
| detail fields. Paid trainings bring the free-trial countdown and the
| "may lead to hire" badge; free trainings leave every paid-only field null.
|
*/

function application_accepted_card(
    array $item
): array {

    $is_paid =
        (bool) ($item['is_paid'] ?? false);

    $trial_period_days =
        $is_paid
            ? (
                isset($item['trial_period_days'])
                &&
                $item['trial_period_days'] !== null
                &&
                $item['trial_period_days'] !== ''
                    ? (int) $item['trial_period_days']
                    : null
            )
            : null;

    /*
    |--------------------------------------------------------------------------
    | Manual Payment State
    |--------------------------------------------------------------------------
    |
    | Free trainings never require a payment ("not_required"). Paid trainings
    | enter the manual bank-transfer lifecycle. Four states are distinguished
    | without contradicting combinations:
    |
    |   - No payment created  : payment_submitted = false, status "pending"
    |                            (the canonical no-record representation, same
    |                            as GET /applications/{id}/payment), all
    |                            payment detail fields null.
    |   - Pending verification: payment_submitted = true, status "pending",
    |                            a reference exists.
    |   - Paid                : payment_submitted = true, status "paid",
    |                            paid_at exists.
    |   - Failed / rejected   : payment_submitted = true, status "failed"
    |                            (one of the canonical stored payment statuses).
    |
    | The remaining trial countdown runs independently of this state; a
    | student whose trial expired but has not paid is simply blocked from
    | continuing, never auto-withdrawn. bank_account below tells the student
    | WHERE to pay; it is never a confirmation on its own.
    |
    */

    $payment_submitted =
        (bool) ($item['payment_submitted'] ?? false);

    $payment_status =
        $is_paid
            ? (
                isset($item['payment_status'])
                &&
                $item['payment_status'] !== null
                &&
                trim(
                    (string) $item['payment_status']
                ) !== ''
                    ? (string) $item['payment_status']
                    : 'pending'
            )
            : 'not_required';

    $payment_amount =
        $is_paid
        && $payment_submitted
        && $item['payment_amount'] !== null
        && trim(
            (string) $item['payment_amount']
        ) !== ''
            ? (float) $item['payment_amount']
            : null;

    $payment_currency =
        $is_paid
        && $payment_submitted
        && $item['payment_currency'] !== null
        && trim(
            (string) $item['payment_currency']
        ) !== ''
            ? (string) $item['payment_currency']
            : null;

    $payment_paid_at =
        $is_paid
        && $payment_submitted
            ? application_iso8601(
                $item['payment_paid_at'] ?? null
            )
            : null;

    /*
    |--------------------------------------------------------------------------
    | Company Bank Account
    |--------------------------------------------------------------------------
    |
    | The bank_account object is the student's manual transfer destination.
    | It is only relevant while a transfer may still be needed, so it follows
    | the payment lifecycle exactly:
    |
    |   paid (confirmed)     -> null (nothing left to pay)
    |   pending / failed /
    |     not submitted      -> owning company's configured destination
    |                           (built from its own stored banking fields;
    |                           null when the company never configured one)
    |   free (is_paid false) -> null (no payment exists at all)
    |
    | The values always come from the OWNING company's companies row and are
    | never mixed with another company's data.
    |
    */

    $bank_account = null;

    if (
        $is_paid
        &&
        $payment_status !== 'paid'
    ) {

        $bank_account_number =
            $item['bank_account_number'] ?? null;

        if (
            $bank_account_number !== null
            &&
            trim(
                (string) $bank_account_number
            ) !== ''
        ) {

            $bank_account = [

                'bank_name' =>
                    (
                        isset($item['bank_name'])
                        &&
                        trim(
                            (string) $item['bank_name']
                        ) !== ''
                    )
                        ? (string) $item['bank_name']
                        : null,

                'account_name' =>
                    (
                        isset($item['bank_account_name'])
                        &&
                        trim(
                            (string) $item['bank_account_name']
                        ) !== ''
                    )
                        ? (string) $item['bank_account_name']
                        : null,

                'account_number' =>
                    (string) $bank_account_number,

                'instructions' =>
                    (
                        isset($item['bank_transfer_instructions'])
                        &&
                        trim(
                            (string) $item['bank_transfer_instructions']
                        ) !== ''
                    )
                        ? (string) $item['bank_transfer_instructions']
                        : null,

            ];
        }
    }

    return [

        'id' =>
            (int) ($item['id'] ?? 0),

        'training_id' =>
            (int) ($item['training_id'] ?? 0),

        'training_title' =>
            (string) ($item['training_title'] ?? ''),

        'status' =>
            'Accepted',

        'status_message' =>
            application_status_message(
                $item['status'] ?? 'accepted'
            ),

        'specialization' =>
            (string) ($item['specialization_name'] ?? ''),

        'company_name' =>
            (string) ($item['company_name'] ?? ''),

        'company_logo' =>
            $item['company_logo'] ?? null,

        'training_type' =>
            (string) ($item['training_type'] ?? ''),

        'method' =>
            (string) ($item['mode'] ?? ''),

        'is_paid' =>
            $is_paid,

        /*
        | Business rule: the "May lead to hire" badge is driven by the paid
        | flag so the frontend shows it only for paid trainings.
        */
        'may_lead_to_hire' =>
            $is_paid,

        'accepted_at' =>
            application_iso8601(
                $item['reviewed_at'] ?? null
            ),

        'starts_at' =>
            application_iso8601(
                $item['starts_at'] ?? null
            ),

        'ends_at' =>
            application_iso8601(
                $item['ends_at'] ?? null
            ),

        'duration' =>
            training_calculate_duration(
                $item['starts_at'] ?? null,
                $item['ends_at'] ?? null
            ),

        'remaining_days' =>
            training_calculate_remaining_days(
                $item['ends_at'] ?? null
            ),

        'free_trial_days' =>
            $trial_period_days,

        'free_trial_days_remaining' =>
            application_free_trial_days_remaining(
                $trial_period_days,
                $item['starts_at'] ?? null
            ),

        'motivational_message' =>
            application_daily_motivational_message(),

        /*
        | Manual bank-transfer destination (paid + company configured + not yet
        | confirmed) and the manual payment lifecycle state. bank_account is
        | NOT a confirmation: it tells the student WHERE to pay and disappears
        | once the payment_status is "paid". payment_status + payment_submitted
        | describe the lifecycle state exactly.
        */
        'payment_status' =>
            $payment_status,

        'payment_submitted' =>
            $payment_submitted,

        'payment_reference' =>
            $is_paid && $payment_submitted
                ? (
                    $item['payment_reference'] ?? null
                )
                : null,

        'payment_amount' =>
            $payment_amount,

        'payment_currency' =>
            $payment_currency,

        'payment_paid_at' =>
            $payment_paid_at,

        'payment_method' =>
            $is_paid && $payment_submitted
                ? (
                    $item['payment_method'] ?? null
                )
                : null,

        'bank_account' =>
            $bank_account,

    ];
}


/*
|--------------------------------------------------------------------------
| Merge Accepted Payment State
|--------------------------------------------------------------------------
|
| Merges one manual-payment row (from the student payment map) onto an
| accepted application item so the accepted card can expose the payment
| lifecycle fully. A missing row keeps the canonical "no payment created"
| representation: status "pending" with payment_submitted = false and all
| detail fields null - the same shape GET /applications/{id}/payment returns
| before the student submits a reference.
|
*/

function application_merge_accepted_payment(
    array $item,
    ?array $payment
): array {

    $is_paid =
        (bool) ($item['is_paid'] ?? false);

    if (!$is_paid) {

        $item['payment_status'] =
            'not_required';

        $item['payment_submitted'] =
            false;

        $item['payment_reference'] =
            null;

        $item['payment_amount'] =
            null;

        $item['payment_currency'] =
            null;

        $item['payment_paid_at'] =
            null;

        $item['payment_method'] =
            null;

        return $item;
    }

    $submitted =
        is_array($payment);

    $item['payment_status'] =
        $submitted
            ? (string) ($payment['status'] ?? 'pending')
            : 'pending';

    $item['payment_submitted'] =
        $submitted;

    $item['payment_reference'] =
        $submitted
            ? ($payment['reference'] ?? null)
            : null;

    $item['payment_amount'] =
        $submitted
            ? ($payment['amount'] ?? null)
            : null;

    $item['payment_currency'] =
        $submitted
            ? ($payment['currency'] ?? null)
            : null;

    $item['payment_paid_at'] =
        $submitted
            ? ($payment['paid_at'] ?? null)
            : null;

    $item['payment_method'] =
        $submitted
            ? ($payment['method'] ?? null)
            : null;

    return $item;
}


/*
|--------------------------------------------------------------------------
| Accepted Application Cards (Batch)
|--------------------------------------------------------------------------
|
| Enriches the service items with the training card fields and returns them
| shaped as Accepted cards.
|
*/

function application_accepted_cards(
    array $items
): array {

    if (empty($items)) {
        return [];
    }

    $training_ids = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        if ($training_id > 0) {
            $training_ids[$training_id] = true;
        }
    }

    $card_fields =
        empty($training_ids)
            ? []
            : application_repository_get_training_card_fields_by_ids(
                array_keys($training_ids)
            );

    /*
    | The accepted ledger belongs to one student, so the payment map is
    | fetched once for all of that student's trainings.
    */
    $student_id =
        (int) ($items[0]['student_id'] ?? 0);

    $payment_map =
        $student_id > 0
            ? application_repository_get_payment_map_for_student(
                $student_id,
                array_keys($training_ids)
            )
            : [];

    $cards = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        $merged =
            is_array($card_fields[$training_id] ?? null)
                ? array_merge($item, $card_fields[$training_id])
                : $item;

        $merged =
            application_merge_accepted_payment(
                $merged,
                $payment_map[$training_id] ?? null
            );

        $cards[] =
            application_accepted_card($merged);
    }

    return $cards;
}


/*
|--------------------------------------------------------------------------
| Rejected Application Card
|--------------------------------------------------------------------------
|
| Transforms one rejected application row (plus its merged training card
| fields) into the clean frontend card consumed by the Rejected tab. It
| answers "which trainings did this student apply to, which were rejected,
| when, by which training/company, and why?" — it deliberately exposes no
| application PII or detail fields and no raw database status value.
|
*/

function application_rejected_card(
    array $item
): array {

    return [

        'id' =>
            (int) ($item['id'] ?? 0),

        'training_id' =>
            (int) ($item['training_id'] ?? 0),

        'training_title' =>
            (string) ($item['training_title'] ?? ''),

        'status' =>
            'Rejected',

        'status_message' =>
            application_status_message(
                $item['status'] ?? 'rejected'
            ),

        'specialization' =>
            (string) ($item['specialization_name'] ?? ''),

        'company_name' =>
            (string) ($item['company_name'] ?? ''),

        'company_logo' =>
            $item['company_logo'] ?? null,

        'training_type' =>
            (string) ($item['training_type'] ?? ''),

        'method' =>
            (string) ($item['mode'] ?? ''),

        /*
        | The rejection timestamp comes from reviewed_at (set when the
        | company rejects), formatted like every other card timestamp.
        */
        'rejected_at' =>
            application_iso8601(
                $item['reviewed_at'] ?? null
            ),

        'rejection_reason' =>
            (
                isset($item['rejection_reason'])
                &&
                $item['rejection_reason'] !== null
                &&
                trim(
                    (string) $item['rejection_reason']
                ) !== ''
            )
                ? (string) $item['rejection_reason']
                : null,

        'rejection_note' =>
            (
                isset($item['rejection_note'])
                &&
                $item['rejection_note'] !== null
                &&
                trim(
                    (string) $item['rejection_note']
                ) !== ''
            )
                ? (string) $item['rejection_note']
                : null,

        'starts_at' =>
            application_iso8601(
                $item['starts_at'] ?? null
            ),

        'ends_at' =>
            application_iso8601(
                $item['ends_at'] ?? null
            ),

        'duration' =>
            training_calculate_duration(
                $item['starts_at'] ?? null,
                $item['ends_at'] ?? null
            ),

        'remaining_days' =>
            training_calculate_remaining_days(
                $item['ends_at'] ?? null
            ),
    ];
}


/*
|--------------------------------------------------------------------------
| Rejected Application Cards (Batch)
|--------------------------------------------------------------------------
|
| Enriches the service items with the training card fields and returns them
| shaped as Rejected cards.
|
*/

function application_rejected_cards(
    array $items
): array {

    if (empty($items)) {
        return [];
    }

    $training_ids = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        if ($training_id > 0) {
            $training_ids[$training_id] = true;
        }
    }

    $card_fields =
        empty($training_ids)
            ? []
            : application_repository_get_training_card_fields_by_ids(
                array_keys($training_ids)
            );

    $cards = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        $merged =
            is_array($card_fields[$training_id] ?? null)
                ? array_merge($item, $card_fields[$training_id])
                : $item;

        $cards[] =
            application_rejected_card($merged);
    }

    return $cards;
}


/*
|--------------------------------------------------------------------------
| Withdrawn Application Card
|--------------------------------------------------------------------------
|
| Clean student-facing card for a withdrawn application. No application PII
| and no internal fields (student_id, company_id, message, skills,
| rejection_*, apply/review timestamps, etc.).
|
*/

function application_withdrawn_card(
    array $item
): array {

    return [

        'id' =>
            (int) ($item['id'] ?? 0),

        'training_id' =>
            (int) ($item['training_id'] ?? 0),

        'training_title' =>
            (string) ($item['training_title'] ?? ''),

        'status' =>
            'Withdrawn',

        'status_message' =>
            application_status_message(
                $item['status'] ?? 'withdrawn'
            ),

        'specialization' =>
            (string) ($item['specialization_name'] ?? ''),

        'company_name' =>
            (string) ($item['company_name'] ?? ''),

        'company_logo' =>
            $item['company_logo'] ?? null,

        'training_type' =>
            (string) ($item['training_type'] ?? ''),

        'method' =>
            (string) ($item['mode'] ?? ''),

        /*
        | The withdrawal timestamp comes from applications.withdrawn_at
        | (never applied_at / reviewed_at), formatted like every other card
        | timestamp.
        */
        'withdrawn_at' =>
            application_iso8601(
                $item['withdrawn_at'] ?? null
            ),

        'starts_at' =>
            application_iso8601(
                $item['starts_at'] ?? null
            ),

        'ends_at' =>
            application_iso8601(
                $item['ends_at'] ?? null
            ),

        'duration' =>
            training_calculate_duration(
                $item['starts_at'] ?? null,
                $item['ends_at'] ?? null
            ),

        'remaining_days' =>
            training_calculate_remaining_days(
                $item['ends_at'] ?? null
            ),
    ];
}


/*
|--------------------------------------------------------------------------
| Withdrawn Application Cards (Batch)
|--------------------------------------------------------------------------
|
| Enriches the service items with the training card fields and returns them
| shaped as Withdrawn cards.
|
*/

function application_withdrawn_cards(
    array $items
): array {

    if (empty($items)) {
        return [];
    }

    $training_ids = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        if ($training_id > 0) {
            $training_ids[$training_id] = true;
        }
    }

    $card_fields =
        empty($training_ids)
            ? []
            : application_repository_get_training_card_fields_by_ids(
                array_keys($training_ids)
            );

    $cards = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        $merged =
            is_array($card_fields[$training_id] ?? null)
                ? array_merge($item, $card_fields[$training_id])
                : $item;

        $cards[] =
            application_withdrawn_card($merged);
    }

    return $cards;
}


/*
|--------------------------------------------------------------------------
| All Application Cards (Batch)
|--------------------------------------------------------------------------
|
| Unified All-tab card mapper. Fetches the training card fields ONCE for
| every page item and dispatches each row to the exact status-specific card
| function already used by the Applied / Accepted / Rejected / Withdrawn
| tabs, so the All response preserves every tab card's legitimate fields
| without duplicating card logic. Accepted rows additionally receive the
| same payment_state enrichment the Accepted tab applies.
|
*/

function application_all_cards(
    array $items
): array {

    if (empty($items)) {
        return [];
    }

    $training_ids = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        if ($training_id > 0) {
            $training_ids[$training_id] = true;
        }
    }

    $card_fields =
        empty($training_ids)
            ? []
            : application_repository_get_training_card_fields_by_ids(
                array_keys($training_ids)
            );

    /*
    | The whole page belongs to one student, so the accepted payment map is
    | fetched once for that student's trainings (same enrichment as the
    | Accepted tab's batch function).
    */
    $student_id =
        (int) ($items[0]['student_id'] ?? 0);

    $payment_map =
        ($student_id > 0 && !empty($training_ids))
            ? application_repository_get_payment_map_for_student(
                $student_id,
                array_keys($training_ids)
            )
            : [];

    $cards = [];

    foreach ($items as $item) {
        $training_id =
            (int) ($item['training_id'] ?? 0);

        $merged =
            is_array($card_fields[$training_id] ?? null)
                ? array_merge($item, $card_fields[$training_id])
                : $item;

        $raw_status =
            strtolower(
                (string) ($item['status'] ?? '')
            );

        if (
            $raw_status === 'submitted'
            ||
            $raw_status === 'pending'
        ) {

            $cards[] =
                application_applied_card($merged);

            continue;
        }

        if ($raw_status === 'accepted') {

            $merged =
                application_merge_accepted_payment(
                    $merged,
                    $payment_map[$training_id] ?? null
                );

            $cards[] =
                application_accepted_card($merged);

            continue;
        }

        if ($raw_status === 'rejected') {

            $cards[] =
                application_rejected_card($merged);

            continue;
        }

        if ($raw_status === 'withdrawn') {

            $cards[] =
                application_withdrawn_card($merged);

            continue;
        }
    }

    return $cards;
}