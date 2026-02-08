<?php

/**
 * dailybuddy Logo Carousel Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Icons_Manager;

class Dailybuddy_Elementor_Logo_Carousel_Widget extends Widget_Base
{

    /**
     * Get widget name
     */
    public function get_name()
    {
        return 'dailybuddy-logo-carousel';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('Logo Carousel', 'dailybuddy');
    }

    /**
     * Get widget icon
     */
    public function get_icon()
    {
        return 'eicon-slider-push mini-icon-dailybuddy';
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
        return array('logo', 'carousel', 'slider', 'brand', 'partner', 'client', 'dailybuddy');
    }

    /**
     * Get style dependencies
     */
    public function get_style_depends()
    {
        return array('swiper', 'font-awesome-5-all', 'font-awesome-4-shim');
    }

    /**
     * Get script dependencies
     */
    public function get_script_depends()
    {
        return array('swiper', 'font-awesome-4-shim');
    }

    /**
     * Register widget controls
     */
    protected function register_controls()
    {
        $this->register_carousel_content_controls();
        $this->register_carousel_settings_controls();
        $this->register_style_controls();
    }

    /**
     * Register Carousel Content Controls
     */
    private function register_carousel_content_controls()
    {
        $this->start_controls_section(
            'section_carousel_content',
            array(
                'label' => __('Logo Carousel', 'dailybuddy'),
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'logo_image',
            array(
                'label'   => __('Upload Logo', 'dailybuddy'),
                'type'    => Controls_Manager::MEDIA,
                'default' => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
            )
        );

        $repeater->add_control(
            'logo_title',
            array(
                'label'       => __('Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'hide_logo_title',
            array(
                'label'        => __('Hide Title?', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $repeater->add_control(
            'logo_alt',
            array(
                'label' => __('Alt Text', 'dailybuddy'),
                'type'  => Controls_Manager::TEXT,
            )
        );

        $repeater->add_control(
            'logo_link',
            array(
                'label'       => __('Link', 'dailybuddy'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'dailybuddy'),
            )
        );

        $this->add_control(
            'carousel_slides',
            array(
                'label'       => __('Logos', 'dailybuddy'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => array(
                    array(
                        'logo_image' => array('url' => Utils::get_placeholder_image_src()),
                        'logo_title' => __('Logo 1', 'dailybuddy'),
                    ),
                    array(
                        'logo_image' => array('url' => Utils::get_placeholder_image_src()),
                        'logo_title' => __('Logo 2', 'dailybuddy'),
                    ),
                    array(
                        'logo_image' => array('url' => Utils::get_placeholder_image_src()),
                        'logo_title' => __('Logo 3', 'dailybuddy'),
                    ),
                    array(
                        'logo_image' => array('url' => Utils::get_placeholder_image_src()),
                        'logo_title' => __('Logo 4', 'dailybuddy'),
                    ),
                ),
                'title_field' => '{{{ logo_title }}}',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Carousel Settings Controls
     */
    private function register_carousel_settings_controls()
    {
        $this->start_controls_section(
            'section_carousel_settings',
            array(
                'label' => __('Carousel Settings', 'dailybuddy'),
            )
        );

        $this->add_responsive_control(
            'items',
            array(
                'label'          => __('Items Per View', 'dailybuddy'),
                'type'           => Controls_Manager::SLIDER,
                'range'          => array(
                    'px' => array(
                        'min' => 1,
                        'max' => 10,
                    ),
                ),
                'default'        => array(
                    'size' => 4,
                ),
                'tablet_default' => array(
                    'size' => 3,
                ),
                'mobile_default' => array(
                    'size' => 2,
                ),
            )
        );

        $this->add_responsive_control(
            'margin',
            array(
                'label'          => __('Spacing Between Items', 'dailybuddy'),
                'type'           => Controls_Manager::SLIDER,
                'range'          => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 100,
                    ),
                ),
                'default'        => array(
                    'size' => 30,
                ),
                'tablet_default' => array(
                    'size' => 20,
                ),
                'mobile_default' => array(
                    'size' => 10,
                ),
            )
        );

        $this->add_control(
            'carousel_effect',
            array(
                'label'   => __('Effect', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'slide',
                'options' => array(
                    'slide'     => __('Slide', 'dailybuddy'),
                    'fade'      => __('Fade', 'dailybuddy'),
                    'cube'      => __('Cube', 'dailybuddy'),
                    'coverflow' => __('Coverflow', 'dailybuddy'),
                    'flip'      => __('Flip', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'slider_speed',
            array(
                'label'   => __('Slider Speed (ms)', 'dailybuddy'),
                'type'    => Controls_Manager::SLIDER,
                'range'   => array(
                    'px' => array(
                        'min'  => 100,
                        'max'  => 3000,
                        'step' => 100,
                    ),
                ),
                'default' => array(
                    'size' => 400,
                ),
            )
        );

        $this->add_control(
            'autoplay',
            array(
                'label'        => __('Autoplay', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'autoplay_speed',
            array(
                'label'     => __('Autoplay Speed (ms)', 'dailybuddy'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => array(
                    'px' => array(
                        'min'  => 500,
                        'max'  => 10000,
                        'step' => 100,
                    ),
                ),
                'default'   => array(
                    'size' => 3000,
                ),
                'condition' => array(
                    'autoplay' => 'yes',
                ),
            )
        );

        $this->add_control(
            'pause_on_hover',
            array(
                'label'        => __('Pause on Hover', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'autoplay' => 'yes',
                ),
            )
        );

        $this->add_control(
            'infinite_loop',
            array(
                'label'        => __('Infinite Loop', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'grab_cursor',
            array(
                'label'        => __('Grab Cursor', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'direction',
            array(
                'label'   => __('Direction', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => array(
                    'left'  => __('Left', 'dailybuddy'),
                    'right' => __('Right', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'navigation_heading',
            array(
                'label'     => __('Navigation', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'arrows',
            array(
                'label'        => __('Show Arrows', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'arrow_icon_next',
            array(
                'label'     => __('Next Arrow Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-chevron-right',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'arrows' => 'yes',
                ),
            )
        );

        $this->add_control(
            'arrow_icon_prev',
            array(
                'label'     => __('Previous Arrow Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-chevron-left',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'arrows' => 'yes',
                ),
            )
        );

        $this->add_control(
            'dots',
            array(
                'label'        => __('Show Dots', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'dots_position',
            array(
                'label'     => __('Dots Position', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'inside',
                'options'   => array(
                    'inside'  => __('Inside', 'dailybuddy'),
                    'outside' => __('Outside', 'dailybuddy'),
                ),
                'condition' => array(
                    'dots' => 'yes',
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
        // Container Style
        $this->start_controls_section(
            'section_container_style',
            array(
                'label' => __('Container', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'container_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-logo-carousel-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Logo Item Style
        $this->start_controls_section(
            'section_logo_item_style',
            array(
                'label' => __('Logo Item', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'logo_item_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-lc-logo-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'logo_item_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-lc-logo-wrap',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'logo_item_border',
                'selector' => '{{WRAPPER}} .dailybuddy-lc-logo-wrap',
            )
        );

        $this->add_responsive_control(
            'logo_item_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-lc-logo-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'logo_item_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-lc-logo-wrap',
            )
        );

        $this->add_control(
            'grayscale_heading',
            array(
                'label'     => __('Grayscale Effect', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'grayscale_normal',
            array(
                'label'        => __('Grayscale in Normal State', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'grayscale_hover',
            array(
                'label'        => __('Grayscale on Hover', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->end_controls_section();

        // Logo Image Style
        $this->start_controls_section(
            'section_logo_image_style',
            array(
                'label' => __('Logo Image', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'logo_width',
            array(
                'label'      => __('Width', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', '%'),
                'range'      => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 500,
                    ),
                    '%'  => array(
                        'min' => 10,
                        'max' => 100,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-lc-img' => 'width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'logo_height',
            array(
                'label'      => __('Height', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 500,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-lc-img' => 'height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'logo_object_fit',
            array(
                'label'     => __('Object Fit', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'contain',
                'options'   => array(
                    'contain' => __('Contain', 'dailybuddy'),
                    'cover'   => __('Cover', 'dailybuddy'),
                    'fill'    => __('Fill', 'dailybuddy'),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-lc-img' => 'object-fit: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'logo_opacity',
            array(
                'label'     => __('Opacity', 'dailybuddy'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-lc-img' => 'opacity: {{SIZE}};',
                ),
            )
        );

        $this->add_control(
            'logo_hover_opacity',
            array(
                'label'     => __('Hover Opacity', 'dailybuddy'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-lc-logo:hover .dailybuddy-lc-img' => 'opacity: {{SIZE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'logo_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-lc-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Title Style
        $this->start_controls_section(
            'section_title_style',
            array(
                'label' => __('Title', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-logo-carousel-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-logo-carousel-title',
            )
        );

        $this->add_responsive_control(
            'title_spacing',
            array(
                'label'      => __('Top Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 100,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-logo-carousel-title' => 'margin-top: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'title_alignment',
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
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-logo-carousel-title' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // Navigation Style
        $this->register_navigation_style_controls();
    }

    /**
     * Register Navigation Style Controls
     */
    private function register_navigation_style_controls()
    {
        // Arrows Style
        $this->start_controls_section(
            'section_arrows_style',
            array(
                'label'     => __('Arrows', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'arrows' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'arrows_size',
            array(
                'label'      => __('Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 100,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .swiper-button-next svg, {{WRAPPER}} .swiper-button-prev svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs('arrows_style_tabs');

        $this->start_controls_tab(
            'arrows_normal',
            array(
                'label' => __('Normal', 'dailybuddy'),
            )
        );

        $this->add_control(
            'arrows_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .swiper-button-next svg, {{WRAPPER}} .swiper-button-prev svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'arrows_background',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'arrows_hover',
            array(
                'label' => __('Hover', 'dailybuddy'),
            )
        );

        $this->add_control(
            'arrows_hover_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-button-next:hover, {{WRAPPER}} .swiper-button-prev:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .swiper-button-next:hover svg, {{WRAPPER}} .swiper-button-prev:hover svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'arrows_hover_background',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-button-next:hover, {{WRAPPER}} .swiper-button-prev:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'arrows_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px'),
                'separator'  => 'before',
                'selectors'  => array(
                    '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'arrows_border',
                'selector' => '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev',
            )
        );

        $this->add_responsive_control(
            'arrows_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Dots Style
        $this->start_controls_section(
            'section_dots_style',
            array(
                'label'     => __('Dots', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'dots' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'dots_size',
            array(
                'label'      => __('Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 5,
                        'max' => 30,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'dots_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'dots_active_color',
            array(
                'label'     => __('Active Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-pagination-bullet-active' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'dots_spacing',
            array(
                'label'      => __('Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .swiper-pagination' => 'margin-top: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $id       = $this->get_id();

        // Wrapper attributes
        $this->add_render_attribute('carousel-wrap', 'class', 'dailybuddy-logo-carousel-wrap');
        if (!empty($settings['dots_position'])) {
            $this->add_render_attribute('carousel-wrap', 'class', 'dots-' . $settings['dots_position']);
        }

        // Carousel attributes
        $this->add_render_attribute('carousel', 'class', 'dailybuddy-logo-carousel swiper');
        $this->add_render_attribute('carousel', 'class', 'swiper-container-' . esc_attr($id));
        $this->add_render_attribute('carousel', 'data-id', esc_attr($id));

        // Carousel settings
        $carousel_settings = array(
            'items_desktop'  => !empty($settings['items']['size']) ? $settings['items']['size'] : 4,
            'items_tablet'   => !empty($settings['items_tablet']['size']) ? $settings['items_tablet']['size'] : 3,
            'items_mobile'   => !empty($settings['items_mobile']['size']) ? $settings['items_mobile']['size'] : 2,
            'margin_desktop' => !empty($settings['margin']['size']) ? $settings['margin']['size'] : 30,
            'margin_tablet'  => !empty($settings['margin_tablet']['size']) ? $settings['margin_tablet']['size'] : 20,
            'margin_mobile'  => !empty($settings['margin_mobile']['size']) ? $settings['margin_mobile']['size'] : 10,
            'effect'         => $settings['carousel_effect'],
            'speed'          => !empty($settings['slider_speed']['size']) ? $settings['slider_speed']['size'] : 400,
            'autoplay'       => $settings['autoplay'] === 'yes',
            'autoplay_speed' => !empty($settings['autoplay_speed']['size']) ? $settings['autoplay_speed']['size'] : 3000,
            'pause_on_hover' => $settings['pause_on_hover'] === 'yes',
            'loop'           => $settings['infinite_loop'] === 'yes',
            'grab_cursor'    => $settings['grab_cursor'] === 'yes',
            'arrows'         => $settings['arrows'] === 'yes',
            'dots'           => $settings['dots'] === 'yes',
        );

        $this->add_render_attribute('carousel', 'data-settings', wp_json_encode($carousel_settings));

        // Direction
        if ($settings['direction'] === 'right') {
            $this->add_render_attribute('carousel', 'dir', 'rtl');
        }

        // Grayscale classes
        if ($settings['grayscale_normal'] === 'yes') {
            $this->add_render_attribute('carousel', 'class', 'grayscale-normal');
        }
        if ($settings['grayscale_hover'] === 'yes') {
            $this->add_render_attribute('carousel', 'class', 'grayscale-hover');
        }
?>

        <div <?php $this->print_render_attribute_string('carousel-wrap'); ?>>
            <div <?php $this->print_render_attribute_string('carousel'); ?>>
                <div class="swiper-wrapper">
                    <?php
                    $i = 1;
                    foreach ($settings['carousel_slides'] as $index => $item):
                        if (!empty($item['logo_image']['url'])):
                            $logo_key = 'logo_' . $i;
                    ?>
                            <div class="swiper-slide">
                                <div class="dailybuddy-lc-logo-wrap">
                                    <div class="dailybuddy-lc-logo">
                                        <?php
                                        $is_linked = false;
                                        if (!empty($item['logo_link']['url'])) {
                                            $is_linked = true;
                                            $this->add_link_attributes($logo_key, $item['logo_link']);
                                        }

                                        if ($is_linked) {
                                            // Use print_render_attribute_string() for proper escaping
                                            echo '<a ';
                                            $this->print_render_attribute_string($logo_key);
                                            echo '>';
                                        }
                                        ?>
                                        <img class="dailybuddy-lc-img"
                                            src="<?php echo esc_url($item['logo_image']['url']); ?>"
                                            alt="<?php echo esc_attr($item['logo_alt']); ?>">
                                        <?php
                                        if ($is_linked) {
                                            echo '</a>';
                                        }
                                        ?>
                                    </div>
                                    <?php
                                    if (!empty($item['logo_title']) && $item['hide_logo_title'] !== 'yes') {
                                        echo '<h3 class="dailybuddy-logo-carousel-title">';
                                        if ($is_linked) {
                                            // Use print_render_attribute_string() for proper escaping
                                            echo '<a ';
                                            $this->print_render_attribute_string($logo_key);
                                            echo '>';
                                        }
                                        echo esc_html($item['logo_title']);
                                        if ($is_linked) {
                                            echo '</a>';
                                        }
                                        echo '</h3>';
                                    }
                                    ?>
                                </div>
                            </div>
                    <?php
                            $i++;
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <?php $this->render_navigation($settings, $id); ?>
        </div>

        <?php
    }

    /**
     * Render navigation (arrows and dots)
     */
    private function render_navigation($settings, $id)
    {
        // Render Arrows
        if ($settings['arrows'] === 'yes') {
        ?>
            <div class="swiper-button-next swiper-button-next-<?php echo esc_attr($id); ?>">
                <?php
                if (!empty($settings['arrow_icon_next']['value'])) {
                    Icons_Manager::render_icon($settings['arrow_icon_next'], array('aria-hidden' => 'true'));
                }
                ?>
            </div>
            <div class="swiper-button-prev swiper-button-prev-<?php echo esc_attr($id); ?>">
                <?php
                if (!empty($settings['arrow_icon_prev']['value'])) {
                    Icons_Manager::render_icon($settings['arrow_icon_prev'], array('aria-hidden' => 'true'));
                }
                ?>
            </div>
        <?php
        }

        // Render Dots
        if ($settings['dots'] === 'yes') {
        ?>
            <div class="swiper-pagination swiper-pagination-<?php echo esc_attr($id); ?>"></div>
<?php
        }
    }
}
