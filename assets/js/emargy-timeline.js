/**
 * Emargy Enhanced Timeline Showcase Widget JavaScript
 * Improved with smoother animations, video support, and optimized performance
 */

(function($) {
    'use strict';

    // Throttle function to limit function calls for better performance
    function throttle(callback, limit) {
        var waiting = false;
        return function() {
            if (!waiting) {
                callback.apply(this, arguments);
                waiting = true;
                setTimeout(function() {
                    waiting = false;
                }, limit);
            }
        };
    }

    // Debounce function to delay function execution until after a period of inactivity
    function debounce(callback, delay) {
        var timer;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() {
                callback.apply(context, args);
            }, delay);
        };
    }

    $(document).ready(function() {
        // Initialize all timeline instances
        initializeTimelines();
        
        // Initialize when Elementor frontend is loaded (for edit mode)
        $(window).on('elementor/frontend/init', function() {
            if (typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode()) {
                elementorFrontend.hooks.addAction('frontend/element_ready/emargy_timeline_showcase.default', function() {
                    initializeTimelines();
                });
            }
        });
    });

    /**
     * Initialize all timeline instances
     */
    function initializeTimelines() {
        $('.emargy-timeline-container').each(function() {
            const $container = $(this);
            
            // Initialize only if not already initialized
            if (!$container.data('initialized')) {
                $container.data('initialized', true);
                initializeTimeline($container);
            }
        });
    }

    /**
     * Initialize a single timeline instance
     * 
     * @param {jQuery} $container The timeline container element
     */
    function initializeTimeline($container) {
        const $items = $container.find('.emargy-timeline-items');
        const $allItems = $container.find('.emargy-timeline-item');
        const $navPrev = $container.find('.emargy-nav-prev');
        const $navNext = $container.find('.emargy-nav-next');
        const openType = $container.data('open-type');
        
        // Always make the 4th item (index 3) the center item
        const centerIndex = 3; // 0-based index, so 4th item is index 3
        const $centerItem = $allItems.eq(centerIndex);
        
        // Remove center class from all items
        $allItems.removeClass('emargy-timeline-center-item');
        
        // Add center class to the 4th item
        $centerItem.addClass('emargy-timeline-center-item');
        
        // Center the item
        centerTimelineItem($items, $centerItem);
        
        // Lazy load images that are not immediately visible
        initLazyLoading($container);
        
        // Update indicator dots
        updateIndicators($container, $centerItem);
        
        // Initialize navigation
        initNavigation($container, $items, $allItems);
        
        // Initialize video modal if needed
        initVideoModal();
        
        // Handle item click
        $allItems.on('click', function(e) {
            const $this = $(this);
            const postId = $this.data('post-id');
            const videoUrl = $this.data('video-url');
            
            // Check if click is on play button
            const isPlayButton = $(e.target).closest('.emargy-play-button').length > 0;
            
            // If clicked on play button and has video
            if (isPlayButton && videoUrl) {
                e.stopPropagation();
                openVideoModal(videoUrl, $this.find('.emargy-timeline-title').text());
                return;
            }
            
            // Skip if already center
            if ($this.hasClass('emargy-timeline-center-item')) {
                // Open the post
                if (postId && !isPlayButton) {
                    openPost(postId, openType);
                }
                return;
            }
            
            // Remove center class from all items
            $allItems.removeClass('emargy-timeline-center-item');
            
            // Add center class to clicked item
            $this.addClass('emargy-timeline-center-item');
            
            // Center the item with animation
            centerTimelineItem($items, $this);
            
            // Update indicator dots
            updateIndicators($container, $this);
        });
        
        // Handle navigation button clicks with throttling
        $navPrev.on('click', throttle(function(e) {
            e.stopPropagation();
            navigateTimeline($container, 'prev');
        }, 300));
        
        $navNext.on('click', throttle(function(e) {
            e.stopPropagation();
            navigateTimeline($container, 'next');
        }, 300));
        
        // Handle resize with debouncing
        $(window).on('resize', debounce(function() {
            // Recenter the center item
            if ($container.find('.emargy-timeline-center-item').length) {
                centerTimelineItem($items, $container.find('.emargy-timeline-center-item'));
            }
        }, 150));
        
        // Add keyboard navigation
        if ($container.hasClass('emargy-keyboard-enabled')) {
            $(document).on('keydown', function(e) {
                if ($container.is(':visible')) {
                    if (e.keyCode === 37) { // Left arrow
                        navigateTimeline($container, 'prev');
                    } else if (e.keyCode === 39) { // Right arrow
                        navigateTimeline($container, 'next');
                    }
                }
            });
        }
    }

    /**
     * Initialize lazy loading for timeline item images
     * 
     * @param {jQuery} $container The timeline container
     */
    function initLazyLoading($container) {
        const $items = $container.find('.emargy-timeline-item');
        const $visibleItems = $items.slice(0, 7); // Load first 7 visible items immediately
        const $lazyItems = $items.slice(7); // Remaining items are lazy loaded
        
        // Load visible items immediately
        $visibleItems.each(function() {
            const $item = $(this);
            const $img = $item.find('img[data-src]');
            
            if ($img.length) {
                $img.attr('src', $img.data('src')).removeAttr('data-src');
            }
        });
        
        // Setup Intersection Observer for lazy items
        if ('IntersectionObserver' in window) {
            const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const $target = $(entry.target);
                        const $img = $target.find('img[data-src]');
                        
                        if ($img.length) {
                            $img.attr('src', $img.data('src')).removeAttr('data-src');
                            $img.on('load', function() {
                                $img.addClass('loaded');
                            });
                        }
                        
                        // Stop observing this element
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            // Observe each lazy item
            $lazyItems.each(function() {
                lazyImageObserver.observe(this);
            });
        } else {
            // Fallback for browsers that don't support Intersection Observer
            $(window).on('scroll', debounce(function() {
                $lazyItems.each(function() {
                    const $item = $(this);
                    const $img = $item.find('img[data-src]');
                    
                    if ($img.length && isElementInViewport($item[0])) {
                        $img.attr('src', $img.data('src')).removeAttr('data-src');
                        // Remove from lazy items array
                        $lazyItems = $lazyItems.not($item);
                    }
                });
            }, 200));
        }
    }

    /**
     * Check if element is in viewport
     * 
     * @param {Element} el The element to check
     * @return {boolean} True if element is in viewport
     */
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    /**
     * Create indicator dots for navigation
     * 
     * @param {jQuery} $container The timeline container
     * @param {jQuery} $allItems All timeline items
     */
    function createIndicatorDots($container, $allItems) {
        // Create indicators container if it doesn't exist
        if ($container.find('.emargy-timeline-indicators').length === 0) {
            $container.append('<div class="emargy-timeline-indicators"></div>');
            
            const $indicators = $container.find('.emargy-timeline-indicators');
            
            // Create an indicator for each item
            $allItems.each(function(index) {
                const $indicator = $('<div class="emargy-timeline-indicator"></div>');
                $indicator.data('index', index);
                
                // Add click event to indicators
                $indicator.on('click', function() {
                    const clickedIndex = $(this).data('index');
                    const $targetItem = $allItems.eq(clickedIndex);
                    
                    // Make this item the center item
                    $allItems.removeClass('emargy-timeline-center-item');
                    $targetItem.addClass('emargy-timeline-center-item');
                    
                    // Center the item
                    centerTimelineItem($container.find('.emargy-timeline-items'), $targetItem);
                    
                    // Update indicators
                    updateIndicators($container, $targetItem);
                });
                
                $indicators.append($indicator);
            });
            
            // Set the initial active indicator
            const centerIndex = $allItems.index($container.find('.emargy-timeline-center-item'));
            $indicators.find('.emargy-timeline-indicator').eq(centerIndex).addClass('active');
        }
    }

    /**
     * Update indicator dots
     * 
     * @param {jQuery} $container The timeline container
     * @param {jQuery} $activeItem The active center item
     */
    function updateIndicators($container, $activeItem) {
        const $indicators = $container.find('.emargy-timeline-indicator');
        const $allItems = $container.find('.emargy-timeline-item');
        const activeIndex = $allItems.index($activeItem);
        
        $indicators.removeClass('active');
        $indicators.eq(activeIndex).addClass('active');
    }

    /**
     * Initialize drag and scroll navigation
     * 
     * @param {jQuery} $container The timeline container element
     * @param {jQuery} $items The timeline items wrapper
     * @param {jQuery} $allItems All timeline items
     */
    function initNavigation($container, $items, $allItems) {
        // Skip if drag is not enabled
        if (!$container.hasClass('emargy-drag-enabled')) {
            return;
        }
        
        let isDown = false;
        let startX;
        let scrollLeft;
        let isDragging = false;
        
        $items.on('mousedown touchstart', function(e) {
            isDown = true;
            isDragging = false;
            $items.addClass('active');
            
            if (e.type === 'touchstart') {
                startX = e.originalEvent.touches[0].pageX - $items.offset().left;
            } else {
                startX = e.pageX - $items.offset().left;
            }
            
            // Use a more reliable way to get current transformation
            const transform = getComputedStyle($items[0]).transform;
            const matrix = new WebKitCSSMatrix(transform === 'none' ? '' : transform);
            scrollLeft = -matrix.m41;
        });
        
        $(document).on('mouseup touchend', function() {
            if (isDown) {
                isDown = false;
                $items.removeClass('active');
                
                // If it was a real drag, snap to nearest item
                if (isDragging) {
                    snapToNearestItem($container);
                }
            }
        });
        
        $(document).on('mouseleave', function() {
            if (isDown) {
                isDown = false;
                $items.removeClass('active');
                
                if (isDragging) {
                    snapToNearestItem($container);
                }
            }
        });
        
        $items.on('mousemove touchmove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            
            let x;
            if (e.type === 'touchmove') {
                x = e.originalEvent.touches[0].pageX - $items.offset().left;
            } else {
                x = e.pageX - $items.offset().left;
            }
            
            const walk = (x - startX) * 1.5; // Scroll speed
            const newPosition = scrollLeft - walk;
            
            isDragging = true;
            
            // Use requestAnimationFrame for smoother animation
            requestAnimationFrame(function() {
                $items.css('transform', `translateX(${-newPosition}px)`);
            });
        });
        
        // Handle mousewheel with throttling
        if ($container.hasClass('emargy-mousewheel-enabled')) {
            $container.on('wheel', throttle(function(e) {
                e.preventDefault();
                
                // Determine direction and navigate
                if (e.originalEvent.deltaY > 0) {
                    navigateTimeline($container, 'next');
                } else {
                    navigateTimeline($container, 'prev');
                }
            }, 300));
        }
    }

    /**
     * Snap to the nearest item after dragging
     * 
     * @param {jQuery} $container The timeline container
     */
    function snapToNearestItem($container) {
        const $items = $container.find('.emargy-timeline-items');
        const $allItems = $container.find('.emargy-timeline-item');
        const containerWidth = $items.parent().width();
        
        // Get current position
        const transform = getComputedStyle($items[0]).transform;
        const matrix = new WebKitCSSMatrix(transform === 'none' ? '' : transform);
        const currentPosition = -matrix.m41;
        
        // Find the item closest to the center
        let closestItem = null;
        let closestDistance = Infinity;
        
        $allItems.each(function() {
            const $item = $(this);
            const itemOffset = $item.position().left;
            const itemCenter = itemOffset + ($item.outerWidth() / 2);
            const distanceToCenter = Math.abs((itemCenter - currentPosition) - (containerWidth / 2));
            
            if (distanceToCenter < closestDistance) {
                closestDistance = distanceToCenter;
                closestItem = $item;
            }
        });
        
        // Make this item the center item
        if (closestItem) {
            $allItems.removeClass('emargy-timeline-center-item');
            closestItem.addClass('emargy-timeline-center-item');
            
            // Center with animation
            centerTimelineItem($items, closestItem);
            
            // Update indicators
            updateIndicators($container, closestItem);
        }
    }

    /**
     * Center a timeline item in the container
     * 
     * @param {jQuery} $items The timeline items wrapper
     * @param {jQuery} $item The item to center
     */
    function centerTimelineItem($items, $item) {
        // Get container and item dimensions
        const containerWidth = $items.parent().width();
        const itemWidth = $item.outerWidth();
        const itemOffset = $item.position().left;
        
        // Calculate the position to center the item
        const scrollTo = itemOffset - (containerWidth / 2) + (itemWidth / 2);
        
        // Use requestAnimationFrame for smoother animation
        requestAnimationFrame(function() {
            $items.css('transform', `translateX(${-scrollTo}px)`);
        });
    }

    /**
     * Navigate the timeline (prev/next)
     * 
     * @param {jQuery} $container The timeline container
     * @param {string} direction Direction to navigate ('prev' or 'next')
     */
    function navigateTimeline($container, direction) {
        const $allItems = $container.find('.emargy-timeline-item');
        const $centerItem = $container.find('.emargy-timeline-center-item');
        const $items = $container.find('.emargy-timeline-items');
        
        // Find current center item index
        const centerIndex = $allItems.index($centerItem);
        let newIndex;
        
        if (direction === 'prev') {
            newIndex = centerIndex - 1;
            if (newIndex < 0) {
                newIndex = $allItems.length - 1; // Loop to the end
            }
        } else {
            newIndex = centerIndex + 1;
            if (newIndex >= $allItems.length) {
                newIndex = 0; // Loop to the beginning
            }
        }
        
        // Get the new center item
        const $newCenterItem = $allItems.eq(newIndex);
        
        // Remove center class from all items
        $allItems.removeClass('emargy-timeline-center-item');
        
        // Add center class to new center item
        $newCenterItem.addClass('emargy-timeline-center-item');
        
        // Center the new item with animation
        centerTimelineItem($items, $newCenterItem);
        
        // Update indicators
        updateIndicators($container, $newCenterItem);
        
        // Load lazy images if needed
        const $img = $newCenterItem.find('img[data-src]');
        if ($img.length) {
            $img.attr('src', $img.data('src')).removeAttr('data-src');
        }
        
        // Preload next and previous images
        const preloadIndices = [
            (newIndex + 1) % $allItems.length,
            (newIndex - 1 + $allItems.length) % $allItems.length
        ];
        
        preloadIndices.forEach(function(index) {
            const $preloadItem = $allItems.eq(index);
            const $preloadImg = $preloadItem.find('img[data-src]');
            
            if ($preloadImg.length) {
                $preloadImg.attr('src', $preloadImg.data('src')).removeAttr('data-src');
            }
        });
    }

    /**
     * Initialize video modal
     */
    function initVideoModal() {
        // Create modal if it doesn't exist
        if ($('#emargy-video-modal').length === 0) {
            $('body').append(`
                <div id="emargy-video-modal" class="emargy-video-modal">
                    <div class="emargy-video-modal-inner">
                        <div class="emargy-video-modal-title"></div>
                        <div class="emargy-video-modal-content"></div>
                        <div class="emargy-video-modal-close">&times;</div>
                    </div>
                </div>
            `);
            
            // Handle close button and outside click
            $(document).on('click', '.emargy-video-modal-close', closeVideoModal);
            $(document).on('click', '#emargy-video-modal', function(e) {
                if (e.target === this) {
                    closeVideoModal();
                }
            });
            
            // Handle ESC key to close modal
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    closeVideoModal();
                }
            });
        }
    }

    /**
     * Open video modal
     * 
     * @param {string} videoUrl The video URL to display
     * @param {string} title Optional title for the video
     */
    function openVideoModal(videoUrl, title) {
        const $modal = $('#emargy-video-modal');
        const $content = $modal.find('.emargy-video-modal-content');
        const $title = $modal.find('.emargy-video-modal-title');
        
        // Clear previous content
        $content.empty();
        
        // Set title if provided
        if (title) {
            $title.text(title).show();
        } else {
            $title.hide();
        }
        
        // Sanitize the URL
        if (!isValidUrl(videoUrl)) {
            console.error('Invalid video URL:', videoUrl);
            return;
        }
        
        // Determine video type and add appropriate element
        if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
            // Extract YouTube ID safely
            let youtubeId = '';
            const youtubeRegex = /(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
            const match = videoUrl.match(youtubeRegex);
            
            if (match && match[1]) {
                youtubeId = match[1];
                $content.html(`<iframe src="https://www.youtube.com/embed/${youtubeId}?autoplay=1" allow="autoplay" frameborder="0" allowfullscreen></iframe>`);
            }
        } else if (videoUrl.includes('vimeo.com')) {
            // Extract Vimeo ID safely
            const vimeoRegex = /vimeo\.com\/(?:video\/|channels\/\S+\/|groups\/[^\/]+\/videos\/|)(\d+)/;
            const match = videoUrl.match(vimeoRegex);
            
            if (match && match[1]) {
                const vimeoId = match[1];
                $content.html(`<iframe src="https://player.vimeo.com/video/${vimeoId}?autoplay=1" allow="autoplay" frameborder="0" allowfullscreen></iframe>`);
            }
        } else if (videoUrl.match(/\.(mp4|webm|ogg)$/i)) {
            // Direct video file
            $content.html(`<video controls autoplay><source src="${videoUrl}"></video>`);
        } else {
            // Default to iframe
            $content.html(`<iframe src="${videoUrl}" frameborder="0" allowfullscreen></iframe>`);
        }
        
        // Show modal
        $modal.css('display', 'flex').hide().fadeIn(300);
    }

    /**
     * Validate URL for security
     * 
     * @param {string} url The URL to validate
     * @return {boolean} True if URL is valid
     */
    function isValidUrl(url) {
        try {
            const parsedUrl = new URL(url);
            return ['http:', 'https:'].includes(parsedUrl.protocol);
        } catch (e) {
            return false;
        }
    }

    /**
     * Close video modal
     */
    function closeVideoModal() {
        const $modal = $('#emargy-video-modal');
        const $content = $modal.find('.emargy-video-modal-content');
        
        // Fade out modal
        $modal.fadeOut(300, function() {
            // Clear content to stop video playback
            $content.empty();
        });
    }

    /**
     * Open a post by ID
     * 
     * @param {number} postId The post ID to open
     * @param {string} openType How to open the post ('popup' or 'page')
     */
    function openPost(postId, openType) {
        if (!postId || isNaN(parseInt(postId))) {
            console.error('Invalid post ID:', postId);
            return;
        }
        
        if (openType === 'popup') {
            // Open in a popup (requires Elementor Pro or a custom popup implementation)
            if (typeof elementorProFrontend !== 'undefined' && 
                typeof elementorProFrontend.modules.popup !== 'undefined') {
                // Use Elementor Pro popup if available
                elementorProFrontend.modules.popup.showPopup({
                    id: postId
                });
            } else {
                // Fallback to a basic modal
                openBasicModal(postId);
            }
        } else if (openType === 'page') {
            // Open in a new page
            window.location.href = `${window.location.origin}/?p=${postId}`;
        }
    }

    /**
     * Open a basic modal for a post (fallback when Elementor Pro is not available)
     * 
     * @param {number} postId The post ID to show
     */
    function openBasicModal(postId) {
        // Create modal if it doesn't exist
        if ($('#emargy-modal').length === 0) {
            $('body').append(`
                <div id="emargy-modal" class="emargy-modal">
                    <div class="emargy-modal-content">
                        <span class="emargy-modal-close">&times;</span>
                        <div class="emargy-modal-body"></div>
                    </div>
                </div>
            `);
            
            // Add modal styles
            $('head').append(`
                <style>
                    .emargy-modal {
                        display: none;
                        position: fixed;
                        z-index: 9999;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        overflow: auto;
                        background-color: rgba(0,0,0,0.8);
                    }
                    .emargy-modal-content {
                        background-color: #fff;
                        margin: 5% auto;
                        padding: 30px;
                        border-radius: 8px;
                        max-width: 800px;
                        width: 90%;
                        position: relative;
                        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
                        transition: transform 0.3s ease;
                        transform: scale(0.9);
                    }
                    .emargy-modal.show .emargy-modal-content {
                        transform: scale(1);
                    }
                    .emargy-modal-close {
                        position: absolute;
                        right: 20px;
                        top: 15px;
                        font-size: 28px;
                        font-weight: bold;
                        cursor: pointer;
                        color: #999;
                        transition: color 0.3s ease;
                    }
                    .emargy-modal-close:hover {
                        color: #333;
                    }
                    .emargy-modal-body {
                        padding: 20px 0;
                    }
                    .emargy-modal-featured-image {
                        margin-bottom: 20px;
                    }
                    .emargy-modal-featured-image img {
                        width: 100%;
                        height: auto;
                        border-radius: 4px;
                    }
                    .emargy-modal-title {
                        font-size: 24px;
                        margin-bottom: 15px;
                        color: #333;
                    }
                    .emargy-modal-meta {
                        margin-top: 20px;
                        padding-top: 15px;
                        border-top: 1px solid #eee;
                        color: #666;
                        font-size: 14px;
                    }
                    .emargy-modal-read-more {
                        display: inline-block;
                        margin-top: 20px;
                        padding: 10px 20px;
                        background-color: #e22d4b;
                        color: #fff;
                        text-decoration: none;
                        border-radius: 4px;
                        transition: background-color 0.3s ease;
                    }
                    .emargy-modal-read-more:hover {
                        background-color: #c42742;
                    }
                </style>
            `);
            
            // Handle close button
            $(document).on('click', '.emargy-modal-close', function() {
                $('#emargy-modal').removeClass('show').fadeOut(300);
            });
            
            // Close when clicking outside the modal
            $(document).on('click', '#emargy-modal', function(e) {
                if (e.target === this) {
                    $(this).removeClass('show').fadeOut(300);
                }
            });
            
            // Handle ESC key
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    $('#emargy-modal').removeClass('show').fadeOut(300);
                }
            });
        }
        
        // Show loading state
        $('#emargy-modal .emargy-modal-body').html('<p>Loading...</p>');
        $('#emargy-modal').fadeIn(300).addClass('show');
        
        // Get nonce from global variable
        const nonce = typeof emargyTimelineVars !== 'undefined' && emargyTimelineVars.nonce ? 
            emargyTimelineVars.nonce : '';
            
        // Get post content via AJAX
        $.ajax({
            url: window.location.origin + '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'emargy_get_post_content',
                post_id: postId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#emargy-modal .emargy-modal-body').html(response.data);
                } else {
                    $('#emargy-modal .emargy-modal-body').html('<p>Error loading content: ' + (response.data || 'Unknown error') + '</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#emargy-modal .emargy-modal-body').html('<p>Error loading content. Please try again. Status: ' + status + ', Error: ' + error + '</p>');
            }
        });
    }

})(jQuery);