<?php

/**
 * Dailybuddy Mega Menu Widget - COMPLETE VERSION
 * Based on Elementor Pro Mega Menu with all Editor Templates
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Modules\NestedElements\Base\Widget_Nested_Base;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;

class Dailybuddy_Mega_Menu_Widget extends Widget_Nested_Base
{
    public function get_name()
    {
        return 'dailybuddy-mega-menu';
    }

    public function get_title()
    {
        return __('Mega Menu', 'dailybuddy');
    }

    public function get_icon()
    {
        return 'eicon-nav-menu  mini-icon-dailybuddy';
    }

    public function get_categories()
    {
        return array('dailybuddy');
    }

    public function get_keywords()
    {
        return array('menu', 'navigation', 'mega menu', 'nav', 'navbar');
    }

    public function get_style_depends(): array
    {
        return array('dailybuddy-mega-menu-style');
    }

    public function get_script_depends(): array
    {
        return array('dailybuddy-mega-menu-script');
    }

    // ========== NESTED ELEMENTS CONFIGURATION ==========

    protected function get_default_children_elements()
    {
        return array(
            array(
                'elType' => 'container',
                'settings' => array(
                    '_title' => __('Item #1', 'dailybuddy'),
                ),
            ),
            array(
                'elType' => 'container',
                'settings' => array(
                    '_title' => __('Item #2', 'dailybuddy'),
                ),
            ),
            array(
                'elType' => 'container',
                'settings' => array(
                    '_title' => __('Item #3', 'dailybuddy'),
                ),
            ),
        );
    }

    protected function get_default_repeater_title_setting_key()
    {
        return 'item_title';
    }

    protected function get_default_children_title()
    {
        // translators: %d is the item number.
        return esc_html__('Item #%d', 'dailybuddy');
    }

    protected function get_default_children_placeholder_selector()
    {
        return '.db-mega-menu-heading';
    }

    protected function get_default_children_container_placeholder_selector()
    {
        return '.db-mega-menu-content';
    }

    protected function get_html_wrapper_class()
    {
        return 'elementor-widget-dailybuddy-mega-menu';
    }

    /**
     * CRITICAL: Initial config for Nested Elements
     */
    protected function get_initial_config(): array
    {
        return array_merge(parent::get_initial_config(), array(
            'support_improved_repeaters' => true,
            'target_container' => array('.db-mega-menu-heading'),
            'node' => 'li',
            'is_interlaced' => true,
        ));
    }

    // ========== CONTROLS ==========

    protected function register_controls()
    {
        // Layout Section
        $this->start_controls_section(
            'section_layout',
            array(
                'label' => __('Layout', 'dailybuddy'),
            )
        );

        $this->add_control(
            'open_on',
            array(
                'label'   => __('Open Dropdown On', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'click',
                'options' => array(
                    'hover' => __('Hover', 'dailybuddy'),
                    'click' => __('Click', 'dailybuddy'),
                ),
                'frontend_available' => true,
            )
        );

        $this->add_responsive_control(
            'item_position_horizontal',
            array(
                'label'   => __('Item Position', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'flex-start',
                'options' => array(
                    'flex-start' => array(
                        'title' => __('Start', 'dailybuddy'),
                        'icon'  => 'eicon-align-start-h',
                    ),
                    'center' => array(
                        'title' => __('Center', 'dailybuddy'),
                        'icon'  => 'eicon-align-center-h',
                    ),
                    'flex-end' => array(
                        'title' => __('End', 'dailybuddy'),
                        'icon'  => 'eicon-align-end-h',
                    ),
                    'space-between' => array(
                        'title' => __('Space Between', 'dailybuddy'),
                        'icon'  => 'eicon-align-stretch-h',
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu .db-mega-menu-heading' => 'justify-content: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_section();

        // Menu Items Section
        $this->start_controls_section(
            'section_menu_items',
            array(
                'label' => __('Menu Items', 'dailybuddy'),
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'item_title',
            array(
                'label'   => __('Title', 'dailybuddy'),
                'type'    => Controls_Manager::TEXT,
                'default' => __('Menu Item', 'dailybuddy'),
                'dynamic' => array(
                    'active' => true,
                ),
            )
        );

        $repeater->add_control(
            'item_link',
            array(
                'label'   => __('Link', 'dailybuddy'),
                'type'    => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'dailybuddy'),
                'default' => array(
                    'url' => '',
                ),
                'dynamic' => array(
                    'active' => true,
                ),
            )
        );

        $repeater->add_control(
            'item_dropdown_content',
            array(
                'label'        => __('Dropdown Content', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('ON', 'dailybuddy'),
                'label_off'    => __('OFF', 'dailybuddy'),
                'return_value' => 'yes',
                'default'      => '',
                'render_type'  => 'template',
                'frontend_available' => true,
            )
        );

        $repeater->add_control(
            'item_dropdown_content_notice',
            array(
                'type' => Controls_Manager::RAW_HTML,
                'raw'  => '<p class="elementor-control-field-description">' . __('Click on this menu item in the editor to edit the Mega Menu content.', 'dailybuddy') . '</p>',
                'condition' => array(
                    'item_dropdown_content' => 'yes',
                ),
            )
        );

        $repeater->add_control(
            'item_icon',
            array(
                'label' => __('Icon', 'dailybuddy'),
                'type'  => Controls_Manager::ICONS,
                'default' => array(
                    'value' => '',
                    'library' => '',
                ),
            )
        );

        $repeater->add_control(
            'element_id',
            array(
                'label' => __('CSS ID', 'dailybuddy'),
                'type'  => Controls_Manager::TEXT,
            )
        );

        $this->add_control(
            'menu_items',
            array(
                'label'       => __('Items', 'dailybuddy'),
                'type'        => Control_Nested_Repeater::CONTROL_TYPE,
                'fields'      => $repeater->get_controls(),
                'default'     => array(
                    array(
                        'item_title' => __('Home', 'dailybuddy'),
                        'item_link'  => array('url' => '#'),
                    ),
                    array(
                        'item_title' => __('About', 'dailybuddy'),
                        'item_link'  => array('url' => '#'),
                    ),
                    array(
                        'item_title' => __('Services', 'dailybuddy'),
                        'item_link'  => array('url' => '#'),
                        'item_dropdown_content' => 'yes',
                    ),
                ),
                'title_field' => '{{{ item_title }}}',
                'button_text' => __('Add Item', 'dailybuddy'),
            )
        );

        $this->end_controls_section();

        // Action Button Section
        $this->start_controls_section(
            'section_action_button',
            array(
                'label' => __('Action Button', 'dailybuddy'),
            )
        );

        $this->add_control(
            'show_action_button',
            array(
                'label'        => __('Show Action Button', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'dailybuddy'),
                'label_off'    => __('No', 'dailybuddy'),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->add_control(
            'action_button_title',
            array(
                'label'       => __('Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Register Now', 'dailybuddy'),
                'placeholder' => __('Button Text', 'dailybuddy'),
                'condition'   => array(
                    'show_action_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'action_button_link',
            array(
                'label'       => __('Link', 'dailybuddy'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'dailybuddy'),
                'default'     => array(
                    'url' => '#',
                ),
                'condition'   => array(
                    'show_action_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'action_button_icon',
            array(
                'label'   => __('Icon', 'dailybuddy'),
                'type'    => Controls_Manager::ICONS,
                'default' => array(
                    'value'   => '',
                    'library' => '',
                ),
                'condition' => array(
                    'show_action_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'action_button_icon_position',
            array(
                'label'   => __('Icon Position', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'before',
                'options' => array(
                    'before' => array(
                        'title' => __('Before', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-left',
                    ),
                    'after' => array(
                        'title' => __('After', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-right',
                    ),
                ),
                'condition' => array(
                    'show_action_button' => 'yes',
                    'action_button_icon[value]!' => '',
                ),
            )
        );

        $this->add_control(
            'heading_action_button_position',
            array(
                'label'     => __('Position', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => array(
                    'show_action_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'action_button_position_desktop',
            array(
                'label'   => __('Desktop Position', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'right',
                'options' => array(
                    'left' => array(
                        'title' => __('Left', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-left',
                    ),
                    'right' => array(
                        'title' => __('Right', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-right',
                    ),
                ),
                'condition' => array(
                    'show_action_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'action_button_position_mobile',
            array(
                'label'   => __('Mobile Position', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'bottom',
                'options' => array(
                    'top' => array(
                        'title' => __('Top', 'dailybuddy'),
                        'icon'  => 'eicon-v-align-top',
                    ),
                    'bottom' => array(
                        'title' => __('Bottom', 'dailybuddy'),
                        'icon'  => 'eicon-v-align-bottom',
                    ),
                ),
                'condition' => array(
                    'show_action_button' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        // Mobile Menu Section
        $this->start_controls_section(
            'section_menu_toggle',
            array(
                'label' => __('Mobile Menu', 'dailybuddy'),
            )
        );

        $this->add_control(
            'mobile_menu_only',
            array(
                'label'        => __('Mobile Menu Only', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'dailybuddy'),
                'label_off'    => __('No', 'dailybuddy'),
                'return_value' => 'yes',
                'default'      => '',
                'description'  => __('Show only the mobile/hamburger menu on all screen sizes.', 'dailybuddy'),
                'frontend_available' => true,
            )
        );

        $this->add_control(
            'breakpoint',
            array(
                'label'   => __('Breakpoint', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'tablet',
                'options' => array(
                    'none'   => __('None', 'dailybuddy'),
                    'mobile' => __('Mobile (< 768px)', 'dailybuddy'),
                    'tablet' => __('Tablet (< 1025px)', 'dailybuddy'),
                ),
                'description' => __('Switch to mobile menu at this breakpoint.', 'dailybuddy'),
                'frontend_available' => true,
                'condition' => array(
                    'mobile_menu_only' => '',
                ),
            )
        );

        $this->add_control(
            'mobile_menu_layout',
            array(
                'label'   => __('Layout', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'slide-left',
                'options' => array(
                    'slide-left'  => __('Slide Left', 'dailybuddy'),
                    'slide-right' => __('Slide Right', 'dailybuddy'),
                    'slide-down'  => __('Slide Down', 'dailybuddy'),
                    'full-screen' => __('Full Screen', 'dailybuddy'),
                ),
                'frontend_available' => true,
                'render_type' => 'template',
            )
        );

        $this->add_control(
            'heading_toggle_icons',
            array(
                'label'     => __('Toggle Icons', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_responsive_control(
            'toggle_position',
            array(
                'label'   => __('Toggle Position', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'flex-start' => array(
                        'title' => __('Start', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-left',
                    ),
                    'center' => array(
                        'title' => __('Center', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-center',
                    ),
                    'flex-end' => array(
                        'title' => __('End', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-right',
                    ),
                ),
                'default' => 'flex-start',
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu' => 'justify-content: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'menu_toggle_icon_normal',
            array(
                'label' => __('Normal Icon', 'dailybuddy'),
                'type'  => Controls_Manager::ICONS,
                'default' => array(
                    'value'   => 'eicon-menu-bar',
                    'library' => 'eicons',
                ),
                'render_type' => 'template',
            )
        );

        $this->add_control(
            'menu_toggle_icon_active',
            array(
                'label' => __('Active Icon', 'dailybuddy'),
                'type'  => Controls_Manager::ICONS,
                'default' => array(
                    'value'   => 'eicon-close',
                    'library' => 'eicons',
                ),
                'render_type' => 'template',
            )
        );

        $this->add_control(
            'heading_toggle_title',
            array(
                'label'     => __('Toggle Title', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'toggle_title_text',
            array(
                'label'       => __('Title Text', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => __('Menu', 'dailybuddy'),
                'render_type' => 'template',
            )
        );

        $this->add_control(
            'toggle_title_position',
            array(
                'label'   => __('Title Position', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'before' => array(
                        'title' => __('Before Icon', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-left',
                    ),
                    'after' => array(
                        'title' => __('After Icon', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-right',
                    ),
                ),
                'default' => 'after',
                'render_type' => 'template',
                'condition' => array(
                    'toggle_title_text!' => '',
                ),
            )
        );

        $this->end_controls_section();


        // ========== STYLE SECTIONS ==========
        
        // Style: Menu Items
        $this->start_controls_section(
            'section_style_menu_items',
            array(
                'label' => __('Menu Items', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'items_space_between',
            array(
                'label'      => __('Space Between Items', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 100),
                    'em' => array('min' => 0, 'max' => 10),
                ),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-title-space-between: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'items_distance_from_content',
            array(
                'label'      => __('Distance from Dropdown', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 100),
                ),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-title-distance-from-content: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'items_typography',
                'selector' => '{{WRAPPER}} .db-mega-menu-title-text',
            )
        );

        $this->start_controls_tabs('items_style_tabs');

        // Normal Tab
        $this->start_controls_tab(
            'items_style_normal',
            array('label' => __('Normal', 'dailybuddy'))
        );

        $this->add_control(
            'items_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'global'    => array('default' => ''),
                'selectors' => array(
                    '{{WRAPPER}}' => '--n-menu-title-color-normal: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'items_background',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu-title-container',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'items_border',
                'selector' => '{{WRAPPER}} .db-mega-menu-title-container',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'items_box_shadow',
                'selector' => '{{WRAPPER}} .db-mega-menu-title-container',
            )
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'items_style_hover',
            array('label' => __('Hover', 'dailybuddy'))
        );

        $this->add_control(
            'items_color_hover',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--n-menu-title-color-hover: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'items_background_hover',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu-title-container:hover',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'items_border_hover',
                'selector' => '{{WRAPPER}} .db-mega-menu-title-container:hover',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'items_box_shadow_hover',
                'selector' => '{{WRAPPER}} .db-mega-menu-title-container:hover',
            )
        );

        $this->end_controls_tab();

        // Active Tab
        $this->start_controls_tab(
            'items_style_active',
            array('label' => __('Active', 'dailybuddy'))
        );

        $this->add_control(
            'items_color_active',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--n-menu-title-color-active: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-title-text' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'items_background_active',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-title-container',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'items_border_active',
                'selector' => '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-title-container',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'items_box_shadow_active',
                'selector' => '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-title-container',
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'items_divider',
            array(
                'label'        => __('Divider', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'separator'    => 'before',
                'selectors'    => array(
                    '{{WRAPPER}} .db-mega-menu-item:not(:last-child)::after' => 'content: ""; position: absolute; right: calc(var(--n-menu-title-space-between) / -2); top: 50%; transform: translateY(-50%); width: 1px; height: 50%; background-color: {{items_divider_color.VALUE}};',
                ),
            )
        );

        $this->add_control(
            'items_divider_color',
            array(
                'label'     => __('Divider Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ddd',
                'condition' => array('items_divider' => 'yes'),
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-item:not(:last-child)::after' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'items_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'em'),
                'separator'  => 'before',
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-title-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'items_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-title-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style: Icon
        $this->start_controls_section(
            'section_style_icon',
            array(
                'label' => __('Icon', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'icon_position',
            array(
                'label'   => __('Position', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'row',
                'options' => array(
                    'column-reverse' => array(
                        'title' => __('Top', 'dailybuddy'),
                        'icon'  => 'eicon-v-align-top',
                    ),
                    'row' => array(
                        'title' => __('Left', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-left',
                    ),
                    'column' => array(
                        'title' => __('Bottom', 'dailybuddy'),
                        'icon'  => 'eicon-v-align-bottom',
                    ),
                    'row-reverse' => array(
                        'title' => __('Right', 'dailybuddy'),
                        'icon'  => 'eicon-h-align-right',
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-title-container' => 'flex-direction: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_size',
            array(
                'label'      => __('Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 100),
                ),
                'default'    => array('size' => 16, 'unit' => 'px'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .db-mega-menu-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_spacing',
            array(
                'label'      => __('Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 50),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-title-container' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs('icon_style_tabs');

        $this->start_controls_tab(
            'icon_style_normal',
            array('label' => __('Normal', 'dailybuddy'))
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_style_hover',
            array('label' => __('Hover', 'dailybuddy'))
        );

        $this->add_control(
            'icon_color_hover',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-item:hover .db-mega-menu-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-item:hover .db-mega-menu-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_style_active',
            array('label' => __('Active', 'dailybuddy'))
        );

        $this->add_control(
            'icon_color_active',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Style: Dropdown Indicator
        $this->start_controls_section(
            'section_style_dropdown_indicator',
            array(
                'label' => __('Dropdown Indicator', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'dropdown_indicator_size',
            array(
                'label'      => __('Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 50),
                ),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-dropdown-indicator-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .db-mega-menu-dropdown-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .db-mega-menu-dropdown-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'dropdown_indicator_rotate',
            array(
                'label'      => __('Rotate', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('deg'),
                'range'      => array(
                    'deg' => array('min' => 0, 'max' => 360),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-dropdown-icon' => 'transform: rotate({{SIZE}}deg);',
                ),
            )
        );

        $this->add_responsive_control(
            'dropdown_indicator_spacing',
            array(
                'label'      => __('Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 50),
                ),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-dropdown-indicator-space: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs('dropdown_indicator_tabs');

        $this->start_controls_tab(
            'dropdown_indicator_normal',
            array('label' => __('Normal', 'dailybuddy'))
        );

        $this->add_control(
            'dropdown_indicator_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-dropdown-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-dropdown-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'dropdown_indicator_hover',
            array('label' => __('Hover', 'dailybuddy'))
        );

        $this->add_control(
            'dropdown_indicator_color_hover',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-item:hover .db-mega-menu-dropdown-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-item:hover .db-mega-menu-dropdown-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'dropdown_indicator_active',
            array('label' => __('Active', 'dailybuddy'))
        );

        $this->add_control(
            'dropdown_indicator_color_active',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-dropdown-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-item.e-active .db-mega-menu-dropdown-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Style: Menu Toggle (Hamburger)
        $this->start_controls_section(
            'section_style_toggle',
            array(
                'label' => __('Menu Toggle', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'toggle_icon_heading',
            array(
                'label' => __('Toggle Icon', 'dailybuddy'),
                'type'  => Controls_Manager::HEADING,
            )
        );

        $this->add_responsive_control(
            'toggle_icon_size',
            array(
                'label'      => __('Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 10, 'max' => 100),
                ),
                'default'    => array('size' => 20, 'unit' => 'px'),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-toggle-icon-size: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'toggle_title_heading',
            array(
                'label'     => __('Toggle Title', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_responsive_control(
            'toggle_title_spacing',
            array(
                'label'      => __('Spacing (Icon to Title)', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 50),
                ),
                'default'    => array('size' => 8, 'unit' => 'px'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-toggle' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'toggle_title_typography',
                'selector' => '{{WRAPPER}} .db-mega-menu-toggle-title',
            )
        );

        $this->add_control(
            'toggle_title_color',
            array(
                'label'     => __('Title Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-toggle-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'toggle_styles_heading',
            array(
                'label'     => __('Toggle Button', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->start_controls_tabs('toggle_style_tabs');

        $this->start_controls_tab(
            'toggle_style_normal',
            array('label' => __('Normal', 'dailybuddy'))
        );

        $this->add_control(
            'toggle_icon_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--n-menu-toggle-icon-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'toggle_background',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu-toggle',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'toggle_border',
                'selector' => '{{WRAPPER}} .db-mega-menu-toggle',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'toggle_box_shadow',
                'selector' => '{{WRAPPER}} .db-mega-menu-toggle',
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'toggle_style_hover',
            array('label' => __('Hover', 'dailybuddy'))
        );

        $this->add_control(
            'toggle_icon_color_hover',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-toggle:hover' => '--n-menu-toggle-icon-color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-toggle:hover i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-toggle:hover svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'toggle_background_hover',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu-toggle:hover',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'toggle_border_hover',
                'selector' => '{{WRAPPER}} .db-mega-menu-toggle:hover',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'toggle_box_shadow_hover',
                'selector' => '{{WRAPPER}} .db-mega-menu-toggle:hover',
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'toggle_style_active',
            array('label' => __('Active', 'dailybuddy'))
        );

        $this->add_control(
            'toggle_icon_color_active',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-toggle i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-toggle svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'toggle_background_active',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-toggle',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'toggle_border_active',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-toggle',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'toggle_box_shadow_active',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-toggle',
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'toggle_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'em'),
                'separator'  => 'before',
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'toggle_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style: Dropdown Content
        $this->start_controls_section(
            'section_style_dropdown_content',
            array(
                'label' => __('Dropdown Content', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'dropdown_content_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .db-mega-menu-content > .e-con',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'dropdown_content_border',
                'selector' => '{{WRAPPER}} .db-mega-menu-content > .e-con',
            )
        );

        $this->add_responsive_control(
            'dropdown_content_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'em'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-content > .e-con' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'dropdown_content_box_shadow',
                'selector' => '{{WRAPPER}} .db-mega-menu-content > .e-con',
            )
        );

        $this->add_responsive_control(
            'dropdown_content_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-content > .e-con' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style: Action Button
        $this->start_controls_section(
            'section_style_action_button',
            array(
                'label'     => __('Action Button', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_action_button' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'action_button_typography',
                'selector' => '{{WRAPPER}} .db-action-button-link',
            )
        );

        $this->add_responsive_control(
            'action_button_icon_size',
            array(
                'label'      => __('Icon Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 50),
                ),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-action-button-icon-size: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs('action_button_tabs');

        $this->start_controls_tab(
            'action_button_normal',
            array('label' => __('Normal', 'dailybuddy'))
        );

        $this->add_control(
            'action_button_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--n-menu-action-button-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'action_button_background',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-action-button-link',
                'fields_options' => array(
                    'background' => array(
                        'default' => 'classic',
                    ),
                    'color' => array(
                        'selectors' => array(
                            '{{WRAPPER}}' => '--n-menu-action-button-bg: {{VALUE}};',
                            '{{SELECTOR}}' => 'background-color: {{VALUE}};',
                        ),
                    ),
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'action_button_border',
                'selector' => '{{WRAPPER}} .db-action-button-link',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'action_button_box_shadow',
                'selector' => '{{WRAPPER}} .db-action-button-link',
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'action_button_hover',
            array('label' => __('Hover', 'dailybuddy'))
        );

        $this->add_control(
            'action_button_color_hover',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--n-menu-action-button-color-hover: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'action_button_background_hover',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-action-button-link:hover',
                'fields_options' => array(
                    'color' => array(
                        'selectors' => array(
                            '{{WRAPPER}}' => '--n-menu-action-button-bg-hover: {{VALUE}};',
                            '{{SELECTOR}}' => 'background-color: {{VALUE}};',
                        ),
                    ),
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'action_button_border_hover',
                'selector' => '{{WRAPPER}} .db-action-button-link:hover',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'action_button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .db-action-button-link:hover',
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'action_button_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'em'),
                'separator'  => 'before',
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-action-button-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .db-action-button-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'action_button_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--n-menu-action-button-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .db-action-button-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style: Mobile Menu (Dropdown Layout)
        $this->start_controls_section(
            'section_style_mobile_menu',
            array(
                'label' => __('Mobile Menu', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        // ========== CLOSE BUTTON ==========
        $this->add_control(
            'mobile_close_button_heading',
            array(
                'label' => __('Close Button', 'dailybuddy'),
                'type'  => Controls_Manager::HEADING,
            )
        );

        $this->add_control(
            'mobile_close_button_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-close' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu-close svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'mobile_close_button_size',
            array(
                'label'      => __('Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array('min' => 10, 'max' => 60),
                    'em' => array('min' => 0.5, 'max' => 4),
                ),
                'default'    => array('size' => 24, 'unit' => 'px'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-close' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .db-mega-menu-close svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        // ========== HEADER (Title + Border) ==========
        $this->add_control(
            'mobile_header_heading',
            array(
                'label'     => __('Header', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'mobile_header_border_type',
            array(
                'label'   => __('Border', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => array(
                    'none'   => __('None', 'dailybuddy'),
                    'solid'  => __('Solid', 'dailybuddy'),
                    'dashed' => __('Dashed', 'dailybuddy'),
                    'dotted' => __('Dotted', 'dailybuddy'),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-mobile-header' => 'border-bottom-style: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'mobile_header_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#e0e0e0',
                'condition' => array(
                    'mobile_header_border_type!' => 'none',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu-mobile-header' => 'border-bottom-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'mobile_header_border_width',
            array(
                'label'      => __('Border Width', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 1, 'max' => 10),
                ),
                'default'    => array('size' => 1, 'unit' => 'px'),
                'condition'  => array(
                    'mobile_header_border_type!' => 'none',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-mobile-header' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'mobile_header_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu-mobile-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        // ========== MENU ITEMS ==========
        $this->add_control(
            'mobile_menu_items_heading',
            array(
                'label'     => __('Menu Items', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'mobile_menu_items_description',
            array(
                'type' => Controls_Manager::RAW_HTML,
                'raw'  => '<p class="elementor-control-field-description">' . __('Styles applied to menu items when in mobile/dropdown layout.', 'dailybuddy') . '</p>',
            )
        );

        $this->add_responsive_control(
            'mobile_menu_items_alignment',
            array(
                'label'   => __('Alignment', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'flex-start' => array(
                        'title' => __('Left', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => __('Center', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'flex-end' => array(
                        'title' => __('Right', 'dailybuddy'),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default'   => 'flex-start',
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-title' => 'justify-content: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-title' => 'justify-content: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'mobile_menu_items_padding',
            array(
                'label'      => __('Item Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs('mobile_menu_items_tabs');

        $this->start_controls_tab(
            'mobile_menu_items_normal',
            array('label' => __('Normal', 'dailybuddy'))
        );

        $this->add_control(
            'mobile_menu_items_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-title-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-title-text' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'mobile_menu_items_background',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-title-container, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-title-container',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'mobile_menu_items_box_shadow',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-title-container, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-title-container',
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'mobile_menu_items_active',
            array('label' => __('Active', 'dailybuddy'))
        );

        $this->add_control(
            'mobile_menu_items_color_active',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-item.e-active .db-mega-menu-title-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-item.e-active .db-mega-menu-title-text' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'mobile_menu_items_background_active',
                'types'    => array('classic', 'gradient'),
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor control parameter, not a database query.
                'exclude'  => array('image'),
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-item.e-active .db-mega-menu-title-container, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-item.e-active .db-mega-menu-title-container',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'mobile_menu_items_box_shadow_active',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-item.e-active .db-mega-menu-title-container, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-item.e-active .db-mega-menu-title-container',
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'mobile_menu_box_heading',
            array(
                'label'     => __('Menu Container', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'mobile_menu_box_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-wrapper, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-wrapper',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'mobile_menu_box_border',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-wrapper, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-wrapper',
            )
        );

        $this->add_responsive_control(
            'mobile_menu_box_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'em'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-wrapper, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'mobile_menu_box_shadow',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-wrapper, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-wrapper',
            )
        );

        $this->add_responsive_control(
            'mobile_menu_box_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-wrapper, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'mobile_dropdown_box_heading',
            array(
                'label'     => __('Dropdown Box', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'mobile_dropdown_box_description',
            array(
                'type' => Controls_Manager::RAW_HTML,
                'raw'  => '<p class="elementor-control-field-description">' . __('Style the dropdown container that holds menu content.', 'dailybuddy') . '</p>',
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'mobile_dropdown_box_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-content > .e-con, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-content > .e-con',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'mobile_dropdown_box_border',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-content > .e-con, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-content > .e-con',
            )
        );

        $this->add_responsive_control(
            'mobile_dropdown_box_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'em'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-content > .e-con, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-content > .e-con' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'mobile_dropdown_box_shadow',
                'selector' => '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-content > .e-con, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-content > .e-con',
            )
        );

        $this->add_responsive_control(
            'mobile_dropdown_box_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .db-mega-menu.e-open .db-mega-menu-content > .e-con, {{WRAPPER}} .db-mega-menu.mobile-menu-only .db-mega-menu-content > .e-con' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    public function get_menu_widget_id()
    {
        return substr($this->get_id(), 0, 3);
    }

    // ========== RENDER (FRONTEND) ==========

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $widget_number = $this->get_menu_widget_id();

        // Mobile menu layout
        $mobile_layout = !empty($settings['mobile_menu_layout']) ? $settings['mobile_menu_layout'] : 'slide-left';

        $menu_classes = array('db-mega-menu', 'mobile-layout-' . $mobile_layout);
        if (!empty($settings['mobile_menu_only']) && $settings['mobile_menu_only'] === 'yes') {
            $menu_classes[] = 'mobile-menu-only';
        } else {
            // Add breakpoint class
            $breakpoint = !empty($settings['breakpoint']) ? $settings['breakpoint'] : 'tablet';
            if ($breakpoint !== 'none') {
                $menu_classes[] = 'breakpoint-' . $breakpoint;
            }
        }

        // Action button position classes
        if (!empty($settings['show_action_button']) && $settings['show_action_button'] === 'yes') {
            $menu_classes[] = 'has-action-button';
            $desktop_pos = !empty($settings['action_button_position_desktop']) ? $settings['action_button_position_desktop'] : 'right';
            $mobile_pos = !empty($settings['action_button_position_mobile']) ? $settings['action_button_position_mobile'] : 'bottom';
            $menu_classes[] = 'action-desktop-' . $desktop_pos;
            $menu_classes[] = 'action-mobile-' . $mobile_pos;
        }

        $open_on = !empty($settings['open_on']) ? $settings['open_on'] : 'click';

        $this->add_render_attribute('menu-wrapper', array(
            'class' => implode(' ', $menu_classes),
            'data-widget-number' => $widget_number,
            'data-open-on' => $open_on,
            'aria-label' => __('Menu', 'dailybuddy'),
        ));
?>
        <nav <?php $this->print_render_attribute_string('menu-wrapper'); ?>>
            <?php $this->render_menu_toggle($settings); ?>
            <div class="db-mega-menu-wrapper">
                <?php $this->render_mobile_header($settings); ?>
                <?php $this->render_action_button($settings, 'mobile-top'); ?>
                <ul class="db-mega-menu-heading" id="menubar-<?php echo esc_attr($widget_number); ?>">
                    <?php $this->render_action_button($settings, 'desktop-left'); ?>
                    <?php
                    if (!empty($settings['menu_items'])) {
                        foreach ($settings['menu_items'] as $index => $item) {
                            $this->render_menu_item($index, $item, $settings);
                        }
                    }
                    ?>
                    <?php $this->render_action_button($settings, 'desktop-right'); ?>
                </ul>
                <?php $this->render_action_button($settings, 'mobile-bottom'); ?>
            </div>
        </nav>
    <?php
    }

    protected function render_menu_toggle($settings)
    {
        $widget_number = $this->get_menu_widget_id();
        $toggle_title = !empty($settings['toggle_title_text']) ? $settings['toggle_title_text'] : '';
        $title_position = !empty($settings['toggle_title_position']) ? $settings['toggle_title_position'] : 'after';
    ?>
        <button class="db-mega-menu-toggle" id="menu-toggle-<?php echo esc_attr($widget_number); ?>"
            aria-haspopup="true" aria-expanded="false"
            aria-controls="menubar-<?php echo esc_attr($widget_number); ?>"
            aria-label="<?php esc_attr_e('Menu Toggle', 'dailybuddy'); ?>">
            <?php if ($toggle_title && $title_position === 'before') : ?>
                <span class="db-mega-menu-toggle-title"><?php echo esc_html($toggle_title); ?></span>
            <?php endif; ?>
            <span class="db-mega-menu-toggle-icon e-open">
                <?php
                if (!empty($settings['menu_toggle_icon_normal']['value'])) {
                    Icons_Manager::render_icon($settings['menu_toggle_icon_normal'], array('aria-hidden' => 'true'));
                } else {
                    echo '<i class="eicon-menu-bar" aria-hidden="true"></i>';
                }
                ?>
            </span>
            <span class="db-mega-menu-toggle-icon e-close">
                <?php
                if (!empty($settings['menu_toggle_icon_active']['value'])) {
                    Icons_Manager::render_icon($settings['menu_toggle_icon_active'], array('aria-hidden' => 'true'));
                } else {
                    echo '<i class="eicon-close" aria-hidden="true"></i>';
                }
                ?>
            </span>
            <?php if ($toggle_title && $title_position === 'after') : ?>
                <span class="db-mega-menu-toggle-title"><?php echo esc_html($toggle_title); ?></span>
            <?php endif; ?>
        </button>
    <?php
    }

    protected function render_mobile_header($settings)
    {
        $toggle_title = !empty($settings['toggle_title_text']) ? $settings['toggle_title_text'] : '';
    ?>
        <div class="db-mega-menu-mobile-header">
            <?php if ($toggle_title) : ?>
                <span class="db-mega-menu-mobile-title"><?php echo esc_html($toggle_title); ?></span>
            <?php endif; ?>
            <button class="db-mega-menu-close" aria-label="<?php esc_attr_e('Close Menu', 'dailybuddy'); ?>">
                <?php
                if (!empty($settings['menu_toggle_icon_active']['value'])) {
                    Icons_Manager::render_icon($settings['menu_toggle_icon_active'], array('aria-hidden' => 'true'));
                } else {
                    echo '<i class="eicon-close" aria-hidden="true"></i>';
                }
                ?>
            </button>
        </div>
    <?php
    }

    protected function render_action_button($settings, $context = 'desktop')
    {
        if (empty($settings['show_action_button']) || $settings['show_action_button'] !== 'yes') {
            return;
        }

        $desktop_position = !empty($settings['action_button_position_desktop']) ? $settings['action_button_position_desktop'] : 'right';
        $mobile_position = !empty($settings['action_button_position_mobile']) ? $settings['action_button_position_mobile'] : 'bottom';

        // Only render if context matches position
        if ($context === 'desktop-left' && $desktop_position !== 'left') {
            return;
        }
        if ($context === 'desktop-right' && $desktop_position !== 'right') {
            return;
        }
        if ($context === 'mobile-top' && $mobile_position !== 'top') {
            return;
        }
        if ($context === 'mobile-bottom' && $mobile_position !== 'bottom') {
            return;
        }

        $title = !empty($settings['action_button_title']) ? $settings['action_button_title'] : '';
        $url = isset($settings['action_button_link']['url']) ? $settings['action_button_link']['url'] : '#';
        $is_external = !empty($settings['action_button_link']['is_external']);
        $nofollow = !empty($settings['action_button_link']['nofollow']);
        $icon_position = !empty($settings['action_button_icon_position']) ? $settings['action_button_icon_position'] : 'before';

        // Build rel attribute
        $rel_attrs = array();
        if ($is_external) {
            $rel_attrs[] = 'noopener';
        }
        if ($nofollow) {
            $rel_attrs[] = 'nofollow';
        }
        $rel_string = !empty($rel_attrs) ? implode(' ', $rel_attrs) : '';

        // Get icon HTML (from Elementor Icons_Manager - already escaped)
        $icon_html = '';
        if (!empty($settings['action_button_icon']['value'])) {
            ob_start();
            Icons_Manager::render_icon($settings['action_button_icon'], array('aria-hidden' => 'true'));
            $icon_html = ob_get_clean();
        }

        $classes = array('db-mega-menu-action-button', $context);
        $is_desktop = strpos($context, 'desktop') !== false;
        $wrapper_tag = $is_desktop ? 'li' : 'div';
    ?>
        <<?php echo esc_attr($wrapper_tag); ?> class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <a href="<?php echo esc_url($url); ?>"<?php if ($is_external) : ?> target="_blank"<?php endif; ?><?php if (!empty($rel_string)) : ?> rel="<?php echo esc_attr($rel_string); ?>"<?php endif; ?> class="db-action-button-link">
                <?php if ($icon_html && $icon_position === 'before') : ?>
                    <span class="db-action-button-icon"><?php echo wp_kses_post($icon_html); ?></span>
                <?php endif; ?>
                <?php if ($title) : ?>
                    <span class="db-action-button-text"><?php echo esc_html($title); ?></span>
                <?php endif; ?>
                <?php if ($icon_html && $icon_position === 'after') : ?>
                    <span class="db-action-button-icon"><?php echo wp_kses_post($icon_html); ?></span>
                <?php endif; ?>
            </a>
        </<?php echo esc_attr($wrapper_tag); ?>>
    <?php
    }

    protected function render_menu_item($index, $item, $settings)
    {
        $widget_number = $this->get_menu_widget_id();
        $display_index = $index + 1;
        $has_dropdown_content = 'yes' === $item['item_dropdown_content'];
        $children = $this->get_children();

        // Classes
        $item_classes = array('db-mega-menu-title');
        if ($has_dropdown_content) {
            $item_classes[] = 'e-click';
        } else {
            $item_classes[] = 'link-only';
        }

        // IDs
        $menu_item_id = !empty($item['element_id'])
            ? $item['element_id']
            : 'db-mega-menu-title-' . $widget_number . $display_index;
        $item_dropdown_id = 'db-mega-menu-dropdown-icon-' . $widget_number . $display_index;
        $content_id = 'db-mega-menu-content-' . $widget_number . $display_index;

        // Menu item icon (per item) - use render_icon with echo=false for reliable rendering
        $menu_item_icon = '';
        if (!empty($item['item_icon']['value'])) {
            ob_start();
            Icons_Manager::render_icon($item['item_icon'], array('aria-hidden' => 'true'));
            $menu_item_icon = ob_get_clean();
        }

        // Link attributes
        $url = isset($item['item_link']['url']) ? $item['item_link']['url'] : '';
        $is_external = !empty($item['item_link']['is_external']);
        $nofollow = !empty($item['item_link']['nofollow']);
        $custom_attributes = isset($item['item_link']['custom_attributes']) ? $item['item_link']['custom_attributes'] : '';

        // Build rel attribute
        $rel_attrs = array();
        if ($is_external) {
            $rel_attrs[] = 'noopener';
        }
        if ($nofollow) {
            $rel_attrs[] = 'nofollow';
        }
        $rel_string = !empty($rel_attrs) ? implode(' ', $rel_attrs) : '';

        // Parse custom attributes (format: key|value, key|value)
        $custom_attrs_array = array();
        if (!empty($custom_attributes)) {
            $attrs_pairs = explode(',', $custom_attributes);
            foreach ($attrs_pairs as $pair) {
                $pair = trim($pair);
                if (strpos($pair, '|') !== false) {
                    list($key, $value) = explode('|', $pair, 2);
                    $custom_attrs_array[sanitize_key(trim($key))] = esc_attr(trim($value));
                }
            }
        }
    ?>
        <li class="db-mega-menu-item" data-has-dropdown="<?php echo esc_attr($has_dropdown_content ? 'true' : 'false'); ?>">
            <div id="<?php echo esc_attr($menu_item_id); ?>" class="<?php echo esc_attr(implode(' ', $item_classes)); ?>">
                <?php if (!empty($url)) : ?>
                    <a class="db-mega-menu-title-container e-link e-focus" href="<?php echo esc_url($url); ?>"<?php if ($is_external) : ?> target="_blank"<?php endif; ?><?php if (!empty($rel_string)) : ?> rel="<?php echo esc_attr($rel_string); ?>"<?php endif; ?><?php foreach ($custom_attrs_array as $attr_key => $attr_value) : ?> <?php echo esc_attr($attr_key); ?>="<?php echo esc_attr($attr_value); ?>"<?php endforeach; ?>>
                    <?php else : ?>
                        <div class="db-mega-menu-title-container">
                        <?php endif; ?>

                        <?php if ($menu_item_icon) : ?>
                            <span class="db-mega-menu-icon"><?php echo wp_kses_post($menu_item_icon); ?></span>
                        <?php endif; ?>

                        <span class="db-mega-menu-title-text"><?php echo esc_html($item['item_title']); ?></span>

                        <?php if (!empty($url)) : ?>
                    </a>
                <?php else : ?>
            </div>
        <?php endif; ?>

        <?php if ($has_dropdown_content) : ?>
            <button id="<?php echo esc_attr($item_dropdown_id); ?>"
                class="db-mega-menu-dropdown-icon e-focus"
                data-tab-index="<?php echo esc_attr($display_index); ?>"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="<?php echo esc_attr($content_id); ?>">
                <span class="db-mega-menu-dropdown-icon-opened">
                    <i class="eicon-caret-up" aria-hidden="true"></i>
                    <span class="elementor-screen-only"><?php
                        // translators: %s is the menu item title.
                        echo esc_html(sprintf(__('Close %s', 'dailybuddy'), $item['item_title']));
                    ?></span>
                </span>
                <span class="db-mega-menu-dropdown-icon-closed">
                    <i class="eicon-caret-down" aria-hidden="true"></i>
                    <span class="elementor-screen-only"><?php
                        // translators: %s is the menu item title.
                        echo esc_html(sprintf(__('Open %s', 'dailybuddy'), $item['item_title']));
                    ?></span>
                </span>
            </button>
        <?php endif; ?>
        </div>

        <?php if ($has_dropdown_content) : ?>
            <div class="db-mega-menu-content">
                <?php $this->print_child($index, $has_dropdown_content, $item_dropdown_id); ?>
            </div>
        <?php endif; ?>
        </li>
    <?php
    }

    public function print_child($index, $has_dropdown_content = false, $menu_item_id = '')
    {
        $children = $this->get_children();
        $menu_index = $index + 1;
        $child_ids = array();

        foreach ($children as $child) {
            $child_ids[] = $child->get_id();
        }

        $add_attribute_to_container = function ($should_render, $container) use ($menu_item_id, $menu_index, $child_ids) {
            if (in_array($container->get_id(), $child_ids)) {
                $this->set_container_attributes($container, $menu_index, $menu_item_id);
            }
            return $should_render;
        };

        if ($this->item_has_dropdown_with_content($index, $children, $has_dropdown_content)) {
            add_filter('elementor/frontend/container/should_render', $add_attribute_to_container, 10, 3);
            $children[$index]->print_element();
            remove_filter('elementor/frontend/container/should_render', $add_attribute_to_container);
        }
    }

    protected function set_container_attributes($container, $menu_index, $menu_item_id)
    {
        $widget_number = $this->get_menu_widget_id();

        $container->add_render_attribute('_wrapper', array(
            'id' => 'db-mega-menu-content-' . $widget_number . $menu_index,
            'role' => 'menu',
            'aria-labelledby' => $menu_item_id,
            'data-tab-index' => $menu_index,
        ));
    }

    protected function item_has_dropdown_with_content($index, $children, $has_dropdown_content = false)
    {
        $data = !empty($children[$index]) ? $children[$index]->get_data() : array();
        $elements = empty($data['elements']) ? array() : $data['elements'];

        return !empty($children[$index]) && !empty($elements) && $has_dropdown_content;
    }

    // ========== EDITOR TEMPLATES (CRITICAL!) ==========

    protected function content_template()
    {
    ?>
        <#
            if ( ! settings['menu_items'] ) {
            return;
            }

            const elementUid=view.getIDInt().toString().substr( 0, 3 );

            // Mobile menu layout
            var mobileLayout=settings['mobile_menu_layout'] || 'slide-left' ;

            let menuClasses=['db-mega-menu', 'mobile-layout-' + mobileLayout];
            if (settings['mobile_menu_only']==='yes' ) {
            menuClasses.push('mobile-menu-only');
            } else {
            // Add breakpoint class
            var breakpoint=settings['breakpoint'] || 'tablet' ;
            if (breakpoint !=='none' ) {
            menuClasses.push('breakpoint-' + breakpoint);
            }
            }

            // Action button classes
            var showActionButton = settings['show_action_button'] === 'yes';
            var actionButtonDesktopPos = settings['action_button_position_desktop'] || 'right';
            var actionButtonMobilePos = settings['action_button_position_mobile'] || 'bottom';
            if (showActionButton) {
                menuClasses.push('has-action-button');
                menuClasses.push('action-desktop-' + actionButtonDesktopPos);
                menuClasses.push('action-mobile-' + actionButtonMobilePos);
            }

            view.addRenderAttribute( 'db-mega-menu' ,
            {
                'class': menuClasses.join(' '),
                'data-widget-number': elementUid,
                'data-open-on': settings['open_on'] || 'click'
            }
            );

            // Render toggle icons dynamically
            var toggleIconNormalHtml='<i class="eicon-menu-bar" aria-hidden="true"></i>' ;
            var toggleIconActiveHtml='<i class="eicon-close" aria-hidden="true"></i>' ;

            if (settings['menu_toggle_icon_normal'] && settings['menu_toggle_icon_normal'].value) {
            var iconObj=elementor.helpers.renderIcon(view, settings['menu_toggle_icon_normal'], { 'aria-hidden' : true }, 'i' , 'object' );
            if (iconObj && iconObj.value) {
            toggleIconNormalHtml=iconObj.value;
            }
            }

            if (settings['menu_toggle_icon_active'] && settings['menu_toggle_icon_active'].value) {
            var iconObj=elementor.helpers.renderIcon(view, settings['menu_toggle_icon_active'], { 'aria-hidden' : true }, 'i' , 'object' );
            if (iconObj && iconObj.value) {
            toggleIconActiveHtml=iconObj.value;
            }
            }

            // Toggle title
            var toggleTitle=settings['toggle_title_text'] || '' ;
            var titlePosition=settings['toggle_title_position'] || 'after' ;
            var titleBeforeHtml=(toggleTitle && titlePosition==='before' ) ? '<span class="db-mega-menu-toggle-title">' + toggleTitle + '</span>' : '' ;
            var titleAfterHtml=(toggleTitle && titlePosition==='after' ) ? '<span class="db-mega-menu-toggle-title">' + toggleTitle + '</span>' : '' ;

            // Action Button
            var actionButtonTitle = settings['action_button_title'] || '';
            var actionButtonUrl = settings['action_button_link'] ? (settings['action_button_link'].url || '#') : '#';
            var actionButtonIconPos = settings['action_button_icon_position'] || 'before';
            var actionButtonIconHtml = '';
            if (settings['action_button_icon'] && settings['action_button_icon'].value) {
                try {
                    var iconObj = elementor.helpers.renderIcon(view, settings['action_button_icon'], { 'aria-hidden': true }, 'i', 'object');
                    if (iconObj && iconObj.value) {
                        actionButtonIconHtml = '<span class="db-action-button-icon">' + iconObj.value + '</span>';
                    }
                } catch(e) {}
            }
            
            function getActionButtonHtml(context) {
                if (!showActionButton) return '';
                if (context === 'desktop-left' && actionButtonDesktopPos !== 'left') return '';
                if (context === 'desktop-right' && actionButtonDesktopPos !== 'right') return '';
                if (context === 'mobile-top' && actionButtonMobilePos !== 'top') return '';
                if (context === 'mobile-bottom' && actionButtonMobilePos !== 'bottom') return '';
                
                var tag = context.indexOf('desktop') !== -1 ? 'li' : 'div';
                var iconBefore = (actionButtonIconHtml && actionButtonIconPos === 'before') ? actionButtonIconHtml : '';
                var iconAfter = (actionButtonIconHtml && actionButtonIconPos === 'after') ? actionButtonIconHtml : '';
                var titleHtml = actionButtonTitle ? '<span class="db-action-button-text">' + actionButtonTitle + '</span>' : '';
                
                return '<' + tag + ' class="db-mega-menu-action-button ' + context + '">' +
                    '<a href="' + actionButtonUrl + '" class="db-action-button-link">' +
                    iconBefore + titleHtml + iconAfter +
                    '</a></' + tag + '>';
            }
            #>
            <nav {{{ view.getRenderAttributeString( 'db-mega-menu' ) }}}>
                <button class="db-mega-menu-toggle" id="menu-toggle-{{{ elementUid }}}"
                    aria-haspopup="true" aria-expanded="false"
                    aria-controls="menubar-{{{ elementUid }}}"
                    aria-label="<?php esc_attr_e('Menu Toggle', 'dailybuddy'); ?>">
                    {{{ titleBeforeHtml }}}
                    <span class="db-mega-menu-toggle-icon e-open">
                        {{{ toggleIconNormalHtml }}}
                    </span>
                    <span class="db-mega-menu-toggle-icon e-close">
                        {{{ toggleIconActiveHtml }}}
                    </span>
                    {{{ titleAfterHtml }}}
                </button>
                <div class="db-mega-menu-wrapper">
                    <div class="db-mega-menu-mobile-header">
                        <# if (toggleTitle) { #>
                            <span class="db-mega-menu-mobile-title">{{{ toggleTitle }}}</span>
                        <# } #>
                        <button class="db-mega-menu-close" aria-label="<?php esc_attr_e('Close Menu', 'dailybuddy'); ?>">
                            {{{ toggleIconActiveHtml }}}
                        </button>
                    </div>
                    {{{ getActionButtonHtml('mobile-top') }}}
                    <ul class="db-mega-menu-heading" id="menubar-{{{ elementUid }}}">
                        {{{ getActionButtonHtml('desktop-left') }}}
                        <# _.each( settings['menu_items'], function( item, index ) {
                            if (!item) return;
                            
                            const menuItemCount = index + 1,
                            menuItemUid = elementUid + menuItemCount,
                            menuItemWrapperKey = 'wrapper-' + menuItemUid,
                            menuItemTitleKey = 'menu-title-' + menuItemUid,
                            menuItemTitleContainerLinkKey = 'link-' + menuItemUid,
                            menuItemDropdownIconKey = 'dropdown-' + menuItemUid,
                            hasDropdownContent = item.item_dropdown_content === 'yes',
                            menuItemClassList = ['db-mega-menu-title'];
                            
                            // Safe icon rendering
                            let menuIcon = '';
                            if (item.item_icon && item.item_icon.value) {
                                try {
                                    const iconResult = elementor.helpers.renderIcon(view, item.item_icon, { 'aria-hidden': true }, 'i', 'object');
                                    if (iconResult && iconResult.value) {
                                        menuIcon = iconResult;
                                    }
                                } catch(e) {}
                            }
                            
                            // Safe link handling
                            let menuItemLink = '';
                            if (item.item_link) {
                                if (typeof item.item_link === 'string') {
                                    menuItemLink = item.item_link;
                                } else if (item.item_link.url) {
                                    menuItemLink = item.item_link.url;
                                }
                            }
                            
                            let menuItemId = 'db-mega-menu-title-' + menuItemUid;
                            if (item.element_id && item.element_id !== '') {
                                menuItemId = item.element_id;
                            }

                            if (!hasDropdownContent) {
                                menuItemClassList.push('link-only');
                            } else {
                                menuItemClassList.push('e-click');
                            }

                            view.addRenderAttribute(menuItemWrapperKey, {
                                'id': menuItemId,
                                'class': menuItemClassList
                            });

                            view.addRenderAttribute(menuItemTitleKey, {
                                'class': 'db-mega-menu-title-text'
                            });

                            const menuItemContainerClasses = ['db-mega-menu-title-container'];

                            if (menuItemLink) {
                                menuItemContainerClasses.push('e-link', 'e-focus');
                            }

                            // Build link attributes safely
                            let linkAttrs = {
                                'class': menuItemContainerClasses,
                                'href': menuItemLink || ''
                            };
                            
                            if (item.item_link && typeof item.item_link === 'object') {
                                if (item.item_link.is_external) {
                                    linkAttrs['target'] = '_blank';
                                    linkAttrs['rel'] = 'noopener' + (item.item_link.nofollow === true ? ' nofollow' : '');
                                } else if (item.item_link.nofollow === true) {
                                    linkAttrs['rel'] = 'nofollow';
                                }
                            }

                            view.addRenderAttribute(menuItemTitleContainerLinkKey, linkAttrs);

                            view.addRenderAttribute(menuItemDropdownIconKey, {
                                'id': 'db-mega-menu-dropdown-icon-' + menuItemUid,
                                'class': ['db-mega-menu-dropdown-icon', 'e-focus'],
                                'data-tab-index': menuItemCount,
                                'aria-haspopup': hasDropdownContent ? 'true' : 'false',
                                'aria-expanded': 'false',
                                'aria-controls': 'db-mega-menu-content-' + menuItemUid
                            });
                            #>

                            <li class="db-mega-menu-item" data-has-dropdown="{{ hasDropdownContent ? 'true' : 'false' }}">
                                <div {{{ view.getRenderAttributeString( menuItemWrapperKey ) }}}>
                                    <# if ( menuItemLink ) { #>
                                        <a {{{ view.getRenderAttributeString( menuItemTitleContainerLinkKey ) }}}>
                                            <# } else { #>
                                                <div {{{ view.getRenderAttributeString( menuItemTitleContainerLinkKey ) }}}>
                                                    <# } #>

                                                        <# if (menuIcon && menuIcon.value) { #>
                                                            <span class="db-mega-menu-icon">{{{ menuIcon.value }}}</span>
                                                            <# } #>

                                                                <span {{{ view.getRenderAttributeString( menuItemTitleKey ) }}}>{{{ item.item_title || '' }}}</span>

                                                                <# if ( menuItemLink ) { #>
                                        </a>
                                        <# } else { #>
                                </div>
                                <# } #>

                                    <# if ( hasDropdownContent ) { #>
                                        <button {{{ view.getRenderAttributeString( menuItemDropdownIconKey ) }}}>
                                            <span class="db-mega-menu-dropdown-icon-opened">
                                                <i class="eicon-caret-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="db-mega-menu-dropdown-icon-closed">
                                                <i class="eicon-caret-down" aria-hidden="true"></i>
                                            </span>
                                        </button>
                                        <# } #>
                </div>
                <div class="db-mega-menu-content"></div>
                </li>
                <# } ); #>
                        {{{ getActionButtonHtml('desktop-right') }}}
                    </ul>
                    {{{ getActionButtonHtml('mobile-bottom') }}}
                    </div>
            </nav>
        <?php
    }

    protected function content_template_single_repeater_item()
    {
        ?>
            <#
                const elementUid = view.getIDInt().toString().substr(0, 3),
                menuItemCount = view.collection.length + 1,
                menuItemUid = elementUid + menuItemCount,
                menuItemWrapperKey = 'wrapper-' + menuItemUid,
                menuItemTitleKey = 'menu-title-' + menuItemUid,
                menuItemTitleContainerLinkKey = 'link-' + menuItemUid,
                menuItemDropdownIconKey = 'dropdown-' + menuItemUid,
                hasDropdownContent = data.item_dropdown_content === 'yes',
                menuItemClassList = ['db-mega-menu-title'];
                
                // Safe icon rendering
                let menuIcon = '';
                if (data.item_icon && data.item_icon.value) {
                    try {
                        const iconResult = elementor.helpers.renderIcon(view, data.item_icon, { 'aria-hidden': true }, 'i', 'object');
                        if (iconResult && iconResult.value) {
                            menuIcon = iconResult;
                        }
                    } catch(e) {}
                }
                
                // Safe link handling
                let menuItemLink = '';
                if (data.item_link) {
                    if (typeof data.item_link === 'string') {
                        menuItemLink = data.item_link;
                    } else if (data.item_link.url) {
                        menuItemLink = data.item_link.url;
                    }
                }
                
                let menuItemId = 'db-mega-menu-title-' + menuItemUid;
                if (data.element_id && data.element_id !== '') {
                    menuItemId = data.element_id;
                }

                if (!hasDropdownContent) {
                    menuItemClassList.push('link-only');
                } else {
                    menuItemClassList.push('e-click');
                }

                view.addRenderAttribute(menuItemWrapperKey, {
                    'id': menuItemId,
                    'class': menuItemClassList
                }, null, true);

                view.addRenderAttribute(menuItemTitleKey, {
                    'class': 'db-mega-menu-title-text'
                }, null, true);

                const menuItemContainerClasses = ['db-mega-menu-title-container'];

                if (menuItemLink) {
                    menuItemContainerClasses.push('e-link', 'e-focus');
                }

                // Build link attributes safely
                let linkAttrs = {
                    'class': menuItemContainerClasses,
                    'href': menuItemLink || ''
                };
                
                if (data.item_link && typeof data.item_link === 'object') {
                    if (data.item_link.is_external) {
                        linkAttrs['target'] = '_blank';
                        linkAttrs['rel'] = 'noopener' + (data.item_link.nofollow === true ? ' nofollow' : '');
                    } else if (data.item_link.nofollow === true) {
                        linkAttrs['rel'] = 'nofollow';
                    }
                }

                view.addRenderAttribute(menuItemTitleContainerLinkKey, linkAttrs, null, true);

                view.addRenderAttribute(menuItemDropdownIconKey, {
                    'id': 'db-mega-menu-dropdown-icon-' + menuItemUid,
                    'class': ['db-mega-menu-dropdown-icon', 'e-focus'],
                    'data-tab-index': menuItemCount,
                    'aria-haspopup': hasDropdownContent ? 'true' : 'false',
                    'aria-expanded': 'false',
                    'aria-controls': 'db-mega-menu-content-' + menuItemUid
                }, null, true);
                #>
                <li class="db-mega-menu-item" data-has-dropdown="{{ hasDropdownContent ? 'true' : 'false' }}">
                    <div {{{ view.getRenderAttributeString( menuItemWrapperKey ) }}}>
                        <# if ( menuItemLink ) { #>
                            <a {{{ view.getRenderAttributeString( menuItemTitleContainerLinkKey ) }}}>
                                <# } else { #>
                                    <div {{{ view.getRenderAttributeString( menuItemTitleContainerLinkKey ) }}}>
                                        <# } #>

                                            <# if (menuIcon && menuIcon.value) { #>
                                                <span class="db-mega-menu-icon">{{{ menuIcon.value }}}</span>
                                                <# } #>

                                                    <span {{{ view.getRenderAttributeString( menuItemTitleKey ) }}}>{{{ data.item_title || '' }}}</span>

                                                    <# if ( menuItemLink ) { #>
                            </a>
                            <# } else { #>
                    </div>
                    <# } #>

                        <# if ( hasDropdownContent ) { #>
                            <button {{{ view.getRenderAttributeString( menuItemDropdownIconKey ) }}}>
                                <span class="db-mega-menu-dropdown-icon-opened">
                                    <i class="eicon-caret-up" aria-hidden="true"></i>
                                </span>
                                <span class="db-mega-menu-dropdown-icon-closed">
                                    <i class="eicon-caret-down" aria-hidden="true"></i>
                                </span>
                            </button>
                            <# } #>
                                </div>
                                <div class="db-mega-menu-content"></div>
                </li>
        <?php
    }
}
