<?php
/**
 * AJAX Handler for Emargy Elements
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
        
        add_action('wp_ajax_emargy_get_video_info', array($this, 'get_video_info'));
        add_action('wp_ajax_nopriv_emargy_get_video_info', array($this, 'get_video_info'));
        
        add_action('wp_ajax_emargy_get_terms', array($this, 'get_taxonomy_terms'));
    }

    /**
     * Get post content for modal/popup
     */
    public function get_post_content() {
        // Verify nonce with proper error handling
        if (!check_ajax_referer('emargy_timeline_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed. Please refresh the page and try again.');
            return;
        }

        // Get post ID
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

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

        // Ensure the post is published and user has permission to view it
        if ($post->post_status !== 'publish' && !current_user_can('read_post', $post_id)) {
            wp_send_json_error('You do not have permission to view this post');
            return;
        }

        // Check if we should include video
        $include_video = isset($_POST['include_video']) ? (bool)$_POST['include_video'] : false;
        $video_field = isset($_POST['video_field']) ? sanitize_text_field($_POST['video_field']) : 'video_url';
        
        // Get video URL if specified
        $video_url = '';
        if ($include_video) {
            $video_url = get_post_meta($post_id, $video_field, true);
            
            // Validate video URL if provided
            if (!empty($video_url) && !$this->is_valid_url($video_url)) {
                $video_url = '';
            }
        }

        // Prepare the content
        $content = '<div class="emargy-modal-post">';
        
        // Title
        $content .= '<h2 class="emargy-modal-title">' . esc_html($post->post_title) . '</h2>';
        
        // Video (if available)
        if ($include_video && $video_url) {
            $content .= '<div class="emargy-modal-video">';
            
            if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                // Extract YouTube ID
                $youtube_id = $this->get_youtube_id($video_url);
                
                if ($youtube_id) {
                    $content .= '<div class="emargy-video-responsive">';
                    $content .= '<iframe src="https://www.youtube.com/embed/' . esc_attr($youtube_id) . '?autoplay=0" frameborder="0" allowfullscreen></iframe>';
                    $content .= '</div>';
                }
            } elseif (strpos($video_url, 'vimeo.com') !== false) {
                // Extract Vimeo ID
                $vimeo_id = $this->get_vimeo_id($video_url);
                
                if ($vimeo_id) {
                    $content .= '<div class="emargy-video-responsive">';
                    $content .= '<iframe src="https://player.vimeo.com/video/' . esc_attr($vimeo_id) . '?autoplay=0" frameborder="0" allowfullscreen></iframe>';
                    $content .= '</div>';
                }
            } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $video_url)) {
                // Direct video file
                $content .= '<div class="emargy-video-responsive">';
                $content .= '<video controls><source src="' . esc_url($video_url) . '"></video>';
                $content .= '</div>';
            }
            
            $content .= '</div>';
        }
        // Featured image (if no video or video not available)
        elseif (has_post_thumbnail($post_id)) {
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
        
        // Custom taxonomies
        $post_type = get_post_type($post_id);
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->name !== 'category' && $taxonomy->name !== 'post_tag') {
                $terms = get_the_terms($post_id, $taxonomy->name);
                
                if (!empty($terms) && !is_wp_error($terms)) {
                    $content .= '<p class="emargy-modal-taxonomy">' . esc_html($taxonomy->label) . ': ';
                    $term_links = array();
                    foreach ($terms as $term) {
                        $term_links[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                    }
                    $content .= implode(', ', $term_links);
                    $content .= '</p>';
                }
            }
        }
        
        $content .= '</div>';
        
        // Read more link
        $content .= '<a href="' . esc_url(get_permalink($post_id)) . '" class="emargy-modal-read-more">' . esc_html__('Read full post', 'emargy-elements') . '</a>';
        
        $content .= '</div>';

        // Add modal styles
        $content .= '<style>
            .emargy-video-responsive {
                position: relative;
                padding-bottom: 56.25%; /* 16:9 ratio */
                height: 0;
                overflow: hidden;
                margin-bottom: 20px;
            }
            .emargy-video-responsive iframe,
            .emargy-video-responsive video {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
        </style>';

        wp_send_json_success($content);
    }
    
    /**
     * Get video information (title, thumbnail, etc.)
     */
    public function get_video_info() {
        // Verify nonce with proper error handling
        if (!check_ajax_referer('emargy_timeline_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed.');
            return;
        }
        
        // Get video URL
        $video_url = isset($_POST['video_url']) ? esc_url_raw($_POST['video_url']) : '';
        
        if (!$video_url || !$this->is_valid_url($video_url)) {
            wp_send_json_error('Invalid video URL');
            return;
        }
        
        $video_info = array(
            'thumbnail' => '',
            'title' => '',
            'provider' => '',
        );
        
        // YouTube
        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
            $youtube_id = $this->get_youtube_id($video_url);
            
            if ($youtube_id) {
                $video_info['thumbnail'] = 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg';
                $video_info['provider'] = 'youtube';
                
                // Try to get title (requires API key, so this is optional)
                $api_key = get_option('emargy_youtube_api_key');
                if ($api_key) {
                    $api_url = add_query_arg(
                        array(
                            'id' => $youtube_id,
                            'key' => $api_key,
                            'part' => 'snippet',
                            'fields' => 'items(snippet(title))'
                        ),
                        'https://www.googleapis.com/youtube/v3/videos'
                    );
                    
                    $response = wp_safe_remote_get($api_url, array(
                        'timeout' => 15,
                        'sslverify' => true
                    ));
                    
                    if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                        $api_body = json_decode(wp_remote_retrieve_body($response), true);
                        
                        if (isset($api_body['items'][0]['snippet']['title'])) {
                            $video_info['title'] = sanitize_text_field($api_body['items'][0]['snippet']['title']);
                        }
                    }
                }
            }
        }
        // Vimeo
        elseif (strpos($video_url, 'vimeo.com') !== false) {
            $vimeo_id = $this->get_vimeo_id($video_url);
            
            if ($vimeo_id) {
                $video_info['provider'] = 'vimeo';
                
                // Get video info from Vimeo API
                $api_url = 'https://vimeo.com/api/v2/video/' . $vimeo_id . '.json';
                $response = wp_safe_remote_get($api_url, array(
                    'timeout' => 15,
                    'sslverify' => true
                ));
                
                if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                    $api_body = json_decode(wp_remote_retrieve_body($response), true);
                    
                    if (isset($api_body[0])) {
                        $video_info['thumbnail'] = esc_url_raw($api_body[0]['thumbnail_large']);
                        $video_info['title'] = sanitize_text_field($api_body[0]['title']);
                    }
                }
            }
        }
        
        wp_send_json_success($video_info);
    }
    
    /**
     * Get taxonomy terms for dynamic control
     */
    public function get_taxonomy_terms() {
        // Check for nonce and permissions
        if (!check_ajax_referer('emargy_timeline_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed.');
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Get taxonomy
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
        
        if (!$taxonomy || !taxonomy_exists($taxonomy)) {
            wp_send_json_error('Invalid taxonomy');
            return;
        }
        
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (is_wp_error($terms)) {
            wp_send_json_error($terms->get_error_message());
            return;
        }
        
        $options = array();
        foreach ($terms as $term) {
            $options[$term->term_id] = $term->name;
        }
        
        wp_send_json_success($options);
    }

    /**
     * Extract YouTube video ID from URL
     *
     * @param string $url YouTube URL
     * @return string|false YouTube ID or false if not found
     */
    private function get_youtube_id($url) {
        $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Extract Vimeo video ID from URL
     *
     * @param string $url Vimeo URL
     * @return string|false Vimeo ID or false if not found
     */
    private function get_vimeo_id($url) {
        $pattern = '/vimeo\.com\/(?:video\/|channels\/\S+\/|groups\/[^\/]+\/videos\/|)(\d+)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Validate URL
     *
     * @param string $url URL to validate
     * @return bool True if URL is valid
     */
    private function is_valid_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

// Initialize the class
new Emargy_AJAX_Handler();