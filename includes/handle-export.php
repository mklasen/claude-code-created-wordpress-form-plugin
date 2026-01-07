<?php
/**
 * Handle CSV export
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($_GET['action']) || $_GET['action'] !== 'export') {
    return;
}

if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'export_entries')) {
    wp_die('Security check failed');
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$table_name = $wpdb->prefix . 'form_entries';
$entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=form-entries-' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, ['ID', 'Name', 'Email', 'Message', 'Date']);

// Add data
foreach ($entries as $entry) {
    fputcsv($output, [
        $entry['id'],
        $entry['name'],
        $entry['email'],
        $entry['message'],
        $entry['created_at']
    ]);
}

fclose($output);
exit;
