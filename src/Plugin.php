<?php

namespace WPFormPlugin;

use WPFormPlugin\Security\SecurityHelper;
use WPFormPlugin\Api\FormSubmissionHandler;

class Plugin
{
    private static ?Plugin $instance = null;
    private SecurityHelper $security;
    private FormSubmissionHandler $formHandler;

    private function __construct()
    {
        $this->security = new SecurityHelper();
        $this->formHandler = new FormSubmissionHandler($this->security);
    }

    public static function getInstance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        // Register activation hook
        register_activation_hook(WP_FORM_PLUGIN_FILE, [$this, 'activate']);

        // Initialize plugin
        add_action('init', [$this, 'registerBlock']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_head', [$this, 'adminStyles']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_init', [$this, 'handleBulkActions']);
        add_action('admin_init', [$this, 'handleDelete']);
        add_action('admin_init', [$this, 'handleStatusUpdate']);
        add_action('admin_init', [$this, 'handleExport']);
        add_action('rest_api_init', [$this, 'registerRestRoute']);
    }

    public function activate(): void
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Form entries table
        $table_name = $wpdb->prefix . 'form_entries';
        $sql1 = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            message text NOT NULL,
            status varchar(20) DEFAULT 'unread' NOT NULL,
            admin_note text,
            ip_address varchar(45),
            user_agent varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY ip_address (ip_address),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Security log table
        $log_table = $wpdb->prefix . 'form_security_log';
        $sql2 = "CREATE TABLE $log_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent varchar(255),
            details text,
            severity varchar(20) DEFAULT 'info' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY ip_address (ip_address),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Blocked IPs table
        $blocked_table = $wpdb->prefix . 'form_blocked_ips';
        $sql3 = "CREATE TABLE $blocked_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            reason text,
            blocked_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            expires_at datetime,
            PRIMARY KEY  (id),
            UNIQUE KEY ip_address (ip_address)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);
    }

    public function registerBlock(): void
    {
        register_block_type(WP_FORM_PLUGIN_PATH . 'build');
    }

    public function enqueueScripts(): void
    {
        if (has_block('wp-form-plugin/contact-form')) {
            wp_localize_script('wp-form-plugin-contact-form-view-script', 'wpFormPlugin', [
                'restUrl' => rest_url('wp-form-plugin/v1/submit'),
                'nonce' => wp_create_nonce('wp_rest')
            ]);
        }
    }

    public function registerRestRoute(): void
    {
        register_rest_route('wp-form-plugin/v1', '/submit', [
            'methods' => 'POST',
            'callback' => [$this->formHandler, 'handleSubmission'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function registerAdminMenu(): void
    {
        add_menu_page(
            'Form Plugin',
            'Form Plugin',
            'manage_options',
            'wp-form-entries',
            [$this, 'entriesPage'],
            'dashicons-feedback',
            30
        );

        add_submenu_page(
            'wp-form-entries',
            'All Entries',
            'All Entries',
            'manage_options',
            'wp-form-entries',
            [$this, 'entriesPage']
        );

        add_submenu_page(
            'wp-form-entries',
            'Analytics',
            'Analytics',
            'manage_options',
            'wp-form-analytics',
            [$this, 'analyticsPage']
        );

        add_submenu_page(
            'wp-form-entries',
            'Settings',
            'Settings',
            'manage_options',
            'wp-form-settings',
            [$this, 'settingsPage']
        );

        add_submenu_page(
            'wp-form-entries',
            'Security',
            'Security',
            'manage_options',
            'wp-form-security',
            [$this, 'securityPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_enable_notifications', ['default' => '1']);
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_enable_auto_reply', ['default' => '1']);
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_notification_email');
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_success_message', ['default' => 'Thank you! Your message has been sent successfully.']);
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_auto_reply_subject', ['default' => 'Thank you for contacting us!']);
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_auto_reply_message', ['default' => "Hi {name},\n\nThank you for reaching out! We've received your message and will get back to you as soon as possible.\n\nBest regards"]);
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_rate_limit', ['default' => '5']);
        register_setting('wp_form_plugin_settings', 'wp_form_plugin_enable_security_logging', ['default' => '1']);
    }

    // Include admin page methods
    public function entriesPage(): void
    {
        include WP_FORM_PLUGIN_PATH . 'includes/admin-entries.php';
    }

    public function analyticsPage(): void
    {
        include WP_FORM_PLUGIN_PATH . 'includes/admin-analytics.php';
    }

    public function settingsPage(): void
    {
        include WP_FORM_PLUGIN_PATH . 'includes/admin-settings.php';
    }

    public function securityPage(): void
    {
        include WP_FORM_PLUGIN_PATH . 'includes/admin-security.php';
    }

    public function adminStyles(): void
    {
        $hook = $GLOBALS['hook_suffix'] ?? '';
        include WP_FORM_PLUGIN_PATH . 'includes/admin-styles.php';
    }

    public function handleBulkActions(): void
    {
        include_once WP_FORM_PLUGIN_PATH . 'includes/handle-bulk-actions.php';
    }

    public function handleDelete(): void
    {
        include_once WP_FORM_PLUGIN_PATH . 'includes/handle-delete.php';
    }

    public function handleStatusUpdate(): void
    {
        include_once WP_FORM_PLUGIN_PATH . 'includes/handle-status-update.php';
    }

    public function handleExport(): void
    {
        include_once WP_FORM_PLUGIN_PATH . 'includes/handle-export.php';
    }

    public function getSecurityHelper(): SecurityHelper
    {
        return $this->security;
    }
}
