<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default homepage settings.
 */
function mpc_get_homepage_defaults() {
    return [
        // Hero.
        'hero_enabled' => 1,
        'hero_heading' => get_bloginfo('name'),
        'hero_layout' => 'split',
        'hero_overlay_opacity' => 45,
        'hero_text' => 'A reusable WordPress theme for bands, artists, and music projects.',
        'hero_overlay_style' => 'side',
        'hero_content_position' => 'bottom_left',   
        'hero_mobile_image_id' => 0,
        'hero_desktop_video_id' => 0,
        'hero_cta_text' => '',
        'hero_cta_url' => '',

        // Featured Content.
        'featured_enabled' => 1,
        'featured_heading' => 'Featured Content',
        'featured_label' => 'Latest Release',
        'featured_title' => '',
        'featured_text' => '',
        'featured_image_id' => 0,
        'featured_cta_text' => '',
        'featured_cta_url' => '',
        'featured_show_quote' => 1,
        'featured_media_type' => 'image',
        'featured_video_url' => '',

        // Blog.
        'blog_enabled' => 1,
        'blog_heading' => 'Blog',
        'blog_posts_per_page' => 2,
        'blog_read_more_text' => 'Read More',
        'blog_view_all_text' => 'View All Posts',
        'blog_view_all_url' => '/blog',
    ];
}

/**
 * Get all homepage settings.
 */
function mpc_get_homepage_settings() {
    $saved = get_option('mpc_homepage_settings', []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, mpc_get_homepage_defaults());
}

/**
 * Get one homepage setting.
 */
function mpc_get_homepage_setting($key, $default = '') {
    $settings = mpc_get_homepage_settings();

    return isset($settings[$key]) ? $settings[$key] : $default;
}

function mpc_sanitize_featured_video_url($url) {
    $url = esc_url_raw(trim((string) $url));

    if (!$url) {
        return '';
    }

    $host = wp_parse_url($url, PHP_URL_HOST);

    if (!$host) {
        return '';
    }

    $host = strtolower($host);
    $host = preg_replace('/^www\./', '', $host);

    $allowed_hosts = [
        'youtube.com',
        'youtu.be',
        'vimeo.com',
        'player.vimeo.com',
    ];

    return in_array($host, $allowed_hosts, true) ? $url : '';
}

/**
 * Sanitize homepage settings before saving.
 */
