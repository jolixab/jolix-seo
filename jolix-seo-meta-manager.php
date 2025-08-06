<?php
/**
 * Plugin Name: Jolix SEO Meta Manager
 * Plugin URI: https://jolix.se/en/jolix-seo-meta-manager
 * Description: A simple SEO plugin to manage meta titles, descriptions, and Open Graph fields.
 * Version: 1.0.0
 * Author: Fredrik Gustavsson, Jolix AB
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Prevent multiple inclusions
if (defined('JOLIX_SEO_VERSION')) {
    return;
}

// Define plugin constants
define('JOLIX_SEO_VERSION', '1.0.0');
define('JOLIX_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JOLIX_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files only if classes don't exist
if (!class_exists('JolixSEOMetaManager')) {
    require_once JOLIX_SEO_PLUGIN_DIR . 'includes/class-jolix-seo-meta-manager.php';
}
if (!class_exists('JolixSEOMetaBox')) {
    require_once JOLIX_SEO_PLUGIN_DIR . 'includes/class-jolix-seo-meta-box.php';
}
if (!class_exists('JolixSEOSettings')) {
    require_once JOLIX_SEO_PLUGIN_DIR . 'includes/class-jolix-seo-settings.php';
}
if (!class_exists('JolixSEOSitemap')) {
    require_once JOLIX_SEO_PLUGIN_DIR . 'includes/class-jolix-seo-sitemap.php';
}
if (!class_exists('JolixSEOWooCommerce')) {
    require_once JOLIX_SEO_PLUGIN_DIR . 'includes/class-jolix-seo-woocommerce.php';
}

// Initialize the plugin
function jolix_seo_init() {
    if (!class_exists('JolixSEOMetaManager')) {
        return;
    }
    new JolixSEOMetaManager();
}
add_action('plugins_loaded', 'jolix_seo_init');

// Activation hook
register_activation_hook(__FILE__, 'jolix_seo_activation');

function jolix_seo_activation() {
    // Flush rewrite rules to ensure sitemap works
    flush_rewrite_rules();
    
    // Set default options
    add_option('jolix_seo_enable_sitemap', 1);
    add_option('jolix_seo_sitemap_post_types', array('post', 'page'));
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'jolix_seo_deactivation');

function jolix_seo_deactivation() {
    // Flush rewrite rules
    flush_rewrite_rules();
}