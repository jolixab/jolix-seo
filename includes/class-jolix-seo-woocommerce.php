<?php
/**
 * WooCommerce integration
 * 
 * @package JolixSEO
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JolixSEOWooCommerce {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init_woocommerce'));
    }
    
    public function init_woocommerce() {
        // Check if WooCommerce is active
        if (class_exists('WooCommerce')) {
            // Add product-specific meta fields
            add_action('woocommerce_product_options_general_product_data', array($this, 'add_woocommerce_meta_fields'));
            add_action('woocommerce_process_product_meta', array($this, 'save_woocommerce_meta_fields'));
            
            // Modify meta tags for products
            add_filter('jolix_seo_meta_title', array($this, 'modify_product_title'), 10, 2);
            add_filter('jolix_seo_meta_description', array($this, 'modify_product_description'), 10, 2);
        }
    }
    
    public function add_woocommerce_meta_fields() {
        global $post;
        
        $product_title = get_post_meta($post->ID, '_seo_product_title', true);
        $product_description = get_post_meta($post->ID, '_seo_product_description', true);
        
        echo '<div class="options_group">';
        
        woocommerce_wp_text_input(array(
            'id' => '_seo_product_title',
            'label' => 'SEO Product Title',
            'placeholder' => 'Custom SEO title for this product',
            'desc_tip' => 'true',
            'description' => 'Override the default product title for SEO purposes.',
            'value' => $product_title
        ));
        
        woocommerce_wp_textarea_input(array(
            'id' => '_seo_product_description',
            'label' => 'SEO Product Description',
            'placeholder' => 'Custom SEO description for this product',
            'desc_tip' => 'true',
            'description' => 'Override the default product description for SEO purposes.',
            'value' => $product_description,
            'rows' => 3
        ));
        
        echo '</div>';
    }
    
    public function save_woocommerce_meta_fields($post_id) {
        // Check if user has permission to edit this post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // WooCommerce handles its own nonce verification, but we add extra security
        if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce_meta_nonce'])), 'woocommerce_save_data')) {
            return;
        }
        
        if (isset($_POST['_seo_product_title'])) {
            update_post_meta($post_id, '_seo_product_title', sanitize_text_field(wp_unslash($_POST['_seo_product_title'])));
        }
        
        if (isset($_POST['_seo_product_description'])) {
            update_post_meta($post_id, '_seo_product_description', sanitize_textarea_field(wp_unslash($_POST['_seo_product_description'])));
        }
    }
    
    public function modify_product_title($title, $post_id) {
        if (get_post_type($post_id) === 'product') {
            $product_title = get_post_meta($post_id, '_seo_product_title', true);
            if (!empty($product_title)) {
                return $product_title;
            }
            
            // Add product price to title if available
            if (function_exists('wc_get_product')) {
                $product = wc_get_product($post_id);
                if ($product && $product->get_price()) {
                    $price = wc_price($product->get_price());
                    $title .= ' - ' . wp_strip_all_tags($price);
                }
            }
        }
        
        return $title;
    }
    
    public function modify_product_description($description, $post_id) {
        if (get_post_type($post_id) === 'product') {
            $product_description = get_post_meta($post_id, '_seo_product_description', true);
            if (!empty($product_description)) {
                return $product_description;
            }
            
            // Use product short description as fallback
            if (function_exists('wc_get_product')) {
                $product = wc_get_product($post_id);
                if ($product && $product->get_short_description()) {
                    return wp_trim_words(wp_strip_all_tags($product->get_short_description()), 25, '...');
                }
            }
        }
        
        return $description;
    }
}