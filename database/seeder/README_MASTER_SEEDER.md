# HRMS Master Seeder

One command to seed every module in the correct order, pointing at **any** database.

## Quick Start

```bash
# Default: localhost / original_template / root / (no password)
php database/seeder/master_seeder.php

# Custom database
php database/seeder/master_seeder.php --db=hrms_production

# Full override
php database/seeder/master_seeder.php --host=db.example.com --db=hrms --user=admin --pass=secret
```

## CLI Flags

| Flag     | Default             | Env Fallback | Description                          |
| -------- | ------------------- | ------------ | ------------------------------------ |
| `--host` | `localhost`         | `DB_HOST`    | MySQL host                           |
| `--db`   | `original_template` | `DB_NAME`    | Database name                        |
| `--user` | `root`              | `DB_USER`    | MySQL user                           |
| `--pass` | _(empty)_           | `DB_PASS`    | MySQL password                       |
| `--skip` | —                   | —            | Comma-separated seeder names to skip |
| `--only` | —                   | —            | Run **only** these seeders           |

## Execution Order

| #   | Seeder                          | Short Name           | Purpose                                                |
| --- | ------------------------------- | -------------------- | ------------------------------------------------------ |
| 1   | `final_seeder.php`              | `final`              | Creates ~55 Indian companies                           |
| 2   | `company_admin_seeder.php`      | `company_admin`      | One Company Owner per company                          |
| 3   | `detailed_employees_seeder.php` | `detailed_employees` | ~55 employees per company (first 3)                    |
| 4   | `manager_seeder.php`            | `manager`            | One Manager per company (first 3)                      |
| 5   | `teams_seeder.php`              | `teams`              | 5-7 teams per company                                  |
| 6   | `seed_asset_categories.php`     | `asset_categories`   | Default asset categories                               |
| 7   | `asset_seeder.php`              | `asset`              | Assets + assignments                                   |
| 8   | `leave_seeder.php`              | `leave`              | Leave policies, balances, requests                     |
| 9   | `payroll_seeder.php`            | `payroll`            | Payslips for last month                                |
| 10  | `recruitment_seeder.php`        | `recruitment`        | Jobs, candidates, interviews                           |
| 11  | `missing_modules_seeder.php`    | `missing_modules`    | Policies, performance, tickets, feedback               |
| 12  | `navbharat_activity_seeder.php` | `navbharat_activity` | Attendance, tasks, team performance                    |
| 13  | `role_sanitizer_seeder.php`     | `role_sanitizer`     | Post-seed cleanup: remove auditors, deduplicate owners |

## Role Mapping (header.php)

| ID  | Role          |
| --- | ------------- |
| 1   | Admin         |
| 2   | Company Owner |
| 3   | HR            |
| 4   | Employee      |
| 5   | Candidate     |
| 6   | Manager       |

**Rules enforced:**

- No user is assigned the Auditor role (legacy SQL template role).
- Each company gets exactly **one** Company Owner.
- All seeded emails follow the format: `{companySlug}_{roleTag}_{token}@info.com` or `@mail.com`.
- Default password for all seeded users: `Staff12@`

## Excluded Seeders (Obsolete / Diagnostic)

These files are still in the `database/seeder/` folder but are **not** called by the master seeder:

| File                                | Reason                                   |
| ----------------------------------- | ---------------------------------------- |
| `auditor_converter.php`             | One-off migration, creates invalid roles |
| `manager_promoter.php`              | Uses wrong role IDs                      |
| `hr_converter.php`                  | Deduplication utility, not repeatable    |
| `indian_companies_seeder.php`       | Superseded by `final_seeder.php`         |
| `indian_companies_seeder_retry.php` | Duplicate of above                       |
| `list_demo_users_file.php`          | Diagnostic only                          |
| `list_demo_users.php`               | Diagnostic only                          |
| `check_roles.php`                   | Diagnostic dump                          |
| `check_manager_roles.php`           | Diagnostic query                         |
| `verify_seeder.php`                 | Simple count check                       |
| `emp_user_cmp_seed.php`             | Legacy bootstrap with wrong role IDs     |
| `list_roles.php`                    | Diagnostic dump                          |

## Examples

```bash
# Skip payroll and leave seeders
php database/seeder/master_seeder.php --skip=payroll,leave

# Run only company creation + admin seeding
php database/seeder/master_seeder.php --only=final,company_admin

# Seed into a different database
php database/seeder/master_seeder.php --db=hrms_staging
```
