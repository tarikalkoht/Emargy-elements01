<?php
/**
 * Main Emargy Elements Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

final class Emargy_Elements {

    /**
     * Instance
     *
     * @since 1.0.0
     * @access private
     * @static
     * @var Emargy_Elements The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     * @static
     * @return Emargy_Elements An instance of the class.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct() {
        // Register activation/deactivation hooks
        register_activation_hook(EMARGY_ELEMENTS_FILE, [$this, 'activation']);
        register_deactivation_hook(EMARGY_ELEMENTS_FILE, [$this, 'deactivation']);

        // Initialize hooks
        add_action('plugins_loaded', [$this, 'init']);
    }

    /**
     * Activation
     *
     * @since 1.0.0
     * @access public
     */
    public function activation() {
        // Activation logic, like flushing rewrite rules if needed
        flush_rewrite_rules();
    }

    /**
     * Deactivation
     *
     * @since 1.0.0
     * @access public
     */
    public function deactivation() {
        // Deactivation logic if needed
        flush_rewrite_rules();
    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Elementor (and other plugins) are loaded.
     *
     * @since 1.0.0
     * @access public
     */
    public function init() {
        emargy_elements_debug_log('Initializing Emargy Elements plugin');

        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            emargy_elements_debug_log('Elementor not loaded');
            return;
        }

        // Register a custom category for our widgets
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);
        
        // Add Plugin actions
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'widget_styles']);
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'emargy-elements'),
            '<strong>' . esc_html__('Emargy Elements', 'emargy-elements') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'emargy-elements') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Register Widgets
     *
     * Register new Elementor widgets.
     *
     * @since 1.0.0
     * @access public
     */
    public function register_widgets($widgets_manager) {
        // Include Widget files
        require_once(EMARGY_ELEMENTS_PATH . 'includes/widgets/class-timeline-showcase-widget.php');

        // Register the widget
        $widgets_manager->register(new \Emargy_Timeline_Showcase_Widget());
    }

    /**
     * Register stylesheets for the frontend
     */
    public function widget_styles() {
        wp_register_style('emargy-timeline-style', EMARGY_ELEMENTS_URL . 'assets/css/emargy-timeline.css', [], EMARGY_ELEMENTS_VERSION);
        wp_enqueue_style('emargy-timeline-style');
    }

    /**
     * Register scripts for the frontend
     */
    public function widget_scripts() {
        wp_register_script('emargy-timeline-script', EMARGY_ELEMENTS_URL . 'assets/js/emargy-timeline.js', ['jquery'], EMARGY_ELEMENTS_VERSION, true);
        wp_enqueue_script('emargy-timeline-script');
    }

    /**
     * Add a new category for our widgets
     */
    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'emargy', // This is the category ID
            [
                'title' => esc_html__('Emargy Elements', 'emargy-elements'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
}