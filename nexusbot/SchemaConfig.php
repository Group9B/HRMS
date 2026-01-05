<?php
/**
 * SchemaConfig.php
 * Defines the "Safe Schema" for the Dynamic Query Engine.
 * 
 * - Whitelists allowable tables and columns for AI queries.
 * - Defines relationships for automatic JOINs.
 * - Maps columns to human-readable aliases.
 */

class SchemaConfig
{
    /**
     * Get the allowed schema definition
     */
    public static function getSchema(): array
    {
        return [
            'employees' => [
                'table' => 'employees',
                'columns' => [
                    'id' => 'int',
                    'first_name' => 'string',
                    'last_name' => 'string',
                    'department_id' => 'int',
                    'designation_id' => 'int',
                    'status' => 'string', // active, inactive
                    'gender' => 'string',
                    'date_of_joining' => 'date'
                ],
                'joins' => [
                    'department' => ['table' => 'departments', 'on' => 'employees.department_id = departments.id'],
                    'designation' => ['table' => 'designations', 'on' => 'employees.designation_id = designations.id'],
                    'team_members' => ['table' => 'team_members', 'on' => 'employees.id = team_members.employee_id']
                ]
            ],
            'departments' => [
                'table' => 'departments',
                'columns' => [
                    'id' => 'int',
                    'name' => 'string'
                ]
            ],
            'teams' => [
                'table' => 'teams',
                'columns' => [
                    'id' => 'int',
                    'name' => 'string',
                    'created_by' => 'int', // Manager User ID
                    'company_id' => 'int'
                ]
            ],
            'tasks' => [
                'table' => 'tasks',
                'columns' => [
                    'id' => 'int',
                    'title' => 'string',
                    'status' => 'string', // pending, in_progress, completed
                    'priority' => 'string',
                    'due_date' => 'date',
                    'employee_id' => 'int'
                ]
            ],
            'leaves' => [
                'table' => 'leaves',
                'columns' => [
                    'id' => 'int',
                    'leave_type' => 'string',
                    'status' => 'string', // pending, approved, rejected
                    'start_date' => 'date',
                    'end_date' => 'date',
                    'employee_id' => 'int'
                ]
            ],
            'attendance' => [
                'table' => 'attendance',
                'columns' => [
                    'id' => 'int',
                    'date' => 'date',
                    'status' => 'string', // present, absent, late
                    'check_in' => 'datetime',
                    'check_out' => 'datetime',
                    'employee_id' => 'int'
                ]
            ]
        ];
    }

    /**
     * Get role-based access rules
     * Defines which entities each role can query
     */
    public static function getAccessRules(): array
    {
        return [
            'admin' => ['employees', 'departments', 'teams', 'tasks', 'leaves', 'attendance'], // Company Admin
            'manager' => ['employees', 'departments', 'teams', 'tasks', 'leaves', 'attendance'], // Manager (Filtered by Team)
            'employee' => ['tasks', 'leaves', 'attendance'] // Employee (Own Data Only)
        ];
    }

    /**
     * Get data visibility scopes (conditions applied automatically)
     */
    public static function getScopeConditions(string $role, int $userId, int $companyId, int $employeeId): array
    {
        // Base scope: Always restrict by Company ID where applicable
        // Note: Logic allows DB structure to define how company_id is linked

        $scopes = [];

        // Admin: Access all data in their company
        if ($role === 'admin' || $role === 'hr_manager') {
            // For tables linked via users or directly having company_id
            // This logic needs to be handled by the QueryBuilder dynamically joining users table or using direct columns
            $scopes['global'] = "company_id = $companyId";
        }

        // Manager: Access their team + own data
        if ($role === 'manager') {
            // Logic for "My Team" is complex, often involves subqueries.
            // For simplicity in this version, we might restrict to created_by = userId for teams
            // or use specific Team IDs.
            $scopes['teams'] = "created_by = $userId";
            // For employees, we'll need a way to filter by "managed by me" -> usually via team_members
        }

        // Employee: Access ONLY their own data
        if ($role === 'employee') {
            $scopes['global'] = "employee_id = $employeeId";
        }

        return $scopes;
    }
}
?>