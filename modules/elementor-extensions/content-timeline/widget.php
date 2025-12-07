<?php

/**
 * dailybuddy Content Timeline Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Utils;
use Elementor\Plugin;

class WP_Dailybuddy_Elementor_Content_Timeline_Widget extends Widget_Base
{

    /**
     * Get widget name
     */
    public function get_name()
    {
        return 'dailybuddy-content-timeline';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('Content Timeline', 'dailybuddy');
    }

    /**
     * Get widget icon
     */
    public function get_icon()
    {
        return 'eicon-time-line mini-icon-dailybuddy';
    }

    /**
     * Get widget categories
     */
    public function get_categories()
    {
        return array('dailybuddy');
    }

    /**
     * Get widget keywords
     */
    public function get_keywords()
    {
        return array('content', 'timeline', 'history', 'events', 'posts', 'blog', 'dailybuddy');
    }

    /**
     * Get style dependencies
     */
    public function get_style_depends()
    {
        return array('font-awesome-5-all', 'font-awesome-4-shim');
    }

    /**
     * Register widget controls
     */
    protected function register_controls()
    {
        $this->register_content_controls();
        $this->register_layout_controls();
        $this->register_query_controls();
        $this->register_style_controls();
    }

    /**
     * Register Content Controls
     */
    private function register_content_controls()
    {
        $this->start_controls_section(
            'section_timeline_content',
            array(
                'label' => __('Timeline Content', 'dailybuddy'),
            )
        );

        $this->add_control(
            'content_source',
            array(
                'label'   => __('Content Source', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'dynamic',
                'options' => array(
                    'dynamic' => __('Dynamic Posts', 'dailybuddy'),
                    'custom'  => __('Custom Content', 'dailybuddy'),
                ),
            )
        );

        // Custom Content Repeater
        $repeater = new Repeater();

        $repeater->add_control(
            'custom_title',
            array(
                'label'       => __('Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Timeline Item', 'dailybuddy'),
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'custom_content',
            array(
                'label'   => __('Content', 'dailybuddy'),
                'type'    => Controls_Manager::WYSIWYG,
                'default' => __('Add your timeline content here...', 'dailybuddy'),
            )
        );

        $repeater->add_control(
            'custom_date',
            array(
                'label'   => __('Date', 'dailybuddy'),
                'type'    => Controls_Manager::TEXT,
                'default' => gmdate('F j, Y'),
            )
        );

        $repeater->add_control(
            'custom_image',
            array(
                'label'   => __('Image', 'dailybuddy'),
                'type'    => Controls_Manager::MEDIA,
                'default' => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
            )
        );

        $repeater->add_control(
            'custom_link',
            array(
                'label'         => __('Link', 'dailybuddy'),
                'type'          => Controls_Manager::URL,
                'placeholder'   => __('https://your-link.com', 'dailybuddy'),
                'show_external' => true,
                'default'       => array(
                    'url'         => '',
                    'is_external' => false,
                    'nofollow'    => false,
                ),
            )
        );

        $repeater->add_control(
            'custom_icon',
            array(
                'label'   => __('Bulletpoint Icon', 'dailybuddy'),
                'type'    => Controls_Manager::ICONS,
                'default' => array(
                    'value'   => 'fas fa-circle',
                    'library' => 'fa-solid',
                ),
            )
        );

        $this->add_control(
            'custom_content_items',
            array(
                'label'       => __('Timeline Items', 'dailybuddy'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => array(
                    array(
                        'custom_title'   => __('First Event', 'dailybuddy'),
                        'custom_content' => __('This is the first event in the timeline', 'dailybuddy'),
                        'custom_date'    => '2024',
                    ),
                    array(
                        'custom_title'   => __('Second Event', 'dailybuddy'),
                        'custom_content' => __('This is the second event in the timeline', 'dailybuddy'),
                        'custom_date'    => '2023',
                    ),
                ),
                'title_field' => '{{{ custom_title }}}',
                'condition'   => array(
                    'content_source' => 'custom',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Layout Controls
     */
    private function register_layout_controls()
    {
        $this->start_controls_section(
            'section_timeline_layout',
            array(
                'label' => __('Layout Settings', 'dailybuddy'),
            )
        );

        $this->add_control(
            'timeline_layout',
            array(
                'label'   => __('Layout', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'vertical',
                'options' => array(
                    'vertical'   => __('Vertical', 'dailybuddy'),
                    'horizontal' => __('Horizontal', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'timeline_alignment',
            array(
                'label'     => __('Alignment', 'dailybuddy'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => __('Left', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => __('Center', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'  => array(
                        'title' => __('Right', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default'   => 'center',
                'condition' => array(
                    'timeline_layout' => 'vertical',
                ),
            )
        );

        $this->add_control(
            'show_image',
            array(
                'label'        => __('Show Image', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_title',
            array(
                'label'        => __('Show Title', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_excerpt',
            array(
                'label'        => __('Show Excerpt/Content', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_date',
            array(
                'label'        => __('Show Date', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_read_more',
            array(
                'label'        => __('Show Read More Button', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'read_more_text',
            array(
                'label'     => __('Read More Text', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('Read More', 'dailybuddy'),
                'condition' => array(
                    'show_read_more' => 'yes',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Query Controls
     */
    private function register_query_controls()
    {
        $this->start_controls_section(
            'section_query',
            array(
                'label'     => __('Query', 'dailybuddy'),
                'condition' => array(
                    'content_source' => 'dynamic',
                ),
            )
        );

        $this->add_control(
            'post_type',
            array(
                'label'   => __('Post Type', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'post',
                'options' => $this->get_post_types(),
            )
        );

        $this->add_control(
            'posts_per_page',
            array(
                'label'   => __('Posts Per Page', 'dailybuddy'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 5,
            )
        );

        $this->add_control(
            'order_by',
            array(
                'label'   => __('Order By', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => array(
                    'date'     => __('Date', 'dailybuddy'),
                    'title'    => __('Title', 'dailybuddy'),
                    'modified' => __('Modified', 'dailybuddy'),
                    'rand'     => __('Random', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'order',
            array(
                'label'   => __('Order', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => array(
                    'DESC' => __('Descending', 'dailybuddy'),
                    'ASC'  => __('Ascending', 'dailybuddy'),
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Style Controls
     */
    private function register_style_controls()
    {
        // Timeline Line Style
        $this->start_controls_section(
            'section_timeline_line_style',
            array(
                'label' => __('Timeline Line', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'line_color',
            array(
                'label'     => __('Line Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#e5e5e5',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-line' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'line_width',
            array(
                'label'      => __('Line Width', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 1,
                        'max' => 10,
                    ),
                ),
                'default'    => array(
                    'size' => 2,
                    'unit' => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-line' => 'width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Bulletpoint/Icon Style
        $this->start_controls_section(
            'section_bulletpoint_style',
            array(
                'label' => __('Bulletpoint / Icon', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => __('Icon Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#3498db',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-timeline-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_background',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-icon' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#3498db',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-icon' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_size',
            array(
                'label'      => __('Icon Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 50,
                    ),
                ),
                'default'    => array(
                    'size' => 16,
                    'unit' => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-timeline-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'icon_box_size',
            array(
                'label'      => __('Box Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 20,
                        'max' => 100,
                    ),
                ),
                'default'    => array(
                    'size' => 40,
                    'unit' => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'icon_border_width',
            array(
                'label'      => __('Border Width', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 10,
                    ),
                ),
                'default'    => array(
                    'size' => 3,
                    'unit' => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-icon' => 'border-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Timeline Item Style
        $this->start_controls_section(
            'section_timeline_item_style',
            array(
                'label' => __('Timeline Items', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'item_background',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-item-content' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'item_border',
                'label'    => __('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-timeline-item-content',
            )
        );

        $this->add_control(
            'item_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-item-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'item_box_shadow',
                'label'    => __('Box Shadow', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-timeline-item-content',
            )
        );

        $this->add_control(
            'item_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-item-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Title Style
        $this->start_controls_section(
            'section_title_style',
            array(
                'label'     => __('Title', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_title' => 'yes',
                ),
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-item-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'label'    => __('Typography', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-timeline-item-title',
            )
        );

        $this->add_control(
            'title_spacing',
            array(
                'label'      => __('Bottom Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-item-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Date Style
        $this->start_controls_section(
            'section_date_style',
            array(
                'label'     => __('Date', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_date' => 'yes',
                ),
            )
        );

        $this->add_control(
            'date_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-item-date' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'date_typography',
                'label'    => __('Typography', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-timeline-item-date',
            )
        );

        $this->end_controls_section();

        // Content Style
        $this->start_controls_section(
            'section_content_style',
            array(
                'label'     => __('Content', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_excerpt' => 'yes',
                ),
            )
        );

        $this->add_control(
            'content_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-item-excerpt' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'content_typography',
                'label'    => __('Typography', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-timeline-item-excerpt',
            )
        );

        $this->end_controls_section();

        // Read More Button Style
        $this->start_controls_section(
            'section_button_style',
            array(
                'label'     => __('Read More Button', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_read_more' => 'yes',
                ),
            )
        );

        $this->start_controls_tabs('button_style_tabs');

        // Normal State
        $this->start_controls_tab(
            'button_normal',
            array(
                'label' => __('Normal', 'dailybuddy'),
            )
        );

        $this->add_control(
            'button_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-read-more-btn' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_background_color',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#3498db',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-read-more-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'button_hover',
            array(
                'label' => __('Hover', 'dailybuddy'),
            )
        );

        $this->add_control(
            'button_hover_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-read-more-btn:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_hover_background_color',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#2980b9',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-timeline-read-more-btn:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'      => 'button_typography',
                'label'     => __('Typography', 'dailybuddy'),
                'selector'  => '{{WRAPPER}} .dailybuddy-timeline-read-more-btn',
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'button_border',
                'label'    => __('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-timeline-read-more-btn',
            )
        );

        $this->add_control(
            'button_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-read-more-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'button_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'default'    => array(
                    'top'    => '10',
                    'right'  => '20',
                    'bottom' => '10',
                    'left'   => '20',
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-timeline-read-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'button_box_shadow',
                'label'    => __('Box Shadow', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-timeline-read-more-btn',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get available post types
     */
    private function get_post_types()
    {
        $post_types = get_post_types(array('public' => true), 'objects');
        $options = array();

        foreach ($post_types as $post_type) {
            $options[$post_type->name] = $post_type->label;
        }

        return $options;
    }

    /**
     * Render the widget
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $layout = $settings['timeline_layout'];

?>
        <div class="dailybuddy-timeline-wrapper dailybuddy-timeline-<?php echo esc_attr($layout); ?>">
            <?php
            if ('dynamic' === $settings['content_source']) {
                $this->render_dynamic_timeline($settings);
            } else {
                $this->render_custom_timeline($settings);
            }
            ?>
        </div>
    <?php
    }

    /**
     * Render dynamic timeline from posts
     */
    private function render_dynamic_timeline($settings)
    {
        $query_args = array(
            'post_type'      => $settings['post_type'],
            'posts_per_page' => $settings['posts_per_page'],
            'orderby'        => $settings['order_by'],
            'order'          => $settings['order'],
            'post_status'    => 'publish',
        );

        $query = new \WP_Query($query_args);

        if ($query->have_posts()) {
            echo '<div class="dailybuddy-timeline">';
            echo '<div class="dailybuddy-timeline-line"></div>';

            $index = 0;
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_timeline_item($settings, array(
                    'title'     => get_the_title(),
                    'content'   => get_the_excerpt(),
                    'date'      => get_the_date(),
                    'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
                    'link'      => get_permalink(),
                ), $index);
                $index++;
            }

            echo '</div>';
            wp_reset_postdata();
        }
    }

    /**
     * Render custom timeline
     */
    private function render_custom_timeline($settings)
    {
        $items = $settings['custom_content_items'];

        if (!empty($items)) {
            echo '<div class="dailybuddy-timeline">';
            echo '<div class="dailybuddy-timeline-line"></div>';

            foreach ($items as $index => $item) {
                $this->render_timeline_item($settings, array(
                    'title'     => $item['custom_title'],
                    'content'   => $item['custom_content'],
                    'date'      => $item['custom_date'],
                    'image_url' => $item['custom_image']['url'],
                    'link'      => $item['custom_link']['url'],
                    'is_external' => $item['custom_link']['is_external'],
                    'nofollow'  => $item['custom_link']['nofollow'],
                    'custom_icon' => !empty($item['custom_icon']) ? $item['custom_icon'] : null,
                ), $index);
            }

            echo '</div>';
        }
    }

    /**
     * Render individual timeline item
     */
    private function render_timeline_item($settings, $data, $index = 0)
    {
        $dailybuddy_target = !empty($data['is_external']) ? ' target="_blank"' : '';
        $dailybuddy_nofollow = !empty($data['nofollow']) ? ' rel="nofollow"' : '';

        // Use custom icon if available, otherwise use default icon
        $icon_to_render = !empty($data['custom_icon']) ? $data['custom_icon'] : array(
            'value'   => 'fas fa-circle',
            'library' => 'fa-solid',
        );

    ?>
        <div class="dailybuddy-timeline-item">
            <div class="dailybuddy-timeline-marker">
                <div class="dailybuddy-timeline-icon">
                    <?php \Elementor\Icons_Manager::render_icon($icon_to_render, ['aria-hidden' => 'true']); ?>
                </div>
            </div>

            <div class="dailybuddy-timeline-item-content">
                <?php if ('yes' === $settings['show_date'] && !empty($data['date'])): ?>
                    <div class="dailybuddy-timeline-item-date">
                        <?php echo esc_html($data['date']); ?>
                    </div>
                <?php endif; ?>

                <?php if ('yes' === $settings['show_image'] && !empty($data['image_url'])): ?>
                    <div class="dailybuddy-timeline-item-image">
                        <img src="<?php echo esc_url($data['image_url']); ?>" alt="<?php echo esc_attr($data['title']); ?>">
                    </div>
                <?php endif; ?>

                <?php if ('yes' === $settings['show_title']): ?>
                    <h3 class="dailybuddy-timeline-item-title">
                        <?php if (!empty($data['link'])): ?>
                            <a href="<?php echo esc_url($data['link']); ?>" <?php echo esc_attr($dailybuddy_target . $dailybuddy_nofollow); ?>>
                                <?php echo esc_html($data['title']); ?>
                            </a>
                        <?php else: ?>
                            <?php echo esc_html($data['title']); ?>
                        <?php endif; ?>
                    </h3>
                <?php endif; ?>

                <?php if ('yes' === $settings['show_excerpt'] && !empty($data['content'])): ?>
                    <div class="dailybuddy-timeline-item-excerpt">
                        <?php echo wp_kses_post($data['content']); ?>
                    </div>
                <?php endif; ?>

                <?php if ('yes' === $settings['show_read_more'] && !empty($data['link'])): ?>
                    <div class="dailybuddy-timeline-read-more">
                        <a href="<?php echo esc_url($data['link']); ?>"
                            class="dailybuddy-timeline-read-more-btn"
                            <?php echo esc_attr($dailybuddy_target . $dailybuddy_nofollow); ?>>
                            <?php echo esc_html($settings['read_more_text']); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
