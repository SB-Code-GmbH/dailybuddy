<?php

/**
 * dailybuddy Product Card Widget
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

class WP_Dailybuddy_Elementor_Product_Card_Widget extends Widget_Base
{

    /**
     * Get widget name
     */
    public function get_name()
    {
        return 'dailybuddy-product-card';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('Product Card', 'dailybuddy');
    }

    /**
     * Get widget icon
     */
    public function get_icon()
    {
        return 'eicon-products mini-icon-dailybuddy';
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
        return array('product', 'card', 'ecommerce', 'shop', 'showcase', 'dailybuddy');
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
        $this->register_layout_controls();
        $this->register_product_details_controls();
        $this->register_badge_controls();
        $this->register_countdown_controls();
        $this->register_quick_view_controls();
        $this->register_social_share_controls();
        $this->register_style_controls();
    }

    /**
     * Register Layout Controls
     */
    private function register_layout_controls()
    {
        $this->start_controls_section(
            'section_layout',
            array(
                'label' => __('Layout', 'dailybuddy'),
            )
        );

        $this->add_control(
            'layout_style',
            array(
                'label'   => __('Layout Style', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => array(
                    'default'          => __('Default', 'dailybuddy'),
                    'overlay'          => __('Overlay on Hover', 'dailybuddy'),
                    'content-on-hover' => __('Content Appears on Hover', 'dailybuddy'),
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Product Details Controls
     */
    private function register_product_details_controls()
    {
        $this->start_controls_section(
            'section_product_details',
            array(
                'label' => __('Product Details', 'dailybuddy'),
            )
        );

        $this->add_control(
            'product_image',
            array(
                'label'   => __('Product Image', 'dailybuddy'),
                'type'    => Controls_Manager::MEDIA,
                'default' => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
            )
        );

        $this->add_control(
            'product_title',
            array(
                'label'       => __('Product Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Amazing Product', 'dailybuddy'),
                'label_block' => true,
            )
        );

        $this->add_control(
            'product_description',
            array(
                'label'   => __('Description', 'dailybuddy'),
                'type'    => Controls_Manager::TEXTAREA,
                'default' => __('This is an amazing product description that showcases the features and benefits.', 'dailybuddy'),
                'rows'    => 4,
            )
        );

        $this->add_control(
            'show_price',
            array(
                'label'        => __('Show Price', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'product_price',
            array(
                'label'     => __('Price', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => '$99.00',
                'condition' => array(
                    'show_price' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_old_price',
            array(
                'label'        => __('Show Old Price', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => array(
                    'show_price' => 'yes',
                ),
            )
        );

        $this->add_control(
            'product_old_price',
            array(
                'label'     => __('Old Price', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => '$149.00',
                'condition' => array(
                    'show_price'     => 'yes',
                    'show_old_price' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_rating',
            array(
                'label'        => __('Show Rating', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'product_rating',
            array(
                'label'     => __('Rating', 'dailybuddy'),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 0,
                'max'       => 5,
                'step'      => 0.1,
                'default'   => 4.5,
                'condition' => array(
                    'show_rating' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_review_count',
            array(
                'label'        => __('Show Review Count', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'show_rating' => 'yes',
                ),
            )
        );

        $this->add_control(
            'review_count',
            array(
                'label'     => __('Review Count', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => '(128 reviews)',
                'condition' => array(
                    'show_rating'       => 'yes',
                    'show_review_count' => 'yes',
                ),
            )
        );

        $this->add_control(
            'buttons_heading',
            array(
                'label'     => __('Buttons', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'show_details_button',
            array(
                'label'        => __('Show Details Button', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'details_button_text',
            array(
                'label'     => __('Button Text', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('View Details', 'dailybuddy'),
                'condition' => array(
                    'show_details_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'details_button_link',
            array(
                'label'       => __('Link', 'dailybuddy'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'dailybuddy'),
                'default'     => array(
                    'url' => '#',
                ),
                'condition'   => array(
                    'show_details_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'details_button_icon',
            array(
                'label'     => __('Button Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'condition' => array(
                    'show_details_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_icon_position',
            array(
                'label'     => __('Icon Position', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'right',
                'options'   => array(
                    'left'  => __('Left', 'dailybuddy'),
                    'right' => __('Right', 'dailybuddy'),
                ),
                'condition' => array(
                    'show_details_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_cart_button',
            array(
                'label'        => __('Show Add to Cart Button', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'cart_button_text',
            array(
                'label'     => __('Cart Button Text', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('Add to Cart', 'dailybuddy'),
                'condition' => array(
                    'show_cart_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_button_link',
            array(
                'label'       => __('Cart Button Link', 'dailybuddy'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'dailybuddy'),
                'default'     => array(
                    'url' => '#',
                ),
                'condition'   => array(
                    'show_cart_button' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_button_icon',
            array(
                'label'     => __('Cart Button Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-shopping-cart',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'show_cart_button' => 'yes',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Badge Controls
     */
    private function register_badge_controls()
    {
        $this->start_controls_section(
            'section_badge',
            array(
                'label' => __('Badge / Ribbon', 'dailybuddy'),
            )
        );

        $this->add_control(
            'show_badge',
            array(
                'label'        => __('Show Badge', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'badge_type',
            array(
                'label'     => __('Badge Type', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'sale',
                'options'   => array(
                    'sale'   => __('Sale', 'dailybuddy'),
                    'new'    => __('New', 'dailybuddy'),
                    'hot'    => __('Hot', 'dailybuddy'),
                    'custom' => __('Custom', 'dailybuddy'),
                ),
                'condition' => array(
                    'show_badge' => 'yes',
                ),
            )
        );

        $this->add_control(
            'badge_custom_text',
            array(
                'label'     => __('Custom Badge Text', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('Limited', 'dailybuddy'),
                'condition' => array(
                    'show_badge'  => 'yes',
                    'badge_type' => 'custom',
                ),
            )
        );

        $this->add_control(
            'badge_position',
            array(
                'label'     => __('Badge Position', 'dailybuddy'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'top-left',
                'options'   => array(
                    'top-left'     => __('Top Left', 'dailybuddy'),
                    'top-right'    => __('Top Right', 'dailybuddy'),
                    'bottom-left'  => __('Bottom Left', 'dailybuddy'),
                    'bottom-right' => __('Bottom Right', 'dailybuddy'),
                ),
                'condition' => array(
                    'show_badge' => 'yes',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Countdown Controls
     */
    private function register_countdown_controls()
    {
        $this->start_controls_section(
            'section_countdown',
            array(
                'label' => __('Countdown Timer', 'dailybuddy'),
            )
        );

        $this->add_control(
            'show_countdown',
            array(
                'label'        => __('Show Countdown', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'countdown_date',
            array(
                'label'       => __('End Date & Time', 'dailybuddy'),
                'type'        => Controls_Manager::DATE_TIME,
                'default'     => gmdate('Y-m-d H:i', strtotime('+7 days')),
                'condition'   => array(
                    'show_countdown' => 'yes',
                ),
                'description' => __('Set the deadline for your offer', 'dailybuddy'),
            )
        );

        $this->add_control(
            'countdown_label',
            array(
                'label'     => __('Countdown Label', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('Offer ends in:', 'dailybuddy'),
                'condition' => array(
                    'show_countdown' => 'yes',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Quick View Controls
     */
    private function register_quick_view_controls()
    {
        $this->start_controls_section(
            'section_quick_view',
            array(
                'label' => __('Quick View', 'dailybuddy'),
            )
        );

        $this->add_control(
            'show_quick_view',
            array(
                'label'        => __('Enable Quick View', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'quick_view_icon',
            array(
                'label'     => __('Quick View Icon', 'dailybuddy'),
                'type'      => Controls_Manager::ICONS,
                'default'   => array(
                    'value'   => 'fas fa-eye',
                    'library' => 'solid',
                ),
                'condition' => array(
                    'show_quick_view' => 'yes',
                ),
            )
        );

        $this->add_control(
            'quick_view_content',
            array(
                'label'     => __('Quick View Content', 'dailybuddy'),
                'type'      => Controls_Manager::WYSIWYG,
                'default'   => __('<h3>Product Details</h3><p>Add detailed product information here that will appear in the quick view popup.</p>', 'dailybuddy'),
                'condition' => array(
                    'show_quick_view' => 'yes',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Social Share Controls
     */
    private function register_social_share_controls()
    {
        $this->start_controls_section(
            'section_social_share',
            array(
                'label' => __('Social Share', 'dailybuddy'),
            )
        );

        $this->add_control(
            'show_social_share',
            array(
                'label'        => __('Enable Social Share', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'share_url',
            array(
                'label'       => __('Share URL', 'dailybuddy'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://your-product-url.com', 'dailybuddy'),
                'default'     => array(
                    'url' => '',
                ),
                'condition'   => array(
                    'show_social_share' => 'yes',
                ),
                'description' => __('Leave empty to use current page URL', 'dailybuddy'),
            )
        );

        $this->add_control(
            'social_facebook',
            array(
                'label'        => __('Facebook', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'show_social_share' => 'yes',
                ),
            )
        );

        $this->add_control(
            'social_twitter',
            array(
                'label'        => __('Twitter', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'show_social_share' => 'yes',
                ),
            )
        );

        $this->add_control(
            'social_pinterest',
            array(
                'label'        => __('Pinterest', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'show_social_share' => 'yes',
                ),
            )
        );

        $this->add_control(
            'social_linkedin',
            array(
                'label'        => __('LinkedIn', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => array(
                    'show_social_share' => 'yes',
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
        // Card Style
        $this->start_controls_section(
            'section_card_style',
            array(
                'label' => __('Card', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'card_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-product-card',
            )
        );

        $this->add_responsive_control(
            'card_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .dailybuddy-product-card',
            )
        );

        $this->add_responsive_control(
            'card_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-product-card',
            )
        );

        $this->end_controls_section();

        // More style sections...
        $this->register_image_style();
        $this->register_content_style();
        $this->register_badge_style();
        $this->register_countdown_style();
        $this->register_button_style();
    }

    /**
     * Register Image Style
     */
    private function register_image_style()
    {
        $this->start_controls_section(
            'section_image_style',
            array(
                'label' => __('Image', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'image_height',
            array(
                'label'      => __('Height', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 100,
                        'max' => 800,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-image img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                ),
            )
        );

        $this->add_responsive_control(
            'image_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-image, {{WRAPPER}} .dailybuddy-product-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        $this->add_control(
            'title_heading',
            array(
                'label' => __('Title', 'dailybuddy'),
                'type'  => Controls_Manager::HEADING,
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-product-title',
            )
        );

        $this->add_responsive_control(
            'title_spacing',
            array(
                'label'      => __('Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'description_heading',
            array(
                'label'     => __('Description', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'description_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-description' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'description_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-product-description',
            )
        );

        $this->add_control(
            'price_heading',
            array(
                'label'     => __('Price', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'price_color',
            array(
                'label'     => __('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-price' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'price_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-product-price',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Badge Style (NEW!)
     */
    private function register_badge_style()
    {
        $this->start_controls_section(
            'section_badge_style',
            array(
                'label'     => __('Badge', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_badge' => 'yes',
                ),
            )
        );

        $this->add_control(
            'badge_background',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#e74c3c',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-badge' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'badge_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-product-badge',
            )
        );

        $this->add_responsive_control(
            'badge_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px'),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Countdown Style (NEW!)
     */
    private function register_countdown_style()
    {
        $this->start_controls_section(
            'section_countdown_style',
            array(
                'label'     => __('Countdown', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_countdown' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'countdown_background',
                'types'    => array('classic', 'gradient'),
                'selector' => '{{WRAPPER}} .dailybuddy-countdown',
            )
        );

        $this->add_control(
            'countdown_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-countdown' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'countdown_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-countdown',
            )
        );

        $this->add_responsive_control(
            'countdown_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px'),
                'default'    => array(
                    'top'    => 12,
                    'right'  => 15,
                    'bottom' => 12,
                    'left'   => 15,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-countdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'countdown_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'default'    => array(
                    'top'    => 8,
                    'right'  => 8,
                    'bottom' => 8,
                    'left'   => 8,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-countdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register Button Style
     */
    private function register_button_style()
    {
        // Details Button Style
        $this->start_controls_section(
            'section_details_button_style',
            array(
                'label'     => __('Details Button', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_details_button' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'details_button_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-product-button.details-button',
            )
        );

        $this->start_controls_tabs('details_button_style_tabs');

        $this->start_controls_tab(
            'details_button_normal',
            array(
                'label' => __('Normal', 'dailybuddy'),
            )
        );

        $this->add_control(
            'details_button_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.details-button' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'details_button_background',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#2196F3',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.details-button' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'details_button_border',
                'selector' => '{{WRAPPER}} .dailybuddy-product-button.details-button',
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'details_button_hover',
            array(
                'label' => __('Hover', 'dailybuddy'),
            )
        );

        $this->add_control(
            'details_button_hover_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.details-button:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'details_button_hover_background',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#1976D2',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.details-button:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'details_button_hover_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.details-button:hover' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'details_button_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px'),
                'separator'  => 'before',
                'default'    => array(
                    'top'    => 12,
                    'right'  => 20,
                    'bottom' => 12,
                    'left'   => 20,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-button.details-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'details_button_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'default'    => array(
                    'top'    => 6,
                    'right'  => 6,
                    'bottom' => 6,
                    'left'   => 6,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-button.details-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Cart Button Style
        $this->start_controls_section(
            'section_cart_button_style',
            array(
                'label'     => __('Cart Button', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_cart_button' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'cart_button_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-product-button.cart-button',
            )
        );

        $this->start_controls_tabs('cart_button_style_tabs');

        $this->start_controls_tab(
            'cart_button_normal',
            array(
                'label' => __('Normal', 'dailybuddy'),
            )
        );

        $this->add_control(
            'cart_button_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.cart-button' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'cart_button_background',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#4CAF50',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.cart-button' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'cart_button_border',
                'selector' => '{{WRAPPER}} .dailybuddy-product-button.cart-button',
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'cart_button_hover',
            array(
                'label' => __('Hover', 'dailybuddy'),
            )
        );

        $this->add_control(
            'cart_button_hover_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.cart-button:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'cart_button_hover_background',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#45a049',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.cart-button:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'cart_button_hover_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-product-button.cart-button:hover' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'cart_button_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px'),
                'separator'  => 'before',
                'default'    => array(
                    'top'    => 12,
                    'right'  => 20,
                    'bottom' => 12,
                    'left'   => 20,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-button.cart-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'cart_button_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'default'    => array(
                    'top'    => 6,
                    'right'  => 6,
                    'bottom' => 6,
                    'left'   => 6,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-product-button.cart-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
?>

        <div class="dailybuddy-product-card layout-<?php echo esc_attr($settings['layout_style']); ?>">

            <!-- Product Image -->
            <div class="dailybuddy-product-image">
                <?php if (!empty($settings['product_image']['url'])): ?>
                    <img src="<?php echo esc_url($settings['product_image']['url']); ?>" alt="<?php echo esc_attr($settings['product_title']); ?>">
                <?php endif; ?>

                <!-- Badge (NEW!) -->
                <?php if ($settings['show_badge'] === 'yes'): ?>
                    <?php $this->render_badge($settings); ?>
                <?php endif; ?>

                <!-- Quick View Button (NEW!) -->
                <?php if ($settings['show_quick_view'] === 'yes'): ?>
                    <button class="dailybuddy-quick-view-btn" data-product-id="<?php echo esc_attr($id); ?>">
                        <?php Icons_Manager::render_icon($settings['quick_view_icon'], array('aria-hidden' => 'true')); ?>
                    </button>
                <?php endif; ?>

                <!-- Social Share (NEW!) -->
                <?php if ($settings['show_social_share'] === 'yes'): ?>
                    <?php $this->render_social_share($settings); ?>
                <?php endif; ?>

                <!-- Overlay Content (for overlay layout) -->
                <?php if ($settings['layout_style'] === 'overlay' || $settings['layout_style'] === 'content-on-hover'): ?>
                    <div class="dailybuddy-product-overlay">
                        <?php $this->render_buttons($settings); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Content -->
            <div class="dailybuddy-product-content">

                <!-- Countdown Timer (NEW!) -->
                <?php if ($settings['show_countdown'] === 'yes'): ?>
                    <?php $this->render_countdown($settings); ?>
                <?php endif; ?>

                <!-- Title -->
                <?php if (!empty($settings['product_title'])): ?>
                    <h3 class="dailybuddy-product-title">
                        <a href="<?php echo esc_url($settings['details_button_link']['url']); ?>">
                            <?php echo esc_html($settings['product_title']); ?>
                        </a>
                    </h3>
                <?php endif; ?>

                <!-- Rating -->
                <?php if ($settings['show_rating'] === 'yes'): ?>
                    <?php $this->render_rating($settings); ?>
                <?php endif; ?>

                <!-- Description -->
                <?php if (!empty($settings['product_description'])): ?>
                    <p class="dailybuddy-product-description"><?php echo wp_kses_post($settings['product_description']); ?></p>
                <?php endif; ?>

                <!-- Price -->
                <?php if ($settings['show_price'] === 'yes'): ?>
                    <div class="dailybuddy-product-price-wrap">
                        <?php if ($settings['show_old_price'] === 'yes' && !empty($settings['product_old_price'])): ?>
                            <span class="dailybuddy-product-old-price"><?php echo esc_html($settings['product_old_price']); ?></span>
                        <?php endif; ?>
                        <span class="dailybuddy-product-price"><?php echo esc_html($settings['product_price']); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Buttons (for default layout) -->
                <?php if ($settings['layout_style'] === 'default'): ?>
                    <div class="dailybuddy-product-buttons">
                        <?php $this->render_buttons($settings); ?>
                    </div>
                <?php endif; ?>

            </div>

        </div>

        <!-- Quick View Modal (NEW!) -->
        <?php if ($settings['show_quick_view'] === 'yes'): ?>
            <div class="dailybuddy-quick-view-modal" id="quick-view-<?php echo esc_attr($id); ?>">
                <div class="dailybuddy-quick-view-overlay"></div>
                <div class="dailybuddy-quick-view-content">
                    <button class="dailybuddy-quick-view-close">&times;</button>
                    <div class="dailybuddy-quick-view-inner">
                        <?php echo wp_kses_post($settings['quick_view_content']); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php
    }

    /**
     * Render Badge (NEW!)
     */
    private function render_badge($settings)
    {
        $badge_text = '';

        switch ($settings['badge_type']) {
            case 'sale':
                $badge_text = __('Sale', 'dailybuddy');
                break;
            case 'new':
                $badge_text = __('New', 'dailybuddy');
                break;
            case 'hot':
                $badge_text = __('Hot', 'dailybuddy');
                break;
            case 'custom':
                $badge_text = $settings['badge_custom_text'];
                break;
        }
    ?>
        <div class="dailybuddy-product-badge badge-<?php echo esc_attr($settings['badge_position']); ?> badge-type-<?php echo esc_attr($settings['badge_type']); ?>">
            <?php echo esc_html($badge_text); ?>
        </div>
    <?php
    }

    /**
     * Render Countdown (NEW!)
     */
    private function render_countdown($settings)
    {
        if (empty($settings['countdown_date'])) {
            return;
        }
    ?>
        <div class="dailybuddy-countdown" data-date="<?php echo esc_attr($settings['countdown_date']); ?>">
            <?php if (!empty($settings['countdown_label'])): ?>
                <span class="dailybuddy-countdown-label"><?php echo esc_html($settings['countdown_label']); ?></span>
            <?php endif; ?>
            <div class="dailybuddy-countdown-timer">
                <span class="dailybuddy-countdown-days"><span class="number">0</span><span class="label">d</span></span>:
                <span class="dailybuddy-countdown-hours"><span class="number">0</span><span class="label">h</span></span>:
                <span class="dailybuddy-countdown-minutes"><span class="number">0</span><span class="label">m</span></span>:
                <span class="dailybuddy-countdown-seconds"><span class="number">0</span><span class="label">s</span></span>
            </div>
        </div>
    <?php
    }

    /**
     * Render Social Share (NEW!)
     */
    private function render_social_share($settings)
    {
        // Get share URL - use custom URL if set, otherwise use current page URL
        $share_url = !empty($settings['share_url']['url']) ? $settings['share_url']['url'] : get_permalink();
        $page_title = get_the_title();

        if (empty($share_url)) {
            $share_url = home_url();
        }
    ?>
        <div class="dailybuddy-social-share">
            <?php if ($settings['social_facebook'] === 'yes'): ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($share_url); ?>" target="_blank" rel="noopener noreferrer" class="dailybuddy-social-btn facebook" aria-label="Share on Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
            <?php endif; ?>

            <?php if ($settings['social_twitter'] === 'yes'): ?>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($share_url); ?>&text=<?php echo urlencode($page_title); ?>" target="_blank" rel="noopener noreferrer" class="dailybuddy-social-btn twitter" aria-label="Share on Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
            <?php endif; ?>

            <?php if ($settings['social_pinterest'] === 'yes'): ?>
                <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode($share_url); ?>&description=<?php echo urlencode($page_title); ?>" target="_blank" rel="noopener noreferrer" class="dailybuddy-social-btn pinterest" aria-label="Share on Pinterest">
                    <i class="fab fa-pinterest-p"></i>
                </a>
            <?php endif; ?>

            <?php if ($settings['social_linkedin'] === 'yes'): ?>
                <a href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode($share_url); ?>&title=<?php echo urlencode($page_title); ?>" target="_blank" rel="noopener noreferrer" class="dailybuddy-social-btn linkedin" aria-label="Share on LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Render Rating
     */
    private function render_rating($settings)
    {
        $rating      = floatval($settings['product_rating']);
        $full_stars  = floor($rating);
        $half_star   = ($rating - $full_stars) >= 0.5;
        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
    ?>
        <div class="dailybuddy-product-rating">
            <?php for ($i = 0; $i < $full_stars; $i++): ?>
                <i class="fas fa-star"></i>
            <?php endfor; ?>
            <?php if ($half_star): ?>
                <i class="fas fa-star-half-alt"></i>
            <?php endif; ?>
            <?php for ($i = 0; $i < $empty_stars; $i++): ?>
                <i class="far fa-star"></i>
            <?php endfor; ?>
            <?php if ($settings['show_review_count'] === 'yes' && !empty($settings['review_count'])): ?>
                <span class="dailybuddy-review-count"><?php echo esc_html($settings['review_count']); ?></span>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Render Buttons
     */
    private function render_buttons($settings)
    {
    ?>
        <?php if ($settings['show_details_button'] === 'yes' && !empty($settings['details_button_text'])): ?>
            <a href="<?php echo esc_url($settings['details_button_link']['url']); ?>"
                class="dailybuddy-product-button details-button"
                <?php echo !empty($settings['details_button_link']['is_external']) ? 'target="_blank"' : ''; ?>
                <?php echo !empty($settings['details_button_link']['nofollow']) ? 'rel="nofollow"' : ''; ?>>
                <?php if ($settings['button_icon_position'] === 'left' && !empty($settings['details_button_icon']['value'])): ?>
                    <?php Icons_Manager::render_icon($settings['details_button_icon'], array('aria-hidden' => 'true')); ?>
                <?php endif; ?>
                <span><?php echo esc_html($settings['details_button_text']); ?></span>
                <?php if ($settings['button_icon_position'] === 'right' && !empty($settings['details_button_icon']['value'])): ?>
                    <?php Icons_Manager::render_icon($settings['details_button_icon'], array('aria-hidden' => 'true')); ?>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <?php if ($settings['show_cart_button'] === 'yes' && !empty($settings['cart_button_text'])): ?>
            <a href="<?php echo esc_url($settings['cart_button_link']['url']); ?>"
                class="dailybuddy-product-button cart-button"
                <?php echo !empty($settings['cart_button_link']['is_external']) ? 'target="_blank"' : ''; ?>
                <?php echo !empty($settings['cart_button_link']['nofollow']) ? 'rel="nofollow"' : ''; ?>>
                <?php if (!empty($settings['cart_button_icon']['value'])): ?>
                    <?php Icons_Manager::render_icon($settings['cart_button_icon'], array('aria-hidden' => 'true')); ?>
                <?php endif; ?>
                <span><?php echo esc_html($settings['cart_button_text']); ?></span>
            </a>
        <?php endif; ?>
<?php
    }
}