function mpc_sanitize_homepage_settings($input) {
    $defaults = mpc_get_homepage_defaults();
    $output = [];

    // Hero.
    $allowed_hero_layouts = ['split', 'full_bleed'];

    $output['hero_layout'] = isset($input['hero_layout'])
        ? sanitize_key($input['hero_layout'])
        : 'split';

    if (!in_array($output['hero_layout'], $allowed_hero_layouts, true)) {
        $output['hero_layout'] = 'split';
    }
    
    $allowed_hero_overlay_styles = ['side', 'bottom', 'center', 'even'];
    $allowed_hero_content_positions = ['bottom_left', 'center_left', 'bottom_center', 'center_center'];

    $output['hero_overlay_style'] = isset($input['hero_overlay_style'])
        ? sanitize_key($input['hero_overlay_style'])
        : 'side';

    if (!in_array($output['hero_overlay_style'], $allowed_hero_overlay_styles, true)) {
        $output['hero_overlay_style'] = 'side';
    }

    $output['hero_content_position'] = isset($input['hero_content_position'])
        ? sanitize_key($input['hero_content_position'])
        : 'bottom_left';

    if (!in_array($output['hero_content_position'], $allowed_hero_content_positions, true)) {
        $output['hero_content_position'] = 'bottom_left';
    } 

    $output['hero_overlay_opacity'] = isset($input['hero_overlay_opacity'])
    ? min(100, max(0, absint($input['hero_overlay_opacity'])))
    : 45;

    $output['hero_enabled'] = !empty($input['hero_enabled']) ? 1 : 0;

    $output['hero_heading'] = isset($input['hero_heading'])
        ? sanitize_text_field($input['hero_heading'])
        : $defaults['hero_heading'];

    $output['hero_text'] = isset($input['hero_text'])
        ? sanitize_textarea_field($input['hero_text'])
        : $defaults['hero_text'];

    $output['hero_mobile_image_id'] = isset($input['hero_mobile_image_id'])
        ? absint($input['hero_mobile_image_id'])
        : 0;

    $output['hero_desktop_video_id'] = isset($input['hero_desktop_video_id'])
        ? absint($input['hero_desktop_video_id'])
        : 0;

    $output['hero_cta_text'] = isset($input['hero_cta_text'])
        ? sanitize_text_field($input['hero_cta_text'])
        : '';

    $output['hero_cta_url'] = isset($input['hero_cta_url'])
        ? esc_url_raw($input['hero_cta_url'])
        : '';   

    // Featured Content.
    $output['featured_enabled'] = !empty($input['featured_enabled']) ? 1 : 0;

    $output['featured_heading'] = isset($input['featured_heading'])
        ? sanitize_text_field($input['featured_heading'])
        : $defaults['featured_heading'];

    $output['featured_label'] = isset($input['featured_label'])
        ? sanitize_text_field($input['featured_label'])
        : $defaults['featured_label'];

    $output['featured_title'] = isset($input['featured_title'])
        ? sanitize_text_field($input['featured_title'])
        : '';

    $output['featured_text'] = isset($input['featured_text'])
        ? sanitize_textarea_field($input['featured_text'])
        : '';

    $output['featured_image_id'] = isset($input['featured_image_id'])
        ? absint($input['featured_image_id'])
        : 0;

    $output['featured_cta_text'] = isset($input['featured_cta_text'])
        ? sanitize_text_field($input['featured_cta_text'])
        : '';

    $output['featured_cta_url'] = isset($input['featured_cta_url'])
        ? esc_url_raw($input['featured_cta_url'])
        : '';

    $output['featured_show_quote'] = !empty($input['featured_show_quote']) ? 1 : 0;

    $allowed_featured_media_types = ['image', 'video'];

    $output['featured_media_type'] = isset($input['featured_media_type'])
        ? sanitize_key($input['featured_media_type'])
        : 'image';

    if (!in_array($output['featured_media_type'], $allowed_featured_media_types, true)) {
        $output['featured_media_type'] = 'image';
    }

    $output['featured_video_url'] = isset($input['featured_video_url'])
        ? mpc_sanitize_featured_video_url($input['featured_video_url'])
        : '';

    // Blog.
    $output['blog_enabled'] = !empty($input['blog_enabled']) ? 1 : 0;

    $output['blog_heading'] = isset($input['blog_heading'])
        ? sanitize_text_field($input['blog_heading'])
        : $defaults['blog_heading'];

    $output['blog_posts_per_page'] = isset($input['blog_posts_per_page'])
        ? absint($input['blog_posts_per_page'])
        : $defaults['blog_posts_per_page'];

    if ($output['blog_posts_per_page'] < 1) {
        $output['blog_posts_per_page'] = 1;
    }

    if ($output['blog_posts_per_page'] > 12) {
        $output['blog_posts_per_page'] = 12;
    }

    $output['blog_read_more_text'] = isset($input['blog_read_more_text'])
        ? sanitize_text_field($input['blog_read_more_text'])
        : $defaults['blog_read_more_text'];

    $output['blog_view_all_text'] = isset($input['blog_view_all_text'])
        ? sanitize_text_field($input['blog_view_all_text'])
        : $defaults['blog_view_all_text'];

    $output['blog_view_all_url'] = isset($input['blog_view_all_url'])
        ? esc_url_raw($input['blog_view_all_url'])
        : $defaults['blog_view_all_url'];
        
    return $output;
}

/**
 * Register settings.
 */
function mpc_register_homepage_settings() {
    register_setting(
        'mpc_homepage_settings_group',
        'mpc_homepage_settings',
        [
            'sanitize_callback' => 'mpc_sanitize_homepage_settings',
        ]
    );
}
add_action('admin_init', 'mpc_register_homepage_settings');

/**
 * Add admin menu.
 */
function mpc_add_admin_menu() {
    add_menu_page(
        __('Music Project', 'music-project-core'),
        __('Music Project', 'music-project-core'),
        'manage_options',
        'mpc-homepage',
        'mpc_render_homepage_settings_page',
        'dashicons-album',
        30
    );

    add_submenu_page(
        'mpc-homepage',
        __('Homepage', 'music-project-core'),
        __('Homepage', 'music-project-core'),
        'manage_options',
        'mpc-homepage',
        'mpc_render_homepage_settings_page'
    );
}
add_action('admin_menu', 'mpc_add_admin_menu', 5);

/**
 * Enqueue admin assets only on our settings page.
 */
