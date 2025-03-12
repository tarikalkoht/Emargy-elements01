<?php
/**
 * Emargy Elements Enhancements
 *
 * Integrates all improvements and fixes for better performance, 
 * security, and user experience.
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define version constant for the enhancements
define('EMARGY_ENHANCEMENTS_VERSION', '2.1.0');

/**
 * Class to manage all enhancements
 */
class Emargy_Enhancements {

    /**
     * Constructor
     */
    public function __construct() {
        // Load improvements based on execution order
        $this->load_dependencies();
        
        // Add plugin activation hook
        register_activation_hook(EMARGY_ELEMENTS_FILE, array($this, 'on_activation'));
        
        // Add plugin update hook
        add_action('upgrader_process_complete', array($this, 'on_update'), 10, 2);
        
        // Add version check
        add_action('admin_init', array($this, 'check_version'));
        
        // Add admin page section for enhancements
        add_action('emargy_admin_after_content', array($this, 'render_enhancements_section'));
        
        // Add readme tab to plugin row
        add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
    }

    /**
     * Load all enhancement files
     */
    private function load_dependencies() {
        // Path to the enhancements folder
        $enhancements_path = EMARGY_ELEMENTS_PATH . 'includes/enhancements/';
        
        // Create directory if it doesn't exist
        if (!file_exists($enhancements_path)) {
            mkdir($enhancements_path, 0755, true);
        }
        
        // Copy enhancement files to the directory
        $this->ensure_enhancement_files_exist($enhancements_path);
        
        // Load in specific order
        
        // 1. PHP Compatibility first (for general compatibility)
        require_once $enhancements_path . 'php-compatibility.php';
        
        // 2. Cache system (for performance)
        require_once $enhancements_path . 'cache-system.php';
        
        // 3. AJAX Handler improvements
        require_once $enhancements_path . 'ajax-handler.php';
        
        // 4. RTL support
        if (is_rtl()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_rtl_styles'), 100);
        }
        
        // 5. WooCommerce integration (if WooCommerce is active)
        if (class_exists('WooCommerce')) {
            require_once $enhancements_path . 'woocommerce-integration.php';
        }
        
        // 6. Accessibility improvements (always loaded)
        require_once $enhancements_path . 'accessibility-improvements.php';
    }

    /**
     * Ensure all enhancement files exist
     *
     * @param string $path Path to enhancements directory
     */
    private function ensure_enhancement_files_exist($path) {
        // List of enhancement files and their source content
        $enhancement_files = array(
            'php-compatibility.php' => $this->get_php_compatibility_content(),
            'cache-system.php' => $this->get_cache_system_content(),
            'ajax-handler.php' => $this->get_ajax_handler_content(),
            'woocommerce-integration.php' => $this->get_woocommerce_integration_content(),
            'accessibility-improvements.php' => $this->get_accessibility_content(),
        );
        
        // Make sure each file exists
        foreach ($enhancement_files as $file => $content) {
            $file_path = $path . $file;
            
            if (!file_exists($file_path)) {
                file_put_contents($file_path, $content);
            }
        }
        
        // Create RTL CSS file
        $rtl_css_path = EMARGY_ELEMENTS_PATH . 'assets/css/emargy-timeline-rtl-enhanced.css';
        if (!file_exists($rtl_css_path)) {
            file_put_contents($rtl_css_path, $this->get_rtl_css_content());
        }
    }

    /**
     * Plugin activation hook
     */
    public function on_activation() {
        // Trigger creation of enhancement files
        $this->load_dependencies();
        
        // Set version in options
        update_option('emargy_enhancements_version', EMARGY_ENHANCEMENTS_VERSION);
        
        // Fire action for enhancements activation
        do_action('emargy_elements_activated');
    }

    /**
     * Plugin update hook
     * 
     * @param WP_Upgrader $upgrader Upgrader instance
     * @param array $options Update options
     */
    public function on_update($upgrader, $options) {
        // Check if our plugin was updated
        if ($options['action'] == 'update' && $options['type'] == 'plugin') {
            foreach ($options['plugins'] as $plugin) {
                if ($plugin == plugin_basename(EMARGY_ELEMENTS_FILE)) {
                    // Our plugin was updated, reload dependencies
                    $this->load_dependencies();
                    
                    // Update version in options
                    update_option('emargy_enhancements_version', EMARGY_ENHANCEMENTS_VERSION);
                    
                    // Fire action for enhancements update
                    do_action('emargy_elements_updated');
                    
                    break;
                }
            }
        }
    }

