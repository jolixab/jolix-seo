<?php

/**
 * XML Sitemap functionality
 * 
 * @package JolixSEOMetaManager
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
        header('Content-Type: application/xml; charset=utf-8');

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
