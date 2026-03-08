<?php

/**
 * dailybuddy Process Steps Widget
 *
 * Displays step-by-step processes with icons, titles, descriptions,
 * and connectors. Supports loop mode for recurring processes.
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Repeater;

class Dailybuddy_Elementor_Process_Steps_Widget extends Widget_Base
{
    public function get_name()
    {
        return 'dailybuddy-process-steps';
    }

    public function get_title()
    {
        return __('Process Steps', 'dailybuddy');
    }

    public function get_icon()
    {
        return 'eicon-navigation-horizontal mini-icon-dailybuddy';
    }

    public function get_categories()
    {
        return array('dailybuddy');
    }

    public function get_keywords()
    {
        return array('process', 'steps', 'workflow', 'walkthrough', 'timeline', 'dailybuddy');
    }

    protected function register_controls()
    {
        $this->register_content_controls();
        $this->register_settings_controls();
        $this->register_style_icon_controls();
        $this->register_style_content_controls();
        $this->register_style_connector_controls();
        $this->register_style_box_controls();
    }

    // ─────────────────────────────────────────────
    //  Content Tab — Steps Repeater
    // ─────────────────────────────────────────────

    private function register_content_controls()
    {
        $this->start_controls_section(
            'section_steps',
            array(
                'label' => __('Steps', 'dailybuddy'),
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'step_icon_type',
            array(
                'label'   => __('Icon Type', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'icon',
                'options' => array(
                    'icon'   => __('Icon', 'dailybuddy'),
                    'image'  => __('Image', 'dailybuddy'),
                    'number' => __('Number', 'dailybuddy'),
                ),
            )
        );

        $repeater->add_control(
            'step_icon',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-star',
                    'library' => 'fa-solid',
                ),
                'condition' => array(
                    'step_icon_type' => 'icon',
                ),
            )
        );

        $repeater->add_control(
            'step_image',
            array(
                'label'     => __('Image', 'dailybuddy'),
                'type'      => Controls_Manager::MEDIA,
                'default'   => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
                'condition' => array(
                    'step_icon_type' => 'image',
                ),
            )
        );

        $repeater->add_control(
            'step_number',
            array(
                'label'       => __('Number', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => __('Auto', 'dailybuddy'),
                'description' => __('Leave empty for auto-numbering.', 'dailybuddy'),
                'condition'   => array(
                    'step_icon_type' => 'number',
                ),
                'ai' => array('active' => false),
            )
        );

        $repeater->add_control(
            'step_title',
            array(
                'label'       => __('Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Step Title', 'dailybuddy'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
                'ai'          => array('active' => false),
            )
        );

        $repeater->add_control(
            'step_description',
            array(
                'label'   => __('Description', 'dailybuddy'),
                'type'    => Controls_Manager::TEXTAREA,
                'default' => __('Short description for this step.', 'dailybuddy'),
                'rows'    => 3,
                'dynamic' => array('active' => true),
            )
        );

        $repeater->add_control(
            'step_link',
            array(
                'label'       => __('Link', 'dailybuddy'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'dailybuddy'),
                'default'     => array(
                    'url'         => '',
                    'is_external' => false,
                    'nofollow'    => false,
                ),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'steps',
            array(
                'label'   => __('Steps', 'dailybuddy'),
                'type'    => Controls_Manager::REPEATER,
                'fields'  => $repeater->get_controls(),
                'default' => array(
                    array(
                        'step_icon_type' => 'icon',
                        'step_icon'      => array('value' => 'fas fa-cloud-upload-alt', 'library' => 'fa-solid'),
                        'step_title'     => __('Upload', 'dailybuddy'),
                        'step_description' => __('Upload your files to the cloud storage.', 'dailybuddy'),
                    ),
                    array(
                        'step_icon_type' => 'icon',
                        'step_icon'      => array('value' => 'fas fa-link', 'library' => 'fa-solid'),
                        'step_title'     => __('Connect', 'dailybuddy'),
                        'step_description' => __('Link your resources together.', 'dailybuddy'),
                    ),
                    array(
                        'step_icon_type' => 'icon',
                        'step_icon'      => array('value' => 'fas fa-share-alt', 'library' => 'fa-solid'),
                        'step_title'     => __('Share', 'dailybuddy'),
                        'step_description' => __('Share with your team or clients.', 'dailybuddy'),
                    ),
                    array(
                        'step_icon_type' => 'icon',
                        'step_icon'      => array('value' => 'fas fa-check-circle', 'library' => 'fa-solid'),
                        'step_title'     => __('Done', 'dailybuddy'),
                        'step_description' => __('Process complete and ready to go.', 'dailybuddy'),
                    ),
                ),
                'title_field' => '{{{ step_title }}}',
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Content Tab — Settings
    // ─────────────────────────────────────────────

    private function register_settings_controls()
    {
        $this->start_controls_section(
            'section_settings',
            array(
                'label' => __('Settings', 'dailybuddy'),
            )
        );

        $this->add_control(
            'layout_style',
            array(
                'label'   => __('Layout', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'snake',
                'options' => array(
                    'snake'   => __('Snake Flow', 'dailybuddy'),
                    'compact' => __('Compact Inline', 'dailybuddy'),
                    'cards'   => __('Card Stack', 'dailybuddy'),
                ),
            )
        );

        $this->add_responsive_control(
            'columns',
            array(
                'label'       => __('Max Columns', 'dailybuddy'),
                'description' => __('Columns reduce automatically based on available width.', 'dailybuddy'),
                'type'        => Controls_Manager::SELECT,
                'default'     => '4',
                'options'     => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-process-steps' => '--ps-max-columns: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'connector_style',
            array(
                'label'   => __('Connector Style', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'arrow',
                'options' => array(
                    'arrow'      => __('Arrow', 'dailybuddy'),
                    'line-arrow' => __('Line + Arrow', 'dailybuddy'),
                    'dashed'     => __('Dashed Line', 'dailybuddy'),
                    'dotted'     => __('Dotted Line', 'dailybuddy'),
                    'none'       => __('None', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'connector_icon',
            array(
                'label'   => __('Connector Icon', 'dailybuddy'),
                'type'    => Controls_Manager::ICONS,
                'default' => array(
                    'value'   => 'fas fa-arrow-right',
                    'library' => 'fa-solid',
                ),
                'condition' => array(
                    'connector_style!' => 'none',
                ),
            )
        );

        $this->add_control(
            'loop_mode',
            array(
                'label'        => __('Loop Mode', 'dailybuddy'),
                'description'  => __('Show a return connector from the last step back to the first.', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => '',
                'return_value' => 'yes',
                'condition'    => array(
                    'layout_style' => 'snake',
                ),
            )
        );

        $this->add_control(
            'loop_icon',
            array(
                'label'   => __('Loop Icon', 'dailybuddy'),
                'type'    => Controls_Manager::ICONS,
                'default' => array(
                    'value'   => 'fas fa-redo',
                    'library' => 'fa-solid',
                ),
                'condition' => array(
                    'layout_style' => 'snake',
                    'loop_mode'    => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_numbers',
            array(
                'label'        => __('Show Step Numbers', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => '',
                'return_value' => 'yes',
            )
        );

        $this->add_control(
            'link_click_area',
            array(
                'label'   => __('Link Click Area', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'box',
                'options' => array(
                    'box'   => __('Entire Step', 'dailybuddy'),
                    'icon'  => __('Icon Only', 'dailybuddy'),
                    'title' => __('Title Only', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'title_tag',
            array(
                'label'   => __('Title HTML Tag', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'h3',
                'options' => array(
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'h4'   => 'H4',
                    'h5'   => 'H5',
                    'h6'   => 'H6',
                    'div'  => 'div',
                    'span' => 'span',
                    'p'    => 'p',
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — Icon / Image
    // ─────────────────────────────────────────────

    private function register_style_icon_controls()
    {
        $this->start_controls_section(
            'section_style_icon',
            array(
                'label' => __('Icon / Image', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'icon_size',
            array(
                'label'      => __('Icon Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 16, 'max' => 120),
                ),
                'default'    => array('unit' => 'px', 'size' => 40),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-step-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-step-icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-step-number-display' => 'font-size: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_box_size',
            array(
                'label'      => __('Box Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 40, 'max' => 200),
                ),
                'default'    => array('unit' => 'px', 'size' => 80),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-icon-wrap' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-process-steps'  => '--ps-icon-box-size: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => __('Icon Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#5d3dfd',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-icon i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-step-icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-step-number-display' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_bg_color',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#eee8ff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-icon-wrap' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', '%'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 100),
                    '%'  => array('min' => 0, 'max' => 50),
                ),
                'default'    => array('unit' => '%', 'size' => 50),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-icon-wrap' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'icon_border',
                'selector' => '{{WRAPPER}} .dailybuddy-step-icon-wrap',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'icon_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-step-icon-wrap',
            )
        );

        // Step number badge
        $this->add_control(
            'heading_number_badge',
            array(
                'label'     => __('Number Badge', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => array('show_numbers' => 'yes'),
            )
        );

        $this->add_control(
            'number_color',
            array(
                'label'     => __('Number Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-number' => 'color: {{VALUE}};',
                ),
                'condition' => array('show_numbers' => 'yes'),
            )
        );

        $this->add_control(
            'number_bg_color',
            array(
                'label'     => __('Number Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#5d3dfd',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-number' => 'background-color: {{VALUE}};',
                ),
                'condition' => array('show_numbers' => 'yes'),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — Content
    // ─────────────────────────────────────────────

    private function register_style_content_controls()
    {
        $this->start_controls_section(
            'section_style_content',
            array(
                'label' => __('Content', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'content_spacing',
            array(
                'label'      => __('Spacing (Icon to Content)', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 60),
                ),
                'default'    => array('unit' => 'px', 'size' => 16),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-content' => 'margin-top: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'content_alignment',
            array(
                'label'   => __('Alignment', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'left'   => array('title' => __('Left', 'dailybuddy'), 'icon' => 'eicon-text-align-left'),
                    'center' => array('title' => __('Center', 'dailybuddy'), 'icon' => 'eicon-text-align-center'),
                    'right'  => array('title' => __('Right', 'dailybuddy'), 'icon' => 'eicon-text-align-right'),
                ),
                'default'   => 'center',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-process-step' => 'text-align: {{VALUE}};',
                ),
            )
        );

        // Title
        $this->add_control(
            'heading_title_style',
            array(
                'label'     => __('Title', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-step-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'title_spacing',
            array(
                'label'      => __('Bottom Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 40),
                ),
                'default'    => array('unit' => 'px', 'size' => 8),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        // Description
        $this->add_control(
            'heading_desc_style',
            array(
                'label'     => __('Description', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-step-description',
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-description' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — Connector
    // ─────────────────────────────────────────────

    private function register_style_connector_controls()
    {
        $this->start_controls_section(
            'section_style_connector',
            array(
                'label'     => __('Connector', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('connector_style!' => 'none'),
            )
        );

        $this->add_control(
            'connector_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#5d3dfd',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-connector i'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-step-connector svg'   => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-connector-line'       => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-loop-connector i'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-loop-connector svg'   => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-loop-connector-line'  => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-ps-row-connector i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-ps-row-connector svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-connector-line-vertical' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'connector_icon_size',
            array(
                'label'      => __('Icon Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 8, 'max' => 40),
                ),
                'default'    => array('unit' => 'px', 'size' => 16),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-connector i'     => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-step-connector svg'   => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-loop-connector i'     => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-loop-connector svg'   => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-ps-row-connector i'   => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-ps-row-connector svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'connector_line_width',
            array(
                'label'      => __('Line Thickness', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 1, 'max' => 6),
                ),
                'default'    => array('unit' => 'px', 'size' => 2),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-connector-line'         => 'border-top-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-loop-connector-line'    => 'border-top-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-connector-line-vertical' => 'border-left-width: {{SIZE}}{{UNIT}};',
                ),
                'condition' => array(
                    'connector_style!' => array('arrow', 'none'),
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — Step Box
    // ─────────────────────────────────────────────

    private function register_style_box_controls()
    {
        $this->start_controls_section(
            'section_style_box',
            array(
                'label' => __('Step Box', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'box_bg_color',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-step-inner' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'box_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'box_border',
                'selector' => '{{WRAPPER}} .dailybuddy-step-inner',
            )
        );

        $this->add_responsive_control(
            'box_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-step-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-step-inner',
            )
        );

        $this->add_responsive_control(
            'step_gap',
            array(
                'label'      => __('Gap Between Steps', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 80),
                ),
                'default'    => array('unit' => 'px', 'size' => 20),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-process-steps' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Render
    // ─────────────────────────────────────────────

    protected function render()
    {
        $settings        = $this->get_settings_for_display();
        $steps           = $settings['steps'];
        $connector_style = $settings['connector_style'];
        $loop_mode       = $settings['loop_mode'] === 'yes';
        $show_numbers    = $settings['show_numbers'] === 'yes';
        $title_tag       = esc_attr($settings['title_tag']);
        $total_steps     = count($steps);

        if (empty($steps)) {
            return;
        }

        $layout_style    = !empty($settings['layout_style']) ? $settings['layout_style'] : 'snake';
        $link_click_area = !empty($settings['link_click_area']) ? $settings['link_click_area'] : 'box';
        $connector_class = 'dailybuddy-connector-style-' . esc_attr($connector_style);
        $layout_class    = 'dailybuddy-ps-layout-' . esc_attr($layout_style);
        $step_counter    = 0;
        ?>
        <div class="dailybuddy-process-steps <?php echo esc_attr($connector_class); ?> <?php echo esc_attr($layout_class); ?>"
             data-layout="<?php echo esc_attr($layout_style); ?>"
             data-loop="<?php echo $loop_mode ? 'yes' : 'no'; ?>">

            <?php foreach ($steps as $step) :
                $step_counter++;
                $icon_type = $step['step_icon_type'];

                // Build link tag if URL is set
                $has_link = !empty($step['step_link']['url']);
                $link_open = '';
                $link_close = '';
                if ($has_link) {
                    $rel_parts = array();
                    if (!empty($step['step_link']['is_external'])) {
                        $rel_parts[] = 'noreferrer';
                    }
                    if (!empty($step['step_link']['nofollow'])) {
                        $rel_parts[] = 'nofollow';
                    }
                    $link_open = '<a href="' . esc_url($step['step_link']['url']) . '"'
                        . (!empty($step['step_link']['is_external']) ? ' target="_blank"' : '')
                        . (!empty($rel_parts) ? ' rel="' . esc_attr(implode(' ', $rel_parts)) . '"' : '')
                        . '>';
                    $link_close = '</a>';
                }
                ?>
                <div class="dailybuddy-process-step <?php echo $has_link ? 'dailybuddy-step-has-link' : ''; ?>" data-step="<?php echo esc_attr($step_counter); ?>">
                    <div class="dailybuddy-step-inner">

                        <?php
                        // Icon link open
                        if ($has_link && $link_click_area === 'icon') {
                            echo wp_kses_post(str_replace('<a ', '<a class="dailybuddy-step-icon-link" ', $link_open));
                        }
                        ?>
                        <div class="dailybuddy-step-icon-wrap">
                            <?php if ($show_numbers) : ?>
                                <span class="dailybuddy-step-number"><?php echo esc_html($step_counter); ?></span>
                            <?php endif; ?>

                            <div class="dailybuddy-step-icon">
                                <?php
                                if ($icon_type === 'icon' && !empty($step['step_icon']['value'])) {
                                    Icons_Manager::render_icon($step['step_icon'], array('aria-hidden' => 'true'));
                                } elseif ($icon_type === 'image' && !empty($step['step_image']['url'])) {
                                    $alt = !empty($step['step_title']) ? $step['step_title'] : 'Step ' . $step_counter;
                                    echo '<img src="' . esc_url($step['step_image']['url']) . '" alt="' . esc_attr($alt) . '">';
                                } elseif ($icon_type === 'number') {
                                    $display_number = !empty($step['step_number']) ? $step['step_number'] : $step_counter;
                                    echo '<span class="dailybuddy-step-number-display">' . esc_html($display_number) . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        // Icon link close
                        if ($has_link && $link_click_area === 'icon') {
                            echo wp_kses_post($link_close);
                        }
                        ?>

                        <?php if (!empty($step['step_title']) || !empty($step['step_description'])) : ?>
                            <div class="dailybuddy-step-content">
                                <?php if (!empty($step['step_title'])) : ?>
                                    <<?php echo esc_html($title_tag); ?> class="dailybuddy-step-title">
                                        <?php
                                        if ($has_link && $link_click_area === 'title') {
                                            echo wp_kses_post(str_replace('<a ', '<a class="dailybuddy-step-title-link" ', $link_open));
                                        }
                                        echo esc_html($step['step_title']);
                                        if ($has_link && $link_click_area === 'title') {
                                            echo wp_kses_post($link_close);
                                        }
                                        ?>
                                    </<?php echo esc_html($title_tag); ?>>
                                <?php endif; ?>

                                <?php if (!empty($step['step_description'])) : ?>
                                    <p class="dailybuddy-step-description">
                                        <?php echo wp_kses_post($step['step_description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Box (entire step) — overlay link
                        if ($has_link && $link_click_area === 'box') {
                            echo wp_kses_post(str_replace('<a ', '<a class="dailybuddy-step-overlay-link" aria-label="' . esc_attr($step['step_title']) . '" ', $link_open));
                            echo wp_kses_post($link_close);
                        }
                        ?>

                    </div>

                    <?php
                    // Connector on every step — JS controls visibility per layout mode
                    if ($connector_style !== 'none') :
                        $this->render_connector($settings);
                    endif;
                    ?>
                </div>
            <?php endforeach; ?>

            <?php
            // Row connector template (hidden, cloned by JS for between-row connectors)
            if ($connector_style !== 'none') :
                $this->render_row_connector_template($settings);
            endif;
            ?>

            <?php
            // Loop connector (from last step back to first)
            if ($loop_mode && $connector_style !== 'none') :
                $this->render_loop_connector($settings);
            endif;
            ?>
        </div>
        <?php
    }

    /**
     * Render a horizontal connector between steps
     */
    private function render_connector($settings)
    {
        $style = $settings['connector_style'];
        ?>
        <div class="dailybuddy-step-connector">
            <?php if ($style !== 'arrow') : ?>
                <span class="dailybuddy-connector-line"></span>
            <?php endif; ?>
            <?php
            if (!empty($settings['connector_icon']['value'])) {
                Icons_Manager::render_icon($settings['connector_icon'], array('aria-hidden' => 'true'));
            }
            ?>
            <?php if ($style !== 'arrow') : ?>
                <span class="dailybuddy-connector-line"></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render a hidden template for vertical row-transition connectors.
     * JS clones this for each row transition in snake layout.
     */
    private function render_row_connector_template($settings)
    {
        $style = $settings['connector_style'];
        ?>
        <template class="dailybuddy-row-connector-template">
            <div class="dailybuddy-ps-row-connector">
                <div class="dailybuddy-row-connector-inner">
                    <?php if ($style !== 'arrow') : ?>
                        <span class="dailybuddy-connector-line-vertical"></span>
                    <?php endif; ?>
                    <?php
                    if (!empty($settings['connector_icon']['value'])) {
                        Icons_Manager::render_icon($settings['connector_icon'], array('aria-hidden' => 'true'));
                    }
                    ?>
                    <?php if ($style !== 'arrow') : ?>
                        <span class="dailybuddy-connector-line-vertical"></span>
                    <?php endif; ?>
                </div>
            </div>
        </template>
        <?php
    }

    /**
     * Render the loop connector (last → first)
     */
    private function render_loop_connector($settings)
    {
        ?>
        <div class="dailybuddy-loop-connector">
            <span class="dailybuddy-loop-connector-line"></span>
            <?php
            if (!empty($settings['loop_icon']['value'])) {
                Icons_Manager::render_icon($settings['loop_icon'], array('aria-hidden' => 'true'));
            }
            ?>
            <span class="dailybuddy-loop-connector-line"></span>
        </div>
        <?php
    }
}
