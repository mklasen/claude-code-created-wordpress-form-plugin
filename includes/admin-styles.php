<?php
/**
 * Admin styles for Form Plugin pages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if we're on a Form Plugin admin page
$allowed_pages = [
    'toplevel_page_wp-form-entries',
    'form-plugin_page_wp-form-analytics',
    'form-plugin_page_wp-form-settings',
    'form-plugin_page_wp-form-security'
];

// $hook variable is set by Plugin::adminStyles() method
if (!isset($hook) || !in_array($hook, $allowed_pages)) {
    return;
}
?>
<style>
    .wp-form-plugin-admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        margin-left: -20px;
        margin-right: -20px;
        margin-top: -10px;
        padding: 40px 40px 50px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    .wp-form-plugin-admin-header h1 {
        color: white;
        font-size: 32px;
        margin: 0 0 10px;
        font-weight: 700;
    }
    .wp-form-plugin-admin-header p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 16px;
        margin: 0;
    }
    .wp-form-plugin-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .wp-form-plugin-stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
    }
    .wp-form-plugin-stat-card h3 {
        margin: 0 0 8px;
        font-size: 14px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wp-form-plugin-stat-card .stat-number {
        font-size: 36px;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .wp-form-plugin-entries-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .wp-form-plugin-entries-container table {
        margin: 0;
    }
    .wp-form-plugin-entries-container th {
        background: #f7fafc;
        font-weight: 600;
        color: #2d3748;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 16px 20px;
    }
    .wp-form-plugin-entries-container td {
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .wp-form-plugin-entries-container tr:last-child td {
        border-bottom: none;
    }
    .wp-form-plugin-entries-container tr:hover {
        background: #f7fafc;
    }
    .entry-message {
        max-width: 400px;
        line-height: 1.6;
        color: #4a5568;
    }
    .entry-email {
        color: #667eea;
        font-weight: 500;
    }
    .entry-name {
        font-weight: 600;
        color: #2d3748;
    }
    .entry-date {
        color: #718096;
        font-size: 13px;
    }
    .entry-id {
        color: #a0aec0;
        font-weight: 600;
    }
    .no-entries {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
    }
    .no-entries-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
</style>
