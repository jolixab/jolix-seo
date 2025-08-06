<?php

/**
 * Redirect management functionality
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JolixSEORedirects
{
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'jolix_seo_redirects';
        
        add_action('template_redirect', array($this, 'handle_redirects'), 1);
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_jolix_seo_add_redirect', array($this, 'handle_add_redirect'));
        add_action('admin_post_jolix_seo_delete_redirect', array($this, 'handle_delete_redirect'));
        add_action('admin_post_jolix_seo_update_redirect', array($this, 'handle_update_redirect'));
        add_action('wp_ajax_jolix_seo_test_redirect', array($this, 'ajax_test_redirect'));
    }

    /**
     * Create redirects table on plugin activation
     */
    public function create_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            source_url varchar(500) NOT NULL,
            target_url varchar(500) NOT NULL,
            redirect_type int(3) NOT NULL DEFAULT 301,
            pattern_type varchar(10) NOT NULL DEFAULT 'exact',
            priority varchar(10) NOT NULL DEFAULT 'normal',
            is_active tinyint(1) NOT NULL DEFAULT 1,
            hit_count int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY source_url (source_url(191)),
            KEY is_active (is_active),
            KEY pattern_type (pattern_type),
            KEY priority (priority)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Handle redirects on 404 pages
     */
    public function handle_redirects()
    {
        if (!is_404()) {
            return;
        }

        $requested_url = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $redirect = $this->find_matching_redirect($requested_url);

        if ($redirect) {
            $this->increment_hit_count($redirect->id);
            $target_url = $this->process_target_url($redirect, $requested_url);
            
            wp_redirect($target_url, $redirect->redirect_type);
            exit;
        }
    }

    /**
     * Find matching redirect for requested URL
     */
    private function find_matching_redirect($requested_url)
    {
        global $wpdb;

        // Get all active redirects ordered by priority then pattern specificity
        $redirects = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} 
             WHERE is_active = 1 
             ORDER BY 
                CASE priority 
                    WHEN 'high' THEN 1
                    WHEN 'normal' THEN 2
                    WHEN 'low' THEN 3
                    ELSE 4
                END,
                CASE pattern_type
                    WHEN 'exact' THEN 1
                    WHEN 'regex' THEN 2
                    WHEN 'wildcard' THEN 3
                    ELSE 4
                END,
                LENGTH(source_url) DESC"
        );

        foreach ($redirects as $redirect) {
            if ($this->url_matches_pattern($requested_url, $redirect->source_url, $redirect->pattern_type)) {
                return $redirect;
            }
        }

        return null;
    }

    /**
     * Check if URL matches pattern based on pattern type
     */
    private function url_matches_pattern($url, $pattern, $pattern_type)
    {
        switch ($pattern_type) {
            case 'exact':
                return $url === $pattern;
                
            case 'wildcard':
                return $this->wildcard_match($url, $pattern);
                
            case 'regex':
                return $this->regex_match($url, $pattern);
                
            default:
                return false;
        }
    }

    /**
     * Wildcard pattern matching (* for multiple chars, ? for single char)
     */
    private function wildcard_match($string, $pattern)
    {
        // Escape special regex characters except * and ?
        $pattern = preg_quote($pattern, '/');
        
        // Convert wildcards to regex equivalents
        $pattern = str_replace('\*', '.*', $pattern);
        $pattern = str_replace('\?', '.', $pattern);
        
        // Make it a full match
        $pattern = '/^' . $pattern . '$/i';
        
        return preg_match($pattern, $string);
    }

    /**
     * Regex pattern matching with error handling
     */
    private function regex_match($string, $pattern)
    {
        // Ensure pattern has delimiters
        if (!preg_match('/^\/.*\/$/', $pattern)) {
            $pattern = '/' . $pattern . '/i';
        }

        // Test for valid regex
        if (@preg_match($pattern, '') === false) {
            return false;
        }

        return preg_match($pattern, $string);
    }

    /**
     * Process target URL with captured groups (for regex) or wildcard replacements
     */
    private function process_target_url($redirect, $requested_url)
    {
        $target = $redirect->target_url;

        if ($redirect->pattern_type === 'regex') {
            // Handle regex captures
            $pattern = $redirect->source_url;
            if (!preg_match('/^\/.*\/$/', $pattern)) {
                $pattern = '/' . $pattern . '/i';
            }
            
            if (@preg_match($pattern, '') !== false) {
                $target = preg_replace($pattern, $target, $requested_url);
            }
        } elseif ($redirect->pattern_type === 'wildcard') {
            // Handle wildcard captures (simple implementation)
            $target = $this->process_wildcard_target($requested_url, $redirect->source_url, $target);
        }

        // Ensure absolute URL
        if (!wp_parse_url($target, PHP_URL_HOST)) {
            $target = home_url($target);
        }

        return $target;
    }

    /**
     * Process wildcard target URL (basic implementation)
     */
    private function process_wildcard_target($url, $source_pattern, $target)
    {
        // For wildcard patterns, we'll do a simple replacement
        // This could be enhanced to capture wildcard groups
        return $target;
    }

    /**
     * Increment hit counter for redirect
     */
    private function increment_hit_count($redirect_id)
    {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "UPDATE {$this->table_name} SET hit_count = hit_count + 1 WHERE id = %d",
            $redirect_id
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        // Add to Tools menu
        add_management_page(
            'SEO Redirects',
            'SEO Redirects',
            'manage_options',
            'jolix-seo-redirects',
            array($this, 'redirects_page')
        );
        
        // Also add as submenu under Jolix SEO Settings for convenience
        add_submenu_page(
            'jolix-seo-settings',
            'Redirects',
            'Redirects',
            'manage_options',
            'jolix-seo-redirects',
            array($this, 'redirects_page')
        );
    }

    /**
     * Redirects management page
     */
    public function redirects_page()
    {
        // Verify nonce for admin page actions
        if (isset($_GET['action']) && $_GET['action'] !== 'list') {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'redirect_admin_nonce')) {
                wp_die('Security verification failed.');
            }
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : 'list';
        
        switch ($action) {
            case 'add':
                $this->render_add_redirect_form();
                break;
            case 'edit':
                $this->render_edit_redirect_form();
                break;
            default:
                $this->render_redirects_list();
                break;
        }
    }

    /**
     * Render redirects list
     */
    private function render_redirects_list()
    {
        global $wpdb;
        
        $redirects = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC");
        
        include JOLIX_SEO_PLUGIN_DIR . 'templates/redirects-list.php';
    }

    /**
     * Render add redirect form
     */
    private function render_add_redirect_form()
    {
        include JOLIX_SEO_PLUGIN_DIR . 'templates/redirect-form.php';
    }

    /**
     * Render edit redirect form
     */
    private function render_edit_redirect_form()
    {
        global $wpdb;
        
        // Verify nonce for edit action
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'redirect_admin_nonce')) {
            wp_die('Security verification failed.');
        }
        
        $redirect_id = isset($_GET['id']) ? intval(wp_unslash($_GET['id'])) : 0;
        $redirect = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $redirect_id
        ));
        
        if (!$redirect) {
            wp_die('Redirect not found');
        }
        
        include JOLIX_SEO_PLUGIN_DIR . 'templates/redirect-form.php';
    }

    /**
     * Handle add redirect form submission
     */
    public function handle_add_redirect()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        check_admin_referer('jolix_seo_redirect_nonce');

        $source_url = isset($_POST['source_url']) ? sanitize_text_field(wp_unslash($_POST['source_url'])) : '';
        $target_url = isset($_POST['target_url']) ? sanitize_text_field(wp_unslash($_POST['target_url'])) : '';
        $redirect_type = isset($_POST['redirect_type']) ? intval($_POST['redirect_type']) : 301;
        $pattern_type = isset($_POST['pattern_type']) ? sanitize_text_field(wp_unslash($_POST['pattern_type'])) : 'exact';
        $priority = isset($_POST['priority']) ? sanitize_text_field(wp_unslash($_POST['priority'])) : 'normal';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($source_url) || empty($target_url)) {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&action=add&error=empty_fields'));
            exit;
        }

        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'source_url' => $source_url,
                'target_url' => $target_url,
                'redirect_type' => $redirect_type,
                'pattern_type' => $pattern_type,
                'priority' => $priority,
                'is_active' => $is_active
            ),
            array('%s', '%s', '%d', '%s', '%s', '%d')
        );

        if ($result === false) {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&error=db_error'));
        } else {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&success=added'));
        }
        exit;
    }

    /**
     * Handle delete redirect
     */
    public function handle_delete_redirect()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        // Verify nonce for delete action
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'jolix_seo_redirect_nonce')) {
            wp_die('Security verification failed.');
        }

        $redirect_id = isset($_GET['id']) ? intval(wp_unslash($_GET['id'])) : 0;
        
        if (!$redirect_id) {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&error=invalid_id'));
            exit;
        }

        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $redirect_id),
            array('%d')
        );

        if ($result === false) {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&error=db_error'));
        } else {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&success=deleted'));
        }
        exit;
    }

    /**
     * Handle update redirect
     */
    public function handle_update_redirect()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        check_admin_referer('jolix_seo_redirect_nonce');

        $redirect_id = isset($_POST['redirect_id']) ? intval($_POST['redirect_id']) : 0;
        $source_url = isset($_POST['source_url']) ? sanitize_text_field(wp_unslash($_POST['source_url'])) : '';
        $target_url = isset($_POST['target_url']) ? sanitize_text_field(wp_unslash($_POST['target_url'])) : '';
        $redirect_type = isset($_POST['redirect_type']) ? intval($_POST['redirect_type']) : 301;
        $pattern_type = isset($_POST['pattern_type']) ? sanitize_text_field(wp_unslash($_POST['pattern_type'])) : 'exact';
        $priority = isset($_POST['priority']) ? sanitize_text_field(wp_unslash($_POST['priority'])) : 'normal';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($source_url) || empty($target_url) || !$redirect_id) {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&action=edit&id=' . $redirect_id . '&error=empty_fields'));
            exit;
        }

        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            array(
                'source_url' => $source_url,
                'target_url' => $target_url,
                'redirect_type' => $redirect_type,
                'pattern_type' => $pattern_type,
                'priority' => $priority,
                'is_active' => $is_active
            ),
            array('id' => $redirect_id),
            array('%s', '%s', '%d', '%s', '%s', '%d'),
            array('%d')
        );

        if ($result === false) {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&error=db_error'));
        } else {
            wp_redirect(admin_url('admin.php?page=jolix-seo-redirects&success=updated'));
        }
        exit;
    }

    /**
     * AJAX test redirect functionality
     */
    public function ajax_test_redirect()
    {
        // Verify nonce for AJAX request
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'] ?? '')), 'jolix_seo_test_redirect_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        $source_url = isset($_POST['source_url']) ? sanitize_text_field(wp_unslash($_POST['source_url'])) : '';
        $target_url = isset($_POST['target_url']) ? sanitize_text_field(wp_unslash($_POST['target_url'])) : '';
        $pattern_type = isset($_POST['pattern_type']) ? sanitize_text_field(wp_unslash($_POST['pattern_type'])) : 'exact';
        $test_url = isset($_POST['test_url']) ? sanitize_text_field(wp_unslash($_POST['test_url'])) : '';

        $matches = $this->url_matches_pattern($test_url, $source_url, $pattern_type);
        
        wp_send_json_success(array(
            'matches' => $matches,
            'processed_target' => $matches ? $this->process_target_url((object) array(
                'source_url' => $source_url,
                'target_url' => $target_url,
                'pattern_type' => $pattern_type
            ), $test_url) : null
        ));
    }
}