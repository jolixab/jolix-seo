<?php

/**
 * Meta Box functionality
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JolixSEOMetaBox
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_meta_boxes()
    {
        $post_types = get_post_types(array('public' => true), 'names');

        // Ensure WooCommerce products are included
        if (class_exists('WooCommerce')) {
            $post_types[] = 'product';
        }

        foreach ($post_types as $post_type) {
            add_meta_box(
                'jolix-seo-meta-manager',
                'Jolix SEO Meta Manager',
                array($this, 'meta_box_callback'),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    public function meta_box_callback($post)
    {
        // Add nonce for security
        wp_nonce_field('seo_meta_manager_nonce', 'seo_meta_manager_nonce');

        // Get existing values
        $meta_title = get_post_meta($post->ID, '_seo_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_seo_meta_description', true);
        $og_image = get_post_meta($post->ID, '_seo_og_image', true);

        // Default values
        if (empty($meta_title)) $meta_title = get_the_title($post->ID);
        if (empty($meta_description)) {
            // For WooCommerce products, try to get short description first
            if ($post->post_type === 'product' && function_exists('wc_get_product')) {
                $product = wc_get_product($post->ID);
                if ($product && $product->get_short_description()) {
                    $meta_description = wp_trim_words(wp_strip_all_tags($product->get_short_description()), 20, '...');
                }
            }
            // Fallback to excerpt or content
            if (empty($meta_description)) {
                $meta_description = get_the_excerpt($post->ID);
                if (empty($meta_description)) {
                    $meta_description = wp_trim_words(wp_strip_all_tags($post->post_content), 20, '...');
                }
            }
        }

        // Get featured image as default OG image
        $featured_image = '';
        if (has_post_thumbnail($post->ID)) {
            $featured_image = get_the_post_thumbnail_url($post->ID, 'large');
        } elseif ($post->post_type === 'product' && function_exists('wc_get_product')) {
            // For WooCommerce products, try to get product gallery images
            $product = wc_get_product($post->ID);
            if ($product) {
                $gallery_images = $product->get_gallery_image_ids();
                if (!empty($gallery_images)) {
                    $featured_image = wp_get_attachment_url($gallery_images[0]);
                }
            }
        }

        // Include the meta box template
        include JOLIX_SEO_PLUGIN_DIR . 'templates/meta-box.php';
    }

    public function enqueue_admin_assets($hook)
    {
        // Only load on post edit screens
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();
        wp_enqueue_script('jquery');
    }

    public function save_meta_data($post_id)
    {
        // Check if nonce is valid
        if (!isset($_POST['seo_meta_manager_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['seo_meta_manager_nonce'])), 'seo_meta_manager_nonce')) {
            return;
        }

        // Check if user has permission
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Don't save on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Save meta fields
        if (isset($_POST['seo_meta_title'])) {
            update_post_meta($post_id, '_seo_meta_title', sanitize_text_field(wp_unslash($_POST['seo_meta_title'])));
        }

        if (isset($_POST['seo_meta_description'])) {
            update_post_meta($post_id, '_seo_meta_description', sanitize_textarea_field(wp_unslash($_POST['seo_meta_description'])));
        }

        // Handle OG image based on option selected
        if (isset($_POST['og_image_option'])) {
            if ($_POST['og_image_option'] === 'custom' && isset($_POST['seo_og_image'])) {
                update_post_meta($post_id, '_seo_og_image', esc_url_raw(wp_unslash($_POST['seo_og_image'])));
            } else {
                // Clear custom image if using featured image
                delete_post_meta($post_id, '_seo_og_image');
            }
        }

        // Handle sitemap exclusion
        if (isset($_POST['seo_exclude_from_sitemap'])) {
            update_post_meta($post_id, '_seo_exclude_from_sitemap', 1);
        } else {
            delete_post_meta($post_id, '_seo_exclude_from_sitemap');
        }
    }
}
