<?php
/**
 * Emargy Elements Enhancements Loader
 * 
 * @package EmargyElements
 * @since 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Enhancements Loader Class
 */
class Emargy_Enhancements {
    /**
     * Singleton instance
     * 
     * @var Emargy_Enhancements
     */
    private static $instance = null;

    /**
     * Get singleton instance
     * 
     * @return Emargy_Enhancements
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prevent direct instantiation
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Public wakeup method to resolve visibility warning
     */
    public function __wakeup() {
        // Prevent unserialize of the instance
        _doing_it_wrong(__METHOD__, 'Unserializing instances of this class is not allowed.', '2.1.0');
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_enhancements'), 5);
    }

    /**
     * Load enhancement modules
     */
    public function load_enhancements() {
        // Ensure Elementor is active
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', array($this, 'elementor_missing_notice'));
            return;
        }

        // Generate and save dynamic files if needed
        $this->generate_dynamic_files();
    }

    /**
     * Generate dynamic files for various modules
     */
    private function generate_dynamic_files() {
        // Ensure the vendor directory exists
        $vendor_path = EMARGY_ELEMENTS_PATH . 'vendor/';
        if (!file_exists($vendor_path)) {
            wp_mkdir_p($vendor_path);
        }

        // Files to generate
        $files = array(
            'emargy-ajax-handler.php' => $this->get_ajax_handler_content(),
            'emargy-cache-system.php' => $this->get_cache_system_content(),
            'emargy-woocommerce-integration.php' => $this->get_woocommerce_integration_content(),
            'emargy-rtl.css' => $this->get_rtl_css_content()
        );

        // Write files
        foreach ($files as $filename => $content) {
            $filepath = $vendor_path . $filename;
            
            // Ensure content is valid
            if (!empty($content)) {
                // Use WordPress filesystem API for writing files
                if (function_exists('wp_filesystem')) {
                    global $wp_filesystem;
                    if (empty($wp_filesystem)) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        WP_Filesystem();
                    }
                    
                    $wp_filesystem->put_contents(
                        $filepath, 
                        $content, 
                        FS_CHMOD_FILE
                    );
                } else {
                    // Fallback to traditional file writing
                    file_put_contents($filepath, $content);
                    chmod($filepath, 0644);
                }
            }
        }
    }

    /**
     * Display notice if Elementor is missing
     */
    public function elementor_missing_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php 
                printf(
                    __('Emargy Elements requires %s to be installed and activated.', 'emargy-elements'), 
                    '<strong>Elementor</strong>'
                ); 
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Get AJAX handler content
     *
     * @return string
     */
    private function get_ajax_handler_content() {
        return '<?php
/**
 * AJAX Handler for Emargy Elements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined("ABSPATH")) {
    exit;
}

class Emargy_Enhanced_AJAX_Handler {
    public function __construct() {
        add_action("wp_ajax_emargy_secure_action", array($this, "handle_secure_action"));
        add_action("wp_ajax_nopriv_emargy_secure_action", array($this, "handle_secure_action"));
    }

    public function handle_secure_action() {
        // Implement robust security checks
        check_ajax_referer("emargy_nonce", "security");
        
        // Implement proper sanitization
        $data = isset($_POST["data"]) ? sanitize_text_field($_POST["data"]) : "";
        
        // Process and return response
        wp_send_json_success($data);
    }
}

new Emargy_Enhanced_AJAX_Handler();';
    }

    /**
     * Get cache system content
     *
     * @return string
     */
    private function get_cache_system_content() {
        return '<?php
/**
 * Emargy Elements Cache System
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined("ABSPATH")) {
    exit;
}

class Emargy_Cache {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Setup cache hooks
    }

    public static function clear_cache() {
        // Implement cache clearing logic
    }
}

Emargy_Cache::instance();';
    }

    /**
     * Get WooCommerce integration content
     *
     * @return string
     */
    private function get_woocommerce_integration_content() {
        return '<?php
/**
 * WooCommerce Integration for Emargy Elements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined("ABSPATH")) {
    exit;
}

class Emargy_WooCommerce_Integration {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Check if WooCommerce is active
        if (!class_exists("WooCommerce")) {
            return;
        }

        // Add integration hooks
        add_action("init", array($this, "setup_integration"));
    }

    public function setup_integration() {
        // Setup WooCommerce integration
    }
}

Emargy_WooCommerce_Integration::instance();';
    }

    /**
     * Get RTL CSS content
     *
     * @return string
     */
    private function get_rtl_css_content() {
        return '/**
 * Emargy RTL Styles
 * Right-to-Left language support
 *
 * @since 2.1.0
 */

.rtl .emargy-timeline-items {
    flex-direction: row-reverse;
}

.rtl .emargy-timeline-item {
    margin-right: 0;
    margin-left: 30px;
}';
    }
}

// Initialize the Emargy_Enhancements class
function emargy_enhancements() {
    return Emargy_Enhancements::instance();
}

// Start the enhancements
emargy_enhancements();