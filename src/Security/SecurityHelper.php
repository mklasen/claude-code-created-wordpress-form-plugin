<?php

namespace WPFormPlugin\Security;

class SecurityHelper
{
    private string $log_table;
    private string $blocked_table;

    public function __construct()
    {
        global $wpdb;
        $this->log_table = $wpdb->prefix . 'form_security_log';
        $this->blocked_table = $wpdb->prefix . 'form_blocked_ips';
    }

    public function getClientIP(): string
    {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    public function logEvent(string $eventType, string $details = '', string $severity = 'info'): void
    {
        global $wpdb;

        $wpdb->insert(
            $this->log_table,
            [
                'event_type' => $eventType,
                'ip_address' => $this->getClientIP(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
                'details' => $details,
                'severity' => $severity,
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
    }

    public function isIPBlocked(string $ip): bool
    {
        global $wpdb;

        // Clean up expired blocks first
        $wpdb->query("DELETE FROM {$this->blocked_table} WHERE expires_at IS NOT NULL AND expires_at < NOW()");

        $blocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->blocked_table} WHERE ip_address = %s",
            $ip
        ));

        return $blocked > 0;
    }

    public function checkRateLimit(string $ip): bool
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'form_entries';

        // Get submission count in last 15 minutes
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE ip_address = %s AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            $ip
        ));

        $limit = get_option('wp_form_plugin_rate_limit', 5);

        if ($count >= $limit) {
            // Auto-block if exceeds rate limit significantly
            if ($count >= $limit * 2) {
                $this->blockIP($ip, 'Automatic block: Excessive submissions (' . $count . ' in 15 minutes)', 3600);
                $this->logEvent('auto_block', "IP blocked for excessive submissions: $count in 15 minutes", 'high');
            }
            return false;
        }

        return true;
    }

    public function blockIP(string $ip, string $reason = '', ?int $duration = null): void
    {
        global $wpdb;

        $data = [
            'ip_address' => $ip,
            'reason' => $reason,
        ];
        $format = ['%s', '%s'];

        if ($duration) {
            $data['expires_at'] = date('Y-m-d H:i:s', time() + $duration);
            $format[] = '%s';
        }

        $wpdb->replace($this->blocked_table, $data, $format);
    }

    public function unblockIP(string $ip): void
    {
        global $wpdb;
        $wpdb->delete($this->blocked_table, ['ip_address' => $ip], ['%s']);
    }
}
