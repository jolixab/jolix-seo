<?php
/**
 * Redirect form template (for both add and edit)
 * 
 * @package JolixSEO
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = isset($redirect);
$form_title = $is_edit ? 'Edit Redirect' : 'Add New Redirect';
$form_action = $is_edit ? 'jolix_seo_update_redirect' : 'jolix_seo_add_redirect';
?>

<div class="wrap">
    <h1><span style="color: #0073aa;">Jolix SEO</span> - <?php echo esc_html($form_title); ?></h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=jolix-seo-redirects')); ?>" class="page-title-action">← Back to Redirects</a>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                switch (sanitize_text_field(wp_unslash($_GET['error']))) {
                    case 'empty_fields':
                        echo 'Please fill in all required fields.';
                        break;
                    case 'db_error':
                        echo 'Database error occurred. Please try again.';
                        break;
                    default:
                        echo 'An error occurred.';
                        break;
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="redirect-form">
        <?php wp_nonce_field('jolix_seo_redirect_nonce'); ?>
        <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="redirect_id" value="<?php echo esc_attr($redirect->id); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="source_url">Source URL *</label>
                </th>
                <td>
                    <input type="text" 
                           id="source_url" 
                           name="source_url" 
                           value="<?php echo $is_edit ? esc_attr($redirect->source_url) : ''; ?>" 
                           class="regular-text" 
                           placeholder="/old-page" 
                           required>
                    <p class="description">
                        The URL to redirect from. Can be exact match, wildcard pattern (* and ?), or regular expression.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="target_url">Target URL *</label>
                </th>
                <td>
                    <input type="text" 
                           id="target_url" 
                           name="target_url" 
                           value="<?php echo $is_edit ? esc_attr($redirect->target_url) : ''; ?>" 
                           class="regular-text" 
                           placeholder="/new-page or https://example.com/page" 
                           required>
                    <p class="description">
                        The URL to redirect to. Can be relative (/page) or absolute (https://example.com/page).
                        For regex patterns, use $1, $2 etc. for captured groups.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="pattern_type">Pattern Type</label>
                </th>
                <td>
                    <select id="pattern_type" name="pattern_type">
                        <option value="exact" <?php echo ($is_edit && $redirect->pattern_type === 'exact') ? 'selected' : ''; ?>>
                            Exact Match
                        </option>
                        <option value="wildcard" <?php echo ($is_edit && $redirect->pattern_type === 'wildcard') ? 'selected' : ''; ?>>
                            Wildcard Pattern (* and ?)
                        </option>
                        <option value="regex" <?php echo ($is_edit && $redirect->pattern_type === 'regex') ? 'selected' : ''; ?>>
                            Regular Expression
                        </option>
                    </select>
                    <p class="description pattern-help">
                        <span data-type="exact">Matches the URL exactly as entered.</span>
                        <span data-type="wildcard" style="display:none;">Use * for multiple characters, ? for single character. Example: /blog/* matches /blog/post-1, /blog/category/news</span>
                        <span data-type="regex" style="display:none;">Full regular expression support. Example: ^/product/(\d+)$ matches /product/123</span>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="priority">Priority</label>
                </th>
                <td>
                    <select id="priority" name="priority">
                        <option value="high" <?php echo ($is_edit && $redirect->priority === 'high') ? 'selected' : ''; ?>>
                            High Priority
                        </option>
                        <option value="normal" <?php echo ($is_edit && $redirect->priority === 'normal') || !$is_edit ? 'selected' : ''; ?>>
                            Normal Priority
                        </option>
                        <option value="low" <?php echo ($is_edit && $redirect->priority === 'low') ? 'selected' : ''; ?>>
                            Low Priority
                        </option>
                    </select>
                    <p class="description">
                        Priority determines redirect order when multiple rules could match. Within the same priority level: 
                        Exact matches are checked first, then regex patterns, then wildcards.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="redirect_type">Redirect Type</label>
                </th>
                <td>
                    <select id="redirect_type" name="redirect_type">
                        <option value="301" <?php echo ($is_edit && $redirect->redirect_type == 301) || !$is_edit ? 'selected' : ''; ?>>
                            301 - Permanent Redirect (SEO-friendly)
                        </option>
                        <option value="302" <?php echo ($is_edit && $redirect->redirect_type == 302) ? 'selected' : ''; ?>>
                            302 - Temporary Redirect
                        </option>
                        <option value="307" <?php echo ($is_edit && $redirect->redirect_type == 307) ? 'selected' : ''; ?>>
                            307 - Temporary Redirect (Strict)
                        </option>
                        <option value="308" <?php echo ($is_edit && $redirect->redirect_type == 308) ? 'selected' : ''; ?>>
                            308 - Permanent Redirect (Strict)
                        </option>
                    </select>
                    <p class="description">
                        301 for permanent moves (passes SEO value), 302 for temporary redirects.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="is_active">Status</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               <?php echo ($is_edit && $redirect->is_active) || !$is_edit ? 'checked' : ''; ?>>
                        Active (redirect will be applied)
                    </label>
                </td>
            </tr>
        </table>

        <div class="redirect-tester" style="margin: 20px 0; padding: 15px; background: #f1f1f1; border-radius: 5px;">
            <h3>Test Redirect Pattern</h3>
            <p>Test your pattern against sample URLs to ensure it works correctly.</p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="test_url">Test URL</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="test_url" 
                               placeholder="/example/path/to/test" 
                               class="regular-text">
                        <button type="button" id="test-pattern" class="button">Test Pattern</button>
                    </td>
                </tr>
            </table>
            
            <div id="test-results" style="margin-top: 10px;"></div>
        </div>

        <?php submit_button($is_edit ? 'Update Redirect' : 'Add Redirect'); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Update pattern help text
    $('#pattern_type').change(function() {
        var selectedType = $(this).val();
        $('.pattern-help span').hide();
        $('.pattern-help span[data-type="' + selectedType + '"]').show();
    });

    // Test pattern functionality
    $('#test-pattern').click(function() {
        var sourceUrl = $('#source_url').val();
        var targetUrl = $('#target_url').val();
        var patternType = $('#pattern_type').val();
        var testUrl = $('#test_url').val();

        if (!sourceUrl || !testUrl) {
            $('#test-results').html('<div class="notice notice-error"><p>Please enter both source URL and test URL.</p></div>');
            return;
        }

        $.post(ajaxurl, {
            action: 'jolix_seo_test_redirect',
            source_url: sourceUrl,
            target_url: targetUrl,
            pattern_type: patternType,
            test_url: testUrl,
            _wpnonce: '<?php echo esc_attr(wp_create_nonce('jolix_seo_test_redirect_nonce')); ?>'
        }, function(response) {
            if (response.success) {
                var html = '<div class="notice ' + (response.data.matches ? 'notice-success' : 'notice-warning') + '">';
                html += '<p><strong>Test Result:</strong> ';
                if (response.data.matches) {
                    html += 'Pattern MATCHES! ✓<br>';
                    html += '<strong>Would redirect to:</strong> <code>' + response.data.processed_target + '</code>';
                } else {
                    html += 'Pattern does not match. ✗';
                }
                html += '</p></div>';
                $('#test-results').html(html);
            } else {
                $('#test-results').html('<div class="notice notice-error"><p>Test failed. Please try again.</p></div>');
            }
        });
    });

    // Trigger pattern help update on page load
    $('#pattern_type').trigger('change');
});
</script>

<style>
.redirect-tester {
    border-left: 4px solid #0073aa;
}
.test-result-match {
    color: #00a32a;
    font-weight: bold;
}
.test-result-no-match {
    color: #d63638;
    font-weight: bold;
}
</style>