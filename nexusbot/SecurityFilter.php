<?php
/**
 * SecurityFilter.php
 * NexusBot Security Layer - Enforces role-based access control and data isolation
 * 
 * This is the core security component that ensures:
 * 1. Users can only access data they're authorized to see
 * 2. Company isolation (multi-tenancy)
 * 3. Credential protection
 * 4. Data ownership verification
 */

class SecurityFilter
{
    private $mysqli;
    private $userId;
    private $companyId;
    private $roleId;
    private $roleName;
    private $employeeId;

    // Role constants matching database
    const ROLE_SUPER_ADMIN = 'Super Admin';
    const ROLE_COMPANY_ADMIN = 'Company Admin';
    const ROLE_HR_MANAGER = 'HR Manager';
    const ROLE_MANAGER = 'Manager';
    const ROLE_EMPLOYEE = 'Employee';
    const ROLE_AUDITOR = 'Auditor';
    const ROLE_CANDIDATE = 'candidate';

    // Protected fields that should never be exposed
    private $protectedFields = [
        'password',
        'csrf_token',
        'reset_token',
        'api_key',
        'secret_key'
    ];

    public function __construct($mysqli, $userContext)
    {
        $this->mysqli = $mysqli;
        $this->userId = $userContext['user_id'] ?? null;
        $this->companyId = $userContext['company_id'] ?? null;
        $this->roleId = $userContext['role_id'] ?? null;
        $this->roleName = $userContext['role_name'] ?? null;
        $this->employeeId = $userContext['employee_id'] ?? null;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }

    /**
     * Check if current user can only access their own data
     */
    public function canAccessOwnDataOnly(): bool
    {
        return in_array($this->roleName, [
            self::ROLE_EMPLOYEE,
            self::ROLE_CANDIDATE
        ]);
    }

    /**
     * Check if current user can access team/subordinate data
     */
    public function canAccessTeamData(): bool
    {
        return in_array($this->roleName, [
            self::ROLE_MANAGER,
            self::ROLE_HR_MANAGER,
            self::ROLE_COMPANY_ADMIN,
            self::ROLE_SUPER_ADMIN
        ]);
    }

    /**
     * Check if current user can access all company data
     */
    public function canAccessCompanyData(): bool
    {
        return in_array($this->roleName, [
            self::ROLE_HR_MANAGER,
            self::ROLE_COMPANY_ADMIN,
            self::ROLE_SUPER_ADMIN,
            self::ROLE_AUDITOR
        ]);
    }

    /**
     * Check if user is Super Admin (system-wide access)
     */
    public function isSuperAdmin(): bool
    {
        return $this->roleName === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user can view other employees' basic info
     */
    public function canViewEmployeeDirectory(): bool
    {
        return in_array($this->roleName, [
            self::ROLE_MANAGER,
            self::ROLE_HR_MANAGER,
            self::ROLE_COMPANY_ADMIN,
            self::ROLE_SUPER_ADMIN
        ]);
    }

    /**
     * Get the employee ID for data filtering
     */
    public function getEmployeeId(): ?int
    {
        return $this->employeeId;
    }

    /**
     * Get the company ID for multi-tenancy filtering
     */
    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    /**
     * Get current role name
     */
    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    /**
     * Filter out credential and sensitive fields from data arrays
     */
    public function filterCredentialFields(array $data): array
    {
        $filtered = [];
        foreach ($data as $key => $value) {
            // Skip protected fields
            if (in_array(strtolower($key), $this->protectedFields)) {
                continue;
            }

            // Recursively filter nested arrays
            if (is_array($value)) {
                $filtered[$key] = $this->filterCredentialFields($value);
            } else {
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }

    /**
     * Validate if user can access specific employee's data
     */
    public function canAccessEmployee(int $targetEmployeeId): bool
    {
        // Super Admin can access anyone
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Own data - always allowed
        if ($this->employeeId === $targetEmployeeId) {
            return true;
        }

        // Employee/Candidate can only access own data
        if ($this->canAccessOwnDataOnly()) {
            return false;
        }

        // For managers, check if target is in their team
        if ($this->roleName === self::ROLE_MANAGER) {
            return $this->isInManagedTeam($targetEmployeeId);
        }

        // HR and Company Admin can access anyone in their company
        if ($this->canAccessCompanyData()) {
            return $this->isInSameCompany($targetEmployeeId);
        }

        return false;
    }

    /**
     * Check if target employee is in a team managed by current user
     */
    private function isInManagedTeam(int $targetEmployeeId): bool
    {
        $sql = "SELECT tm.id 
                FROM team_members tm
                INNER JOIN teams t ON tm.team_id = t.id
                WHERE tm.employee_id = ? 
                AND t.created_by = ?
                AND t.company_id = ?
                LIMIT 1";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iii", $targetEmployeeId, $this->userId, $this->companyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $found = $result->num_rows > 0;
        $stmt->close();

        return $found;
    }

    /**
     * Check if target employee is in the same company
     */
    private function isInSameCompany(int $targetEmployeeId): bool
    {
        $sql = "SELECT e.id 
                FROM employees e
                INNER JOIN users u ON e.user_id = u.id
                WHERE e.id = ? AND u.company_id = ?
                LIMIT 1";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ii", $targetEmployeeId, $this->companyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $found = $result->num_rows > 0;
        $stmt->close();

        return $found;
    }

    /**
     * Sanitize user input to prevent SQL injection
     */
    public function sanitizeInput(string $input): string
    {
        // Remove any potential SQL injection patterns
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    /**
     * Check if a query is attempting to access forbidden data
     */
    public function detectMaliciousIntent(string $message): bool
    {
        $maliciousPatterns = [
            '/password/i',
            '/credential/i',
            '/secret/i',
            '/api.?key/i',
            '/token/i',
            '/hack/i',
            '/inject/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/truncate/i',
            '/union\s+select/i',
            '/<script/i',
            '/javascript:/i'
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate security denial message
     */
    public function getAccessDeniedMessage(string $dataType = 'data'): string
    {
        $messages = [
            "I'm sorry, but you don't have permission to access this {$dataType}.",
            "For security reasons, I can only show you your own {$dataType}.",
            "Access to this {$dataType} is restricted based on your role.",
            "I cannot share other users' {$dataType} for privacy and security reasons."
        ];

        return $messages[array_rand($messages)];
    }

    /**
     * Get credential protection message
     */
    public function getCredentialProtectionMessage(): string
    {
        return "I cannot provide password or credential information for security reasons. If you need to reset a password, please use the password reset feature or contact your administrator.";
    }
}
?>