function mpc_enqueue_admin_assets($hook) {
    if ($hook !== 'toplevel_page_mpc-homepage') {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_script(
        'mpc-admin',
        MPC_URL . 'assets/admin.js',
        ['jquery'],
        MPC_VERSION,
        true
    );

    wp_enqueue_style(
        'mpc-admin',
        MPC_URL . 'assets/admin.css',
        [],
        MPC_VERSION
    );
}
add_action('admin_enqueue_scripts', 'mpc_enqueue_admin_assets');

/**
 * Render reusable media upload field.
 */
function mpc_render_media_field($field_name, $attachment_id, $media_type = 'image') {
    $attachment_id = absint($attachment_id);
    $field_id = 'mpc_' . sanitize_key($field_name);
    $preview = '';

    if ($attachment_id) {
        if ($media_type === 'image') {
            $preview = wp_get_attachment_image($attachment_id, 'medium');
        } else {
            $url = wp_get_attachment_url($attachment_id);
            $preview = $url ? '<p><strong>Selected file:</strong><br>' . esc_html(basename($url)) . '</p>' : '';
        }
    }
    ?>

    <div class="mpc-media-field">
        <input
            type="hidden"
            id="<?php echo esc_attr($field_id); ?>"
            name="mpc_homepage_settings[<?php echo esc_attr($field_name); ?>]"
            value="<?php echo esc_attr($attachment_id); ?>"
        >

        <div class="mpc-media-preview" data-preview-for="<?php echo esc_attr($field_id); ?>">
            <?php echo $preview; ?>
        </div>

        <button
            type="button"
            class="button mpc-media-upload"
            data-target="<?php echo esc_attr($field_id); ?>"
            data-type="<?php echo esc_attr($media_type); ?>"
        >
            <?php echo $attachment_id ? esc_html__('Replace File', 'music-project-core') : esc_html__('Choose File', 'music-project-core'); ?>
        </button>

        <button
            type="button"
            class="button mpc-media-remove"
            data-target="<?php echo esc_attr($field_id); ?>"
        >
            <?php esc_html_e('Remove', 'music-project-core'); ?>
        </button>
    </div>

    <?php
}

/**
 * Render Homepage settings page.
 */
function mpc_render_homepage_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_homepage_settings();
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('Music Project Homepage', 'music-project-core'); ?></h1>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_homepage_settings_group'); ?>

            <h2><?php esc_html_e('Hero Section', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Hero', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="hidden" name="mpc_homepage_settings[hero_enabled]" value="0">
                            <input
                                type="checkbox"
                                name="mpc_homepage_settings[hero_enabled]"
                                value="1"
                                <?php checked(1, $settings['hero_enabled']); ?>
                            >
                            <?php esc_html_e('Show hero section on homepage', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="mpc_homepage_hero_layout">Hero Layout</label>
                    </th>
                    <td>
                        <select
                            id="mpc_homepage_hero_layout"
                            class="mpc-hero-layout-select"
                            name="mpc_homepage_settings[hero_layout]"
                        >
                            <option value="split" <?php selected($settings['hero_layout'], 'split'); ?>>
                                Split Media / Text
                            </option>
                            <option value="full_bleed" <?php selected($settings['hero_layout'], 'full_bleed'); ?>>
                                Full-Bleed Media
                            </option>
                        </select>

                        <p class="description">
                            Split shows media beside the text. Full-Bleed places text over a large background image or video.
                        </p>

                        <div class="mpc-admin-helper mpc-admin-helper--split">
                            <strong>Split Media / Text:</strong>
                            Best for a traditional landing section with clear text and a framed image or video.
                        </div>

                        <div class="mpc-admin-helper mpc-admin-helper--full-bleed">
                            <strong>Full-Bleed Media:</strong>
                            Best for a cinematic hero. Uses desktop video when available; otherwise falls back to the hero image.
                        </div>
                    </td>
                </tr>

                <tr class="mpc-conditional-row mpc-hero-full-bleed-row">
                    <th scope="row">
                        <label for="mpc_homepage_hero_overlay_opacity">Hero Overlay Strength</label>
                    </th>
                    <td>
                        <input
                            id="mpc_homepage_hero_overlay_opacity"
                            type="range"
                            min="0"
                            max="100"
                            step="5"
                            name="mpc_homepage_settings[hero_overlay_opacity]"
                            value="<?php echo esc_attr($settings['hero_overlay_opacity']); ?>"
                            oninput="this.nextElementSibling.value = this.value"
                        >
                        <output><?php echo esc_html($settings['hero_overlay_opacity']); ?></output>

                        <p class="description">
                            Controls the darkness of the full-bleed hero overlay. Lower numbers show more image/video.
                        </p>
                    </td>
                </tr>

                <tr class="mpc-conditional-row mpc-hero-full-bleed-row">
                    <th scope="row">
                        <label for="mpc_homepage_hero_overlay_style">Hero Overlay Style</label>
                    </th>
                    <td>
                        <select
                            id="mpc_homepage_hero_overlay_style"
                            name="mpc_homepage_settings[hero_overlay_style]"
                        >
                            <option value="side" <?php selected($settings['hero_overlay_style'], 'side'); ?>>
                                Side Gradient
                            </option>
                            <option value="bottom" <?php selected($settings['hero_overlay_style'], 'bottom'); ?>>
                                Bottom Gradient
                            </option>
                            <option value="center" <?php selected($settings['hero_overlay_style'], 'center'); ?>>
                                Center Vignette
                            </option>
                            <option value="even" <?php selected($settings['hero_overlay_style'], 'even'); ?>>
                                Even Wash
                            </option>
                        </select>

                        <p class="description">
                            Controls the direction/style of the full-bleed hero overlay.
                        </p>
                    </td>
                </tr>

                <tr class="mpc-conditional-row mpc-hero-full-bleed-row">
                    <th scope="row">
                        <label for="mpc_homepage_hero_content_position">Hero Content Position</label>
                    </th>
                    <td>
                        <select
                            id="mpc_homepage_hero_content_position"
                            name="mpc_homepage_settings[hero_content_position]"
                        >
                            <option value="bottom_left" <?php selected($settings['hero_content_position'], 'bottom_left'); ?>>
                                Bottom Left
                            </option>
                            <option value="center_left" <?php selected($settings['hero_content_position'], 'center_left'); ?>>
                                Center Left
                            </option>
                            <option value="bottom_center" <?php selected($settings['hero_content_position'], 'bottom_center'); ?>>
                                Bottom Center
                            </option>
                            <option value="center_center" <?php selected($settings['hero_content_position'], 'center_center'); ?>>
                                Center Center
                            </option>
                        </select>

                        <p class="description">
                            Controls where the text sits inside the full-bleed hero.
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="hero_heading"><?php esc_html_e('Hero Heading', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="hero_heading"
                            name="mpc_homepage_settings[hero_heading]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['hero_heading']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="hero_text"><?php esc_html_e('Hero Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="hero_text"
                            name="mpc_homepage_settings[hero_text]"
                            class="large-text"
                            rows="4"
                        ><?php echo esc_textarea($settings['hero_text']); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Mobile Hero Image', 'music-project-core'); ?>
                    </th>
                    <td>
                        <?php mpc_render_media_field('hero_mobile_image_id', $settings['hero_mobile_image_id'], 'image'); ?>
                        <p class="description">
                            <?php esc_html_e('Used for mobile and fallback display.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Desktop Hero Video', 'music-project-core'); ?>
                    </th>
                    <td>
                        <?php mpc_render_media_field('hero_desktop_video_id', $settings['hero_desktop_video_id'], 'video'); ?>
                        <p class="description">
                            <?php esc_html_e('MP4 recommended. Used for desktop hero background/video area.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="hero_cta_text"><?php esc_html_e('CTA Button Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="hero_cta_text"
                            name="mpc_homepage_settings[hero_cta_text]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['hero_cta_text']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="hero_cta_url"><?php esc_html_e('CTA Button URL', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="url"
                            id="hero_cta_url"
                            name="mpc_homepage_settings[hero_cta_url]"
                            class="regular-text"
                            value="<?php echo esc_url($settings['hero_cta_url']); ?>"
                        >
                    </td>
                </tr>
            </table>

            <hr>

            <h2><?php esc_html_e('Featured Content', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Featured Content', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="hidden" name="mpc_homepage_settings[featured_enabled]" value="0">
                            <input
                                type="checkbox"
                                name="mpc_homepage_settings[featured_enabled]"
                                value="1"
                                <?php checked(1, $settings['featured_enabled']); ?>
                            >
                            <?php esc_html_e('Show featured content section on homepage', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="featured_heading"><?php esc_html_e('Section Heading', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="featured_heading"
                            name="mpc_homepage_settings[featured_heading]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['featured_heading']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="featured_label"><?php esc_html_e('Featured Label', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="featured_label"
                            name="mpc_homepage_settings[featured_label]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['featured_label']); ?>"
                            placeholder="<?php esc_attr_e('Latest Release, New Video, Announcement, etc.', 'music-project-core'); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="featured_title"><?php esc_html_e('Featured Title', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="featured_title"
                            name="mpc_homepage_settings[featured_title]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['featured_title']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="featured_text"><?php esc_html_e('Featured Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="featured_text"
                            name="mpc_homepage_settings[featured_text]"
                            class="large-text"
                            rows="4"
                        ><?php echo esc_textarea($settings['featured_text']); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Featured Image', 'music-project-core'); ?>
                    </th>
                    <td>
                        <?php mpc_render_media_field('featured_image_id', $settings['featured_image_id'], 'image'); ?>
                        <p class="description">
                            <?php esc_html_e('Used for the manual featured content card.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="mpc_homepage_featured_media_type">Featured Media Type</label>
                    </th>
                    <td>
                        <select
                            id="mpc_homepage_featured_media_type"
                            name="mpc_homepage_settings[featured_media_type]"
                        >
                            <option value="image" <?php selected($settings['featured_media_type'], 'image'); ?>>
                                Image / Artwork
                            </option>
                            <option value="video" <?php selected($settings['featured_media_type'], 'video'); ?>>
                                Video Embed
                            </option>
                        </select>

                        <p class="description">
                            Choose whether the featured media area shows an uploaded image or a YouTube/Vimeo video.
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="mpc_homepage_featured_video_url">Featured Video URL</label>
                    </th>
                    <td>
                        <input
                            id="mpc_homepage_featured_video_url"
                            class="regular-text"
                            type="url"
                            name="mpc_homepage_settings[featured_video_url]"
                            value="<?php echo esc_url($settings['featured_video_url']); ?>"
                            placeholder="https://www.youtube.com/watch?v=..."
                        >

                        <p class="description">
                            Supports YouTube and Vimeo URLs only. Used when Featured Media Type is set to Video Embed.
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="featured_cta_text"><?php esc_html_e('CTA Button Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="featured_cta_text"
                            name="mpc_homepage_settings[featured_cta_text]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['featured_cta_text']); ?>"
                            placeholder="<?php esc_attr_e('Listen Now, Watch Video, Read More, etc.', 'music-project-core'); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="featured_cta_url"><?php esc_html_e('CTA Button URL', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="url"
                            id="featured_cta_url"
                            name="mpc_homepage_settings[featured_cta_url]"
                            class="regular-text"
                            value="<?php echo esc_url($settings['featured_cta_url']); ?>"
                            placeholder="https://..."
                        >
                        <p class="description">
                            <?php esc_html_e('Can be a streaming link, video link, internal /music URL, or any promo URL.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Show Press Quote', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="hidden" name="mpc_homepage_settings[featured_show_quote]" value="0">
                            <input
                                type="checkbox"
                                name="mpc_homepage_settings[featured_show_quote]"
                                value="1"
                                <?php checked(1, $settings['featured_show_quote']); ?>
                            >
                            <?php esc_html_e('Show the featured press quote beside this content', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <hr>

            <h2><?php esc_html_e('Blog Section', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Blog Section', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="hidden" name="mpc_homepage_settings[blog_enabled]" value="0">
                            <input
                                type="checkbox"
                                name="mpc_homepage_settings[blog_enabled]"
                                value="1"
                                <?php checked(1, $settings['blog_enabled']); ?>
                            >
                            <?php esc_html_e('Show blog section on homepage', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="blog_heading"><?php esc_html_e('Section Heading', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="blog_heading"
                            name="mpc_homepage_settings[blog_heading]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['blog_heading']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="blog_posts_per_page"><?php esc_html_e('Number of Posts', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="number"
                            id="blog_posts_per_page"
                            name="mpc_homepage_settings[blog_posts_per_page]"
                            class="small-text"
                            min="1"
                            max="12"
                            value="<?php echo esc_attr($settings['blog_posts_per_page']); ?>"
                        >
                        <p class="description">
                            <?php esc_html_e('How many recent posts should appear on the homepage. Maximum 12.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="blog_read_more_text"><?php esc_html_e('Post Link Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="blog_read_more_text"
                            name="mpc_homepage_settings[blog_read_more_text]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['blog_read_more_text']); ?>"
                            placeholder="<?php esc_attr_e('Read More', 'music-project-core'); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="blog_view_all_text"><?php esc_html_e('View All Button Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="blog_view_all_text"
                            name="mpc_homepage_settings[blog_view_all_text]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['blog_view_all_text']); ?>"
                            placeholder="<?php esc_attr_e('View All Posts', 'music-project-core'); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="blog_view_all_url"><?php esc_html_e('View All Button URL', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="blog_view_all_url"
                            name="mpc_homepage_settings[blog_view_all_url]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['blog_view_all_url']); ?>"
                            placeholder="/blog"
                        >
                        <p class="description">
                            <?php esc_html_e('Usually /blog or the URL of your posts page.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Homepage Settings', 'music-project-core')); ?>
        </form>
    </div>

    <?php
}