<?php

/**
 * MASAR - Training Card Functions
 *
 * Shared helpers for shaping the training card responses returned by
 * the student-facing endpoints (list, search, filters and saved).
 */

/*
|--------------------------------------------------------------------------
| Training Card Specialization
|--------------------------------------------------------------------------
|
| Returns the single specialization object shown inside a training card.
|
| Every training now carries one primary specialization
| (training_listings.specialization_id), which is the exact value used to
| scope the training list, search, filters and saved pages for students.
| The card queries already join `specializations` so each row includes
| `specialization_id` and `specialization_name`; this helper turns those two
| row fields into the canonical {id, name} object, or null when the training
| has no specialization assigned.
|
*/

function training_card_specialization(
    array $item
): ?array {

    $specialization_id =
        (int) ($item['specialization_id'] ?? 0);

    if ($specialization_id <= 0) {
        return null;
    }

    return [
        'id' => $specialization_id,
        'name' => (string) ($item['specialization_name'] ?? ''),
    ];
}