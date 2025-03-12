<?php
/**
 * Timeline Showcase Shortcode Template
 *
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue necessary styles and scripts
wp_enqueue_style('emargy-timeline-style');
wp_enqueue_script('emargy-timeline-script');

// Prepare query arguments
$args = [
    'post_type' => $atts['type'],
    'posts_per_page' => intval($atts['limit']),
    'order' => 'DESC',
];

// Run the query
$timeline_query = new \WP_Query($args);

// Container classes
$container_classes = [
    'emargy-timeline-container',
    'emargy-timeline-style-' . $atts['layout'],
];

// Get all posts and calculate middle item
$posts = $timeline_query->posts;
$total_posts = count($posts);
$middle_index = floor($total_posts / 2);
?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
     data-open-type="page"
     data-center-size="<?php echo esc_attr($atts['featured_size']); ?>">
    
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
                    $is_center = ($counter - 1) === $middle_index;
                    if ($is_center) {
                        $item_classes[] = 'emargy-timeline-center-item';
                    }
                    
                    // Format the counter with leading zero
                    $formatted_counter = sprintf("%02d", $counter);
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>"
                         data-post-id="<?php the_ID(); ?>">
                        <div class="emargy-timeline-item-inner">
                            <div class="emargy-timeline-thumbnail">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title_attribute(); ?>">
                                <?php else : ?>
                                    <div class="emargy-no-thumbnail"></div>
                                <?php endif; ?>
                                
                                <div class="emargy-play-button">
                                    <i class="eicon-play"></i>
                                </div>
                            </div>
                            
                            <div class="emargy-timeline-content">
                                <h4 class="emargy-timeline-title"><?php the_title(); ?></h4>
                            </div>
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
</div>