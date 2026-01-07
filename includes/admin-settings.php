<?php
/**
 * Settings page
 */

if (!defined('ABSPATH')) {
    exit;
}

if (isset($_POST['wp_form_plugin_settings_submit'])) {
    check_admin_referer('wp_form_plugin_settings_nonce');
    update_option('wp_form_plugin_enable_notifications', isset($_POST['wp_form_plugin_enable_notifications']) ? '1' : '0');
    update_option('wp_form_plugin_enable_auto_reply', isset($_POST['wp_form_plugin_enable_auto_reply']) ? '1' : '0');
    update_option('wp_form_plugin_notification_email', sanitize_email($_POST['wp_form_plugin_notification_email']));
    update_option('wp_form_plugin_success_message', sanitize_text_field($_POST['wp_form_plugin_success_message']));
    update_option('wp_form_plugin_auto_reply_subject', sanitize_text_field($_POST['wp_form_plugin_auto_reply_subject']));
    update_option('wp_form_plugin_auto_reply_message', sanitize_textarea_field($_POST['wp_form_plugin_auto_reply_message']));
    update_option('wp_form_plugin_rate_limit', intval($_POST['wp_form_plugin_rate_limit']));
    update_option('wp_form_plugin_enable_security_logging', isset($_POST['wp_form_plugin_enable_security_logging']) ? '1' : '0');
    echo '<div class="notice notice-success"><p><strong>Settings saved successfully!</strong></p></div>';
}

$enable_notifications = get_option('wp_form_plugin_enable_notifications', '1');
$enable_auto_reply = get_option('wp_form_plugin_enable_auto_reply', '1');
$notification_email = get_option('wp_form_plugin_notification_email', get_option('admin_email'));
$success_message = get_option('wp_form_plugin_success_message', 'Thank you! Your message has been sent successfully.');
$auto_reply_subject = get_option('wp_form_plugin_auto_reply_subject', 'Thank you for contacting us!');
$auto_reply_message = get_option('wp_form_plugin_auto_reply_message', "Hi {name},\n\nThank you for reaching out! We've received your message and will get back to you as soon as possible.\n\nBest regards");
$rate_limit = get_option('wp_form_plugin_rate_limit', '5');
$enable_security_logging = get_option('wp_form_plugin_enable_security_logging', '1');
?>
<div class="wrap">
    <div class="wp-form-plugin-admin-header">
        <h1>⚙️ Settings</h1>
        <p>Configure your contact form behavior</p>
    </div>

    <form method="post" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); max-width: 800px;">
        <?php wp_nonce_field('wp_form_plugin_settings_nonce'); ?>

        <h2 style="margin-top: 0; color: #2d3748;">Email Notifications</h2>

        <p style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="wp_form_plugin_enable_notifications" value="1" <?php checked($enable_notifications, '1'); ?>>
                <strong>Send admin notification on new submissions</strong>
            </label>
        </p>

        <p>
            <label><strong>Notification Email Address</strong></label><br>
            <input type="email" name="wp_form_plugin_notification_email" value="<?php echo esc_attr($notification_email); ?>" class="regular-text" style="margin-top: 5px;">
            <p class="description">Leave empty to use the WordPress admin email</p>
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e2e8f0;">

        <h2 style="color: #2d3748;">Auto-Reply to Submitters</h2>

        <p style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="wp_form_plugin_enable_auto_reply" value="1" <?php checked($enable_auto_reply, '1'); ?>>
                <strong>Send confirmation email to form submitters</strong>
            </label>
        </p>

        <p>
            <label><strong>Auto-Reply Subject</strong></label><br>
            <input type="text" name="wp_form_plugin_auto_reply_subject" value="<?php echo esc_attr($auto_reply_subject); ?>" class="large-text" style="margin-top: 5px;">
        </p>

        <p>
            <label><strong>Auto-Reply Message</strong></label><br>
            <textarea name="wp_form_plugin_auto_reply_message" rows="6" class="large-text" style="margin-top: 5px; font-family: monospace;"><?php echo esc_textarea($auto_reply_message); ?></textarea>
            <p class="description">Use {name}, {email}, and {message} as placeholders</p>
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e2e8f0;">

        <h2 style="color: #2d3748;">Success Message</h2>

        <p>
            <label><strong>Form Success Message</strong></label><br>
            <input type="text" name="wp_form_plugin_success_message" value="<?php echo esc_attr($success_message); ?>" class="large-text" style="margin-top: 5px;">
            <p class="description">Message shown to users after successful form submission</p>
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e2e8f0;">

        <h2 style="color: #2d3748;">🔒 Security Settings</h2>

        <p style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="wp_form_plugin_enable_security_logging" value="1" <?php checked($enable_security_logging, '1'); ?>>
                <strong>Enable security event logging</strong>
            </label>
            <p class="description">Logs all security events including blocked submissions, suspicious activity, and threats</p>
        </p>

        <p>
            <label><strong>Rate Limit (submissions per 15 minutes)</strong></label><br>
            <input type="number" name="wp_form_plugin_rate_limit" value="<?php echo esc_attr($rate_limit); ?>" min="1" max="100" style="width: 100px; margin-top: 5px;">
            <p class="description">Maximum number of submissions allowed from a single IP address within 15 minutes. Exceeding 2x this limit results in automatic IP blocking. Default: 5</p>
        </p>

        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin-top: 20px; border-radius: 4px;">
            <p style="margin: 0; color: #92400e;"><strong>⚠️ Security Features Active:</strong></p>
            <ul style="margin: 8px 0 0 20px; color: #92400e;">
                <li>Honeypot spam protection</li>
                <li>Rate limiting and auto-blocking</li>
                <li>IP address tracking</li>
                <li>XSS and injection attack detection</li>
                <li>Input validation and sanitization</li>
                <li>Security event logging</li>
            </ul>
            <p style="margin: 12px 0 0 0; color: #92400e;">Visit the <a href="<?php echo admin_url('admin.php?page=wp-form-security'); ?>" style="color: #92400e; text-decoration: underline;"><strong>Security Dashboard</strong></a> to view logs and manage blocked IPs.</p>
        </div>

        <p style="margin-top: 30px;">
            <button type="submit" name="wp_form_plugin_settings_submit" class="button button-primary button-hero" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);">
                Save Settings
            </button>
        </p>
    </form>
</div>
