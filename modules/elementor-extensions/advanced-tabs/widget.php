<?php

/**
 * dailybuddy Advanced Tabs Widget
 * Adapted for dailybuddy plugin
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
use Elementor\Icons_Manager;
use Elementor\Plugin;

class WP_Dailybuddy_Elementor_Advanced_Tabs_Widget extends Widget_Base
{
    /**
     * Get widget name
     */
    public function get_name()
    {
        return 'dailybuddy-advanced-tabs';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('Advanced Tabs', 'dailybuddy');
    }

    /**
     * Get widget icon
     */
    public function get_icon()
    {
        return 'eicon-tabs mini-icon-dailybuddy';
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
        return array('tabs', 'accordion', 'toggle', 'content', 'navigation', 'dailybuddy');
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
        $this->register_general_settings();
        $this->register_content_settings();
        $this->register_style_settings();
    }

    /**
     * Register General Settings
     */
    private function register_general_settings()
    {
        $this->start_controls_section(
            'dailybuddy_section_adv_tabs_settings',
            array(
                'label' => __('General Settings', 'dailybuddy'),
            )
        );

        // Style Selection with new designs
        $this->add_control(
            'dailybuddy_adv_tab_new_style',
            array(
                'label'       => __('Tab Style', 'dailybuddy'),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'default',
                'label_block' => true,
                'options'     => array(
                    'default'     => __('Default', 'dailybuddy'),
                    'modern-card' => __('Modern Card', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tab_layout',
            array(
                'label'       => __('Layout', 'dailybuddy'),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'dailybuddy-tabs-horizontal',
                'label_block' => false,
                'options'     => array(
                    'dailybuddy-tabs-horizontal' => __('Horizontal', 'dailybuddy'),
                    'dailybuddy-tabs-vertical'   => __('Vertical', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_alignment',
            array(
                'label'       => __('Tab Alignment', 'dailybuddy'),
                'type'        => Controls_Manager::CHOOSE,
                'default'     => 'left',
                'options'     => array(
                    'left'    => array(
                        'title' => __('Left', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center'  => array(
                        'title' => __('Center', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'   => array(
                        'title' => __('Right', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-right',
                    ),
                    'stretch' => array(
                        'title' => __('Stretch', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-justify',
                    ),
                ),
                'condition'   => array(
                    'dailybuddy_adv_tab_layout' => 'dailybuddy-tabs-horizontal',
                ),
                'selectors'   => array(
                    '{{WRAPPER}} .dailybuddy-tabs-horizontal .dailybuddy-tabs-nav ul' => 'justify-content: {{VALUE}};',
                ),
                'selectors_dictionary' => array(
                    'left'    => 'flex-start',
                    'center'  => 'center',
                    'right'   => 'flex-end',
                    'stretch' => 'space-between',
                ),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_icon_show',
            array(
                'label'        => __('Enable Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
            )
        );

        $this->add_control(
            'dailybuddy_adv_tab_icon_position',
            array(
                'label'       => __('Icon Position', 'dailybuddy'),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'dailybuddy-tab-inline-icon',
                'label_block' => false,
                'options'     => array(
                    'dailybuddy-tab-top-icon'    => __('Stacked', 'dailybuddy'),
                    'dailybuddy-tab-inline-icon' => __('Inline', 'dailybuddy'),
                ),
                'condition'   => array(
                    'dailybuddy_adv_tabs_icon_show' => 'yes',
                ),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_tab_icon_alignment',
            array(
                'label'       => __('Icon Alignment', 'dailybuddy'),
                'description' => __('Set icon position before/after the tab title.', 'dailybuddy'),
                'type'        => Controls_Manager::CHOOSE,
                'default'     => 'before',
                'options'     => array(
                    'before' => array(
                        'title' => __('Before', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-left',
                    ),
                    'after'  => array(
                        'title' => __('After', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-right',
                    ),
                ),
                'condition'   => array(
                    'dailybuddy_adv_tab_icon_position' => 'dailybuddy-tab-inline-icon',
                ),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_default_active_tab',
            array(
                'label'         => __('Auto Active?', 'dailybuddy'),
                'type'          => Controls_Manager::SWITCHER,
                'description'   => __('Activate the first tab if no tab is selected as the active tab.', 'dailybuddy'),
                'default'       => 'yes',
                'return_value'  => 'yes',
                'label_on'      => __('Yes', 'dailybuddy'),
                'label_off'     => __('No', 'dailybuddy'),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_toggle_tab',
            array(
                'label'         => __('Toggle Tab', 'dailybuddy'),
                'type'          => Controls_Manager::SWITCHER,
                'description'   => __('Enables tab to expand and collapse.', 'dailybuddy'),
                'default'       => '',
                'return_value'  => 'yes',
                'label_on'      => __('Yes', 'dailybuddy'),
                'label_off'     => __('No', 'dailybuddy'),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_custom_id_offset',
            array(
                'label'       => __('Custom ID offset', 'dailybuddy'),
                'description' => __('Use offset to set the custom ID target scrolling position.', 'dailybuddy'),
                'type'        => Controls_Manager::NUMBER,
                'label_block' => false,
                'default'     => 0,
                'min'         => 0,
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_scroll_speed',
            array(
                'label'       => __('Scroll Speed (ms)', 'dailybuddy'),
                'type'        => Controls_Manager::NUMBER,
                'label_block' => false,
                'default'     => 300,
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_scroll_onclick',
            array(
                'label'        => __('Scroll on Click', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'no',
                'return_value' => 'yes',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Content Settings
     */
    private function register_content_settings()
    {
        $this->start_controls_section(
            'dailybuddy_section_adv_tabs_content_settings',
            array(
                'label' => __('Content', 'dailybuddy'),
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_show_as_default',
            array(
                'label'        => __('Active as Default', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'inactive',
                'return_value' => 'active-default',
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_show_as_scheduled',
            array(
                'label'        => __('Active as Scheduled', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'dailybuddy'),
                'label_off'    => __('No', 'dailybuddy'),
                'return_value' => 'yes',
                'default'      => 'no',
                'description'  => __('When enabled, this tab will become active if the current date matches the scheduled date/time.', 'dailybuddy'),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_schedule_date',
            array(
                'label'   => __('Start Date', 'dailybuddy'),
                'type'    => Controls_Manager::DATE_TIME,
                'default' => gmdate('Y-m-d H:i', current_time('timestamp', 0)),
                'condition' => array(
                    'dailybuddy_adv_tabs_tab_show_as_scheduled' => 'yes',
                ),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_schedule_end_date',
            array(
                'label'   => __('End Date', 'dailybuddy'),
                'type'    => Controls_Manager::DATE_TIME,
                'default' => '',
                'condition' => array(
                    'dailybuddy_adv_tabs_tab_show_as_scheduled' => 'yes',
                ),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_icon_type',
            array(
                'label'       => __('Icon Type', 'dailybuddy'),
                'type'        => Controls_Manager::CHOOSE,
                'label_block' => false,
                'options'     => array(
                    'none'  => array(
                        'title' => __('None', 'dailybuddy'),
                        'icon'  => 'fa fa-ban',
                    ),
                    'icon'  => array(
                        'title' => __('Icon', 'dailybuddy'),
                        'icon'  => 'eicon-icon-box',
                    ),
                    'image' => array(
                        'title' => __('Image', 'dailybuddy'),
                        'icon'  => 'eicon-image-bold',
                    ),
                ),
                'default'     => 'icon',
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_title_icon_new',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-home',
                    'library' => 'fa-solid',
                ),
                'condition' => array(
                    'dailybuddy_adv_tabs_icon_type' => 'icon',
                ),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_title_image',
            array(
                'label'     => __('Image', 'dailybuddy'),
                'type'      => Controls_Manager::MEDIA,
                'default'   => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
                'condition' => array(
                    'dailybuddy_adv_tabs_icon_type' => 'image',
                ),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_title',
            array(
                'name'    => 'dailybuddy_adv_tabs_tab_title',
                'label'   => __('Tab Title', 'dailybuddy'),
                'type'    => Controls_Manager::TEXT,
                'default' => __('Tab Title', 'dailybuddy'),
                'dynamic' => array('active' => true),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_title_html_tag',
            array(
                'name'    => 'dailybuddy_adv_tabs_tab_title',
                'label'   => __('Title HTML Tag', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h1'   => 'H1',
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'h4'   => 'H4',
                    'h5'   => 'H5',
                    'h6'   => 'H6',
                    'div'  => 'div',
                    'span' => 'span',
                    'p'    => 'p',
                ),
                'default' => 'span',
                'dynamic' => array('active' => true),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_text_type',
            array(
                'label'   => __('Content Type', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'content'  => __('Content', 'dailybuddy'),
                    'template' => __('Saved Templates', 'dailybuddy'),
                ),
                'default' => 'content',
            )
        );

        $repeater->add_control(
            'dailybuddy_primary_templates',
            array(
                'label'       => __('Choose Template', 'dailybuddy'),
                'type'        => Controls_Manager::SELECT,
                'options'     => $this->get_elementor_templates(),
                'label_block' => true,
                'condition'   => array(
                    'dailybuddy_adv_tabs_text_type' => 'template',
                ),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_content',
            array(
                'label'     => __('Tab Content', 'dailybuddy'),
                'type'      => Controls_Manager::WYSIWYG,
                'default'   => __('Enhance user experience with Advanced Tabs, allowing seamless content navigation. Organize information efficiently while keeping the interface clean and interactive.', 'dailybuddy'),
                'dynamic'   => array('active' => true),
                'condition' => array(
                    'dailybuddy_adv_tabs_text_type' => 'content',
                ),
            )
        );

        $repeater->add_control(
            'dailybuddy_adv_tabs_tab_id',
            array(
                'label'       => __('Custom ID', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'description' => __('Custom ID will be added as an anchor tag. For example, if you add "test" as your custom ID, the link will become like: https://www.example.com/#test', 'dailybuddy'),
                'default'     => '',
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_tab',
            array(
                'type'        => Controls_Manager::REPEATER,
                'seperator'   => 'before',
                'default'     => array(
                    array(
                        'dailybuddy_adv_tabs_tab_title'          => __('Mission', 'dailybuddy'),
                        'dailybuddy_adv_tabs_tab_title_icon_new' => array(
                            'value'   => 'far fa-lightbulb',
                            'library' => 'fa-solid',
                        ),
                    ),
                    array(
                        'dailybuddy_adv_tabs_tab_title'          => __('Vision', 'dailybuddy'),
                        'dailybuddy_adv_tabs_tab_title_icon_new' => array(
                            'value'   => 'fas fa-eye',
                            'library' => 'fa-solid',
                        ),
                    ),
                    array(
                        'dailybuddy_adv_tabs_tab_title'          => __('Philosophy', 'dailybuddy'),
                        'dailybuddy_adv_tabs_tab_title_icon_new' => array(
                            'value'   => 'fas fa-filter',
                            'library' => 'fa-solid',
                        ),
                    ),
                ),
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{dailybuddy_adv_tabs_tab_title}}',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get Elementor templates
     */
    private function get_elementor_templates()
    {
        $templates = array('' => __('Select Template', 'dailybuddy'));

        $args = array(
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );

        $page_templates = get_posts($args);

        if (!empty($page_templates) && !is_wp_error($page_templates)) {
            foreach ($page_templates as $post) {
                $templates[$post->ID] = $post->post_title;
            }
        }

        return $templates;
    }

    /**
     * Register Style Settings
     */
    private function register_style_settings()
    {
        // General Style
        $this->start_controls_section(
            'dailybuddy_section_adv_tabs_style_settings',
            array(
                'label' => __('General', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_margin',
            array(
                'label'      => __('Margin', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_border',
                'label'    => __('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs',
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs',
            )
        );

        $this->end_controls_section();

        // Tab Title Style
        $this->start_controls_section(
            'dailybuddy_section_adv_tabs_tab_style_settings',
            array(
                'label' => __('Tab Title', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_tab_title_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li .dailybuddy-tab-title',
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_tab_icon_size',
            array(
                'label'      => __('Icon Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'default'    => array(
                    'size' => 16,
                    'unit' => 'px',
                ),
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li img' => 'width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_tab_icon_gap',
            array(
                'label'      => __('Icon Gap', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'default'    => array(
                    'size' => 10,
                    'unit' => 'px',
                ),
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.dailybuddy-tab-inline-icon i' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.dailybuddy-tab-inline-icon svg' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.dailybuddy-tab-inline-icon img' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.dailybuddy-tab-top-icon i' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.dailybuddy-tab-top-icon svg' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.dailybuddy-tab-top-icon img' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_tab_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_tab_margin',
            array(
                'label'      => __('Margin', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs('dailybuddy_adv_tabs_tab_style_tabs');

        // Normal State Tab
        $this->start_controls_tab(
            'dailybuddy_adv_tabs_tab_style_normal',
            array(
                'label' => __('Normal', 'dailybuddy'),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_tab_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#444',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_tab_icon_color',
            array(
                'label'     => __('Icon Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#444',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_tab_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_tab_border',
                'label'    => __('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li',
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_tab_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_tab();

        // Active State Tab
        $this->start_controls_tab(
            'dailybuddy_adv_tabs_tab_style_active',
            array(
                'label' => __('Active', 'dailybuddy'),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_tab_color_active',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#fff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active-default' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_tab_icon_color_active',
            array(
                'label'     => __('Icon Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#fff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active-default i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active-default svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_tab_background_active',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active, {{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active-default',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_tab_border_active',
                'label'    => __('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active, {{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active-default',
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_tab_border_radius_active',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-nav > ul li.active-default' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Tab Content Style
        $this->start_controls_section(
            'dailybuddy_section_adv_tabs_content_style_settings',
            array(
                'label' => __('Tab Content', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_content_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div',
            )
        );

        $this->add_control(
            'dailybuddy_adv_tabs_content_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#333',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_content_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div',
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_content_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_content_margin',
            array(
                'label'      => __('Margin', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_content_border',
                'label'    => __('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div',
            )
        );

        $this->add_responsive_control(
            'dailybuddy_adv_tabs_content_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'dailybuddy_adv_tabs_content_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-advance-tabs .dailybuddy-tabs-content > div',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Helper function to convert string to CSS ID
     */
    private function str_to_css_id($str)
    {
        return preg_replace('/[^a-z0-9-_]/i', '-', strtolower($str));
    }

    /**
     * Helper function to validate HTML tag
     */
    private function validate_html_tag($tag)
    {
        $allowed_tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p');
        return in_array($tag, $allowed_tags) ? $tag : 'span';
    }

    /**
     * Check if template is published
     */
    private function is_elementor_publish_template($template_id)
    {
        if (empty($template_id)) {
            return false;
        }

        $template = get_post($template_id);

        if (!$template || $template->post_status !== 'publish') {
            return false;
        }

        return true;
    }

    /**
     * Render widget output
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $dailybuddy_find_default_tab = array();
        $tab_icon_migrated = isset($settings['__fa4_migrated']['dailybuddy_adv_tabs_tab_title_icon_new']);
        $tab_icon_is_new = empty($settings['dailybuddy_adv_tabs_tab_title_icon']);

        // Check for scheduled tabs
        $scheduled_active_tab_index = null;
        if (!empty($settings['dailybuddy_adv_tabs_tab'])) {
            $current_time = current_time('timestamp');
            foreach ($settings['dailybuddy_adv_tabs_tab'] as $index => $tab) {
                if (isset($tab['dailybuddy_adv_tabs_tab_show_as_scheduled']) && $tab['dailybuddy_adv_tabs_tab_show_as_scheduled'] === 'yes') {
                    $start_time = isset($tab['dailybuddy_adv_tabs_schedule_date']) ? strtotime($tab['dailybuddy_adv_tabs_schedule_date']) : 0;
                    $end_time = !empty($tab['dailybuddy_adv_tabs_schedule_end_date']) ? strtotime($tab['dailybuddy_adv_tabs_schedule_end_date']) : PHP_INT_MAX;

                    if ($current_time >= $start_time && $current_time <= $end_time) {
                        $scheduled_active_tab_index = $index;
                        break;
                    }
                }
            }
        }

        $this->add_render_attribute(
            'dailybuddy_tab_wrapper',
            array(
                'class' => array(
                    'dailybuddy-advance-tabs',
                    esc_attr($settings['dailybuddy_adv_tab_layout']),
                ),
                'data-tabid' => esc_attr($this->get_id()),
            )
        );

        if ($settings['dailybuddy_adv_tabs_toggle_tab'] === 'yes') {
            $this->add_render_attribute('dailybuddy_tab_wrapper', 'data-toggle', 'yes');
        }

        if ($settings['dailybuddy_adv_tabs_default_active_tab'] === 'yes') {
            $this->add_render_attribute('dailybuddy_tab_wrapper', 'data-default-active', 'yes');
        }

        if ($settings['dailybuddy_adv_tabs_scroll_onclick'] === 'yes') {
            $this->add_render_attribute('dailybuddy_tab_wrapper', 'data-scroll-on-click', 'yes');
        }

        if (!empty($settings['dailybuddy_adv_tabs_scroll_speed'])) {
            $this->add_render_attribute('dailybuddy_tab_wrapper', 'data-scroll-speed', esc_attr($settings['dailybuddy_adv_tabs_scroll_speed']));
        }

        if (!empty($settings['dailybuddy_adv_tabs_custom_id_offset'])) {
            $this->add_render_attribute('dailybuddy_tab_wrapper', 'data-custom-id-offset', esc_attr($settings['dailybuddy_adv_tabs_custom_id_offset']));
        }

        $this->add_render_attribute('dailybuddy_tab_icon_position', 'class', esc_attr($settings['dailybuddy_adv_tab_icon_position']));
        $this->add_render_attribute('dailybuddy_tab_icon_position', 'role', 'tablist');

        // Add stretch class if alignment is stretch
        if (isset($settings['dailybuddy_adv_tabs_alignment']) && $settings['dailybuddy_adv_tabs_alignment'] === 'stretch') {
            $this->add_render_attribute('dailybuddy_tab_icon_position', 'class', 'dailybuddy-tabs-stretch');
        }

        // Add style-specific class
        $style_class = '';
        if ($settings['dailybuddy_adv_tab_new_style'] === 'modern-card') {
            $style_class = 'dailybuddy-tabs-modern-card';
        }

        $this->add_render_attribute(
            'dailybuddy_tab_style_wrapper',
            array(
                'class' => array('dailybuddy-tabs-nav', $style_class),
            )
        );

?>
        <div <?php $this->print_render_attribute_string('dailybuddy_tab_wrapper'); ?>>
            <div <?php $this->print_render_attribute_string('dailybuddy_tab_style_wrapper'); ?>>
                <ul <?php $this->print_render_attribute_string('dailybuddy_tab_icon_position'); ?>>
                    <?php foreach ($settings['dailybuddy_adv_tabs_tab'] as $index => $tab) :
                        $tab_id = $tab['dailybuddy_adv_tabs_tab_id'] ? $tab['dailybuddy_adv_tabs_tab_id'] : $this->str_to_css_id($tab['dailybuddy_adv_tabs_tab_title']);
                        $tab_id = $tab_id === 'safari' ? 'dailybuddy-safari' : $tab_id;

                        $tab_count = $index + 1;
                        $tab_title_setting_key = $this->get_repeater_setting_key('dailybuddy_adv_tabs_tab_title', 'dailybuddy_adv_tabs_tab', $index);

                        $tab_active_class = '';
                        if ($scheduled_active_tab_index !== null) {
                            if ($index === $scheduled_active_tab_index) {
                                $tab_active_class = 'active-default';
                            }
                        } else {
                            $tab_active_class = $tab['dailybuddy_adv_tabs_tab_show_as_default'];
                        }

                        $this->add_render_attribute($tab_title_setting_key, array(
                            'id'             => $tab_id,
                            'class'          => array($tab_active_class, 'dailybuddy-tab-item-trigger', 'dailybuddy-tab-nav-item'),
                            'aria-selected'  => 1 === $tab_count ? 'true' : 'false',
                            'data-tab'       => $tab_count,
                            'role'           => 'tab',
                            'tabindex'       => 1 === $tab_count ? '0' : '-1',
                            'aria-controls'  => $tab_id . '-tab',
                            'aria-expanded'  => 'false',
                        ));

                        $repeater_html_tag = !empty($tab['dailybuddy_adv_tabs_tab_title_html_tag']) ? $this->validate_html_tag($tab['dailybuddy_adv_tabs_tab_title_html_tag']) : 'span';
                        $repeater_tab_title = $tab['dailybuddy_adv_tabs_tab_title'];

                    ?>
                        <li <?php $this->print_render_attribute_string($tab_title_setting_key); ?>>
                            <?php if ($settings['dailybuddy_adv_tab_icon_position'] === 'dailybuddy-tab-inline-icon' && $settings['dailybuddy_adv_tabs_tab_icon_alignment'] === 'after') : ?>
                                <?php
                                $this->add_render_attribute($tab_title_setting_key . '_repeater_tab_title_attr', array(
                                    'class' => array('dailybuddy-tab-title', 'title-before-icon'),
                                ));

                                echo '<' . esc_attr($repeater_html_tag) . ' ';
                                $this->print_render_attribute_string($tab_title_setting_key . '_repeater_tab_title_attr');
                                echo ' >';
                                echo wp_kses_post($this->parse_text_editor($repeater_tab_title));
                                echo '</' . esc_attr($repeater_html_tag) . '>';
                                ?>
                            <?php endif; ?>

                            <?php if ($settings['dailybuddy_adv_tabs_icon_show'] === 'yes') :
                                if ($tab['dailybuddy_adv_tabs_icon_type'] === 'icon') : ?>
                                    <?php
                                    if ($tab_icon_is_new || $tab_icon_migrated) {
                                        Icons_Manager::render_icon($tab['dailybuddy_adv_tabs_tab_title_icon_new']);
                                    } else {
                                        echo '<i class="' . esc_attr($tab['dailybuddy_adv_tabs_tab_title_icon']) . '"></i>';
                                    }
                                    ?>
                                <?php elseif ($tab['dailybuddy_adv_tabs_icon_type'] === 'image') : ?>
                                    <img src="<?php echo esc_url($tab['dailybuddy_adv_tabs_tab_title_image']['url']); ?>" alt="<?php echo esc_attr(get_post_meta($tab['dailybuddy_adv_tabs_tab_title_image']['id'], '_wp_attachment_image_alt', true)); ?>">
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($settings['dailybuddy_adv_tab_icon_position'] === 'dailybuddy-tab-inline-icon' && $settings['dailybuddy_adv_tabs_tab_icon_alignment'] !== 'after') : ?>
                                <?php
                                $this->add_render_attribute($tab_title_setting_key . '_repeater_tab_title_attr', array(
                                    'class' => array('dailybuddy-tab-title', 'title-after-icon'),
                                ));

                                echo '<' . esc_attr($repeater_html_tag) . ' ';
                                $this->print_render_attribute_string($tab_title_setting_key . '_repeater_tab_title_attr');
                                echo ' >';
                                echo wp_kses_post($this->parse_text_editor($repeater_tab_title));
                                echo '</' . esc_attr($repeater_html_tag) . '>';
                                ?>
                            <?php endif; ?>

                            <?php if ($settings['dailybuddy_adv_tab_icon_position'] !== 'dailybuddy-tab-inline-icon') : ?>
                                <?php
                                $this->add_render_attribute($tab_title_setting_key . '_repeater_tab_title_attr', array(
                                    'class' => array('dailybuddy-tab-title', 'title-after-icon'),
                                ));

                                echo '<' . esc_attr($repeater_html_tag) . ' ';
                                $this->print_render_attribute_string($tab_title_setting_key . '_repeater_tab_title_attr');
                                echo ' >';
                                echo wp_kses_post($this->parse_text_editor($repeater_tab_title));
                                echo '</' . esc_attr($repeater_html_tag) . '>';
                                ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="dailybuddy-tabs-content">
                <?php foreach ($settings['dailybuddy_adv_tabs_tab'] as $content_index => $tab) :
                    $dailybuddy_find_default_tab[] = $tab['dailybuddy_adv_tabs_tab_show_as_default'];
                    $tab_id = $tab['dailybuddy_adv_tabs_tab_id'] ? $tab['dailybuddy_adv_tabs_tab_id'] : $this->str_to_css_id($tab['dailybuddy_adv_tabs_tab_title']);
                    $tab_id = $tab_id === 'safari' ? 'dailybuddy-safari-tab' : $tab_id . '-tab';

                    $content_active_class = '';
                    if ($scheduled_active_tab_index !== null) {
                        if ($content_index === $scheduled_active_tab_index) {
                            $content_active_class = 'active-default';
                        }
                    } else {
                        $content_active_class = $tab['dailybuddy_adv_tabs_tab_show_as_default'];
                    }
                ?>

                    <div id="<?php echo esc_attr($tab_id); ?>" class="clearfix dailybuddy-tab-content-item <?php echo esc_attr($content_active_class); ?>" data-title-link="<?php echo esc_attr($tab_id); ?>">
                        <?php
                        if ('content' == $tab['dailybuddy_adv_tabs_text_type']) :
                            echo wp_kses_post($this->parse_text_editor($tab['dailybuddy_adv_tabs_tab_content']));

                        elseif ('template' == $tab['dailybuddy_adv_tabs_text_type']) :
                            if (!empty($tab['dailybuddy_primary_templates'])) {
                                $current_page_id = get_the_ID();
                                $revisions = wp_get_post_revisions($current_page_id);
                                $revision_ids = wp_list_pluck($revisions, 'ID');

                                if (absint($tab['dailybuddy_primary_templates']) === $current_page_id || in_array(absint($tab['dailybuddy_primary_templates']), $revision_ids, true)) {
                                    echo '<p>' . esc_html__('The provided Template matches the current page or one of its revisions!', 'dailybuddy') . '</p>';
                                } else {
                                    if (!$this->is_elementor_publish_template($tab['dailybuddy_primary_templates'])) {
                                        continue;
                                    }

                                    // WPML Compatibility
                                    if (! is_array($tab['dailybuddy_primary_templates'])) {
                                        $tab['dailybuddy_primary_templates'] = apply_filters(
                                            'wpml_object_id', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML filter
                                            $tab['dailybuddy_primary_templates'],
                                            'wp_template',
                                            true
                                        );
                                    }

                                    echo wp_kses_post(
                                        Plugin::$instance->frontend->get_builder_content(
                                            $tab['dailybuddy_primary_templates'],
                                            true
                                        )
                                    );
                                }
                            }
                        endif;
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
<?php
    }
}
