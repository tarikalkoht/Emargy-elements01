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
     * Vendor path
     * 
     * @since 2.1.0
     * @access public
     * @var string Path to vendor directory
     */
    public $vendor_path;
    
    /**
     * Enhancements path
     * 
     * @since 2.1.0
     * @access public
     * @var string Path to enhancements directory
     */
    public $enhancements_path;

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
        // Define paths
        $this->vendor_path = EMARGY_ELEMENTS_PATH . 'vendor/';
        $this->enhancements_path = EMARGY_ELEMENTS_PATH . 'includes/enhancements/';
        
        // Register activation/deactivation hooks
        register_activation_hook(EMARGY_ELEMENTS_FILE, [$this, 'activation']);
        register_deactivation_hook(EMARGY_ELEMENTS_FILE, [$this, 'deactivation']);

        // Initialize hooks
        add_action('plugins_loaded', [$this, 'init']);
        
        // Load compatibility features early
        $this->load_compatibility_features();
    }

    /**
     * Activation
     *
     * @since 1.0.0
     * @access public
     */
    public function activation() {
        // Create required directories
        $this->create_directories();
        
        // Copy enhancement files if needed
        $this->copy_enhancement_files();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Trigger action for other components
        do_action('emargy_elements_activated');
    }

    /**
     * Deactivation
     *
     * @since 1.0.0
     * @access public
     */
    public function deactivation() {
        // Clean up transients
        if (class_exists('Emargy_Cache')) {
            Emargy_Cache::clear_all_cache();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Trigger action for other components
        do_action('emargy_elements_deactivated');
    }

    /**
     * Create required directories
     * 
     * @since 2.1.0
     * @access private
     */
    private function create_directories() {
        $directories = [
            $this->vendor_path,
            $this->enhancements_path,
            EMARGY_ELEMENTS_PATH . 'logs/',
        ];
        
        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);
            }
        }
    }

    /**
     * Copy enhancement files if they don't exist
     * 
     * @since 2.1.0
     * @access private
     */
    private function copy_enhancement_files() {
        // Enhancement files mapping (source => destination)
        $files = [
            'php-compatibility.php' => 'emargy-php-compatibility.php',
            'ajax-handler.php' => 'emargy-ajax-handler.php',
            'accessibility-improvements.php' => 'emargy-accessibility-improvements.php',
            'woocommerce-integration.php' => 'emargy-woocommerce-integration.php',
        ];
        
        foreach ($files as $source => $destination) {
            $source_path = $this->enhancements_path . $source;
            $dest_path = $this->vendor_path . $destination;
            
            // Only copy if destination doesn't exist or source is newer
            if (!file_exists($dest_path) || (file_exists($source_path) && filemtime($source_path) > filemtime($dest_path))) {
                if (file_exists($source_path)) {
                    copy($source_path, $dest_path);
                }
            }
        }
    }

    /**
     * Load compatibility features early
     * 
     * @since 2.1.0
     * @access private
     */
    private function load_compatibility_features() {
        // Load PHP compatibility file
        $php_compat_file = $this->vendor_path . 'emargy-php-compatibility.php';
        if (file_exists($php_compat_file)) {
            require_once $php_compat_file;
        }
        
        // Define compatibility constants
        if (!defined('EMARGY_COMPAT_VERSION')) {
            define('EMARGY_COMPAT_VERSION', '2.1.0');
        }
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
        // Load textdomain
        $this->load_textdomain();
        
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
        
        // Load enhancements
        $this->load_enhancements();
    }

    /**
     * Load plugin textdomain
     * 
     * @since 2.1.0
     * @access public
     */
    public function load_textdomain() {
        load_plugin_textdomain('emargy-elements', false, dirname(plugin_basename(EMARGY_ELEMENTS_FILE)) . '/languages/');
    }

    /**
     * Load enhancement files
     * 
     * @since 2.1.0
     * @access public
     */
    public function load_enhancements() {
        // Load AJAX handler
        $ajax_handler = $this->vendor_path . 'emargy-ajax-handler.php';
        if (file_exists($ajax_handler)) {
            require_once $ajax_handler;
        }
        
        // Load accessibility improvements
        $accessibility = $this->vendor_path . 'emargy-accessibility-improvements.php';
        if (file_exists($accessibility)) {
            require_once $accessibility;
        }
        
        // Load cache system
        $cache_system = $this->vendor_path . 'emargy-cache-system.php';
        if (file_exists($cache_system)) {
            require_once $cache_system;
        }
        
        // Load WooCommerce integration if WooCommerce is active
        if (class_exists('WooCommerce')) {
            $woocommerce = $this->vendor_path . 'emargy-woocommerce-integration.php';
            if (file_exists($woocommerce)) {
                require_once $woocommerce;
            }
        }
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
        require_once(EMARGY_ELEMENTS_PATH . 'includes/widgets/class-animated-heading-widget.php');

        // Register widgets
        $widgets_manager->register(new \Emargy_Timeline_Showcase_Widget());
        $widgets_manager->register(new \Emargy_Animated_Heading_Widget());
    }

    /**
     * Register stylesheets for the frontend
     */
    public function widget_styles() {
        wp_register_style('emargy-timeline-style', EMARGY_ELEMENTS_URL . 'assets/css/emargy-timeline.css', [], EMARGY_ELEMENTS_VERSION);
        wp_enqueue_style('emargy-timeline-style');
        
        wp_register_style('emargy-animated-heading-style', EMARGY_ELEMENTS_URL . 'assets/css/animated-heading.css', [], EMARGY_ELEMENTS_VERSION);
        wp_enqueue_style('emargy-animated-heading-style');
        
        wp_register_style('emargy-timeline-editor-style', EMARGY_ELEMENTS_URL . 'assets/css/editor.css', [], EMARGY_ELEMENTS_VERSION);
        wp_enqueue_style('emargy-timeline-editor-style');
        
        // Load RTL styles if needed
        if (is_rtl()) {
            wp_register_style('emargy-timeline-rtl', EMARGY_ELEMENTS_URL . 'assets/css/emargy-timeline-rtl.css', ['emargy-timeline-style'], EMARGY_ELEMENTS_VERSION);
            wp_enqueue_style('emargy-timeline-rtl');
        }
    }

    /**
     * Register scripts for the frontend
     */
    public function widget_scripts() {
        // Existing scripts
        wp_register_script('emargy-timeline-script', EMARGY_ELEMENTS_URL . 'assets/js/emargy-timeline.js', ['jquery'], EMARGY_ELEMENTS_VERSION, true);
        
        // New scripts for text effects
        wp_register_script('typed-js', EMARGY_ELEMENTS_URL . 'assets/js/typed.min.js', [], '2.0.12', true);
        wp_register_script('emargy-animated-heading', EMARGY_ELEMENTS_URL . 'assets/js/animated-heading.js', ['jquery', 'typed-js'], EMARGY_ELEMENTS_VERSION, true);
        
        // Enqueue timeline script
        wp_enqueue_script('emargy-timeline-script');
        
        // Localize script with variables
        $this->localize_script_vars();
    }

    /**
     * Localize script variables
     * 
     * @since 2.1.0
     * @access private
     */
    private function localize_script_vars() {
        $vars = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('emargy_timeline_nonce'),
            'isRTL' => is_rtl() ? true : false,
            'version' => EMARGY_ELEMENTS_VERSION,
        ];
        
        // Allow other components to add variables
        $vars = apply_filters('emargy_timeline_vars', $vars);
        
        wp_localize_script('emargy-timeline-script', 'emargyTimelineVars', $vars);
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