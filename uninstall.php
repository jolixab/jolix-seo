<?php
/**
 * Uninstall script for Jolix SEO Meta Manager
 * 
 * This file is executed when the plugin is deleted from WordPress admin.
 * It cleans up all plugin data from the database.
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('jolix_seo_title_suffix');
delete_option('jolix_seo_title_truncate');
delete_option('jolix_seo_description_suffix');
delete_option('jolix_seo_description_truncate');

// Remove all post meta created by the plugin
global $wpdb;

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_seo_meta_title'
    )
);

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_seo_meta_description'
    )
);

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_seo_og_image'
    )
);

// Clear any cached data
wp_cache_flush();