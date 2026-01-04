<?php
/**
 * User Preferences Configuration
 * 
 * This file defines all available user preferences and their default values.
 * When you need to add or modify preferences, just update this file.
 * No need to change multiple files anymore!
 * 
 * Adding a new preference:
 * 1. Add entry to $PREFERENCES array below
 * 2. Add corresponding HTML element in account.php (if UI needed)
 * 3. Done! Everything else is automatic.
 */

/**
 * All available user preferences with their default values
 * 
 * Format:
 * 'preference_key' => [
 *     'default' => 'default_value',
 *     'type' => 'boolean|string|select',
 *     'label' => 'Display label'
 * ]
 */
$PREFERENCES = [
    // Notification Preferences - Notification Types
    'notif_leave_status' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'Leave Request Status Updates',
        'description' => 'Receive notifications when your leave requests are approved or rejected'
    ],
    'notif_attendance' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'Attendance Alerts',
        'description' => 'Get notified for late check-ins or missing punch records'
    ],
    'notif_payslip' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'Payslip Availability',
        'description' => 'Receive notifications when your payslip is ready for download'
    ],
    'notif_announcements' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'Company Announcements',
        'description' => 'Be notified of important company-wide announcements and updates'
    ],
    'notif_system' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'System Alerts',
        'description' => 'Important system messages, maintenance notices, and security alerts'
    ],

    // Privacy Settings
    'privacy_profile_visible' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'Show Profile to Colleagues',
        'description' => 'Display your profile and work information to colleagues within the organization'
    ],
    'privacy_phone_visible' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'Show Phone Number Internally',
        'description' => 'Display your phone number to colleagues within the organization'
    ],
    'privacy_email_visible' => [
        'default' => '1',
        'type' => 'boolean',
        'label' => 'Show Email Address Internally',
        'description' => 'Display your email on your internal profile'
    ],

    // Deactivation Settings
    'deactivation_requested' => [
        'default' => '0',
        'type' => 'boolean',
        'label' => 'Account Deactivation Status',
        'description' => 'Current deactivation status of your account'
    ],
    'deactivation_reason' => [
        'default' => '',
        'type' => 'string',
        'label' => 'Deactivation Reason',
        'description' => 'The reason provided for account deactivation request'
    ],
    'deactivation_comments' => [
        'default' => '',
        'type' => 'string',
        'label' => 'Deactivation Comments',
        'description' => 'Additional comments provided with deactivation request'
    ],
];

/**
 * Get default preferences array
 * Useful for initialization of new users
 */
function getDefaultPreferences()
{
    global $PREFERENCES;
    $defaults = [];
    foreach ($PREFERENCES as $key => $config) {
        $defaults[$key] = $config['default'];
    }
    return $defaults;
}

/**
 * Validate preference key and value
 */
function isValidPreferenceKey($key)
{
    global $PREFERENCES;
    return isset($PREFERENCES[$key]);
}

/**
 * Get preference configuration
 */
function getPreferenceConfig($key)
{
    global $PREFERENCES;
    return $PREFERENCES[$key] ?? null;
}

/**
 * Get all preference keys
 */
function getPreferenceKeys()
{
    global $PREFERENCES;
    return array_keys($PREFERENCES);
}

/**
 * Get all preferences with their config
 */
function getAllPreferencesConfig()
{
    global $PREFERENCES;
    return $PREFERENCES;
}
