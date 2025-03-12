<?php
/**
 * WooCommerce Integration for Emargy Elements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle WooCommerce integration
 */
class Emargy_WooCommerce_Integration {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'check_woocommerce'));
        add_filter('emargy_content_types', array($this, 'add_product_content_type'));
        add_filter('emargy_post_item_content', array($this, 'modify_product_content'), 10, 2);
        add_filter('emargy_post_item_css_class', array($this, 'add_product_css_class'), 10, 2);
        add_action('wp_ajax_emargy_add_to_cart', array($this, 'add_to_cart_ajax'));
        add_action('wp_ajax_nopriv_emargy_add_to_cart', array($this, 'add_to_cart_ajax'));
    }

    /**
     * Check if WooCommerce is active
     */
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // WooCommerce is active, register additional hooks
        add_action('emargy_woocommerce_loaded', array($this, 'woocommerce_loaded'));
        
        // Fire the action to let other components know WooCommerce is loaded
        do_action('emargy_woocommerce_loaded');
    }
    
    /**
     * Add 'product' to content types dropdown
     *
     * @param array $content_types Array of content types
     * @return array Modified array of content types
     */
    public function add_product_content_type($content_types) {
        if (class_exists('WooCommerce')) {
            $content_types['product'] = esc_html__('WooCommerce Products', 'emargy-elements');
        }
        return $content_types;
    }
    
    /**
     * Modify product content in timeline
     *
     * @param string $content The post content
     * @param int $post_id The post ID
     * @return string The modified content
     */
    public function modify_product_content($content, $post_id) {
        if (get_post_type($post_id) !== 'product') {
            return $content;
        }
        
        $product = wc_get_product($post_id);
        if (!$product) {
            return $content;
        }
        
        // Start product container
        $product_content = '<div class="emargy-product-container">';
        
        // Add price
        $product_content .= '<div class="emargy-product-price">';
        $product_content .= $product->get_price_html();
        $product_content .= '</div>';
        
        // Add rating
        if (wc_review_ratings_enabled()) {
            $rating_count = $product->get_rating_count();
            if ($rating_count > 0) {
                $product_content .= '<div class="emargy-product-rating">';
                $product_content .= wc_get_rating_html($product->get_average_rating(), $rating_count);
                $product_content .= '</div>';
            }
        }
        
        // Add stock status
        $product_content .= '<div class="emargy-product-stock">';
        if ($product->is_in_stock()) {
            $product_content .= '<span class="emargy-in-stock">' . esc_html__('In stock', 'emargy-elements') . '</span>';
        } else {
            $product_content .= '<span class="emargy-out-of-stock">' . esc_html__('Out of stock', 'emargy-elements') . '</span>';
        }
        $product_content .= '</div>';
        
        // Add excerpt
        $product_content .= '<div class="emargy-product-excerpt">';
        $product_content .= wp_trim_words($product->get_short_description(), 20);
        $product_content .= '</div>';
        
        // Add "Add to Cart" button
        if ($product->is_in_stock() && $product->is_purchasable()) {
            $product_content .= '<div class="emargy-product-actions">';
            $product_content .= '<a href="#" class="emargy-add-to-cart" data-product-id="' . esc_attr($post_id) . '">' . esc_html__('Add to Cart', 'emargy-elements') . '</a>';
            $product_content .= '<a href="' . esc_url($product->get_permalink()) . '" class="emargy-view-product">' . esc_html__('View Product', 'emargy-elements') . '</a>';
            $product_content .= '</div>';
        } else {
            $product_content .= '<div class="emargy-product-actions">';
            $product_content .= '<a href="' . esc_url($product->get_permalink()) . '" class="emargy-view-product">' . esc_html__('View Product', 'emargy-elements') . '</a>';
            $product_content .= '</div>';
        }
        
        // End product container
        $product_content .= '</div>';
        
        // Add JavaScript for "Add to Cart" functionality
        $product_content .= $this->get_add_to_cart_script();
        
        // Add product styles
        $product_content .= $this->get_product_styles();
        
        return $content . $product_content;
    }
    
    /**
     * Add product-specific CSS classes
     *
     * @param string $classes CSS classes
     * @param int $post_id The post ID
     * @return string Modified CSS classes
     */
    public function add_product_css_class($classes, $post_id) {
        if (get_post_type($post_id) === 'product') {
            $classes .= ' emargy-timeline-product';
            
            $product = wc_get_product($post_id);
            if ($product) {
                if ($product->is_on_sale()) {
                    $classes .= ' emargy-product-on-sale';
                }
                
                if (!$product->is_in_stock()) {
                    $classes .= ' emargy-product-out-of-stock';
                }
            }
        }
        
        return $classes;
    }
    
    /**
     * Add to cart AJAX handler
     */
    public function add_to_cart_ajax() {
        // Verify nonce
        if (!check_ajax_referer('emargy_woocommerce_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => esc_html__('Security check failed', 'emargy-elements')));
            return;
        }
        
        // Get product ID
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        
        if (!$product_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid product ID', 'emargy-elements')));
            return;
        }
        
        // Get product
        $product = wc_get_product($product_id);
        
        if (!$product) {
            wp_send_json_error(array('message' => esc_html__('Product not found', 'emargy-elements')));
            return;
        }
        
        // Check if product is purchasable
        if (!$product->is_purchasable()) {
            wp_send_json_error(array('message' => esc_html__('This product cannot be purchased', 'emargy-elements')));
            return;
        }
        
        // Check if product is in stock
        if (!$product->is_in_stock()) {
            wp_send_json_error(array('message' => esc_html__('This product is out of stock', 'emargy-elements')));
            return;
        }
        
        // Get quantity
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
        
        // Add to cart
        $added = WC()->cart->add_to_cart($product_id, $quantity);
        
        if ($added) {
            // Get mini cart HTML
            $fragments = array(
                'message' => esc_html__('Product added to cart!', 'emargy-elements'),
                'product_name' => $product->get_name(),
                'cart_url' => wc_get_cart_url(),
                'cart_count' => WC()->cart->get_cart_contents_count()
            );
            
            wp_send_json_success($fragments);
        } else {
            wp_send_json_error(array('message' => esc_html__('Could not add the product to the cart', 'emargy-elements')));
        }
    }
    
    /**
     * Get JavaScript for "Add to Cart" functionality
     * 
     * @return string JavaScript code
     */
    private function get_add_to_cart_script() {
        ob_start();
        ?>
        <script>
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                $(document).on('click', '.emargy-add-to-cart', function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var productId = $button.data('product-id');
                    
                    if (!productId) {
                        return;
                    }
                    
                    $button.addClass('loading');
                    
                    $.ajax({
                        url: emargyTimelineVars.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'emargy_add_to_cart',
                            product_id: productId,
                            quantity: 1,
                            nonce: emargyTimelineVars.wc_nonce
                        },
                        success: function(response) {
                            $button.removeClass('loading');
                            
                            if (response.success) {
                                $button.addClass('added');
                                
                                // Show success message
                                var $message = $('<div class="emargy-cart-message"></div>')
                                    .text(response.data.message)
                                    .appendTo('body')
                                    .fadeIn(300);
                                
                                // Update cart count if WooCommerce has cart fragments
                                if (typeof wc_add_to_cart_params !== 'undefined') {
                                    $('.cart-contents-count').text(response.data.cart_count);
                                }
                                
                                // Remove message after 2 seconds
                                setTimeout(function() {
                                    $message.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                }, 2000);
                            } else {
                                alert(response.data.message);
                            }
                        },
                        error: function() {
                            $button.removeClass('loading');
                            alert('Error occurred. Please try again.');
                        }
                    });
                });
            });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get product styles
     * 
     * @return string CSS styles
     */
    private function get_product_styles() {
        ob_start();
        ?>
        <style>
        .emargy-product-container {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .emargy-product-price {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #fff;
        }
        
        .emargy-product-rating {
            margin-bottom: 10px;
        }
        
        .emargy-product-stock {
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .emargy-in-stock {
            color: #7ad03a;
        }
        
        .emargy-out-of-stock {
            color: #a44;
        }
        
        .emargy-product-excerpt {
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .emargy-product-actions {
            display: flex;
            gap: 10px;
        }
        
        .emargy-add-to-cart,
        .emargy-view-product {
            display: inline-block;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.9);
            color: #e22d4b;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .emargy-add-to-cart:hover,
        .emargy-view-product:hover {
            background: #fff;
            color: #c42742;
        }
        
        .emargy-add-to-cart.loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .emargy-add-to-cart.added {
            background-color: #7ad03a;
            color: #fff;
        }
        
        .emargy-cart-message {
            position: fixed;
            top: 50px;
            right: 20px;
            background: #7ad03a;
            color: #fff;
            padding: 10px 15px;
            border-radius: 4px;
            z-index: 9999;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: none;
        }
        
        .rtl .emargy-cart-message {
            right: auto;
            left: 20px;
        }
        
        /* Responsive styles */
        @media screen and (max-width: 767px) {
            .emargy-product-price {
                font-size: 16px;
            }
            
            .emargy-product-actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .emargy-add-to-cart,
            .emargy-view-product {
                text-align: center;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * WooCommerce loaded callback
     */
    public function woocommerce_loaded() {
        // Make sure WooCommerce scripts are loaded
        add_action('wp_enqueue_scripts', array($this, 'enqueue_woocommerce_scripts'));
        
        // Add WooCommerce nonce to global vars
        add_filter('emargy_timeline_localize_vars', array($this, 'add_woocommerce_nonce'));
    }
    
    /**
     * Enqueue WooCommerce scripts
     */
    public function enqueue_woocommerce_scripts() {
        // Enqueue WooCommerce styles if not already enqueued
        if (!wp_style_is('woocommerce-general')) {
            wp_enqueue_style('woocommerce-general');
        }
    }
    
    /**
     * Add WooCommerce nonce to global vars
     * 
     * @param array $vars Localized script variables
     * @return array Modified variables
     */
    public function add_woocommerce_nonce($vars) {
        $vars['wc_nonce'] = wp_create_nonce('emargy_woocommerce_nonce');
        return $vars;
    }
}

// Initialize the class
new Emargy_WooCommerce_Integration();