/**
 * Emargy Enhanced Timeline Showcase Widget JavaScript
 * Improved with smoother animations and video support
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize all timeline instances
        initializeTimelines();
        
        // Initialize when Elementor frontend is loaded (for edit mode)
        $(window).on('elementor/frontend/init', function() {
            if (elementorFrontend.isEditMode()) {
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
        
        // Handle navigation button clicks
        $navPrev.on('click', function(e) {
            e.stopPropagation();
            navigateTimeline($container, 'prev');
        });
        
        $navNext.on('click', function(e) {
            e.stopPropagation();
            navigateTimeline($container, 'next');
        });
        
        // Handle resize
        $(window).on('resize', function() {
            // Recenter the center item
            if ($container.find('.emargy-timeline-center-item').length) {
                centerTimelineItem($items, $container.find('.emargy-timeline-center-item'));
            }
        });
        
        // Add keyboard navigation
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
            
            const matrix = new WebKitCSSMatrix(getComputedStyle($items[0]).transform);
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
            
            // Update transform instead of scrollLeft for smoother animation
            $items.css('transform', `translateX(${-newPosition}px)`);
        });
        
        // Handle mousewheel
        $container.on('wheel', function(e) {
            e.preventDefault();
            
            // Determine direction and navigate
            if (e.originalEvent.deltaY > 0) {
                navigateTimeline($container, 'next');
            } else {
                navigateTimeline($container, 'prev');
            }
        });
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
        const matrix = new WebKitCSSMatrix(getComputedStyle($items[0]).transform);
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
        
        // Animate to the position
        $items.css('transform', `translateX(${-scrollTo}px)`);
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
        
        // Determine video type and add appropriate element
        if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
            // Extract YouTube ID
            let youtubeId = '';
            if (videoUrl.includes('v=')) {
                youtubeId = videoUrl.split('v=')[1];
                const ampIndex = youtubeId.indexOf('&');
                if (ampIndex !== -1) {
                    youtubeId = youtubeId.substring(0, ampIndex);
                }
            } else if (videoUrl.includes('youtu.be')) {
                youtubeId = videoUrl.split('youtu.be/')[1];
            }
            
            if (youtubeId) {
                $content.html(`<iframe src="https://www.youtube.com/embed/${youtubeId}?autoplay=1" frameborder="0" allowfullscreen></iframe>`);
            }
        } else if (videoUrl.includes('vimeo.com')) {
            // Extract Vimeo ID
            const vimeoId = videoUrl.split('vimeo.com/')[1];
            if (vimeoId) {
                $content.html(`<iframe src="https://player.vimeo.com/video/${vimeoId}?autoplay=1" frameborder="0" allowfullscreen></iframe>`);
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
        console.log('Opening post', postId, openType); // Debug line to check if this function is being called
        
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
        } else {
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
        
        // Get post content via AJAX
        $.ajax({
            url: window.location.origin + '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'emargy_get_post_content',
                post_id: postId,
                nonce: emargyTimelineVars.nonce || ''
            },
            beforeSend: function() {
                $('#emargy-modal .emargy-modal-body').html('<p>Loading...</p>');
                $('#emargy-modal').fadeIn(300).addClass('show');
            },
            success: function(response) {
                if (response.success) {
                    $('#emargy-modal .emargy-modal-body').html(response.data);
                } else {
                    $('#emargy-modal .emargy-modal-body').html('<p>Error loading content. Please try again.</p>');
                }
            },
            error: function() {
                $('#emargy-modal .emargy-modal-body').html('<p>Error loading content. Please try again.</p>');
            }
        });
    }

})(jQuery);