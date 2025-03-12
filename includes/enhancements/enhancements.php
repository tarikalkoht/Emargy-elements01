/**
     * Get AJAX handler content
     *
     * @return string File content
     */
    private function get_ajax_handler_content() {
        // Basic implementation of AJAX handler
        return "<?php
/**
 * AJAX Handler for Emargy Elements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle AJAX requests with enhanced security
 */
class Emargy_Enhanced_AJAX_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_emargy_get_posts', array($this, 'get_posts'));
        add_action('wp_ajax_nopriv_emargy_get_posts', array($this, 'get_posts'));
        
        add_action('wp_ajax_emargy_get_post_content', array($this, 'get_post_content'));
        add_action('wp_ajax_nopriv_emargy_get_post_content', array($this, 'get_post_content'));
        
        add_action('wp_ajax_emargy_get_video_info', array($this, 'get_video_info'));
        add_action('wp_ajax_nopriv_emargy_get_video_info', array($this, 'get_video_info'));
        
        add_action('wp_ajax_emargy_get_terms', array($this, 'get_taxonomy_terms'));
        
        // Setup rate limiting and security checks
        add_action('init', array($this, 'setup_security'));
    }

    /**
     * Setup security measures
     */
    public function setup_security() {
        // Only apply rate limiting on AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $this->check_rate_limit();
            $this->check_referrer();
        }
    }

    /**
     * Check rate limiting
     */
    private function check_rate_limit() {
        // Get client IP
        $client_ip = $this->get_client_ip();
        
        // Initialize rate limiting
        $rate_key = 'emargy_rate_' . md5($client_ip);
        $rate_limit = 60; // Requests per minute
        $rate_window = 60; // 1 minute window
        
        // Get current count and time
        $rate_count = get_transient($rate_key);
        
        if (false === $rate_count) {
            // First request, set to 1
            set_transient($rate_key, 1, $rate_window);
        } else if ($rate_count < $rate_limit) {
            // Increment request count
            set_transient($rate_key, $rate_count + 1, $rate_window);
        } else {
            // Too many requests
            $this->send_error_response('rate_limit_exceeded', 'Rate limit exceeded. Please try again later.', 429);
        }
    }

    /**
     * Check HTTP referrer
     */
    private function check_referrer() {
        // Skip for admin
        if (current_user_can('manage_options')) {
            return;
        }
        
        // Check referrer
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        if (!$referrer || parse_url($referrer, PHP_URL_HOST) !== parse_url(home_url(), PHP_URL_HOST)) {
            $this->send_error_response('invalid_referrer', 'Invalid request origin', 403);
        }
    }

    /**
     * Get posts via AJAX
     */
    public function get_posts() {
        $this->verify_nonce('emargy_timeline_nonce');
        
        // Parse request data
        $content_type = isset($_POST['content_type']) ? sanitize_text_field($_POST['content_type']) : 'post';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 10;
        $category = isset($_POST['category']) ? array_map('absint', (array)$_POST['category']) : array();
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_key($_POST['taxonomy']) : '';
        $terms = isset($_POST['terms']) ? array_map('absint', (array)$_POST['terms']) : array();
        
        // Build query args
        $args = array(
            'post_type' => $content_type,
            'posts_per_page' => $limit,
            'orderby' => $orderby,
            'order' => $order,
            'post_status' => 'publish',
        );
        
        // Category filter for posts
        if ($content_type === 'post' && !empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $category,
                )
            );
        }
        
        // Custom taxonomy filter
        if (!empty($taxonomy) && !empty($terms)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $terms,
                )
            );
        }
        
        // Get posts with caching if available
        if (function_exists('emargy_get_cached_query')) {
            $query = emargy_get_cached_query($args, 'ajax_timeline');
        } else {
            $query = new WP_Query($args);
        }
        
        // Prepare response data
        $posts_data = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                // Get thumbnail URL
                $thumbnail_url = '';
                if (has_post_thumbnail()) {
                    $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                }
                
                // Get video URL if available
                $video_url = '';
                $video_field = isset($_POST['video_field']) ? sanitize_text_field($_POST['video_field']) : 'video_url';
                if ($video_field) {
                    $video_url = get_post_meta(get_the_ID(), $video_field, true);
                }
                
                // Build post data
                $posts_data[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 15),
                    'permalink' => get_permalink(),
                    'thumbnail_url' => $thumbnail_url,
                    'video_url' => $video_url,
                    'date' => get_the_date(),
                );
            }
            wp_reset_postdata();
        }
        
        // Send response
        wp_send_json_success(array(
            'posts' => $posts_data,
            'found_posts' => $query->found_posts,
            'max_num_pages' => $query->max_num_pages,
        ));
    }

    /**
     * Helper methods
     */
    private function verify_nonce($action) {
        if (!check_ajax_referer($action, 'nonce', false)) {
            $this->send_error_response('invalid_nonce', 'Security check failed.', 403);
        }
    }
    
    private function send_error_response($code, $message, $status = 400) {
        status_header($status);
        wp_send_json_error(array(
            'code' => $code,
            'message' => $message
        ));
        exit;
    }
    
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }
        
        return '127.0.0.1';
    }
}

