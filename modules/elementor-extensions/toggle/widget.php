<?php

/**
 * dailybuddy Toggle Widget
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
use Elementor\Utils;
use Elementor\Plugin;

class WP_Dailybuddy_Elementor_Toggle_Widget extends Widget_Base
{

    /**
     * Get widget name
     */
    public function get_name()
    {
        return 'dailybuddy-toggle';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('Content Toggle', 'dailybuddy');
    }

    /**
     * Get widget icon
     */
    public function get_icon()
    {
        return 'eicon-dual-button mini-icon-dailybuddy';
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
        return array('toggle', 'switcher', 'content', 'tabs', 'switch', 'dailybuddy');
    }

    /**
     * Register widget controls
     */
    protected function register_controls()
    {
        $this->register_general_controls();
        $this->register_primary_content_controls();
        $this->register_secondary_content_controls();
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
            'toggle_style',
            array(
                'label'   => __('Toggle Style', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => array(
                    'default'     => __('Default', 'dailybuddy'),
                    'glossy'      => __('Liquid Glass', 'dailybuddy'),
                    'grasshopper' => __('Crystalmorphism', 'dailybuddy'),
                ),
            )
        );

        $this->add_responsive_control(
            'toggle_alignment',
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
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-toggle-container' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Primary Content Controls
     */
    private function register_primary_content_controls()
    {
        $this->start_controls_section(
            'section_primary',
            array(
                'label' => __('Primary Content', 'dailybuddy'),
            )
        );

        $this->add_control(
            'primary_label',
            array(
                'label'       => __('Label', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Primary', 'dailybuddy'),
                'label_block' => true,
            )
        );

        // Default Style Icon
        $this->add_control(
            'primary_icon_show',
            array(
                'label'        => __('Show Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => array(
                    'toggle_style' => 'default',
                ),
            )
        );

        $this->add_control(
            'primary_icon',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-sun',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'toggle_style'       => 'default',
                    'primary_icon_show' => 'yes',
                ),
            )
        );

        // Glossy Style Icon
        $this->add_control(
            'glossy_primary_icon_show',
            array(
                'label'        => __('Show Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'toggle_style' => 'glossy',
                ),
            )
        );

        $this->add_control(
            'glossy_primary_icon',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-sun',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'toggle_style'              => 'glossy',
                    'glossy_primary_icon_show' => 'yes',
                ),
            )
        );

        // Grasshopper Style Icon
        $this->add_control(
            'grasshopper_primary_icon_show',
            array(
                'label'        => __('Show Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'toggle_style' => 'grasshopper',
                ),
            )
        );

        $this->add_control(
            'grasshopper_primary_icon',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-sun',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'toggle_style'                    => 'grasshopper',
                    'grasshopper_primary_icon_show' => 'yes',
                ),
            )
        );

        $this->add_control(
            'primary_content_type',
            array(
                'label'     => __('Content Type', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'content',
                'options'   => array(
                    'content'  => __('Content', 'dailybuddy'),
                    'image'    => __('Image', 'dailybuddy'),
                    'template' => __('Saved Template', 'dailybuddy'),
                ),
                'separator' => 'before',
            )
        );

        $this->add_control(
            'primary_content',
            array(
                'label'     => __('Content', 'dailybuddy'),
                'type'      => Controls_Manager::WYSIWYG,
                'default'   => __('This is the primary content. You can add any HTML content here.', 'dailybuddy'),
                'condition' => array(
                    'primary_content_type' => 'content',
                ),
            )
        );

        $this->add_control(
            'primary_image',
            array(
                'label'     => __('Image', 'dailybuddy'),
                'type'      => Controls_Manager::MEDIA,
                'default'   => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
                'condition' => array(
                    'primary_content_type' => 'image',
                ),
            )
        );

        $this->add_control(
            'primary_template',
            array(
                'label'     => __('Select Template', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $this->get_saved_templates(),
                'condition' => array(
                    'primary_content_type' => 'template',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Secondary Content Controls
     */
    private function register_secondary_content_controls()
    {
        $this->start_controls_section(
            'section_secondary',
            array(
                'label' => __('Secondary Content', 'dailybuddy'),
            )
        );

        $this->add_control(
            'secondary_label',
            array(
                'label'       => __('Label', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Secondary', 'dailybuddy'),
                'label_block' => true,
            )
        );

        // Default Style Icon
        $this->add_control(
            'secondary_icon_show',
            array(
                'label'        => __('Show Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => array(
                    'toggle_style' => 'default',
                ),
            )
        );

        $this->add_control(
            'secondary_icon',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-moon',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'toggle_style'         => 'default',
                    'secondary_icon_show' => 'yes',
                ),
            )
        );

        // Glossy Style Icon
        $this->add_control(
            'glossy_secondary_icon_show',
            array(
                'label'        => __('Show Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'toggle_style' => 'glossy',
                ),
            )
        );

        $this->add_control(
            'glossy_secondary_icon',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-moon',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'toggle_style'                => 'glossy',
                    'glossy_secondary_icon_show' => 'yes',
                ),
            )
        );

        // Grasshopper Style Icon
        $this->add_control(
            'grasshopper_secondary_icon_show',
            array(
                'label'        => __('Show Icon', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'toggle_style' => 'grasshopper',
                ),
            )
        );

        $this->add_control(
            'grasshopper_secondary_icon',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-moon',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'toggle_style'                      => 'grasshopper',
                    'grasshopper_secondary_icon_show' => 'yes',
                ),
            )
        );

        $this->add_control(
            'secondary_content_type',
            array(
                'label'     => __('Content Type', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'content',
                'options'   => array(
                    'content'  => __('Content', 'dailybuddy'),
                    'image'    => __('Image', 'dailybuddy'),
                    'template' => __('Saved Template', 'dailybuddy'),
                ),
                'separator' => 'before',
            )
        );

        $this->add_control(
            'secondary_content',
            array(
                'label'     => __('Content', 'dailybuddy'),
                'type'      => Controls_Manager::WYSIWYG,
                'default'   => __('This is the secondary content. You can add any HTML content here.', 'dailybuddy'),
                'condition' => array(
                    'secondary_content_type' => 'content',
                ),
            )
        );

        $this->add_control(
            'secondary_image',
            array(
                'label'     => __('Image', 'dailybuddy'),
                'type'      => Controls_Manager::MEDIA,
                'default'   => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
                'condition' => array(
                    'secondary_content_type' => 'image',
                ),
            )
        );

        $this->add_control(
            'secondary_template',
            array(
                'label'     => __('Select Template', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $this->get_saved_templates(),
                'condition' => array(
                    'secondary_content_type' => 'template',
                ),
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
            'container_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-toggle-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Toggle Switch Style
        $this->register_toggle_switch_style();

        // Label Style
        $this->register_label_style();

        // Content Style
        $this->register_content_style();
    }

    /**
     * Register Toggle Switch Style
     */
    private function register_toggle_switch_style()
    {
        $this->start_controls_section(
            'section_switch_style',
            array(
                'label' => __('Toggle Switch', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'switch_size',
            array(
                'label'      => __('Switch Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 30,
                        'max' => 100,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-toggle-switch' => 'width: {{SIZE}}{{UNIT}}; height: calc({{SIZE}}{{UNIT}} / 2);',
                    '{{WRAPPER}} .dailybuddy-toggle-slider:before' => 'width: calc({{SIZE}}{{UNIT}} / 2 - 4px); height: calc({{SIZE}}{{UNIT}} / 2 - 4px);',
                ),
            )
        );

        $this->add_control(
            'switch_background',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-toggle-slider' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'switch_active_background',
            array(
                'label'     => __('Active Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-toggle-input:checked + .dailybuddy-toggle-slider' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'switch_handle_color',
            array(
                'label'     => __('Handle Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-toggle-slider:before' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'switch_spacing',
            array(
                'label'      => __('Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 100,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-toggle-switcher' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Label Style
     */
    private function register_label_style()
    {
        $this->start_controls_section(
            'section_label_style',
            array(
                'label' => __('Labels', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'label_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-toggle-label' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-toggle-label',
            )
        );

        $this->add_responsive_control(
            'label_spacing',
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
                    '{{WRAPPER}} .dailybuddy-primary-toggle-label' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-secondary-toggle-label' => 'margin-left: {{SIZE}}{{UNIT}};',
                ),
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

        $this->add_responsive_control(
            'content_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-toggle-content-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'content_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-toggle-content-wrap',
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'content_border',
                'selector' => '{{WRAPPER}} .dailybuddy-toggle-content-wrap',
            )
        );

        $this->add_responsive_control(
            'content_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-toggle-content-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'content_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-toggle-content-wrap',
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
?>

        <div class="dailybuddy-toggle-container">
            <div class="dailybuddy-toggle-wrapper toggle-style-<?php echo esc_attr($settings['toggle_style']); ?>">

                <!-- Toggle Switcher -->
                <div class="dailybuddy-toggle-switcher">

                    <?php if ($settings['toggle_style'] === 'default'): ?>
                        <!-- Default Style -->
                        <div class="dailybuddy-primary-toggle-label dailybuddy-toggle-label">
                            <?php
                            if ($settings['primary_icon_show'] === 'yes' && !empty($settings['primary_icon']['value'])) {
                                Icons_Manager::render_icon($settings['primary_icon'], array('aria-hidden' => 'true'));
                            }
                            echo esc_html($settings['primary_label']);
                            ?>
                        </div>

                        <label class="dailybuddy-toggle-switch">
                            <input type="checkbox" class="dailybuddy-toggle-input" data-id="<?php echo esc_attr($id); ?>">
                            <span class="dailybuddy-toggle-slider"></span>
                        </label>

                        <div class="dailybuddy-secondary-toggle-label dailybuddy-toggle-label">
                            <?php
                            if ($settings['secondary_icon_show'] === 'yes' && !empty($settings['secondary_icon']['value'])) {
                                Icons_Manager::render_icon($settings['secondary_icon'], array('aria-hidden' => 'true'));
                            }
                            echo esc_html($settings['secondary_label']);
                            ?>
                        </div>

                    <?php elseif ($settings['toggle_style'] === 'glossy'): ?>
                        <!-- Glossy Style -->
                        <div class="dailybuddy-primary-toggle-label dailybuddy-toggle-label">
                            <?php echo esc_html($settings['primary_label']); ?>
                        </div>

                        <div class="dailybuddy-glossy-switcher">
                            <label class="dailybuddy-glossy-option" data-option="1">
                                <input class="dailybuddy-glossy-input" type="radio" name="toggle-<?php echo esc_attr($id); ?>" checked data-id="<?php echo esc_attr($id); ?>" data-option="1">
                                <span class="dailybuddy-glossy-icon">
                                    <?php
                                    if ($settings['glossy_primary_icon_show'] === 'yes' && !empty($settings['glossy_primary_icon']['value'])) {
                                        Icons_Manager::render_icon($settings['glossy_primary_icon'], array('aria-hidden' => 'true'));
                                    }
                                    ?>
                                </span>
                            </label>

                            <label class="dailybuddy-glossy-option" data-option="2">
                                <input class="dailybuddy-glossy-input" type="radio" name="toggle-<?php echo esc_attr($id); ?>" data-id="<?php echo esc_attr($id); ?>" data-option="2">
                                <span class="dailybuddy-glossy-icon">
                                    <?php
                                    if ($settings['glossy_secondary_icon_show'] === 'yes' && !empty($settings['glossy_secondary_icon']['value'])) {
                                        Icons_Manager::render_icon($settings['glossy_secondary_icon'], array('aria-hidden' => 'true'));
                                    }
                                    ?>
                                </span>
                            </label>
                        </div>

                        <div class="dailybuddy-secondary-toggle-label dailybuddy-toggle-label">
                            <?php echo esc_html($settings['secondary_label']); ?>
                        </div>

                    <?php elseif ($settings['toggle_style'] === 'grasshopper'): ?>
                        <!-- Grasshopper Style -->
                        <div class="dailybuddy-grasshopper-switcher">
                            <input type="radio" class="dailybuddy-grasshopper-input" id="switch-opt-1-<?php echo esc_attr($id); ?>" name="toggle-<?php echo esc_attr($id); ?>" checked data-id="<?php echo esc_attr($id); ?>" data-option="1">
                            <input type="radio" class="dailybuddy-grasshopper-input" id="switch-opt-2-<?php echo esc_attr($id); ?>" name="toggle-<?php echo esc_attr($id); ?>" data-id="<?php echo esc_attr($id); ?>" data-option="2">

                            <label for="switch-opt-1-<?php echo esc_attr($id); ?>" class="dailybuddy-grasshopper-button" data-option="1">
                                <?php
                                if ($settings['grasshopper_primary_icon_show'] === 'yes' && !empty($settings['grasshopper_primary_icon']['value'])) {
                                    Icons_Manager::render_icon($settings['grasshopper_primary_icon'], array('aria-hidden' => 'true'));
                                }
                                ?>
                                <div class="dailybuddy-primary-toggle-label dailybuddy-toggle-label">
                                    <?php echo esc_html($settings['primary_label']); ?>
                                </div>
                            </label>

                            <label for="switch-opt-2-<?php echo esc_attr($id); ?>" class="dailybuddy-grasshopper-button" data-option="2">
                                <?php
                                if ($settings['grasshopper_secondary_icon_show'] === 'yes' && !empty($settings['grasshopper_secondary_icon']['value'])) {
                                    Icons_Manager::render_icon($settings['grasshopper_secondary_icon'], array('aria-hidden' => 'true'));
                                }
                                ?>
                                <div class="dailybuddy-secondary-toggle-label dailybuddy-toggle-label">
                                    <?php echo esc_html($settings['secondary_label']); ?>
                                </div>
                            </label>

                            <div class="dailybuddy-grasshopper-slider"></div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Toggle Content -->
                <div class="dailybuddy-toggle-content-wrap" data-id="<?php echo esc_attr($id); ?>">
                    <div class="dailybuddy-toggle-primary-wrap dailybuddy-toggle-active">
                        <?php $this->render_toggle_content('primary', $settings); ?>
                    </div>
                    <div class="dailybuddy-toggle-secondary-wrap">
                        <?php $this->render_toggle_content('secondary', $settings); ?>
                    </div>
                </div>

            </div>
        </div>

<?php
    }

    /**
     * Render content based on type
     */
    private function render_toggle_content($type, $settings)
    {
        $content_type = $settings[$type . '_content_type'];

        if ($content_type === 'content') {
            echo wp_kses_post($settings[$type . '_content']);
        } elseif ($content_type === 'image' && !empty($settings[$type . '_image']['url'])) {
            echo '<img src="' . esc_url($settings[$type . '_image']['url']) . '" alt="' . esc_attr($settings[$type . '_label']) . '">';
        } elseif ($content_type === 'template' && !empty($settings[$type . '_template'])) {
            echo esc_html(
                Plugin::instance()->frontend->get_builder_content(
                    $settings[$type . '_template'],
                    true
                )
            );
        }
    }
}
