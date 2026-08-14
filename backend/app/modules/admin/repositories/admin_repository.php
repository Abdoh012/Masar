<?php

/**
 * MASAR - Admin Repository
 *
 * Central repository for administrative operations.
 *
 * Service
 *     ↓
 * AdminRepository
 *     ↓
 * Database / PDO
 */

class AdminRepository
{
    protected mixed $db;

    protected string $usersTable = 'users';

    protected string $companiesTable = 'companies';

    protected string $trainingsTable = 'trainings';

    protected string $certificatesTable = 'certificates';

    protected string $appealsTable = 'certificate_appeals';

    protected string $activityTable = 'admin_activity_logs';

    public function __construct(
        mixed $db = null
    ) {
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
    | Dashboard Statistics
    |--------------------------------------------------------------------------
    */

    public function getDashboardStatistics(): array
    {
        return [
            'users' => $this->countTable(
                $this->usersTable
            ),

            'companies' => $this->countTable(
                $this->companiesTable
            ),

            'trainings' => $this->countTable(
                $this->trainingsTable
            ),

            'certificates' => $this->countTable(
                $this->certificatesTable
            ),

            'appeals' => $this->countTable(
                $this->appealsTable
            )
        ];
    }

    public function statistics(): array
    {
        return $this->getDashboardStatistics();
    }

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    public function countUsers(
        array $filters = []
    ): int {
        return $this->countTable(
            $this->usersTable,
            $filters
        );
    }

    public function getUsers(
        array $filters = []
    ): array {
        return $this->getTableRows(
            $this->usersTable,
            $filters
        );
    }

    public function findUser(
        int $userId
    ): mixed {
        return $this->findById(
            $this->usersTable,
            $userId
        );
    }

    public function updateUser(
        int $userId,
        array $data
    ): bool {
        return $this->updateById(
            $this->usersTable,
            $userId,
            $data
        );
    }

    public function deleteUser(
        int $userId
    ): bool {
        return $this->deleteById(
            $this->usersTable,
            $userId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    public function countCompanies(
        array $filters = []
    ): int {
        return $this->countTable(
            $this->companiesTable,
            $filters
        );
    }

    public function getCompanies(
        array $filters = []
    ): array {
        return $this->getTableRows(
            $this->companiesTable,
            $filters
        );
    }

    public function findCompany(
        int $companyId
    ): mixed {
        return $this->findById(
            $this->companiesTable,
            $companyId
        );
    }

    public function updateCompany(
        int $companyId,
        array $data
    ): bool {
        return $this->updateById(
            $this->companiesTable,
            $companyId,
            $data
        );
    }

    public function deleteCompany(
        int $companyId
    ): bool {
        return $this->deleteById(
            $this->companiesTable,
            $companyId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Trainings
    |--------------------------------------------------------------------------
    */

    public function countTrainings(
        array $filters = []
    ): int {
        return $this->countTable(
            $this->trainingsTable,
            $filters
        );
    }

    public function getTrainings(
        array $filters = []
    ): array {
        return $this->getTableRows(
            $this->trainingsTable,
            $filters
        );
    }

    public function findTraining(
        int $trainingId
    ): mixed {
        return $this->findById(
            $this->trainingsTable,
            $trainingId
        );
    }

    public function updateTraining(
        int $trainingId,
        array $data
    ): bool {
        return $this->updateById(
            $this->trainingsTable,
            $trainingId,
            $data
        );
    }

    public function deleteTraining(
        int $trainingId
    ): bool {
        return $this->deleteById(
            $this->trainingsTable,
            $trainingId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Certificates
    |--------------------------------------------------------------------------
    */

    public function countCertificates(
        array $filters = []
    ): int {
        return $this->countTable(
            $this->certificatesTable,
            $filters
        );
    }

    public function getCertificates(
        array $filters = []
    ): array {
        return $this->getTableRows(
            $this->certificatesTable,
            $filters
        );
    }

    public function findCertificate(
        int $certificateId
    ): mixed {
        return $this->findById(
            $this->certificatesTable,
            $certificateId
        );
    }

    public function updateCertificate(
        int $certificateId,
        array $data
    ): bool {
        return $this->updateById(
            $this->certificatesTable,
            $certificateId,
            $data
        );
    }

    public function deleteCertificate(
        int $certificateId
    ): bool {
        return $this->deleteById(
            $this->certificatesTable,
            $certificateId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Appeals
    |--------------------------------------------------------------------------
    */

    public function countAppeals(
        array $filters = []
    ): int {
        return $this->countTable(
            $this->appealsTable,
            $filters
        );
    }

    public function getAppeals(
        array $filters = []
    ): array {
        return $this->getTableRows(
            $this->appealsTable,
            $filters
        );
    }

    public function findAppeal(
        int $appealId
    ): mixed {
        return $this->findById(
            $this->appealsTable,
            $appealId
        );
    }

    public function updateAppeal(
        int $appealId,
        array $data
    ): bool {
        return $this->updateById(
            $this->appealsTable,
            $appealId,
            $data
        );
    }

    public function deleteAppeal(
        int $appealId
    ): bool {
        return $this->deleteById(
            $this->appealsTable,
            $appealId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generic Search
    |--------------------------------------------------------------------------
    */

    public function searchUsers(
        string $query,
        array $filters = []
    ): array {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $sql = "
            SELECT *
            FROM {$this->usersTable}
            WHERE
                name LIKE :query
                OR email LIKE :query
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ";

        return $this->fetchAll(
            $sql,
            [
                'query' => '%' . $query . '%',
                'limit' =>
                    $this->getLimit($filters),
                'offset' =>
                    $this->getOffset($filters)
            ]
        );
    }

    public function searchCompanies(
        string $query,
        array $filters = []
    ): array {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $sql = "
            SELECT *
            FROM {$this->companiesTable}
            WHERE
                name LIKE :query
                OR email LIKE :query
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ";

        return $this->fetchAll(
            $sql,
            [
                'query' => '%' . $query . '%',
                'limit' =>
                    $this->getLimit($filters),
                'offset' =>
                    $this->getOffset($filters)
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Activity Logs
    |--------------------------------------------------------------------------
    */

    public function logAdminAction(
        array $payload
    ): bool {
        if (!$this->tableExists(
            $this->activityTable
        )) {
            return false;
        }

        $adminId =
            (int)
            ($payload['admin_id'] ?? 0);

        $action =
            trim(
                (string)
                ($payload['action'] ?? '')
            );

        $targetId =
            isset($payload['target_id'])
                ? (int)
                    $payload['target_id']
                : null;

        $metadata =
            $payload['metadata'] ?? [];

        if ($action === '') {
            return false;
        }

        $sql = "
            INSERT INTO {$this->activityTable}
            (
                admin_id,
                action,
                target_id,
                metadata,
                created_at
            )
            VALUES
            (
                :admin_id,
                :action,
                :target_id,
                :metadata,
                :created_at
            )
        ";

        return $this->execute(
            $sql,
            [
                'admin_id' => $adminId,
                'action' => $action,
                'target_id' => $targetId,
                'metadata' =>
                    json_encode(
                        $metadata,
                        JSON_UNESCAPED_UNICODE
                    ),
                'created_at' =>
                    $payload['created_at']
                    ?? date('Y-m-d H:i:s')
            ]
        );
    }

    public function logActivity(
        array $payload
    ): bool {
        return $this->logAdminAction(
            $payload
        );
    }

    public function getActivityLogs(
        array $filters = []
    ): array {
        if (!$this->tableExists(
            $this->activityTable
        )) {
            return [];
        }

        $conditions = [];
        $params = [];

        if (
            isset($filters['admin_id'])
        ) {
            $conditions[] =
                'admin_id = :admin_id';

            $params['admin_id'] =
                (int)
                $filters['admin_id'];
        }

        if (
            isset($filters['action'])
        ) {
            $conditions[] =
                'action = :action';

            $params['action'] =
                trim(
                    (string)
                    $filters['action']
                );
        }

        if (
            isset($filters['target_id'])
        ) {
            $conditions[] =
                'target_id = :target_id';

            $params['target_id'] =
                (int)
                $filters['target_id'];
        }

        $where = '';

        if (!empty($conditions)) {
            $where =
                'WHERE ' .
                implode(
                    ' AND ',
                    $conditions
                );
        }

        $limit =
            $this->getLimit($filters);

        $offset =
            $this->getOffset($filters);

        $sql = "
            SELECT *
            FROM {$this->activityTable}
            {$where}
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ";

        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            $sql,
            $params
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generic Helpers
    |--------------------------------------------------------------------------
    */

    protected function countTable(
        string $table,
        array $filters = []
    ): int {
        $this->assertSafeTable(
            $table
        );

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

        $sql = "
            SELECT COUNT(*) AS total
            FROM {$table}
            {$where}
        ";

        $row =
            $this->fetchOne(
                $sql,
                $params
            );

        return max(
            0,
            (int)
            ($row['total'] ?? 0)
        );
    }

    protected function getTableRows(
        string $table,
        array $filters = []
    ): array {
        $this->assertSafeTable(
            $table
        );

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

        $limit =
            $this->getLimit($filters);

        $offset =
            $this->getOffset($filters);

        $sql = "
            SELECT *
            FROM {$table}
            {$where}
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ";

        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            $sql,
            $params
        );
    }

    protected function findById(
        string $table,
        int $id
    ): mixed {
        $this->assertSafeTable(
            $table
        );

        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT *
            FROM {$table}
            WHERE id = :id
            LIMIT 1
        ";

        return $this->fetchOne(
            $sql,
            [
                'id' => $id
            ]
        );
    }

    protected function updateById(
        string $table,
        int $id,
        array $data
    ): bool {
        $this->assertSafeTable(
            $table
        );

        if ($id <= 0 || empty($data)) {
            return false;
        }

        $allowed = [];

        foreach ($data as $column => $value) {
            if (
                preg_match(
                    '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                    (string) $column
                )
            ) {
                $allowed[$column] = $value;
            }
        }

        if (empty($allowed)) {
            return false;
        }

        $sets = [];
        $params = [
            'id' => $id
        ];

        foreach ($allowed as $column => $value) {
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
            ] = $value;
        }

        $sql = "
            UPDATE {$table}
            SET " .
            implode(
                ', ',
                $sets
            ) . "
            WHERE id = :id
        ";

        return $this->execute(
            $sql,
            $params
        );
    }

    protected function deleteById(
        string $table,
        int $id
    ): bool {
        $this->assertSafeTable(
            $table
        );

        if ($id <= 0) {
            return false;
        }

        $sql = "
            DELETE FROM {$table}
            WHERE id = :id
        ";

        return $this->execute(
            $sql,
            [
                'id' => $id
            ]
        );
    }

    protected function applyFilters(
        array $filters,
        array &$conditions,
        array &$params
    ): void {
        if (
            isset($filters['status']) &&
            $filters['status'] !== ''
        ) {
            $conditions[] =
                'status = :status';

            $params['status'] =
                $filters['status'];
        }

        if (
            isset($filters['role']) &&
            $filters['role'] !== ''
        ) {
            $conditions[] =
                'role = :role';

            $params['role'] =
                $filters['role'];
        }

        if (
            isset($filters['user_id'])
        ) {
            $conditions[] =
                'user_id = :user_id';

            $params['user_id'] =
                (int)
                $filters['user_id'];
        }

        if (
            isset($filters['student_id'])
        ) {
            $conditions[] =
                'student_id = :student_id';

            $params['student_id'] =
                (int)
                $filters['student_id'];
        }

        if (
            isset($filters['company_id'])
        ) {
            $conditions[] =
                'company_id = :company_id';

            $params['company_id'] =
                (int)
                $filters['company_id'];
        }

        if (
            isset($filters['training_id'])
        ) {
            $conditions[] =
                'training_id = :training_id';

            $params['training_id'] =
                (int)
                $filters['training_id'];
        }

        if (
            isset($filters['certificate_id'])
        ) {
            $conditions[] =
                'certificate_id = :certificate_id';

            $params['certificate_id'] =
                (int)
                $filters['certificate_id'];
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

            $conditions[] =
                '(
                    name LIKE :search
                    OR email LIKE :search
                )';

            $params['search'] =
                '%' . $search . '%';
        }
    }

    protected function getLimit(
        array $filters
    ): int {
        return min(
            100,
            max(
                1,
                (int)
                ($filters['limit'] ?? 20)
            )
        );
    }

    protected function getOffset(
        array $filters
    ): int {
        $page =
            max(
                1,
                (int)
                ($filters['page'] ?? 1)
            );

        return
            ($page - 1) *
            $this->getLimit(
                $filters
            );
    }

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
                $this->fetchMode()
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
                $this->fetchMode()
            );

        return $row === false
            ? null
            : $row;
    }

    protected function execute(
        string $sql,
        array $params = []
    ): bool {
        $statement =
            $this->prepare(
                $sql,
                $params
            );

        return $statement->rowCount() >= 0;
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

            if (
                method_exists(
                    $statement,
                    'execute'
                )
            ) {
                $statement->execute(
                    $params
                );
            }

            return $statement;
        }

        throw new RuntimeException(
            'Unsupported database connection.'
        );
    }

    protected function fetchMode(): int
    {
        return PDO::FETCH_ASSOC;
    }

    protected function tableExists(
        string $table
    ): bool {
        $this->assertSafeTable(
            $table
        );

        try {
            if (
                $this->db instanceof PDO
            ) {
                $statement =
                    $this->db->prepare(
                        "SELECT 1 FROM {$table} LIMIT 1"
                    );

                $statement->execute();

                return true;
            }
        } catch (Throwable $e) {
            return false;
        }

        return false;
    }

    protected function assertSafeTable(
        string $table
    ): void {
        if (
            !preg_match(
                '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                $table
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid database table name.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    */

    public function setUsersTable(
        string $table
    ): self {
        $this->assertSafeTable($table);

        $this->usersTable = $table;

        return $this;
    }

    public function setCompaniesTable(
        string $table
    ): self {
        $this->assertSafeTable($table);

        $this->companiesTable = $table;

        return $this;
    }

    public function setTrainingsTable(
        string $table
    ): self {
        $this->assertSafeTable($table);

        $this->trainingsTable = $table;

        return $this;
    }

    public function setCertificatesTable(
        string $table
    ): self {
        $this->assertSafeTable($table);

        $this->certificatesTable = $table;

        return $this;
    }

    public function setAppealsTable(
        string $table
    ): self {
        $this->assertSafeTable($table);

        $this->appealsTable = $table;

        return $this;
    }

    public function setActivityTable(
        string $table
    ): self {
        $this->assertSafeTable($table);

        $this->activityTable = $table;

        return $this;
    }

    public function getDatabase(): mixed
    {
        return $this->db;
    }
}
