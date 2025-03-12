<?php
/**
 * Plugin Name: Emargy Elements
 * Plugin URI: https://www.emargy.com
 * Description: A custom addon for Elementor that allows you to create an interactive timeline to showcase posts, projects, services, or any other content in a professional way.
 * Version: 1.0.0
 * Author: Emargy
 * Author URI: https://www.emargy.com
 * Text Domain: emargy-elements
 * Domain Path: /languages
 * Elementor tested up to: 3.16.0
 * Elementor Pro tested up to: 3.16.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EMARGY_ELEMENTS_VERSION', '1.0.0');
define('EMARGY_ELEMENTS_FILE', __FILE__);
define('EMARGY_ELEMENTS_PATH', plugin_dir_path(__FILE__));
define('EMARGY_ELEMENTS_URL', plugins_url('/', __FILE__));

/**
 * Load plugin text domain
 */
function emargy_elements_load_textdomain() {
    load_plugin_textdomain('emargy-elements', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'emargy_elements_load_textdomain');

/**
 * Include the main Emargy Elements class
 */
if (!class_exists('Emargy_Elements')) {
    include_once EMARGY_ELEMENTS_PATH . 'includes/class-emargy-elements.php';
    include_once EMARGY_ELEMENTS_PATH . 'includes/ajax-handler.php';
}

/**
 * Main function that returns the Emargy_Elements instance
 */
function emargy_elements() {
    return Emargy_Elements::instance();
}

// Initialize the plugin
emargy_elements();

/**
 * Enqueue admin scripts and styles
 */
function emargy_elements_admin_scripts() {
    wp_enqueue_style('emargy-admin-style', EMARGY_ELEMENTS_URL . 'assets/css/admin.css', array(), EMARGY_ELEMENTS_VERSION);
}
add_action('admin_enqueue_scripts', 'emargy_elements_admin_scripts');

/**
 * Localize script variables
 */
function emargy_elements_localize_script() {
    wp_localize_script('emargy-timeline-script', 'emargyTimelineVars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('emargy_timeline_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'emargy_elements_localize_script');

/**
 * Register the shortcode
 */
function emargy_timeline_shortcode($atts) {
    // Extract shortcode attributes with defaults
    $atts = shortcode_atts(array(
        'type' => 'post',
        'limit' => 10,
        'layout' => 'wave',
        'featured_size' => '2x',
    ), $atts, 'emargy_timeline');

    // Buffer output
    ob_start();

    // Include the shortcode template
    include EMARGY_ELEMENTS_PATH . 'includes/widgets/shortcode-timeline.php';

    // Return buffered content
    return ob_get_clean();
}
add_shortcode('emargy_timeline', 'emargy_timeline_shortcode');

/**
 * Add debug logging function
 */
function emargy_elements_debug_log($message) {
    if (WP_DEBUG && WP_DEBUG_LOG) {
        error_log('Emargy Elements Debug: ' . $message);
    }
}

// Add log entries at key points
add_action('plugins_loaded', function() {
    emargy_elements_debug_log('Plugins loaded hook fired');
});

add_action('elementor/loaded', function() {
    emargy_elements_debug_log('Elementor loaded hook fired');
});

add_action('elementor/widgets/register', function($widgets_manager) {
    emargy_elements_debug_log('Widget register hook fired');
}, 5); // Priority 5 so it runs before your actual registration