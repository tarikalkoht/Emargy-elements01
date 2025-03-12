<?php
/**
 * Animated Heading Integration Functions
 * 
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Animated Heading scripts and styles
 */
function emargy_register_animated_heading_assets() {
    // Register typed.js library
    wp_register_script(
        'typed-js', 
        EMARGY_ELEMENTS_URL . 'assets/js/typed.min.js', 
        [], 
        '2.0.12', 
        true
    );
    
    // Register animated heading script
    wp_register_script(
        'emargy-animated-heading', 
        EMARGY_ELEMENTS_URL . 'assets/js/animated-heading.js', 
        ['jquery', 'typed-js'], 
        EMARGY_ELEMENTS_VERSION, 
        true
    );
    
    // Register animated heading styles
    wp_register_style(
        'emargy-animated-heading-style', 
        EMARGY_ELEMENTS_URL . 'assets/css/animated-heading.css', 
        [], 
        EMARGY_ELEMENTS_VERSION
    );
}
add_action('wp_enqueue_scripts', 'emargy_register_animated_heading_assets');
add_action('admin_enqueue_scripts', 'emargy_register_animated_heading_assets');

/**
 * Add highlight paint worklet for advanced highlight effects
 */
function emargy_add_highlight_paint_worklet() {
    ?>
    <script>
    if ('paintWorklet' in CSS) {
        try {
            CSS.paintWorklet.addModule('<?php echo esc_url(EMARGY_ELEMENTS_URL . 'assets/js/highlight-paint-worklet.js'); ?>');
        } catch(e) {
            console.warn('CSS Paint API not fully supported in this browser.');
        }
    }
    </script>
    <?php
}
add_action('wp_footer', 'emargy_add_highlight_paint_worklet');

/**
 * Add animated heading shortcode
 * 
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function emargy_animated_heading_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 'emargy-heading-' . uniqid(),
        'before_text' => '',
        'animated_text' => 'Amazing, Creative, Unique',
        'after_text' => '',
        'animation' => 'typing',
        'tag' => 'h2',
        'alignment' => 'center',
        'color' => '',
        'animated_color' => '',
        'speed' => 100,
        'delay' => 2000,
        'loop' => 'yes',
        'cursor' => 'yes',
        'class' => '',
    ), $atts, 'emargy_animated_heading');
    
    // Prepare animated text
    $animated_text_array = explode(',', $atts['animated_text']);
    $animated_text_array = array_map('trim', $animated_text_array);
    
    // Prepare animation settings
    $settings = array(
        'type' => $atts['animation'],
        'typingSpeed' => intval($atts['speed']),
        'delayBetweenWords' => intval($atts['delay']),
        'showCursor' => $atts['cursor'] === 'yes',
        'loop' => $atts['loop'] === 'yes',
        'words' => $animated_text_array
    );
    
    // Prepare styles
    $styles = array();
    $animated_styles = array();
    
    if (!empty($atts['color'])) {
        $styles[] = 'color: ' . $atts['color'];
    }
    
    if (!empty($atts['animated_color'])) {
        $animated_styles[] = 'color: ' . $atts['animated_color'];
    }
    
    // Prepare classes
    $wrapper_classes = array(
        'emargy-animated-heading-wrapper',
        'emargy-animation-type-' . $atts['animation'],
        'emargy-heading-align-' . $atts['alignment']
    );
    
    if (!empty($atts['class'])) {
        $wrapper_classes[] = $atts['class'];
    }
    
    // Ensure scripts and styles are enqueued
    wp_enqueue_script('typed-js');
    wp_enqueue_script('emargy-animated-heading');
    wp_enqueue_style('emargy-animated-heading-style');
    
    // Build output
    ob_start();
    ?>
    <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" data-settings="<?php echo esc_attr(json_encode($settings)); ?>" style="text-align: <?php echo esc_attr($atts['alignment']); ?>">
        <<?php echo esc_html($atts['tag']); ?> class="emargy-animated-heading" <?php echo !empty($styles) ? 'style="' . esc_attr(implode(';', $styles)) . '"' : ''; ?>>
            <?php echo esc_html($atts['before_text']); ?>
            <span id="<?php echo esc_attr($atts['id']); ?>" class="emargy-animated-text" <?php echo !empty($animated_styles) ? 'style="' . esc_attr(implode(';', $animated_styles)) . '"' : ''; ?>></span>
            <?php echo esc_html($atts['after_text']); ?>
        </<?php echo esc_html($atts['tag']); ?>>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('emargy_animated_heading', 'emargy_animated_heading_shortcode');

/**
 * Add extra features to animated heading
 */
function emargy_animated_heading_extra_features() {
    // Add reduced motion support
    echo '<style>
    @media (prefers-reduced-motion: reduce) {
        .emargy-animated-heading-wrapper .emargy-animated-text {
            transition: none !important;
        }
        .typed-cursor {
            animation: none !important;
        }
    }
    </style>';
}
add_action('wp_head', 'emargy_animated_heading_extra_features');