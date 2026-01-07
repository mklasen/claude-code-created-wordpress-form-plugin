<?php
/**
 * Analytics page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'form_entries';

// Get stats
$total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$today = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE DATE(created_at) = %s", current_time('Y-m-d')));
$this_week = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE created_at >= %s", date('Y-m-d', strtotime('-7 days'))));
$this_month = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE MONTH(created_at) = %d AND YEAR(created_at) = %d", date('m'), date('Y')));
$unread = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'unread'");

// Get daily stats for last 30 days
$daily_stats = $wpdb->get_results("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM $table_name
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

$dates = [];
$counts = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('M j', strtotime($date));
    $count = 0;
    foreach ($daily_stats as $stat) {
        if ($stat->date === $date) {
            $count = $stat->count;
            break;
        }
    }
    $counts[] = $count;
}

?>
<div class="wrap">
    <div class="wp-form-plugin-admin-header">
        <h1>📊 Analytics</h1>
        <p>Track your form submission trends and performance</p>
    </div>

    <div class="wp-form-plugin-stats">
        <div class="wp-form-plugin-stat-card">
            <h3>Total Submissions</h3>
            <div class="stat-number"><?php echo esc_html($total); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>Today</h3>
            <div class="stat-number"><?php echo esc_html($today); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>This Week</h3>
            <div class="stat-number"><?php echo esc_html($this_week); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>This Month</h3>
            <div class="stat-number"><?php echo esc_html($this_month); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>Unread</h3>
            <div class="stat-number" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo esc_html($unread); ?></div>
        </div>
    </div>

    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-top: 20px;">
        <h2 style="margin-top: 0;">Submissions Over Time (Last 30 Days)</h2>
        <canvas id="submissionsChart" style="max-height: 400px;"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    const ctx = document.getElementById('submissionsChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Submissions',
                data: <?php echo json_encode($counts); ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    </script>
</div>
