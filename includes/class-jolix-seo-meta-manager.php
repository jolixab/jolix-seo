<?php
/**
 * Main plugin class
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JolixSEOMetaManager {
    
    private $meta_box;
    private $settings;
    private $sitemap;
    private $woocommerce;
    private $redirects;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Initialize components
        $this->meta_box = new JolixSEOMetaBox();
        $this->settings = new JolixSEOSettings();
        $this->sitemap = new JolixSEOSitemap();
        $this->woocommerce = new JolixSEOWooCommerce();
        $this->redirects = new JolixSEORedirects();
        
        // Add meta tags to head
        add_action('wp_head', array($this, 'add_meta_tags'));
    }
    
    public function add_meta_tags() {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        
        // Get meta values
        $meta_title = get_post_meta($post->ID, '_seo_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_seo_meta_description', true);
        $og_image = get_post_meta($post->ID, '_seo_og_image', true);
        
        // Use defaults if not set
        if (empty($meta_title)) {
            $meta_title = get_the_title($post->ID);
            // Apply WooCommerce filter if available
            $meta_title = apply_filters('jolix_seo_meta_title', $meta_title, $post->ID);
        }
        
        if (empty($meta_description)) {
            $meta_description = get_the_excerpt($post->ID);
            if (empty($meta_description)) {
                $meta_description = wp_trim_words(wp_strip_all_tags($post->post_content), 25, '...');
            }
            // Apply WooCommerce filter if available
            $meta_description = apply_filters('jolix_seo_meta_description', $meta_description, $post->ID);
        }
        
        // Apply global suffixes
        $meta_title = $this->apply_title_suffix($meta_title);
        $meta_description = $this->apply_description_suffix($meta_description);
        
        // Use featured image if no custom OG image
        if (empty($og_image) && has_post_thumbnail($post->ID)) {
            $og_image = get_the_post_thumbnail_url($post->ID, 'large');
        }
        
        // Output meta tags
        echo "\n<!-- Jolix SEO Meta Manager -->\n";
        
        // Basic SEO meta tags
        if ($meta_title) {
            echo '<meta name="title" content="' . esc_attr($meta_title) . '">' . "\n";
        }
        
        if ($meta_description) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
        
        // Open Graph meta tags
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post->ID)) . '">' . "\n";
        
        if ($meta_title) {
            echo '<meta property="og:title" content="' . esc_attr($meta_title) . '">' . "\n";
        }
        
        if ($meta_description) {
            echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
        
        if ($og_image) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        }
        
        // Twitter Card meta tags
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        
        if ($meta_title) {
            echo '<meta name="twitter:title" content="' . esc_attr($meta_title) . '">' . "\n";
        }
        
        if ($meta_description) {
            echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
        
        if ($og_image) {
            echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
        }
        
        echo "<!-- /Jolix SEO Meta Manager -->\n\n";
    }
    
    private function apply_title_suffix($title) {
        $suffix = get_option('jolix_seo_title_suffix', '');
        $truncate = get_option('jolix_seo_title_truncate', 0);
        
        if (empty($suffix)) {
            return $title;
        }
        
        $full_title = $title . ' ' . $suffix;
        
        if ($truncate && strlen($full_title) > 60) {
            // Calculate how much space we have for the title
            $available_space = 60 - strlen(' ' . $suffix);
            if ($available_space > 0) {
                $title = substr($title, 0, $available_space);
                $full_title = $title . ' ' . $suffix;
            }
        }
        
        return $full_title;
    }
    
    private function apply_description_suffix($description) {
        $suffix = get_option('jolix_seo_description_suffix', '');
        $truncate = get_option('jolix_seo_description_truncate', 0);
        
        if (empty($suffix)) {
            return $description;
        }
        
        $full_description = $description . ' ' . $suffix;
        
        if ($truncate && strlen($full_description) > 160) {
            // Calculate how much space we have for the description
            $available_space = 160 - strlen(' ' . $suffix);
            if ($available_space > 0) {
                $description = substr($description, 0, $available_space);
                $full_description = $description . ' ' . $suffix;
            }
        }
        
        return $full_description;
    }
}