<?php
/**
 * Display form entries admin page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'form_entries';

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Handle search and status filter
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

$where_conditions = [];
if ($search) {
    $where_conditions[] = $wpdb->prepare(
        "(name LIKE %s OR email LIKE %s OR message LIKE %s)",
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%'
    );
}
if ($status_filter && in_array($status_filter, ['read', 'unread'])) {
    $where_conditions[] = $wpdb->prepare("status = %s", $status_filter);
}

$where = !empty($where_conditions) ? ' WHERE ' . implode(' AND ', $where_conditions) : '';

$entries = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

$total_entries = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$filtered_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
$total_pages = ceil($filtered_count / $per_page);

$today_entries = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_name WHERE DATE(created_at) = %s",
    current_time('Y-m-d')
));
$unread_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'unread'");

?>
<div class="wrap">
    <div class="wp-form-plugin-admin-header">
        <h1>📧 Form Submissions</h1>
        <p>Manage and view all contact form submissions</p>
    </div>

    <?php if (isset($_GET['deleted'])) : ?>
        <div class="notice notice-success is-dismissible" style="margin: 0 0 20px;">
            <p><strong>Entry deleted successfully.</strong></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['bulk_deleted'])) : ?>
        <div class="notice notice-success is-dismissible" style="margin: 0 0 20px;">
            <p><strong><?php echo intval($_GET['bulk_deleted']); ?> entries deleted successfully.</strong></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['bulk_updated'])) : ?>
        <div class="notice notice-success is-dismissible" style="margin: 0 0 20px;">
            <p><strong><?php echo intval($_GET['bulk_updated']); ?> entries updated successfully.</strong></p>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 16px; flex-wrap: wrap;">
        <form method="get" style="flex: 1; min-width: 300px;">
            <input type="hidden" name="page" value="wp-form-entries">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <input
                    type="search"
                    name="s"
                    value="<?php echo esc_attr($search); ?>"
                    placeholder="Search entries..."
                    style="flex: 1; min-width: 200px; padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                >
                <select name="status" style="padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
                    <option value="">All Status</option>
                    <option value="unread" <?php selected($status_filter, 'unread'); ?>>Unread</option>
                    <option value="read" <?php selected($status_filter, 'read'); ?>>Read</option>
                </select>
                <button
                    type="submit"
                    class="button button-secondary"
                    style="padding: 10px 20px; border-radius: 8px;"
                >
                    Filter
                </button>
                <?php if ($search || $status_filter) : ?>
                    <a
                        href="<?php echo admin_url('admin.php?page=wp-form-entries'); ?>"
                        class="button"
                        style="padding: 10px 16px; border-radius: 8px;"
                    >
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
        <a
            href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-form-entries&action=export'), 'export_entries'); ?>"
            class="button button-primary"
            style="padding: 10px 20px; border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);"
        >
            📥 Export to CSV
        </a>
    </div>

    <div class="wp-form-plugin-stats">
        <div class="wp-form-plugin-stat-card">
            <h3>Total Submissions</h3>
            <div class="stat-number"><?php echo esc_html($total_entries); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>Today's Submissions</h3>
            <div class="stat-number"><?php echo esc_html($today_entries); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>Unread</h3>
            <div class="stat-number" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo esc_html($unread_count); ?></div>
        </div>
        <div class="wp-form-plugin-stat-card">
            <h3>Showing</h3>
            <div style="font-size: 28px; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; white-space: nowrap;"><?php echo esc_html($filtered_count); ?> / <?php echo esc_html($total_entries); ?></div>
        </div>
    </div>

    <?php if ($search || $status_filter) : ?>
        <div style="background: #fffbeb; border: 2px solid #fbbf24; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #92400e;">
            <strong>Filter Results:</strong> Found <?php echo esc_html($filtered_count); ?> entr<?php echo $filtered_count === 1 ? 'y' : 'ies'; ?>
            <?php if ($search) echo ' matching "' . esc_html($search) . '"'; ?>
            <?php if ($status_filter) echo ' with status "' . esc_html($status_filter) . '"'; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($entries)) : ?>
        <form method="post" id="bulk-action-form">
            <?php wp_nonce_field('wp_form_bulk_action_nonce'); ?>
            <div style="background: white; border-radius: 12px 12px 0 0; padding: 16px 20px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0; border-bottom: none;">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                        <input type="checkbox" id="select-all" style="width: 18px; height: 18px;">
                        Select All
                    </label>
                    <select name="wp_form_bulk_action" style="padding: 8px 12px; border-radius: 6px; border: 2px solid #e2e8f0;">
                        <option value="">Bulk Actions</option>
                        <option value="mark_read">Mark as Read</option>
                        <option value="mark_unread">Mark as Unread</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="submit" class="button" style="padding: 8px 16px; border-radius: 6px;" onclick="return confirm('Are you sure you want to perform this action?');">
                        Apply
                    </button>
                    <span id="selected-count" style="color: #667eea; font-weight: 600;"></span>
                </div>
            </div>
    <?php endif; ?>

    <div class="wp-form-plugin-entries-container" style="<?php echo !empty($entries) ? 'border-radius: 0 0 12px 12px;' : ''; ?>">
        <?php if (empty($entries)) : ?>
            <div class="no-entries">
                <div class="no-entries-icon">📭</div>
                <?php if ($search) : ?>
                    <h2 style="margin: 0 0 8px; color: #2d3748;">No results found</h2>
                    <p style="margin: 0; color: #718096;">No entries match your search for "<?php echo esc_html($search); ?>"</p>
                <?php else : ?>
                    <h2 style="margin: 0 0 8px; color: #2d3748;">No submissions yet</h2>
                    <p style="margin: 0; color: #718096;">Form submissions will appear here once users start filling out your contact form.</p>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped" style="border: none;">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" style="display: none;"></th>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 180px;">Name</th>
                        <th style="width: 200px;">Email</th>
                        <th>Message</th>
                        <th style="width: 160px;">Date</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry) : ?>
                        <tr style="<?php echo $entry->status === 'unread' ? 'background: #fffbeb;' : ''; ?>">
                            <td style="padding-left: 20px;">
                                <input type="checkbox" name="entry_ids[]" value="<?php echo esc_attr($entry->id); ?>" class="entry-checkbox" style="width: 18px; height: 18px;">
                            </td>
                            <td class="entry-id">#<?php echo esc_html($entry->id); ?></td>
                            <td>
                                <?php if ($entry->status === 'unread') : ?>
                                    <span style="background: #fbbf24; color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase;">Unread</span>
                                <?php else : ?>
                                    <span style="background: #10b981; color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase;">Read</span>
                                <?php endif; ?>
                            </td>
                            <td class="entry-name"><?php echo esc_html($entry->name); ?></td>
                            <td class="entry-email">
                                <a href="mailto:<?php echo esc_attr($entry->email); ?>">
                                    <?php echo esc_html($entry->email); ?>
                                </a>
                            </td>
                            <td class="entry-message"><?php echo esc_html($entry->message); ?></td>
                            <td class="entry-date"><?php echo esc_html(date('M j, Y g:i A', strtotime($entry->created_at))); ?></td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                    <?php if ($entry->status === 'unread') : ?>
                                        <a
                                            href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-form-entries&action=mark_read&entry_id=' . $entry->id), 'status_entry_' . $entry->id); ?>"
                                            class="button button-small"
                                            title="Mark as Read"
                                            style="padding: 4px 8px; font-size: 12px;"
                                        >
                                            ✓
                                        </a>
                                    <?php else : ?>
                                        <a
                                            href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-form-entries&action=mark_unread&entry_id=' . $entry->id), 'status_entry_' . $entry->id); ?>"
                                            class="button button-small"
                                            title="Mark as Unread"
                                            style="padding: 4px 8px; font-size: 12px;"
                                        >
                                            ✕
                                        </a>
                                    <?php endif; ?>
                                    <a
                                        href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-form-entries&action=delete&entry_id=' . $entry->id), 'delete_entry_' . $entry->id); ?>"
                                        class="button button-small"
                                        title="Delete"
                                        style="color: #e53e3e; border-color: #e53e3e; padding: 4px 8px; font-size: 12px;"
                                        onclick="return confirm('Are you sure you want to delete this entry?');"
                                    >
                                        🗑
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($entries)) : ?>
        </form>

        <?php if ($total_pages > 1) : ?>
            <div style="margin-top: 20px; display: flex; justify-content: center; gap: 8px; align-items: center;">
                <?php
                $base_url = admin_url('admin.php?page=wp-form-entries');
                if ($search) $base_url .= '&s=' . urlencode($search);
                if ($status_filter) $base_url .= '&status=' . urlencode($status_filter);

                if ($current_page > 1) : ?>
                    <a href="<?php echo $base_url . '&paged=' . ($current_page - 1); ?>" class="button">← Previous</a>
                <?php endif; ?>

                <span style="padding: 0 16px; font-weight: 600;">
                    Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                </span>

                <?php if ($current_page < $total_pages) : ?>
                    <a href="<?php echo $base_url . '&paged=' . ($current_page + 1); ?>" class="button">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <script>
        document.getElementById('select-all').addEventListener('change', function(e) {
            document.querySelectorAll('.entry-checkbox').forEach(cb => cb.checked = e.target.checked);
            updateSelectedCount();
        });

        document.querySelectorAll('.entry-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const count = document.querySelectorAll('.entry-checkbox:checked').length;
            const countEl = document.getElementById('selected-count');
            if (count > 0) {
                countEl.textContent = count + ' selected';
            } else {
                countEl.textContent = '';
            }
        }
        </script>
    <?php endif; ?>
</div>
