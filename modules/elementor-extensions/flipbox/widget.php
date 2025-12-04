<?php

/**
 * dailybuddy FlipBox Widget
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

class WP_Dailybuddy_Elementor_FlipBox_Widget extends Widget_Base
{

    /**
     * Get widget name
     */
    public function get_name()
    {
        return 'dailybuddy-flipbox';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('FlipBox', 'dailybuddy');
    }

    /**
     * Get widget icon (Icon im Elementor-Panel)
     */
    public function get_icon()
    {
        return 'eicon-flip-box mini-icon-dailybuddy';
    }

    /**
     * Get widget categories (linke Seitenleiste)
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
        return array('flipbox', 'flip', 'box', 'card', 'rotate', 'dailybuddy');
    }

    /**
     * Register widget controls
     */
    protected function register_controls()
    {
        $this->register_settings_controls();
        $this->register_front_content_controls();
        $this->register_back_content_controls();
        $this->register_style_controls();
    }

    /**
     * Register Settings Controls
     */
    private function register_settings_controls()
    {
        $this->start_controls_section(
            'section_settings',
            array(
                'label' => __('Settings', 'dailybuddy'),
            )
        );

        $this->add_control(
            'flip_effect',
            array(
                'label'   => __('Flip Effect', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'flip-left',
                'options' => array(
                    'flip-left'   => __('Flip Left', 'dailybuddy'),
                    'flip-right'  => __('Flip Right', 'dailybuddy'),
                    'flip-up'     => __('Flip Up', 'dailybuddy'),
                    'flip-down'   => __('Flip Down', 'dailybuddy'),
                    'zoom-in'     => __('Zoom In', 'dailybuddy'),
                    'zoom-out'    => __('Zoom Out', 'dailybuddy'),
                    'fade'        => __('Fade', 'dailybuddy'),
                ),
            )
        );

        $this->add_responsive_control(
            'box_height',
            array(
                'label'      => __('Height', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 200,
                        'max' => 800,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 400,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox' => 'height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'flip_duration',
            array(
                'label'      => __('Flip Duration (ms)', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => array(
                    'px' => array(
                        'min'  => 100,
                        'max'  => 2000,
                        'step' => 100,
                    ),
                ),
                'default'    => array(
                    'size' => 600,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-inner' => 'transition-duration: {{SIZE}}ms;',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Front Content Controls
     */
    private function register_front_content_controls()
    {
        $this->start_controls_section(
            'section_front_content',
            array(
                'label' => __('Front Content', 'dailybuddy'),
            )
        );

        $this->add_control(
            'front_icon_type',
            array(
                'label'   => __('Icon Type', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'none' => array(
                        'title' => __('None', 'dailybuddy'),
                        'icon'  => 'eicon-ban',
                    ),
                    'icon' => array(
                        'title' => __('Icon', 'dailybuddy'),
                        'icon'  => 'eicon-star',
                    ),
                    'image' => array(
                        'title' => __('Image', 'dailybuddy'),
                        'icon'  => 'eicon-image',
                    ),
                ),
                'default' => 'icon',
            )
        );

        $this->add_control(
            'front_icon',
            array(
                'label'       => __('Icon', 'dailybuddy'),
                'type'        => Controls_Manager::ICONS,
                'default'     => array(
                    'value'   => 'fas fa-star',
                    'library' => 'solid',
                ),
                'condition'   => array(
                    'front_icon_type' => 'icon',
                ),
            )
        );

        $this->add_control(
            'front_image',
            array(
                'label'     => __('Image', 'dailybuddy'),
                'type'      => Controls_Manager::MEDIA,
                'default'   => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
                'condition' => array(
                    'front_icon_type' => 'image',
                ),
            )
        );

        $this->add_control(
            'front_title',
            array(
                'label'       => __('Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Front Title', 'dailybuddy'),
                'label_block' => true,
                'dynamic'     => array(
                    'active' => true,
                ),
            )
        );

        $this->add_control(
            'front_description',
            array(
                'label'       => __('Description', 'dailybuddy'),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => __('This is the front side of the flip box.', 'dailybuddy'),
                'rows'        => 5,
                'dynamic'     => array(
                    'active' => true,
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Back Content Controls
     */
    private function register_back_content_controls()
    {
        $this->start_controls_section(
            'section_back_content',
            array(
                'label' => __('Back Content', 'dailybuddy'),
            )
        );

        $this->add_control(
            'back_icon_type',
            array(
                'label'   => __('Icon Type', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'none' => array(
                        'title' => __('None', 'dailybuddy'),
                        'icon'  => 'eicon-ban',
                    ),
                    'icon' => array(
                        'title' => __('Icon', 'dailybuddy'),
                        'icon'  => 'eicon-star',
                    ),
                    'image' => array(
                        'title' => __('Image', 'dailybuddy'),
                        'icon'  => 'eicon-image',
                    ),
                ),
                'default' => 'none',
            )
        );

        $this->add_control(
            'back_icon',
            array(
                'label'       => __('Icon', 'dailybuddy'),
                'type'        => Controls_Manager::ICONS,
                'default'     => array(
                    'value'   => 'fas fa-heart',
                    'library' => 'solid',
                ),
                'condition'   => array(
                    'back_icon_type' => 'icon',
                ),
            )
        );

        $this->add_control(
            'back_image',
            array(
                'label'     => __('Image', 'dailybuddy'),
                'type'      => Controls_Manager::MEDIA,
                'default'   => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
                'condition' => array(
                    'back_icon_type' => 'image',
                ),
            )
        );

        $this->add_control(
            'back_title',
            array(
                'label'       => __('Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Back Title', 'dailybuddy'),
                'label_block' => true,
                'dynamic'     => array(
                    'active' => true,
                ),
            )
        );

        $this->add_control(
            'back_description',
            array(
                'label'       => __('Description', 'dailybuddy'),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => __('This is the back side of the flip box with more detailed information.', 'dailybuddy'),
                'rows'        => 5,
                'dynamic'     => array(
                    'active' => true,
                ),
            )
        );

        $this->add_control(
            'show_button',
            array(
                'label'        => __('Show Button', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'dailybuddy'),
                'label_off'    => __('No', 'dailybuddy'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'button_text',
            array(
                'label'       => __('Button Text', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Learn More', 'dailybuddy'),
                'condition'   => array(
                    'show_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_link',
            array(
                'label'       => __('Button Link', 'dailybuddy'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'dailybuddy'),
                'default'     => array(
                    'url' => '#',
                ),
                'condition'   => array(
                    'show_button' => 'yes',
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
        // Front Side Styles
        $this->start_controls_section(
            'section_front_style',
            array(
                'label' => __('Front Side', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'front_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-front',
            )
        );

        $this->add_control(
            'front_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-front' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'front_border',
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-front',
            )
        );

        $this->add_control(
            'front_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-front' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'front_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-front',
            )
        );

        // Icon Front
        $this->add_control(
            'front_icon_heading',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'front_icon_color',
            array(
                'label'     => __('Icon Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-icon i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'front_icon_size',
            array(
                'label'      => __('Icon Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 200,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        // Front Title
        $this->add_control(
            'front_title_heading',
            array(
                'label'     => __('Title', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'front_title_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'front_title_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-title',
            )
        );

        // Front Description
        $this->add_control(
            'front_description_heading',
            array(
                'label'     => __('Description', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'front_description_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-description' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'front_description_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-front .flipbox-description',
            )
        );

        $this->end_controls_section();

        // Back Side Styles
        $this->start_controls_section(
            'section_back_style',
            array(
                'label' => __('Back Side', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'back_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-back',
            )
        );

        $this->add_control(
            'back_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-back' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'back_border',
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-back',
            )
        );

        $this->add_control(
            'back_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-back' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        // Icon Back
        $this->add_control(
            'back_icon_heading',
            array(
                'label'     => __('Icon', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'back_icon_color',
            array(
                'label'     => __('Icon Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-icon i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-icon svg' => 'fill: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'back_icon_size',
            array(
                'label'      => __('Icon Size', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 200,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        // Back Title
        $this->add_control(
            'back_title_heading',
            array(
                'label'     => __('Title', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'back_title_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'back_title_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-title',
            )
        );

        // Back Description
        $this->add_control(
            'back_description_heading',
            array(
                'label'     => __('Description', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'back_description_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-description' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'back_description_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-flipbox-back .flipbox-description',
            )
        );

        // Button
        $this->add_control(
            'button_heading',
            array(
                'label'     => __('Button', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'button_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .flipbox-button' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_background',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .flipbox-button' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .flipbox-button',
            )
        );

        $this->add_control(
            'button_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .flipbox-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'button_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .flipbox-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
        $settings    = $this->get_settings_for_display();
        $flip_effect = $settings['flip_effect'];

        // Button link attributes
        $target   = !empty($settings['button_link']['is_external']) ? ' target="_blank"' : '';
        $nofollow = !empty($settings['button_link']['nofollow']) ? ' rel="nofollow"' : '';
?>

        <div class="dailybuddy-flipbox <?php echo esc_attr($flip_effect); ?>">
            <div class="dailybuddy-flipbox-inner">

                <!-- Front Side -->
                <div class="dailybuddy-flipbox-front">
                    <div class="flipbox-content">

                        <?php if ($settings['front_icon_type'] === 'icon' && !empty($settings['front_icon']['value'])) : ?>
                            <div class="flipbox-icon">
                                <?php Icons_Manager::render_icon($settings['front_icon'], array('aria-hidden' => 'true')); ?>
                            </div>
                        <?php elseif ($settings['front_icon_type'] === 'image' && !empty($settings['front_image']['url'])) : ?>
                            <div class="flipbox-image">
                                <img src="<?php echo esc_url($settings['front_image']['url']); ?>" alt="<?php echo esc_attr($settings['front_title']); ?>">
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($settings['front_title'])) : ?>
                            <h3 class="flipbox-title"><?php echo esc_html($settings['front_title']); ?></h3>
                        <?php endif; ?>

                        <?php if (!empty($settings['front_description'])) : ?>
                            <div class="flipbox-description"><?php echo wp_kses_post($settings['front_description']); ?></div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Back Side -->
                <div class="dailybuddy-flipbox-back">
                    <div class="flipbox-content">

                        <?php if ($settings['back_icon_type'] === 'icon' && !empty($settings['back_icon']['value'])) : ?>
                            <div class="flipbox-icon">
                                <?php Icons_Manager::render_icon($settings['back_icon'], array('aria-hidden' => 'true')); ?>
                            </div>
                        <?php elseif ($settings['back_icon_type'] === 'image' && !empty($settings['back_image']['url'])) : ?>
                            <div class="flipbox-image">
                                <img src="<?php echo esc_url($settings['back_image']['url']); ?>" alt="<?php echo esc_attr($settings['back_title']); ?>">
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($settings['back_title'])) : ?>
                            <h3 class="flipbox-title"><?php echo esc_html($settings['back_title']); ?></h3>
                        <?php endif; ?>

                        <?php if (!empty($settings['back_description'])) : ?>
                            <div class="flipbox-description"><?php echo wp_kses_post($settings['back_description']); ?></div>
                        <?php endif; ?>

                        <?php
                        $button_link = $settings['button_link'];

                        $target   = ! empty($button_link['is_external']) ? '_blank' : '_self';
                        $nofollow = ! empty($button_link['nofollow']) ? 'nofollow' : '';

                        if ('yes' === $settings['show_button'] && ! empty($settings['button_text'])) : ?>
                            <a href="<?php echo esc_url($settings['button_link']['url']); ?>"
                                class="flipbox-button"
                                target="<?php echo esc_attr($target); ?>"
                                <?php if ($nofollow) : ?>
                                rel="<?php echo esc_attr($nofollow); ?>"
                                <?php endif; ?>>
                                <?php echo esc_html($settings['button_text']); ?>
                            </a>
                        <?php endif; ?>


                    </div>
                </div>

            </div>
        </div>

<?php
    }
}
