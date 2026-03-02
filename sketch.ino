/*
 * ═══════════════════════════════════════════════════════════════
 *  HRMS IoT Attendance System - ESP32 + MFRC522 RFID Reader
 * ═══════════════════════════════════════════════════════════════
 *  Components: ESP32 DevKit v4 + RC522 RFID Module + 5 RFID Cards
 *
 *  Wiring (ESP32 → RC522):
 *    3.3V  → 3.3V   (Power - MUST be 3.3V, NOT 5V!)
 *    GND   → GND
 *    D5    → SDA/SS  (Chip Select)
 *    D18   → SCK     (SPI Clock)
 *    D23   → MOSI    (Master Out Slave In)
 *    D19   → MISO    (Master In Slave Out)
 *    D15   → RST     (Reset)
 *
 *  How it works:
 *    1. ESP32 connects to your local WiFi
 *    2. Sends heartbeat to server every 30 seconds
 *    3. When RFID card is tapped, reads UID
 *    4. POSTs the UID to /api/iot_attendance.php
 *    5. Server toggles check-in / check-out
 *    6. Response printed to Serial Monitor
 * ═══════════════════════════════════════════════════════════════
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// ─────────────────────────────────────────────────────────────
// CONFIGURATION - UPDATE THESE VALUES
// ─────────────────────────────────────────────────────────────
const char* WIFI_SSID     = "YOUR_WIFI_SSID";       // ← Your WiFi name
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";    // ← Your WiFi password

// Server URL - Use your PC's local IP (run 'ipconfig' in cmd to find it)
// Example: "http://192.168.1.100/hrms/api"
const char* SERVER_BASE_URL = "http://YOUR_PC_IP/hrms/api";

// Device token from iot_devices table (run iot_test.php to generate one)
const char* DEVICE_TOKEN = "YOUR_DEVICE_TOKEN_HERE";

// ─────────────────────────────────────────────────────────────
// PIN DEFINITIONS (ESP32 → RC522)
// ─────────────────────────────────────────────────────────────
#define SS_PIN    5    // SDA/SS → GPIO 5
#define RST_PIN   15   // RST    → GPIO 15
// SCK  → GPIO 18 (default SPI)
// MOSI → GPIO 23 (default SPI)
// MISO → GPIO 19 (default SPI)

// ─────────────────────────────────────────────────────────────
// TIMING CONSTANTS
// ─────────────────────────────────────────────────────────────
#define HEARTBEAT_INTERVAL   30000   // Heartbeat every 30 seconds
#define CARD_COOLDOWN        3000    // 3 sec cooldown between same card taps
#define WIFI_RETRY_DELAY     5000    // 5 sec between WiFi reconnect attempts
#define HTTP_TIMEOUT         10000   // 10 sec HTTP request timeout

// ─────────────────────────────────────────────────────────────
// GLOBAL OBJECTS
// ─────────────────────────────────────────────────────────────
MFRC522 rfid(SS_PIN, RST_PIN);

unsigned long lastHeartbeat = 0;
unsigned long lastCardScan = 0;
String lastCardUID = "";
int heartbeatCount = 0;
int scanCount = 0;
int successCount = 0;
int failCount = 0;

// ═════════════════════════════════════════════════════════════
//  SETUP
// ═════════════════════════════════════════════════════════════
void setup() {
  Serial.begin(115200);
  delay(1000);  // Wait for Serial Monitor to connect

  Serial.println();
  Serial.println("═══════════════════════════════════════════════════════");
  Serial.println("  HRMS IoT Attendance System - Starting Up");
  Serial.println("═══════════════════════════════════════════════════════");
  Serial.println();

  // ─── Initialize SPI Bus ───
  Serial.println("[INIT] Initializing SPI bus...");
  SPI.begin();
  Serial.println("[INIT] SPI bus initialized (SCK=18, MOSI=23, MISO=19)");

  // ─── Initialize RFID Reader ───
  Serial.println("[INIT] Initializing MFRC522 RFID reader...");
  rfid.PCD_Init();
  delay(100);

  // Check if RFID reader is connected
  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  Serial.print("[INIT] MFRC522 firmware version: 0x");
  Serial.println(version, HEX);

  if (version == 0x00 || version == 0xFF) {
    Serial.println("[ERROR] *** MFRC522 NOT DETECTED! Check wiring: ***");
    Serial.println("[ERROR]   ESP32 D5  → RC522 SDA");
    Serial.println("[ERROR]   ESP32 D18 → RC522 SCK");
    Serial.println("[ERROR]   ESP32 D23 → RC522 MOSI");
    Serial.println("[ERROR]   ESP32 D19 → RC522 MISO");
    Serial.println("[ERROR]   ESP32 D15 → RC522 RST");
    Serial.println("[ERROR]   ESP32 3V3 → RC522 3.3V");
    Serial.println("[ERROR]   ESP32 GND → RC522 GND");
    Serial.println("[ERROR] Halting. Fix wiring and reset.");
    while (true) { delay(1000); }
  }

  Serial.println("[INIT] MFRC522 detected and ready ✓");
  rfid.PCD_DumpVersionToSerial();

  // ─── Connect to WiFi ───
  connectWiFi();

  // ─── Initial Heartbeat / Connectivity Test ───
  Serial.println();
  Serial.println("[INIT] Sending initial ping to server...");
  sendPing();

  Serial.println();
  Serial.println("[INIT] Sending initial heartbeat...");
  sendHeartbeat();

  Serial.println();
  Serial.println("═══════════════════════════════════════════════════════");
  Serial.println("  SYSTEM READY - Tap an RFID card to mark attendance");
  Serial.println("═══════════════════════════════════════════════════════");
  Serial.println();
}

// ═════════════════════════════════════════════════════════════
//  MAIN LOOP
// ═════════════════════════════════════════════════════════════
void loop() {
  // ─── Check WiFi Connection ───
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WIFI] Connection lost! Reconnecting...");
    connectWiFi();
  }

  // ─── Periodic Heartbeat ───
  if (millis() - lastHeartbeat >= HEARTBEAT_INTERVAL) {
    sendHeartbeat();
    lastHeartbeat = millis();
  }

  // ─── Check for RFID Card ───
  if (!rfid.PICC_IsNewCardPresent()) {
    delay(50);  // Small delay to avoid busy-waiting
    return;
  }

  if (!rfid.PICC_ReadCardSerial()) {
    Serial.println("[RFID] Card detected but failed to read serial. Try again.");
    return;
  }

  // ─── Card Detected! Read UID ───
  scanCount++;
  String cardUID = getCardUID();

  Serial.println();
  Serial.println("───────────────────────────────────────────────────────");
  Serial.print("[RFID] Card #");
  Serial.print(scanCount);
  Serial.print(" detected! UID: ");
  Serial.println(cardUID);
  Serial.print("[RFID] Card type: ");
  Serial.println(rfid.PICC_GetTypeName(rfid.PICC_GetType(rfid.uid.sak)));

  // ─── Cooldown Check (prevent double-tap) ───
  if (cardUID == lastCardUID && (millis() - lastCardScan) < CARD_COOLDOWN) {
    unsigned long remaining = CARD_COOLDOWN - (millis() - lastCardScan);
    Serial.print("[RFID] Same card tapped too quickly. Cooldown: ");
    Serial.print(remaining);
    Serial.println("ms remaining. Ignoring.");
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }

  lastCardUID = cardUID;
  lastCardScan = millis();

  // ─── Send Attendance Request ───
  Serial.println("[HTTP] Sending attendance request to server...");
  sendAttendance(cardUID);

  // ─── Halt card communication ───
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  Serial.println("───────────────────────────────────────────────────────");
  Serial.println();
}

// ═════════════════════════════════════════════════════════════
//  WiFi CONNECTION
// ═════════════════════════════════════════════════════════════
void connectWiFi() {
  Serial.println("[WIFI] ─── WiFi Connection ───");
  Serial.print("[WIFI] SSID: ");
  Serial.println(WIFI_SSID);
  Serial.print("[WIFI] Connecting");

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    attempts++;

    if (attempts >= 40) {  // 20 second timeout
      Serial.println();
      Serial.println("[WIFI] *** CONNECTION FAILED after 20 seconds ***");
      Serial.println("[WIFI] Check SSID and password. Retrying in 5 sec...");
      delay(WIFI_RETRY_DELAY);
      attempts = 0;
      WiFi.disconnect();
      WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
      Serial.print("[WIFI] Retrying");
    }
  }

  Serial.println();
  Serial.println("[WIFI] Connected successfully! ✓");
  Serial.print("[WIFI] IP Address: ");
  Serial.println(WiFi.localIP());
  Serial.print("[WIFI] Signal Strength (RSSI): ");
  Serial.print(WiFi.RSSI());
  Serial.println(" dBm");
  Serial.print("[WIFI] Gateway: ");
  Serial.println(WiFi.gatewayIP());
  Serial.print("[WIFI] DNS: ");
  Serial.println(WiFi.dnsIP());
  Serial.println();
}

// ═════════════════════════════════════════════════════════════
//  READ RFID CARD UID AS HEX STRING
// ═════════════════════════════════════════════════════════════
String getCardUID() {
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) {
      uid += "0";
    }
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.print("[RFID] Raw UID bytes (");
  Serial.print(rfid.uid.size);
  Serial.print(" bytes): ");
  for (byte i = 0; i < rfid.uid.size; i++) {
    Serial.print("0x");
    if (rfid.uid.uidByte[i] < 0x10) Serial.print("0");
    Serial.print(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) Serial.print(" ");
  }
  Serial.println();

  return uid;
}

// ═════════════════════════════════════════════════════════════
//  SEND ATTENDANCE (POST to /api/iot_attendance.php)
// ═════════════════════════════════════════════════════════════
void sendAttendance(String cardUID) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP] ERROR: WiFi not connected! Cannot send attendance.");
    failCount++;
    return;
  }

  HTTPClient http;
  String url = String(SERVER_BASE_URL) + "/iot_attendance.php";

  Serial.print("[HTTP] URL: ");
  Serial.println(url);
  Serial.print("[HTTP] Method: POST");
  Serial.println();

  http.begin(url);
  http.setTimeout(HTTP_TIMEOUT);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Authorization", String("Bearer ") + DEVICE_TOKEN);

  // Build JSON payload
  String jsonPayload = "{\"auth_type\":\"rfid\",\"identifier_value\":\"" + cardUID + "\"}";
  Serial.print("[HTTP] Payload: ");
  Serial.println(jsonPayload);

  Serial.println("[HTTP] Sending POST request...");
  unsigned long startTime = millis();
  int httpCode = http.POST(jsonPayload);
  unsigned long elapsed = millis() - startTime;

  Serial.print("[HTTP] Response time: ");
  Serial.print(elapsed);
  Serial.println("ms");
  Serial.print("[HTTP] HTTP Status Code: ");
  Serial.println(httpCode);

  if (httpCode > 0) {
    String response = http.getString();
    Serial.print("[HTTP] Response body: ");
    Serial.println(response);

    // Parse JSON response
    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, response);

    if (error) {
      Serial.print("[JSON] Parse error: ");
      Serial.println(error.c_str());
      failCount++;
    } else {
      bool success = doc["success"] | false;
      const char* action = doc["action"] | "unknown";
      const char* message = doc["message"] | "No message";

      Serial.println("[RESULT] ─── Server Response ───");
      Serial.print("[RESULT] Success: ");
      Serial.println(success ? "YES" : "NO");
      Serial.print("[RESULT] Action: ");
      Serial.println(action);
      Serial.print("[RESULT] Message: ");
      Serial.println(message);

      if (doc.containsKey("data")) {
        const char* empName = doc["data"]["employee_name"] | "N/A";
        const char* empCode = doc["data"]["employee_code"] | "N/A";
        const char* timestamp = doc["data"]["timestamp"] | "N/A";
        const char* device = doc["data"]["device"] | "N/A";
        const char* hoursWorked = doc["data"]["hours_worked"] | "N/A";

        Serial.print("[RESULT] Employee: ");
        Serial.println(empName);
        Serial.print("[RESULT] Code: ");
        Serial.println(empCode);
        Serial.print("[RESULT] Time: ");
        Serial.println(timestamp);
        Serial.print("[RESULT] Device: ");
        Serial.println(device);

        if (String(action) == "checked_out") {
          Serial.print("[RESULT] Hours Worked: ");
          Serial.println(hoursWorked);
        }
      }

      if (success) {
        successCount++;
        Serial.println("[RESULT] ✓ Attendance recorded successfully!");
      } else {
        failCount++;
        Serial.println("[RESULT] ✗ Attendance failed!");
      }
    }
  } else {
    Serial.print("[HTTP] ERROR: Request failed. Error code: ");
    Serial.println(httpCode);
    Serial.print("[HTTP] Error: ");
    Serial.println(http.errorToString(httpCode));
    Serial.println("[HTTP] Check: Is XAMPP Apache running? Is the IP correct?");
    failCount++;
  }

  http.end();
  printStats();
}

// ═════════════════════════════════════════════════════════════
//  SEND PING (GET to /api/iot_ping.php) - No Auth
// ═════════════════════════════════════════════════════════════
void sendPing() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[PING] ERROR: WiFi not connected!");
    return;
  }

  HTTPClient http;
  String url = String(SERVER_BASE_URL) + "/iot_ping.php";

  Serial.print("[PING] URL: ");
  Serial.println(url);

  http.begin(url);
  http.setTimeout(HTTP_TIMEOUT);

  Serial.println("[PING] Sending GET request...");
  unsigned long startTime = millis();
  int httpCode = http.GET();
  unsigned long elapsed = millis() - startTime;

  Serial.print("[PING] Response time: ");
  Serial.print(elapsed);
  Serial.println("ms");
  Serial.print("[PING] HTTP Status Code: ");
  Serial.println(httpCode);

  if (httpCode > 0) {
    Serial.print("[PING] Response: ");
    Serial.println(http.getString());
    Serial.println("[PING] ✓ Server is reachable!");
  } else {
    Serial.print("[PING] ERROR: ");
    Serial.println(http.errorToString(httpCode));
    Serial.println("[PING] ✗ Cannot reach server!");
    Serial.println("[PING] Troubleshoot:");
    Serial.println("[PING]   1. Is XAMPP Apache running?");
    Serial.println("[PING]   2. Is SERVER_BASE_URL correct?");
    Serial.print("[PING]   3. Can you open ");
    Serial.print(url);
    Serial.println(" in browser?");
    Serial.println("[PING]   4. Is Windows Firewall blocking port 80?");
  }

  http.end();
}

// ═════════════════════════════════════════════════════════════
//  SEND HEARTBEAT (GET to /api/iot_heartbeat.php)
// ═════════════════════════════════════════════════════════════
void sendHeartbeat() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HEARTBEAT] ERROR: WiFi not connected!");
    return;
  }

  heartbeatCount++;
  HTTPClient http;
  String url = String(SERVER_BASE_URL) + "/iot_heartbeat.php";

  Serial.print("[HEARTBEAT] #");
  Serial.print(heartbeatCount);
  Serial.print(" → ");
  Serial.println(url);

  http.begin(url);
  http.setTimeout(HTTP_TIMEOUT);
  http.addHeader("Authorization", String("Bearer ") + DEVICE_TOKEN);

  unsigned long startTime = millis();
  int httpCode = http.GET();
  unsigned long elapsed = millis() - startTime;

  Serial.print("[HEARTBEAT] Status: ");
  Serial.print(httpCode);
  Serial.print(" | Time: ");
  Serial.print(elapsed);
  Serial.println("ms");

  if (httpCode == 200) {
    String response = http.getString();

    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, response);
    if (!error) {
      const char* serverTime = doc["data"]["server_time"] | "N/A";
      const char* serverDate = doc["data"]["server_date"] | "N/A";
      const char* deviceName = doc["data"]["device_name"] | "N/A";
      Serial.print("[HEARTBEAT] Server time: ");
      Serial.print(serverDate);
      Serial.print(" ");
      Serial.println(serverTime);
      Serial.print("[HEARTBEAT] Device: ");
      Serial.println(deviceName);
    }
    Serial.println("[HEARTBEAT] ✓ Device online");
  } else if (httpCode == 401) {
    Serial.println("[HEARTBEAT] ✗ UNAUTHORIZED - Check DEVICE_TOKEN!");
  } else {
    Serial.print("[HEARTBEAT] ✗ Failed: ");
    Serial.println(http.errorToString(httpCode));
  }

  http.end();
}

// ═════════════════════════════════════════════════════════════
//  PRINT RUNNING STATS
// ═════════════════════════════════════════════════════════════
void printStats() {
  Serial.println("[STATS] ─── Session Stats ───");
  Serial.print("[STATS] Total Scans: ");
  Serial.print(scanCount);
  Serial.print(" | Success: ");
  Serial.print(successCount);
  Serial.print(" | Failed: ");
  Serial.print(failCount);
  Serial.print(" | Heartbeats: ");
  Serial.println(heartbeatCount);
  Serial.print("[STATS] WiFi RSSI: ");
  Serial.print(WiFi.RSSI());
  Serial.print(" dBm | Free heap: ");
  Serial.print(ESP.getFreeHeap());
  Serial.println(" bytes");
}