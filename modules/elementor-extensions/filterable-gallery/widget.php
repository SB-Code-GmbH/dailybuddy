<?php

/**
 * dailybuddy Filterable Gallery Widget
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
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Utils;
use Elementor\Plugin;

class Dailybuddy_Elementor_Filterable_Gallery_Widget extends Widget_Base
{
    private $popup_status = false;
    private $default_control_key = 0;
    private $custom_default_control = false;

    public function get_name()
    {
        return 'dailybuddy-filterable-gallery';
    }

    public function get_title()
    {
        return esc_html__('Filterable Gallery', 'dailybuddy');
    }

    public function get_icon()
    {
        return 'eicon-gallery-grid mini-icon-dailybuddy';
    }

    public function get_categories()
    {
        return ['dailybuddy'];
    }

    public function get_style_depends()
    {
        return [
            'font-awesome-5-all',
            'font-awesome-4-shim',
        ];
    }

    public function get_script_depends()
    {
        return [
            'font-awesome-4-shim'
        ];
    }

    public function get_keywords()
    {
        return [
            'gallery',
            'ea filter gallery',
            'ea filterable gallery',
            'image gallery',
            'media gallery',
            'media',
            'photo gallery',
            'portfolio',
            'ea portfolio',
            'media grid',
            'responsive gallery',
            'photo gallery'
        ];
    }

    protected function is_dynamic_content(): bool
    {
        return false;
    }

    public function has_widget_inner_wrapper(): bool
    {
        return ! false;
    }

    public function get_custom_help_url()
    {
        return '#'; // No external documentation
    }

    protected function register_controls()
    {
        /**
         * Filter Gallery Settings
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_settings',
            [
                'label' => esc_html__('Settings', 'dailybuddy'),
            ]
        );

        $this->add_control(
            'dailybuddy_fg_caption_style',
            [
                'label'       => esc_html__('Layout', 'dailybuddy'),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'hoverer',
                'label_block' => true,
                'render_type' => 'template',  // Force widget re-render
                'options'     => [
                    'hoverer'  => esc_html__('Overlay', 'dailybuddy'),
                    'card'     => esc_html__('Card', 'dailybuddy'),
                    'layout_3' => esc_html__('Search and Filter', 'dailybuddy'),
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_items_to_show',
            [
                'label'       => esc_html__('Items to show', 'dailybuddy'),
                'type'        => Controls_Manager::NUMBER,
                'dynamic'     => ['active' => true],
                'label_block' => false,
                'default'     => 6,
                'ai'          => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_filter_duration',
            [
                'label'       => esc_html__('Animation Duration (ms)', 'dailybuddy'),
                'type'        => Controls_Manager::NUMBER,
                'label_block' => false,
                'default'     => 500,
                'ai'          => [
                    'active' => false,
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => ['hoverer', 'card', 'layout_3'],
                ],
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'   => __('Columns', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    '1' => [
                        'title' => '1',
                        'text' => '1',
                    ],
                    '2' => [
                        'title' => '2',
                        'text' => '2',
                    ],
                    '3' => [
                        'title' => '3',
                        'text' => '3',
                    ],
                    '4' => [
                        'title' => '4',
                        'text' => '4',
                    ],
                    '5' => [
                        'title' => '5',
                        'text' => '5',
                    ],
                    '6' => [
                        'title' => '6',
                        'text' => '6',
                    ],
                ],
                'default'        => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'toggle'         => false,
            ]
        );

        $this->add_control(
            'dailybuddy_fg_grid_style',
            [
                'label'   => esc_html__('Grid Style', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'grid' => [
                        'title' => esc_html__('Grid', 'dailybuddy'),
                        'icon' => 'eicon-gallery-grid',
                    ],
                    'masonry' => [
                        'title' => esc_html__('Masonry', 'dailybuddy'),
                        'icon' => 'eicon-gallery-masonry',
                    ],
                ],
                'default'   => 'grid',
                'toggle'    => false,
                'render_type' => 'template',  // Force widget re-render
                'condition' => [
                    'dailybuddy_fg_caption_style' => ['hoverer', 'card', 'layout_3'],
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_grid_item_height',
            [
                'label'     => esc_html__('Image Height', 'dailybuddy'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 300,
                'condition' => [
                    'dailybuddy_fg_grid_style' => 'grid',
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item .gallery-item-thumbnail-wrap, 
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-grid-fg-box .dailybuddy-grid-fg-img,
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-hg-grid__cell-img .grid__cell-img-inner' => 'height: {{VALUE}}px;',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_search_among_all',
            [
                'label'        => __('Search Full Gallery ?', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'condition'    => [
                    'dailybuddy_fg_caption_style' =>  'layout_3'
                ]
            ]
        );

        $this->add_control(
            'dailybuddy_search_among_note',
            [
                'label' => '',
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('Enabling this will load all prior items up to the one you searched for.', 'dailybuddy'),
                'content_classes' => 'dailybuddy-warning',
                'condition' => [
                    'dailybuddy_search_among_all' => 'yes',
                    'dailybuddy_fg_caption_style' =>  'layout_3'
                ]
            ]
        );

        $this->add_control(
            'dailybuddy_fg_not_found_text',
            [
                'label' => esc_html__('Not Found Text', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('No Items Found', 'dailybuddy'),
                'placeholder' => esc_html__('Not Found Text', 'dailybuddy'),
                'condition' => [
                    'dailybuddy_fg_caption_style' =>  'layout_3'
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'hover_style_heading',
            [
                'label'     => esc_html__('Hover', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'hoverer',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_grid_hover_style',
            [
                'label' => esc_html__('Style', 'dailybuddy'),
                'type' => Controls_Manager::SELECT,
                'default' => 'dailybuddy-slide-up',
                'options' => [
                    'dailybuddy-none' => esc_html__('None', 'dailybuddy'),
                    'dailybuddy-slide-up' => esc_html__('Slide In Up', 'dailybuddy'),
                    'dailybuddy-fade-in' => esc_html__('Fade In', 'dailybuddy'),
                    'dailybuddy-zoom-in' => esc_html__('Zoom In ', 'dailybuddy'),
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'hoverer',
                ],

            ]
        );
        $this->add_control(
            'dailybuddy_fg_grid_hover_transition',
            [
                'label' => esc_html__('Transition', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 500,
                ],
                'range' => [
                    'px' => [
                        'max' => 4000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap' => 'transition: {{SIZE}}ms;',
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'hoverer',
                    'dailybuddy_fg_grid_hover_style!' => 'dailybuddy-none',
                ],
                'separator' => 'after',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_show_popup',
            [
                'label'   => esc_html__('Link to', 'dailybuddy'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'none' => [
                        'title' => esc_html__('None', 'dailybuddy'),
                        'icon' => 'eicon-ban',
                    ],
                    'media' => [
                        'title' => esc_html__('Media', 'dailybuddy'),
                        'icon' => 'eicon-e-image',
                    ],
                    'buttons' => [
                        'title' => esc_html__('Buttons', 'dailybuddy'),
                        'icon' => 'eicon-button',
                    ],
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style!'    => ['grid_flow_gallery', 'harmonic_gallery'],
                    'dailybuddy_fg_caption_style'     => ['hoverer', 'card', 'layout_3'],
                ],
                'default'   => 'buttons',
                'toggle'    => false,
                'render_type' => 'template',  // Force re-render when Link to changes
            ]
        );

        $this->add_control(
            'dailybuddy_title_clickable',
            [
                'label'        => __('Title Clickable', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'condition'    => [
                    'dailybuddy_fg_caption_style' => ['hoverer', 'card', 'layout_3'],
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_section_fg_full_image_clickable',
            [
                'label'        => __('Image Clickable', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'condition'    => [
                    'dailybuddy_fg_caption_style' => ['hoverer', 'card', 'layout_3'],
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_section_fg_mfp_caption',
            [
                'label'        => __('Caption in Popup', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Show', 'dailybuddy'),
                'label_off'    => __('Hide', 'dailybuddy'),
                'return_value' => 'yes',
                'default'      => '',
                'condition'    => [
                    'dailybuddy_fg_caption_style' => ['hoverer', 'card', 'layout_3'],
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_section_fg_zoom_icon_new',
            [
                'label' => esc_html__('Lightbox Icon', 'dailybuddy'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'dailybuddy_section_fg_zoom_icon',
                'default' => [
                    'value' => 'fas fa-search-plus',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'dailybuddy_fg_show_popup' => 'buttons',
                    'dailybuddy_section_fg_full_image_clickable!' => 'yes',
                    'dailybuddy_fg_caption_style!'    => ['grid_flow_gallery', 'harmonic_gallery'],
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_section_fg_link_icon_new',
            [
                'label' => esc_html__('Link Icon', 'dailybuddy'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'dailybuddy_section_fg_link_icon',
                'default' => [
                    'value' => 'fas fa-link',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'dailybuddy_fg_show_popup' => 'buttons',
                    'dailybuddy_section_fg_full_image_clickable!' => 'yes',
                    'dailybuddy_fg_caption_style!'    => ['grid_flow_gallery', 'harmonic_gallery'],
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_section_fg_full_image_action',
            [
                'label' => esc_html__('Full Image Action', 'dailybuddy'),
                'type' => Controls_Manager::SELECT,
                'default' => 'lightbox',
                'options' => [
                    'lightbox' => esc_html__('Lightbox', 'dailybuddy'),
                    'link' => esc_html__('Link', 'dailybuddy'),
                ],
                'condition' => [
                    'dailybuddy_section_fg_full_image_clickable'    => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        /**
         * Filter Gallery Control Settings
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_control_settings',
            [
                'label' => esc_html__('Filterable Controls', 'dailybuddy'),
            ]
        );

        $this->add_control(
            'filter_enable',
            [
                'label' => __('Filter', 'dailybuddy'),
                'type' => Controls_Manager::SWITCHER,
                'label_on'     => __('Enable', 'dailybuddy'),
                'label_off'    => __('Disable', 'dailybuddy'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_all_label_text',
            [
                'label' => esc_html__('Gallery All Label', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'default' => esc_html__('All', 'dailybuddy'),
                'condition' => [
                    'filter_enable' => 'yes',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'fg_all_label_icon',
            [
                'label' => __('All label icon', 'dailybuddy'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-angle-down',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ]
            ]
        );


        $this->add_control(
            'title_tag',
            [
                'label' => __('Title Tag', 'dailybuddy'),
                'type' => Controls_Manager::CHOOSE,
                'label_block' => true,
                'default' => 'h2',
                'options' => [
                    'h1' => [
                        'title' => __('H1', 'dailybuddy'),
                        'text' => 'H1',
                    ],
                    'h2' => [
                        'title' => __('H2', 'dailybuddy'),
                        'text' => 'H2',
                    ],
                    'h3' => [
                        'title' => __('H3', 'dailybuddy'),
                        'text' => 'H3',
                    ],
                    'h4' => [
                        'title' => __('H4', 'dailybuddy'),
                        'text' => 'H4',
                    ],
                    'h5' => [
                        'title' => __('H5', 'dailybuddy'),
                        'text' => 'H5',
                    ],
                    'h6' => [
                        'title' => __('H6', 'dailybuddy'),
                        'text' => 'H6',
                    ],
                    'span' => [
                        'title' => __('SPAN', 'dailybuddy'),
                        'text' => 'SPAN',
                    ],
                    'p' => [
                        'title' => __('P', 'dailybuddy'),
                        'text' => 'P',
                    ],
                    'div' => [
                        'title' => __('DIV', 'dailybuddy'),
                        'text' => 'DIV',
                    ],
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_controls',
            [
                'type' => Controls_Manager::REPEATER,
                'default' => [
                    ['dailybuddy_fg_control' => 'Gallery Filter'],
                ],
                'fields' => [
                    [
                        'name' => 'dailybuddy_fg_control',
                        'label' => esc_html__('Filter Title', 'dailybuddy'),
                        'type' => Controls_Manager::TEXT,
                        'dynamic' => ['active' => true],
                        'label_block' => true,
                        'default' => esc_html__('Gallery Filter', 'dailybuddy'),
                        'ai' => [
                            'active' => false,
                        ],
                    ],
                    [
                        'name' => 'dailybuddy_fg_control_custom_id',
                        'label' => esc_html__('Custom ID', 'dailybuddy'),
                        'description' => esc_html__('Adding a custom ID will function as an anchor tag. For instance, if you input "test" as your custom ID, the link will change to "https://www.example.com/#test" and it will immediately open the corresponding tab.', 'dailybuddy'),
                        'type' => Controls_Manager::TEXT,
                        'dynamic' => ['active' => true],
                        'label_block' => true,
                        'default' => '',
                        'ai' => [
                            'active' => false,
                        ],
                    ],
                    [
                        'name' => 'dailybuddy_fg_custom_label',
                        'label' => __('Custom Label', 'dailybuddy'),
                        'type' => Controls_Manager::SWITCHER,
                        'dynamic' => ['active' => true],
                        'return' => 'yes',
                        'default' => '',
                    ],
                    [
                        'name' => 'dailybuddy_fg_control_label',
                        'label' => esc_html__('Item Label', 'dailybuddy'),
                        'type' => Controls_Manager::TEXT,
                        'dynamic' => ['active' => true],
                        'label_block' => true,
                        'condition' => [
                            'dailybuddy_fg_custom_label' => 'yes',
                        ],
                        'ai' => [
                            'active' => false,
                        ],
                    ],
                    [
                        'name' => 'dailybuddy_fg_control_active_as_default',
                        'label' => __('Active as Default', 'dailybuddy'),
                        'type' => Controls_Manager::SWITCHER,
                        'dynamic' => ['active' => true],
                        'return' => 'yes',
                        'default' => '',
                    ],
                ],
                'title_field' => '{{dailybuddy_fg_control}}',
            ]
        );

        $this->end_controls_section();

        /**
         * Filter Gallery Grid Settings
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_grid_settings',
            [
                'label' => esc_html__('Gallery Items', 'dailybuddy'),
            ]
        );

        // This is for the grid flow layout item animator control
        do_action('dailybuddy_grid_fg_item_animator_popover', $this);

        $this->add_control(
            'photo_gallery',
            [
                'label'              => __('Photo Gallery', 'dailybuddy'),
                'type'               => Controls_Manager::SWITCHER,
                'label_on'           => __('Enable', 'dailybuddy'),
                'label_off'          => __('Disable', 'dailybuddy'),
                'default'            => 'yes',
                'frontend_available' => true,
                'condition'          => [
                    'dailybuddy_fg_caption_style!' => ['grid_flow_gallery', 'harmonic_gallery']
                ],
            ]
        );

        // YouTube.
        $this->add_control(
            'video_gallery_yt_privacy',
            [
                'label'               => esc_html__('Video Privacy Mode', 'dailybuddy'),
                'type'                => Controls_Manager::SWITCHER,
                'description'         => esc_html__('If enabled, YouTube won\'t store information about visitors unless they play the video.', 'dailybuddy'),
                'frontend_available'  => true,
                'default'             => '',
                'condition'           => [
                    'dailybuddy_fg_caption_style!' => ['grid_flow_gallery', 'harmonic_gallery']
                ],
            ]
        );

        //Youtube video privacy notice
        $this->add_control(
            'dailybuddy_privacy_notice_control',
            [
                'label'        => esc_html__('Consent Notice', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Show', 'dailybuddy'),
                'label_off'    => __('Hide', 'dailybuddy'),
                'description'  => esc_html__('If enabled, The consent notice will appear before playing the video.', 'dailybuddy'),
                'default'      => '',
                'condition'    => [
                    'dailybuddy_fg_caption_style!' => ['grid_flow_gallery', 'harmonic_gallery']
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_privacy_notice',
            [
                'label'       => esc_html__('Privacy Notice', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'ai'          => ['active' => false,],
                'condition'   => ['dailybuddy_privacy_notice_control' => 'yes'],
                'condition'   => [
                    'dailybuddy_fg_caption_style!' => ['grid_flow_gallery', 'harmonic_gallery']
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_item_randomize',
            [
                'label'        => __('Randomize Item', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'description'  => __('Items will be displayed in a random order.', 'dailybuddy'),
                'condition'    => [
                    'dailybuddy_fg_caption_style!' => ['grid_flow_gallery', 'harmonic_gallery']
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'dailybuddy_fg_gallery_item_name',
            [
                'label'       => esc_html__('Gallery Item Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'label_block' => true,
                'default'     => esc_html__('Gallery item name', 'dailybuddy'),
                'ai'          => [
                    'active' => false,
                ],
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_control_name',
            [
                'label'       => esc_html__('Gallery Filter Title', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'label_block' => true,
                'default'     => '',
                'description' => __('Use the gallery filter title from Control Settings. Separate multiple items with comma (e.g. <strong>Gallery Filter, Gallery Filter 2</strong>)', 'dailybuddy'),
                'ai'          => [
                    'active' => false,
                ],
                'separator' => 'before',
            ]
        );

        // This is for the grid flow layout icon control
        do_action('dailybuddy_grid_flow_gallery_icon_control', $repeater);

        $repeater->add_control(
            'fg_video_gallery_heading',
            [
                'label'     => esc_html__('Video Gallery?', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'fg_video_gallery_switch',
            [
                'label'        => __('Enable', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'false',
                'return_value' => 'true',
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_video_gallery_alert',
            [
                'type'       => \Elementor\Controls_Manager::ALERT,
                'alert_type' => 'info',
                'heading'    => esc_html__('For Video Display', 'dailybuddy'),
                'content'    => esc_html__('Currently, videos are displayed in the Overlay, Card and Search and Filter layout', 'dailybuddy'),
                'condition'  => [
                    'fg_video_gallery_switch' => 'true',
                ]
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_item_video_link',
            [
                'label'       => esc_html__('Link', 'dailybuddy'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'https://www.youtube.com/watch?v = kB4U67tiQLA',
                'condition'   => [
                    'fg_video_gallery_switch' => 'true',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_video_layout',
            [
                'label' => esc_html__('Layout Type', 'dailybuddy'),
                'type'  => Controls_Manager::CHOOSE,
                'options' => [
                    'horizontal' => [
                        'title' => esc_html__('Horizontal', 'dailybuddy'),
                        'icon' => 'eicon-justify-space-around-v',
                    ],
                    'vertical' => [
                        'title' => esc_html__('Vertical', 'dailybuddy'),
                        'icon' => 'eicon-justify-space-around-h',
                    ],
                ],
                'condition' => [
                    'fg_video_gallery_switch' => 'true',
                ],
                'default'   => 'horizontal',
                'toggle'    => false,
            ]
        );

        $repeater->add_control(
            'fg_item_price_switch',
            [
                'label'        => __('Price', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'false',
                'label_on'     => esc_html__('Show', 'dailybuddy'),
                'label_off'    => esc_html__('Hide', 'dailybuddy'),
                'return_value' => 'true',
                'separator'    => 'before',
            ]
        );

        $repeater->add_control(
            'fg_item_price',
            [
                'label'     => esc_html__('Value', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'dynamic'   => ['active' => true],
                'default'   => esc_html__('$20.00', 'dailybuddy'),
                'separator' => 'after',
                'condition' => [
                    'fg_item_price_switch' => 'true'
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $repeater->add_control(
            'fg_item_ratings_switch',
            [
                'label'        => __('Ratings', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'false',
                'label_on'     => esc_html__('Show', 'dailybuddy'),
                'label_off'    => esc_html__('Hide', 'dailybuddy'),
                'return_value' => 'true'
            ]
        );

        $repeater->add_control(
            'fg_item_ratings',
            [
                'label' => esc_html__('Value', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => ['active' => true],
                'default' => esc_html__('5', 'dailybuddy'),
                'separator' => 'after',
                'condition' => [
                    'fg_item_ratings_switch' => 'true'
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $repeater->add_control(
            'fg_item_cat_switch',
            [
                'label'        => __('Category', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'false',
                'label_on'     => esc_html__('Show', 'dailybuddy'),
                'label_off'    => esc_html__('Hide', 'dailybuddy'),
                'return_value' => 'true'
            ]
        );

        $repeater->add_control(
            'fg_item_cat',
            [
                'label' => esc_html__('Name', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => ['active' => true],
                'default' => esc_html__('dailybuddy', 'dailybuddy'),
                'condition' => [
                    'fg_item_cat_switch' => 'true'
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $repeater->add_control(
            'filterable_item_notice',
            [
                'type'        => Controls_Manager::NOTICE,
                'notice_type' => 'info',
                'dismissible' => false,
                'content'     => sprintf(
                    // translators: %1$s and %2$s are HTML tags for bold text around "Search & Filter".
                    '<strong>%s</strong> %s',
                    esc_html__('Price, Ratings and Category', 'dailybuddy'),
                    // translators: %1$s and %2$s are HTML tags for bold text around "Search & Filter".
                    sprintf(esc_html__('will be visible only on the "%1$sSearch & Filter%2$s" layout.', 'dailybuddy'), '<strong>', '</strong>')
                ),
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_item_content',
            [
                'label' => esc_html__('Content', 'dailybuddy'),
                'type' => Controls_Manager::WYSIWYG,
                'label_block' => true,
                'separator' => 'before',
                'default' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem, provident.', 'dailybuddy'),
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_img',
            [
                'label' => esc_html__('Image', 'dailybuddy'),
                'type' => Controls_Manager::MEDIA,
                'dynamic' => ['active' => true],
                'default' => [
                    'url' => DAILYBUDDY_URL . 'assets/images/placeholder-image.png',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $repeater->add_control(
            'fg_video_gallery_play_icon',
            [
                'label' => __('Video play icon', 'dailybuddy'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => DAILYBUDDY_URL . 'assets/front-end/img/play-icon.png',
                ],
                'condition' => [
                    'fg_video_gallery_switch' => 'true',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_lightbox',
            [
                'label'        => __('Lightbox Button?', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'true',
                'label_on'     => esc_html__('Show', 'dailybuddy'),
                'label_off'    => esc_html__('Hide', 'dailybuddy'),
                'return_value' => 'true',
                'condition'    => [
                    'fg_video_gallery_switch!' => 'true',
                ],
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_link',
            [
                'label'        => __('Link Button?', 'dailybuddy'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'true',
                'label_on'     => esc_html__('Show', 'dailybuddy'),
                'label_off'    => esc_html__('Hide', 'dailybuddy'),
                'return_value' => 'true',
                'condition'    => [
                    'fg_video_gallery_switch!' => 'true',
                ],
            ]
        );

        $repeater->add_control(
            'dailybuddy_fg_gallery_img_link',
            [
                'type'        => Controls_Manager::URL,
                'dynamic'     => ['active'       => true],
                'label_block' => true,
                'default'     => [
                    'url'         => '#',
                    'is_external' => '',
                ],
                'show_external' => true,
                'condition'     => [
                    'fg_video_gallery_switch!' => 'true',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_gallery_items',
            [
                'type' => Controls_Manager::REPEATER,
                'seperator' => 'before',
                'default' => [
                    ['dailybuddy_fg_gallery_item_name' => 'Gallery Item Name'],
                    ['dailybuddy_fg_gallery_item_name' => 'Gallery Item Name'],
                    ['dailybuddy_fg_gallery_item_name' => 'Gallery Item Name'],
                    ['dailybuddy_fg_gallery_item_name' => 'Gallery Item Name'],
                    ['dailybuddy_fg_gallery_item_name' => 'Gallery Item Name'],
                    ['dailybuddy_fg_gallery_item_name' => 'Gallery Item Name'],
                ],
                'fields' => $repeater->get_controls(),
                'title_field' => '{{dailybuddy_fg_gallery_item_name}}',
            ]
        );

        $this->end_controls_section();

        /**
         * Content Tab: Gallery Load More Button
         */
        $this->start_controls_section(
            'section_pagination',
            [
                'label' => __('Load More Button', 'dailybuddy'),
            ]
        );

        $this->add_control(
            'pagination',
            [
                'label' => __('Load More Button', 'dailybuddy'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'false',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'images_per_page',
            [
                'label' => __('Images Per Page', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'dynamic'   => ['active' => true],
                'default' => 6,
                'condition' => [
                    'pagination' => 'yes',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'load_more_text',
            [
                'label' => __('Button Label', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'dynamic'   => ['active' => true],
                'default' => __('Load More', 'dailybuddy'),
                'condition' => [
                    'pagination' => 'yes',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'nomore_items_text',
            [
                'label' => __('No More Items Text', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'dynamic'   => ['active' => true],
                'default' => __('No more items!', 'dailybuddy'),
                'condition' => [
                    'pagination' => 'yes',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'button_size',
            [
                'label' => __('Size', 'dailybuddy'),
                'type' => Controls_Manager::SELECT,
                'default' => 'sm',
                'options' => [
                    'xs' => __('Extra Small', 'dailybuddy'),
                    'sm' => __('Small', 'dailybuddy'),
                    'md' => __('Medium', 'dailybuddy'),
                    'lg' => __('Large', 'dailybuddy'),
                    'xl' => __('Extra Large', 'dailybuddy'),
                ],
                'condition' => [
                    'pagination' => 'yes',
                    'load_more_text!' => '',
                ],
            ]
        );

        $this->add_control(
            'load_more_icon_new',
            [
                'label' => __('Button Icon', 'dailybuddy'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'load_more_icon',
                'condition' => [
                    'pagination' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_icon_position',
            [
                'label' => __('Icon Position', 'dailybuddy'),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'after',
                'options' => [
                    'before' => [
                        'title' => __('Before', 'dailybuddy'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'after' => [
                        'title' => __('After', 'dailybuddy'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'condition' => [
                    'pagination' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'load_more_align',
            [
                'label' => __('Alignment', 'dailybuddy'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'dailybuddy'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'dailybuddy'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'dailybuddy'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-loadmore' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    'pagination' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Tab Style (Filterable Gallery Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_style_settings',
            [
                'label' => esc_html__('General', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'dailybuddy_fg_bg_color',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-wrapper' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_container_padding',
            [
                'label' => esc_html__('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_container_margin',
            [
                'label' => esc_html__('Margin', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-wrapper',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_border_radius',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-wrapper' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dailybuddy_fg_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-wrapper',
            ]
        );

        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Tab Style (Filterable Gallery Control Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_control_style_settings',
            [
                'label' => esc_html__('Control', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style!' => 'layout_3'
                ]
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_bar',
            [
                'label'     => esc_html__('Bar', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'after',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_bar_bg_color',
            [
                'label'     => esc_html__('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_control_bar_margin',
            [
                'label'      => esc_html__('Margin', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_control_bar_padding',
            [
                'label'      => esc_html__('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'dailybuddy_fg_control_bar_border',
                'label'    => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-control ul',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_bar_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'rem', 'em', 'custom'],
                'selectors'  => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_bar_button',
            [
                'label'     => esc_html__('Buttons', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'after',
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_control_padding',
            [
                'label' => esc_html__('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_control_margin',
            [
                'label' => esc_html__('Margin', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'dailybuddy_fg_control_typography',
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control',
            ]
        );
        // Tabs
        $this->start_controls_tabs('dailybuddy_fg_control_tabs');

        // Normal State Tab
        $this->start_controls_tab('dailybuddy_fg_control_normal', ['label' => esc_html__('Normal', 'dailybuddy')]);

        $this->add_control(
            'dailybuddy_fg_control_normal_text_color',
            [
                'label' => esc_html__('Text Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#444',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_normal_bg_color',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_control_normal_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-control ul > li.control',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_normal_border_radius',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul > li.control' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dailybuddy_fg_control_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control',
            ]
        );

        $this->end_controls_tab();

        // Active State Tab
        $this->start_controls_tab('dailybuddy_cta_btn_hover', ['label' => esc_html__('Active', 'dailybuddy')]);

        $this->add_control(
            'dailybuddy_fg_control_active_text_color',
            [
                'label' => esc_html__('Text Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_active_bg_color',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control.active' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_control_active_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-control ul > li.control.active',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_control_active_border_radius',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control.active' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dailybuddy_fg_control_active_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-filter-gallery-control ul li.control.active',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Tab Style (Filterable Gallery Item Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_item_style_settings',
            [
                'label' => esc_html__('Item', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style!' => ['harmonic_gallery'],
                ]
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_container_bg_color',
            [
                'label'     => esc_html__('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item,
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-grid-fg-box' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_container_padding',
            [
                'label' => esc_html__('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item,
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-grid-fg-box,
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-hg-grid__cell-img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_container_margin',
            [
                'label' => esc_html__('Margin', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item,
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-grid-fg-box,
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-hg-grid__cell-img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_item_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item, 
                {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-grid-fg-box,
                {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-hg-grid__cell-img',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item, 
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-grid-fg-box,
                    {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-hg-grid__cell-img' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dailybuddy_fg_item_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item, 
                {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-grid-fg-box,
                {{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-hg-grid__cell-img',
            ]
        );
        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Tab Style (Filterable Gallery Hoverer Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_item_cap_style_settings',
            [
                'label' => esc_html__('Mouseover Effect', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => ['hoverer']
                ],
            ]
        );

        $this->add_control('dailybuddy_section_fg_item_card_hover_note_hoverer', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => __('These controls will be in effect when the mouse hovers over the items.', 'dailybuddy'),
            'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
        ]);

        $this->add_control(
            'dailybuddy_fg_item_cap_bg_color',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0,0,0,0.7)',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-hoverer-bg' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_cap_container_padding',
            [
                'label' => esc_html__('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_hover_title_typography_heading',
            [
                'label' => esc_html__('Title Typography', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_hover_title_color',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer .fg-item-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_hover_title_hover_color',
            [
                'label' => esc_html__('Hover Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer .fg-item-title:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'dailybuddy_fg_item_hover_title_typography',
                'selector' => '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer .fg-item-title',
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_title_margin',
            [
                'label'      => esc_html__('Margin', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer .fg-item-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_hover_content_typography_heading',
            [
                'label' => esc_html__('Content Typography', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_hover_content_color',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer .fg-item-content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'dailybuddy_fg_item_hover_content_typography',
                'selector' => '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer .fg-item-content',
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_content_margin',
            [
                'label'      => esc_html__('Margin', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer .fg-item-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_item_cap_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-hoverer',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dailybuddy_fg_item_cap_shadow',
                'selector' => '{{WRAPPER}} .gallery-item-thumbnail-wrap .gallery-item-caption-wrap',
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_hoverer_content_alignment',
            [
                'label' => esc_html__('Content Alignment', 'dailybuddy'),
                'type' => Controls_Manager::CHOOSE,
                'label_block' => true,
                'separator' => 'before',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'dailybuddy'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'dailybuddy'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'dailybuddy'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'prefix_class' => 'dailybuddy-fg-hoverer-content-align-',
            ]
        );

        $this->end_controls_section();

        #only for layout 3
        $this->start_controls_section(
            'fg_item_thumb_style',
            [
                'label' => esc_html__('Thumbnail', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'fg_item_thubm_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .fg-layout-3-item-thumb',
            ]
        );

        $this->add_responsive_control(
            'fg_item_thubm_border_radius',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-item-thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .fg-layout-3-item .gallery-item-caption-wrap.card-hover-bg.caption-style-hoverer'  => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Tab Style (Filterable Gallery card Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_item_card_hover_style',
            [
                'label' => esc_html__('Mouseover Effect', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => ['card', 'layout_3']
                ],
            ]
        );

        $this->add_control('dailybuddy_section_fg_item_card_hover_note_card', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => __('These controls will be in effect when the mouse hovers over the items.', 'dailybuddy'),
            'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
        ]);

        $this->add_control(
            'dailybuddy_fg_item_card_hover_bg_color',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0,0,0,0.7)',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.card-hover-bg' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Tab Style (Video item Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_video_item_style',
            [
                'label' => esc_html__('Video', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style!' => 'layout_3',
                    'dailybuddy_fg_caption_style!' => ['grid_flow_gallery', 'harmonic_gallery'],
                ]
            ]
        );

        $this->add_control(
            'dailybuddy_section_fg_video_item_mouseover_effect_heading',
            [
                'label' => esc_html__('Mouseover Effects', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'dailybuddy_fg_video_item_hover_bg',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0, 0, 0, .7)',
                'selectors' => [
                    '{{WRAPPER}} .video-popup-bg' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_video_item_hover_bg_trans',
            [
                'label' => esc_html__('Background transition', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
                    'px' => 350,
                ],
                'range' => [
                    'px' => [
                        'max' => 4000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .video-popup-bg' => 'transition: {{SIZE}}ms;',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_video_item_hover_icon_size',
            [
                'label' => esc_html__('Icon size', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'default' => [
                    'px' => 62,
                ],
                'range' => [
                    'px' => [
                        'max' => 150,
                    ],
                    'em' => [
                        'max' => 150,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .video-popup > img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_video_item_icon_hover_scale',
            [
                'label' => esc_html__('Hover icon scale', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'default' => '1.1',
                'selectors' => [
                    '{{WRAPPER}} .video-popup:hover > img' => 'transform: scale({{VALUE}});',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_video_item_icon_hover_scale_transition',
            [
                'label' => esc_html__('Icon transition', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
                    'px' => 350,
                ],
                'range' => [
                    'px' => [
                        'max' => 4000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .video-popup > img' => 'transition: {{SIZE}}ms;',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_section_fg_lightbox_custom_width',
            [
                'label'     => __('Custom Width', 'dailybuddy'),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __('Yes', 'dailybuddy'),
                'label_off' => __('No', 'dailybuddy'),
                'return_value' => 'yes',
                'default'   => '',
                'separator' => 'before',
                'frontend_available' => true,
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_section_fg_lightbox_video_width',
            [
                'label' => esc_html__('Video Content Width', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'widescreen_default' => [
                    'unit' => '%',
                ],
                'laptop_default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'tablet_default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'tablet_extra_default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'mobile_default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'mobile_extra_default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'range' => [
                    '%' => [
                        'min' => 30,
                    ],
                ],
                'devices' => ['widescreen', 'desktop', 'laptop', 'tablet', 'tablet_extra', 'mobile', 'mobile_extra'],
                'selectors' => [
                    '.mfp-container.mfp-iframe-holder .mfp-content' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'dailybuddy_section_fg_lightbox_custom_width' => 'yes',
                ]
            ]
        );

        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Grid Flow Gallery
         * -------------------------------------------
         */
        do_action('dailybuddy_grid_flow_gallery_style', $this);

        /**
         * -------------------------------------------
         * Harmonic Gallery Style
         * -------------------------------------------
         */
        do_action('dailybuddy_harmonic_gallery_style', $this);

        /**
         * -------------------------------------------
         * Tab Style (Card Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_item_content_style_settings',
            [
                'label' => esc_html__('Item Card', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => ['card', 'layout_3']
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_bg_color',
            [
                'label'     => esc_html__('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#f1f2f9',
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .gallery-item-caption-wrap.caption-style-card' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .fg-layout-3-item-content' => 'background-color: {{VALUE}};'
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'card'
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_layout_3_content_bg_color',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-item-content' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ]
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_content_container_padding',
            [
                'label' => esc_html__('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .gallery-item-caption-wrap.caption-style-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .fg-layout-3-item-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_item_content_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .gallery-item-caption-wrap.caption-style-card, {{WRAPPER}} .fg-layout-3-item-content',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dailybuddy_fg_item_content_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .gallery-item-caption-wrap.caption-style-card, {{WRAPPER}} .fg-layout-3-item-content',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_iamge_settings',
            [
                'label'     => esc_html__('Image', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'after',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'dailybuddy_fg_item_content_image_border',
                'label'    => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item .gallery-item-thumbnail-wrap>img',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_image_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-filterable-gallery-item-wrap .dailybuddy-gallery-grid-item .gallery-item-thumbnail-wrap>img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_title_typography_settings',
            [
                'label' => esc_html__('Title Typography', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_title_color',
            [
                'label'     => esc_html__('Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#F56A6A',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-card .fg-item-title' => 'color: {{VALUE}};'
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => ['card']
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_layout_3_content_title_color',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#031d3c',
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-item-content .fg-item-title' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_title_hover_color',
            [
                'label' => esc_html__('Hover Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-card .fg-item-title:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .fg-layout-3-item-content .fg-item-title:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'dailybuddy_fg_item_content_title_typography',
                'selector' => '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-card .fg-item-title, 
                {{WRAPPER}} .fg-layout-3-item-content .fg-item-title',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_text_typography_settings',
            [
                'label' => esc_html__('Content Typography', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_content_text_color',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#444',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-card .fg-item-content' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'card'
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_layout_3_content_text_color',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#7f8995',
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-item-content .fg-item-content p' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'dailybuddy_fg_item_content_text_typography',
                'selector' => '{{WRAPPER}} .gallery-item-caption-wrap.caption-style-card .fg-item-content, {{WRAPPER}} .fg-layout-3-item-content .fg-item-content p',
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_content_alignment',
            [
                'label' => esc_html__('Content Alignment', 'dailybuddy'),
                'type' => Controls_Manager::CHOOSE,
                'label_block' => true,
                'separator' => 'before',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'dailybuddy'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'dailybuddy'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'dailybuddy'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'prefix_class' => 'dailybuddy-fg-card-content-align-',
            ]
        );

        $this->end_controls_section();

        /**
         * -------------------------------------------
         * Tab Style (Hoverer Icon Style)
         * -------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_section_fg_item_hover_icons_style',
            [
                'label' => esc_html__('Icons', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style!' => ['grid_flow_gallery', 'harmonic_gallery'],
                ]
            ]
        );

        $this->start_controls_tabs('fg_icons_style');

        $this->start_controls_tab(
            'fg_icons_style_normal',
            [
                'label'        => __('Normal', 'dailybuddy')
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_bg_color',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ff622a',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_color',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_icon_padding',
            [
                'label' => esc_html__('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_fg_item_icon_margin',
            [
                'label' => esc_html__('Margin', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_exact_size',
            [
                'label' => esc_html__('Icon Size', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 120,
                    ],
                    'em' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_size',
            [
                'label' => esc_html__('Icon Font Size', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                    'em' => [
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 18,
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span img' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );



        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_item_icon_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_border_radius',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 100,
                ],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'fg_icons_style_hover',
            [
                'label'        => __('Hover', 'dailybuddy')
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_bg_color_hover',
            [
                'label' => esc_html__('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ff622a',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span:hover' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_color_hover',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_fg_item_icon_border_hover',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span:hover',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_border_radius_hover',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 100,
                ],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span:hover' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_control(
            'dailybuddy_fg_item_icon_transition',
            [
                'label' => esc_html__('Transition', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 300,
                ],
                'range' => [
                    'px' => [
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-caption-wrap .gallery-item-buttons > a span' => 'transition: {{SIZE}}ms;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        $this->end_controls_section();

        $this->start_controls_section(
            'fg_item_price_style',
            [
                'label' => esc_html__('Price', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ]
            ]
        );

        $this->add_control(
            'fg_item_price_color',
            [
                'label' => __('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-caption-head .fg-item-price' => 'color: {{VALUE}}',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'fg_item_price_typography',
                'label' => __('Typography', 'dailybuddy'),
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .fg-caption-head .fg-item-price'
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'fg_item_ratings_style',
            [
                'label' => esc_html__('Ratings', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ]
            ]
        );

        $this->add_control(
            'fg_item_ratings_color',
            [
                'label' => __('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-caption-head .fg-item-ratings' => 'color: {{VALUE}}',
                ]
            ]
        );

        $this->add_control(
            'fg_item_ratings_star_color',
            [
                'label' => __('Star Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-caption-head .fg-item-ratings i' => 'color: {{VALUE}}',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'fg_item_ratings_typography',
                'label' => __('Typography', 'dailybuddy'),
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .fg-caption-head .fg-item-ratings'
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'fg_item_category_style',
            [
                'label' => esc_html__('Category', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ]
            ]
        );

        $this->add_control(
            'fg_item_category_color',
            [
                'label' => __('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-item-category span' => 'color: {{VALUE}}',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'fg_item_category_typography',
                'label' => __('Typography', 'dailybuddy'),
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .fg-item-category span'
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'fg_item_category_background',
                'label'     => __('Background', 'dailybuddy'),
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}} .fg-item-category span',
            ]
        );

        $this->add_responsive_control(
            'fg_item_category_border_radius',
            [
                'label' => __('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fg-item-category span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'fg_search_form_style',
            [
                'label' => esc_html__('Search Form', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dailybuddy_fg_caption_style' => 'layout_3'
                ]
            ]
        );

        $this->add_control(
            'fg_sf_controls',
            [
                'label' => esc_html__('Controls', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'fg_sf_controls_typography',
                'label' => __('Typography', 'dailybuddy'),
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .fg-filter-trigger > span'
            ]
        );

        $this->add_responsive_control(
            'fg_sf_controls_icon_space',
            [
                'label' => esc_html__('Icon Space', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-trigger > i' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .fg-filter-trigger img' => 'margin-left: {{SIZE}}{{UNIT}};',
                ]
            ]
        );


        $this->add_responsive_control(
            'fg_sf_controls_icon_size',
            [
                'label' => esc_html__('Icon Size', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 14,
                ],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-trigger > i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .fg-filter-trigger img' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                ]
            ]
        );

        $this->add_responsive_control(
            'fg_sf_controls_width',
            [
                'label' => esc_html__('Width', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                    '%' => [
                        'max'   => 100
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-wrap' => 'flex-basis: {{SIZE}}{{UNIT}};',
                ]
            ]
        );

        $this->add_control(
            'fg_sf_controls_color',
            [
                'label' => __('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default'   => '#7f8995',
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-wrap button' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'fg_sf_controls_background',
            [
                'label' => __('Controls Background', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-wrap button' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'fg_sf_controls_border_radius',
            [
                'label' => __('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-wrap button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );

        $this->add_responsive_control(
            'fg_sf_controls_margin',
            [
                'label' => __('Margin', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'fg_sf_controls_box_shadow',
                'selector' => '{{WRAPPER}} .fg-filter-wrap button'
            ]
        );

        $this->add_control(
            'fg_sf_separator',
            [
                'label' => esc_html__('Separator', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'sf_left_border_size',
            [
                'label' => esc_html__('Separator Size', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-wrap button' => 'border-right: {{SIZE}}px solid;',
                ]
            ]
        );

        $this->add_control(
            'sf_left_border_color',
            [
                'label' => __('Separator Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default'   => '#abb5ff',
                'selectors' => [
                    '{{WRAPPER}} .fg-filter-wrap button' => 'border-color: {{VALUE}}',
                ]
            ]
        );

        $this->add_control(
            'fg_sf',
            [
                'label' => esc_html__('Form', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'fg_sf_background',
            [
                'label' => __('Background', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-filters-wrap .fg-layout-3-search-box' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'fg_sf_placeholder',
            [
                'label' => esc_html__('Placeholder', 'dailybuddy'),
                'type' => Controls_Manager::TEXT,
                'default'   => __('Search Gallery Item...', 'dailybuddy'),
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'fg_sf_placeholder_color',
            [
                'label' => __('Placeholder Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#858e9a',
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-search-box input[type="text"]::-webkit-input-placeholder' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .fg-layout-3-search-box input[type="text"]::-moz-placeholder'  => 'color: {{VALUE}}',
                    '{{WRAPPER}} .fg-layout-3-search-box input[type="text"]:-ms-input-placeholder' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .fg-layout-3-search-box input[type="text"]:-moz-placeholder'   => 'color: {{VALUE}}',
                    '{{WRAPPER}} .fg-layout-3-search-box input'   => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'fg_sf_form_width',
            [
                'label' => esc_html__('Width', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                    '%' => [
                        'max'   => 100
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-search-box' => 'flex-basis: {{SIZE}}{{UNIT}};',
                ]
            ]
        );

        $this->add_responsive_control(
            'fg_sf_form_border_radius',
            [
                'label' => __('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-filters-wrap .fg-layout-3-search-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'fg_sf_form_box_shadow',
                'selector' => '{{WRAPPER}} .fg-layout-3-filters-wrap .fg-layout-3-search-box'
            ]
        );

        $this->add_control(
            'fg_sf_dropdown',
            [
                'label' => esc_html__('Dropdown', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'fg_sf_dropdown_color',
            [
                'label' => __('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-filter-controls li.control' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'fg_sf_dropdown_hover_color',
            [
                'label' => __('Hover Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-filter-controls li.control:hover' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'    => 'fg_sf_dropdown_bg',
                'types'   => ['classic', 'gradient'],
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
                'exclude' => [
                    'image',
                ],
                'selector' => '{{WRAPPER}} .fg-layout-3-filter-controls',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'fg_sf_dropdown_typography',
                'label' => __('Typography', 'dailybuddy'),
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .fg-layout-3-filter-controls li.control'
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'fg_sf_dropdown_border',
                'label' => __('Border', 'dailybuddy'),
                'placeholder' => '1px',
                'selector' => '{{WRAPPER}} .fg-layout-3-filter-controls li.control'
            ]
        );

        $this->add_responsive_control(
            'fg_sf_dropdown_spacing',
            [
                'label' => __('Spacing', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-filter-controls li.control' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );


        $this->add_responsive_control(
            'fg_sf_dropdown_border_radius',
            [
                'label' => __('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fg-layout-3-filter-controls.open-filters' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );

        $this->end_controls_section();

        /**
         * Style Tab: Not found text
         * -------------------------------------------------
         */
        $this->start_controls_section(
            'dailybuddy_not_found_text_style',
            [
                'label' => esc_html__('Not Found Text', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'dailybuddy_fg_not_found_text_typography',
                'selector' => '{{WRAPPER}} #dailybuddy-fg-no-items-found',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_not_found_text_align',
            [
                'label' => esc_html__('Alignment', 'dailybuddy'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'dailybuddy'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'dailybuddy'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'dailybuddy'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} #dailybuddy-fg-no-items-found' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'dailybuddy_fg_not_found_text_bg_color',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} #dailybuddy-fg-no-items-found',
            ]
        );

        $this->add_control(
            'dailybuddy_fg_not_found_text_color',
            [
                'label' => esc_html__('Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#858e9a',
                'selectors' => [
                    '{{WRAPPER}} #dailybuddy-fg-no-items-found' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_not_found_text_padding',
            [
                'label' => esc_html__('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} #dailybuddy-fg-no-items-found' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dailybuddy_not_found_text_margin',
            [
                'label' => esc_html__('Margin', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} #dailybuddy-fg-no-items-found' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dailybuddy_not_found_text_border',
                'label' => esc_html__('Border', 'dailybuddy'),
                'selector' => '{{WRAPPER}} #dailybuddy-fg-no-items-found',
            ]
        );

        $this->add_control(
            'dailybuddy_not_found_text_border_radius',
            [
                'label' => esc_html__('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} #dailybuddy-fg-no-items-found' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dailybuddy_not_found_text_shadow',
                'selector' => '{{WRAPPER}} #dailybuddy-fg-no-items-found',
            ]
        );

        $this->end_controls_section();

        /**
         * Style Tab: Load More Button
         * -------------------------------------------------
         */
        $this->start_controls_section(
            'section_loadmore_button_style',
            [
                'label' => __('Load More Button', 'dailybuddy'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'pagination' => 'yes',
                    'load_more_text!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin_top',
            [
                'label' => __('Top Spacing', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                        'step' => 1,
                    ],
                ],
                'size_units' => '',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'load_more_button_icon_heading',
            [
                'label' => __('Button Icon', 'dailybuddy'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'load_more_button_icon_size',
            [
                'label' => __('Size', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 15,
                ],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 500,
                        'step' => 1,
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more .dailybuddy-filterable-gallery-load-more-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-gallery-load-more img.dailybuddy-filterable-gallery-load-more-icon' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};'
                ]
            ]
        );

        $this->add_control(
            'load_more_button_icon_spacing',
            [
                'label' => __('Spacing', 'dailybuddy'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more .fg-load-more-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dailybuddy-gallery-load-more .fg-load-more-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
                'separator' => 'after',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'load_more_button_typography',
                'label' => __('Typography', 'dailybuddy'),
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .dailybuddy-gallery-load-more .dailybuddy-filterable-gallery-load-more-text',
                'condition' => [
                    'pagination' => 'yes',
                    'load_more_text!' => '',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'load_more_button_border_normal',
                'label' => __('Border', 'dailybuddy'),
                'placeholder' => '1px',
                'default' => '1px',
                'selector' => '{{WRAPPER}} .dailybuddy-gallery-load-more',
                // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
                'exclude'  => ['color'],
            ]
        );

        $this->add_control(
            'load_more_button_border_radius',
            [
                'label' => __('Border Radius', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'load_more_button_padding',
            [
                'label' => __('Padding', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'load_more_button_icon_margin',
            [
                'label' => __('Margin', 'dailybuddy'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'placeholder' => [
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more .dailybuddy-filterable-gallery-load-more-icon' => 'margin-top: {{TOP}}{{UNIT}}; margin-left: {{LEFT}}{{UNIT}}; margin-right: {{RIGHT}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_dailybuddy_load_more_button_style');

        $this->start_controls_tab(
            'tab_load_more_button_normal',
            [
                'label' => __('Normal', 'dailybuddy'),
            ]
        );

        $this->add_control(
            'load_more_button_bg_color_normal',
            [
                'label' => __('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'load_more_button_text_color_normal',
            [
                'label' => __('Text Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'load_more_button_border_normal_color',
            [
                'label' => __('Border Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'load_more_button_box_shadow',
                'selector' => '{{WRAPPER}} .dailybuddy-gallery-load-more',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => __('Hover', 'dailybuddy'),
            ]
        );

        $this->add_control(
            'button_bg_color_hover',
            [
                'label' => __('Background Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => __('Text Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_border_color_hover',
            [
                'label' => __('Border Color', 'dailybuddy'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .dailybuddy-gallery-load-more:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .dailybuddy-gallery-load-more:hover',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    public function sorter_class($string)
    {
        $sorter_class = strtolower($string);
        $sorter_class = str_replace(' ', '-', $sorter_class);
        $sorter_class = str_replace(',-', ' dailybuddy-cf-', $sorter_class);
        $sorter_class = str_replace(',', 'comma', $sorter_class);
        $sorter_class = str_replace('&', 'and', $sorter_class);
        $sorter_class = str_replace('+', 'plus', $sorter_class);
        $sorter_class = str_replace('amp;', '', $sorter_class);
        $sorter_class = str_replace('/', 'slash', $sorter_class);
        $sorter_class = str_replace("'", 'apostrophe', $sorter_class);
        $sorter_class = str_replace('"', 'apostrophe', $sorter_class);
        $sorter_class = str_replace('.', '-', $sorter_class);
        $sorter_class = str_replace('~', 'tilde', $sorter_class);
        $sorter_class = str_replace('!', 'exclamation', $sorter_class);
        $sorter_class = str_replace('@', 'at', $sorter_class);
        $sorter_class = str_replace('#', 'hash', $sorter_class);
        $sorter_class = str_replace('(', 'parenthesis', $sorter_class);
        $sorter_class = str_replace(')', 'parenthesis', $sorter_class);
        $sorter_class = str_replace('=', 'equal', $sorter_class);
        $sorter_class = str_replace(';', 'semicolon', $sorter_class);
        $sorter_class = str_replace(':', 'colon', $sorter_class);
        $sorter_class = str_replace('<', 'lessthan', $sorter_class);
        $sorter_class = str_replace('>', 'greaterthan', $sorter_class);
        $sorter_class = str_replace('|', 'pipe', $sorter_class);
        $sorter_class = str_replace('\\', 'backslash', $sorter_class);
        $sorter_class = str_replace('^', 'caret', $sorter_class);
        $sorter_class = str_replace('*', 'asterisk', $sorter_class);
        $sorter_class = str_replace('$', 'dollar', $sorter_class);
        $sorter_class = str_replace('%', 'percent', $sorter_class);
        $sorter_class = str_replace('`', 'backtick', $sorter_class);
        $sorter_class = str_replace('[', 'bracket', $sorter_class);
        $sorter_class = str_replace(']', 'bracket', $sorter_class);
        $sorter_class = str_replace('{', 'curlybracket', $sorter_class);
        $sorter_class = str_replace('}', 'curlybracket', $sorter_class);
        $sorter_class = str_replace('?', 'questionmark', $sorter_class);

        $sorter_class = mb_convert_encoding($sorter_class, 'UTF-8');

        return $sorter_class;
    }

    protected function render_filters()
    {
        $settings = $this->get_settings_for_display();
        $all_text = ($settings['dailybuddy_fg_all_label_text'] != '') ? $settings['dailybuddy_fg_all_label_text'] : esc_html__('All', 'dailybuddy');

        if ($settings['filter_enable'] == 'yes') {
?>
            <div class="dailybuddy-filter-gallery-control">
                <ul><?php
                    if ($settings['dailybuddy_fg_all_label_text']) {
                    ?><li data-load-more-status="0" data-first-init="1" class="control all-control <?php if (! $this->custom_default_control) : ?> active <?php endif; ?>" data-filter="*"><?php echo wp_kses($all_text, wp_kses_allowed_html("post")); ?></li><?php
                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                            foreach ($settings['dailybuddy_fg_controls'] as $key => $control) :
                                                                                                                                                                                                                                                                $sorter_filter = $this->sorter_class($control['dailybuddy_fg_control']);
                                                                                                                                                                                                                                                                $sorter_label  = $control['dailybuddy_fg_control_label'] != '' ? $control['dailybuddy_fg_control_label'] : $control['dailybuddy_fg_control'];
                                                                                                                                                                                                                                                                $custom_id = sanitize_text_field($control['dailybuddy_fg_control_custom_id']) ?? "";

                                                                                                                                                                                                                                                                ?><li <?php if ($custom_id) : ?> id="<?php echo esc_attr($custom_id); ?>" <?php endif; ?> data-load-more-status="0" data-first-init="0"
                            class="control <?php if ($this->custom_default_control) {
                                                                                                                                                                                                                                                                    if ($this->default_control_key === $key) {
                                                                                                                                                                                                                                                                        echo 'active';
                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                } ?>" data-filter=".dailybuddy-cf-<?php echo esc_attr($sorter_filter); ?>"><?php echo esc_html($sorter_label); ?></li><?php
                                                                                                                                                                                                                                                                                                                                                                                    endforeach;
                                                                                                                                                                                                                                                                                                                                                                                        ?></ul>
            </div>
        <?php
        }
    }

    protected function render_layout_3_filters()
    {
        $settings = $this->get_settings_for_display();
        if ($settings['filter_enable'] == 'yes') {
        ?>
            <div class="fg-layout-3-filters-wrap">
                <div class="fg-filter-wrap">
                    <button id="fg-filter-trigger" class="fg-filter-trigger">
                        <span>
                            <?php
                            if ($settings['dailybuddy_fg_all_label_text']) {
                                echo wp_kses($settings['dailybuddy_fg_all_label_text'], wp_kses_allowed_html("post"));
                            } elseif (isset($settings['dailybuddy_fg_controls']) && !empty($settings['dailybuddy_fg_controls'])) {
                                echo wp_kses($settings['dailybuddy_fg_controls'][0]['dailybuddy_fg_control'], wp_kses_allowed_html("post"));
                            }
                            ?>
                        </span>
                        <?php
                        if (isset($settings['fg_all_label_icon']) && !empty($settings['fg_all_label_icon'])) {
                            if (isset($settings['fg_all_label_icon']['value']['url'])) {
                                echo '<img src="' . esc_url($settings['fg_all_label_icon']['value']['url']) . '" alt="' . esc_attr(get_post_meta($settings['fg_all_label_icon']['value']['id'], '_wp_attachment_image_alt', true)) . '" />';
                            } else {
                                echo '<i class="' . esc_attr($settings['fg_all_label_icon']['value']) . '"></i>';
                            }
                        } else {
                            echo '<i class="fas fa-angle-down"></i>';
                        }
                        ?>

                    </button>
                    <ul class="fg-layout-3-filter-controls">
                        <?php if ($settings['dailybuddy_fg_all_label_text']) { ?>
                            <li class="control <?php if (! $this->custom_default_control) : ?> active <?php endif; ?>" data-filter="*"><?php echo wp_kses($settings['dailybuddy_fg_all_label_text'], wp_kses_allowed_html("post")); ?></li>
                        <?php } ?>

                        <?php foreach ($settings['dailybuddy_fg_controls'] as $key => $control) :
                            $sorter_filter = $this->sorter_class($control['dailybuddy_fg_control']);
                            $custom_id = sanitize_text_field($control['dailybuddy_fg_control_custom_id']) ?? "";
                        ?>
                            <li <?php if ($custom_id) : ?> id="<?php echo esc_attr($custom_id); ?>" <?php endif; ?> class="control <?php if ($this->custom_default_control) {
                                                                                                                                        if ($this->default_control_key === $key) {
                                                                                                                                            echo 'active';
                                                                                                                                        }
                                                                                                                                    } ?>" data-filter=".dailybuddy-cf-<?php echo esc_attr($sorter_filter); ?>">echo esc_html($control['dailybuddy_fg_control']);
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <form class="fg-layout-3-search-box" id="fg-layout-3-search-box" autocomplete="off">
                    <input type="text" id="fg-search-box-input" name="fg-frontend-search" placeholder="<?php echo esc_attr($settings['fg_sf_placeholder']); ?>" />
                </form>

            </div>
        <?php
        }
    }

    protected function render_loadmore_button()
    {
        $settings = $this->get_settings_for_display();
        $icon_migrated = isset($settings['__fa4_migrated']['load_more_icon_new']);
        $icon_is_new = empty($settings['load_more_icon']);

        $this->add_render_attribute('load-more-button', 'class', [
            'dailybuddy-gallery-load-more',
            'elementor-button',
            'elementor-size-' . $settings['button_size'],
        ]);

        if ($settings['pagination'] == 'yes') { ?>
            <div class="dailybuddy-filterable-gallery-loadmore">
                <button <?php $this->print_render_attribute_string('load-more-button'); ?>>
                    <span class="dailybuddy-btn-loader"></span>
                    <?php if ($settings['button_icon_position'] == 'before') { ?>
                        <?php if ($icon_is_new || $icon_migrated) { ?>
                            <?php if (isset($settings['load_more_icon_new']['value']['url'])) : ?>
                                <img class="dailybuddy-filterable-gallery-load-more-icon fg-load-more-icon-left" src="<?php echo esc_url($settings['load_more_icon_new']['value']['url']); ?>" alt="<?php echo esc_attr(get_post_meta($settings['load_more_icon_new']['value']['id'], '_wp_attachment_image_alt', true)); ?>" />
                            <?php else : ?>
                                <span class="dailybuddy-filterable-gallery-load-more-icon fg-load-more-icon-left <?php echo esc_attr($settings['load_more_icon_new']['value']); ?>" aria-hidden="true"></span>
                            <?php endif; ?>
                        <?php } else { ?>
                            <span class="dailybuddy-filterable-gallery-load-more-icon fg-load-more-icon-left <?php echo esc_attr($settings['load_more_icon']); ?>" aria-hidden="true"></span>
                        <?php } ?>
                    <?php } ?>
                    <span class="dailybuddy-filterable-gallery-load-more-text">
                        <?php echo wp_kses($settings['load_more_text'], wp_kses_allowed_html("post")); ?>
                    </span>
                    <?php if ($settings['button_icon_position'] == 'after') { ?>
                        <?php if ($icon_is_new || $icon_migrated) { ?>
                            <?php if (isset($settings['load_more_icon_new']['value']['url'])) : ?>
                                <img class="dailybuddy-filterable-gallery-load-more-icon fg-load-more-icon-right" src="<?php echo esc_url($settings['load_more_icon_new']['value']['url']); ?>" alt="<?php echo esc_attr(get_post_meta($settings['load_more_icon_new']['value']['id'], '_wp_attachment_image_alt', true)); ?>" />
                            <?php else : ?>
                                <span class="dailybuddy-filterable-gallery-load-more-icon fg-load-more-icon-right <?php echo esc_attr($settings['load_more_icon_new']['value']); ?>" aria-hidden="true"></span>
                            <?php endif; ?>
                        <?php } else { ?>
                            <span class="dailybuddy-filterable-gallery-load-more-icon fg-load-more-icon-right <?php echo esc_attr($settings['load_more_icon']); ?>" aria-hidden="true"></span>
                        <?php } ?>
                    <?php } ?>
                </button>
            </div>
            <?php }
    }

    protected function gallery_item_store()
    {
        $settings = $this->get_settings_for_display();
        $gallery_items = $settings['dailybuddy_fg_gallery_items'];
        $gallery_store = [];
        $counter = 0;
        $video_gallery_yt_privacy = ! empty($settings['video_gallery_yt_privacy']) && 'yes' === $settings['video_gallery_yt_privacy'] ? 1 : 0;

        foreach ($gallery_items as $gallery) {
            $gallery_store[$counter]['title']        = wp_kses_post($gallery['dailybuddy_fg_gallery_item_name']);
            $gallery_store[$counter]['content']      = $this->parse_text_editor($gallery['dailybuddy_fg_gallery_item_content']);
            $gallery_store[$counter]['id']           = $gallery['_id'];
            $gallery_store[$counter]['image']        = $gallery['dailybuddy_fg_gallery_img'];
            $gallery_store[$counter]['image']        = sanitize_url($gallery['dailybuddy_fg_gallery_img']['url']);
            $gallery_store[$counter]['image_id']     = $gallery['dailybuddy_fg_gallery_img']['id'];
            $gallery_store[$counter]['maybe_link']   = $gallery['dailybuddy_fg_gallery_link'];
            $gallery_store[$counter]['link']         = $gallery['dailybuddy_fg_gallery_img_link'] ?? [];
            $gallery_store[$counter]['toggle']       = isset($gallery['dailybuddy_fg_gallery_item_toggle']) ? $gallery['dailybuddy_fg_gallery_item_toggle'] : '';
            $gallery_store[$counter]['writing_mode'] = isset($gallery['dailybuddy_fg_gallery_item_tag_writing_mode']) ? $gallery['dailybuddy_fg_gallery_item_tag_writing_mode'] : 'vertical-lr';
            $gallery_store[$counter]['tag_icon_enable'] = isset($gallery['dailybuddy_fg_gallery_item_tag_icon_enable']) ? $gallery['dailybuddy_fg_gallery_item_tag_icon_enable'] : '';
            $gallery_store[$counter]['tag_icon']     = isset($gallery['dailybuddy_fg_gallery_item_tag_icon']) ? $gallery['dailybuddy_fg_gallery_item_tag_icon'] : '';
            $gallery_store[$counter]['video_layout'] = isset($gallery['dailybuddy_fg_gallery_video_layout']) && !empty($gallery['dailybuddy_fg_gallery_video_layout']) ? $gallery['dailybuddy_fg_gallery_video_layout'] : 'horizontal';

            $gallery_store[$counter]['video_gallery_switch'] = isset($gallery['fg_video_gallery_switch']) ? $gallery['fg_video_gallery_switch'] : '';

            $gallery['dailybuddy_fg_gallery_item_video_link'] = empty($gallery['dailybuddy_fg_gallery_item_video_link']) ? '' : $gallery['dailybuddy_fg_gallery_item_video_link'];
            if (strpos($gallery['dailybuddy_fg_gallery_item_video_link'], 'youtu.be') != false) {
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $gallery['dailybuddy_fg_gallery_item_video_link'], $matches);
                $video_link = !empty($matches) ? sprintf('https://www.youtube.com/watch?v=%s', $matches[1]) : '';
                $gallery_store[$counter]['video_link'] = $video_link;
            } else if (strpos($gallery['dailybuddy_fg_gallery_item_video_link'], 'youtube.com/shorts/') !== false) {
                preg_match('/shorts\/([a-zA-Z0-9_-]+)/', $gallery['dailybuddy_fg_gallery_item_video_link'], $matches);
                $video_link = ! empty($matches) ? sprintf('https://www.youtube.com/watch?v=%s', $matches[1]) : '';
                $gallery_store[$counter]['video_link'] = $video_link;
            } else {
                $gallery_store[$counter]['video_link'] = $gallery['dailybuddy_fg_gallery_item_video_link'];
            }

            if ($video_gallery_yt_privacy) {
                if (strpos($gallery_store[$counter]['video_link'], 'youtube') != false) {
                    $gallery_store[$counter]['video_link'] = str_replace('youtube.com/watch?v=', 'youtube-nocookie.com/embed/', $gallery_store[$counter]['video_link']);
                }

                if (strpos($gallery_store[$counter]['video_link'], 'vimeo') != false) {
                    $gallery_store[$counter]['video_link'] = esc_url(add_query_arg(['dnt' => 1], $gallery_store[$counter]['video_link']));
                }
            }

            $gallery_store[$counter]['show_lightbox'] = $gallery['dailybuddy_fg_gallery_lightbox'];
            $gallery_store[$counter]['play_icon'] = $gallery['fg_video_gallery_play_icon'];
            $gallery_store[$counter]['controls'] = $this->sorter_class($gallery['dailybuddy_fg_gallery_control_name']);
            $gallery_store[$counter]['controls_name'] = $gallery['dailybuddy_fg_gallery_control_name'];
            $gallery_store[$counter]['price_switch'] = $gallery['fg_item_price_switch'];
            $gallery_store[$counter]['price'] = $gallery['fg_item_price'];
            $gallery_store[$counter]['ratings_switch'] = $gallery['fg_item_ratings_switch'];
            $gallery_store[$counter]['ratings'] = $gallery['fg_item_ratings'];
            $gallery_store[$counter]['category_switch'] = $gallery['fg_item_cat_switch'];
            $gallery_store[$counter]['category'] = $gallery['fg_item_cat'];
            $counter++;
        }

        return $gallery_store;
    }

    /**
     * Generating gallery item full image clickable content
     *
     * @since 4.7.5
     * @param array $settings : Elementor provided settings
     * @param array $item : Gallery item
     * @param boolean $check_popup_status
     * @return string : Html markup
     */
    public function gallery_item_full_image_clickable_content($settings, $item, $check_popup_status = true)
    {
        $html = $title = '';
        $magnific_class = "dailybuddy-magnific-link dailybuddy-magnific-link-clone active";
        $is_lightbox = 'yes';

        if ($settings['dailybuddy_section_fg_mfp_caption'] === 'yes') {
            $title = $item['title'];
        }

        if ($settings['dailybuddy_fg_show_popup'] === 'media' && $settings['dailybuddy_section_fg_full_image_action'] === 'link') {
            $magnific_class = '';
            $is_lightbox = 'no';
        }

        if ($check_popup_status) {
            if ($settings['dailybuddy_section_fg_full_image_action'] === 'lightbox' && !$this->popup_status) {
                $this->popup_status = true;
                $html .= '<a area-hidden="true" href="' . esc_url($item['image']) . '" class="' . $magnific_class . ' media-content-wrap active" data-elementor-open-lightbox="' . esc_attr($is_lightbox) . '" title="' . esc_attr($title) . '">';
            }
        } else {
            if ($settings['dailybuddy_section_fg_full_image_action'] === 'lightbox') {
                $html .= '<a area-hidden="true" href="' . esc_url($item['image']) . '" class="' . $magnific_class . ' media-content-wrap active" data-elementor-open-lightbox="' . esc_attr($is_lightbox) . '" title="' . esc_attr($title) . '">';
            }
        }

        if ($settings['dailybuddy_section_fg_full_image_action'] === 'link') {
            static $ea_link_repeater_index = 0;
            $link_key = 'link_' . $ea_link_repeater_index++;

            if (! empty($item['link']) && is_array($item['link'])) {
                $this->add_link_attributes($link_key, $item['link']);
            }

            $html .= '<a ' . $this->get_render_attribute_string($link_key) . '>';
        }

        return $html;
    }

    /**
     * Generating video gallery item thumbnail content
     *
     * @since 4.7.5
     * @param array $settings : Elementor provided settings
     * @param array $item : Gallery item
     * @return string : Html markup
     */
    protected function gallery_item_thumbnail_content($settings, $item)
    {

        $caption_style  = $settings['dailybuddy_fg_caption_style'] == 'card' ? 'caption-style-card' : 'caption-style-hoverer';
        $image_alt = get_post_meta($item['image_id'], '_wp_attachment_image_alt', true);
        $alt_text = $image_alt ? $image_alt : $item['title'];

        $html = '<img src="' . esc_url($item['image']) . '" data-lazy-src="' . esc_url($item['image']) . '" alt="' . esc_attr($alt_text) . '" class="gallery-item-thumbnail">';

        if (empty($settings['dailybuddy_section_fg_full_image_clickable']) && $item['video_gallery_switch'] !== 'true') {
            if ($settings['dailybuddy_fg_show_popup'] == 'buttons' && $settings['dailybuddy_fg_caption_style'] === 'card') {
                $html .= '<div class="gallery-item-caption-wrap card-hover-bg caption-style-hoverer ' . esc_attr($settings['dailybuddy_fg_grid_hover_style']) . '">
                            ' . $this->render_fg_buttons($settings, $item) . '
                        </div>';
            } elseif ($settings['dailybuddy_fg_show_popup'] === 'media' && $settings['dailybuddy_fg_caption_style'] === 'card') {
                $html .= '<div class="gallery-item-caption-wrap card-hover-bg caption-style-hoverer ' . esc_attr($settings['dailybuddy_fg_grid_hover_style']) . '"></div>';
            }
        }

        if (isset($item['video_gallery_switch']) && ($item['video_gallery_switch'] === 'true')) {
            $html .= $this->video_gallery_switch_content($item, $caption_style, true, $settings);
        }

        return $html;
    }

    /**
     * Generating video gallery switch content
     *
     * @since 4.7.5
     * @param array $item : Gallery item
     * @param boolean $show_video_popup_bg
     * @return string : Html markup
     */
    protected function video_gallery_switch_content($item, $caption_style, $show_video_popup_bg = true, $settings = null)
    {
        $html       = '';
        $icon_url   = isset($item['play_icon']['url']) ? $item['play_icon']['url'] : '';
        $video_url  = isset($item['video_link']) ? $item['video_link'] : '#';
        $title      = isset($item['title']) ? $item['title'] : '';
        $classes    = "video-popup dailybuddy-magnific-link dailybuddy-magnific-link-clone active dailybuddy-magnific-video-link mfp-iframe playout-" . $item['video_layout'];

        $html .= '<a area-hidden="true"  title="' . esc_attr(wp_strip_all_tags($title)) . '" aria-label="dailybuddy-magnific-video-link" href="' . esc_url($video_url) . '" class="' . esc_attr($classes) . '" data-id="' . esc_attr($item['id']) . '" data-elementor-open-lightbox="yes">';

        if ($show_video_popup_bg) {
            if ('caption-style-card' === $caption_style) {
                $html .= '<div class="video-popup-bg"></div>';
            } else {
                $html .= '<div class="video-popup-bg gallery-item-caption-wrap ' . esc_attr($caption_style . ' ' . $settings['dailybuddy_fg_grid_hover_style']) . '">';
                $html .= '<div class="gallery-item-caption-over">';
                if (isset($item['title']) && !empty($item['title']) || isset($item['content']) && !empty($item['content'])) {
                    if (!empty($item['title'])) {
                        $title_link_open = $title_link_close = '';
                        $html .= $title_link_open . '<' . esc_attr($settings['title_tag']) . ' class="fg-item-title">' . $item['title'] . '</' . esc_attr($settings['title_tag']) . '>' . $title_link_close;
                    }

                    if (!empty($item['content'])) {
                        $html .= '<div class="fg-item-content">' . wpautop(preg_replace('/<a\b[^>]*>(.*?)<\/a>/i', '', $item['content'])) . '</div>';
                    }
                }
                $html .= '</div>';
                $html .= '</div>';
            }
        }

        if (!empty($icon_url)) {
            $html .= '<img width="62" height="62" src="' . esc_url($icon_url) . '" alt="dailybuddy-fg-video-play-icon" >';
        }

        $html .= '</a>';

        return $html;
    }

    /**
     * Generating caption content for gallery item
     *
     * @since 4.7.5
     * @param array $settings : Elementor provided settings
     * @param array $item : Gallery item
     * @param string $caption_style
     * @return string : Html markup
     */
    protected function gallery_item_caption_content($settings, $item, $caption_style)
    {
        $html = '<div class="gallery-item-caption-wrap ' . esc_attr($caption_style . ' ' . $settings['dailybuddy_fg_grid_hover_style']) . '">';
        $is_image_clickable = isset($settings['dailybuddy_section_fg_full_image_clickable']) && 'yes' === $settings['dailybuddy_section_fg_full_image_clickable'] && 'card' !== $settings['dailybuddy_fg_caption_style'];
        if ('hoverer' == $settings['dailybuddy_fg_caption_style']) {
            $html .= '<div class="gallery-item-hoverer-bg"></div>';
        }

        $html .= '<div class="gallery-item-caption-over">';
        if (isset($item['title']) && !empty($item['title']) || isset($item['content']) && !empty($item['content'])) {
            if (!empty($item['title'])) {
                $title_link_open = $title_link_close = '';

                // Determine if title should be clickable
                $should_make_title_clickable = false;

                if ($settings['dailybuddy_fg_caption_style'] === 'hoverer') {
                    $should_make_title_clickable = $settings['dailybuddy_title_clickable'] === 'yes' && !$is_image_clickable;
                } else {
                    $should_make_title_clickable = $settings['dailybuddy_title_clickable'] === 'yes';
                }

                // Generate link HTML if title should be clickable
                if ($should_make_title_clickable) {
                    static $ea_link_repeater_index = 0;
                    $link_key = 'link_' . $ea_link_repeater_index++;

                    if (empty($this->get_render_attribute_string($link_key))) {
                        $link_key = 'dailybuddy_link_' . $ea_link_repeater_index++;
                        $this->add_link_attributes($link_key, $item['link']);
                    }

                    $title_link_open = '<a ' . $this->get_render_attribute_string($link_key) . '>';
                    $title_link_close = '</a>';
                }

                $title_tag = esc_attr($settings['title_tag']);
                $html .= $title_link_open . '<' . $title_tag . ' class="fg-item-title">' . $item['title'] . '</' . $title_tag . '>' . $title_link_close;
            }

            if (! empty($item['content'])) {
                if ($settings['dailybuddy_fg_caption_style'] === 'hoverer') {
                    $content = ! $is_image_clickable ? $item['content'] : preg_replace(['/<a\b[^>]*>/i', '/<\/a>/i'], '', $item['content']);
                    $html .= '<div class="fg-item-content">' . wpautop($content) . '</div>';
                } else {
                    $html .= '<div class="fg-item-content">' . wpautop($item['content']) . '</div>';
                }
            }
        }

        if ($settings['dailybuddy_fg_show_popup'] == 'buttons' && $settings['dailybuddy_fg_caption_style'] !== 'card') {
            if (! $is_image_clickable) {
                $html .= $this->render_fg_buttons($settings, $item);
            }
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    protected function render_fg_buttons($settings, $item)
    {
        $zoom_icon_migrated = isset($settings['__fa4_migrated']['dailybuddy_section_fg_zoom_icon_new']);
        $zoom_icon_is_new = empty($settings['dailybuddy_section_fg_zoom_icon']);
        $link_icon_migrated = isset($settings['__fa4_migrated']['dailybuddy_section_fg_link_icon_new']);
        $link_icon_is_new = empty($settings['dailybuddy_section_fg_link_icon']);
        $title = '';

        if ($settings['dailybuddy_section_fg_mfp_caption'] === 'yes') {
            $title = $item['title'];
        }

        ob_start();

        echo '<div class="gallery-item-buttons">';

        if ($item['show_lightbox'] == true) {
            echo '<a area-hidden="true" aria-label="dailybuddy-magnific-link" href="' . esc_url($item['image']) . '" class="dailybuddy-magnific-link dailybuddy-magnific-link-clone active" data-elementor-open-lightbox="yes" title="' . esc_attr($title) . '">';

            echo '<span class="fg-item-icon-inner">';
            if ($zoom_icon_is_new || $zoom_icon_migrated) {
                if (isset($settings['dailybuddy_section_fg_zoom_icon_new']['value']['url'])) {
                    echo '<img src="' . esc_url($settings['dailybuddy_section_fg_zoom_icon_new']['value']['url']) . '" alt="' . esc_attr(get_post_meta($settings['dailybuddy_section_fg_zoom_icon_new']['value']['id'], '_wp_attachment_image_alt', true)) . '" />';
                } else if (isset($settings['dailybuddy_section_fg_zoom_icon_new']['value'])) {
                    echo '<i class="' . esc_attr($settings['dailybuddy_section_fg_zoom_icon_new']['value']) . '" aria-hidden="true"></i>';
                }
            } else {
                echo '<i class="' . esc_attr($settings['dailybuddy_section_fg_zoom_icon']) . '" aria-hidden="true"></i>';
            }
            echo '</span>
            </a>';
        }

        if ($item['maybe_link'] == 'true') {
            if (!empty($item['link']['url'])) {
                static $ea_link_repeater_index = 0;
                $link_key = 'link_' . $ea_link_repeater_index++;

                $this->add_link_attributes($link_key, $item['link']);
                $this->add_render_attribute($link_key, 'aria-label', 'dailybuddy-item-maybe-link');
            ?>
                <a <?php $this->print_render_attribute_string($link_key); ?>> <?php
                                                                                echo '<span class="fg-item-icon-inner">';

                                                                                if ($link_icon_is_new || $link_icon_migrated) {
                                                                                    if (isset($settings['dailybuddy_section_fg_link_icon_new']['value']['url'])) {
                                                                                        echo '<img src="' . esc_url($settings['dailybuddy_section_fg_link_icon_new']['value']['url']) . '" alt="' . esc_attr(get_post_meta($settings['dailybuddy_section_fg_link_icon_new']['value']['id'], '_wp_attachment_image_alt', true)) . '" />';
                                                                                    } else {
                                                                                        echo '<i class="' . esc_attr($settings['dailybuddy_section_fg_link_icon_new']['value']) . '" aria-hidden="true"></i>';
                                                                                    }
                                                                                } else {
                                                                                    echo '<i class="' . esc_attr($settings['dailybuddy_section_fg_link_icon']) . '" aria-hidden="true"></i>';
                                                                                }

                                                                                echo '</span>';
                                                                                echo '</a>';
                                                                            }
                                                                        }

                                                                        echo '</div>';

                                                                        return ob_get_clean();
                                                                    }

                                                                    protected function render_layout_3_gallery_items($init_show = 0)
                                                                    {
                                                                        $settings = $this->get_settings_for_display();
                                                                        $gallery = $this->gallery_item_store();
                                                                        $caption_style  = $settings['dailybuddy_fg_caption_style'] == 'card' ? 'caption-style-card' : 'caption-style-hoverer';
                                                                        $gallery_markup = [];

                                                                        foreach ($gallery as $item) {
                                                                            $html = '<div class="dailybuddy-filterable-gallery-item-wrap dailybuddy-cf-' . esc_attr($item['controls']) . '" data-search-key="' . esc_attr(strtolower(str_replace(" ", "-", $item['title']))) . '">';
                                                                            $html .= '<div class="fg-layout-3-item dailybuddy-gallery-grid-item">';

                                                                            if ($settings['dailybuddy_section_fg_full_image_clickable'] && 'true' !== $item['video_gallery_switch']) {
                                                                                $html .= $this->gallery_item_full_image_clickable_content($settings, $item, false);
                                                                            }

                                                                            if (
                                                                                isset($item['video_gallery_switch']) && ($item['video_gallery_switch'] === 'true')
                                                                                && isset($settings['dailybuddy_section_fg_full_image_clickable']) && $settings['dailybuddy_section_fg_full_image_clickable'] === 'yes'
                                                                            ) {
                                                                                $html .= '<div class="gallery-item-thumbnail-wrap fg-layout-3-item-thumb video_gallery_switch_on">';
                                                                            } else {
                                                                                $html .= '<div class="gallery-item-thumbnail-wrap fg-layout-3-item-thumb">';
                                                                            }

                                                                            $alt_text = get_post_meta($item['image_id'], '_wp_attachment_image_alt', true);
                                                                            $alt_text = ! empty($alt_text) ? $alt_text : $item['title'];
                                                                            $html .= '<img src="' . esc_url($item['image']) . '" data-lazy-src="' . esc_url($item['image']) . '" alt="' . esc_attr($alt_text) . '" class="gallery-item-thumbnail">';

                                                                            $html .= '<div class="gallery-item-caption-wrap card-hover-bg caption-style-hoverer">';
                                                                            $html .= '<div class="fg-caption-head">';
                                                                            if (isset($item['price_switch']) && $item['price_switch'] == 'true') {
                                                                                $html .= '<div class="fg-item-price">' . $item['price'] . '</div>';
                                                                            }
                                                                            if (isset($item['ratings_switch']) && $item['ratings_switch'] == 'true') {
                                                                                $html .= '<div class="fg-item-ratings"><i class="fas fa-star"></i> ' . $item['ratings'] . '</div>';
                                                                            }
                                                                            $html .= '</div>';

                                                                            if (isset($item['video_gallery_switch']) && ($item['video_gallery_switch'] === 'true')) {
                                                                                $html .= $this->video_gallery_switch_content($item, $caption_style, false);
                                                                            } else {
                                                                                if (empty($settings['dailybuddy_section_fg_full_image_clickable'])) {
                                                                                    $html .= $this->render_fg_buttons($settings, $item);
                                                                                }
                                                                            }

                                                                            $html .= '</div>';

                                                                            $html .= '</div>';

                                                                            if ($settings['dailybuddy_section_fg_full_image_clickable']) $html .= '</a>';

                                                                            $html .= '<div class="fg-layout-3-item-content">';

                                                                            if (isset($item['category_switch']) && $item['category_switch'] == 'true') {
                                                                                $html .= '<div class="fg-item-category"><span>' . $item['category'] . '</span></div>';
                                                                            }
                                                                            $title_link_open = $title_link_close = '';
                                                                            if ($settings['dailybuddy_title_clickable'] === 'yes') {
                                                                                static $ea_link_repeater_index = 0;
                                                                                $link_key = 'link_' . $ea_link_repeater_index++;
                                                                                if (empty($this->get_render_attribute_string($link_key))) {
                                                                                    $link_key = 'dailybuddy_link_' . $ea_link_repeater_index++;
                                                                                    $this->add_link_attributes($link_key, $item['link']);
                                                                                }
                                                                                $title_link_open = '<a ' . $this->get_render_attribute_string($link_key) . '>';
                                                                                $title_link_close = '</a>';
                                                                            }

                                                                            $title_tag = esc_attr($settings['title_tag']);
                                                                            $html .= $title_link_open . '<' . $title_tag . ' class="fg-item-title">' . $item['title'] . '</' . $title_tag . '>' . $title_link_close;
                                                                            $html .= '<div class="fg-item-content">' . wpautop($item['content']) . '</div>';
                                                                            $html .= '</div>';

                                                                            $html .= '</div>';
                                                                            $html .= '</div>';

                                                                            $gallery_markup[] = $html;
                                                                        }
                                                                        return $gallery_markup;
                                                                    }

                                                                    protected function render_gallery_items($init_show = 0)
                                                                    {
                                                                        $settings       = $this->get_settings_for_display();
                                                                        $gallery        = $this->gallery_item_store();
                                                                        $gallery_markup = [];
                                                                        $caption_style  = $settings['dailybuddy_fg_caption_style'] == 'card' ? 'caption-style-card' : 'caption-style-hoverer';
                                                                        $magnific_class = "dailybuddy-magnific-link dailybuddy-magnific-link-clone active";
                                                                        $is_lightbox    = 'yes';

                                                                        if ($settings['dailybuddy_fg_show_popup'] === 'media' && $settings['dailybuddy_section_fg_full_image_action'] === 'link') {
                                                                            $magnific_class = '';
                                                                            $is_lightbox = 'no';
                                                                        }

                                                                        foreach ($gallery as $item) {
                                                                            $this->popup_status = false;
                                                                            $close_media_content_wrap = false;

                                                                            $title = '';

                                                                            if ($settings['dailybuddy_section_fg_mfp_caption'] === 'yes') {
                                                                                $title = $item['title'];
                                                                            }

                                                                            if ($item['controls'] != '') {
                                                                                $html = '<div class="dailybuddy-filterable-gallery-item-wrap dailybuddy-cf-' . $item['controls'] . '">
				<div class="dailybuddy-gallery-grid-item">';
                                                                            } else {
                                                                                $html = '<div class="dailybuddy-filterable-gallery-item-wrap">
				<div class="dailybuddy-gallery-grid-item">';
                                                                            }

                                                                            if (
                                                                                $settings['dailybuddy_fg_caption_style'] === 'card'
                                                                                && $item['video_gallery_switch'] != 'true'
                                                                                && $settings['dailybuddy_fg_show_popup'] === 'media'
                                                                            ) {
                                                                                $this->popup_status = true;
                                                                                $close_media_content_wrap = true;
                                                                                $html .= '<a  aria-hidden="true" aria-label="dailybuddy-magnific-link" href="' . esc_url($item['image']) . '" class="' . $magnific_class . ' media-content-wrap" data-elementor-open-lightbox="' . esc_attr($is_lightbox) . '" title="' . esc_attr($title) . '">';
                                                                            }

                                                                            if ($settings['dailybuddy_section_fg_full_image_clickable'] && 'true' !== $item['video_gallery_switch']) {
                                                                                $html .= $this->gallery_item_full_image_clickable_content($settings, $item);
                                                                            }

                                                                            if (
                                                                                isset($item['video_gallery_switch']) && ($item['video_gallery_switch'] === 'true')
                                                                                && isset($settings['dailybuddy_section_fg_full_image_clickable']) && $settings['dailybuddy_section_fg_full_image_clickable'] === 'yes'
                                                                            ) {
                                                                                $html .= '<div class="gallery-item-thumbnail-wrap video_gallery_switch_on">';
                                                                            } else {
                                                                                $html .= '<div class="gallery-item-thumbnail-wrap">';
                                                                            }

                                                                            $html .= $this->gallery_item_thumbnail_content($settings, $item);

                                                                            $html .= '</div>';

                                                                            if ($settings['dailybuddy_section_fg_full_image_clickable'] && 'card' === $settings['dailybuddy_fg_caption_style']) {
                                                                                $html .= '</a>';
                                                                            }
                                                                            if ($close_media_content_wrap) {
                                                                                $html .= '</a>';
                                                                            }

                                                                            if ($settings['dailybuddy_fg_show_popup'] == 'media' && $settings['dailybuddy_fg_caption_style'] !== 'card' && !$this->popup_status) {
                                                                                $html .= '<a area-hidden="true" aria-label="dailybuddy-magnific-link" href="' . esc_url($item['image']) . '" class="' . $magnific_class . ' media-content-wrap" data-elementor-open-lightbox="' . esc_attr($is_lightbox) . '" title="' . esc_attr($title) . '">';
                                                                            }

                                                                            // Overlay
                                                                            if ($settings['dailybuddy_fg_caption_style'] === 'hoverer') {
                                                                                if ($item['video_gallery_switch'] !== 'true') {
                                                                                    $html .= $this->gallery_item_caption_content($settings, $item, $caption_style);
                                                                                }
                                                                            }

                                                                            if ($settings['dailybuddy_fg_show_popup'] == 'media') {
                                                                                $html .= '</a>';
                                                                            }

                                                                            if ($settings['dailybuddy_section_fg_full_image_clickable'] && 'card' !== $settings['dailybuddy_fg_caption_style']) {
                                                                                $html .= '</a>';
                                                                            }

                                                                            // Card
                                                                            if ($settings['dailybuddy_fg_caption_style'] === 'card') {
                                                                                $html .= $this->gallery_item_caption_content($settings, $item, $caption_style);
                                                                            }

                                                                            $html .= '</div></div>';

                                                                            $gallery_markup[] = $html;
                                                                        }

                                                                        return $gallery_markup;
                                                                    }

                                                                    protected function render_media_query($settings)
                                                                    {
                                                                        $section_id  = esc_html($this->get_id());
                                                                        $breakpoints = method_exists(Plugin::$instance->breakpoints, 'get_breakpoints_config') ? Plugin::$instance->breakpoints->get_breakpoints_config() : [];
                                                                        
                                                                        // Set default column values
                                                                        $columns_desktop = isset($settings['columns']) ? $settings['columns'] : 3;
                                                                        $columns_tablet = isset($settings['columns_tablet']) ? $settings['columns_tablet'] : 2;
                                                                        $columns_mobile = isset($settings['columns_mobile']) ? $settings['columns_mobile'] : 1;
                                                                        
                                                                        // Set CSS Custom Properties on the gallery element
                                                                        // These will be used by the responsive.css file
                                                                        $this->add_render_attribute('gallery', 'style', 
                                                                            '--fg-columns-desktop:' . esc_attr($columns_desktop) . ';' .
                                                                            '--fg-columns-tablet:' . esc_attr($columns_tablet) . ';' .
                                                                            '--fg-columns-mobile:' . esc_attr($columns_mobile) . ';'
                                                                        );
                                                                    }

                                                                    /**
                                                                     * Render gallery items
                                                                     *
                                                                     * @param [type] $settings
                                                                     * @param [type] $gallery_items
                                                                     * @return void
                                                                     */
                                                                    public function dailybuddy_render_gallery_item_wrap($settings, $gallery_items)
                                                                    {
                                                                                ?>
            <div <?php $this->print_render_attribute_string('gallery-items-wrap'); ?>>
                <?php
                                                                        $init_show = absint($settings['dailybuddy_fg_items_to_show']);

                                                                        for ($i = 0; $i < $init_show; $i++) {

                                                                            if (array_key_exists($i, $gallery_items)) {
                                                                                /**
                                                                                 * Output gallery item HTML
                                                                                 * 
                                                                                 * $gallery_items contains self-generated HTML from render_gallery_items()
                                                                                 * or render_layout_3_gallery_items() functions. The HTML is already
                                                                                 * escaped at generation time (esc_url, esc_attr, etc.).
                                                                                 * 
                                                                                 * Using wp_kses_post() to allow safe HTML tags while filtering
                                                                                 * any potentially dangerous content.
                                                                                 */
                                                                                echo wp_kses_post($gallery_items[$i]);
                                                                            }
                                                                        }
                                                                        if ($settings['dailybuddy_fg_caption_style'] === 'layout_3'):
                ?>
                    <div id="dailybuddy-fg-no-items-found" style="display:none;">
                        <?php
                                                                            echo wp_kses($settings['dailybuddy_fg_not_found_text'], wp_kses_allowed_html("post"));
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
                                                                    }

                                                                    protected function render()
                                                                    {
                                                                        $settings = $this->get_settings_for_display();

                                                                        if (!empty($settings['dailybuddy_fg_filter_duration'])) {
                                                                            $filter_duration = $settings['dailybuddy_fg_filter_duration'];
                                                                        } else {
                                                                            $filter_duration = 500;
                                                                        }

                                                                        $this->add_render_attribute(
                                                                            'gallery',
                                                                            [
                                                                                'id' => 'dailybuddy-filter-gallery-wrapper-' . esc_attr($this->get_id()),
                                                                                'class' => 'dailybuddy-filter-gallery-wrapper',
                                                                                'data-layout-mode'  => $settings['dailybuddy_fg_caption_style']
                                                                            ]
                                                                        );

                                                                        $gallery_settings = [
                                                                            'grid_style' => $settings['dailybuddy_fg_grid_style'],
                                                                            'popup' => $settings['dailybuddy_fg_show_popup'],
                                                                            'duration' => $filter_duration,
                                                                            'gallery_enabled' => $settings['photo_gallery'],
                                                                            'video_gallery_yt_privacy' => $settings['video_gallery_yt_privacy'],
                                                                            'control_all_text' => $settings['dailybuddy_fg_all_label_text'],
                                                                        ];

                                                                        if (Plugin::$instance->editor->is_edit_mode()) {
                                                                            $gallery_settings['post_id'] =  Plugin::$instance->editor->get_post_id();
                                                                        } else {
                                                                            $gallery_settings['post_id'] = get_the_ID();
                                                                        }
                                                                        if (method_exists(Plugin::$instance->breakpoints, 'get_breakpoints_config') && ! empty($breakpoints = \Elementor\Plugin::$instance->breakpoints->get_breakpoints_config())) {

                                                                            $this->add_render_attribute('gallery', 'data-breakpoints', wp_json_encode($breakpoints));
                                                                        }

                                                                        $gallery_settings['widget_id'] = $this->get_id();

                                                                        $no_more_items_text = esc_html($settings['nomore_items_text']);
                                                                        $grid_class = $settings['dailybuddy_fg_grid_style'] == 'grid' ? 'dailybuddy-filter-gallery-grid' : 'masonry';

                                                                        if ('layout_3' == $settings['dailybuddy_fg_caption_style']) {
                                                                            $gallery_items = $items = $this->render_layout_3_gallery_items();
                                                                        } else {
                                                                            $gallery_items = $items = $this->render_gallery_items();
                                                                        }

                                                                        $this->add_render_attribute('gallery-items-wrap', [
                                                                            'class' => [
                                                                                'dailybuddy-filter-gallery-container',
                                                                                $grid_class
                                                                            ],
                                                                            'data-images-per-page' => $settings['images_per_page'],
                                                                            'data-total-gallery-items' => count($settings['dailybuddy_fg_gallery_items']),
                                                                            'data-nomore-item-text' => $no_more_items_text,
                                                                            'data-is-randomize' => 'yes' === $settings['dailybuddy_item_randomize'] ? 'yes' : 'no',
                                                                        ]);

                                                                        if ('yes' === $settings['dailybuddy_privacy_notice_control'] && !empty($settings['dailybuddy_privacy_notice'])) {
                                                                            $this->add_render_attribute('gallery-items-wrap', 'data-privacy-notice', esc_html($settings['dailybuddy_privacy_notice']));
                                                                        }

                                                                        $html_json   = wp_json_encode($gallery_items);
                                                                        $json_base64 = base64_encode($html_json);

                                                                        $this->add_render_attribute('gallery-items-wrap', 'data-settings', wp_json_encode($gallery_settings));
                                                                        $this->add_render_attribute('gallery-items-wrap', 'data-search-all', esc_attr($settings['dailybuddy_search_among_all']));
                                                                        $this->add_render_attribute('gallery-items-wrap', 'data-gallery-items', esc_attr($json_base64));
                                                                        $this->add_render_attribute('gallery-items-wrap', 'data-init-show', esc_attr($settings['dailybuddy_fg_items_to_show']));
                                                                        $this->render_media_query($settings);

                                                                        $this->custom_default_control = empty($settings['dailybuddy_fg_all_label_text']) ? true : false;

                                                                        foreach ($settings['dailybuddy_fg_controls'] as $key_default => $control_default) :
                                                                            if (! empty($control_default['dailybuddy_fg_control_active_as_default']) && 'yes' === $control_default['dailybuddy_fg_control_active_as_default']) {
                                                                                $this->default_control_key = $key_default;
                                                                                $this->custom_default_control = true;
                                                                            }
                                                                        endforeach;

                                                                        $this->add_render_attribute('gallery', 'data-default_control_key', esc_attr($this->default_control_key));
                                                                        $this->add_render_attribute('gallery', 'data-custom_default_control', esc_attr($this->custom_default_control));
        ?>
            <div <?php $this->print_render_attribute_string('gallery'); ?>>
                <?php
                                                                        if (in_array($settings['dailybuddy_fg_caption_style'], ['grid_flow_gallery', 'harmonic_gallery'])) {
                                                                            $gallery_items_in = $this->gallery_item_store();
                                                                            $this->render_filters();
                                                                            do_action(
                                                                                'dailybuddy_add_filterable_gallery_style_block',
                                                                                $settings,
                                                                                $this,
                                                                                $gallery_items_in
                                                                            );
                                                                        } elseif ('layout_3' == $settings['dailybuddy_fg_caption_style']) {
                                                                            $this->render_layout_3_filters();
                                                                            $this->dailybuddy_render_gallery_item_wrap($settings, $gallery_items);
                                                                        } else {
                                                                            $this->render_filters();
                                                                            $this->dailybuddy_render_gallery_item_wrap($settings, $gallery_items);
                                                                        }
                                                                        // gallery-items-wrap

                                                                        // Editor script now loaded via wp_enqueue_script in module.php
                                                                        $this->render_loadmore_button();
                ?>
            </div>

        <?php
                                                                    }

                                                                    /**
                                                                     * Render masonry script
                                                                }
