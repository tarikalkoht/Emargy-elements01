/**
 * Emargy Animated Heading Widget JavaScript
 */
(function($) {
    'use strict';
    
    window.initAnimatedHeading = function(headingId, settings) {
        var $heading = $('#' + headingId);
        
        if (!$heading.length) {
            return;
        }
        
        if (settings.type === 'typing') {
            // Typing effect using Typed.js
            if (typeof Typed !== 'undefined') {
                new Typed('#' + headingId, {
                    strings: settings.words,
                    typeSpeed: settings.typingSpeed,
                    backSpeed: settings.backSpeed,
                    backDelay: settings.delayBetweenWords,
                    loop: true,
                    showCursor: settings.showCursor,
                    cursorChar: settings.cursorChar
                });
            }
        } else {
            // Other animation types
            var currentIndex = 0;
            var animationClass = 'emargy-animation-' + settings.type;
            
            $heading.addClass(animationClass);
            $heading.text(settings.words[0]);
            
            setInterval(function() {
                currentIndex = (currentIndex + 1) % settings.words.length;
                
                // Apply exit animation
                $heading.removeClass('emargy-animation-enter').addClass('emargy-animation-exit');
                
                setTimeout(function() {
                    // Change text and apply enter animation
                    $heading.text(settings.words[currentIndex]);
                    $heading.removeClass('emargy-animation-exit').addClass('emargy-animation-enter');
                }, settings.animationDuration / 2);
                
            }, settings.animationDuration + settings.delayBetweenWords);
        }
    };
    
    // Initialize all animated headings when Elementor frontend is ready
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/emargy_animated_heading.default', function($element) {
            // Animation will be initialized through inline script for each instance
        });
    });
    
})(jQuery);