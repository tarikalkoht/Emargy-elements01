<?php
/**
 * Timeline Showcase Widget - Enhanced Version
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
        return esc_html__('Timeline Showcase', 'emargy-elements');
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
        return ['timeline', 'showcase', 'posts', 'media', 'video', 'projects', 'portfolio', 'emargy'];
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
                'label' => esc_html__('Number of Items', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 5,
                'max' => 21,
                'step' => 2, // Odd numbers for better centering
                'default' => 11,
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => esc_html__('Category Filter', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_all_categories(),
                'multiple' => true,
                'condition' => [
                    'content_type' => 'post',
                ],
            ]
        );

        $this->add_control(
            'custom_taxonomy',
            [
                'label' => esc_html__('Custom Taxonomy', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_taxonomies(),
                'condition' => [
                    'content_type!' => ['post', 'page'],
                ],
            ]
        );

        $this->add_control(
            'custom_terms',
            [
                'label' => esc_html__('Terms', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => [],
                'multiple' => true,
                'condition' => [
                    'content_type!' => ['post', 'page'],
                    'custom_taxonomy!' => '',
                ],
            ]
        );

        $this->add_control(
            'order_by',
            [
                'label' => esc_html__('Order By', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => esc_html__('Date', 'emargy-elements'),
                    'title' => esc_html__('Title', 'emargy-elements'),
                    'menu_order' => esc_html__('Menu Order', 'emargy-elements'),
                    'rand' => esc_html__('Random', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__('Order', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => esc_html__('Ascending', 'emargy-elements'),
                    'DESC' => esc_html__('Descending', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'manual_selection',
            [
                'label' => esc_html__('Manual Selection', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'manual_posts',
            [
                'label' => esc_html__('Select Posts', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_all_posts(),
                'multiple' => true,
                'condition' => [
                    'manual_selection' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * Media Settings
         */
        $this->start_controls_section(
            'section_media',
            [
                'label' => esc_html__('Media Settings', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'media_source',
            [
                'label' => esc_html__('Media Source', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'featured_image',
                'options' => [
                    'featured_image' => esc_html__('Featured Image', 'emargy-elements'),
                    'video_thumbnail' => esc_html__('Video Thumbnail', 'emargy-elements'),
                    'custom_field' => esc_html__('Custom Field', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'video_field',
            [
                'label' => esc_html__('Video URL Custom Field', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'video_url',
                'condition' => [
                    'media_source' => ['video_thumbnail', 'custom_field'],
                ],
            ]
        );

        $this->add_control(
            'thumbnail_size',
            [
                'label' => esc_html__('Thumbnail Size', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'medium',
                'options' => $this->get_thumbnail_sizes(),
            ]
        );

        $this->add_control(
            'enable_video_popup',
            [
                'label' => esc_html__('Enable Video Popup', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
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
                    'size' => 30,
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
                'default' => '2.2x',
                'options' => [
                    '1.5x' => esc_html__('1.5x', 'emargy-elements'),
                    '1.8x' => esc_html__('1.8x', 'emargy-elements'),
                    '2.2x' => esc_html__('2.2x', 'emargy-elements'),
                    '2.5x' => esc_html__('2.5x', 'emargy-elements'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-center-item .emargy-timeline-item-inner' => 'transform: scale({{VALUE}});',
                    '{{WRAPPER}} .emargy-hover-enabled .emargy-timeline-center-item:hover .emargy-timeline-item-inner' => 'transform: scale({{VALUE}}) translateY(-5px);',
                ],
            ]
        );

        $this->add_control(
            'item_width',
            [
                'label' => esc_html__('Item Width', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 80,
                        'max' => 300,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 160,
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-item' => 'min-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'thumbnail_mode',
            [
                'label' => esc_html__('Thumbnail Display Mode', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image_title',
                'options' => [
                    'image_only' => esc_html__('Image Only', 'emargy-elements'),
                    'image_title' => esc_html__('Image + Title', 'emargy-elements'),
                    'image_excerpt' => esc_html__('Image + Title + Excerpt', 'emargy-elements'),
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

        $this->add_control(
            'show_indicators',
            [
                'label' => esc_html__('Show Indicator Dots', 'emargy-elements'),
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

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_gradient',
                'label' => esc_html__('Background', 'emargy-elements'),
                'types' => ['gradient'],
                'selector' => '{{WRAPPER}} .emargy-timeline-container',
            ]
        );

        $this->add_control(
            'timeline_color',
            [
                'label' => esc_html__('Timeline Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(255, 255, 255, 0.4)',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-line' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .emargy-timeline-wave::after' => 'background-color: {{VALUE}}',
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
            'number_color',
            [
                'label' => esc_html__('Number Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-number' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'play_button_color',
            [
                'label' => esc_html__('Play Button Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(255, 255, 255, 0.25)',
                'selectors' => [
                    '{{WRAPPER}} .emargy-play-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'play_button_icon_color',
            [
                'label' => esc_html__('Play Button Icon Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .emargy-play-button i' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'nav_button_color',
            [
                'label' => esc_html__('Navigation Button Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(255, 255, 255, 0.15)',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-nav' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'nav_border_color',
            [
                'label' => esc_html__('Navigation Border Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(255, 255, 255, 0.4)',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-nav' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'indicator_color',
            [
                'label' => esc_html__('Indicator Dot Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(255, 255, 255, 0.3)',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-indicator' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'show_indicators' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'active_indicator_color',
            [
                'label' => esc_html__('Active Indicator Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-indicator.active' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'show_indicators' => 'yes',
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

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'label' => esc_html__('Excerpt Typography', 'emargy-elements'),
                'selector' => '{{WRAPPER}} .emargy-timeline-excerpt',
                'condition' => [
                    'thumbnail_mode' => 'image_excerpt',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'number_typography',
                'label' => esc_html__('Number Typography', 'emargy-elements'),
                'selector' => '{{WRAPPER}} .emargy-timeline-number',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'item_border',
                'label' => esc_html__('Item Border', 'emargy-elements'),
                'selector' => '{{WRAPPER}} .emargy-timeline-item-inner',
            ]
        );

        $this->add_control(
            'item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_box_shadow',
                'label' => esc_html__('Item Shadow', 'emargy-elements'),
                'selector' => '{{WRAPPER}} .emargy-timeline-item-inner',
            ]
        );

        $this->end_controls_section();

        /**
         * Animation Settings
         */
        $this->start_controls_section(
            'section_animation',
            [
                'label' => esc_html__('Animation & Effects', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'animate_entrance',
            [
                'label' => esc_html__('Animate on Entrance', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'entrance_animation',
            [
                'label' => esc_html__('Entrance Animation', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'fadeIn',
                'options' => [
                    'fadeIn' => esc_html__('Fade In', 'emargy-elements'),
                    'fadeInUp' => esc_html__('Fade In Up', 'emargy-elements'),
                    'fadeInDown' => esc_html__('Fade In Down', 'emargy-elements'),
                    'zoomIn' => esc_html__('Zoom In', 'emargy-elements'),
                    'slideInUp' => esc_html__('Slide In Up', 'emargy-elements'),
                    'slideInLeft' => esc_html__('Slide In Left', 'emargy-elements'),
                    'slideInRight' => esc_html__('Slide In Right', 'emargy-elements'),
                ],
                'condition' => [
                    'animate_entrance' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'animation_duration',
            [
                'label' => esc_html__('Animation Duration', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 0.1,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 's',
                    'size' => 0.6,
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-container' => 'animation-duration: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'animate_entrance' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'enable_thumbnail_zoom',
            [
                'label' => esc_html__('Enable Thumbnail Zoom Effect', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-center-item .emargy-timeline-thumbnail img' => 'transform: scale(1.05);',
                    '{{WRAPPER}} .emargy-hover-enabled .emargy-timeline-item:not(.emargy-timeline-center-item):hover .emargy-timeline-thumbnail img' => 'transform: scale(1.1);',
                ],
            ]
        );

        $this->add_control(
            'pulse_effect',
            [
                'label' => esc_html__('Enable Pulse Effect on Play Button', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .emargy-play-button::before' => 'content: ""; position: absolute; top: -5px; left: -5px; right: -5px; bottom: -5px; border-radius: 50%; border: 1px solid rgba(255, 255, 255, 0.5); animation: pulse 2s infinite;',
                ],
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
            'enable_keyboard',
            [
                'label' => esc_html__('Enable Keyboard Navigation', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'enable_mousewheel',
            [
                'label' => esc_html__('Enable Mousewheel Navigation', 'emargy-elements'),
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
                'label' => esc_html__('Item Click Action', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'popup',
                'options' => [
                    'popup' => esc_html__('Open in Popup', 'emargy-elements'),
                    'page' => esc_html__('Go to Page', 'emargy-elements'),
                    'none' => esc_html__('Just Focus Item', 'emargy-elements'),
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

        $this->add_control(
            'transition_speed',
            [
                'label' => esc_html__('Transition Speed', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 0.1,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 's',
                    'size' => 0.6,
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-items' => 'transition-duration: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_animations' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * Advanced Settings
         */
        $this->start_controls_section(
            'section_advanced',
            [
                'label' => esc_html__('Advanced Settings', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'css_classes',
            [
                'label' => esc_html__('Additional CSS Classes', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Class 1 class 2', 'emargy-elements'),
            ]
        );

        $this->add_control(
            'item_aspect_ratio',
            [
                'label' => esc_html__('Item Aspect Ratio', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '16:9',
                'options' => [
                    '16:9' => '16:9',
                    '4:3' => '4:3',
                    '3:2' => '3:2',
                    '1:1' => '1:1',
                    '21:9' => '21:9',
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-timeline-thumbnail' => 'padding-bottom: calc(100% * {{VALUE.SIZE}} / {{VALUE.SIZE}});',
                ],
            ]
        );

        $this->add_control(
            'initial_center_item',
            [
                'label' => esc_html__('Initial Center Item', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'middle',
                'options' => [
                    'first' => esc_html__('First Item', 'emargy-elements'),
                    'middle' => esc_html__('Middle Item', 'emargy-elements'),
                    'last' => esc_html__('Last Item', 'emargy-elements'),
                    'custom' => esc_html__('Custom (Index)', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'custom_center_index',
            [
                'label' => esc_html__('Custom Center Item Index', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 20,
                'step' => 1,
                'default' => 0,
                'condition' => [
                    'initial_center_item' => 'custom',
                ],
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
     * Get all available taxonomies
     * 
     * @return array
     */
    private function get_taxonomies() {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $options = ['' => esc_html__('Select Taxonomy', 'emargy-elements')];

        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->name !== 'category' && $taxonomy->name !== 'post_tag') {
                $options[$taxonomy->name] = $taxonomy->label;
            }
        }

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
     * Get all posts
     * 
     * @return array
     */
    private function get_all_posts() {
        $args = [
            'post_type' => 'post',
            'posts_per_page' => 100,
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        $posts = get_posts($args);
        $options = [];

        foreach ($posts as $post) {
            $options[$post->ID] = $post->post_title;
        }

        return $options;
    }

    /**
     * Get all available thumbnail sizes
     * 
     * @return array
     */
    private function get_thumbnail_sizes() {
        $sizes = get_intermediate_image_sizes();
        $options = [];

        foreach ($sizes as $size) {
            $options[$size] = ucfirst(str_replace(['_', '-'], ' ', $size));
        }

        $options['full'] = esc_html__('Full Size', 'emargy-elements');

        return $options;
    }

    /**
     * Get video thumbnail from URL
     * 
     * @param string $url Video URL
     * @return string|bool Thumbnail URL or false
     */
    private function get_video_thumbnail($url) {
        // YouTube
        if (preg_match('/youtu\\.be\\/([^\\/?\\s]+)/', $url, $matches) || preg_match('/youtube\\.com\\/watch\\?v=([^\\&\\s]+)/', $url, $matches)) {
            return 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
        }
        
        // Vimeo
        if (preg_match('/vimeo\\.com\\/([0-9]+)/', $url, $matches)) {
            $vimeo_id = $matches[1];
            $data = file_get_contents('http://vimeo.com/api/v2/video/' . $vimeo_id . '.json');
            if ($data) {
                $data = json_decode($data);
                return $data[0]->thumbnail_large;
            }
        }
        
        return false;
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
            'orderby' => $settings['order_by'],
            'order' => $settings['order'],
        ];

        // Manual selection
        if ($settings['manual_selection'] === 'yes' && !empty($settings['manual_posts'])) {
            $args['post__in'] = $settings['manual_posts'];
            $args['orderby'] = 'post__in';
        } else {
            // Categories for posts
            if ($settings['content_type'] === 'post' && !empty($settings['category'])) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'category',
                        'field' => 'term_id',
                        'terms' => $settings['category'],
                    ]
                ];
            }
            
            // Custom taxonomy terms
            if ($settings['content_type'] !== 'post' && !empty($settings['custom_taxonomy']) && !empty($settings['custom_terms'])) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => $settings['custom_taxonomy'],
                        'field' => 'term_id',
                        'terms' => $settings['custom_terms'],
                    ]
                ];
            }
        }

        // Run the query
        $timeline_query = new \WP_Query($args);

        // Container classes
        $container_classes = [
            'emargy-timeline-container',
            'emargy-timeline-style-' . $settings['timeline_style'],
        ];

        // Add animation class if enabled
        if ($settings['animate_entrance'] === 'yes') {
            $container_classes[] = 'animated';
            $container_classes[] = $settings['entrance_animation'];
        }

        if ($settings['hover_effects'] === 'yes') {
            $container_classes[] = 'emargy-hover-enabled';
        }

        if ($settings['enable_drag_scroll'] === 'yes') {
            $container_classes[] = 'emargy-drag-enabled';
        }

        if ($settings['enable_animations'] === 'yes') {
            $container_classes[] = 'emargy-animations-enabled';
        }

        if ($settings['enable_mousewheel'] === 'yes') {
            $container_classes[] = 'emargy-mousewheel-enabled';
        }

        if ($settings['css_classes']) {
            $container_classes[] = $settings['css_classes'];
        }

        // Get all posts and determine initial center item
        $posts = $timeline_query->posts;
        $total_posts = count($posts);
        
        // Set the initial center item index
        switch ($settings['initial_center_item']) {
            case 'first':
                $center_index = 0;
                break;
            case 'last':
                $center_index = $total_posts - 1;
                break;
            case 'custom':
                $center_index = min($settings['custom_center_index'], $total_posts - 1);
                $center_index = max(0, $center_index); // Ensure valid index
                break;
            case 'middle':
            default:
                $center_index = floor($total_posts / 2);
                break;
        }
        
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
                            $is_center = ($counter - 1) === $center_index;
                            if ($is_center) {
                                $item_classes[] = 'emargy-timeline-center-item';
                            }
                            
                            // Format the counter with leading zero
                            $formatted_counter = sprintf("%02d", $counter);
                            
                            // Get video URL if available
                            $video_url = '';
                            if ($settings['media_source'] !== 'featured_image') {
                                $meta_key = $settings['video_field'] ? $settings['video_field'] : 'video_url';
                                $video_url = get_post_meta(get_the_ID(), $meta_key, true);
                            }
                            ?>
                            <div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>"
                                 data-post-id="<?php the_ID(); ?>"
                                 <?php if ($video_url) : ?>
                                 data-video-url="<?php echo esc_url($video_url); ?>"
                                 <?php endif; ?>>
                                <div class="emargy-timeline-item-inner">
                                    <div class="emargy-timeline-thumbnail">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <img src="<?php the_post_thumbnail_url($settings['thumbnail_size']); ?>" alt="<?php the_title_attribute(); ?>">
                                        <?php elseif ($video_url && $thumbnail = $this->get_video_thumbnail($video_url)) : ?>
                                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>">
                                        <?php else : ?>
                                            <div class="emargy-no-thumbnail"></div>
                                        <?php endif; ?>
                                        
                                        <?php if ($settings['enable_video_popup'] === 'yes' && $video_url) : ?>
                                        <div class="emargy-play-button">
                                            <i class="eicon-play"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($settings['thumbnail_mode'] !== 'image_only') : ?>
                                    <div class="emargy-timeline-content">
                                        <h4 class="emargy-timeline-title"><?php the_title(); ?></h4>
                                        
                                        <?php if ($settings['thumbnail_mode'] === 'image_excerpt') : ?>
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
                
                <div class="emargy-timeline-line <?php echo esc_attr('emargy-timeline-' . $settings['timeline_style']); ?>"></div>
            </div>
            
            <?php if ($settings['navigation_arrows'] === 'yes') : ?>
            <div class="emargy-timeline-nav emargy-nav-next">
                <i class="eicon-chevron-right"></i>
            </div>
            <?php endif; ?>
            
            <?php if ($settings['show_indicators'] === 'yes' && $total_posts > 0) : ?>
            <div class="emargy-timeline-indicators">
                <?php for ($i = 0; $i < $total_posts; $i++) : ?>
                    <div class="emargy-timeline-indicator <?php echo ($i === $center_index) ? 'active' : ''; ?>"></div>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($settings['pulse_effect'] === 'yes') : ?>
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
        <?php endif; ?>
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
        
        if (settings.animate_entrance === 'yes') {
            containerClasses.push('animated');
            containerClasses.push(settings.entrance_animation);
        }
        
        if (settings.hover_effects === 'yes') {
            containerClasses.push('emargy-hover-enabled');
        }
        
        if (settings.enable_drag_scroll === 'yes') {
            containerClasses.push('emargy-drag-enabled');
        }
        
        if (settings.enable_animations === 'yes') {
            containerClasses.push('emargy-animations-enabled');
        }
        
        if (settings.css_classes) {
            containerClasses.push(settings.css_classes);
        }
        
        // Calculate middle index for preview
        var itemsNumber = settings.items_number || 11;
        var centerIndex;
        
        switch (settings.initial_center_item) {
            case 'first':
                centerIndex = 0;
                break;
            case 'last':
                centerIndex = itemsNumber - 1;
                break;
            case 'custom':
                centerIndex = Math.min(settings.custom_center_index, itemsNumber - 1);
                centerIndex = Math.max(0, centerIndex);
                break;
            case 'middle':
            default:
                centerIndex = Math.floor(itemsNumber / 2);
                break;
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
                    <# for (var i = 1; i <= itemsNumber; i++) { 
                        var itemClasses = ['emargy-timeline-item'];
                        
                        if (i - 1 === centerIndex) {
                            itemClasses.push('emargy-timeline-center-item');
                        }
                        
                        var formattedCounter = i < 10 ? '0' + i : i;
                    #>
                    <div class="{{ itemClasses.join(' ') }}">
                        <div class="emargy-timeline-item-inner">
                            <div class="emargy-timeline-thumbnail">
                                <div class="emargy-no-thumbnail"></div>
                                <# if (settings.enable_video_popup === 'yes') { #>
                                <div class="emargy-play-button">
                                    <i class="eicon-play"></i>
                                </div>
                                <# } #>
                            </div>
                            
                            <# if (settings.thumbnail_mode !== 'image_only') { #>
                            <div class="emargy-timeline-content">
                                <h4 class="emargy-timeline-title">{{ 'Item Title ' + i }}</h4>
                                
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
            
            <# if (settings.show_indicators === 'yes') { #>
            <div class="emargy-timeline-indicators">
                <# for (var j = 0; j < itemsNumber; j++) { #>
                    <div class="emargy-timeline-indicator {{ (j === centerIndex) ? 'active' : '' }}"></div>
                <# } #>
            </div>
            <# } #>
        </div>
        
        <# if (settings.pulse_effect === 'yes') { #>
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
        <# } #>
        <?php
    }
}