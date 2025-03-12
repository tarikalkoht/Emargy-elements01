<?php
/**
 * Animated Heading Widget
 * 
 * @since 2.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Emargy_Animated_Heading_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'emargy_animated_heading';
    }

    public function get_title() {
        return esc_html__('Animated Heading', 'emargy-elements');
    }

    public function get_icon() {
        return 'eicon-animation-text';
    }

    public function get_categories() {
        return ['emargy', 'basic'];
    }

    public function get_keywords() {
        return ['heading', 'title', 'animated', 'typing', 'text', 'effects'];
    }

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
            'heading_text',
            [
                'label' => esc_html__('Heading Text', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('This is an ', 'emargy-elements'),
                'placeholder' => esc_html__('Enter your heading', 'emargy-elements'),
                'label_block' => true,
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
            ]
        );

        $this->add_control(
            'after_text',
            [
                'label' => esc_html__('After Text', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__(' heading', 'emargy-elements'),
                'placeholder' => esc_html__('Enter text after animation', 'emargy-elements'),
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

        $this->end_controls_section();

        // Style Section
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

        $this->add_control(
            'cursor_color',
            [
                'label' => esc_html__('Cursor Color', 'emargy-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .typed-cursor' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'animation_type' => 'typing',
                    'cursor' => 'yes',
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
    }

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
            'words' => $animated_text
        ];
        
        // Output HTML
        ?>
        <div class="emargy-animated-heading-wrapper">
            <<?php echo $html_tag; ?> class="emargy-animated-heading">
                <?php echo $settings['heading_text']; ?>
                <span id="<?php echo esc_attr($heading_id); ?>" class="emargy-animated-text"></span>
                <?php echo $settings['after_text']; ?>
            </<?php echo $html_tag; ?>>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var animationSettings = <?php echo json_encode($animation_settings); ?>;
                initAnimatedHeading('<?php echo $heading_id; ?>', animationSettings);
            });
        </script>
        <?php
    }
    
    protected function content_template() {
        ?>
        <#
        var headingId = 'emargy-animated-heading-' + view.getID();
        var animatedText = settings.animated_text.split(/\r\n|\n|\r/);
        var htmlTag = settings.html_tag;
        #>
        <div class="emargy-animated-heading-wrapper">
            <{{{ htmlTag }}} class="emargy-animated-heading">
                {{{ settings.heading_text }}}
                <span id="{{ headingId }}" class="emargy-animated-text">{{ animatedText[0] }}</span>
                {{{ settings.after_text }}}
            </{{{ htmlTag }}}>
        </div>
        <?php
    }
}