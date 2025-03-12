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
        
        // Setup proper error handling
        $this->setup_error_handling();
        
        // Add escaping functions
        $this->add_escaping_functions();
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
        if (!headers_sent()) {
            // Prevent MIME type sniffing
            header('X-Content-Type-Options: nosniff');
            
            // Prevent clickjacking
            header('X-Frame-Options: SAMEORIGIN');
            
            // Enable XSS protection
            header('X-XSS-Protection: 1; mode=block');
            
            // Set content security policy for iframes
            header("Content-Security-Policy: frame-ancestors 'self'");
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

    /**
     * Add escaping functions
     */
    private function add_escaping_functions() {
        if (!function_exists('emargy_esc_html')) {
            /**
             * Escape HTML with better handling of arrays and objects
             *
             * @param mixed $data Data to escape
             * @return mixed Escaped data
             */
            function emargy_esc_html($data) {
                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        $data[$key] = emargy_esc_html($value);
                    }
                    return $data;
                } elseif (is_object($data)) {
                    $result = new stdClass();
                    foreach ($data as $key => $value) {
                        $result->$key = emargy_esc_html($value);
                    }
                    return $result;
                } elseif (is_string($data)) {
                    return esc_html($data);
                }
                
                return $data;
            }
        }
        
        if (!function_exists('emargy_esc_attr')) {
            /**
             * Escape attribute with better handling of arrays and objects
             *
             * @param mixed $data Data to escape
             * @return mixed Escaped data
             */
            function emargy_esc_attr($data) {
                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        $data[$key] = emargy_esc_attr($value);
                    }
                    return $data;
                } elseif (is_object($data)) {
                    $result = new stdClass();
                    foreach ($data as $key => $value) {
                        $result->$key = emargy_esc_attr($value);
                    }
                    return $result;
                } elseif (is_string($data)) {
                    return esc_attr($data);
                }
                
                return $data;
            }
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

/**
 * Validate type for method arguments
 *
 * @param mixed $value Value to check
 * @param string $expected_type Expected type
 * @param string $param_name Parameter name for error messages
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