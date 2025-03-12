/**
 * Emargy Timeline Showcase Widget JavaScript
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
        const $centerItem = $container.find('.emargy-timeline-center-item');
        const $navPrev = $container.find('.emargy-nav-prev');
        const $navNext = $container.find('.emargy-nav-next');
        const openType = $container.data('open-type');
        
        // Set initial position to center the center item
        if ($centerItem.length) {
            centerTimelineItem($items, $centerItem);
        }
        
        // Initialize navigation
        initNavigation($container, $items, $allItems);
        
        // Handle item click
        $allItems.on('click', function() {
            const $this = $(this);
            const postId = $this.data('post-id');
            
            // Skip if already center
            if ($this.hasClass('emargy-timeline-center-item')) {
                // Open the post
                if (postId) {
                    openPost(postId, openType);
                }
                return;
            }
            
            // Remove center class from all items
            $allItems.removeClass('emargy-timeline-center-item');
            
            // Add center class to clicked item
            $this.addClass('emargy-timeline-center-item');
            
            // Center the item
            centerTimelineItem($items, $this);
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
        
        $items.on('mousedown', function(e) {
            isDown = true;
            $items.addClass('active');
            startX = e.pageX - $items.offset().left;
            scrollLeft = $items.parent().scrollLeft();
        });
        
        $(document).on('mouseup', function() {
            isDown = false;
            $items.removeClass('active');
        });
        
        $(document).on('mouseleave', function() {
            isDown = false;
            $items.removeClass('active');
        });
        
        $items.on('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - $items.offset().left;
            const walk = (x - startX) * 2; // Scroll speed
            const newPosition = scrollLeft - walk;
            
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
        
        // Center the new item
        centerTimelineItem($items, $newCenterItem);
    }

    /**
     * Open a post by ID
     * 
     * @param {number} postId The post ID to open
     * @param {string} openType How to open the post ('popup' or 'page')
     */
    function openPost(postId, openType) {
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
                        margin: 10% auto;
                        padding: 20px;
                        border-radius: 5px;
                        max-width: 800px;
                        width: 90%;
                        position: relative;
                    }
                    .emargy-modal-close {
                        position: absolute;
                        right: 20px;
                        top: 10px;
                        font-size: 28px;
                        font-weight: bold;
                        cursor: pointer;
                    }
                    .emargy-modal-body {
                        padding: 20px 0;
                    }
                </style>
            `);
            
            // Handle close button
            $(document).on('click', '.emargy-modal-close', function() {
                $('#emargy-modal').hide();
            });
            
            // Close when clicking outside the modal
            $(document).on('click', '#emargy-modal', function(e) {
                if (e.target === this) {
                    $(this).hide();
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
                $('#emargy-modal').show();
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