<?php
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
     * Cache group name
     * 
     * @var string
     */
    private $cache_group = 'emargy_elements';

    /**
     * Cache expiration in seconds
     * 
     * @var int
     */
    private $cache_expiration = 3600; // 1 hour

    /**
     * Constructor
     */
    public function __construct() {
        // Add transient caching for timeline queries
        add_filter('emargy_timeline_query_args', array($this, 'maybe_get_cached_query_results'), 10, 2);
        add_filter('emargy_timeline_query_results', array($this, 'maybe_cache_query_results'), 10, 2);
        
        // Add cache invalidation when posts are updated
        add_action('save_post', array($this, 'invalidate_post_cache'), 10, 3);
        add_action('deleted_post', array($this, 'invalidate_post_cache'));
        add_action('trashed_post', array($this, 'invalidate_post_cache'));
        
        // Add cache invalidation for WooCommerce products if available
        add_action('woocommerce_update_product', array($this, 'invalidate_product_cache'));
        add_action('woocommerce_delete_product', array($this, 'invalidate_product_cache'));
        
        // Add cache invalidation for plugin activation/deactivation
        add_action('activated_plugin', array($this, 'invalidate_all_cache'));
        add_action('deactivated_plugin', array($this, 'invalidate_all_cache'));
        
        // Add admin option to clear cache
        add_action('admin_init', array($this, 'register_cache_settings'));
        add_action('admin_notices', array($this, 'display_cache_cleared_notice'));
    }

    /**
     * Check if we have cached query results
     *
     * @param array $args Query arguments
     * @param string $context Context for the query
     * @return array Query arguments (possibly with pre-cached results)
     */
    public function maybe_get_cached_query_results($args, $context) {
        // Skip cache if not enabled
        if (!$this->is_cache_enabled()) {
            return $args;
        }
        
        // Don't cache in admin or during AJAX
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return $args;
        }
        
        // Generate cache key based on query args and context
        $cache_key = $this->generate_cache_key($args, $context);
        
        // Check if we have cached results
        $cached_results = get_transient($cache_key);
        
        if (false !== $cached_results) {
            // Return cached results
            $args['cached_results'] = $cached_results;
        }
        
        return $args;
    }

    /**
     * Cache query results if not already cached
     *
     * @param object $results Query results
     * @param array $args Query arguments
     * @return object Original query results
     */
    public function maybe_cache_query_results($results, $args) {
        // Skip cache if not enabled
        if (!$this->is_cache_enabled()) {
            return $results;
        }
        
        // Don't cache in admin or during AJAX
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return $results;
        }
        
        // Generate cache key based on query args
        $cache_key = $this->generate_cache_key($args, isset($args['context']) ? $args['context'] : 'default');
        
        // Cache the results
        set_transient($cache_key, $results, $this->get_cache_expiration());
        
        return $results;
    }

    /**
     * Generate a cache key based on query args
     *
     * @param array $args Query arguments
     * @param string $context Context for the query
     * @return string Cache key
     */
    private function generate_cache_key($args, $context) {
        // Remove any cached_results from args to avoid recursion
        if (isset($args['cached_results'])) {
            unset($args['cached_results']);
        }
        
        // Generate a unique key based on args and context
        $key_data = array(
            'args' => $args,
            'context' => $context,
            'lang' => defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '',
            'version' => EMARGY_ELEMENTS_VERSION
        );
        
        return 'emargy_' . md5(serialize($key_data));
    }

    /**
     * Invalidate cache for a specific post
     *
     * @param int $post_id Post ID
     * @param object $post Optional. Post object
     * @param bool $update Optional. Whether this is an update
     */
    public function invalidate_post_cache($post_id, $post = null, $update = false) {
        // Skip revisions
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Get post type
        $post_type = $post ? $post->post_type : get_post_type($post_id);
        
        // Delete specific post cache
        $this->delete_cache('post_' . $post_id);
        
        // Delete post type cache
        $this->delete_cache('post_type_' . $post_type);
        
        // Delete general timeline cache
        $this->delete_cache('timeline');
    }

    /**
     * Invalidate cache for WooCommerce products
     *
     * @param int $product_id Product ID
     */
    public function invalidate_product_cache($product_id) {
        // Delete specific product cache
        $this->delete_cache('product_' . $product_id);
        
        // Delete product type cache
        $this->delete_cache('post_type_product');
        
        // Delete general timeline cache
        $this->delete_cache('timeline');
    }

    /**
     * Invalidate all plugin cache
     */
    public function invalidate_all_cache() {
        global $wpdb;
        
        // Get all transients in our group
        $prefix = '_transient_emargy_';
        $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '%s'";
        $wpdb->query($wpdb->prepare($sql, $prefix . '%'));
        
        // Also delete expired transients
        $prefix = '_transient_timeout_emargy_';
        $wpdb->query($wpdb->prepare($sql, $prefix . '%'));
    }

    /**
     * Delete cache for a specific key pattern
     *
     * @param string $key_pattern Key pattern to match
     */
    private function delete_cache($key_pattern) {
        global $wpdb;
        
        // Delete matching transients
        $prefix = '_transient_emargy_';
        $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '%s'";
        $wpdb->query($wpdb->prepare($sql, $prefix . '%' . $key_pattern . '%'));
        
        // Also delete timeouts
        $prefix = '_transient_timeout_emargy_';
        $wpdb->query($wpdb->prepare($sql, $prefix . '%' . $key_pattern . '%'));
    }

    /**
     * Check if caching is enabled
     *
     * @return bool True if caching is enabled
     */
    private function is_cache_enabled() {
        // Check if caching is explicitly disabled
        if (defined('EMARGY_DISABLE_CACHE') && EMARGY_DISABLE_CACHE) {
            return false;
        }
        
        // Get option from settings
        $enabled = get_option('emargy_enable_cache', '1');
        
        return $enabled === '1';
    }

    /**
     * Get cache expiration time
     *
     * @return int Cache expiration in seconds
     */
    private function get_cache_expiration() {
        // Check if there's a custom expiration time
        if (defined('EMARGY_CACHE_EXPIRATION')) {
            return intval(EMARGY_CACHE_EXPIRATION);
        }
        
        // Get from options
        $expiration = get_option('emargy_cache_expiration', $this->cache_expiration);
        
        return intval($expiration);
    }

    /**
     * Register cache settings
     */
    public function register_cache_settings() {
        register_setting('emargy_cache_settings', 'emargy_enable_cache');
        register_setting('emargy_cache_settings', 'emargy_cache_expiration');
        
        // Handle cache clear action
        if (isset($_GET['emargy_clear_cache']) && current_user_can('manage_options')) {
            $this->invalidate_all_cache();
            
            // Redirect back without the query param
            wp_redirect(remove_query_arg('emargy_clear_cache', wp_get_referer()));
            exit;
        }
    }

    /**
     * Display notice when cache is cleared
     */
    public function display_cache_cleared_notice() {
        if (isset($_GET['emargy_cache_cleared']) && current_user_can('manage_options')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Emargy Elements cache has been cleared.', 'emargy-elements'); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Add cache settings to the admin page
     */
    public static function render_cache_settings() {
        ?>
        <div class="emargy-admin-card">
            <h2><?php esc_html_e('Cache Settings', 'emargy-elements'); ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields('emargy_cache_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Caching', 'emargy-elements'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="emargy_enable_cache" value="1" <?php checked(get_option('emargy_enable_cache', '1'), '1'); ?>>
                                <?php esc_html_e('Cache timeline queries for better performance', 'emargy-elements'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Cache Duration', 'emargy-elements'); ?></th>
                        <td>
                            <input type="number" name="emargy_cache_expiration" value="<?php echo esc_attr(get_option('emargy_cache_expiration', 3600)); ?>" min="60" step="60" class="small-text">
                            <?php esc_html_e('seconds', 'emargy-elements'); ?>
                            <p class="description"><?php esc_html_e('How long to keep cached data (in seconds). Default is 3600 (1 hour).', 'emargy-elements'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <div class="emargy-admin-actions">
                <h3><?php esc_html_e('Clear Cache', 'emargy-elements'); ?></h3>
                <p><?php esc_html_e('If you\'ve made changes to your content and don\'t see them reflected in the timeline, you can clear the cache.', 'emargy-elements'); ?></p>
                <a href="<?php echo esc_url(add_query_arg('emargy_clear_cache', '1')); ?>" class="button button-secondary">
                    <?php esc_html_e('Clear Timeline Cache', 'emargy-elements'); ?>
                </a>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
new Emargy_Cache();

/**
 * Function to be called from templates to get cached query results
 *
 * @param array $args Query arguments
 * @param string $context Optional. Context for the query
 * @return WP_Query Query object
 */
function emargy_get_cached_query($args, $context = 'default') {
    // Apply filter to get possible cached results
    $args = apply_filters('emargy_timeline_query_args', $args, $context);
    
    // Check if we have cached results
    if (isset($args['cached_results'])) {
        return $args['cached_results'];
    }
    
    // No cache, run the query
    $query = new WP_Query($args);
    
    // Cache the results for next time
    return apply_filters('emargy_timeline_query_results', $query, $args);
}

/**
 * Add the cache settings section to the admin page
 */
function emargy_add_cache_settings_to_admin() {
    add_action('emargy_admin_after_content', array('Emargy_Cache', 'render_cache_settings'));
}
add_action('init', 'emargy_add_cache_settings_to_admin');

/**
 * Function to manually clear all plugin cache
 */
function emargy_clear_all_cache() {
    $cache = new Emargy_Cache();
    $cache->invalidate_all_cache();
}