<?php
/**
 * Animated Heading Widget - Enhanced Version
 * 
 * @since 2.1.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Emargy_Animated_Heading_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'emargy_animated_heading';
    }

    /**
     * Get widget title.
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__('Animated Heading', 'emargy-elements');
    }

    /**
     * Get widget icon.
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-animation-text';
    }

    /**
     * Get widget categories.
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['emargy', 'basic'];
    }

    /**
     * Get widget keywords.
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return ['heading', 'title', 'animated', 'typing', 'text', 'effects', 'rotate'];
    }

    /**
     * Get script dependencies.
     *
     * @return array Scripts dependencies.
     */
    public function get_script_depends() {
        return ['typed-js', 'emargy-animated-heading'];
    }

    /**
     * Get style dependencies.
     *
     * @return array Styles dependencies.
     */
    public function get_style_depends() {
        return ['emargy-animated-heading-style'];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'heading_layout',
            [
                'label' => esc_html__('Layout', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'inline',
                'options' => [
                    'inline' => esc_html__('Inline', 'emargy-elements'),
                    'block' => esc_html__('Block', 'emargy-elements'),
                ],
                'prefix_class' => 'emargy-heading-layout-',
            ]
        );

        $this->add_control(
            'heading_text',
            [
                'label' => esc_html__('Heading Text', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('This is an ', 'emargy-elements'),
                'placeholder' => esc_html__('Enter your heading', 'emargy-elements'),
                'label_block' => true,
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'animated_text',
            [
                'label' => esc_html__('Animated Text', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__("Amazing\nCreative\nUnique", 'emargy-elements'),
                'placeholder' => esc_html__('Enter each word in a new line', 'emargy-elements'),
                'description' => esc_html__('Enter each word in a new line', 'emargy-elements'),
                'separator' => 'before',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'after_text',
            [
                'label' => esc_html__('After Text', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__(' heading', 'emargy-elements'),
                'placeholder' => esc_html__('Enter text after animation', 'emargy-elements'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'animation_type',
            [
                'label' => esc_html__('Animation Type', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'typing',
                'options' => [
                    'typing' => esc_html__('Typing', 'emargy-elements'),
                    'fade' => esc_html__('Fade', 'emargy-elements'),
                    'slide' => esc_html__('Slide', 'emargy-elements'),
                    'zoom' => esc_html__('Zoom', 'emargy-elements'),
                    'bounce' => esc_html__('Bounce', 'emargy-elements'),
                    'rotate' => esc_html__('Rotate', 'emargy-elements'),
                    'highlight' => esc_html__('Highlight', 'emargy-elements'),
                ],
            ]
        );

        $this->add_control(
            'highlighted_shape',
            [
                'label' => esc_html__('Highlight Shape', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'circle',
                'options' => [
                    'circle' => esc_html__('Circle', 'emargy-elements'),
                    'curly' => esc_html__('Curly', 'emargy-elements'),
                    'underline' => esc_html__('Underline', 'emargy-elements'),
                    'double' => esc_html__('Double', 'emargy-elements'),
                    'rect' => esc_html__('Rectangle', 'emargy-elements'),
                ],
                'condition' => [
                    'animation_type' => 'highlight',
                ],
            ]
        );

        $this->add_control(
            'html_tag',
            [
                'label' => esc_html__('HTML Tag', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'h2',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => esc_html__('Link', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'emargy-elements'),
                'show_external' => true,
                'default' => [
                    'url' => '',
                    'is_external' => false,
                    'nofollow' => false,
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // Animation Settings
        $this->start_controls_section(
            'animation_settings',
            [
                'label' => esc_html__('Animation Settings', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'typing_speed',
            [
                'label' => esc_html__('Typing Speed', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 100,
                'min' => 10,
                'max' => 500,
                'step' => 10,
                'condition' => [
                    'animation_type' => 'typing',
                ],
            ]
        );

        $this->add_control(
            'backspeed',
            [
                'label' => esc_html__('Back Speed', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 50,
                'min' => 10,
                'max' => 500,
                'step' => 10,
                'condition' => [
                    'animation_type' => 'typing',
                ],
            ]
        );

        $this->add_control(
            'animation_duration',
            [
                'label' => esc_html__('Animation Duration', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['ms'],
                'range' => [
                    'ms' => [
                        'min' => 100,
                        'max' => 10000,
                        'step' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'ms',
                    'size' => 2000,
                ],
                'condition' => [
                    'animation_type!' => 'typing',
                ],
            ]
        );

        $this->add_control(
            'delay_between_words',
            [
                'label' => esc_html__('Delay Between Words', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['ms'],
                'range' => [
                    'ms' => [
                        'min' => 100,
                        'max' => 5000,
                        'step' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'ms',
                    'size' => 2000,
                ],
            ]
        );

        $this->add_control(
            'cursor',
            [
                'label' => esc_html__('Show Cursor', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'animation_type' => 'typing',
                ],
            ]
        );

        $this->add_control(
            'cursor_char',
            [
                'label' => esc_html__('Cursor Character', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '|',
                'condition' => [
                    'animation_type' => 'typing',
                    'cursor' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'typing_loop',
            [
                'label' => esc_html__('Loop Typing', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'emargy-elements'),
                'label_off' => esc_html__('No', 'emargy-elements'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'animation_type' => 'typing',
                ],
            ]
        );

        $this->add_control(
            'typing_start_delay',
            [
                'label' => esc_html__('Start Delay', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'max' => 5000,
                'step' => 100,
                'condition' => [
                    'animation_type' => 'typing',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - General
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label' => esc_html__('Heading Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .emargy-animated-heading' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'animated_text_color',
            [
                'label' => esc_html__('Animated Text Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .emargy-animated-text' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .emargy-animated-heading',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'animated_text_typography',
                'selector' => '{{WRAPPER}} .emargy-animated-text',
            ]
        );

        $this->add_control(
            'alignment',
            [
                'label' => esc_html__('Alignment', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'emargy-elements'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'emargy-elements'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'emargy-elements'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .emargy-animated-heading-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Cursor
        $this->start_controls_section(
            'cursor_style_section',
            [
                'label' => esc_html__('Cursor Style', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'animation_type' => 'typing',
                    'cursor' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'cursor_color',
            [
                'label' => esc_html__('Cursor Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .typed-cursor' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'cursor_size',
            [
                'label' => esc_html__('Cursor Size', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0.1,
                        'max' => 10,
                    ],
                    'rem' => [
                        'min' => 0.1,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .typed-cursor' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'cursor_animation_speed',
            [
                'label' => esc_html__('Cursor Animation Speed', 'emargy-elements'),
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
                    'size' => 0.7,
                ],
                'selectors' => [
                    '{{WRAPPER}} .typed-cursor' => 'animation-duration: {{SIZE}}s;',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Highlight
        $this->start_controls_section(
            'highlight_style_section',
            [
                'label' => esc_html__('Highlight Style', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'animation_type' => 'highlight',
                ],
            ]
        );

        $this->add_control(
            'highlight_color',
            [
                'label' => esc_html__('Highlight Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FFC107',
                'selectors' => [
                    '{{WRAPPER}} .emargy-highlight-shape.circle' => 'background-image: paint(circle, {{VALUE}});',
                    '{{WRAPPER}} .emargy-highlight-shape.curly' => 'background-image: paint(curly, {{VALUE}});',
                    '{{WRAPPER}} .emargy-highlight-shape.underline' => 'background-image: linear-gradient(transparent 60%, {{VALUE}} 40%);',
                    '{{WRAPPER}} .emargy-highlight-shape.double' => 'background-image: linear-gradient(transparent 60%, {{VALUE}} 40%), linear-gradient(transparent 80%, {{VALUE}} 20%);',
                    '{{WRAPPER}} .emargy-highlight-shape.rect' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'highlight_width',
            [
                'label' => esc_html__('Highlight Width', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 20,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 3,
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-highlight-shape.underline, {{WRAPPER}} .emargy-highlight-shape.double' => 'background-size: 100% {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'highlighted_shape' => ['underline', 'double'],
                ],
            ]
        );

        $this->add_control(
            'highlight_z_index',
            [
                'label' => esc_html__('Z-Index', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => -1,
                'selectors' => [
                    '{{WRAPPER}} .emargy-highlight-shape' => 'z-index: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Responsive Settings
        $this->start_controls_section(
            'responsive_section',
            [
                'label' => esc_html__('Responsive', 'emargy-elements'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'heading_font_size',
            [
                'label' => esc_html__('Heading Font Size', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                    'em' => [
                        'min' => 0.1,
                        'max' => 20,
                    ],
                    'rem' => [
                        'min' => 0.1,
                        'max' => 20,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-animated-heading' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_line_height',
            [
                'label' => esc_html__('Line Height', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 300,
                    ],
                    'em' => [
                        'min' => 0.1,
                        'max' => 5,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .emargy-animated-heading' => 'line-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Prepare animated text array
        $animated_text = preg_split("/\r\n|\n|\r/", $settings['animated_text']);
        $animated_text_json = json_encode($animated_text);
        
        // HTML Tag
        $html_tag = $settings['html_tag'];
        
        // Animation type
        $animation_type = $settings['animation_type'];
        
        // Unique ID for this instance
        $heading_id = 'emargy-animated-heading-' . $this->get_id();
        
        // Animation settings
        $animation_settings = [
            'type' => $animation_type,
            'typingSpeed' => $settings['typing_speed'],
            'backSpeed' => $settings['backspeed'],
            'animationDuration' => isset($settings['animation_duration']['size']) ? $settings['animation_duration']['size'] : 2000,
            'delayBetweenWords' => isset($settings['delay_between_words']['size']) ? $settings['delay_between_words']['size'] : 2000,
            'showCursor' => $settings['cursor'] === 'yes',
            'cursorChar' => $settings['cursor_char'],
            'words' => $animated_text,
            'loop' => isset($settings['typing_loop']) ? $settings['typing_loop'] === 'yes' : true,
            'startDelay' => isset($settings['typing_start_delay']) ? $settings['typing_start_delay'] : 0,
            'highlightedShape' => isset($settings['highlighted_shape']) ? $settings['highlighted_shape'] : 'circle'
        ];
        
        // Link attributes
        $link_tag_open = '';
        $link_tag_close = '';
        
        if (!empty($settings['link']['url'])) {
            $this->add_link_attributes('link', $settings['link']);
            $link_attributes = $this->get_render_attribute_string('link');
            $link_tag_open = '<a ' . $link_attributes . '>';
            $link_tag_close = '</a>';
        }
        
        // Wrapper classes
        $wrapper_classes = [
            'emargy-animated-heading-wrapper',
            'emargy-animation-type-' . $animation_type
        ];
        
        // Output HTML
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" data-settings="<?php echo esc_attr(json_encode($animation_settings)); ?>">
            <?php echo $link_tag_open; ?>
            <<?php echo $html_tag; ?> class="emargy-animated-heading">
                <?php echo $settings['heading_text']; ?>
                <span id="<?php echo esc_attr($heading_id); ?>" class="emargy-animated-text <?php echo ($animation_type === 'highlight') ? 'emargy-highlight-shape ' . esc_attr($settings['highlighted_shape']) : ''; ?>"></span>
                <?php echo $settings['after_text']; ?>
            </<?php echo $html_tag; ?>>
            <?php echo $link_tag_close; ?>
        </div>
        <?php
        
        // The animation is initialized via JS file now, so no inline script needed
    }
    
    /**
     * Render the widget output in the editor.
     */
    protected function content_template() {
        ?>
        <#
        var headingId = 'emargy-animated-heading-' + view.getID();
        var animatedText = settings.animated_text.split(/\r\n|\n|\r/);
        var htmlTag = settings.html_tag;
        var animationType = settings.animation_type;
        var highlightedClass = '';
        
        if (animationType === 'highlight' && settings.highlighted_shape) {
            highlightedClass = 'emargy-highlight-shape ' + settings.highlighted_shape;
        }
        
        var wrapperClasses = [
            'emargy-animated-heading-wrapper',
            'emargy-animation-type-' + animationType
        ];
        
        var linkTagOpen = '';
        var linkTagClose = '';
        
        if (settings.link.url) {
            linkTagOpen = '<a href="' + settings.link.url + '">';
            linkTagClose = '</a>';
        }
        #>
        <div class="{{ wrapperClasses.join(' ') }}">
            {{{ linkTagOpen }}}
            <{{{ htmlTag }}} class="emargy-animated-heading">
                {{{ settings.heading_text }}}
                <span id="{{ headingId }}" class="emargy-animated-text {{ highlightedClass }}">{{ animatedText[0] }}</span>
                {{{ settings.after_text }}}
            </{{{ htmlTag }}}>
            {{{ linkTagClose }}}
        </div>
        <?php
    }
}