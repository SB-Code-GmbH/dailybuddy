<?php

/**
 * dailybuddy Advanced Accordion Widget
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
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Plugin;

class WP_Dailybuddy_Elementor_Advanced_Accordion_Widget extends Widget_Base
{

    /**
     * Get widget name
     */
    public function get_name()
    {
        return 'dailybuddy-advanced-accordion';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('Advanced Accordion', 'dailybuddy');
    }

    /**
     * Get widget icon
     */
    public function get_icon()
    {
        return 'eicon-accordion mini-icon-dailybuddy';
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
        return array('accordion', 'toggle', 'collapse', 'faq', 'tabs', 'dailybuddy');
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
        $this->register_general_controls();
        $this->register_accordion_items();
        $this->register_style_controls();
    }

    /**
     * Register General Controls
     */
    private function register_general_controls()
    {
        $this->start_controls_section(
            'section_general',
            array(
                'label' => __('General Settings', 'dailybuddy'),
            )
        );

        $this->add_control(
            'accordion_type',
            array(
                'label'   => __('Accordion Type', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'accordion',
                'options' => array(
                    'accordion' => __('Accordion (One at a Time)', 'dailybuddy'),
                    'toggle'    => __('Toggle (Multiple Open)', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'accordion_style',
            array(
                'label'   => __('Design Style', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'classic',
                'options' => array(
                    'classic' => __('Classic', 'dailybuddy'),
                    'modern'  => __('Modern', 'dailybuddy'),
                    'minimal' => __('Minimal', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'title_html_tag',
            array(
                'label'   => __('Title HTML Tag', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'div',
                'options' => array(
                    'h1'   => 'H1',
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'h4'   => 'H4',
                    'h5'   => 'H5',
                    'h6'   => 'H6',
                    'div'  => 'DIV',
                    'span' => 'SPAN',
                    'p'    => 'P',
                ),
            )
        );

        $this->add_control(
            'enable_auto_numbering',
            array(
                'label'        => __('Enable Auto Numbering', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
                'description'  => __('Add automatic numbering to accordion items', 'dailybuddy'),
            )
        );

        $this->add_control(
            'show_toggle_icon',
            array(
                'label'        => __('Show Toggle Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'toggle_icon_position',
            array(
                'label'     => __('Icon Position', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'right',
                'options'   => array(
                    'left'  => __('Left', 'dailybuddy'),
                    'right' => __('Right', 'dailybuddy'),
                ),
                'condition' => array(
                    'show_toggle_icon' => 'yes',
                ),
            )
        );

        $this->add_control(
            'icon_opened',
            array(
                'label'     => __('Opened Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-minus',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'show_toggle_icon' => 'yes',
                ),
            )
        );

        $this->add_control(
            'icon_closed',
            array(
                'label'     => __('Closed Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-plus',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'show_toggle_icon' => 'yes',
                ),
            )
        );

        $this->add_control(
            'toggle_speed',
            array(
                'label'   => __('Toggle Speed (ms)', 'dailybuddy'),
                'type'    => Controls_Manager::SLIDER,
                'range'   => array(
                    'px' => array(
                        'min'  => 100,
                        'max'  => 1000,
                        'step' => 50,
                    ),
                ),
                'default' => array(
                    'size' => 300,
                ),
            )
        );

        $this->add_control(
            'scroll_on_click',
            array(
                'label'        => __('Scroll on Click', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'scroll_offset',
            array(
                'label'     => __('Scroll Offset (px)', 'dailybuddy'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 200,
                    ),
                ),
                'default'   => array(
                    'size' => 0,
                ),
                'condition' => array(
                    'scroll_on_click' => 'yes',
                ),
            )
        );

        $this->add_control(
            'enable_faq_schema',
            array(
                'label'        => __('Enable FAQ Schema', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
                'description'  => __('Add structured data for FAQ schema', 'dailybuddy'),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Accordion Items
     */
    private function register_accordion_items()
    {
        $this->start_controls_section(
            'section_items',
            array(
                'label' => __('Accordion Items', 'dailybuddy'),
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'tab_title',
            array(
                'label'       => __('Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Accordion Title', 'dailybuddy'),
                'label_block' => true,
                'dynamic'     => array(
                    'active' => true,
                ),
            )
        );

        $repeater->add_control(
            'tab_icon_enable',
            array(
                'label'        => __('Enable Tab Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $repeater->add_control(
            'tab_icon',
            array(
                'label'     => __('Tab Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'condition' => array(
                    'tab_icon_enable' => 'yes',
                ),
            )
        );

        $repeater->add_control(
            'content_type',
            array(
                'label'   => __('Content Type', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'content',
                'options' => array(
                    'content'  => __('Content', 'dailybuddy'),
                    'template' => __('Saved Template', 'dailybuddy'),
                ),
            )
        );

        $repeater->add_control(
            'tab_content',
            array(
                'label'     => __('Content', 'dailybuddy'),
                'type'      => Controls_Manager::WYSIWYG,
                'default'   => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'dailybuddy'),
                'condition' => array(
                    'content_type' => 'content',
                ),
            )
        );

        $repeater->add_control(
            'saved_template',
            array(
                'label'     => __('Select Template', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $this->get_saved_templates(),
                'condition' => array(
                    'content_type' => 'template',
                ),
            )
        );

        $repeater->add_control(
            'active_as_default',
            array(
                'label'        => __('Active as Default', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $repeater->add_control(
            'custom_id',
            array(
                'label'       => __('Custom ID', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'description' => __('Add custom ID for deep linking (e.g., #my-accordion)', 'dailybuddy'),
            )
        );

        $this->add_control(
            'accordion_items',
            array(
                'label'       => __('Items', 'dailybuddy'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => array(
                    array(
                        'tab_title'         => __('Accordion Item #1', 'dailybuddy'),
                        'tab_content'       => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'dailybuddy'),
                        'active_as_default' => 'yes',
                    ),
                    array(
                        'tab_title'   => __('Accordion Item #2', 'dailybuddy'),
                        'tab_content' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'dailybuddy'),
                    ),
                    array(
                        'tab_title'   => __('Accordion Item #3', 'dailybuddy'),
                        'tab_content' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'dailybuddy'),
                    ),
                ),
                'title_field' => '{{{ tab_title }}}',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get saved templates
     */
    private function get_saved_templates()
    {
        $templates = Plugin::instance()->templates_manager->get_source('local')->get_items();
        $options   = array('' => __('Select Template', 'dailybuddy'));

        foreach ($templates as $template) {
            $options[$template['template_id']] = $template['title'];
        }

        return $options;
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
            'container_spacing',
            array(
                'label'      => __('Spacing Between Items', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                    ),
                ),
                'default'    => array(
                    'size' => 10,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-accordion-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Tab Title Style
        $this->register_tab_style();

        // Content Style
        $this->register_content_style();

        // Icon Style
        $this->register_icon_style();
    }

    /**
     * Register Tab Style
     */
    private function register_tab_style()
    {
        $this->start_controls_section(
            'section_tab_style',
            array(
                'label' => __('Tab Title', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'tab_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-title',
            )
        );

        $this->add_responsive_control(
            'tab_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px'),
                'default'    => array(
                    'top'    => 15,
                    'right'  => 20,
                    'bottom' => 15,
                    'left'   => 20,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-accordion-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs('tab_style_tabs');

        // Normal State
        $this->start_controls_tab(
            'tab_normal',
            array(
                'label' => __('Normal', 'dailybuddy'),
            )
        );

        $this->add_control(
            'tab_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'tab_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-title',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'tab_border',
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-title',
            )
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'tab_hover',
            array(
                'label' => __('Hover', 'dailybuddy'),
            )
        );

        $this->add_control(
            'tab_hover_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-title:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'tab_hover_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-title:hover',
            )
        );

        $this->add_control(
            'tab_hover_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-title:hover' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        // Active State
        $this->start_controls_tab(
            'tab_active',
            array(
                'label' => __('Active', 'dailybuddy'),
            )
        );

        $this->add_control(
            'tab_active_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#2196F3',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-item.active .dailybuddy-accordion-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'tab_active_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-item.active .dailybuddy-accordion-title',
            )
        );

        $this->add_control(
            'tab_active_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-item.active .dailybuddy-accordion-title' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'tab_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'separator'  => 'before',
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-accordion-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'tab_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-title',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Content Style
     */
    private function register_content_style()
    {
        $this->start_controls_section(
            'section_content_style',
            array(
                'label' => __('Content', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'content_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-content' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-content',
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'content_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-content',
            )
        );

        $this->add_responsive_control(
            'content_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px'),
                'default'    => array(
                    'top'    => 20,
                    'right'  => 20,
                    'bottom' => 20,
                    'left'   => 20,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-accordion-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'content_border',
                'selector' => '{{WRAPPER}} .dailybuddy-accordion-content',
            )
        );

        $this->add_responsive_control(
            'content_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-accordion-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Icon Style
     */
    private function register_icon_style()
    {
        $this->start_controls_section(
            'section_icon_style',
            array(
                'label'     => __('Toggle Icon', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_toggle_icon' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_size',
            array(
                'label'      => __('Size', 'dailybuddy'),
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
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-accordion-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-accordion-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-accordion-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_active_color',
            array(
                'label'     => __('Active Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-accordion-item.active .dailybuddy-accordion-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-accordion-item.active .dailybuddy-accordion-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_spacing',
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
                'default'    => array(
                    'size' => 10,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .icon-left .dailybuddy-accordion-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .icon-right .dailybuddy-accordion-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
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
        $this->add_render_attribute(
            'accordion-wrapper',
            array(
                'class'            => array(
                    'dailybuddy-accordion',
                    'style-' . $settings['accordion_style'],
                    'icon-' . $settings['toggle_icon_position'],
                ),
                'data-type'        => $settings['accordion_type'],
                'data-speed'       => $settings['toggle_speed']['size'],
                'data-scroll'      => $settings['scroll_on_click'],
                'data-offset'      => !empty($settings['scroll_offset']['size']) ? $settings['scroll_offset']['size'] : 0,
            )
        );

        if ($settings['enable_auto_numbering'] === 'yes') {
            $this->add_render_attribute('accordion-wrapper', 'class', 'has-numbering');
        }

        // FAQ Schema
        $faq_schema = array();
        if ($settings['enable_faq_schema'] === 'yes') {
            $faq_schema['@context'] = 'https://schema.org';
            $faq_schema['@type'] = 'FAQPage';
            $faq_schema['mainEntity'] = array();
        }
?>

        <div <?php $this->print_render_attribute_string('accordion-wrapper'); ?>>
            <?php
            $item_number = 1;
            foreach ($settings['accordion_items'] as $index => $item):
                $item_key = 'item_' . $index;
                $title_key = 'title_' . $index;
                $content_key = 'content_' . $index;

                $custom_id = !empty($item['custom_id']) ? sanitize_title($item['custom_id']) : '';
                $is_active = $item['active_as_default'] === 'yes' ? 'active' : '';

                // Add to FAQ Schema
                if ($settings['enable_faq_schema'] === 'yes' && $item['content_type'] === 'content') {
                    $faq_schema['mainEntity'][] = array(
                        '@type' => 'Question',
                        'name' => $item['tab_title'],
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text' => wp_strip_all_tags($item['tab_content']),
                        ),
                    );
                }
            ?>
                <div class="dailybuddy-accordion-item <?php echo esc_attr($is_active); ?>"
                    <?php echo !empty($custom_id) ? 'id="' . esc_attr($custom_id) . '"' : ''; ?>>

                    <<?php echo esc_attr($settings['title_html_tag']); ?>
                        class="dailybuddy-accordion-title"
                        role="button"
                        aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>">

                        <?php if (isset($settings['enable_auto_numbering']) && 'yes' === $settings['enable_auto_numbering']) : ?>
                            <span class="dailybuddy-accordion-number">
                                <?php echo esc_html(sprintf('%02d', (int) $item_number)); ?>
                            </span>
                        <?php endif; ?>


                        <?php if ($item['tab_icon_enable'] === 'yes' && !empty($item['tab_icon']['value'])): ?>
                            <span class="dailybuddy-tab-icon">
                                <?php Icons_Manager::render_icon($item['tab_icon'], array('aria-hidden' => 'true')); ?>
                            </span>
                        <?php endif; ?>

                        <span class="dailybuddy-accordion-title-text"><?php echo esc_html($item['tab_title']); ?></span>

                        <?php if ($settings['show_toggle_icon'] === 'yes'): ?>
                            <span class="dailybuddy-accordion-icon">
                                <span class="icon-closed">
                                    <?php Icons_Manager::render_icon($settings['icon_closed'], array('aria-hidden' => 'true')); ?>
                                </span>
                                <span class="icon-opened">
                                    <?php Icons_Manager::render_icon($settings['icon_opened'], array('aria-hidden' => 'true')); ?>
                                </span>
                            </span>
                        <?php endif; ?>
                    </<?php echo esc_attr($settings['title_html_tag']); ?>>

                    <div class="dailybuddy-accordion-content" <?php echo $is_active ? ' style="display:block;"' : ' style="display:none;"'; ?>>
                        <?php
                        if ($item['content_type'] === 'template' && !empty($item['saved_template'])) {
                            echo wp_kses_post(
                                Plugin::instance()->frontend->get_builder_content(
                                    $item['saved_template'],
                                    true
                                )
                            );
                        } else {
                            echo wp_kses_post($item['tab_content']);
                        }
                        ?>
                    </div>
                </div>
            <?php
                $item_number++;
            endforeach;
            ?>
        </div>

<?php
        // Output FAQ Schema
        if ($settings['enable_faq_schema'] === 'yes' && !empty($faq_schema['mainEntity'])) {
            echo '<script type="application/ld+json">' . wp_json_encode($faq_schema) . '</script>';
        }
    }
}
