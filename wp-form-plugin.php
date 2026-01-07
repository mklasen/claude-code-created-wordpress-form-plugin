<?php
/**
 * Plugin Name: WP Form Plugin
 * Plugin URI: https://example.com/wp-form-plugin
 * Description: A custom WordPress form plugin with block editor support and enterprise security
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-form-plugin
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_FORM_PLUGIN_VERSION', '1.0.0');
define('WP_FORM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_FORM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_FORM_PLUGIN_FILE', __FILE__);

// Require Composer autoloader
if (file_exists(WP_FORM_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once WP_FORM_PLUGIN_PATH . 'vendor/autoload.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="error"><p><strong>WP Form Plugin:</strong> Please run <code>composer install</code> to install dependencies.</p></div>';
    });
    return;
}

// Initialize plugin
$wpFormPlugin = \WPFormPlugin\Plugin::getInstance();
$wpFormPlugin->init();
