<?php
/**
 * DynamicQueryBuilder.php
 * Securely constructs SQL queries from AI intents using SchemaConfig.
 */

require_once __DIR__ . '/SchemaConfig.php';

class DynamicQueryBuilder
{
    private $mysqli;
    private $userId;
    private $companyId;
    private $employeeId;
    private $role;

    public function __construct($mysqli, array $userContext)
    {
        $this->mysqli = $mysqli;
        $this->userId = $userContext['user_id'] ?? 0;
        $this->companyId = $userContext['company_id'] ?? 0;
        $this->employeeId = $userContext['employee_id'] ?? 0;

        // Map Role ID to simplified Role String
        $roleId = $userContext['role_id'] ?? 0;
        if ($roleId == 1 || $roleId == 2 || $roleId == 3) {
            $this->role = 'admin';
        } elseif ($roleId == 6) {
            $this->role = 'manager';
        } else {
            $this->role = 'employee';
        }
    }

    /**
     * Build and execute a secure query based on Intent JSON
     * 
     * @param array $intentJson {
     *   "action": "count" | "list",
     *   "entity": "employees" | "tasks" ...,
     *   "filters": {"status": "active", "department": "IT"}
     * }
     */
    public function execute(array $intentJson): array
    {
        $entity = $intentJson['entity'] ?? null;
        $action = $intentJson['action'] ?? 'list';
        $filters = $intentJson['filters'] ?? [];

        // 1. Validate Access
        if (!$this->canAccess($entity)) {
            return [
                'success' => false,
                'message' => "Restricted access: Your role cannot query '$entity'."
            ];
        }

        // 2. Get Tables and Columns
        $schema = SchemaConfig::getSchema();
        if (!isset($schema[$entity])) {
            return ['success' => false, 'message' => "Unknown entity: $entity"];
        }

        $config = $schema[$entity];
        $table = $config['table'];

        // 3. Build Base SQL
        $sql = "SELECT ";
        $params = [];
        $types = "";

        // SELECT Clause
        if ($action === 'count') {
            $sql .= "COUNT(*) as total ";
        } else {
            // Default select limit
            $cols = array_keys($config['columns']);
            // Prefix columns to avoid ambiguity if joined
            $prefixedCols = array_map(function ($c) use ($table) {
                return "$table.$c";
            }, $cols);
            $sql .= implode(", ", $prefixedCols) . " ";
        }

        $sql .= "FROM $table ";

        // 4. Applies Joins (Simple logic: if filtering by dept name, join departments)
        // For now, we assume simple single-table filters or explicit ID filters
        // Future: Add join logic here

        // 5. WHERE Clause (Security + User Filters)
        $whereClauses = [];

        // --- Mandatory Security Scope ---
        if ($this->role === 'employee' && isset($config['columns']['employee_id'])) {
            $whereClauses[] = "$table.employee_id = ?";
            $params[] = $this->employeeId;
            $types .= "i";
        } elseif (($this->role === 'admin' || $this->role === 'manager') && isset($config['columns']['company_id'])) {
            // Some tables have company_id directly
            $whereClauses[] = "$table.company_id = ?";
            $params[] = $this->companyId;
            $types .= "i";
        } elseif (($this->role === 'admin' || $this->role === 'manager') && $entity === 'employees') {
            // Employees table links to users table which has company_id
            $sql .= "INNER JOIN users ON $table.user_id = users.id ";
            $whereClauses[] = "users.company_id = ?";
            $params[] = $this->companyId;
            $types .= "i";
        }

        // --- User Filters ---
        foreach ($filters as $key => $val) {
            if (isset($config['columns'][$key])) {
                // If filter is "department": "IT" but column is "department_id", we need resolution.
                // For this MVP, we assume the AI maps to IDs or simple string matches

                $colType = $config['columns'][$key];

                if ($action === 'count' && $val === 'all')
                    continue; // count all

                if (is_array($val)) {
                    // WHERE IN (...)
                } else {
                    $whereClauses[] = "$table.$key = ?";
                    $params[] = $val;
                    $types .= ($colType === 'int' ? 'i' : 's');
                }
            }
        }

        if (!empty($whereClauses)) {
            $sql .= "WHERE " . implode(" AND ", $whereClauses);
        }

        // Limit
        if ($action === 'list') {
            $sql .= " LIMIT 10";
        }

        // 6. Execute
        try {
            $stmt = $this->mysqli->prepare($sql);
            if (!$stmt) {
                return ['success' => false, 'message' => "SQL Error: " . $this->mysqli->error];
            }
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Format "count" result simply
            if ($action === 'count') {
                $count = $data[0]['total'];
                return [
                    'success' => true,
                    'type' => 'data_count',
                    'message' => "Found $count " . ($count == 1 ? $entity : $entity),
                    'data' => ['count' => $count, 'entity' => $entity]
                ];
            }

            return [
                'success' => true,
                'data' => $data,
                'type' => 'data_list',
                'message' => "Here is the data you requested."
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => "Query execution failed: " . $e->getMessage()];
        }
    }

    private function canAccess(string $entity): bool
    {
        $rules = SchemaConfig::getAccessRules();
        $allowed = $rules[$this->role] ?? [];
        return in_array($entity, $allowed);
    }
}
?>