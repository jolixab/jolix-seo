<?php

/**
 * XML Sitemap functionality
 * 
 * @package JolixSEO
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JolixSEOSitemap
{

    public function __construct()
    {
        add_action('init', array($this, 'init_sitemap'));
        
        // Early hook to catch sitemap requests before WordPress fully loads
        add_action('wp_loaded', array($this, 'early_sitemap_check'));
        
        // Generate physical sitemap when content changes
        add_action('save_post', array($this, 'generate_physical_sitemap'));
        add_action('delete_post', array($this, 'generate_physical_sitemap'));
        add_action('wp_trash_post', array($this, 'generate_physical_sitemap'));
        add_action('untrash_post', array($this, 'generate_physical_sitemap'));
    }

    public function init_sitemap()
    {
        // Add rewrite rule for sitemap
        add_rewrite_rule('^sitemap\.xml$', 'index.php?jolix_sitemap=1', 'top');

        // Handle sitemap query
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_sitemap_request'));

        // Handle sitemap via parse_request as primary method
        add_action('parse_request', array($this, 'parse_sitemap_request'));
    }

    public function parse_sitemap_request($wp)
    {
        // Check if this is a sitemap request
        if (isset($wp->request) && $wp->request === 'sitemap.xml' && get_option('jolix_seo_enable_sitemap', 1)) {
            $this->generate_sitemap();
            exit;
        }

        // Also check for direct URL patterns
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));

            // Handle different possible sitemap URLs
            if (in_array($request_uri, array('/sitemap.xml', '/mwp/sitemap.xml')) && get_option('jolix_seo_enable_sitemap', 1)) {
                $this->generate_sitemap();
                exit;
            }
        }
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'jolix_sitemap';
        return $vars;
    }

    public function handle_sitemap_request()
    {
        if (get_query_var('jolix_sitemap') && get_option('jolix_seo_enable_sitemap', 1)) {
            $this->generate_sitemap();
            exit;
        }
    }

    public function generate_sitemap()
    {
        // Set headers for better Google compatibility
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        
        // Prevent any output before sitemap
        if (ob_get_length()) {
            ob_clean();
        }

        $this->output_sitemap_content();
    }
    
    /**
     * Early sitemap check for better Google compatibility
     */
    public function early_sitemap_check()
    {
        if (!get_option('jolix_seo_enable_sitemap', 1)) {
            return;
        }
        
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        
        // Check various sitemap URL patterns
        if (preg_match('/\/sitemap\.xml(\?.*)?$/', $request_uri)) {
            $this->generate_sitemap();
            exit;
        }
    }
    
    /**
     * Generate a physical sitemap.xml file
     */
    public function generate_physical_sitemap()
    {
        if (!get_option('jolix_seo_enable_sitemap', 1)) {
            return;
        }
        
        // Get the sitemap content
        ob_start();
        $this->output_sitemap_content();
        $sitemap_content = ob_get_clean();
        
        // Write to physical file
        $upload_dir = wp_upload_dir();
        $sitemap_path = trailingslashit($upload_dir['basedir']) . 'sitemap.xml';
        
        // Also create in root directory if writable
        $root_sitemap = ABSPATH . 'sitemap.xml';
        
        // Write to uploads directory
        file_put_contents($sitemap_path, $sitemap_content);
        
        // Try to write to root (may fail on some hosts)
        if (is_writable(ABSPATH)) {
            file_put_contents($root_sitemap, $sitemap_content);
        }
    }
    
    /**
     * Output sitemap content without headers
     */
    private function output_sitemap_content()
    {
        $post_types = get_option('jolix_seo_sitemap_post_types', array('post', 'page'));

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        echo '<url>' . "\n";
        echo '<loc>' . esc_url(home_url('/')) . '</loc>' . "\n";
        echo '<lastmod>' . esc_html(gmdate('Y-m-d\TH:i:s+00:00')) . '</lastmod>' . "\n";
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
                // Double check exclusion (belt and suspenders approach)
                $exclude_from_sitemap = get_post_meta($post->ID, '_seo_exclude_from_sitemap', true);
                if ($exclude_from_sitemap) {
                    continue;
                }
                
                // Skip homepage to avoid duplicate entries
                if (get_permalink($post->ID) === home_url('/')) {
                    continue;
                }

                echo '<url>' . "\n";
                echo '<loc>' . esc_url(get_permalink($post->ID)) . '</loc>' . "\n";
                echo '<lastmod>' . esc_html(gmdate('Y-m-d\TH:i:s+00:00', strtotime($post->post_modified))) . '</lastmod>' . "\n";

                // Set priority and change frequency based on post type
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
    }
}
