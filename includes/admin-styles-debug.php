<?php
if (!defined('ABSPATH')) {
    exit;
}

// Debug: log the hook value
error_log('Hook value: ' . ($GLOBALS['hook_suffix'] ?? 'NOT SET'));
error_log('Hook from param: ' . ($hook ?? 'NOT SET FROM PARAM'));

$allowed_pages = [
    'toplevel_page_wp-form-entries',
    'wp-form-entries_page_wp-form-analytics',
    'wp-form-entries_page_wp-form-settings',
    'wp-form-entries_page_wp-form-security'
];

error_log('Allowed pages: ' . implode(', ', $allowed_pages));
