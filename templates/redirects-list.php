<?php
/**
 * Redirects list template
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Redirects Management 
        <a href="<?php echo esc_url(admin_url('admin.php?page=jolix-seo-redirects&action=add')); ?>" class="page-title-action">Add New</a>
    </h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                switch ($_GET['success']) {
                    case 'added':
                        echo 'Redirect added successfully.';
                        break;
                    case 'updated':
                        echo 'Redirect updated successfully.';
                        break;
                    case 'deleted':
                        echo 'Redirect deleted successfully.';
                        break;
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                switch ($_GET['error']) {
                    case 'empty_fields':
                        echo 'Please fill in all required fields.';
                        break;
                    case 'db_error':
                        echo 'Database error occurred. Please try again.';
                        break;
                    case 'invalid_id':
                        echo 'Invalid redirect ID.';
                        break;
                    default:
                        echo 'An error occurred.';
                        break;
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="tablenav top">
        <div class="alignleft actions">
            <p class="description">
                Manage URL redirects for your site. Supports exact matches, wildcard patterns (* for multiple characters, ? for single character), and regular expressions.
            </p>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column">Source URL</th>
                <th scope="col" class="manage-column">Target URL</th>
                <th scope="col" class="manage-column">Type</th>
                <th scope="col" class="manage-column">Pattern</th>
                <th scope="col" class="manage-column">Priority</th>
                <th scope="col" class="manage-column">Status</th>
                <th scope="col" class="manage-column">Hits</th>
                <th scope="col" class="manage-column">Created</th>
                <th scope="col" class="manage-column">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($redirects)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px;">
                        No redirects found. <a href="<?php echo esc_url(admin_url('admin.php?page=jolix-seo-redirects&action=add')); ?>">Add your first redirect</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($redirects as $redirect): ?>
                    <tr>
                        <td>
                            <code><?php echo esc_html($redirect->source_url); ?></code>
                        </td>
                        <td>
                            <code><?php echo esc_html($redirect->target_url); ?></code>
                        </td>
                        <td>
                            <span class="redirect-type redirect-type-<?php echo esc_attr($redirect->redirect_type); ?>">
                                <?php echo esc_html($redirect->redirect_type); ?>
                            </span>
                        </td>
                        <td>
                            <span class="pattern-type pattern-<?php echo esc_attr($redirect->pattern_type); ?>">
                                <?php 
                                switch ($redirect->pattern_type) {
                                    case 'exact':
                                        echo 'Exact';
                                        break;
                                    case 'wildcard':
                                        echo 'Wildcard';
                                        break;
                                    case 'regex':
                                        echo 'RegEx';
                                        break;
                                    default:
                                        echo esc_html($redirect->pattern_type);
                                }
                                ?>
                            </span>
                        </td>
                        <td>
                            <span class="priority priority-<?php echo esc_attr($redirect->priority); ?>">
                                <?php echo esc_html(ucfirst($redirect->priority)); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($redirect->is_active): ?>
                                <span class="status-active" style="color: #00a32a;">● Active</span>
                            <?php else: ?>
                                <span class="status-inactive" style="color: #dba617;">● Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo intval($redirect->hit_count); ?>
                        </td>
                        <td>
                            <?php echo esc_html(mysql2date('Y/m/d', $redirect->created_at)); ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=jolix-seo-redirects&action=edit&id=' . $redirect->id)); ?>" class="button button-small">Edit</a>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=jolix_seo_delete_redirect&id=' . $redirect->id), 'jolix_seo_redirect_nonce')); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Are you sure you want to delete this redirect?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.redirect-type {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.redirect-type-301 {
    background: #e74c3c;
    color: white;
}
.redirect-type-302 {
    background: #f39c12;
    color: white;
}
.pattern-type {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    background: #ecf0f1;
    color: #2c3e50;
}
.pattern-regex {
    background: #9b59b6;
    color: white;
}
.pattern-wildcard {
    background: #3498db;
    color: white;
}
.priority {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.priority-high {
    background: #e74c3c;
    color: white;
}
.priority-normal {
    background: #95a5a6;
    color: white;
}
.priority-low {
    background: #bdc3c7;
    color: #2c3e50;
}
</style>