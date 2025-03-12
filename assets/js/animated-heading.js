/**
 * Emargy Animated Heading Widget JavaScript - Enhanced Version
 * 
 * @since 2.1.0
 */
(function($) {
    'use strict';
    
    /**
     * Initialize animation based on type
     * 
     * @param {string} headingId - The heading element ID
     * @param {object} settings - Animation settings
     */
    function initAnimatedHeading(headingId, settings) {
        const $heading = $('#' + headingId);
        
        if (!$heading.length) {
            return;
        }
        
        switch (settings.type) {
            case 'typing':
                initTypingAnimation($heading, settings);
                break;
            case 'highlight':
                initHighlightAnimation($heading, settings);
                break;
            default:
                initStandardAnimation($heading, settings);
                break;
        }
    }
    
    /**
     * Initialize typing animation using Typed.js
     * 
     * @param {jQuery} $heading - The heading element
     * @param {object} settings - Animation settings
     */
    function initTypingAnimation($heading, settings) {
        if (typeof Typed !== 'undefined') {
            new Typed('#' + $heading.attr('id'), {
                strings: settings.words,
                typeSpeed: settings.typingSpeed,
                backSpeed: settings.backSpeed,
                backDelay: settings.delayBetweenWords,
                startDelay: settings.startDelay || 0,
                loop: settings.loop !== undefined ? settings.loop : true,
                showCursor: settings.showCursor,
                cursorChar: settings.cursorChar,
                onBegin: function(self) {
                    // Ensure proper accessibility
                    $heading.attr('aria-live', 'polite');
                },
                preStringTyped: function(arrayPos, self) {
                    // Fire event for potential hooks
                    $(document).trigger('emargy_typing_started', [$heading, arrayPos, settings]);
                },
                onStringTyped: function(arrayPos, self) {
                    // Fire event for potential hooks
                    $(document).trigger('emargy_typing_completed', [$heading, arrayPos, settings]);
                }
            });
        } else {
            console.warn('Typed.js library not loaded. Please check if the script is properly enqueued.');
        }
    }
    
    /**
     * Initialize highlight animation
     * 
     * @param {jQuery} $heading - The heading element
     * @param {object} settings - Animation settings
     */
    function initHighlightAnimation($heading, settings) {
        let currentIndex = 0;
        
        // Add highlight class
        $heading.addClass('emargy-highlight-shape ' + settings.highlightedShape);
        
        // Set initial text
        $heading.text(settings.words[0]);
        
        // Start animation cycle
        setInterval(function() {
            currentIndex = (currentIndex + 1) % settings.words.length;
            
            // Apply exit animation
            $heading.removeClass('emargy-animation-enter').addClass('emargy-animation-exit');
            
            // Change text and apply enter animation after half the duration
            setTimeout(function() {
                $heading.text(settings.words[currentIndex]);
                $heading.removeClass('emargy-animation-exit').addClass('emargy-animation-enter');
                
                // Fire event for potential hooks
                $(document).trigger('emargy_word_changed', [$heading, currentIndex, settings]);
            }, settings.animationDuration / 2);
            
        }, settings.animationDuration + settings.delayBetweenWords);
    }
    
    /**
     * Initialize standard animation (fade, slide, zoom, bounce, rotate)
     * 
     * @param {jQuery} $heading - The heading element
     * @param {object} settings - Animation settings
     */
    function initStandardAnimation($heading, settings) {
        let currentIndex = 0;
        
        // Set initial text
        $heading.text(settings.words[0]);
        $heading.addClass('emargy-animation-enter');
        
        // Start animation cycle
        setInterval(function() {
            currentIndex = (currentIndex + 1) % settings.words.length;
            
            // Apply exit animation
            $heading.removeClass('emargy-animation-enter').addClass('emargy-animation-exit');
            
            // Change text and apply enter animation after half the duration
            setTimeout(function() {
                $heading.text(settings.words[currentIndex]);
                $heading.removeClass('emargy-animation-exit').addClass('emargy-animation-enter');
                
                // Fire event for potential hooks
                $(document).trigger('emargy_word_changed', [$heading, currentIndex, settings]);
            }, settings.animationDuration / 2);
            
        }, settings.animationDuration + settings.delayBetweenWords);
    }
    
    /**
     * Initialize all animated headings when page loads
     */
    $(document).ready(function() {
        $('.emargy-animated-heading-wrapper').each(function() {
            const $wrapper = $(this);
            const $heading = $wrapper.find('.emargy-animated-text');
            
            if ($heading.length) {
                // Get settings from data attribute
                const settings = $wrapper.data('settings');
                
                if (settings) {
                    initAnimatedHeading($heading.attr('id'), settings);
                }
            }
        });
    });
    
    // Initialize all animated headings when Elementor frontend is ready
    $(window).on('elementor/frontend/init', function() {
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/emargy_animated_heading.default', function($element) {
                const $wrapper = $element.find('.emargy-animated-heading-wrapper');
                const $heading = $wrapper.find('.emargy-animated-text');
                
                if ($heading.length) {
                    // Get settings from data attribute
                    const settings = $wrapper.data('settings');
                    
                    if (settings) {
                        initAnimatedHeading($heading.attr('id'), settings);
                    }
                }
            });
        }
    });
    
    // Make function available globally
    window.initAnimatedHeading = initAnimatedHeading;
    
})(jQuery);