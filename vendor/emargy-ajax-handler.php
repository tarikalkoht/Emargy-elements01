<?php
/**
 * AJAX Handler for Emargy Elements
 *
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle AJAX requests with enhanced security and performance
 */
class Emargy_Enhanced_AJAX_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_emargy_get_posts', array($this, 'get_posts'));
        add_action('wp_ajax_nopriv_emargy_get_posts', array($this, 'get_posts'));
        
        add_action('wp_ajax_emargy_get_post_content', array($this, 'get_post_content'));
        add_action('wp_ajax_nopriv_emargy_get_post_content', array($this, 'get_post_content'));
        
        add_action('wp_ajax_emargy_get_video_info', array($this, 'get_video_info'));
        add_action('wp_ajax_nopriv_emargy_get_video_info', array($this, 'get_video_info'));
        
        add_action('wp_ajax_emargy_get_terms', array($this, 'get_taxonomy_terms'));
        
        // Setup rate limiting and security checks
        add_action('init', array($this, 'setup_security'));
    }

    /**
     * Setup security measures
     */
    public function setup_security() {
        // Only apply rate limiting on AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $this->check_rate_limit();
            $this->check_referrer();
        }
    }

    /**
     * Check rate limiting
     */
    private function check_rate_limit() {
        // Get client IP
        $client_ip = $this->get_client_ip();
        
        // Initialize rate limiting
        $rate_key = 'emargy_rate_' . md5($client_ip);
        $rate_limit = 60; // Requests per minute
        $rate_window = 60; // 1 minute window
        
        // Get current count and time
        $rate_count = get_transient($rate_key);
        
        if (false === $rate_count) {
            // First request, set to 1
            set_transient($rate_key, 1, $rate_window);
        } else if ($rate_count < $rate_limit) {
            // Increment request count
            set_transient($rate_key, $rate_count + 1, $rate_window);
        } else {
            // Too many requests
            $this->send_error_response('rate_limit_exceeded', 'Rate limit exceeded. Please try again later.', 429);
        }
    }

    /**
     * Check HTTP referrer
     */
    private function check_referrer() {
        // Skip for admin
        if (current_user_can('manage_options')) {
            return;
        }
        
        // Check referrer
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        if (!$referrer || parse_url($referrer, PHP_URL_HOST) !== parse_url(home_url(), PHP_URL_HOST)) {
            $this->send_error_response('invalid_referrer', 'Invalid request origin', 403);
        }
    }

    /**
     * Get posts via AJAX
     */
    public function get_posts() {
        $this->verify_nonce('emargy_timeline_nonce');
        
        // Parse request data
        $content_type = isset($_POST['content_type']) ? sanitize_text_field($_POST['content_type']) : 'post';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 10;
        $category = isset($_POST['category']) ? array_map('absint', (array)$_POST['category']) : array();
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_key($_POST['taxonomy']) : '';
        $terms = isset($_POST['terms']) ? array_map('absint', (array)$_POST['terms']) : array();
        
        // Build query args
        $args = array(
            'post_type' => $content_type,
            'posts_per_page' => $limit,
            'orderby' => $orderby,
            'order' => $order,
            'post_status' => 'publish',
        );
        
        // Category filter for posts
        if ($content_type === 'post' && !empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $category,
                )
            );
        }
        
        // Custom taxonomy filter
        if (!empty($taxonomy) && !empty($terms)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $terms,
                )
            );
        }
        
        // Get posts with caching if available
        if (function_exists('emargy_get_cached_query')) {
            $query = emargy_get_cached_query($args, 'ajax_timeline');
        } else {
            $query = new WP_Query($args);
        }
        
        // Prepare response data
        $posts_data = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                // Get thumbnail URL
                $thumbnail_url = '';
                if (has_post_thumbnail()) {
                    $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                }
                
                // Get video URL if available
                $video_url = '';
                $video_field = isset($_POST['video_field']) ? sanitize_text_field($_POST['video_field']) : 'video_url';
                if ($video_field) {
                    $video_url = get_post_meta(get_the_ID(), $video_field, true);
                }
                
                // Build post data
                $posts_data[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 15),
                    'permalink' => get_permalink(),
                    'thumbnail_url' => $thumbnail_url,
                    'video_url' => $video_url,
                    'date' => get_the_date(),
                );
            }
            wp_reset_postdata();
        }
        
        // Send response
        wp_send_json_success(array(
            'posts' => $posts_data,
            'found_posts' => $query->found_posts,
            'max_num_pages' => $query->max_num_pages,
        ));
    }

    /**
     * Get post content for modal/popup with enhanced security
     */
    public function get_post_content() {
        $this->verify_nonce('emargy_timeline_nonce');
        
        // Get post ID with validation
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
            return;
        }
        
        // Get post with permission check
        $post = get_post($post_id);
        
        if (!$post) {
            wp_send_json_error(array('message' => 'Post not found'));
            return;
        }
        
        // Check post status and permissions
        if ($post->post_status !== 'publish' && !current_user_can('read_post', $post_id)) {
            wp_send_json_error(array('message' => 'You do not have permission to view this content'));
            return;
        }
        
        // Get video info if needed
        $include_video = isset($_POST['include_video']) ? (bool)$_POST['include_video'] : false;
        $video_field = isset($_POST['video_field']) ? sanitize_text_field($_POST['video_field']) : 'video_url';
        $video_url = '';
        
        if ($include_video && $video_field) {
            $video_url = get_post_meta($post_id, $video_field, true);
            
            // Validate video URL
            if (!empty($video_url) && !$this->is_valid_url($video_url)) {
                $video_url = '';
            }
        }
        
        // Prepare the content with enhanced output
        ob_start();
        
        echo '<div class="emargy-modal-post">';
        
        // Title
        echo '<h2 class="emargy-modal-title">' . esc_html($post->post_title) . '</h2>';
        
        // Video (if available)
        if ($include_video && $video_url) {
            echo '<div class="emargy-modal-video">';
            
            if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                // Extract YouTube ID
                $youtube_id = $this->get_youtube_id($video_url);
                
                if ($youtube_id) {
                    echo '<div class="emargy-video-responsive">';
                    echo '<iframe src="https://www.youtube.com/embed/' . esc_attr($youtube_id) . '?autoplay=0" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" title="' . esc_attr($post->post_title) . '"></iframe>';
                    echo '</div>';
                }
            } elseif (strpos($video_url, 'vimeo.com') !== false) {
                // Extract Vimeo ID
                $vimeo_id = $this->get_vimeo_id($video_url);
                
                if ($vimeo_id) {
                    echo '<div class="emargy-video-responsive">';
                    echo '<iframe src="https://player.vimeo.com/video/' . esc_attr($vimeo_id) . '?autoplay=0" frameborder="0" allowfullscreen allow="autoplay; fullscreen; picture-in-picture" title="' . esc_attr($post->post_title) . '"></iframe>';
                    echo '</div>';
                }
            } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $video_url)) {
                // Direct video file
                echo '<div class="emargy-video-responsive">';
                echo '<video controls><source src="' . esc_url($video_url) . '" type="video/' . esc_attr(pathinfo($video_url, PATHINFO_EXTENSION)) . '"></video>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        // Featured image (if no video or video not available)
        elseif (has_post_thumbnail($post_id)) {
            echo '<div class="emargy-modal-featured-image">';
            echo get_the_post_thumbnail($post_id, 'large', array('alt' => esc_attr($post->post_title)));
            echo '</div>';
        }
        
        // Content
        echo '<div class="emargy-modal-content">';
        echo apply_filters('the_content', $post->post_content);
        echo '</div>';
        
        // Meta
        echo '<div class="emargy-modal-meta">';
        
        // Date
        echo '<p class="emargy-modal-date">' . esc_html__('Published on', 'emargy-elements') . ' ' . get_the_date('', $post_id) . '</p>';
        
        // Author
        echo '<p class="emargy-modal-author">' . esc_html__('By', 'emargy-elements') . ' ' . get_the_author_meta('display_name', $post->post_author) . '</p>';
        
        // Categories for posts
        if ($post->post_type === 'post') {
            $categories = get_the_category($post_id);
            if (!empty($categories)) {
                echo '<p class="emargy-modal-categories">' . esc_html__('Categories:', 'emargy-elements') . ' ';
                $cat_links = array();
                foreach ($categories as $category) {
                    $cat_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                }
                echo implode(', ', $cat_links);
                echo '</p>';
            }
        }
        
        // Custom taxonomies
        $taxonomies = get_object_taxonomies($post->post_type, 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->name !== 'category' && $taxonomy->name !== 'post_tag' && $taxonomy->public) {
                $terms = get_the_terms($post_id, $taxonomy->name);
                
                if (!empty($terms) && !is_wp_error($terms)) {
                    echo '<p class="emargy-modal-taxonomy">' . esc_html($taxonomy->label) . ': ';
                    $term_links = array();
                    foreach ($terms as $term) {
                        $term_links[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                    }
                    echo implode(', ', $term_links);
                    echo '</p>';
                }
            }
        }
        
        echo '</div>';
        
        // Read more link
        echo '<a href="' . esc_url(get_permalink($post_id)) . '" class="emargy-modal-read-more">' . 
            esc_html__('Read full post', 'emargy-elements') . 
            '<span class="screen-reader-text"> ' . esc_html($post->post_title) . '</span>' . 
        '</a>';
        
        echo '</div>';
        
        // Get buffered content
        $content = ob_get_clean();
        
        // Add modal styles
        $content .= '<style>
            .emargy-video-responsive {
                position: relative;
                padding-bottom: 56.25%; /* 16:9 ratio */
                height: 0;
                overflow: hidden;
                margin-bottom: 20px;
                background-color: #000;
            }
            .emargy-video-responsive iframe,
            .emargy-video-responsive video {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
            .screen-reader-text {
                border: 0;
                clip: rect(1px, 1px, 1px, 1px);
                clip-path: inset(50%);
                height: 1px;
                margin: -1px;
                overflow: hidden;
                padding: 0;
                position: absolute;
                width: 1px;
                word-wrap: normal !important;
            }
        </style>';
        
        wp_send_json_success($content);
    }

    /**
     * Get video info with enhanced validation
     */
    public function get_video_info() {
        $this->verify_nonce('emargy_timeline_nonce');
        
        // Get and validate video URL
        $video_url = isset($_POST['video_url']) ? esc_url_raw($_POST['video_url']) : '';
        
        if (!$video_url || !$this->is_valid_url($video_url)) {
            wp_send_json_error(array('message' => 'Invalid video URL'));
            return;
        }
        
        // Prepare response data
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
     * Get taxonomy terms with proper validation
     */
    public function get_taxonomy_terms() {
        $this->verify_nonce('emargy_timeline_nonce');
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        // Get taxonomy with validation
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_key($_POST['taxonomy']) : '';
        
        if (!$taxonomy || !taxonomy_exists($taxonomy)) {
            wp_send_json_error(array('message' => 'Invalid taxonomy'));
            return;
        }
        
        // Get terms
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (is_wp_error($terms)) {
            wp_send_json_error(array('message' => $terms->get_error_message()));
            return;
        }
        
        // Prepare response data
        $options = array();
        foreach ($terms as $term) {
            $options[$term->term_id] = $term->name;
        }
        
        wp_send_json_success($options);
    }

    /**
     * Helper method to verify nonce
     *
     * @param string $action Nonce action
     */
    private function verify_nonce($action) {
        if (!check_ajax_referer($action, 'nonce', false)) {
            $this->send_error_response('invalid_nonce', 'Security check failed. Please refresh and try again.', 403);
        }
    }

    /**
     * Send error response with proper status code
     *
     * @param string $code Error code
     * @param string $message Error message
     * @param int $status HTTP status code
     */
    private function send_error_response($code, $message, $status = 400) {
        status_header($status);
        wp_send_json_error(array(
            'code' => $code,
            'message' => $message
        ));
        exit;
    }

    /**
     * Get client IP address with better detection
     * 
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }
        
        // Default to server IP if no client IP found
        return '127.0.0.1';
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
     * Validate URL with enhanced security
     *
     * @param string $url URL to validate
     * @return bool True if URL is valid
     */
    private function is_valid_url($url) {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Get URL components
        $parsed_url = parse_url($url);
        
        // Check protocol (only allow http and https)
        if (!isset($parsed_url['scheme']) || !in_array($parsed_url['scheme'], array('http', 'https'))) {
            return false;
        }
        
        // Check for allowed domains for videos
        $allowed_domains = array(
            'youtube.com',
            'youtu.be',
            'vimeo.com',
            // Add more trusted domains as needed
        );
        
        // If it's a video URL, check if domain is allowed
        if (preg_match('/(youtube\.com|youtu\.be|vimeo\.com)/', $url)) {
            $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
            $is_allowed = false;
            
            foreach ($allowed_domains as $domain) {
                if (strpos($host, $domain) !== false) {
                    $is_allowed = true;
                    break;
                }
            }
            
            if (!$is_allowed) {
                return false;
            }
        }
        
        return true;
    }
}

// Initialize the class
new Emargy_Enhanced_AJAX_Handler();