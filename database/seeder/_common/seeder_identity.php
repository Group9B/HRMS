<?php
/**
 * Shared Identity Generator for Seeders
 * -------------------------------------
 * Generates unique usernames/emails without hex tokens.
 * When DOB is provided, email local-part uses name + DOB
 * with DOB at start or end.
 */

require_once __DIR__ . '/seeder_runtime.php';

/**
 * Build a short slug from a company name.
 * Example: "Navbharat Construct" -> "navbharatconstruct"
 */
function company_slug(string $companyName): string
{
    return strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($companyName)));
}

/**
 * Keep only lowercase letters and digits for username/email parts.
 */
function normalize_name_part(string $value): string
{
    $clean = strtolower(trim($value));
    $clean = preg_replace('/[^a-z0-9]+/', '', $clean);
    return $clean !== '' ? $clean : 'user';
}

/**
 * Convert DOB to YYYYMMDD token if possible.
 */
function birth_token(string $birthDate): string
{
    $digits = preg_replace('/[^0-9]/', '', $birthDate);
    return strlen($digits) >= 8 ? substr($digits, 0, 8) : '';
}

/**
 * Generate a unique username + email pair that does not collide with
 * existing rows in the `users` table.
 *
 * @param PDO    $pdo         Active DB connection
 * @param string $companyName Human-readable company name
 * @param string $roleTag     Short tag: 'owner','hr','mgr','emp'
 * @param string $firstName   Optional first name
 * @param string $lastName    Optional last name
 * @param string $birthDate   Optional DOB in Y-m-d
 * @return array{username: string, email: string}
 */
function generate_credentials(PDO $pdo, string $companyName, string $roleTag, string $firstName = '', string $lastName = '', string $birthDate = ''): array
{
    $slug = company_slug($companyName);
    $domains = ['info.com', 'mail.com'];
    $domain = $domains[array_rand($domains)];

    if ($firstName !== '' && $lastName !== '') {
        $nameBase = normalize_name_part($firstName) . '.' . normalize_name_part($lastName);
    } else {
        $nameBase = $slug . '.' . normalize_name_part($roleTag);
    }

    $dobToken = birth_token($birthDate);
    $needsDobInEmail = $dobToken !== '';

    if ($needsDobInEmail) {
        $baseLocal = (rand(0, 1) === 0) ? ($dobToken . '.' . $nameBase) : ($nameBase . '.' . $dobToken);
    } else {
        $baseLocal = $nameBase;
    }

    $chkUser = $pdo->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
    $chkEmail = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");

    for ($i = 0; $i < 50; $i++) {
        $suffix = $i === 0 ? '' : '.' . ($i + 1);
        $local = $baseLocal . $suffix;
        $username = str_replace('.', '_', $local);
        $email = $local . '@' . $domain;

        $chkUser->execute([$username]);
        if ($chkUser->fetch()) {
            continue;
        }

        $chkEmail->execute([$email]);
        if ($chkEmail->fetch()) {
            continue;
        }

        return ['username' => $username, 'email' => $email];
    }

    $fallbackLocal = $baseLocal . '.' . date('YmdHis');
    $fallbackUsername = str_replace('.', '_', $fallbackLocal);
    return ['username' => $fallbackUsername, 'email' => $fallbackLocal . '@info.com'];
}

/**
 * Check whether a company already has a user with the given role.
 */
function company_has_role(PDO $pdo, int $companyId, int $roleId): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE company_id = ? AND role_id = ? LIMIT 1");
    $stmt->execute([$companyId, $roleId]);
    return (bool) $stmt->fetch();
}
