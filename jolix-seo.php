<?php
/**
 * Plugin Name: Jolix SEO
 * Plugin URI: https://jolix.se/en/jolix-seo
 * Description: A simple SEO plugin to manage meta titles, descriptions, Open Graph fields, XML sitemaps, and redirects.
 * Version: 1.1.0
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
define('JOLIX_SEO_VERSION', '1.1.0');
define('JOLIX_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JOLIX_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files only if classes don't exist
if (!class_exists('JolixSEO')) {
    require_once JOLIX_SEO_PLUGIN_DIR . 'includes/class-jolix-seo.php';
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
if (!class_exists('JolixSEORedirects')) {
    require_once JOLIX_SEO_PLUGIN_DIR . 'includes/class-jolix-seo-redirects.php';
}

// Initialize the plugin
function jolix_seo_init() {
    if (!class_exists('JolixSEO')) {
        return;
    }
    new JolixSEO();
}
add_action('plugins_loaded', 'jolix_seo_init');

// Add plugin action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'jolix_seo_plugin_action_links');

function jolix_seo_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=jolix-seo-settings') . '">' . __('Settings', 'jolix-seo') . '</a>';
    $redirects_link = '<a href="' . admin_url('tools.php?page=jolix-seo-redirects') . '">' . __('Redirects', 'jolix-seo') . '</a>';
    
    array_unshift($links, $settings_link, $redirects_link);
    
    return $links;
}

// Activation hook
register_activation_hook(__FILE__, 'jolix_seo_activation');

function jolix_seo_activation() {
    // Create redirects table
    if (class_exists('JolixSEORedirects')) {
        $redirects = new JolixSEORedirects();
        $redirects->create_table();
    }
    
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