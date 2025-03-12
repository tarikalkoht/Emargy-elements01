<?php
/**
 * Plugin Name: Emargy Elements
 * Plugin URI: https://www.emargy.com
 * Description: A premium addon for Elementor that allows you to create interactive timelines to showcase media, posts, projects, services, or any other content with an elegant professional design.
 * Version: 2.0.0
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
define('EMARGY_ELEMENTS_VERSION', '2.0.0');
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
    require_once EMARGY_ELEMENTS_PATH . 'includes/enhancements/enhancements.php'; // Load enhancements
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
        'isRTL' => is_rtl() ? true : false,
    ));
}
add_action('wp_enqueue_scripts', 'emargy_elements_localize_script', 20);

/**
 * Register the shortcode
 */
function emargy_timeline_shortcode($atts) {
    // Extract shortcode attributes with defaults
    $atts = shortcode_atts(array(
        'type' => 'post',
        'limit' => 11,
        'layout' => 'wave',
        'featured_size' => '2.2x',
        'category' => '',
        'order' => 'DESC',
        'order_by' => 'date',
        'thumbnail_mode' => 'image_title',
        'video_field' => 'video_url',
        'enable_video' => 'yes',
        'bg_color' => '#e22d4b',
        'item_spacing' => '30px',
        'custom_class' => '',
        'center_item' => 'middle',
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
 * Add dashboard widget for plugin info
 */
function emargy_elements_add_dashboard_widget() {
    if (current_user_can('manage_options')) {
        wp_add_dashboard_widget(
            'emargy_elements_dashboard_widget',
            'Emargy Elements - Timeline Showcase',
            'emargy_elements_dashboard_widget_content'
        );
    }
}
add_action('wp_dashboard_setup', 'emargy_elements_add_dashboard_widget');

/**
 * Dashboard widget content
 */
function emargy_elements_dashboard_widget_content() {
    ?>
    <div class="emargy-dashboard-widget">
        <div class="emargy-dashboard-header">
            <img src="<?php echo EMARGY_ELEMENTS_URL; ?>assets/img/emargy-icon.svg" alt="Emargy" width="40">
            <h3>Timeline Showcase Widget</h3>
        </div>
        <p>Create professional timeline showcases for your content with the Elementor widget or shortcode:</p>
        <code>[emargy_timeline type="post" limit="11" layout="wave"]</code>
        <div class="emargy-dashboard-links">
            <a href="https://www.emargy.com/docs/" target="_blank">Documentation</a> | 
            <a href="https://www.emargy.com/support/" target="_blank">Support</a> | 
            <a href="https://www.emargy.com/tutorials/" target="_blank">Video Tutorials</a>
        </div>
    </div>
    <style>
        .emargy-dashboard-widget {
            padding: 10px 0;
        }
        .emargy-dashboard-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .emargy-dashboard-header img {
            margin-right: 10px;
        }
        .emargy-dashboard-header h3 {
            margin: 0;
            padding: 0;
        }
        .emargy-dashboard-widget code {
            display: block;
            padding: 8px;
            margin: 10px 0;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }
        .emargy-dashboard-links {
            margin-top: 15px;
            color: #666;
        }
    </style>
    <?php
}

/**
 * Add an admin notice for first-time activation
 */
function emargy_elements_admin_notice() {
    // Check if already dismissed
    if (get_option('emargy_elements_notice_dismissed')) {
        return;
    }
    
    // Check if this is the first activation
    if (!get_option('emargy_elements_activated')) {
        update_option('emargy_elements_activated', true);
        ?>
        <div class="notice notice-info is-dismissible emargy-admin-notice">
            <h2>Thank You for Installing Emargy Elements!</h2>
            <p>Create stunning timeline showcases with our premium Elementor addon. Here's how to get started:</p>
            <ol>
                <li>Go to Elementor editor and add the <strong>Timeline Showcase</strong> widget to your page.</li>
                <li>Customize it to match your design needs using our extensive style options.</li>
                <li>Alternatively, use the shortcode: <code>[emargy_timeline type="post" limit="11" layout="wave"]</code></li>
            </ol>
            <p>
                <a href="<?php echo admin_url('admin.php?page=elementor'); ?>" class="button button-primary">Start Building</a>
                <a href="https://www.emargy.com/docs/" target="_blank" class="button">View Documentation</a>
                <a href="#" class="dismiss-notice" data-notice="emargy_elements_notice_dismissed">Dismiss</a>
            </p>
        </div>
        <script>
            jQuery(document).on('click', '.emargy-admin-notice .dismiss-notice', function(e) {
                e.preventDefault();
                var notice = jQuery(this).data('notice');
                jQuery.post(ajaxurl, {
                    action: 'emargy_dismiss_notice',
                    notice: notice,
                    nonce: '<?php echo wp_create_nonce('emargy_dismiss_notice'); ?>'
                });
                jQuery(this).closest('.notice').remove();
            });
        </script>
        <?php
    }
}
add_action('admin_notices', 'emargy_elements_admin_notice');

/**
 * AJAX handler to dismiss admin notices
 */
function emargy_elements_dismiss_notice() {
    if (check_ajax_referer('emargy_dismiss_notice', 'nonce', false) && current_user_can('manage_options')) {
        $notice = isset($_POST['notice']) ? sanitize_text_field($_POST['notice']) : '';
        if ($notice) {
            update_option($notice, true);
        }
    }
    wp_die();
}
add_action('wp_ajax_emargy_dismiss_notice', 'emargy_elements_dismiss_notice');

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

/**
 * Add RTL support
 */
function emargy_elements_rtl_support($styles) {
    if (is_rtl()) {
        wp_enqueue_style('emargy-timeline-rtl', EMARGY_ELEMENTS_URL . 'assets/css/emargy-timeline-rtl.css', array('emargy-timeline-style'), EMARGY_ELEMENTS_VERSION);
    }
}
add_action('wp_enqueue_scripts', 'emargy_elements_rtl_support', 30);

/**
 * Register a dynamic menu for the plugin
 */
function emargy_elements_register_admin_menu() {
    add_menu_page(
        'Emargy Elements',
        'Emargy Elements',
        'manage_options',
        'emargy-elements',
        'emargy_elements_admin_page',
        'dashicons-layout',
        30
    );
}
add_action('admin_menu', 'emargy_elements_register_admin_menu');

/**
 * Admin page callback
 */
function emargy_elements_admin_page() {
    ?>
    <div class="wrap emargy-admin-wrap">
        <h1>Emargy Elements - Timeline Showcase</h1>
        
        <div class="emargy-admin-content">
            <div class="emargy-admin-card">
                <h2>Getting Started</h2>
                <p>Create beautiful and interactive timeline showcases for your content with our premium Elementor addon.</p>
                <ol>
                    <li>Edit a page with Elementor</li>
                    <li>Search for "Timeline" in the widgets panel</li>
                    <li>Drag the Timeline Showcase widget to your page</li>
                    <li>Configure the widget settings to suit your needs</li>
                </ol>
                <p><a href="https://www.emargy.com/tutorials/getting-started/" target="_blank" class="button button-primary">Watch Tutorial</a></p>
            </div>
            
            <div class="emargy-admin-card">
                <h2>Shortcode Usage</h2>
                <p>You can also use the shortcode to add a timeline showcase anywhere on your site:</p>
                <code>[emargy_timeline type="post" limit="11" layout="wave" featured_size="2.2x"]</code>
                <h3>Available Parameters:</h3>
                <ul>
                    <li><code>type</code>: Content type (post, page, product, etc.)</li>
                    <li><code>limit</code>: Number of items (recommended odd numbers)</li>
                    <li><code>layout</code>: Timeline style (wave, straight, custom)</li>
                    <li><code>featured_size</code>: Size of the center item (1.5x, 1.8x, 2.2x, 2.5x)</li>
                    <li><code>category</code>: Category IDs (comma-separated)</li>
                    <li><code>order</code>: Sort order (ASC or DESC)</li>
                    <li><code>order_by</code>: Sort field (date, title, menu_order, rand)</li>
                    <li><code>thumbnail_mode</code>: Display mode (image_only, image_title, image_excerpt)</li>
                    <li><code>enable_video</code>: Enable video popup (yes or no)</li>
                    <li><code>video_field</code>: Custom field for video URL</li>
                    <li><code>bg_color</code>: Background color (e.g., #e22d4b)</li>
                </ul>
            </div>
        </div>
        
        <div class="emargy-admin-sidebar">
            <div class="emargy-admin-card">
                <h2>Need Help?</h2>
                <p>Check our comprehensive documentation or contact support if you need assistance.</p>
                <p><a href="https://www.emargy.com/docs/" target="_blank" class="button">Documentation</a></p>
                <p><a href="https://www.emargy.com/support/" target="_blank" class="button">Contact Support</a></p>
            </div>
            
            <div class="emargy-admin-card">
                <h2>Rate Our Plugin</h2>
                <p>If you enjoy using Emargy Elements, please consider leaving us a review!</p>
                <p><a href="https://wordpress.org/support/plugin/emargy-elements/reviews/#new-post" target="_blank" class="button">Leave a Review</a></p>
            </div>
        </div>
    </div>
    
    <style>
        .emargy-admin-wrap {
            margin: 20px 20px 0 0;
        }
        .emargy-admin-wrap h1 {
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .emargy-admin-content {
            float: left;
            width: 65%;
        }
        .emargy-admin-sidebar {
            float: right;
            width: 30%;
        }
        .emargy-admin-card {
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .emargy-admin-card h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #e22d4b;
        }
        .emargy-admin-card code {
            display: block;
            padding: 10px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            margin: 10px 0;
        }
        .emargy-admin-card ul {
            list-style: disc inside;
        }
        .emargy-admin-card ul li {
            margin-bottom: 5px;
        }
        .emargy-admin-card .button {
            margin-right: 5px;
        }
    </style>
    <?php
}