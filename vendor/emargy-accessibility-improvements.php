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

/**
 * Class to handle accessibility improvements
 */
class Emargy_Accessibility {

    /**
     * Constructor
     */
    public function __construct() {
        // Add ARIA attributes to timeline elements
        add_filter('emargy_timeline_container_attrs', array($this, 'add_timeline_container_attrs'));
        add_filter('emargy_timeline_item_attrs', array($this, 'add_timeline_item_attrs'));
        
        // Add keyboard navigation support
        add_action('wp_footer', array($this, 'add_keyboard_navigation_script'));
        
        // Add screen reader text
        add_filter('emargy_timeline_before_container', array($this, 'add_screen_reader_instructions'));
        
        // Add high contrast mode support
        add_action('wp_head', array($this, 'add_high_contrast_styles'));
        
        // Add reduced motion support
        add_action('wp_head', array($this, 'add_reduced_motion_styles'));
    }

    /**
     * Add ARIA attributes to timeline container
     *
     * @param array $attrs Existing attributes
     * @return array Modified attributes
     */
    public function add_timeline_container_attrs($attrs) {
        $attrs['role'] = 'region';
        $attrs['aria-label'] = __('Timeline Showcase', 'emargy-elements');
        $attrs['tabindex'] = '0';
        
        return $attrs;
    }

    /**
     * Add ARIA attributes to timeline items
     *
     * @param array $attrs Existing attributes
     * @return array Modified attributes
     */
    public function add_timeline_item_attrs($attrs) {
        $attrs['role'] = 'button';
        $attrs['tabindex'] = '0';
        $attrs['aria-controls'] = 'emargy-modal';
        
        return $attrs;
    }

    /**
     * Add keyboard navigation script
     */
    public function add_keyboard_navigation_script() {
        ?>
        <script>
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                // Handle keyboard navigation
                $('.emargy-timeline-item').on('keydown', function(e) {
                    // Enter or space key to activate item
                    if (e.which === 13 || e.which === 32) {
                        e.preventDefault();
                        $(this).trigger('click');
                    }
                });
                
                // Arrow keys for timeline container
                $('.emargy-timeline-container').on('keydown', function(e) {
                    var $container = $(this);
                    
                    // Left arrow
                    if (e.which === 37) {
                        e.preventDefault();
                        $container.find('.emargy-nav-prev').trigger('click');
                    }
                    // Right arrow
                    else if (e.which === 39) {
                        e.preventDefault();
                        $container.find('.emargy-nav-next').trigger('click');
                    }
                });
                
                // Make modals accessible
                $('.emargy-modal-close, .emargy-video-modal-close').attr({
                    'role': 'button',
                    'tabindex': '0',
                    'aria-label': '<?php echo esc_js(__('Close modal', 'emargy-elements')); ?>'
                }).on('keydown', function(e) {
                    if (e.which === 13 || e.which === 32) {
                        e.preventDefault();
                        $(this).trigger('click');
                    }
                });
                
                // Focus trap for modals
                $(document).on('keydown', '.emargy-modal, .emargy-video-modal', function(e) {
                    if (e.which === 9) {
                        var $modal = $(this);
                        var $focusable = $modal.find('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
                        var $firstFocusable = $focusable.first();
                        var $lastFocusable = $focusable.last();
                        
                        // Shift + Tab
                        if (e.shiftKey) {
                            if (document.activeElement === $firstFocusable[0]) {
                                e.preventDefault();
                                $lastFocusable.focus();
                            }
                        } 
                        // Tab
                        else {
                            if (document.activeElement === $lastFocusable[0]) {
                                e.preventDefault();
                                $firstFocusable.focus();
                            }
                        }
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Add screen reader instructions
     *
     * @param string $output Existing output
     * @return string Modified output
     */
    public function add_screen_reader_instructions($output) {
        $instructions = '<div class="screen-reader-text">' . 
            __('Timeline navigation. Use left and right arrow keys to navigate between items. Press Enter to select an item.', 'emargy-elements') . 
        '</div>';
        
        return $output . $instructions;
    }

    /**
     * Add high contrast mode styles
     */
    public function add_high_contrast_styles() {
        ?>
        <style>
            @media (prefers-contrast: more) {
                .emargy-timeline-container {
                    background-color: #000 !important;
                }
                
                .emargy-timeline-item-inner {
                    background-color: #111 !important;
                    border: 2px solid #fff !important;
                }
                
                .emargy-timeline-title,
                .emargy-timeline-number,
                .emargy-timeline-excerpt {
                    color: #fff !important;
                }
                
                .emargy-timeline-line,
                .emargy-timeline-wave::after {
                    background-color: #fff !important;
                }
                
                .emargy-play-button {
                    background-color: #000 !important;
                    border: 2px solid #fff !important;
                }
                
                .emargy-play-button i {
                    color: #fff !important;
                }
                
                .emargy-timeline-nav {
                    background-color: #000 !important;
                    border: 2px solid #fff !important;
                }
                
                .emargy-timeline-nav i {
                    color: #fff !important;
                }
                
                .emargy-timeline-indicator {
                    background-color: #666 !important;
                    border: 1px solid #fff !important;
                }
                
                .emargy-timeline-indicator.active {
                    background-color: #fff !important;
                }
            }
        </style>
        <?php
    }

    /**
     * Add reduced motion styles
     */
    public function add_reduced_motion_styles() {
        ?>
        <style>
            @media (prefers-reduced-motion: reduce) {
                .emargy-timeline-items {
                    transition: none !important;
                }
                
                .emargy-timeline-item-inner {
                    transition: none !important;
                }
                
                .emargy-timeline-thumbnail img {
                    transition: none !important;
                }
                
                .emargy-play-button::before {
                    animation: none !important;
                }
                
                .emargy-timeline-nav:hover {
                    transform: translateY(-50%) !important;
                }
                
                .emargy-hover-enabled .emargy-timeline-item:hover .emargy-timeline-item-inner {
                    transform: none !important;
                }
                
                .emargy-hover-enabled .emargy-timeline-center-item:hover .emargy-timeline-item-inner {
                    transform: scale(2) !important;
                }
                
                .emargy-hover-enabled .emargy-timeline-item:not(.emargy-timeline-center-item):hover .emargy-timeline-thumbnail img {
                    transform: none !important;
                }
                
                .typed-cursor {
                    animation: none !important;
                }
            }
        </style>
        <?php
    }
}

// Initialize the class
new Emargy_Accessibility();