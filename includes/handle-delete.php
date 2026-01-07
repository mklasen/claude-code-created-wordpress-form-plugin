<?php
/**
 * Handle delete entry action
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($_GET['action']) || $_GET['action'] !== 'delete') {
    return;
}

if (!isset($_GET['entry_id']) || !isset($_GET['_wpnonce'])) {
    return;
}

if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_entry_' . $_GET['entry_id'])) {
    wp_die('Security check failed');
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$table_name = $wpdb->prefix . 'form_entries';
$entry_id = intval($_GET['entry_id']);

$wpdb->delete($table_name, ['id' => $entry_id], ['%d']);

// Log admin action
$security = \WPFormPlugin\Plugin::getInstance()->getSecurityHelper();
$security->logEvent('entry_deleted', 'Admin deleted entry #' . $entry_id . ' from ' . wp_get_current_user()->user_login, 'info');

wp_redirect(admin_url('admin.php?page=wp-form-entries&deleted=1'));
exit;
