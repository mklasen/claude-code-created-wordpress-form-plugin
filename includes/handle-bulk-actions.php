<?php
/**
 * Handle bulk actions for form entries
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($_POST['wp_form_bulk_action']) || !isset($_POST['entry_ids'])) {
    return;
}

check_admin_referer('wp_form_bulk_action_nonce');

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$table_name = $wpdb->prefix . 'form_entries';
$action = sanitize_text_field($_POST['wp_form_bulk_action']);
$entry_ids = array_map('intval', $_POST['entry_ids']);

if (empty($entry_ids)) {
    return;
}

$ids_placeholder = implode(',', array_fill(0, count($entry_ids), '%d'));
$count = count($entry_ids);
$user = wp_get_current_user()->user_login;
$security = \WPFormPlugin\Plugin::getInstance()->getSecurityHelper();

switch ($action) {
    case 'delete':
        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($ids_placeholder)", ...$entry_ids));
        $security->logEvent('bulk_delete', "Admin $user deleted $count entries", 'info');
        wp_redirect(admin_url('admin.php?page=wp-form-entries&bulk_deleted=' . $count));
        exit;
    case 'mark_read':
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 'read' WHERE id IN ($ids_placeholder)", ...$entry_ids));
        $security->logEvent('bulk_status_update', "Admin $user marked $count entries as read", 'info');
        wp_redirect(admin_url('admin.php?page=wp-form-entries&bulk_updated=' . $count));
        exit;
    case 'mark_unread':
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 'unread' WHERE id IN ($ids_placeholder)", ...$entry_ids));
        $security->logEvent('bulk_status_update', "Admin $user marked $count entries as unread", 'info');
        wp_redirect(admin_url('admin.php?page=wp-form-entries&bulk_updated=' . $count));
        exit;
}