    /**
     * Check version and update if needed
     */
    public function check_version() {
        $current_version = get_option('emargy_enhancements_version', '0');
        
        if (version_compare($current_version, EMARGY_ENHANCEMENTS_VERSION, '<')) {
            // Version has changed, reload dependencies
            $this->load_dependencies();
            
            // Update version in options
            update_option('emargy_enhancements_version', EMARGY_ENHANCEMENTS_VERSION);
            
            // Fire action for enhancements update
            do_action('emargy_elements_updated');
        }
    }

    /**
     * Enqueue RTL styles
     */
    public function enqueue_rtl_styles() {
        wp_enqueue_style(
            'emargy-timeline-rtl-enhanced',
            EMARGY_ELEMENTS_URL . 'assets/css/emargy-timeline-rtl-enhanced.css',
            array('emargy-timeline-style'),
            EMARGY_ENHANCEMENTS_VERSION
        );
    }

    /**
     * Render enhancements section on admin page
     */
    public function render_enhancements_section() {
        ?>
        <div class="emargy-admin-card">
            <h2><?php esc_html_e('Enhancements & Optimizations', 'emargy-elements'); ?></h2>
            <p><?php esc_html_e('Your Emargy Elements plugin has been enhanced with the following improvements:', 'emargy-elements'); ?></p>
            
            <div class="emargy-enhancements-list">
                <div class="emargy-enhancement-item">
                    <h4><span class="dashicons dashicons-performance"></span> <?php esc_html_e('Performance', 'emargy-elements'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Optimized JavaScript with better memory management', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('Implemented caching system for faster loading', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('Added lazy loading for timeline images', 'emargy-elements'); ?></li>
                    </ul>
                </div>
                
                <div class="emargy-enhancement-item">
                    <h4><span class="dashicons dashicons-shield"></span> <?php esc_html_e('Security', 'emargy-elements'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Improved nonce verification for AJAX requests', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('Better data validation and sanitization', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('Added rate limiting for API requests', 'emargy-elements'); ?></li>
                    </ul>
                </div>
                
                <div class="emargy-enhancement-item">
                    <h4><span class="dashicons dashicons-universal-access"></span> <?php esc_html_e('Accessibility', 'emargy-elements'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Full keyboard navigation support', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('Screen reader support with ARIA attributes', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('High contrast mode and reduced motion support', 'emargy-elements'); ?></li>
                    </ul>
                </div>
                
                <div class="emargy-enhancement-item">
                    <h4><span class="dashicons dashicons-admin-site"></span> <?php esc_html_e('Compatibility', 'emargy-elements'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Enhanced RTL language support', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('WooCommerce integration', 'emargy-elements'); ?></li>
                        <li><?php esc_html_e('PHP 7.4+ and 8.0+ compatibility', 'emargy-elements'); ?></li>
                    </ul>
                </div>
            </div>
            
            <p class="emargy-enhancements-footer">
                <?php printf(
                    esc_html__('Enhancements Version: %s', 'emargy-elements'),
                    EMARGY_ENHANCEMENTS_VERSION
                ); ?>
            </p>
        </div>
        
        <style>
            .emargy-enhancements-list {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            
            .emargy-enhancement-item {
                background: #f9f9f9;
                border-radius: 5px;
                padding: 15px;
                border-left: 4px solid #e22d4b;
            }
            
            .emargy-enhancement-item h4 {
                display: flex;
                align-items: center;
                margin-top: 0;
                color: #e22d4b;
            }
            
            .emargy-enhancement-item h4 .dashicons {
                margin-right: 8px;
            }
            
            .emargy-enhancement-item ul {
                margin: 10px 0 0 0;
                padding-left: 25px;
            }
            
            .emargy-enhancement-item li {
                margin-bottom: 5px;
            }
            
            .emargy-enhancements-footer {
                margin-top: 20px;
                color: #666;
                font-size: 12px;
                text-align: right;
            }
        </style>
        <?php
    }

    /**
     * Add plugin row meta
     *
     * @param array $plugin_meta Plugin meta
     * @param string $plugin_file Plugin file
     * @return array Modified plugin meta
     */
    public function add_plugin_row_meta($plugin_meta, $plugin_file) {
        if (plugin_basename(EMARGY_ELEMENTS_FILE) === $plugin_file) {
            $row_meta = array(
                'enhancements' => '<a href="' . esc_url(admin_url('admin.php?page=emargy-elements#enhancements')) . '">' . 
                                 esc_html__('Enhancements', 'emargy-elements') . '</a>'
            );
            
            return array_merge($plugin_meta, $row_meta);
        }
        
        return $plugin_meta;
    }

    /**
     * Get PHP compatibility content
     *
     * @return string File content
     */
    private function get_php_compatibility_content() {
        ob_start();
        ?>
<?php
/**
 * Modern PHP Compatibility and Security Enhancements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Include the main compatibility class
require_once dirname(__FILE__) . '/../../vendor/emargy-php-compatibility.php';
<?php
        return ob_get_clean();
    }

    /**
     * Get cache system content
     *
     * @return string File content
     */
    private function get_cache_system_content() {
        ob_start();
        ?>
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

// Include the main cache class
require_once dirname(__FILE__) . '/../../vendor/emargy-cache-system.php';
<?php
        return ob_get_clean();
    }

    /**
     * Get AJAX handler content
     *
     * @return string File content
     */
    private function get_ajax_handler_content() {
        ob_start();
        ?>
<?php
/**
 * Improved AJAX Handlers for Emargy Elements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Include the improved AJAX handler
require_once dirname(__FILE__) . '/../../vendor/emargy-ajax-handler.php';
<?php
        return ob_get_clean();
    }

    /**
     * Get WooCommerce integration content
     *
     * @return string File content
     */
    private function get_woocommerce_integration_content() {
        ob_start();
        ?>
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

// Return if WooCommerce is not active
if (!class_exists('WooCommerce')) {
    return;
}

// Include the WooCommerce integration
require_once dirname(__FILE__) . '/../../vendor/emargy-woocommerce-integration.php';
<?php
        return ob_get_clean();
    }

    /**
     * Get accessibility content
     *
     * @return string File content
     */
    private function get_accessibility_content() {
        ob_start();
        ?>
<?php
/**
 * Accessibility Improvements for Emargy Elements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Include the accessibility improvements
require_once dirname(__FILE__) . '/../../vendor/emargy-accessibility-improvements.php';
<?php
        return ob_get_clean();
    }

    /**
     * Get RTL CSS content
     *
     * @return string CSS content
     */
    private function get_rtl_css_content() {
        ob_start();
        ?>
/**
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
}
<?php
        return ob_get_clean();
    }
}

// Initialize the Emargy_Enhancements class
new Emargy_Enhancements();

/**
 * Create vendor directory and files
 */
function emargy_create_vendor_files() {
    // Path to vendor directory
    $vendor_path = EMARGY_ELEMENTS_PATH . 'vendor/';
    
    // Create the directory if it doesn't exist
    if (!file_exists($vendor_path)) {
        mkdir($vendor_path, 0755, true);
    }
    
    // Copy the enhancement files to vendor directory
    $enhancement_files = array(
        'php-compatibility.php' => EMARGY_ELEMENTS_PATH . 'includes/enhancements/php-compatibility.php',
        'cache-system.php' => EMARGY_ELEMENTS_PATH . 'includes/enhancements/cache-system.php',
        'ajax-handler.php' => EMARGY_ELEMENTS_PATH . 'includes/enhancements/ajax-handler.php',
        'woocommerce-integration.php' => EMARGY_ELEMENTS_PATH . 'includes/enhancements/woocommerce-integration.php',
        'accessibility-improvements.php' => EMARGY_ELEMENTS_PATH . 'includes/enhancements/accessibility-improvements.php',
    );
    
    foreach ($enhancement_files as $target => $source) {
        copy($source, $vendor_path . 'emargy-' . $target);
    }
}

// Register activation hook
register_activation_hook(EMARGY_ELEMENTS_FILE, 'emargy_create_vendor_files');