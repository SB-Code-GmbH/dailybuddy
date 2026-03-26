<?php

/**
 * dailybuddy Category Post List Widget
 *
 * Displays a linked list of posts from a chosen category.
 * Highlights the currently viewed post for sidebar navigation.
 * Features: live search, excerpt tooltip, button styles, responsive select.
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class Dailybuddy_Elementor_Category_Post_List_Widget extends Widget_Base
{
    public function get_name()
    {
        return 'dailybuddy-category-post-list';
    }

    public function get_title()
    {
        return __('Category Post List', 'dailybuddy');
    }

    public function get_icon()
    {
        return 'eicon-post-list mini-icon-dailybuddy';
    }

    public function get_categories()
    {
        return array('dailybuddy');
    }

    public function get_keywords()
    {
        return array('category', 'post', 'list', 'navigation', 'sidebar', 'links', 'dailybuddy');
    }

    protected function register_controls()
    {
        $this->register_content_controls();
        $this->register_features_controls();
        $this->register_style_list_controls();
        $this->register_style_active_controls();
        $this->register_style_search_controls();
        $this->register_style_tooltip_controls();
    }

    // ─────────────────────────────────────────────
    //  Content Tab — Posts
    // ─────────────────────────────────────────────

    private function register_content_controls()
    {
        $this->start_controls_section(
            'section_posts',
            array(
                'label' => __('Posts', 'dailybuddy'),
            )
        );

        $categories = $this->get_category_options();

        $this->add_control(
            'category',
            array(
                'label'       => __('Category', 'dailybuddy'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $categories,
                'default'     => '',
                'label_block' => true,
                'description' => __('Select the category whose posts to display.', 'dailybuddy'),
            )
        );

        $this->add_control(
            'order_by',
            array(
                'label'   => __('Order By', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'title',
                'options' => array(
                    'title'      => __('Title (A-Z)', 'dailybuddy'),
                    'date'       => __('Date', 'dailybuddy'),
                    'menu_order' => __('Menu Order', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'order',
            array(
                'label'   => __('Order', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => array(
                    'ASC'  => __('Ascending', 'dailybuddy'),
                    'DESC' => __('Descending', 'dailybuddy'),
                ),
            )
        );

        $this->add_control(
            'max_posts',
            array(
                'label'       => __('Max Posts', 'dailybuddy'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => -1,
                'min'         => -1,
                'description' => __('-1 = show all posts.', 'dailybuddy'),
            )
        );

        $this->end_controls_section();

        // --- Active State Section ---
        $this->start_controls_section(
            'section_active_state',
            array(
                'label' => __('Active State', 'dailybuddy'),
            )
        );

        $this->add_control(
            'highlight_style',
            array(
                'label'   => __('Highlight Style', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'border',
                'options' => array(
                    'bold'        => __('Bold', 'dailybuddy'),
                    'background'  => __('Background', 'dailybuddy'),
                    'border'      => __('Border Left', 'dailybuddy'),
                    'bold-border' => __('Bold + Border Left', 'dailybuddy'),
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Content Tab — Features
    // ─────────────────────────────────────────────

    private function register_features_controls()
    {
        $this->start_controls_section(
            'section_features',
            array(
                'label' => __('Features', 'dailybuddy'),
            )
        );

        $this->add_control(
            'show_search',
            array(
                'label'       => __('Search Filter', 'dailybuddy'),
                'type'        => Controls_Manager::SWITCHER,
                'label_on'    => __('Yes', 'dailybuddy'),
                'label_off'   => __('No', 'dailybuddy'),
                'default'     => '',
                'description' => __('Adds a live search field above the list to filter posts by title.', 'dailybuddy'),
            )
        );

        $this->add_control(
            'search_placeholder',
            array(
                'label'     => __('Search Placeholder', 'dailybuddy'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('Search...', 'dailybuddy'),
                'condition' => array('show_search' => 'yes'),
            )
        );

        $this->add_control(
            'show_excerpt',
            array(
                'label'       => __('Excerpt Tooltip', 'dailybuddy'),
                'type'        => Controls_Manager::SWITCHER,
                'label_on'    => __('Yes', 'dailybuddy'),
                'label_off'   => __('No', 'dailybuddy'),
                'default'     => '',
                'description' => __('Shows the post excerpt as a tooltip on hover.', 'dailybuddy'),
            )
        );

        $this->add_control(
            'excerpt_length',
            array(
                'label'     => __('Excerpt Length (words)', 'dailybuddy'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 20,
                'min'       => 5,
                'max'       => 80,
                'condition' => array('show_excerpt' => 'yes'),
            )
        );

        $this->add_control(
            'show_post_count',
            array(
                'label'       => __('Post Count Badge', 'dailybuddy'),
                'type'        => Controls_Manager::SWITCHER,
                'label_on'    => __('Yes', 'dailybuddy'),
                'label_off'   => __('No', 'dailybuddy'),
                'default'     => '',
                'description' => __('Shows total number of posts above the list.', 'dailybuddy'),
            )
        );

        $this->add_control(
            'scroll_to_active',
            array(
                'label'       => __('Scroll to Active', 'dailybuddy'),
                'type'        => Controls_Manager::SWITCHER,
                'label_on'    => __('Yes', 'dailybuddy'),
                'label_off'   => __('No', 'dailybuddy'),
                'default'     => '',
                'description' => __('Automatically scrolls long lists to the active item.', 'dailybuddy'),
            )
        );

        $this->add_control(
            'max_height',
            array(
                'label'      => __('Max Height', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'vh'),
                'range'      => array(
                    'px' => array('min' => 100, 'max' => 1000),
                    'vh' => array('min' => 10, 'max' => 100),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-cpl-list' => 'max-height: {{SIZE}}{{UNIT}}; overflow-y: auto;',
                ),
                'description' => __('Set a max height to make the list scrollable.', 'dailybuddy'),
            )
        );

        $this->add_control(
            'heading_responsive',
            array(
                'label'     => __('Responsive', 'dailybuddy'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'mobile_select',
            array(
                'label'       => __('Mobile: Show as Dropdown', 'dailybuddy'),
                'type'        => Controls_Manager::SWITCHER,
                'label_on'    => __('Yes', 'dailybuddy'),
                'label_off'   => __('No', 'dailybuddy'),
                'default'     => 'yes',
                'description' => __('On mobile, the list becomes a native dropdown with integrated search.', 'dailybuddy'),
            )
        );

        $this->add_control(
            'mobile_breakpoint',
            array(
                'label'     => __('Breakpoint (px)', 'dailybuddy'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 767,
                'min'       => 320,
                'max'       => 1024,
                'condition' => array('mobile_select' => 'yes'),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — List
    // ─────────────────────────────────────────────

    private function register_style_list_controls()
    {
        $this->start_controls_section(
            'section_style_list',
            array(
                'label' => __('List', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'link_style',
            array(
                'label'   => __('Link Style', 'dailybuddy'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'plain',
                'options' => array(
                    'plain'   => __('Plain', 'dailybuddy'),
                    'button'  => __('Button', 'dailybuddy'),
                    'pill'    => __('Pill', 'dailybuddy'),
                    'card'    => __('Card', 'dailybuddy'),
                    'minimal' => __('Minimal (underline)', 'dailybuddy'),
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'list_typography',
                'label'    => __('Typography', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-cpl-link',
            )
        );

        $this->add_control(
            'text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-link' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'hover_color',
            array(
                'label'     => __('Hover Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#5d3dfd',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-link:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'link_bg_color',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-link' => 'background-color: {{VALUE}};',
                ),
                'condition' => array(
                    'link_style' => array('button', 'pill', 'card'),
                ),
            )
        );

        $this->add_control(
            'link_hover_bg_color',
            array(
                'label'     => __('Hover Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-link:hover' => 'background-color: {{VALUE}};',
                ),
                'condition' => array(
                    'link_style' => array('button', 'pill', 'card'),
                ),
            )
        );

        $this->add_control(
            'link_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 30),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-cpl-link' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
                'condition'  => array(
                    'link_style' => array('button', 'pill', 'card'),
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'      => 'link_border',
                'label'     => __('Border', 'dailybuddy'),
                'selector'  => '{{WRAPPER}} .dailybuddy-cpl-style-card .dailybuddy-cpl-link, {{WRAPPER}} .dailybuddy-cpl-style-button .dailybuddy-cpl-link',
                'condition' => array(
                    'link_style' => array('button', 'card'),
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'      => 'link_shadow',
                'label'     => __('Box Shadow', 'dailybuddy'),
                'selector'  => '{{WRAPPER}} .dailybuddy-cpl-style-card .dailybuddy-cpl-link',
                'condition' => array(
                    'link_style' => 'card',
                ),
            )
        );

        $this->add_responsive_control(
            'item_spacing',
            array(
                'label'      => __('Spacing', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'separator'  => 'before',
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 40),
                ),
                'default'    => array('unit' => 'px', 'size' => 0),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-cpl-item + .dailybuddy-cpl-item' => 'margin-top: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'item_padding',
            array(
                'label'      => __('Padding', 'dailybuddy'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em'),
                'default'    => array(
                    'top' => '8', 'right' => '12', 'bottom' => '8', 'left' => '12', 'unit' => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-cpl-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — Active Item
    // ─────────────────────────────────────────────

    private function register_style_active_controls()
    {
        $this->start_controls_section(
            'section_style_active',
            array(
                'label' => __('Active Item', 'dailybuddy'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'active_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#5d3dfd',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-active .dailybuddy-cpl-link' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'active_bg_color',
            array(
                'label'     => __('Background Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#f0ecff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-active .dailybuddy-cpl-link' => 'background-color: {{VALUE}};',
                ),
                'condition' => array(
                    'highlight_style' => 'background',
                ),
            )
        );

        $this->add_control(
            'active_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#5d3dfd',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-active .dailybuddy-cpl-link' => 'border-left-color: {{VALUE}};',
                ),
                'condition' => array(
                    'highlight_style' => array('border', 'bold-border'),
                ),
            )
        );

        $this->add_control(
            'active_border_width',
            array(
                'label'      => __('Border Width', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 1, 'max' => 8),
                ),
                'default'    => array('unit' => 'px', 'size' => 3),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-cpl-active .dailybuddy-cpl-link' => 'border-left-width: {{SIZE}}{{UNIT}};',
                ),
                'condition'  => array(
                    'highlight_style' => array('border', 'bold-border'),
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — Search
    // ─────────────────────────────────────────────

    private function register_style_search_controls()
    {
        $this->start_controls_section(
            'section_style_search',
            array(
                'label'     => __('Search', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_search' => 'yes'),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'search_typography',
                'label'    => __('Typography', 'dailybuddy'),
                'selector' => '{{WRAPPER}} .dailybuddy-cpl-search',
            )
        );

        $this->add_control(
            'search_bg_color',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#f5f5f5',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-search' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'search_border_color',
            array(
                'label'     => __('Border Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#e0e0e0',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-search' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'search_border_radius',
            array(
                'label'      => __('Border Radius', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 0, 'max' => 20),
                ),
                'default'    => array('unit' => 'px', 'size' => 6),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-cpl-search' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Style Tab — Tooltip
    // ─────────────────────────────────────────────

    private function register_style_tooltip_controls()
    {
        $this->start_controls_section(
            'section_style_tooltip',
            array(
                'label'     => __('Excerpt Tooltip', 'dailybuddy'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_excerpt' => 'yes'),
            )
        );

        $this->add_control(
            'tooltip_bg_color',
            array(
                'label'     => __('Background', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-tooltip' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .dailybuddy-cpl-tooltip::after' => 'border-right-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'tooltip_text_color',
            array(
                'label'     => __('Text Color', 'dailybuddy'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dailybuddy-cpl-tooltip' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'tooltip_max_width',
            array(
                'label'      => __('Max Width', 'dailybuddy'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 150, 'max' => 500),
                ),
                'default'    => array('unit' => 'px', 'size' => 280),
                'selectors'  => array(
                    '{{WRAPPER}} .dailybuddy-cpl-tooltip' => 'max-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────

    private function get_category_options()
    {
        $options    = array('' => __('— Select —', 'dailybuddy'));
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        foreach ($categories as $cat) {
            $options[$cat->term_id] = $cat->name . ' (' . $cat->count . ')';
        }

        return $options;
    }

    private function get_trimmed_excerpt($post_id, $length = 20)
    {
        $excerpt = get_the_excerpt($post_id);
        if (empty($excerpt)) {
            $post    = get_post($post_id);
            $excerpt = $post ? $post->post_content : '';
        }
        $excerpt = wp_strip_all_tags($excerpt);
        $words   = explode(' ', $excerpt);
        if (count($words) > $length) {
            $words   = array_slice($words, 0, $length);
            $excerpt = implode(' ', $words) . ' …';
        }
        return $excerpt;
    }

    // ─────────────────────────────────────────────
    //  Render
    // ─────────────────────────────────────────────

    protected function render()
    {
        $settings        = $this->get_settings_for_display();
        $category_id     = absint($settings['category']);
        $highlight_style = $settings['highlight_style'];
        $link_style      = !empty($settings['link_style']) ? $settings['link_style'] : 'plain';
        $show_search     = $settings['show_search'] === 'yes';
        $show_excerpt    = $settings['show_excerpt'] === 'yes';
        $show_count      = $settings['show_post_count'] === 'yes';
        $scroll_active   = $settings['scroll_to_active'] === 'yes';
        $mobile_select   = !empty($settings['mobile_select']) && $settings['mobile_select'] === 'yes';
        $mobile_bp       = absint($settings['mobile_breakpoint']) ?: 767;
        $excerpt_length  = absint($settings['excerpt_length']) ?: 20;

        if (empty($category_id)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<p style="opacity:.6;text-align:center;">' . esc_html__('Please select a category.', 'dailybuddy') . '</p>';
            }
            return;
        }

        $query_args = array(
            'cat'            => $category_id,
            'orderby'        => $settings['order_by'],
            'order'          => $settings['order'],
            'posts_per_page' => intval($settings['max_posts']),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        );

        $query = new \WP_Query($query_args);

        if (!$query->have_posts()) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<p style="opacity:.6;text-align:center;">' . esc_html__('No posts found in this category.', 'dailybuddy') . '</p>';
            }
            wp_reset_postdata();
            return;
        }

        $current_post_id = get_the_ID();
        $highlight_class = 'dailybuddy-cpl-highlight-' . esc_attr($highlight_style);
        $style_class     = 'dailybuddy-cpl-style-' . esc_attr($link_style);

        // Collect posts for both list and select rendering.
        $posts_data = array();
        while ($query->have_posts()) {
            $query->the_post();
            $posts_data[] = array(
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'permalink' => get_permalink(),
                'is_active' => (get_the_ID() === $current_post_id),
                'excerpt'   => $show_excerpt ? $this->get_trimmed_excerpt(get_the_ID(), $excerpt_length) : '',
            );
        }
        wp_reset_postdata();

        $data_attrs = '';
        if ($scroll_active) {
            $data_attrs .= ' data-scroll-active="1"';
        }
        if ($show_excerpt) {
            $data_attrs .= ' data-show-excerpt="1"';
        }
        if ($mobile_select) {
            $data_attrs .= ' data-mobile-select="1" data-mobile-bp="' . esc_attr($mobile_bp) . '"';
        }
        ?>
        <div class="dailybuddy-cpl-wrap <?php echo esc_attr($style_class); ?>"<?php echo wp_kses_post($data_attrs); ?>>

            <?php if ($show_count) : ?>
                <div class="dailybuddy-cpl-count">
                    <?php
                    /* translators: %d: number of posts */
                    printf(esc_html__('%d Posts', 'dailybuddy'), count($posts_data));
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($show_search) : ?>
                <div class="dailybuddy-cpl-search-wrap">
                    <input
                        type="text"
                        class="dailybuddy-cpl-search"
                        placeholder="<?php echo esc_attr($settings['search_placeholder']); ?>"
                        autocomplete="off"
                    >
                </div>
            <?php endif; ?>

            <!-- Desktop: List -->
            <ul class="dailybuddy-cpl-list <?php echo esc_attr($highlight_class); ?>">
                <?php foreach ($posts_data as $p) : ?>
                    <li class="dailybuddy-cpl-item <?php echo $p['is_active'] ? 'dailybuddy-cpl-active' : ''; ?>"
                        <?php if ($show_excerpt && $p['excerpt']) : ?>
                            data-excerpt="<?php echo esc_attr($p['excerpt']); ?>"
                        <?php endif; ?>
                    >
                        <a href="<?php echo esc_url($p['permalink']); ?>" class="dailybuddy-cpl-link">
                            <span class="dailybuddy-cpl-link-text"><?php echo esc_html($p['title']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($mobile_select) : ?>
                <!-- Mobile: Searchable dropdown (uses <details> for no-JS open/close) -->
                <details class="dailybuddy-cpl-mobile dailybuddy-cpl-dropdown">
                    <summary class="dailybuddy-cpl-dropdown-toggle">
                        <span class="dailybuddy-cpl-dropdown-label">
                            <?php
                            $active_title = '';
                            foreach ($posts_data as $p) {
                                if ($p['is_active']) {
                                    $active_title = $p['title'];
                                    break;
                                }
                            }
                            echo esc_html($active_title ?: $posts_data[0]['title']);
                            ?>
                        </span>
                        <svg class="dailybuddy-cpl-dropdown-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </summary>
                    <div class="dailybuddy-cpl-dropdown-panel">
                        <?php if ($show_search) : ?>
                            <div class="dailybuddy-cpl-dropdown-search-wrap">
                                <input
                                    type="text"
                                    class="dailybuddy-cpl-dropdown-search"
                                    placeholder="<?php echo esc_attr($settings['search_placeholder']); ?>"
                                    autocomplete="off"
                                >
                            </div>
                        <?php endif; ?>
                        <ul class="dailybuddy-cpl-dropdown-list">
                            <?php foreach ($posts_data as $p) : ?>
                                <li class="dailybuddy-cpl-dropdown-item <?php echo $p['is_active'] ? 'dailybuddy-cpl-dropdown-active' : ''; ?>">
                                    <a href="<?php echo esc_url($p['permalink']); ?>" class="dailybuddy-cpl-dropdown-link">
                                        <?php echo esc_html($p['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="dailybuddy-cpl-dropdown-empty" style="display:none;">
                            <?php esc_html_e('No matches found.', 'dailybuddy'); ?>
                        </div>
                    </div>
                </details>
            <?php endif; ?>

            <div class="dailybuddy-cpl-no-results" style="display:none;">
                <?php esc_html_e('No matches found.', 'dailybuddy'); ?>
            </div>
        </div>
        <?php
    }
}
