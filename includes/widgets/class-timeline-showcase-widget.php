<?php
/**
 * Timeline Showcase Widget
 *
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Emargy_Timeline_Showcase_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget name.
     */
    public function get_name() {
        return 'emargy_timeline_showcase';
    }

    /**
     * Get widget title.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__('Timeline Showcase Widget', 'emargy-elements');
    }

    /**
     * Get widget icon.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-time-line';
    }

    /**
     * Get widget categories.
     *
     * @since 1.0.0
     * @access public
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['emargy', 'basic'];
    }

    /**
     * Get widget keywords.
     *
     * @since 1.0.0
     * @access public
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return ['timeline', 'showcase', 'posts', 'projects', 'services', 'emargy'];
    }

    /**
     * Register widget controls.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {

        /**
         * Content Settings
         */
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Content Settings', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'content_type',
            [
                'label' => esc_html__('Content Type', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'post',
                'options' => $this->get_post_types(),
            ]
        );

        $this->add_control(
            'items_number',
            [
                'label' => esc_html__('Number of Items Displayed', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 3,
                'max' => 15,
                'step' => 1,
                'default' => 11,
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => esc_html__('Category Filtering', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_all_categories(),
                'multiple' => true,
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__('Sorting Order', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => esc_html__('Ascending', 'emargy-elements'),
                    'DESC' => esc_html__('Descending', 'emargy-elements'),
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * Layout Settings
         */
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Layout Settings', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'timeline_style',
            [
                'label' => esc_html__('Timeline Style', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'wave',
                'options' => [
                    'straight' => esc_html__('Straight Line', 'emargy-elements'),
                    'wave' => esc_html__('Soundwave', 'emargy-elements'),
                    'custom' => esc_html__('Custom', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'item_spacing',
            [
                'label' => esc_html__('Spacing Between Items', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-item' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'center_post_size',
            [
                'label' => esc_html__('Center Post Size', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '2x',
                'options' => [
                    '1.5x' => esc_html__('1.5x', 'emargy-elements'),
                    '2x' => esc_html__('2x', 'emargy-elements'),
                    '2.5x' => esc_html__('2.5x', 'emargy-elements'),
                    '3x' => esc_html__('3x', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'thumbnail_mode',
            [
                'label' => esc_html__('Thumbnail Display Mode', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image_only',
                'options' => [
                    'image_only' => esc_html__('Image Only', 'emargy-elements'),
                    'image_title' => esc_html__('Image + Title', 'emargy-elements'),
                    'image_excerpt' => esc_html__('Image + Excerpt', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'hover_effects',
            [
                'label' => esc_html__('Enable Hover Effects', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        /**
         * Colors & Styling
         */
        $this->start_controls_section(
            'section_styling',
            [
                'label' => esc_html__('Colors & Styling', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => esc_html__('Background Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e22d4b',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-container' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'timeline_color',
            [
                'label' => esc_html__('Timeline Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-line' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Title Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => esc_html__('Button Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-button' => 'color: {{VALUE}}; border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__('Title Typography', 'emargy-elements'),
                'selector' => '{{WRAPPER}} .emargy-timeline-title',
            ]
        );

        $this->end_controls_section();

        /**
         * Interaction Settings
         */
        $this->start_controls_section(
            'section_interaction',
            [
                'label' => esc_html__('Interaction Settings', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_drag_scroll',
            [
                'label' => esc_html__('Enable Drag & Scroll Navigation', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'navigation_arrows',
            [
                'label' => esc_html__('Enable Navigation Arrows', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'open_posts',
            [
                'label' => esc_html__('Open Posts', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'popup',
                'options' => [
                    'popup' => esc_html__('In Popup', 'emargy-elements'),
                    'page' => esc_html__('Separate Page', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'enable_animations',
            [
                'label' => esc_html__('Enable Animated Transitions', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get all available post types
     * 
     * @return array
     */
    private function get_post_types() {
        $post_types = get_post_types(['public' => true], 'objects');
        $options = ['post' => esc_html__('Posts', 'emargy-elements')];

        foreach ($post_types as $post_type) {
            if ($post_type->name !== 'post' && $post_type->name !== 'page' && $post_type->name !== 'attachment') {
                $options[$post_type->name] = $post_type->label;
            }
        }

        $options['custom'] = esc_html__('Custom', 'emargy-elements');

        return $options;
    }

    /**
     * Get all available categories
     * 
     * @return array
     */
    private function get_all_categories() {
        $categories = get_categories([
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ]);

        $options = [];

        foreach ($categories as $category) {
            $options[$category->term_id] = $category->name;
        }

        return $options;
    }

    /**
     * Render widget output on the frontend.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Prepare query arguments
        $args = [
            'post_type' => $settings['content_type'],
            'posts_per_page' => $settings['items_number'],
            'order' => $settings['order'],
        ];

        // Add category filtering if selected
        if (!empty($settings['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $settings['category'],
                ]
            ];
        }

        // Run the query
        $timeline_query = new \WP_Query($args);

        // Container classes
        $container_classes = [
            'emargy-timeline-container',
            'emargy-timeline-style-' . $settings['timeline_style'],
        ];

        if ($settings['hover_effects'] === 'yes') {
            $container_classes[] = 'emargy-hover-enabled';
        }

        if ($settings['enable_drag_scroll'] === 'yes') {
            $container_classes[] = 'emargy-drag-enabled';
        }

        if ($settings['enable_animations'] === 'yes') {
            $container_classes[] = 'emargy-animations-enabled';
        }

        // Get all posts and calculate middle item
        $posts = $timeline_query->posts;
        $total_posts = count($posts);
        $middle_index = floor($total_posts / 2);
        
        ?>
        <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
             data-open-type="<?php echo esc_attr($settings['open_posts']); ?>"
             data-center-size="<?php echo esc_attr($settings['center_post_size']); ?>">
            
            <?php if ($settings['navigation_arrows'] === 'yes') : ?>
            <div class="emargy-timeline-nav emargy-nav-prev">
                <i class="eicon-chevron-left"></i>
            </div>
            <?php endif; ?>

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
                                    
                                    <?php if ($settings['thumbnail_mode'] !== 'image_only') : ?>
                                    <div class="emargy-timeline-content">
                                        <h4 class="emargy-timeline-title"><?php the_title(); ?></h4>
                                        
                                        <?php if ($settings['thumbnail_mode'] === 'image_excerpt') : ?>
                                        <div class="emargy-timeline-excerpt">
                                            <?php echo wp_trim_words(get_the_excerpt(), 10); ?>
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
                
                <div class="emargy-timeline-line <?php echo esc_attr('emargy-timeline-' . $settings['timeline_style']); ?>"></div>
            </div>
            
            <?php if ($settings['navigation_arrows'] === 'yes') : ?>
            <div class="emargy-timeline-nav emargy-nav-next">
                <i class="eicon-chevron-right"></i>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render the widget output in the editor.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function content_template() {
        ?>
        <# 
        var containerClasses = [
            'emargy-timeline-container',
            'emargy-timeline-style-' + settings.timeline_style
        ];
        
        if (settings.hover_effects === 'yes') {
            containerClasses.push('emargy-hover-enabled');
        }
        
        if (settings.enable_drag_scroll === 'yes') {
            containerClasses.push('emargy-drag-enabled');
        }
        
        if (settings.enable_animations === 'yes') {
            containerClasses.push('emargy-animations-enabled');
        }
        #>
        <div class="{{ containerClasses.join(' ') }}" 
             data-open-type="{{ settings.open_posts }}"
             data-center-size="{{ settings.center_post_size }}">
            
            <# if (settings.navigation_arrows === 'yes') { #>
            <div class="emargy-timeline-nav emargy-nav-prev">
                <i class="eicon-chevron-left"></i>
            </div>
            <# } #>
            
            <div class="emargy-timeline-items-wrapper">
                <div class="emargy-timeline-items">
                    <# for (var i = 1; i <= settings.items_number; i++) { 
                        var itemClasses = ['emargy-timeline-item'];
                        var middleIndex = Math.floor(settings.items_number / 2);
                        
                        if (i - 1 === middleIndex) {
                            itemClasses.push('emargy-timeline-center-item');
                        }
                        
                        var formattedCounter = i < 10 ? '0' + i : i;
                    #>
                    <div class="{{ itemClasses.join(' ') }}">
                        <div class="emargy-timeline-item-inner">
                            <div class="emargy-timeline-thumbnail">
                                <div class="emargy-no-thumbnail"></div>
                                <div class="emargy-play-button">
                                    <i class="eicon-play"></i>
                                </div>
                            </div>
                            
                            <# if (settings.thumbnail_mode !== 'image_only') { #>
                            <div class="emargy-timeline-content">
                                <h4 class="emargy-timeline-title">{{ 'Post Title ' + i }}</h4>
                                
                                <# if (settings.thumbnail_mode === 'image_excerpt') { #>
                                <div class="emargy-timeline-excerpt">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                </div>
                                <# } #>
                            </div>
                            <# } #>
                        </div>
                        <div class="emargy-timeline-number">{{ formattedCounter }}</div>
                    </div>
                    <# } #>
                </div>
                
                <div class="emargy-timeline-line emargy-timeline-{{ settings.timeline_style }}"></div>
            </div>
            
            <# if (settings.navigation_arrows === 'yes') { #>
            <div class="emargy-timeline-nav emargy-nav-next">
                <i class="eicon-chevron-right"></i>
            </div>
            <# } #>
        </div>
        <?php
    }
}