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
 *
 * ═══════════════════════════════════════════════════════════════
 *  SETUP GUIDE - Run these steps on a new PC before uploading
 * ═══════════════════════════════════════════════════════════════
 *
 *  Step 1: Find your PC's local IP address
 *    Open CMD/PowerShell and run:
 *      ipconfig
 *    Look for "Wireless LAN adapter Wi-Fi" → "IPv4 Address"
 *    (e.g., 192.168.1.3). Update SERVER_BASE_URL below.
 *
 *  Step 2: Make sure XAMPP Apache is running
 *    Open XAMPP Control Panel → Start Apache & MySQL
 *
 *  Step 3: Run the database migration (one-time)
 *    Open phpMyAdmin (http://localhost/phpmyadmin) or run in CMD:
 *      cd C:\xampp\mysql\bin
 *      mysql.exe -u root YOUR_DB_NAME < C:\xampp\htdocs\HRMS\database\iot_attendance_migration.sql
 *    This creates: employee_credentials, iot_devices tables + attendance columns
 *
 *  Step 4: Open Windows Firewall for port 80 (allow ESP32 to reach Apache)
 *    Open PowerShell as Administrator and run:
 *      netsh advfirewall firewall add rule name="XAMPP Apache HTTP" dir=in action=allow protocol=TCP localport=80
 *
 *  Step 5: Remove any existing Apache BLOCK rules (if connection still refused)
 *    In Admin PowerShell, check for blocking rules:
 *      netsh advfirewall firewall show rule name="Apache HTTP Server" verbose
 *    If any show Action: Block, delete them:
 *      netsh advfirewall firewall delete rule name="Apache HTTP Server" program="C:\xampp\apache\bin\httpd.exe"
 *    Then re-add a clean allow rule:
 *      netsh advfirewall firewall add rule name="Apache HTTP Server" dir=in action=allow program="C:\xampp\apache\bin\httpd.exe" protocol=TCP enable=yes profile=any
 *
 *  Step 6: Register RFID cards to employees
 *    Option A — Via the Web UI:
 *      Go to Employee Management → Actions (⋮) → "IoT Credentials"
 *      Enter the card UID (from Serial Monitor) and click Add
 *
 *    Option B — Via MySQL directly:
 *      cd C:\xampp\mysql\bin
 *      mysql.exe -u root YOUR_DB_NAME -e "INSERT INTO employee_credentials (employee_id, type, identifier_value) VALUES (1, 'rfid', '0A8F8005');"
 *      (Replace 1 with the employee ID and '0A8F8005' with the UID from Serial Monitor)
 *
 *  Step 7: Verify connectivity
 *    Open this URL in your browser (use YOUR IP):
 *      http://YOUR_PC_IP/HRMS/api/iot_ping.php
 *    Should return: {"success":true,"message":"Ping OK",...}
 *
 *  Step 8: Upload this sketch to ESP32
 *    - Install Arduino IDE + ESP32 board package
 *    - Install libraries: MFRC522 (by GithubCommunity), ArduinoJson (by Benoit Blanchon)
 *    - Select Board: "ESP32 Dev Module"
 *    - Update WIFI_SSID, WIFI_PASSWORD, SERVER_BASE_URL, DEVICE_TOKEN below
 *    - Upload and open Serial Monitor at 115200 baud
 *
 * ═══════════════════════════════════════════════════════════════
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// ─────────────────────────────────────────────────────────────
// CONFIGURATION - UPDATE THESE VALUES
// ─────────────────────────────────────────────────────────────
const char* WIFI_SSID     = "Airtel_Aditya_11111";       // ← Your WiFi name
const char* WIFI_PASSWORD = "Adiv2005@";    // ← Your WiFi password

// Server URL - Use your PC's local IP (run 'ipconfig' in cmd to find it)
// Example: "http://192.168.1.100/hrms/api"
const char* SERVER_BASE_URL = "http://192.168.1.3/HRMS/api";

// Device token from iot_devices table (run iot_test.php to generate one)
const char* DEVICE_TOKEN = "34481d80d8ccb98a0dadd2799cafcc21e3946a1fdcf90dad100907e0c339d958";

// ─────────────────────────────────────────────────────────────
// PIN DEFINITIONS (ESP32 → RC522)
// ─────────────────────────────────────────────────────────────
#define SS_PIN    5    // SDA/SS → GPIO 5
#define RST_PIN   15   // RST    → GPIO 15
// SCK  → GPIO 18 (default SPI)
// MOSI → GPIO 23 (default SPI)
// MISO → GPIO 19 (default SPI)

// ─────────────────────────────────────────────────────────────
// LED & BUTTON PIN DEFINITIONS
// ─────────────────────────────────────────────────────────────
#define LED_RED     2   // Red LED - Errors/Failed states
#define LED_GREEN   4   // Green LED - Success states
#define LED_BLUE    16  // Blue LED - Processing states
#define LED_YELLOW  17  // Yellow LED - Standby/Ready states
#define BUTTON_PIN    21  // Push button for adding new cards
#define SWITCH_PIN    22  // On/Off switch for reset/power control
#define RFID_IRQ_PIN  27  // MFRC522 IRQ → GPIO27 (RTC-capable) for card-tap wake from deep sleep

// ─────────────────────────────────────────────────────────────
// I2C LCD DISPLAY (16×2)
//   Wiring: LCD SDA → ESP32 GPIO33, LCD SCL → ESP32 GPIO32
//           LCD VCC → 5V, LCD GND → GND
// ─────────────────────────────────────────────────────────────
#define LCD_SDA   33
#define LCD_SCL   32
#define LCD_ADDR  0x27  // Common I2C address; try 0x3F if display stays blank

// ─────────────────────────────────────────────────────────────
// TIMING CONSTANTS
// ─────────────────────────────────────────────────────────────
#define HEARTBEAT_INTERVAL   30000   // Heartbeat every 30 seconds
#define CARD_COOLDOWN        3000    // 3 sec cooldown between same card taps
#define WIFI_RETRY_DELAY     5000    // 5 sec between WiFi reconnect attempts
#define HTTP_TIMEOUT         10000   // 10 sec HTTP request timeout
#define BUTTON_DEBOUNCE      200     // Button debounce time in ms
#define LED_BLINK_INTERVAL   500     // LED blink interval for processing states
#define ADD_CARD_TIMEOUT     60000   // 60 sec timeout for add card mode (matches server)

// ─────────────────────────────────────────────────────────────
// SYSTEM MODES
// ─────────────────────────────────────────────────────────────
enum SystemMode {
  MODE_NORMAL,      // Normal attendance operation
  MODE_ADD_CARD     // Adding new RFID card mode
};

// ─────────────────────────────────────────────────────────────
// LED STATES
// ─────────────────────────────────────────────────────────────
enum LedState {
  LED_OFF,
  LED_ON,
  LED_BLINK_SLOW,
  LED_BLINK_FAST
};

// ─────────────────────────────────────────────────────────────
// GLOBAL OBJECTS
// ─────────────────────────────────────────────────────────────
MFRC522 rfid(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDR, 16, 2);

unsigned long lastHeartbeat = 0;
unsigned long lastCardScan = 0;
String lastCardUID = "";
int heartbeatCount = 0;
int scanCount = 0;
int successCount = 0;
int failCount = 0;

// ─────────────────────────────────────────────────────────────
// BUTTON & LED CONTROL VARIABLES
// ─────────────────────────────────────────────────────────────
SystemMode currentMode = MODE_NORMAL;
unsigned long lastButtonPress = 0;
unsigned long addCardModeStart = 0;
unsigned long bootTime = 0;
bool buttonPressed = false;
bool lastButtonState = HIGH;
unsigned long lastLedBlink = 0;
bool ledBlinkState = false;

// LED states
LedState redLedState = LED_OFF;
LedState greenLedState = LED_OFF;
LedState blueLedState = LED_OFF;
LedState yellowLedState = LED_OFF;

// ═════════════════════════════════════════════════════════════
//  LED CONTROL FUNCTIONS
// ═════════════════════════════════════════════════════════════
void setLedState(int ledPin, LedState state) {
  switch (ledPin) {
    case LED_RED: redLedState = state; break;
    case LED_GREEN: greenLedState = state; break;
    case LED_BLUE: blueLedState = state; break;
    case LED_YELLOW: yellowLedState = state; break;
  }
}

void updateLeds() {
  unsigned long currentTime = millis();
  
  if (currentTime - lastLedBlink >= LED_BLINK_INTERVAL) {
    ledBlinkState = !ledBlinkState;
    lastLedBlink = currentTime;
  }
  
  // Update each LED based on its state
  updateSingleLed(LED_RED, redLedState);
  updateSingleLed(LED_GREEN, greenLedState);
  updateSingleLed(LED_BLUE, blueLedState);
  updateSingleLed(LED_YELLOW, yellowLedState);
}

void updateSingleLed(int ledPin, LedState state) {
  switch (state) {
    case LED_OFF:
      digitalWrite(ledPin, LOW);
      break;
    case LED_ON:
      digitalWrite(ledPin, HIGH);
      break;
    case LED_BLINK_SLOW:
      digitalWrite(ledPin, ledBlinkState);
      break;
    case LED_BLINK_FAST:
      if ((millis() / 200) % 2 == 0) {
        digitalWrite(ledPin, HIGH);
      } else {
        digitalWrite(ledPin, LOW);
      }
      break;
  }
}

// ═════════════════════════════════════════════════════════════
//  LCD HELPER
// ═════════════════════════════════════════════════════════════
void lcdPrint(String line1, String line2 = "") {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(line1.substring(0, 16));
  lcd.setCursor(0, 1);
  lcd.print(line2.substring(0, 16));
}

void setStatusLeds(String status) {
  // Turn off all LEDs first
  setLedState(LED_RED, LED_OFF);
  setLedState(LED_GREEN, LED_OFF);
  setLedState(LED_BLUE, LED_OFF);
  setLedState(LED_YELLOW, LED_OFF);
  
  if (status == "WIFI_CONNECTING") {
    setLedState(LED_BLUE, LED_BLINK_SLOW);
  } else if (status == "WIFI_CONNECTED") {
    setLedState(LED_GREEN, LED_ON);
    setLedState(LED_YELLOW, LED_ON);
  } else if (status == "WIFI_ERROR") {
    setLedState(LED_RED, LED_BLINK_FAST);
  } else if (status == "RFID_ERROR") {
    setLedState(LED_RED, LED_ON);
  } else if (status == "PROCESSING") {
    setLedState(LED_BLUE, LED_BLINK_FAST);
  } else if (status == "SUCCESS") {
    // Solid green for 2 seconds — attendance recorded
    digitalWrite(LED_GREEN, HIGH);
    updateLeds();
    delay(2000);
    digitalWrite(LED_GREEN, LOW);
    setLedState(LED_YELLOW, LED_ON); // Back to ready
  } else if (status == "FAILED") {
    // Solid red for 2 seconds — attendance failed
    digitalWrite(LED_RED, HIGH);
    updateLeds();
    delay(2000);
    digitalWrite(LED_RED, LOW);
    setLedState(LED_YELLOW, LED_ON); // Back to ready
  } else if (status == "READY") {
    setLedState(LED_YELLOW, LED_ON);
  } else if (status == "ADD_CARD_MODE") {
    setLedState(LED_BLUE, LED_BLINK_SLOW);
    setLedState(LED_YELLOW, LED_BLINK_SLOW);
  } else if (status == "HEARTBEAT") {
    // Brief green flash for heartbeat
    setLedState(LED_GREEN, LED_ON);
    delay(100);
    setLedState(LED_GREEN, LED_OFF);
    setLedState(LED_YELLOW, LED_ON); // Back to ready
  }
}

// ═════════════════════════════════════════════════════════════
//  BUTTON HANDLING FUNCTIONS
// ═════════════════════════════════════════════════════════════
void checkButton() {
  // Ignore button for 2 seconds after boot to avoid noise-triggered presses
  if (millis() - bootTime < 2000) return;

  bool currentButtonState = digitalRead(BUTTON_PIN);
  
  if (currentButtonState != lastButtonState) {
    if (millis() - lastButtonPress > BUTTON_DEBOUNCE) {
      if (currentButtonState == LOW) { // Button pressed (assuming pull-up)
        handleButtonPress();
        lastButtonPress = millis();
      }
    }
    lastButtonState = currentButtonState;
  }
}

void handleButtonPress() {
  if (currentMode == MODE_NORMAL) {
    // Enter add card mode
    currentMode = MODE_ADD_CARD;
    addCardModeStart = millis();
    Serial.println();
    Serial.println("═══════════════════════════════════════════════════════");
    Serial.println("  ADD NEW RFID CARD MODE ACTIVATED");
    Serial.println("  Tap a new RFID card to register it for assignment");
    Serial.println("  Mode will timeout in 30 seconds");
    Serial.println("═══════════════════════════════════════════════════════");
    setStatusLeds("ADD_CARD_MODE");
    lcdPrint("Add Card Mode", "Tap new card");
  } else if (currentMode == MODE_ADD_CARD) {
    // Exit add card mode
    currentMode = MODE_NORMAL;
    Serial.println("[MODE] Exited add card mode - returning to normal operation");
    setStatusLeds("READY");
    lcdPrint("System Ready", "Tap RFID Card");
  }
}

void checkAddCardTimeout() {
  if (currentMode == MODE_ADD_CARD) {
    if (millis() - addCardModeStart > ADD_CARD_TIMEOUT) {
      currentMode = MODE_NORMAL;
      Serial.println("[MODE] Add card mode timeout - returning to normal operation");
      setStatusLeds("READY");
      lcdPrint("System Ready", "Tap RFID Card");
    }
  }
}

// ═════════════════════════════════════════════════════════════
//  POWER SWITCH HANDLING
// ═════════════════════════════════════════════════════════════
void checkPowerSwitch() {
  // Deep sleep removed — switch pin is no longer used.
}

// ═════════════════════════════════════════════════════════════
//  SETUP
// ═════════════════════════════════════════════════════════════
void setup() {
  Serial.begin(115200);
  delay(500);

  Serial.println();
  Serial.println("═══════════════════════════════════════════════════════");
  Serial.println("  HRMS IoT Attendance System - Starting Up");
  Serial.println("═══════════════════════════════════════════════════════");
  Serial.println();

  // ─── Initialize GPIO Pins ───
  Serial.println("[INIT] Initializing GPIO pins...");

  // LED pins as outputs
  pinMode(LED_RED, OUTPUT);
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_BLUE, OUTPUT);
  pinMode(LED_YELLOW, OUTPUT);

  // Button pin as input with pull-up
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(SWITCH_PIN, INPUT_PULLUP);

  // ─── Boot sequence: light ALL LEDs for 1 second ───
  digitalWrite(LED_RED, HIGH);
  digitalWrite(LED_GREEN, HIGH);
  digitalWrite(LED_BLUE, HIGH);
  digitalWrite(LED_YELLOW, HIGH);
  Serial.println("[INIT] Boot indicator — all LEDs ON");
  delay(1000);
  digitalWrite(LED_RED, LOW);
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_BLUE, LOW);
  digitalWrite(LED_YELLOW, LOW);

  // ─── Initialize I2C LCD ───
  Wire.begin(LCD_SDA, LCD_SCL);
  lcd.init();
  lcd.backlight();
  lcdPrint("HRMS Attendance", "Booting...");
  Serial.println("[INIT] I2C LCD initialized (SDA=33, SCL=32) ✓");

  Serial.println("[INIT] GPIO pins initialized ✓");
  Serial.println("[INIT]   LEDs: Red=2, Green=4, Blue=16, Yellow=17");
  Serial.println("[INIT]   Button: 21 (pull-up), Switch: 22 (pull-up)");

  // ─── Initialize SPI Bus ───
  Serial.println("[INIT] Initializing SPI bus...");
  setStatusLeds("PROCESSING");
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
    lcdPrint("RFID ERROR!", "Check wiring");
    setStatusLeds("RFID_ERROR");
    while (true) { 
      updateLeds();
      delay(100); 
    }
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
  Serial.println("  SYSTEM READY - Tap RFID card or press button");
  Serial.println("  🟡 Yellow LED = Ready/Standby");
  Serial.println("  🔵 Blue LED = Processing/WiFi connecting");  
  Serial.println("  🟢 Green LED = Success/Connected");
  Serial.println("  🔴 Red LED = Error/Failed");
  Serial.println("  📋 BUTTON = Press to enter Add Card mode");
  Serial.println("═══════════════════════════════════════════════════════");
  Serial.println();
  
  // Set system to ready state
  setStatusLeds("READY");
  lcdPrint("System Ready", "Tap RFID Card");

  // Read actual button state now so the first loop() doesn't see a fake transition
  lastButtonState = digitalRead(BUTTON_PIN);
  bootTime = millis();
}

