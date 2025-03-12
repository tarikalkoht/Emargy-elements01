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
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Add WooCommerce product to timeline content types
        add_filter('emargy_timeline_content_types', array($this, 'add_product_content_type'));
        
        // Add product data to timeline items
        add_filter('emargy_timeline_item_data', array($this, 'add_product_data'), 10, 2);
        
        // Add product template for timeline items
        add_filter('emargy_timeline_item_template', array($this, 'product_item_template'), 10, 2);
        
        // Add product modal template
        add_filter('emargy_modal_content', array($this, 'product_modal_content'), 10, 2);
        
        // Add to cart AJAX handler
        add_action('wp_ajax_emargy_add_to_cart', array($this, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_emargy_add_to_cart', array($this, 'ajax_add_to_cart'));
        
        // Enqueue WooCommerce scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_woocommerce_assets'));
        
        // Add WooCommerce settings
        add_action('emargy_timeline_settings', array($this, 'add_woocommerce_settings'));
    }

    /**
     * Add product to timeline content types
     *
     * @param array $content_types Current content types
     * @return array Modified content types
     */
    public function add_product_content_type($content_types) {
        $content_types['product'] = __('WooCommerce Products', 'emargy-elements');
        return $content_types;
    }

    /**
     * Add product data to timeline item
     *
     * @param array $item_data Item data
     * @param WP_Post $post Post object
     * @return array Modified item data
     */
    public function add_product_data($item_data, $post) {
        if ($post->post_type !== 'product') {
            return $item_data;
        }
        
        $product = wc_get_product($post->ID);
        
        if (!$product) {
            return $item_data;
        }
        
        // Add product data
        $item_data['product'] = array(
            'id' => $product->get_id(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'price_html' => $product->get_price_html(),
            'on_sale' => $product->is_on_sale(),
            'in_stock' => $product->is_in_stock(),
            'stock_quantity' => $product->get_stock_quantity(),
            'average_rating' => $product->get_average_rating(),
            'rating_count' => $product->get_rating_count(),
            'categories' => array(),
            'gallery_images' => array(),
            'purchasable' => $product->is_purchasable()
        );
        
        // Add categories
        $terms = get_the_terms($post->ID, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $item_data['product']['categories'][] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'url' => get_term_link($term)
                );
            }
        }
        
        // Add gallery images
        $attachment_ids = $product->get_gallery_image_ids();
        foreach ($attachment_ids as $attachment_id) {
            $item_data['product']['gallery_images'][] = array(
                'id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
                'thumbnail' => wp_get_attachment_image_url($attachment_id, 'thumbnail'),
                'medium' => wp_get_attachment_image_url($attachment_id, 'medium'),
                'large' => wp_get_attachment_image_url($attachment_id, 'large')
            );
        }
        
        return $item_data;
    }

    /**
     * Custom template for product items in timeline
     *
     * @param string $template Current template HTML
     * @param array $item Item data
     * @return string Modified template HTML
     */
    public function product_item_template($template, $item) {
        if (isset($item['post_type']) && $item['post_type'] === 'product' && isset($item['product'])) {
            ob_start();
            ?>
            <div class="emargy-timeline-product-item">
                <?php if ($item['product']['on_sale']) : ?>
                <span class="emargy-product-sale-badge"><?php esc_html_e('Sale!', 'emargy-elements'); ?></span>
                <?php endif; ?>
                
                <div class="emargy-timeline-thumbnail">
                    <?php if (!empty($item['thumbnail_url'])) : ?>
                    <img src="<?php echo esc_url($item['thumbnail_url']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                    <?php else : ?>
                    <div class="emargy-no-thumbnail"></div>
                    <?php endif; ?>
                    
                    <?php if (isset($item['video_url']) && !empty($item['video_url'])) : ?>
                    <div class="emargy-play-button">
                        <i class="eicon-play"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="emargy-timeline-content">
                    <h4 class="emargy-timeline-title"><?php echo esc_html($item['title']); ?></h4>
                    
                    <div class="emargy-product-price">
                        <?php echo wp_kses_post($item['product']['price_html']); ?>
                    </div>
                    
                    <?php if ($item['product']['in_stock'] && $item['product']['purchasable']) : ?>
                    <div class="emargy-stock-status in-stock">
                        <?php esc_html_e('In stock', 'emargy-elements'); ?>
                    </div>
                    <?php else : ?>
                    <div class="emargy-stock-status out-of-stock">
                        <?php esc_html_e('Out of stock', 'emargy-elements'); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        return $template;
    }

    /**
     * Custom modal content for products
     *
     * @param string $content Current modal content
     * @param array $item Item data
     * @return string Modified modal content
     */
    public function product_modal_content($content, $item) {
        if (isset($item['post_type']) && $item['post_type'] === 'product' && isset($item['product'])) {
            ob_start();
            ?>
            <div class="emargy-product-modal">
                <h2 class="emargy-modal-title"><?php echo esc_html($item['title']); ?></h2>
                
                <div class="emargy-product-modal-content">
                    <div class="emargy-product-images">
                        <?php if (!empty($item['thumbnail_url'])) : ?>
                        <div class="emargy-product-main-image">
                            <img src="<?php echo esc_url($item['thumbnail_url']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['product']['gallery_images'])) : ?>
                        <div class="emargy-product-gallery">
                            <?php foreach ($item['product']['gallery_images'] as $image) : ?>
                            <div class="emargy-product-gallery-item">
                                <img src="<?php echo esc_url($image['thumbnail']); ?>" data-full="<?php echo esc_url($image['large']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="emargy-product-info">
                        <div class="emargy-product-price">
                            <?php echo wp_kses_post($item['product']['price_html']); ?>
                        </div>
                        
                        <?php if ($item['product']['average_rating'] > 0) : ?>
                        <div class="emargy-product-rating">
                            <?php
                            $rating = $item['product']['average_rating'];
                            echo wc_get_rating_html($rating, $item['product']['rating_count']);
                            ?>
                            <span class="emargy-rating-count">(<?php echo esc_html($item['product']['rating_count']); ?>)</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="emargy-product-excerpt">
                            <?php echo wp_kses_post($item['excerpt']); ?>
                        </div>
                        
                        <?php if ($item['product']['in_stock'] && $item['product']['purchasable']) : ?>
                        <div class="emargy-product-actions">
                            <div class="emargy-product-quantity">
                                <label for="emargy-quantity-<?php echo esc_attr($item['id']); ?>"><?php esc_html_e('Quantity', 'emargy-elements'); ?></label>
                                <input type="number" id="emargy-quantity-<?php echo esc_attr($item['id']); ?>" class="emargy-quantity" value="1" min="1" max="<?php echo esc_attr($item['product']['stock_quantity'] ? $item['product']['stock_quantity'] : ''); ?>">
                            </div>
                            <button class="emargy-add-to-cart-btn" data-product-id="<?php echo esc_attr($item['id']); ?>"><?php esc_html_e('Add to Cart', 'emargy-elements'); ?></button>
                            <a href="<?php echo esc_url($item['permalink']); ?>" class="emargy-view-product-btn"><?php esc_html_e('View Product', 'emargy-elements'); ?></a>
                        </div>
                        <div class="emargy-cart-message"></div>
                        <?php else : ?>
                        <div class="emargy-product-actions">
                            <a href="<?php echo esc_url($item['permalink']); ?>" class="emargy-view-product-btn"><?php esc_html_e('View Product', 'emargy-elements'); ?></a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['product']['categories'])) : ?>
                        <div class="emargy-product-categories">
                            <span class="emargy-product-categories-label"><?php esc_html_e('Categories:', 'emargy-elements'); ?></span>
                            <?php 
                            $category_links = array();
                            foreach ($item['product']['categories'] as $category) {
                                $category_links[] = '<a href="' . esc_url($category['url']) . '">' . esc_html($category['name']) . '</a>';
                            }
                            echo implode(', ', $category_links);
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <script>
            (function($) {
                'use strict';
                
                $(document).ready(function() {
                    // Gallery images click
                    $('.emargy-product-gallery-item img').on('click', function() {
                        var fullUrl = $(this).data('full');
                        $('.emargy-product-main-image img').attr('src', fullUrl);
                    });
                    
                    // Add to cart
                    $('.emargy-add-to-cart-btn').on('click', function() {
                        var $button = $(this);
                        var productId = $button.data('product-id');
                        var quantity = $('#emargy-quantity-' + productId).val();
                        
                        if (!productId) {
                            return;
                        }
                        
                        $button.addClass('loading').prop('disabled', true);
                        
                        $.ajax({
                            url: emargyTimelineVars.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'emargy_add_to_cart',
                                product_id: productId,
                                quantity: quantity,
                                nonce: emargyTimelineVars.wc_nonce
                            },
                            success: function(response) {
                                $button.removeClass('loading').prop('disabled', false);
                                
                                if (response.success) {
                                    $button.addClass('added');
                                    $('.emargy-cart-message').html('<p class="success">' + response.data.message + '</p>').fadeIn();
                                    
                                    setTimeout(function() {
                                        $('.emargy-cart-message').fadeOut();
                                    }, 3000);
                                    
                                    // Update cart fragments if available
                                    if (response.data.fragments) {
                                        $.each(response.data.fragments, function(key, value) {
                                            $(key).replaceWith(value);
                                        });
                                    }
                                } else {
                                    $('.emargy-cart-message').html('<p class="error">' + response.data.message + '</p>').fadeIn();
                                    
                                    setTimeout(function() {
                                        $('.emargy-cart-message').fadeOut();
                                    }, 3000);
                                }
                            },
                            error: function() {
                                $button.removeClass('loading').prop('disabled', false);
                                $('.emargy-cart-message').html('<p class="error"><?php esc_html_e('Error occurred. Please try again.', 'emargy-elements'); ?></p>').fadeIn();
                                
                                setTimeout(function() {
                                    $('.emargy-cart-message').fadeOut();
                                }, 3000);
                            }
                        });
                    });
                });
            })(jQuery);
            </script>
            
            <style>
            .emargy-product-modal {
                padding: 20px;
            }
            
            .emargy-product-modal-content {
                display: flex;
                flex-wrap: wrap;
                gap: 30px;
                margin-top: 20px;
            }
            
            .emargy-product-images {
                flex: 1;
                min-width: 300px;
            }
            
            .emargy-product-main-image {
                margin-bottom: 10px;
            }
            
            .emargy-product-main-image img {
                width: 100%;
                height: auto;
                border-radius: 4px;
            }
            
            .emargy-product-gallery {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .emargy-product-gallery-item {
                width: 60px;
                height: 60px;
                cursor: pointer;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
            }
            
            .emargy-product-gallery-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: opacity 0.3s;
            }
            
            .emargy-product-gallery-item img:hover {
                opacity: 0.8;
            }
            
            .emargy-product-info {
                flex: 1;
                min-width: 300px;
            }
            
            .emargy-product-price {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 15px;
                color: #e22d4b;
            }
            
            .emargy-product-rating {
                margin-bottom: 15px;
                display: flex;
                align-items: center;
            }
            
            .emargy-rating-count {
                margin-left: 5px;
                color: #777;
            }
            
            .emargy-product-excerpt {
                margin-bottom: 20px;
                line-height: 1.6;
                color: #555;
            }
            
            .emargy-product-actions {
                margin-bottom: 20px;
            }
            
            .emargy-product-quantity {
                margin-bottom: 15px;
                display: flex;
                align-items: center;
            }
            
            .emargy-product-quantity label {
                margin-right: 10px;
                font-weight: bold;
            }
            
            .emargy-quantity {
                width: 60px;
                padding: 5px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            
            .emargy-add-to-cart-btn,
            .emargy-view-product-btn {
                display: inline-block;
                padding: 10px 20px;
                margin-right: 10px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                text-decoration: none;
                transition: all 0.3s;
            }
            
            .emargy-add-to-cart-btn {
                background-color: #e22d4b;
                color: #fff;
            }
            
            .emargy-add-to-cart-btn:hover {
                background-color: #c42742;
            }
            .emargy-view-product-btn {
                background-color: #f5f5f5;
                color: #333;
            }
            
            .emargy-view-product-btn:hover {
                background-color: #e5e5e5;
            }
            
            .emargy-add-to-cart-btn.loading {
                opacity: 0.7;
                cursor: wait;
                position: relative;
            }
            
            .emargy-add-to-cart-btn.loading::after {
                content: "";
                position: absolute;
                top: 50%;
                right: 10px;
                width: 12px;
                height: 12px;
                border: 2px solid rgba(255, 255, 255, 0.3);
                border-top-color: #fff;
                border-radius: 50%;
                animation: emargy-spin 1s infinite linear;
            }
            
            .emargy-add-to-cart-btn.added {
                background-color: #4CAF50;
            }
            
            .emargy-cart-message {
                margin-top: 10px;
                display: none;
            }
            
            .emargy-cart-message .success {
                color: #4CAF50;
                background-color: rgba(76, 175, 80, 0.1);
                padding: 10px;
                border-radius: 4px;
                border-left: 3px solid #4CAF50;
            }
            
            .emargy-cart-message .error {
                color: #f44336;
                background-color: rgba(244, 67, 54, 0.1);
                padding: 10px;
                border-radius: 4px;
                border-left: 3px solid #f44336;
            }
            
            .emargy-product-categories {
                font-size: 14px;
                color: #777;
            }
            
            .emargy-product-categories-label {
                font-weight: bold;
                margin-right: 5px;
            }
            
            .emargy-product-sale-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: #e22d4b;
                color: #fff;
                padding: 5px 10px;
                font-size: 12px;
                font-weight: bold;
                border-radius: 3px;
                z-index: 2;
            }
            
            @keyframes emargy-spin {
                to {
                    transform: rotate(360deg);
                }
            }
            
            @media (max-width: 767px) {
                .emargy-product-modal-content {
                    flex-direction: column;
                }
            }
            </style>
            <?php
            return ob_get_clean();
        }
        
        return $content;
    }

    /**
     * AJAX add to cart handler
     */
    public function ajax_add_to_cart() {
        // Check security nonce
        check_ajax_referer('emargy_woocommerce_nonce', 'nonce');
        
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
        
        if (!$product_id) {
            wp_send_json_error(array('message' => __('Invalid product ID', 'emargy-elements')));
            exit;
        }
        
        // Get product
        $product = wc_get_product($product_id);
        
        if (!$product) {
            wp_send_json_error(array('message' => __('Product not found', 'emargy-elements')));
            exit;
        }
        
        // Check if product is purchasable
        if (!$product->is_purchasable()) {
            wp_send_json_error(array('message' => __('This product cannot be purchased', 'emargy-elements')));
            exit;
        }
        
        // Check if product is in stock
        if (!$product->is_in_stock()) {
            wp_send_json_error(array('message' => __('This product is out of stock', 'emargy-elements')));
            exit;
        }
        
        // Add to cart
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
        
        if ($cart_item_key) {
            // Get updated cart fragments
            $fragments = array();
            
            ob_start();
            woocommerce_mini_cart();
            $mini_cart = ob_get_clean();
            
            $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';
            
            wp_send_json_success(array(
                'message' => sprintf(__('%s has been added to your cart.', 'emargy-elements'), $product->get_name()),
                'product_name' => $product->get_name(),
                'cart_hash' => WC()->cart->get_cart_hash(),
                'fragments' => $fragments,
                'cart_url' => wc_get_cart_url(),
                'cart_count' => WC()->cart->get_cart_contents_count()
            ));
        } else {
            wp_send_json_error(array('message' => __('Error adding product to cart', 'emargy-elements')));
        }
        
        exit;
    }

    /**
     * Enqueue WooCommerce assets
     */
    public function enqueue_woocommerce_assets() {
        // Check if on a page with our timeline
        if (has_shortcode(get_the_content(), 'emargy_timeline') || is_active_widget(false, false, 'emargy-timeline')) {
            // Enqueue WooCommerce styles if not already enqueued
            if (!wp_style_is('woocommerce-general', 'enqueued')) {
                wp_enqueue_style('woocommerce-general');
            }
            
            // Add WooCommerce nonce to timeline vars
            add_filter('emargy_timeline_vars', function($vars) {
                $vars['wc_nonce'] = wp_create_nonce('emargy_woocommerce_nonce');
                return $vars;
            });
        }
    }

    /**
     * Add WooCommerce settings to timeline settings
     *
     * @param array $settings Current settings
     * @return array Modified settings
     */
    public function add_woocommerce_settings($settings) {
        $woocommerce_settings = array(
            'woocommerce' => array(
                'title' => __('WooCommerce Settings', 'emargy-elements'),
                'fields' => array(
                    'show_product_price' => array(
                        'type' => 'switch',
                        'label' => __('Show Product Price', 'emargy-elements'),
                        'default' => 'yes',
                    ),
                    'show_sale_badge' => array(
                        'type' => 'switch',
                        'label' => __('Show Sale Badge', 'emargy-elements'),
                        'default' => 'yes',
                    ),
                    'show_rating' => array(
                        'type' => 'switch',
                        'label' => __('Show Product Rating', 'emargy-elements'),
                        'default' => 'yes',
                    ),
                    'enable_add_to_cart' => array(
                        'type' => 'switch',
                        'label' => __('Enable Add to Cart', 'emargy-elements'),
                        'default' => 'yes',
                    ),
                ),
            ),
        );
        
        return array_merge($settings, $woocommerce_settings);
    }
}

// Initialize the class
new Emargy_WooCommerce_Integration();