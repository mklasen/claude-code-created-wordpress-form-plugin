<?php
/**
 * Security page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$log_table = $wpdb->prefix . 'form_security_log';
$blocked_table = $wpdb->prefix . 'form_blocked_ips';
$security = \WPFormPlugin\Plugin::getInstance()->getSecurityHelper();

// Handle IP unblock
if (isset($_GET['action']) && $_GET['action'] === 'unblock' && isset($_GET['ip']) && isset($_GET['_wpnonce'])) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'unblock_ip_' . $_GET['ip'])) {
        $security->unblockIP($_GET['ip']);
        $security->logEvent('ip_unblocked', 'IP manually unblocked: ' . $_GET['ip'], 'info');
        echo '<div class="notice notice-success"><p><strong>IP unblocked successfully.</strong></p></div>';
    }
}

// Handle manual IP block
if (isset($_POST['block_ip_submit'])) {
    check_admin_referer('wp_form_security_block_ip');
    $ip_to_block = sanitize_text_field($_POST['ip_to_block']);
    $block_reason = sanitize_textarea_field($_POST['block_reason']);
    $duration = intval($_POST['block_duration']);

    if (filter_var($ip_to_block, FILTER_VALIDATE_IP)) {
        $security->blockIP($ip_to_block, $block_reason, $duration > 0 ? $duration : null);
        $security->logEvent('ip_blocked_manual', "IP manually blocked: $ip_to_block - $block_reason", 'medium');
        echo '<div class="notice notice-success"><p><strong>IP blocked successfully.</strong></p></div>';
    } else {
        echo '<div class="notice notice-error"><p><strong>Invalid IP address.</strong></p></div>';
    }
}

// Get stats
$total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $log_table");
$high_severity = $wpdb->get_var("SELECT COUNT(*) FROM $log_table WHERE severity IN ('high', 'critical')");
$blocked_ips = $wpdb->get_var("SELECT COUNT(*) FROM $blocked_table");
$today_threats = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $log_table WHERE severity IN ('high', 'critical', 'medium') AND DATE(created_at) = %s",
    current_time('Y-m-d')
));

// Get recent logs
$recent_logs = $wpdb->get_results("SELECT * FROM $log_table ORDER BY created_at DESC LIMIT 50");

// Get blocked IPs
$blocked = $wpdb->get_results("SELECT * FROM $blocked_table ORDER BY blocked_at DESC");

?>
<div class="wrap">
    <div class="wp-form-plugin-admin-header">
        <h1>🔒 Security Dashboard</h1>
        <p>Monitor and manage security events and threats</p>
    </div>

    <div class="wp-form-plugin-stats">
        <div class="wp-form-plugin-stat-card">
            <h3>Total Events</h3>
            <div class="stat-number"><?php echo esc_html($total_logs); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>High Priority</h3>
            <div class="stat-number" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo esc_html($high_severity); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>Blocked IPs</h3>
            <div class="stat-number" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo esc_html($blocked_ips); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>Today's Threats</h3>
            <div class="stat-number"><?php echo esc_html($today_threats); ?></div>
        </div>
    </div>

    <!-- Manual IP Block Form -->
    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Block IP Address</h2>
        <form method="post">
            <?php wp_nonce_field('wp_form_security_block_ip'); ?>
            <div style="display: grid; grid-template-columns: 2fr 3fr 2fr auto; gap: 12px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">IP Address</label>
                    <input type="text" name="ip_to_block" placeholder="192.168.1.1" required style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Reason</label>
                    <input type="text" name="block_reason" placeholder="Spam, abuse, etc." style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Duration (seconds, 0=permanent)</label>
                    <input type="number" name="block_duration" value="0" min="0" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                </div>
                <div>
                    <button type="submit" name="block_ip_submit" class="button button-primary" style="background: #ef4444; border-color: #ef4444; padding: 10px 20px;">Block IP</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Blocked IPs -->
    <?php if (!empty($blocked)) : ?>
        <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 20px;">
            <h2 style="margin-top: 0;">Blocked IP Addresses (<?php echo count($blocked); ?>)</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 150px;">IP Address</th>
                        <th>Reason</th>
                        <th style="width: 180px;">Blocked At</th>
                        <th style="width: 180px;">Expires</th>
                        <th style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blocked as $block) : ?>
                        <tr>
                            <td><code style="background: #fef3c7; padding: 4px 8px; border-radius: 4px;"><?php echo esc_html($block->ip_address); ?></code></td>
                            <td><?php echo esc_html($block->reason ?: 'No reason specified'); ?></td>
                            <td><?php echo esc_html(date('M j, Y g:i A', strtotime($block->blocked_at))); ?></td>
                            <td><?php echo $block->expires_at ? esc_html(date('M j, Y g:i A', strtotime($block->expires_at))) : '<strong>Permanent</strong>'; ?></td>
                            <td>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-form-security&action=unblock&ip=' . urlencode($block->ip_address)), 'unblock_ip_' . $block->ip_address); ?>" class="button button-small">Unblock</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Security Logs -->
    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
        <h2 style="margin-top: 0;">Recent Security Events (Last 50)</h2>
        <?php if (!empty($recent_logs)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Severity</th>
                        <th style="width: 160px;">Event Type</th>
                        <th style="width: 130px;">IP Address</th>
                        <th>Details</th>
                        <th style="width: 170px;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_logs as $log) : ?>
                        <tr>
                            <td>
                                <?php
                                $severity_colors = [
                                    'critical' => '#dc2626',
                                    'high' => '#ef4444',
                                    'medium' => '#f59e0b',
                                    'warning' => '#fbbf24',
                                    'low' => '#3b82f6',
                                    'info' => '#10b981',
                                ];
                                $color = $severity_colors[$log->severity] ?? '#6b7280';
                                ?>
                                <span style="background: <?php echo $color; ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; display: inline-block;">
                                    <?php echo esc_html($log->severity); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log->event_type); ?></td>
                            <td><code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;"><?php echo esc_html($log->ip_address); ?></code></td>
                            <td><?php echo esc_html($log->details); ?></td>
                            <td><?php echo esc_html(date('M j, g:i:s A', strtotime($log->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p style="text-align: center; padding: 40px; color: #718096;">No security events logged yet.</p>
        <?php endif; ?>
    </div>
</div>
