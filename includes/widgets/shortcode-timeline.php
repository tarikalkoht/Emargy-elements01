<?php
/**
 * Timeline Showcase Shortcode Template - Enhanced Version
 *
 * @since 2.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue necessary styles and scripts
wp_enqueue_style('emargy-timeline-style');
wp_enqueue_script('emargy-timeline-script');

// Prepare category filter
$category_filter = '';
if (!empty($atts['category'])) {
    $category_filter = array_map('trim', explode(',', $atts['category']));
}

// Prepare query arguments
$args = [
    'post_type' => $atts['type'],
    'posts_per_page' => intval($atts['limit']),
    'orderby' => $atts['order_by'],
    'order' => $atts['order'],
];

// Add category filter if set
if (!empty($category_filter)) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => $category_filter,
        ]
    ];
}

// Run the query
$timeline_query = new \WP_Query($args);

// Container classes
$container_classes = [
    'emargy-timeline-container',
    'emargy-timeline-style-' . $atts['layout'],
    'emargy-hover-enabled',
    'emargy-drag-enabled',
];

// Custom class
if (!empty($atts['custom_class'])) {
    $container_classes[] = $atts['custom_class'];
}

// Get all posts and determine initial center item
$posts = $timeline_query->posts;
$total_posts = count($posts);

// Set the initial center item based on shortcode parameter
switch ($atts['center_item']) {
    case 'first':
        $center_index = 0;
        break;
    case 'last':
        $center_index = $total_posts - 1;
        break;
    case 'middle':
    default:
        $center_index = floor($total_posts / 2);
        break;
}

// Inline styles for background color
$inline_styles = '';
if (!empty($atts['bg_color'])) {
    $inline_styles = 'style="background-color: ' . esc_attr($atts['bg_color']) . ';"';
}
?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
     data-open-type="popup"
     data-center-size="<?php echo esc_attr($atts['featured_size']); ?>"
     <?php echo $inline_styles; ?>>
    
    <div class="emargy-timeline-nav emargy-nav-prev">
        <i class="eicon-chevron-left"></i>
    </div>

    <div class="emargy-timeline-items-wrapper">
        <div class="emargy-timeline-items">
            <?php 
            $counter = 1;
            if ($timeline_query->have_posts()) :
                while ($timeline_query->have_posts()) : $timeline_query->the_post();
                    $item_classes = ['emargy-timeline-item'];
                    
                    // Is this the center item?
                    $is_center = ($counter - 1) === $center_index;
                    if ($is_center) {
                        $item_classes[] = 'emargy-timeline-center-item';
                    }
                    
                    // Format the counter with leading zero
                    $formatted_counter = sprintf("%02d", $counter);
                    
                    // Get video URL if enabled
                    $video_url = '';
                    if ($atts['enable_video'] === 'yes') {
                        $video_field = $atts['video_field'] ? $atts['video_field'] : 'video_url';
                        $video_url = get_post_meta(get_the_ID(), $video_field, true);
                    }
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>"
                         data-post-id="<?php the_ID(); ?>"
                         <?php if ($video_url) : ?>
                         data-video-url="<?php echo esc_url($video_url); ?>"
                         <?php endif; ?>>
                        <div class="emargy-timeline-item-inner">
                            <div class="emargy-timeline-thumbnail">
                                <?php 
                                // Display featured image, video thumbnail, or placeholder
                                if (has_post_thumbnail()) : 
                                    echo get_the_post_thumbnail(get_the_ID(), 'medium_large');
                                elseif ($video_url) :
                                    // Try to get video thumbnail based on provider
                                    $thumbnail_url = '';
                                    
                                    // YouTube
                                    if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                        preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches);
                                        $youtube_id = isset($matches[1]) ? $matches[1] : '';
                                        if ($youtube_id) {
                                            $thumbnail_url = 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg';
                                        }
                                    }
                                    // Vimeo
                                    elseif (strpos($video_url, 'vimeo.com') !== false) {
                                        preg_match('/vimeo\.com\/(?:video\/|channels\/\S+\/|groups\/[^\/]+\/videos\/|)(\d+)/', $video_url, $matches);
                                        $vimeo_id = isset($matches[1]) ? $matches[1] : '';
                                        if ($vimeo_id) {
                                            $vimeo_data = wp_remote_get('https://vimeo.com/api/v2/video/' . $vimeo_id . '.json');
                                            if (!is_wp_error($vimeo_data) && wp_remote_retrieve_response_code($vimeo_data) === 200) {
                                                $vimeo_data = json_decode(wp_remote_retrieve_body($vimeo_data));
                                                if (isset($vimeo_data[0]->thumbnail_large)) {
                                                    $thumbnail_url = $vimeo_data[0]->thumbnail_large;
                                                }
                                            }
                                        }
                                    }
                                    
                                    if ($thumbnail_url) :
                                    ?>
                                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php the_title_attribute(); ?>">
                                    <?php else : ?>
                                        <div class="emargy-no-thumbnail"></div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="emargy-no-thumbnail"></div>
                                <?php endif; ?>
                                
                                <?php if ($video_url) : ?>
                                <div class="emargy-play-button">
                                    <i class="eicon-play"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($atts['thumbnail_mode'] !== 'image_only') : ?>
                            <div class="emargy-timeline-content">
                                <h4 class="emargy-timeline-title"><?php the_title(); ?></h4>
                                
                                <?php if ($atts['thumbnail_mode'] === 'image_excerpt') : ?>
                                <div class="emargy-timeline-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 12); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="emargy-timeline-number"><?php echo esc_html($formatted_counter); ?></div>
                    </div>
                    <?php
                    $counter++;
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
        
        <div class="emargy-timeline-line <?php echo esc_attr('emargy-timeline-' . $atts['layout']); ?>"></div>
    </div>
    
    <div class="emargy-timeline-nav emargy-nav-next">
        <i class="eicon-chevron-right"></i>
    </div>
    
    <!-- Indicator dots -->
    <div class="emargy-timeline-indicators">
        <?php for ($i = 0; $i < $total_posts; $i++) : ?>
            <div class="emargy-timeline-indicator <?php echo ($i === $center_index) ? 'active' : ''; ?>"></div>
        <?php endfor; ?>
    </div>
</div>

<!-- Add animation for play button pulse -->
<style>
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }
</style>