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

/**
 * Class to handle PHP compatibility and security
 */
class Emargy_PHP_Compatibility {

    /**
     * Constructor
     */
    public function __construct() {
        // Check PHP version
        add_action('admin_init', array($this, 'check_php_version'));
        
        // Add security headers
        add_action('send_headers', array($this, 'add_security_headers'));
        
        // Use modern PHP features when available
        $this->setup_modern_php_features();
        
        // Setup proper error handling
        $this->setup_error_handling();
    }

    /**
     * Check PHP version and show warning if too old
     */
    public function check_php_version() {
        $required_php_version = '7.4';
        $current_php_version = phpversion();
        
        if (version_compare($current_php_version, $required_php_version, '<')) {
            add_action('admin_notices', function() use ($current_php_version, $required_php_version) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <?php 
                        printf(
                            esc_html__('Emargy Elements: Your PHP version (%1$s) is below the recommended version (%2$s). Some features may not work correctly. Please upgrade your PHP version for better performance and security.', 'emargy-elements'),
                            esc_html($current_php_version),
                            esc_html($required_php_version)
                        ); 
                        ?>
                    </p>
                </div>
                <?php
            });
        }
    }

    /**
     * Add security headers to prevent XSS and other attacks
     */
    public function add_security_headers() {
        // Only add these headers when processing AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            if (!headers_sent()) {
                // Prevent MIME type sniffing
                header('X-Content-Type-Options: nosniff');
                
                // Prevent clickjacking
                header('X-Frame-Options: SAMEORIGIN');
                
                // Enable XSS protection
                header('X-XSS-Protection: 1; mode=block');
            }
        }
    }

    /**
     * Setup modern PHP features when available
     */
    private function setup_modern_php_features() {
        // Check if we can use typed properties (PHP 7.4+)
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            // We can use typed properties, but since we need to maintain compatibility,
            // we'll use docblocks for types and implement runtime type checking
            $this->setup_type_checking();
        }
        
        // Check if we can use named arguments (PHP 8.0+)
        if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
            // We can use named arguments, but we need to maintain backward compatibility
            // so we'll primarily use associative arrays for arguments
        }
        
        // Check if we can use attributes (PHP 8.0+)
        if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
            // We can use attributes, but we need to maintain backward compatibility
            // so we'll primarily use annotations in comments
        }
    }

    /**
     * Setup type checking for method arguments
     */
    private function setup_type_checking() {
        // This is a simple implementation of runtime type checking
        // that will be used in other classes
        if (!function_exists('emargy_validate_type')) {
            /**
             * Validate that a value matches the expected type
             *
             * @param mixed $value The value to check
             * @param string $expected_type The expected type
             * @param string $param_name The parameter name for error messages
             * @return bool True if valid, throws Exception if invalid
             * @throws InvalidArgumentException If type doesn't match
             */
            function emargy_validate_type($value, $expected_type, $param_name) {
                $valid = false;
                
                switch ($expected_type) {
                    case 'string':
                        $valid = is_string($value);
                        break;
                    case 'int':
                    case 'integer':
                        $valid = is_int($value);
                        break;
                    case 'float':
                    case 'double':
                        $valid = is_float($value);
                        break;
                    case 'bool':
                    case 'boolean':
                        $valid = is_bool($value);
                        break;
                    case 'array':
                        $valid = is_array($value);
                        break;
                    case 'object':
                        $valid = is_object($value);
                        break;
                    case 'null':
                        $valid = is_null($value);
                        break;
                    default:
                        // Check if it's a class
                        if (class_exists($expected_type)) {
                            $valid = $value instanceof $expected_type;
                        }
                        break;
                }
                
                if (!$valid) {
                    $actual_type = gettype($value);
                    if ($actual_type === 'object') {
                        $actual_type = get_class($value);
                    }
                    
                    throw new InvalidArgumentException(
                        sprintf(
                            'Argument "%s" must be of type %s, %s given',
                            $param_name,
                            $expected_type,
                            $actual_type
                        )
                    );
                }
                
                return true;
            }
        }
    }

    /**
     * Setup proper error handling
     */
    private function setup_error_handling() {
        // Register error handler for plugin-specific errors
        add_action('emargy_elements_error', array($this, 'handle_error'), 10, 4);
    }

    /**
     * Handle errors within the plugin
     *
     * @param string $message Error message
     * @param string $title Error title
     * @param array $args Additional arguments
     * @param bool $log Whether to log the error
     */
    public function handle_error($message, $title = '', $args = array(), $log = true) {
        // Default title if not provided
        if (empty($title)) {
            $title = __('Emargy Elements Error', 'emargy-elements');
        }
        
        // Format error message
        $error_message = sprintf('[%s] %s', $title, $message);
        
        // Add additional info if available
        if (!empty($args)) {
            $error_message .= ' ' . json_encode($args);
        }
        
        // Log error if requested and WP_DEBUG_LOG is enabled
        if ($log && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($error_message);
        }
        
        // Display error to admins if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            add_action('admin_notices', function() use ($title, $message) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong><?php echo esc_html($title); ?>:</strong> <?php echo esc_html($message); ?></p>
                </div>
                <?php
            });
        }
    }
}

// Initialize the class
new Emargy_PHP_Compatibility();

/**
 * Helper function to safely throw errors
 *
 * @param string $message Error message
 * @param string $title Error title
 * @param array $args Additional arguments
 * @param bool $log Whether to log the error
 */
function emargy_elements_error($message, $title = '', $args = array(), $log = true) {
    do_action('emargy_elements_error', $message, $title, $args, $log);
}

/**
 * Enhanced version of wp_kses_post that allows filtering allowed HTML
 *
 * @param string $content Content to filter
 * @return string Filtered content
 */
function emargy_kses_post($content) {
    $allowed_html = apply_filters('emargy_allowed_html', wp_kses_allowed_html('post'));
    return wp_kses($content, $allowed_html);
}

/**
 * Sanitize JSON data
 *
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function emargy_sanitize_json_data($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = emargy_sanitize_json_data($value);
        }
    } elseif (is_object($data)) {
        foreach ($data as $key => $value) {
            $data->$key = emargy_sanitize_json_data($value);
        }
    } elseif (is_string($data)) {
        $data = sanitize_text_field($data);
    }
    
    return $data;
}