// ═════════════════════════════════════════════════════════════
//  MAIN LOOP
// ═════════════════════════════════════════════════════════════
void loop() {
  // ─── Update LEDs ───
  updateLeds();
  
  // ─── Check Power Switch ───
  checkPowerSwitch();
  
  // ─── Check Button ───
  checkButton();
  
  // ─── Check Add Card Mode Timeout ───
  checkAddCardTimeout();
  
  // ─── Check WiFi Connection ───
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WIFI] Connection lost! Reconnecting...");
    setStatusLeds("WIFI_CONNECTING");
    connectWiFi();
  }

  // ─── Periodic Heartbeat (faster polling when idle to catch server scan requests) ───
  unsigned long heartbeatInterval = (currentMode == MODE_NORMAL) ? HEARTBEAT_INTERVAL : 5000;
  if (millis() - lastHeartbeat >= heartbeatInterval) {
    if (currentMode == MODE_NORMAL) {
      setStatusLeds("HEARTBEAT");
    }
    sendHeartbeat();
    lastHeartbeat = millis();
    if (currentMode == MODE_NORMAL) {
      setStatusLeds("READY");
    }
  }

  // ─── Check for RFID Card ───
  if (!rfid.PICC_IsNewCardPresent()) {
    delay(50);  // Small delay to avoid busy-waiting
    return;
  }

  if (!rfid.PICC_ReadCardSerial()) {
    Serial.println("[RFID] Card detected but failed to read serial. Try again.");
    setStatusLeds("FAILED");
    delay(500);
    setStatusLeds("READY");
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

  lcdPrint("Card Scanned", cardUID);

  // ─── Handle Different Modes ───
  if (currentMode == MODE_ADD_CARD) {
    handleNewCardRegistration(cardUID);
  } else {
    // Normal attendance mode
    
    // ─── Cooldown Check (prevent double-tap) ───
    if (cardUID == lastCardUID && (millis() - lastCardScan) < CARD_COOLDOWN) {
      unsigned long remaining = CARD_COOLDOWN - (millis() - lastCardScan);
      Serial.print("[RFID] Same card tapped too quickly. Cooldown: ");
      Serial.print(remaining);
      Serial.println("ms remaining. Ignoring.");
      setStatusLeds("FAILED");
      delay(500);
      setStatusLeds("READY");
      rfid.PICC_HaltA();
      rfid.PCD_StopCrypto1();
      return;
    }

    lastCardUID = cardUID;
    lastCardScan = millis();

    // ─── Send Attendance Request ───
    Serial.println("[HTTP] Sending attendance request to server...");
    setStatusLeds("PROCESSING");
    sendAttendance(cardUID);
  }

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

  setStatusLeds("WIFI_CONNECTING");
  lcdPrint("Connecting WiFi", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    attempts++;
    updateLeds(); // Keep LEDs blinking during connection

    if (attempts >= 40) {  // 20 second timeout
      Serial.println();
      Serial.println("[WIFI] *** CONNECTION FAILED after 20 seconds ***");
      Serial.println("[WIFI] Check SSID and password. Retrying in 5 sec...");
      setStatusLeds("WIFI_ERROR");
      delay(WIFI_RETRY_DELAY);
      attempts = 0;
      WiFi.disconnect();
      WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
      Serial.print("[WIFI] Retrying");
      setStatusLeds("WIFI_CONNECTING");
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
  
  setStatusLeds("WIFI_CONNECTED");
  lcdPrint("WiFi Connected!", WiFi.localIP().toString());
  delay(1000); // Show connected status
  setStatusLeds("READY");
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
    setStatusLeds("WIFI_ERROR");
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
      setStatusLeds("FAILED");
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
        const char* eName = doc["data"]["employee_name"] | "";
        if (String(action) == "checked_in") {
          lcdPrint("Welcome!", eName);
        } else {
          lcdPrint("Goodbye!", eName);
        }
        Serial.println("[RESULT] ✓ Attendance recorded successfully!");
        setStatusLeds("SUCCESS");
      } else {
        failCount++;
        lcdPrint("Denied!", message);
        Serial.println("[RESULT] ✗ Attendance failed!");
        setStatusLeds("FAILED");
      }
    }
  } else {
    Serial.print("[HTTP] ERROR: Request failed. Error code: ");
    Serial.println(httpCode);
    Serial.print("[HTTP] Error: ");
    Serial.println(http.errorToString(httpCode));
    Serial.println("[HTTP] Check: Is XAMPP Apache running? Is the IP correct?");
    lcdPrint("Server Error!", "Check connection");
    setStatusLeds("FAILED");
    failCount++;
  }

  http.end();
  printStats();
  lcdPrint("System Ready", "Tap RFID Card");
}

