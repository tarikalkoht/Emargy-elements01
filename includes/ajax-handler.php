<?php
/**
 * AJAX Handlers for the Emargy Elements plugin
 *
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle AJAX requests
 */
class Emargy_AJAX_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_emargy_get_post_content', array($this, 'get_post_content'));
        add_action('wp_ajax_nopriv_emargy_get_post_content', array($this, 'get_post_content'));
    }

    /**
     * Get post content for modal/popup
     */
    public function get_post_content() {
        // Check for nonce
        check_ajax_referer('emargy_timeline_nonce', 'nonce');

        // Get post ID
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
            return;
        }

        // Get the post
        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error('Post not found');
            return;
        }

        // Prepare the content
        $content = '<div class="emargy-modal-post">';
        
        // Title
        $content .= '<h2 class="emargy-modal-title">' . esc_html($post->post_title) . '</h2>';
        
        // Featured image
        if (has_post_thumbnail($post_id)) {
            $content .= '<div class="emargy-modal-featured-image">';
            $content .= get_the_post_thumbnail($post_id, 'large');
            $content .= '</div>';
        }
        
        // Content
        $content .= '<div class="emargy-modal-content">';
        $content .= apply_filters('the_content', $post->post_content);
        $content .= '</div>';
        
        // Meta
        $content .= '<div class="emargy-modal-meta">';
        $content .= '<p class="emargy-modal-date">' . esc_html__('Published on', 'emargy-elements') . ' ' . get_the_date('', $post_id) . '</p>';
        
        // Categories
        $categories = get_the_category($post_id);
        if (!empty($categories)) {
            $content .= '<p class="emargy-modal-categories">' . esc_html__('Categories:', 'emargy-elements') . ' ';
            $cat_links = array();
            foreach ($categories as $category) {
                $cat_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
            }
            $content .= implode(', ', $cat_links);
            $content .= '</p>';
        }
        
        $content .= '</div>';
        
        // Read more link
        $content .= '<a href="' . esc_url(get_permalink($post_id)) . '" class="emargy-modal-read-more">' . esc_html__('Read full post', 'emargy-elements') . '</a>';
        
        $content .= '</div>';

        wp_send_json_success($content);
    }
}

// Initialize the class
new Emargy_AJAX_Handler();