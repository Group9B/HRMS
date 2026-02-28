# Plan: IoT Attendance System Backend

Build a self-contained IoT attendance module that integrates seamlessly with the existing MySQLi + `query()` architecture, following all existing conventions (no new dependencies, consistent JSON responses, IST timezone).

---

## Step 1 — Run the DB Migration SQL

Add `device_id` and `auth_method` to `attendance` and create the two new tables (`employee_credentials`, `iot_devices`) using the schemas you provided. Run this once via phpMyAdmin or the XAMPP shell. No PHP changes needed for this step.

```sql
CREATE TABLE `employee_credentials` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `type` enum('rfid', 'fingerprint', 'face_id') NOT NULL,
  `identifier_value` varchar(255) NOT NULL COMMENT 'The RFID UID or Fingerprint ID',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_credential` (`type`, `identifier_value`)
) ENGINE=InnoDB;

CREATE TABLE `iot_devices` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `device_name` varchar(100) NOT NULL,
  `device_token` varchar(255) UNIQUE NOT NULL COMMENT 'Secret key for API Bearer Auth',
  `location` varchar(100),
  `status` enum('active', 'inactive') DEFAULT 'active',
  `last_heartbeat` timestamp NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE `attendance`
ADD COLUMN `device_id` int(11) AFTER `employee_id`,
ADD COLUMN `auth_method` enum('rfid', 'fingerprint', 'face_id') AFTER `status`,
ADD CONSTRAINT `fk_attendance_device` FOREIGN KEY (`device_id`) REFERENCES `iot_devices`(`id`) ON DELETE SET NULL;
```

---

## Step 2 — Create `middleware/verifyDevice.php`

Create a new file at `c:\xampp\htdocs\HRMS\middleware\verifyDevice.php`.

The `verifyDevice()` function will:
- Extract the `Authorization` header using `getallheaders()` (with Apache fallback via `$_SERVER['HTTP_AUTHORIZATION']`)
- Strip the `Bearer ` prefix from the token
- Run a single `query($mysqli, "SELECT * FROM iot_devices WHERE device_token = ? AND status = 'active'", [$token])`
- On success: update `last_heartbeat` to `NOW()` and return the device row array
- On failure (no header / bad token / inactive device): send `401` JSON `{ success: false, message: "Unauthorized device" }` and `exit()`

> **Note:** This file must `require_once '../config/db.php'` itself since `$mysqli` is needed and this is included from the `api/` directory.

---

## Step 3 — Create `api/iot_attendance.php`

Create `c:\xampp\htdocs\HRMS\api\iot_attendance.php`. This is the single endpoint the ESP32 calls. It handles:

1. **Device Auth** — `require_once '../middleware/verifyDevice.php'` then `$device = verifyDevice()`
2. **Parse JSON body** — `json_decode(file_get_contents('php://input'))`, validate that `auth_type` and `identifier_value` are present, return `400` if missing
3. **Look up employee credential** — `query()` on `employee_credentials JOIN employees` matching `type` and `identifier_value`, return `404` if not found
4. **Today's attendance query** — fetch the **last** attendance row for that `employee_id` where `date = CURDATE()`, ordered by `id DESC LIMIT 1`
5. **Toggle logic:**
   - No row today, OR last row already has `check_out` set → **INSERT** `(employee_id, date=CURDATE(), check_in=NOW(), status='present', device_id, auth_method)`
   - Last row has `check_in` but `check_out` IS NULL → **UPDATE** that row with `check_out=NOW()`
6. **Return structured JSON:**
   ```json
   {
     "success": true,
     "action": "checked_in" | "checked_out",
     "message": "Welcome, John Doe" | "Goodbye, John Doe",
     "data": { "employee_name": "...", "timestamp": "HH:MM:SS", "device": "..." }
   }
   ```

---

## Step 4 — Create `api/iot_heartbeat.php` *(optional but recommended)*

A minimal endpoint `c:\xampp\htdocs\HRMS\api\iot_heartbeat.php` the ESP32 pings every 30s. Calls `verifyDevice()` (which already updates `last_heartbeat`) and returns `{ success: true, server_time: "..." }`. Lets the OLED display show synced server time.

---

## Step 5 — Register Devices via Admin UI *(out of scope for now but noted)*

`iot_devices` rows need to be inserted manually via phpMyAdmin or a future admin UI page. The `device_token` should be a long random string (e.g., `bin2hex(random_bytes(32))`).

---

## Further Considerations

1. **`attendance` UNIQUE key conflict** — The existing table may have a `UNIQUE(employee_id, date)` constraint. The toggle logic uses `INSERT` for new check-ins and `UPDATE` for check-outs, so there's no conflict — but confirm this key exists before running the migration so the IoT inserts don't clash with the web UI's `INSERT ... ON DUPLICATE KEY UPDATE` pattern.

2. **Timezone** — `config/db.php` already sets `date_default_timezone_set('Asia/Kolkata')`, so `NOW()` / `date()` in PHP will return IST. The new API should also run `$mysqli->query("SET time_zone = '+05:30'")`  after connection to ensure MySQL `NOW()` is also IST-aligned.

3. **Half-day logic** — The existing `api_employee_attendance.php` calculates half-day on check-out based on hours worked vs. shift. Should the IoT check-out also apply this logic? If yes, the `iot_attendance.php` will need to replicate the shift lookup and `hours_worked` calculation from `api_employee_attendance.php` — worth confirming before implementation.
