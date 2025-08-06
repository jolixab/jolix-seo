<?php

/**
 * Settings page functionality
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JolixSEOSettings
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_flush_rewrite'));
    }

    public function handle_flush_rewrite()
    {
        if (isset($_GET['flush_rewrite']) && sanitize_text_field($_GET['flush_rewrite']) === '1' && current_user_can('manage_options')) {
            // Add the rewrite rule
            add_rewrite_rule('^sitemap\.xml$', 'index.php?jolix_sitemap=1', 'top');
            // Flush rewrite rules
            flush_rewrite_rules();

            // Also try to generate a physical sitemap file as backup
            $this->generate_physical_sitemap();

            // Show success message
            add_action('admin_notices', array($this, 'flush_success_notice'));
        }
    }

    public function generate_physical_sitemap()
    {
        if (!get_option('jolix_seo_enable_sitemap', 1)) {
            return;
        }

        // Get sitemap content
        ob_start();

        $post_types = get_option('jolix_seo_sitemap_post_types', array('post', 'page'));

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        echo '<url>' . "\n";
        echo '<loc>' . esc_url(home_url('/')) . '</loc>' . "\n";
        echo '<lastmod>' . date('Y-m-d\TH:i:s+00:00') . '</lastmod>' . "\n";
        echo '<changefreq>daily</changefreq>' . "\n";
        echo '<priority>1.0</priority>' . "\n";
        echo '</url>' . "\n";

        // Posts and Pages
        foreach ($post_types as $post_type) {
            $posts = get_posts(array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby' => 'modified',
                'order' => 'DESC',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_seo_exclude_from_sitemap',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => '_seo_exclude_from_sitemap',
                        'value' => '1',
                        'compare' => '!='
                    )
                )
            ));

            foreach ($posts as $post) {
                $exclude_from_sitemap = get_post_meta($post->ID, '_seo_exclude_from_sitemap', true);
                if ($exclude_from_sitemap) {
                    continue;
                }

                echo '<url>' . "\n";
                echo '<loc>' . esc_url(get_permalink($post->ID)) . '</loc>' . "\n";
                echo '<lastmod>' . date('Y-m-d\TH:i:s+00:00', strtotime($post->post_modified)) . '</lastmod>' . "\n";

                if ($post_type === 'page') {
                    echo '<changefreq>monthly</changefreq>' . "\n";
                    echo '<priority>0.8</priority>' . "\n";
                } elseif ($post_type === 'product') {
                    echo '<changefreq>weekly</changefreq>' . "\n";
                    echo '<priority>0.7</priority>' . "\n";
                } else {
                    echo '<changefreq>weekly</changefreq>' . "\n";
                    echo '<priority>0.6</priority>' . "\n";
                }

                echo '</url>' . "\n";
            }
        }

        echo '</urlset>';

        $sitemap_content = ob_get_clean();

        // Try to write physical sitemap file
        $upload_dir = wp_upload_dir();
        $sitemap_file = $upload_dir['basedir'] . '/sitemap.xml';

        if (is_writable($upload_dir['basedir'])) {
            file_put_contents($sitemap_file, $sitemap_content);
        }

        // Also try to write to WordPress root if possible
        $root_sitemap = ABSPATH . 'sitemap.xml';
        if (is_writable(ABSPATH)) {
            file_put_contents($root_sitemap, $sitemap_content);
        }
    }

    public function flush_success_notice()
    {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>Sitemap URL refreshed! Try accessing your <a href="' . esc_url(home_url('/sitemap.xml')) . '" target="_blank">sitemap</a> now.</p>';
        echo '</div>';
    }

    public function add_admin_menu()
    {
        add_options_page(
            'Jolix SEO Settings',
            'Jolix SEO',
            'manage_options',
            'jolix-seo-settings',
            array($this, 'settings_page')
        );
    }

    public function register_settings()
    {
        register_setting('jolix_seo_settings', 'jolix_seo_title_suffix', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('jolix_seo_settings', 'jolix_seo_title_truncate', array(
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
        register_setting('jolix_seo_settings', 'jolix_seo_description_suffix', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('jolix_seo_settings', 'jolix_seo_description_truncate', array(
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
        register_setting('jolix_seo_settings', 'jolix_seo_enable_sitemap', array(
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
        register_setting('jolix_seo_settings', 'jolix_seo_sitemap_post_types', array(
            'sanitize_callback' => array($this, 'sanitize_post_types')
        ));
    }

    public function sanitize_checkbox($input)
    {
        return $input ? 1 : 0;
    }

    public function sanitize_post_types($input)
    {
        if (!is_array($input)) {
            return array();
        }
        return array_map('sanitize_text_field', $input);
    }

    public function settings_page()
    {
        // Include the settings template
        include JOLIX_SEO_PLUGIN_DIR . 'templates/settings-page.php';
    }
}
