<?php

/**
 * MASAR - Payment Service
 *
 * Handles payment business logic.
 *
 * Controller
 *     ↓
 * PaymentService
 *     ↓
 * PaymentRepository
 */

$payment_repository_file =
    __DIR__ . '/../repositories/payment_repository.php';

if (file_exists($payment_repository_file)) {
    require_once $payment_repository_file;
}

class PaymentService
{
    protected mixed $repository = null;

    protected array $allowedStatuses = [
        'pending',
        'processing',
        'paid',
        'failed',
        'cancelled',
        'refunded',
        'partially_refunded'
    ];

    public function __construct(
        mixed $repository = null
    ) {
        $this->repository =
            $repository ?? $this->resolveRepository();
    }

    protected function resolveRepository(): mixed
    {
        if (class_exists('PaymentRepository')) {
            return new PaymentRepository();
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Create Payment
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data,
        array $context = []
    ): mixed {
        $userId =
            $this->resolveUserId(
                $context,
                $data
            );

        $payload =
            $this->validatePaymentData(
                $data
            );

        $payload['user_id'] =
            $userId;

        $payload['status'] =
            $payload['status']
            ?? 'pending';

        $payload['currency'] =
            strtoupper(
                $payload['currency']
                ?? 'EGP'
            );

        if ($this->repository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'create'
                )
            ) {
                return
                    $this->repository->create(
                        $payload
                    );
            }

            if (
                method_exists(
                    $this->repository,
                    'insert'
                )
            ) {
                return
                    $this->repository->insert(
                        $payload
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to create payment.'
            );
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Payment
    |--------------------------------------------------------------------------
    */

    public function find(
        int $paymentId,
        array $context = []
    ): mixed {
        $paymentId =
            $this->validateId(
                $paymentId,
                'payment ID'
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
                $payment =
                    $this->repository->find(
                        $paymentId
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'findById'
                )
            ) {
                $payment =
                    $this->repository->findById(
                        $paymentId
                    );
            } elseif (
                method_exists(
                    $this->repository,
                    'getById'
                )
            ) {
                $payment =
                    $this->repository->getById(
                        $paymentId
                    );
            } else {
                return null;
            }

            $this->assertPaymentAccess(
                $payment,
                $context
            );

            return $payment;
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to load payment.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | List Payments
    |--------------------------------------------------------------------------
    */

    public function list(
        array $filters = [],
        array $context = []
    ): array {
        $filters =
            $this->normalizeFilters(
                $filters
            );

        $this->applyContextFilter(
            $filters,
            $context
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
                'Unable to load payments.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Payment
    |--------------------------------------------------------------------------
    */

    public function update(
        int $paymentId,
        array $data,
        array $context = []
    ): mixed {
        $payment =
            $this->find(
                $paymentId,
                $context
            );

        if ($payment === null) {
            throw new RuntimeException(
                'Payment not found.'
            );
        }

        $payload =
            $this->sanitizeUpdateData(
                $data
            );

        if (empty($payload)) {
            throw new InvalidArgumentException(
                'No valid payment data was provided.'
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
                return
                    $this->repository->update(
                        $paymentId,
                        $payload
                    );
            }

            if (
                method_exists(
                    $this->repository,
                    'edit'
                )
            ) {
                return
                    $this->repository->edit(
                        $paymentId,
                        $payload
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to update payment.'
            );
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Management
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        int $paymentId,
        string $status,
        array $context = [],
        string $note = ''
    ): mixed {
        $paymentId =
            $this->validateId(
                $paymentId,
                'payment ID'
            );

        $status =
            strtolower(
                trim($status)
            );

        if (
            !in_array(
                $status,
                $this->allowedStatuses,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid payment status.'
            );
        }

        $payment =
            $this->find(
                $paymentId,
                $context
            );

        if ($payment === null) {
            throw new RuntimeException(
                'Payment not found.'
            );
        }

        if (
            !$this->isValidTransition(
                $this->getPaymentValue(
                    $payment,
                    'status'
                ),
                $status
            )
        ) {
            throw new RuntimeException(
                'Invalid payment status transition.'
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
                return
                    $this->repository->changeStatus(
                        $paymentId,
                        $status,
                        $note
                    );
            }

            if (
                method_exists(
                    $this->repository,
                    'setStatus'
                )
            ) {
                return
                    $this->repository->setStatus(
                        $paymentId,
                        $status,
                        $note
                    );
            }

            if (
                method_exists(
                    $this->repository,
                    'update'
                )
            ) {
                $data = [
                    'status' => $status
                ];

                if ($note !== '') {
                    $data['status_note'] = $note;
                }

                return
                    $this->repository->update(
                        $paymentId,
                        $data
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to change payment status.'
            );
        }

        return false;
    }

    public function markProcessing(
        int $paymentId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $paymentId,
            'processing',
            $context
        );
    }

    public function markPaid(
        int $paymentId,
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $paymentId,
            'paid',
            $context
        );
    }

    public function markFailed(
        int $paymentId,
        string $reason = '',
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $paymentId,
            'failed',
            $context,
            $reason
        );
    }

    public function cancel(
        int $paymentId,
        string $reason = '',
        array $context = []
    ): mixed {
        return $this->changeStatus(
            $paymentId,
            'cancelled',
            $context,
            $reason
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Refunds
    |--------------------------------------------------------------------------
    */

    public function refund(
        int $paymentId,
        ?float $amount = null,
        string $reason = '',
        array $context = []
    ): mixed {
        $paymentId =
            $this->validateId(
                $paymentId,
                'payment ID'
            );

        $payment =
            $this->find(
                $paymentId,
                $context
            );

        if ($payment === null) {
            throw new RuntimeException(
                'Payment not found.'
            );
        }

        $status =
            strtolower(
                (string)
                $this->getPaymentValue(
                    $payment,
                    'status'
                )
            );

        if ($status !== 'paid') {
            throw new RuntimeException(
                'Only paid payments can be refunded.'
            );
        }

        $total =
            (float)
            (
                $this->getPaymentValue(
                    $payment,
                    'amount'
                )
                ?? 0
            );

        $refunded =
            (float)
            (
                $this->getPaymentValue(
                    $payment,
                    'refunded_amount'
                )
                ?? 0
            );

        $remaining =
            max(
                0,
                $total - $refunded
            );

        if ($amount === null) {
            $amount = $remaining;
        }

        $amount =
            round(
                (float) $amount,
                2
            );

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'Refund amount must be greater than zero.'
            );
        }

        if ($amount > $remaining) {
            throw new InvalidArgumentException(
                'Refund amount exceeds the remaining payment amount.'
            );
        }

        if ($this->repository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'refund'
                )
            ) {
                return
                    $this->repository->refund(
                        $paymentId,
                        $amount,
                        $reason
                    );
            }

            if (
                method_exists(
                    $this->repository,
                    'createRefund'
                )
            ) {
                return
                    $this->repository->createRefund(
                        $paymentId,
                        [
                            'amount' => $amount,
                            'reason' => $reason
                        ]
                    );
            }

            if (
                method_exists(
                    $this->repository,
                    'update'
                )
            ) {
                $newRefunded =
                    round(
                        $refunded + $amount,
                        2
                    );

                $newStatus =
                    $newRefunded >= $total
                        ? 'refunded'
                        : 'partially_refunded';

                return
                    $this->repository->update(
                        $paymentId,
                        [
                            'refunded_amount' =>
                                $newRefunded,
                            'status' =>
                                $newStatus,
                            'refund_reason' =>
                                $reason
                        ]
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to process refund.'
            );
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Confirmation
    |--------------------------------------------------------------------------
    */

    public function confirm(
        int $paymentId,
        array $transactionData = [],
        array $context = []
    ): mixed {
        $payment =
            $this->find(
                $paymentId,
                $context
            );

        if ($payment === null) {
            throw new RuntimeException(
                'Payment not found.'
            );
        }

        if ($this->repository === null) {
            return false;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'confirm'
                )
            ) {
                return
                    $this->repository->confirm(
                        $paymentId,
                        $transactionData
                    );
            }

            $data = [
                'status' => 'paid'
            ];

            $allowedTransactionFields = [
                'transaction_id',
                'gateway',
                'gateway_reference',
                'paid_at',
                'payment_method',
                'gateway_response'
            ];

            foreach (
                $allowedTransactionFields
                as $field
            ) {
                if (
                    array_key_exists(
                        $field,
                        $transactionData
                    )
                ) {
                    $data[$field] =
                        $transactionData[$field];
                }
            }

            if (
                method_exists(
                    $this->repository,
                    'update'
                )
            ) {
                return
                    $this->repository->update(
                        $paymentId,
                        $data
                    );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to confirm payment.'
            );
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction Lookup
    |--------------------------------------------------------------------------
    */

    public function findByTransactionId(
        string $transactionId,
        array $context = []
    ): mixed {
        $transactionId =
            trim($transactionId);

        if ($transactionId === '') {
            throw new InvalidArgumentException(
                'Transaction ID is required.'
            );
        }

        if ($this->repository === null) {
            return null;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'findByTransactionId'
                )
            ) {
                $payment =
                    $this->repository
                        ->findByTransactionId(
                            $transactionId
                        );
            } elseif (
                method_exists(
                    $this->repository,
                    'findByReference'
                )
            ) {
                $payment =
                    $this->repository
                        ->findByReference(
                            $transactionId
                        );
            } else {
                return null;
            }

            $this->assertPaymentAccess(
                $payment,
                $context
            );

            return $payment;
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to find payment transaction.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Payments
    |--------------------------------------------------------------------------
    */

    public function getUserPayments(
        int $userId,
        array $filters = [],
        array $context = []
    ): array {
        $userId =
            $this->validateId(
                $userId,
                'user ID'
            );

        $this->assertUserAccess(
            $userId,
            $context
        );

        $filters['user_id'] =
            $userId;

        return $this->list(
            $filters,
            [
                'is_admin' => true
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Totals
    |--------------------------------------------------------------------------
    */

    public function getUserTotalPaid(
        int $userId,
        array $context = []
    ): float {
        $userId =
            $this->validateId(
                $userId,
                'user ID'
            );

        $this->assertUserAccess(
            $userId,
            $context
        );

        if ($this->repository === null) {
            return 0.0;
        }

        try {
            if (
                method_exists(
                    $this->repository,
                    'getUserTotalPaid'
                )
            ) {
                return (float)
                    $this->repository
                        ->getUserTotalPaid(
                            $userId
                        );
            }

            if (
                method_exists(
                    $this->repository,
                    'sumPaidByUser'
                )
            ) {
                return (float)
                    $this->repository
                        ->sumPaidByUser(
                            $userId
                        );
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Unable to calculate user payment total.'
            );
        }

        return 0.0;
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Validation
    |--------------------------------------------------------------------------
    */

    public function validate(
        array $data
    ): array {
        $errors = [];

        $amount =
            $data['amount']
            ?? null;

        if ($amount === null) {
            $errors['amount'] =
                'Payment amount is required.';
        } elseif (
            !is_numeric($amount) ||
            (float) $amount <= 0
        ) {
            $errors['amount'] =
                'Payment amount must be greater than zero.';
        }

        $currency =
            strtoupper(
                trim(
                    (string)
                    (
                        $data['currency']
                        ?? 'EGP'
                    )
                )
            );

        if (
            !preg_match(
                '/^[A-Z]{3}$/',
                $currency
            )
        ) {
            $errors['currency'] =
                'Invalid currency code.';
        }

        if (
            isset($data['payment_method']) &&
            trim(
                (string)
                $data['payment_method']
            ) === ''
        ) {
            $errors['payment_method'] =
                'Payment method cannot be empty.';
        }

        if (
            isset($data['status']) &&
            !in_array(
                strtolower(
                    trim(
                        (string)
                        $data['status']
                    )
                ),
                $this->allowedStatuses,
                true
            )
        ) {
            $errors['status'] =
                'Invalid payment status.';
        }

        return $errors;
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Validation
    |--------------------------------------------------------------------------
    */

    protected function validatePaymentData(
        array $data
    ): array {
        $errors =
            $this->validate(
                $data
            );

        if (!empty($errors)) {
            throw new InvalidArgumentException(
                implode(
                    ' ',
                    array_values($errors)
                )
            );
        }

        $allowed = [
            'user_id',
            'order_id',
            'training_id',
            'company_id',
            'amount',
            'currency',
            'payment_method',
            'status',
            'transaction_id',
            'gateway',
            'gateway_reference',
            'description',
            'metadata',
            'paid_at',
            'expires_at'
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (
                array_key_exists(
                    $field,
                    $data
                )
            ) {
                $payload[$field] =
                    $data[$field];
            }
        }

        if (isset($payload['amount'])) {
            $payload['amount'] =
                round(
                    (float)
                    $payload['amount'],
                    2
                );
        }

        if (isset($payload['currency'])) {
            $payload['currency'] =
                strtoupper(
                    trim(
                        (string)
                        $payload['currency']
                    )
                );
        }

        if (isset($payload['status'])) {
            $payload['status'] =
                strtolower(
                    trim(
                        (string)
                        $payload['status']
                    )
                );
        }

        return $payload;
    }

    protected function sanitizeUpdateData(
        array $data
    ): array {
        $allowed = [
            'amount',
            'currency',
            'payment_method',
            'description',
            'metadata',
            'transaction_id',
            'gateway',
            'gateway_reference',
            'status',
            'status_note',
            'paid_at',
            'expires_at',
            'refunded_amount',
            'refund_reason'
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (
                array_key_exists(
                    $field,
                    $data
                )
            ) {
                $payload[$field] =
                    $data[$field];
            }
        }

        if (isset($payload['amount'])) {
            $payload['amount'] =
                round(
                    (float)
                    $payload['amount'],
                    2
                );
        }

        if (isset($payload['refunded_amount'])) {
            $payload['refunded_amount'] =
                round(
                    (float)
                    $payload['refunded_amount'],
                    2
                );
        }

        if (isset($payload['currency'])) {
            $payload['currency'] =
                strtoupper(
                    trim(
                        (string)
                        $payload['currency']
                    )
                );
        }

        if (isset($payload['status'])) {
            $status =
                strtolower(
                    trim(
                        (string)
                        $payload['status']
                    )
                );

            if (
                !in_array(
                    $status,
                    $this->allowedStatuses,
                    true
                )
            ) {
                throw new InvalidArgumentException(
                    'Invalid payment status.'
                );
            }

            $payload['status'] =
                $status;
        }

        return $payload;
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    protected function assertPaymentAccess(
        mixed $payment,
        array $context
    ): void {
        if ($payment === null) {
            return;
        }

        if (
            $this->isAdmin(
                $context
            )
        ) {
            return;
        }

        $paymentUserId =
            (int)
            (
                $this->getPaymentValue(
                    $payment,
                    'user_id'
                )
                ?? 0
            );

        $contextUserId =
            (int)
            (
                $context['user_id']
                ?? 0
            );

        if (
            $paymentUserId > 0 &&
            $contextUserId > 0 &&
            $paymentUserId !== $contextUserId
        ) {
            throw new RuntimeException(
                'Unauthorized payment access.'
            );
        }
    }

    protected function assertUserAccess(
        int $userId,
        array $context
    ): void {
        if (
            $this->isAdmin(
                $context
            )
        ) {
            return;
        }

        $contextUserId =
            (int)
            (
                $context['user_id']
                ?? 0
            );

        if (
            $contextUserId <= 0 ||
            $contextUserId !== $userId
        ) {
            throw new RuntimeException(
                'Unauthorized user payment access.'
            );
        }
    }

    protected function isAdmin(
        array $context
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
                        $context['role']
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

    protected function resolveUserId(
        array $context,
        array $data
    ): int {
        $userId =
            (int)
            (
                $data['user_id']
                ?? $context['user_id']
                ?? 0
            );

        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'User ID is required for payment.'
            );
        }

        if (
            !$this->isAdmin($context) &&
            isset($context['user_id']) &&
            (int)
            $context['user_id'] !== $userId
        ) {
            throw new RuntimeException(
                'Unauthorized payment creation.'
            );
        }

        return $userId;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Transitions
    |--------------------------------------------------------------------------
    */

    protected function isValidTransition(
        mixed $current,
        string $next
    ): bool {
        $current =
            strtolower(
                trim(
                    (string)
                    $current
                )
            );

        if ($current === '') {
            return true;
        }

        $transitions = [
            'pending' => [
                'processing',
                'paid',
                'failed',
                'cancelled'
            ],

            'processing' => [
                'paid',
                'failed',
                'cancelled'
            ],

            'paid' => [
                'refunded',
                'partially_refunded'
            ],

            'partially_refunded' => [
                'refunded'
            ],

            'failed' => [
                'pending',
                'processing',
                'cancelled'
            ],

            'cancelled' => [],

            'refunded' => []
        ];

        return in_array(
            $next,
            $transitions[$current]
                ?? [],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function getPaymentValue(
        mixed $payment,
        string $key
    ): mixed {
        if (is_array($payment)) {
            return $payment[$key]
                ?? null;
        }

        if (
            is_object($payment)
        ) {
            return $payment->{$key}
                ?? null;
        }

        return null;
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

    protected function normalizeFilters(
        array $filters
    ): array {
        $allowed = [
            'page',
            'limit',
            'user_id',
            'order_id',
            'training_id',
            'company_id',
            'status',
            'payment_method',
            'gateway',
            'transaction_id',
            'search',
            'currency',
            'from',
            'to',
            'sort',
            'order'
        ];

        $result = [];

        foreach ($allowed as $field) {
            if (
                array_key_exists(
                    $field,
                    $filters
                )
            ) {
                $result[$field] =
                    $filters[$field];
            }
        }

        $result['page'] =
            max(
                1,
                (int)
                ($result['page'] ?? 1)
            );

        $result['limit'] =
            min(
                100,
                max(
                    1,
                    (int)
                    ($result['limit'] ?? 20)
                )
            );

        return $result;
    }

    protected function applyContextFilter(
        array &$filters,
        array $context
    ): void {
        if ($this->isAdmin($context)) {
            return;
        }

        $userId =
            (int)
            (
                $context['user_id']
                ?? 0
            );

        if ($userId <= 0) {
            throw new RuntimeException(
                'Authenticated user is required.'
            );
        }

        $filters['user_id'] =
            $userId;
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
                return [
                    'items' =>
                        is_array(
                            $result['items']
                            ?? $result['data']
                            ?? []
                        )
                            ? (
                                $result['items']
                                ?? $result['data']
                            )
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
                ($filters['page'] ?? 1)
            );

        $limit =
            min(
                100,
                max(
                    1,
                    (int)
                    ($filters['limit'] ?? 20)
                )
            );

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => 0,
            'pages' => 0
        ];
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

    public function getAllowedStatuses(): array
    {
        return $this->allowedStatuses;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function payment_create(
    array $data,
    array $context = []
): mixed {
    return
        (new PaymentService())
            ->create(
                $data,
                $context
            );
}

function payment_find(
    int $paymentId,
    array $context = []
): mixed {
    return
        (new PaymentService())
            ->find(
                $paymentId,
                $context
            );
}

function payment_list(
    array $filters = [],
    array $context = []
): array {
    return
        (new PaymentService())
            ->list(
                $filters,
                $context
            );
}

function payment_confirm(
    int $paymentId,
    array $transactionData = [],
    array $context = []
): mixed {
    return
        (new PaymentService())
            ->confirm(
                $paymentId,
                $transactionData,
                $context
            );
}

function payment_refund(
    int $paymentId,
    ?float $amount = null,
    string $reason = '',
    array $context = []
): mixed {
    return
        (new PaymentService())
            ->refund(
                $paymentId,
                $amount,
                $reason,
                $context
            );
}

function payment_cancel(
    int $paymentId,
    string $reason = '',
    array $context = []
): mixed {
    return
        (new PaymentService())
            ->cancel(
                $paymentId,
                $reason,
                $context
            );
}