// ═════════════════════════════════════════════════════════════
//  NEW CARD REGISTRATION (for Add Card Mode)
// ═════════════════════════════════════════════════════════════
void handleNewCardRegistration(String cardUID) {
  Serial.println();
  Serial.println("[NEW CARD] ═══ NEW RFID CARD REGISTRATION ═══");
  Serial.print("[NEW CARD] Card UID: ");
  Serial.println(cardUID);
  Serial.println("[NEW CARD] Sending to server for registration...");

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[NEW CARD] ERROR: WiFi not connected!");
    setStatusLeds("WIFI_ERROR");
    return;
  }

  HTTPClient http;
  String url = String(SERVER_BASE_URL) + "/iot_register_card.php";

  Serial.print("[NEW CARD] URL: ");
  Serial.println(url);

  http.begin(url);
  http.setTimeout(HTTP_TIMEOUT);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Authorization", String("Bearer ") + DEVICE_TOKEN);

  // Build JSON payload for new card registration
  String jsonPayload = "{\"card_uid\":\"" + cardUID + "\"}";
  Serial.print("[NEW CARD] Payload: ");
  Serial.println(jsonPayload);

  Serial.println("[NEW CARD] Sending POST request...");
  lcdPrint("Registering...", cardUID);
  setStatusLeds("PROCESSING");
  
  unsigned long startTime = millis();
  int httpCode = http.POST(jsonPayload);
  unsigned long elapsed = millis() - startTime;

  Serial.print("[NEW CARD] Response time: ");
  Serial.print(elapsed);
  Serial.println("ms");
  Serial.print("[NEW CARD] HTTP Status Code: ");
  Serial.println(httpCode);

  if (httpCode > 0) {
    String response = http.getString();
    Serial.print("[NEW CARD] Response: ");
    Serial.println(response);

    // Parse JSON response
    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, response);

    if (!error) {
      bool success = doc["success"] | false;
      const char* message = doc["message"] | "No message";

      Serial.println("[NEW CARD] ─── Registration Result ───");
      Serial.print("[NEW CARD] Success: ");
      Serial.println(success ? "YES" : "NO");
      Serial.print("[NEW CARD] Message: ");
      Serial.println(message);

      if (success) {
        Serial.println("[NEW CARD] ✓ Card registered successfully!");
        Serial.println("[NEW CARD] HR can now assign this card to an employee");
        Serial.println("[NEW CARD] via the Employee Management page");
        lcdPrint("Card Registered!", "Assign in HRMS");
        setStatusLeds("SUCCESS");
        
        // Exit add card mode after successful registration
        currentMode = MODE_NORMAL;
        delay(2000); // Show success for 2 seconds
        setStatusLeds("READY");
        lcdPrint("System Ready", "Tap RFID Card");
      } else {
        Serial.println("[NEW CARD] ✗ Card registration failed!");
        Serial.println("[NEW CARD] Card may already be registered");
        lcdPrint("Reg Failed!", "Already exists?");
        setStatusLeds("FAILED");
        delay(2000); // Show fail for 2 seconds
        setStatusLeds("ADD_CARD_MODE"); // Stay in add card mode
        lcdPrint("Add Card Mode", "Tap new card");
      }
    } else {
      Serial.print("[NEW CARD] JSON Parse error: ");
      Serial.println(error.c_str());
      setStatusLeds("FAILED");
    }
  } else {
    Serial.print("[NEW CARD] ERROR: Request failed. Error code: ");
    Serial.println(httpCode);
    Serial.print("[NEW CARD] Error: ");
    Serial.println(http.errorToString(httpCode));
    setStatusLeds("FAILED");
  }

  http.end();
  Serial.println("[NEW CARD] ═══════════════════════════════════");
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

    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, response);
    if (!error) {
      const char* serverTime = doc["data"]["server_time"] | "N/A";
      const char* serverDate = doc["data"]["server_date"] | "N/A";
      const char* deviceName = doc["data"]["device_name"] | "N/A";
      int addCardMode = doc["data"]["add_card_mode"] | 0;
      
      Serial.print("[HEARTBEAT] Server time: ");
      Serial.print(serverDate);
      Serial.print(" ");
      Serial.println(serverTime);
      Serial.print("[HEARTBEAT] Device: ");
      Serial.println(deviceName);
      
      // Check if server wants us to enter add card mode
      if (addCardMode == 1 && currentMode != MODE_ADD_CARD) {
        currentMode = MODE_ADD_CARD;
        addCardModeStart = millis();
        Serial.println();
        Serial.println("═══════════════════════════════════════════════════════");
        Serial.println("  SERVER REQUESTED: ADD CARD MODE ACTIVATED");
        Serial.println("  HR is waiting - tap an RFID card to register it");
        Serial.println("  Mode will timeout in 60 seconds");
        Serial.println("═══════════════════════════════════════════════════════");
        setStatusLeds("ADD_CARD_MODE");
      }
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