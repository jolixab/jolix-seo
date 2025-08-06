<?php

/**
 * Settings page template
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Jolix SEO Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('jolix_seo_settings'); ?>
        <?php do_settings_sections('jolix_seo_settings'); ?>

        <h2>Global Suffix Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="jolix_seo_title_suffix">Title Suffix</label>
                </th>
                <td>
                    <input type="text" id="jolix_seo_title_suffix" name="jolix_seo_title_suffix"
                        value="<?php echo esc_attr(get_option('jolix_seo_title_suffix', '')); ?>"
                        class="regular-text" placeholder="e.g., | Your Site Name">
                    <p class="description">Optional suffix to add to all meta titles (e.g., "| Your Site Name")</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="jolix_seo_title_truncate">Truncate Title to Fit Suffix</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="jolix_seo_title_truncate" name="jolix_seo_title_truncate"
                            value="1" <?php checked(get_option('jolix_seo_title_truncate', 0), 1); ?>>
                        Automatically truncate title to ensure title + suffix stays within 60 characters
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="jolix_seo_description_suffix">Description Suffix</label>
                </th>
                <td>
                    <input type="text" id="jolix_seo_description_suffix" name="jolix_seo_description_suffix"
                        value="<?php echo esc_attr(get_option('jolix_seo_description_suffix', '')); ?>"
                        class="regular-text" placeholder="e.g., Learn more at YourSite.com">
                    <p class="description">Optional suffix to add to all meta descriptions</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="jolix_seo_description_truncate">Truncate Description to Fit Suffix</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="jolix_seo_description_truncate" name="jolix_seo_description_truncate"
                            value="1" <?php checked(get_option('jolix_seo_description_truncate', 0), 1); ?>>
                        Automatically truncate description to ensure description + suffix stays within 160 characters
                    </label>
                </td>
            </tr>
        </table>

        <h2>XML Sitemap Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="jolix_seo_enable_sitemap">Enable XML Sitemap</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="jolix_seo_enable_sitemap" name="jolix_seo_enable_sitemap"
                            value="1" <?php checked(get_option('jolix_seo_enable_sitemap', 1), 1); ?>>
                        Generate XML sitemap at <code><?php echo esc_url(home_url('/sitemap.xml')); ?></code>
                    </label>
                    <p class="description">
                        <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" class="button button-secondary">View Sitemap</a>
                        <a href="<?php echo esc_url(admin_url('options-general.php?page=jolix-seo-settings&flush_rewrite=1')); ?>" class="button button-secondary">Refresh Sitemap URL</a>
                        <a href="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/sitemap.xml'); ?>" target="_blank" class="button button-secondary">View Backup Sitemap</a>
                    </p>
                    <?php if (get_option('jolix_seo_enable_sitemap', 1)): ?>
                        <p class="description">
                            <strong>Sitemap Status:</strong>
                            <span id="sitemap-status">Checking...</span>
                        </p>
                        <script>
                            jQuery(document).ready(function($) {
                                // Check primary sitemap
                                $.get('<?php echo esc_url(home_url('/sitemap.xml')); ?>')
                                    .done(function() {
                                        $('#sitemap-status').html('<span style="color: green;">✓ Primary sitemap working</span>');
                                    })
                                    .fail(function() {
                                        // Try backup sitemap
                                        $.get('<?php echo esc_url(wp_upload_dir()['baseurl'] . '/sitemap.xml'); ?>')
                                            .done(function() {
                                                $('#sitemap-status').html('<span style="color: orange;">⚠ Using backup sitemap (primary failed)</span>');
                                            })
                                            .fail(function() {
                                                $('#sitemap-status').html('<span style="color: red;">✗ Sitemap not accessible - try refreshing</span>');
                                            });
                                    });
                            });
                        </script>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label>Include Post Types</label>
                </th>
                <td>
                    <?php
                    $selected_post_types = get_option('jolix_seo_sitemap_post_types', array('post', 'page'));
                    $public_post_types = get_post_types(array('public' => true), 'objects');

                    foreach ($public_post_types as $post_type) {
                        $checked = in_array($post_type->name, $selected_post_types) ? 'checked' : '';
                        echo '<label style="margin-right: 15px; display: inline-block;">';
                        echo '<input type="checkbox" name="jolix_seo_sitemap_post_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . '> ';
                        echo esc_html($post_type->labels->name);
                        echo '</label>';
                    }
                    ?>
                    <p class="description">Select which post types to include in the sitemap</p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <div style="margin-top: 30px; padding: 15px; background: #e8f4f8; border-left: 4px solid #0073aa;">
        <h3>SEO Redirects Management</h3>
        <p>Manage 301/302 redirects with advanced pattern matching support.</p>
        <p>
            <a href="<?php echo esc_url(admin_url('tools.php?page=jolix-seo-redirects')); ?>" class="button button-primary">
                Manage Redirects
            </a>
            <a href="<?php echo esc_url(admin_url('tools.php?page=jolix-seo-redirects&action=add')); ?>" class="button button-secondary">
                Add New Redirect
            </a>
        </p>
        <ul>
            <li><strong>Exact Matches:</strong> /old-page redirects to /new-page</li>
            <li><strong>Wildcard Patterns:</strong> /blog/* redirects to /news/* (supports * and ? wildcards)</li>
            <li><strong>Regular Expressions:</strong> Advanced pattern matching with capture groups</li>
            <li><strong>Priority System:</strong> High/Normal/Low priority with intelligent pattern ordering</li>
            <li><strong>Hit Tracking:</strong> Monitor redirect usage and performance</li>
        </ul>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa;">
        <h3>How it works:</h3>
        <ul>
            <li><strong>Suffixes are applied globally</strong> to all posts and pages when rendering meta tags</li>
            <li><strong>Individual post settings</strong> in the editor show the base title/description without suffix</li>
            <li><strong>Truncation happens automatically</strong> if enabled, ensuring optimal SEO length limits</li>
            <li><strong>XML Sitemap</strong> is automatically generated and updated when you publish/update content</li>
            <li><strong>WooCommerce Integration</strong> provides additional product-specific SEO fields if WooCommerce is active</li>
            <li><strong>Leave suffix empty</strong> to disable the feature for that field</li>
        </ul>
    </div>
</div>