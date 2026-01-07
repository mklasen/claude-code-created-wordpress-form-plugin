<?php

namespace WPFormPlugin\Api;

use WPFormPlugin\Security\SecurityHelper;
use WP_Error;
use WP_REST_Request;

class FormSubmissionHandler
{
    private SecurityHelper $security;
    private string $table_name;

    public function __construct(SecurityHelper $security)
    {
        $this->security = $security;
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'form_entries';
    }

    public function handleSubmission(WP_REST_Request $request)
    {
        $ip = $this->security->getClientIP();

        // Check if IP is blocked
        if ($this->security->isIPBlocked($ip)) {
            $this->security->logEvent('blocked_submission', 'Submission attempt from blocked IP', 'warning');
            return new WP_Error('blocked', 'Your IP address has been blocked', ['status' => 403]);
        }

        // Rate limiting check
        if (!$this->security->checkRateLimit($ip)) {
            $this->security->logEvent('rate_limit_exceeded', 'Too many submissions in short period', 'warning');
            return new WP_Error('rate_limit', 'Too many submissions. Please try again later.', ['status' => 429]);
        }

        // Honeypot spam protection
        $honeypot = $request->get_param('website');
        if (!empty($honeypot)) {
            $this->security->logEvent('honeypot_caught', 'Bot caught by honeypot field', 'medium');
            return rest_ensure_response([
                'success' => true,
                'message' => 'Form submitted successfully!',
            ]);
        }

        // Validate and sanitize inputs
        $validation = $this->validateInputs($request);
        if (is_wp_error($validation)) {
            return $validation;
        }

        [$name, $email, $message] = $validation;

        // Check for suspicious content
        if ($this->hasSuspiciousContent($name, $email, $message)) {
            $this->security->logEvent('suspicious_content', 'Suspicious pattern detected in submission', 'high');
            $this->security->blockIP($ip, 'Automatic block: Suspicious content detected', 7200);
            return new WP_Error('invalid_input', 'Invalid input detected', ['status' => 400]);
        }

        // Save to database
        if (!$this->saveEntry($name, $email, $message, $ip)) {
            $this->security->logEvent('db_error', 'Failed to save form entry', 'high');
            return new WP_Error('db_error', 'Failed to save form entry', ['status' => 500]);
        }

        // Log successful submission
        $this->security->logEvent('form_submitted', "Submission from $name ($email)", 'info');

        // Send notifications
        $this->sendNotifications($name, $email, $message);

        // Get custom success message
        $success_message = get_option('wp_form_plugin_success_message', 'Thank you! Your message has been sent successfully.');

        return rest_ensure_response([
            'success' => true,
            'message' => $success_message,
        ]);
    }

    private function validateInputs(WP_REST_Request $request)
    {
        $name = sanitize_text_field($request->get_param('name'));
        $email = sanitize_email($request->get_param('email'));
        $message = sanitize_textarea_field($request->get_param('message'));

        if (empty($name) || empty($email) || empty($message)) {
            $this->security->logEvent('validation_error', 'Missing required fields', 'low');
            return new WP_Error('missing_fields', 'All fields are required', ['status' => 400]);
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            $this->security->logEvent('validation_error', 'Invalid name length', 'low');
            return new WP_Error('invalid_name', 'Name must be between 2 and 100 characters', ['status' => 400]);
        }

        if (!is_email($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->security->logEvent('validation_error', 'Invalid email format: ' . $email, 'low');
            return new WP_Error('invalid_email', 'Invalid email address', ['status' => 400]);
        }

        if (strlen($message) < 10 || strlen($message) > 5000) {
            $this->security->logEvent('validation_error', 'Invalid message length', 'low');
            return new WP_Error('invalid_message', 'Message must be between 10 and 5000 characters', ['status' => 400]);
        }

        return [$name, $email, $message];
    }

    private function hasSuspiciousContent(string $name, string $email, string $message): bool
    {
        $suspicious_patterns = [
            '/<script/i',
            '/<iframe/i',
            '/javascript:/i',
            '/onclick=/i',
            '/onerror=/i',
        ];

        $combined = $name . $email . $message;
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $combined)) {
                return true;
            }
        }

        return false;
    }

    private function saveEntry(string $name, string $email, string $message, string $ip): bool
    {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table_name,
            [
                'name' => $name,
                'email' => $email,
                'message' => $message,
                'ip_address' => $ip,
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );

        return $result !== false;
    }

    private function sendNotifications(string $name, string $email, string $message): void
    {
        // Send email notification to admin (if enabled)
        $enable_notifications = get_option('wp_form_plugin_enable_notifications', '1');
        if ($enable_notifications === '1') {
            $notification_email = get_option('wp_form_plugin_notification_email', get_option('admin_email'));
            if (empty($notification_email)) {
                $notification_email = get_option('admin_email');
            }

            $subject = sprintf('[%s] New Form Submission from %s', get_bloginfo('name'), $name);
            $admin_message = sprintf(
                "New form submission received:\n\nName: %s\nEmail: %s\nMessage:\n%s\n\nView all submissions: %s",
                $name,
                $email,
                $message,
                admin_url('admin.php?page=wp-form-entries')
            );
            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            wp_mail($notification_email, $subject, $admin_message, $headers);
        }

        // Send auto-reply to submitter (if enabled)
        $enable_auto_reply = get_option('wp_form_plugin_enable_auto_reply', '1');
        if ($enable_auto_reply === '1') {
            $auto_reply_subject = get_option('wp_form_plugin_auto_reply_subject', 'Thank you for contacting us!');
            $auto_reply_template = get_option('wp_form_plugin_auto_reply_message', "Hi {name},\n\nThank you for reaching out! We've received your message and will get back to you as soon as possible.\n\nBest regards");

            $auto_reply_message = str_replace(
                ['{name}', '{email}', '{message}'],
                [$name, $email, $message],
                $auto_reply_template
            );

            $headers = [
                'Content-Type: text/plain; charset=UTF-8',
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
            ];

            wp_mail($email, $auto_reply_subject, $auto_reply_message, $headers);
        }
    }
}