// Initialize the class
new Emargy_Enhanced_AJAX_Handler();";
    }

    /**
     * Get cache system content
     *
     * @return string File content
     */
    private function get_cache_system_content() {
        // Basic implementation of cache system
        return "<?php
/**
 * Emargy Elements Cache System
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle caching for Emargy Elements
 */
class Emargy_Cache {

    /**
     * Singleton instance
     */
    private static \$_instance = null;

    /**
     * Cache group name
     */
    private \$cache_group = 'emargy_elements';

    /**
     * Cache expiration in seconds
     */
    private \$cache_expiration = 3600; // 1 hour

    /**
     * Get singleton instance
     */
    public static function instance() {
        if (is_null(self::\$_instance)) {
            self::\$_instance = new self();
        }
        return self::\$_instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Add transient caching for timeline queries
        add_filter('emargy_timeline_query_args', array(\$this, 'maybe_get_cached_query_results'), 10, 2);
        add_filter('emargy_timeline_query_results', array(\$this, 'maybe_cache_query_results'), 10, 2);
        
        // Add cache invalidation when posts are updated
        add_action('save_post', array(\$this, 'invalidate_post_cache'), 10, 3);
        add_action('deleted_post', array(\$this, 'invalidate_post_cache'));
        add_action('trashed_post', array(\$this, 'invalidate_post_cache'));
    }

    /**
     * Clear all plugin cache
     */
    public static function clear_all_cache() {
        \$instance = self::instance();
        \$instance->invalidate_all_cache();
    }

    /**
     * Clear cache for a specific post
     */
    public static function clear_post_cache(\$post_id) {
        \$instance = self::instance();
        \$instance->invalidate_post_cache(\$post_id);
    }

    /**
     * Invalidate all cache
     */
    public function invalidate_all_cache() {
        global \$wpdb;
        
        // Get all transients in our group
        \$prefix = '_transient_emargy_';
        \$sql = \"DELETE FROM \$wpdb->options WHERE option_name LIKE '%s'\";
        \$wpdb->query(\$wpdb->prepare(\$sql, \$prefix . '%'));
        
        // Also delete expired transients
        \$prefix = '_transient_timeout_emargy_';
        \$wpdb->query(\$wpdb->prepare(\$sql, \$prefix . '%'));
    }

    /**
     * Invalidate cache for a specific post
     */
    public function invalidate_post_cache(\$post_id, \$post = null, \$update = false) {
        // Implementation details
    }
}

// Initialize cache system
Emargy_Cache::instance();

/**
 * Function to get cached query results
 */
function emargy_get_cached_query(\$args, \$context = 'default') {
    // Apply filter to get possible cached results
    \$args = apply_filters('emargy_timeline_query_args', \$args, \$context);
    
    // Check if we have cached results
    if (isset(\$args['cached_results'])) {
        return \$args['cached_results'];
    }
    
    // No cache, run the query
    \$query = new WP_Query(\$args);
    
    // Cache the results for next time
    return apply_filters('emargy_timeline_query_results', \$query, \$args);
}";
    }

    /**
     * Get WooCommerce integration content
     *
     * @return string File content
     */
    private function get_woocommerce_integration_content() {
        // Basic implementation of WooCommerce integration
        return "<?php
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
        add_filter('emargy_timeline_content_types', array(\$this, 'add_product_content_type'));
        
        // Add product data to timeline items
        add_filter('emargy_timeline_item_data', array(\$this, 'add_product_data'), 10, 2);
        
        // Add product template for timeline items
        add_filter('emargy_timeline_item_template', array(\$this, 'product_item_template'), 10, 2);
        
        // Add to cart AJAX handler
        add_action('wp_ajax_emargy_add_to_cart', array(\$this, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_emargy_add_to_cart', array(\$this, 'ajax_add_to_cart'));
    }

    /**
     * Add product to timeline content types
     */
    public function add_product_content_type(\$content_types) {
        \$content_types['product'] = __('WooCommerce Products', 'emargy-elements');
        return \$content_types;
    }

    /**
     * Add product data to timeline item
     */
    public function add_product_data(\$item_data, \$post) {
        // Implementation details
        return \$item_data;
    }

    /**
     * Product item template
     */
    public function product_item_template(\$template, \$item) {
        // Implementation details
        return \$template;
    }
}

// Initialize the class
new Emargy_WooCommerce_Integration();";
    }

    /**
     * Get RTL CSS content
     *
     * @return string CSS content
     */
    private function get_rtl_css_content() {
        return "/**
 * Emargy Timeline Showcase Widget RTL Styles - Enhanced Version
 * Improved for Right-to-Left language support
 *
 * @since 2.1.0
 */

/* Base RTL Conversions */
.rtl .emargy-timeline-items {
    flex-direction: row-reverse;
}

.rtl .emargy-timeline-item {
    margin-right: 0;
    margin-left: 30px;
}

/* Timeline Line */
.rtl .emargy-timeline-line {
    right: 0;
    left: auto;
}

/* Timeline Wave */
.rtl .emargy-timeline-wave::before {
    background: repeating-linear-gradient(
        to left,
        transparent,
        transparent 3px,
        rgba(255, 255, 255, 0.5) 3px,
        rgba(255, 255, 255, 0.5) 4px
    );
}

.rtl .emargy-timeline-wave::after {
    right: 0;
    left: auto;
}

/* Navigation Arrows */
.rtl .emargy-nav-prev {
    right: 25px;
    left: auto;
}

.rtl .emargy-nav-next {
    left: 25px;
    right: auto;
}

.rtl .emargy-timeline-nav.emargy-nav-prev i {
    transform: rotate(180deg);
}

.rtl .emargy-timeline-nav.emargy-nav-next i {
    transform: rotate(180deg);
}

/* Modal Styles */
.rtl .emargy-modal-close,
.rtl .emargy-video-modal-close {
    right: auto;
    left: 20px;
}

.rtl .emargy-modal-meta {
    text-align: right;
}

.rtl .emargy-modal-read-more {
    float: right;
}

/* Timeline Content */
.rtl .emargy-timeline-content {
    text-align: right;
}

/* Timeline Numbers */
.rtl .emargy-timeline-number {
    direction: ltr; /* Keep numbers in LTR direction */
}

/* Fix for RTL drag & scroll */
.rtl .emargy-timeline-container.emargy-drag-enabled .emargy-timeline-items {
    direction: rtl;
}

/* Keyboard Navigation in RTL */
.rtl .emargy-timeline-container:focus .emargy-timeline-nav.emargy-nav-prev {
    box-shadow: -3px 0 5px rgba(0, 0, 0, 0.2); 
}

.rtl .emargy-timeline-container:focus .emargy-timeline-nav.emargy-nav-next {
    box-shadow: 3px 0 5px rgba(0, 0, 0, 0.2);
}

/* Accessibility Enhancements for RTL */
.rtl .screen-reader-text {
    left: auto;
    right: -9999px;
}

/* Responsive Adjustments */
@media screen and (max-width: 1200px) {
    .rtl .emargy-timeline-item {
        margin-left: 25px;
    }
}

@media screen and (max-width: 1024px) {
    .rtl .emargy-timeline-item {
        margin-left: 20px;
    }
}

@media screen and (max-width: 767px) {
    .rtl .emargy-nav-prev {
        right: 15px;
        left: auto;
    }
    
    .rtl .emargy-nav-next {
        left: 15px;
        right: auto;
    }
    
    .rtl .emargy-timeline-item {
        margin-left: 15px;
    }
}

@media screen and (max-width: 480px) {
    .rtl .emargy-timeline-item {
        margin-left: 12px;
    }
}";
    }
}

// Initialize the Emargy_Enhancements class
function emargy_enhancements() {
    return Emargy_Enhancements::instance();
}

// Start the enhancements
emargy_enhancements();