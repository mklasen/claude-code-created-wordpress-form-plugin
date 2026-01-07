<?php
/**
 * Handle status update action
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($_GET['action']) || !in_array($_GET['action'], ['mark_read', 'mark_unread'])) {
    return;
}

if (!isset($_GET['entry_id']) || !isset($_GET['_wpnonce'])) {
    return;
}

if (!wp_verify_nonce($_GET['_wpnonce'], 'status_entry_' . $_GET['entry_id'])) {
    wp_die('Security check failed');
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$table_name = $wpdb->prefix . 'form_entries';
$entry_id = intval($_GET['entry_id']);
$status = $_GET['action'] === 'mark_read' ? 'read' : 'unread';

$wpdb->update($table_name, ['status' => $status], ['id' => $entry_id], ['%s'], ['%d']);

// Log admin action
$security = \WPFormPlugin\Plugin::getInstance()->getSecurityHelper();
$security->logEvent('status_updated', 'Admin ' . wp_get_current_user()->user_login . ' marked entry #' . $entry_id . ' as ' . $status, 'info');

wp_redirect(admin_url('admin.php?page=wp-form-entries'));
exit;
