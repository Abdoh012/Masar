<?php

/**
 * MASAR - Payment Repository
 *
 * Responsible for persistence and database access
 * related to payments.
 */

class PaymentRepository
{
    protected mixed $db;

    protected string $table = 'payments';

    public function __construct(mixed $db = null)
    {
        $this->db =
            $db
            ?? ($GLOBALS['db'] ?? null)
            ?? ($GLOBALS['pdo'] ?? null);

        if ($this->db === null) {
            throw new RuntimeException(
                'Database connection is required.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(array $data): mixed
    {
        if (empty($data)) {
            return false;
        }

        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $column => $value) {
            if (!$this->isSafeIdentifier($column)) {
                continue;
            }

            $columns[] = $column;

            $placeholder =
                ':p_' . count($placeholders);

            $placeholders[] = $placeholder;

            $params[
                substr($placeholder, 1)
            ] = $this->normalizeValue($value);
        }

        if (empty($columns)) {
            return false;
        }

        $sql = "
            INSERT INTO {$this->table}
            (" . implode(', ', $columns) . ")
            VALUES
            (" . implode(', ', $placeholders) . ")
        ";

        $statement =
            $this->execute(
                $sql,
                $params
            );

        if (!$statement) {
            return false;
        }

        return $this->lastInsertId();
    }

    public function insert(array $data): mixed
    {
        return $this->create($data);
    }

    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function find(int $paymentId): mixed
    {
        if ($paymentId <= 0) {
            return null;
        }

        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE id = :id
            LIMIT 1
        ";

        return $this->fetchOne(
            $sql,
            [
                'id' => $paymentId
            ]
        );
    }

    public function findById(int $paymentId): mixed
    {
        return $this->find($paymentId);
    }

    public function getById(int $paymentId): mixed
    {
        return $this->find($paymentId);
    }

    /*
    |--------------------------------------------------------------------------
    | Find By Transaction
    |--------------------------------------------------------------------------
    */

    public function findByTransactionId(
        string $transactionId
    ): mixed {
        $transactionId =
            trim($transactionId);

        if ($transactionId === '') {
            return null;
        }

        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE transaction_id = :transaction_id
            LIMIT 1
        ";

        return $this->fetchOne(
            $sql,
            [
                'transaction_id' =>
                    $transactionId
            ]
        );
    }

    public function findByReference(
        string $reference
    ): mixed {
        $reference =
            trim($reference);

        if ($reference === '') {
            return null;
        }

        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE
                transaction_id = :reference
                OR gateway_reference = :reference
            LIMIT 1
        ";

        return $this->fetchOne(
            $sql,
            [
                'reference' => $reference
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | List / Pagination
    |--------------------------------------------------------------------------
    */

    public function getAll(
        array $filters = []
    ): array {
        return $this->paginate($filters);
    }

    public function all(
        array $filters = []
    ): array {
        return $this->paginate($filters);
    }

    public function paginate(
        array $filters = []
    ): array {
        $conditions = [];
        $params = [];

        $this->applyFilters(
            $filters,
            $conditions,
            $params
        );

        $where = '';

        if (!empty($conditions)) {
            $where =
                'WHERE ' .
                implode(
                    ' AND ',
                    $conditions
                );
        }

        $sort =
            $this->safeSort(
                $filters['sort'] ?? 'id'
            );

        $order =
            $this->safeOrder(
                $filters['order'] ?? 'DESC'
            );

        $limit =
            $this->getLimit(
                $filters
            );

        $page =
            $this->getPage(
                $filters
            );

        $offset =
            ($page - 1) *
            $limit;

        $countSql = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            {$where}
        ";

        $countRow =
            $this->fetchOne(
                $countSql,
                $params
            );

        $total =
            (int)
            ($countRow['total'] ?? 0);

        $sql = "
            SELECT *
            FROM {$this->table}
            {$where}
            ORDER BY {$sort} {$order}
            LIMIT :limit
            OFFSET :offset
        ";

        $queryParams = $params;

        $queryParams['limit'] =
            $limit;

        $queryParams['offset'] =
            $offset;

        $items =
            $this->fetchAll(
                $sql,
                $queryParams
            );

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' =>
                    $limit > 0
                        ? (int)
                            ceil(
                                $total /
                                $limit
                            )
                        : 0
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        int $paymentId,
        array $data
    ): bool {
        if (
            $paymentId <= 0 ||
            empty($data)
        ) {
            return false;
        }

        $sets = [];
        $params = [
            'id' => $paymentId
        ];

        foreach ($data as $column => $value) {
            if (
                !$this->isSafeIdentifier(
                    $column
                )
            ) {
                continue;
            }

            if ($column === 'id') {
                continue;
            }

            $placeholder =
                ':field_' .
                count($sets);

            $sets[] =
                "{$column} = {$placeholder}";

            $params[
                substr(
                    $placeholder,
                    1
                )
            ] =
                $this->normalizeValue(
                    $value
                );
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "
            UPDATE {$this->table}
            SET " .
            implode(', ', $sets) . "
            WHERE id = :id
        ";

        $statement =
            $this->execute(
                $sql,
                $params
            );

        return $statement !== false;
    }

    public function edit(
        int $paymentId,
        array $data
    ): bool {
        return $this->update(
            $paymentId,
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        int $paymentId,
        string $status,
        string $note = ''
    ): bool {
        $data = [
            'status' => $status
        ];

        if ($note !== '') {
            $data['status_note'] =
                $note;
        }

        return $this->update(
            $paymentId,
            $data
        );
    }

    public function setStatus(
        int $paymentId,
        string $status,
        string $note = ''
    ): bool {
        return $this->changeStatus(
            $paymentId,
            $status,
            $note
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Confirm Payment
    |--------------------------------------------------------------------------
    */

    public function confirm(
        int $paymentId,
        array $transactionData = []
    ): bool {
        if ($paymentId <= 0) {
            return false;
        }

        $data = [
            'status' => 'paid'
        ];

        $allowed = [
            'transaction_id',
            'gateway',
            'gateway_reference',
            'payment_method',
            'gateway_response',
            'paid_at'
        ];

        foreach ($allowed as $field) {
            if (
                array_key_exists(
                    $field,
                    $transactionData
                )
            ) {
                $data[$field] =
                    $this->normalizeValue(
                        $transactionData[$field]
                    );
            }
        }

        if (
            !isset($data['paid_at'])
        ) {
            $data['paid_at'] =
                date('Y-m-d H:i:s');
        }

        return $this->update(
            $paymentId,
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Refund
    |--------------------------------------------------------------------------
    */

    public function refund(
        int $paymentId,
        float $amount,
        string $reason = ''
    ): bool {
        if (
            $paymentId <= 0 ||
            $amount <= 0
        ) {
            return false;
        }

        $payment =
            $this->find(
                $paymentId
            );

        if ($payment === null) {
            return false;
        }

        $total =
            (float)
            ($payment['amount'] ?? 0);

        $alreadyRefunded =
            (float)
            (
                $payment['refunded_amount']
                ?? 0
            );

        $remaining =
            max(
                0,
                $total - $alreadyRefunded
            );

        if ($amount > $remaining) {
            return false;
        }

        $newRefunded =
            round(
                $alreadyRefunded +
                $amount,
                2
            );

        $newStatus =
            $newRefunded >= $total
                ? 'refunded'
                : 'partially_refunded';

        $data = [
            'refunded_amount' =>
                $newRefunded,
            'status' =>
                $newStatus
        ];

        if ($reason !== '') {
            $data['refund_reason'] =
                $reason;
        }

        return $this->update(
            $paymentId,
            $data
        );
    }

    public function createRefund(
        int $paymentId,
        array $data
    ): bool {
        $amount =
            isset($data['amount'])
                ? (float)
                    $data['amount']
                : 0.0;

        $reason =
            trim(
                (string)
                (
                    $data['reason']
                    ?? ''
                )
            );

        return $this->refund(
            $paymentId,
            $amount,
            $reason
        );
    }

    /*
    |--------------------------------------------------------------------------
    | User Totals
    |--------------------------------------------------------------------------
    */

    public function getUserTotalPaid(
        int $userId
    ): float {
        if ($userId <= 0) {
            return 0.0;
        }

        $sql = "
            SELECT
                COALESCE(
                    SUM(amount),
                    0
                ) AS total
            FROM {$this->table}
            WHERE
                user_id = :user_id
                AND status = 'paid'
        ";

        $row =
            $this->fetchOne(
                $sql,
                [
                    'user_id' => $userId
                ]
            );

        return round(
            (float)
            ($row['total'] ?? 0),
            2
        );
    }

    public function sumPaidByUser(
        int $userId
    ): float {
        return $this->getUserTotalPaid(
            $userId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | User Payments
    |--------------------------------------------------------------------------
    */

    public function getByUser(
        int $userId,
        array $filters = []
    ): array {
        if ($userId <= 0) {
            return [
                'items' => [],
                'pagination' => [
                    'page' => 1,
                    'limit' => 20,
                    'total' => 0,
                    'pages' => 0
                ]
            ];
        }

        $filters['user_id'] =
            $userId;

        return $this->paginate(
            $filters
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Company Payments
    |--------------------------------------------------------------------------
    */

    public function getByCompany(
        int $companyId,
        array $filters = []
    ): array {
        if ($companyId <= 0) {
            return [
                'items' => [],
                'pagination' => [
                    'page' => 1,
                    'limit' => 20,
                    'total' => 0,
                    'pages' => 0
                ]
            ];
        }

        $filters['company_id'] =
            $companyId;

        return $this->paginate(
            $filters
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Training Payments
    |--------------------------------------------------------------------------
    */

    public function getByTraining(
        int $trainingId,
        array $filters = []
    ): array {
        if ($trainingId <= 0) {
            return [
                'items' => [],
                'pagination' => [
                    'page' => 1,
                    'limit' => 20,
                    'total' => 0,
                    'pages' => 0
                ]
            ];
        }

        $filters['training_id'] =
            $trainingId;

        return $this->paginate(
            $filters
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $paymentId
    ): bool {
        if ($paymentId <= 0) {
            return false;
        }

        $sql = "
            DELETE FROM {$this->table}
            WHERE id = :id
        ";

        $statement =
            $this->execute(
                $sql,
                [
                    'id' => $paymentId
                ]
            );

        return $statement !== false;
    }

    public function deleteById(
        int $paymentId
    ): bool {
        return $this->delete(
            $paymentId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    public function exists(
        int $paymentId
    ): bool {
        if ($paymentId <= 0) {
            return false;
        }

        $sql = "
            SELECT 1
            FROM {$this->table}
            WHERE id = :id
            LIMIT 1
        ";

        $row =
            $this->fetchOne(
                $sql,
                [
                    'id' => $paymentId
                ]
            );

        return $row !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    protected function applyFilters(
        array $filters,
        array &$conditions,
        array &$params
    ): void {
        $integerFilters = [
            'user_id',
            'order_id',
            'training_id',
            'company_id'
        ];

        foreach ($integerFilters as $field) {
            if (
                isset($filters[$field]) &&
                (int)
                $filters[$field] > 0
            ) {
                $conditions[] =
                    "{$field} = :{$field}";

                $params[$field] =
                    (int)
                    $filters[$field];
            }
        }

        $stringFilters = [
            'status',
            'payment_method',
            'gateway',
            'currency'
        ];

        foreach ($stringFilters as $field) {
            if (
                isset($filters[$field]) &&
                trim(
                    (string)
                    $filters[$field]
                ) !== ''
            ) {
                $conditions[] =
                    "{$field} = :{$field}";

                $params[$field] =
                    trim(
                        (string)
                        $filters[$field]
                    );
            }
        }

        if (
            isset($filters['transaction_id']) &&
            trim(
                (string)
                $filters['transaction_id']
            ) !== ''
        ) {
            $conditions[] =
                'transaction_id = :transaction_id';

            $params['transaction_id'] =
                trim(
                    (string)
                    $filters['transaction_id']
                );
        }

        if (
            isset($filters['search']) &&
            trim(
                (string)
                $filters['search']
            ) !== ''
        ) {
            $search =
                trim(
                    (string)
                    $filters['search']
                );

            $conditions[] = "
                (
                    transaction_id LIKE :search
                    OR gateway_reference LIKE :search
                    OR description LIKE :search
                )
            ";

            $params['search'] =
                '%' . $search . '%';
        }

        if (
            isset($filters['from']) &&
            trim(
                (string)
                $filters['from']
            ) !== ''
        ) {
            $conditions[] =
                'created_at >= :date_from';

            $params['date_from'] =
                $filters['from'];
        }

        if (
            isset($filters['to']) &&
            trim(
                (string)
                $filters['to']
            ) !== ''
        ) {
            $conditions[] =
                'created_at <= :date_to';

            $params['date_to'] =
                $filters['to'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Database Helpers
    |--------------------------------------------------------------------------
    */

    protected function fetchAll(
        string $sql,
        array $params = []
    ): array {
        $statement =
            $this->prepare(
                $sql,
                $params
            );

        $rows =
            $statement->fetchAll(
                PDO::FETCH_ASSOC
            );

        return is_array($rows)
            ? $rows
            : [];
    }

    protected function fetchOne(
        string $sql,
        array $params = []
    ): mixed {
        $statement =
            $this->prepare(
                $sql,
                $params
            );

        $row =
            $statement->fetch(
                PDO::FETCH_ASSOC
            );

        return $row === false
            ? null
            : $row;
    }

    protected function execute(
        string $sql,
        array $params = []
    ): mixed {
        $statement =
            $this->prepare(
                $sql,
                $params
            );

        return $statement;
    }

    protected function prepare(
        string $sql,
        array $params = []
    ): mixed {
        if (
            $this->db instanceof PDO
        ) {
            $statement =
                $this->db->prepare(
                    $sql
                );

            foreach ($params as $key => $value) {
                $parameter =
                    ':' .
                    ltrim(
                        (string) $key,
                        ':'
                    );

                $type =
                    is_int($value)
                        ? PDO::PARAM_INT
                        : (
                            is_bool($value)
                                ? PDO::PARAM_BOOL
                                : PDO::PARAM_STR
                        );

                $statement->bindValue(
                    $parameter,
                    $value,
                    $type
                );
            }

            $statement->execute();

            return $statement;
        }

        if (
            is_object($this->db) &&
            method_exists(
                $this->db,
                'prepare'
            )
        ) {
            $statement =
                $this->db->prepare(
                    $sql
                );

            $statement->execute(
                $params
            );

            return $statement;
        }

        throw new RuntimeException(
            'Unsupported database connection.'
        );
    }

    protected function lastInsertId(): mixed
    {
        if (
            $this->db instanceof PDO
        ) {
            return $this->db->lastInsertId();
        }

        if (
            is_object($this->db) &&
            method_exists(
                $this->db,
                'lastInsertId'
            )
        ) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination Helpers
    |--------------------------------------------------------------------------
    */

    protected function getPage(
        array $filters
    ): int {
        return max(
            1,
            (int)
            (
                $filters['page']
                ?? 1
            )
        );
    }

    protected function getLimit(
        array $filters
    ): int {
        return min(
            100,
            max(
                1,
                (int)
                (
                    $filters['limit']
                    ?? 20
                )
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SQL Safety
    |--------------------------------------------------------------------------
    */

    protected function safeSort(
        string $sort
    ): string {
        $allowed = [
            'id',
            'user_id',
            'order_id',
            'training_id',
            'company_id',
            'amount',
            'status',
            'currency',
            'created_at',
            'updated_at',
            'paid_at'
        ];

        return in_array(
            $sort,
            $allowed,
            true
        )
            ? $sort
            : 'id';
    }

    protected function safeOrder(
        string $order
    ): string {
        return strtoupper(
            $order
        ) === 'ASC'
            ? 'ASC'
            : 'DESC';
    }

    protected function isSafeIdentifier(
        string $identifier
    ): bool {
        return (bool)
            preg_match(
                '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                $identifier
            );
    }

    protected function normalizeValue(
        mixed $value
    ): mixed {
        if (
            is_array($value) ||
            is_object($value)
        ) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
            );
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    public function setTable(
        string $table
    ): self {
        if (
            !$this->isSafeIdentifier(
                $table
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid payments table name.'
            );
        }

        $this->table = $table;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getDatabase(): mixed
    {
        return $this->db;
    }
}
