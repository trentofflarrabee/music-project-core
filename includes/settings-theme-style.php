<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default theme style settings.
 */
function mpc_get_theme_style_defaults() {
    return [
        // Colors.
        'color_background' => '#111111',
        'color_surface' => '#101010',
        'color_text' => '#f5f5f5',
        'color_muted' => '#b8b8b8',
        'color_accent' => '#ffffff',
        'color_button_background' => '#f5f5f5',
        'color_button_text' => '#111111',

        // Typography.
        'google_fonts_url' => '',
        'font_heading' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'font_body' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'font_accent' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'heading_text_transform' => 'none',
        'heading_letter_spacing' => '-0.04em',

        // Texture.
        'texture_enabled' => 0,
        'texture_image_id' => 0,
        'texture_opacity' => '0.08',
        'texture_size' => '420px',
        'texture_repeat' => 'repeat',
        'texture_apply_body' => 1,
        'texture_apply_footer' => 1,
        'texture_apply_buttons' => 0,
    ];
}

/**
 * Get all theme style settings.
 */
function mpc_get_theme_style_settings() {
    $saved = get_option('mpc_theme_style_settings', []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, mpc_get_theme_style_defaults());
}

/**
 * Get one theme style setting.
 */
function mpc_get_theme_style_setting($key, $default = '') {
    $settings = mpc_get_theme_style_settings();

    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Sanitize font-family input.
 */
function mpc_sanitize_font_family($value) {
    $value = is_string($value) ? wp_unslash($value) : '';
    $value = wp_strip_all_tags($value);

    // Allow letters, numbers, spaces, commas, quotes, hyphens, underscores, and common font syntax.
    $value = preg_replace('/[^a-zA-Z0-9\s,\-_"\'().]/', '', $value);

    return trim($value);
}

/**
 * Sanitize Google Fonts stylesheet URL.
 */
function mpc_sanitize_google_fonts_url($url) {
    $url = is_string($url) ? trim(wp_unslash($url)) : '';

    if ($url === '') {
        return '';
    }

    $url = esc_url_raw($url);
    $host = wp_parse_url($url, PHP_URL_HOST);

    if ($host !== 'fonts.googleapis.com') {
        return '';
    }

    return $url;
}

/**
 * Sanitize opacity value.
 */
function mpc_sanitize_opacity($value) {
    $value = is_numeric($value) ? (float) $value : 0.08;

    if ($value < 0) {
        $value = 0;
    }

    if ($value > 1) {
        $value = 1;
    }

    return number_format($value, 2, '.', '');
}

/**
 * Sanitize CSS background-size value.
 */
function mpc_sanitize_texture_size($value) {
    $value = is_string($value) ? trim(wp_unslash($value)) : '';

    $allowed_keywords = ['auto', 'cover', 'contain'];

    if (in_array($value, $allowed_keywords, true)) {
        return $value;
    }

    if (preg_match('/^\d+(\.\d+)?(px|rem|em|%)$/', $value)) {
        return $value;
    }

    return '420px';
}

/**
 * Sanitize theme style settings.
 */
function mpc_sanitize_theme_style_settings($input) {
    $defaults = mpc_get_theme_style_defaults();
    $output = [];

    // Colors.
    $color_fields = [
        'color_background',
        'color_surface',
        'color_text',
        'color_muted',
        'color_accent',
        'color_button_background',
        'color_button_text',
    ];

    foreach ($color_fields as $field) {
        $color = isset($input[$field]) ? sanitize_hex_color($input[$field]) : '';
        $output[$field] = $color ?: $defaults[$field];
    }

    // Typography.
    $output['google_fonts_url'] = isset($input['google_fonts_url'])
        ? mpc_sanitize_google_fonts_url($input['google_fonts_url'])
        : '';

    $output['font_heading'] = isset($input['font_heading'])
        ? mpc_sanitize_font_family($input['font_heading'])
        : $defaults['font_heading'];

    $output['font_body'] = isset($input['font_body'])
        ? mpc_sanitize_font_family($input['font_body'])
        : $defaults['font_body'];

    $output['font_accent'] = isset($input['font_accent'])
        ? mpc_sanitize_font_family($input['font_accent'])
        : $defaults['font_accent'];

    $allowed_transforms = ['none', 'uppercase', 'lowercase', 'capitalize'];
    $transform = isset($input['heading_text_transform'])
        ? sanitize_key($input['heading_text_transform'])
        : $defaults['heading_text_transform'];

    $output['heading_text_transform'] = in_array($transform, $allowed_transforms, true)
        ? $transform
        : $defaults['heading_text_transform'];

    $letter_spacing = isset($input['heading_letter_spacing'])
        ? trim(wp_unslash($input['heading_letter_spacing']))
        : $defaults['heading_letter_spacing'];

    $output['heading_letter_spacing'] = preg_match('/^-?\d+(\.\d+)?(px|rem|em)$/', $letter_spacing)
        ? $letter_spacing
        : $defaults['heading_letter_spacing'];

    // Texture.
    $output['texture_enabled'] = !empty($input['texture_enabled']) ? 1 : 0;

    $output['texture_image_id'] = isset($input['texture_image_id'])
        ? absint($input['texture_image_id'])
        : 0;

    $output['texture_opacity'] = isset($input['texture_opacity'])
        ? mpc_sanitize_opacity($input['texture_opacity'])
        : $defaults['texture_opacity'];

    $output['texture_size'] = isset($input['texture_size'])
        ? mpc_sanitize_texture_size($input['texture_size'])
        : $defaults['texture_size'];

    $allowed_repeats = ['repeat', 'no-repeat', 'repeat-x', 'repeat-y'];
    $repeat = isset($input['texture_repeat'])
        ? sanitize_key($input['texture_repeat'])
        : $defaults['texture_repeat'];

    $output['texture_repeat'] = in_array($repeat, $allowed_repeats, true)
        ? $repeat
        : $defaults['texture_repeat'];

    $output['texture_apply_body'] = !empty($input['texture_apply_body']) ? 1 : 0;
    $output['texture_apply_footer'] = !empty($input['texture_apply_footer']) ? 1 : 0;
    $output['texture_apply_buttons'] = !empty($input['texture_apply_buttons']) ? 1 : 0;

    return $output;
}

/**
 * Register settings.
 */
function mpc_register_theme_style_settings() {
    register_setting(
        'mpc_theme_style_settings_group',
        'mpc_theme_style_settings',
        [
            'sanitize_callback' => 'mpc_sanitize_theme_style_settings',
        ]
    );
}
add_action('admin_init', 'mpc_register_theme_style_settings');

/**
 * Add Theme Style submenu.
 */
function mpc_add_theme_style_submenu() {
    add_submenu_page(
        'mpc-homepage',
        __('Theme Style', 'music-project-core'),
        __('Theme Style', 'music-project-core'),
        'manage_options',
        'mpc-theme-style',
        'mpc_render_theme_style_settings_page'
    );
}
add_action('admin_menu', 'mpc_add_theme_style_submenu');

/**
 * Enqueue media uploader on Theme Style page.
 */
function mpc_enqueue_theme_style_admin_assets() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'mpc-theme-style') {
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
add_action('admin_enqueue_scripts', 'mpc_enqueue_theme_style_admin_assets');

/**
 * Render media upload field for Theme Style settings.
 */
function mpc_render_theme_style_media_field($field_name, $attachment_id, $media_type = 'image') {
    $attachment_id = absint($attachment_id);
    $field_id = 'mpc_theme_style_' . sanitize_key($field_name);
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
            name="mpc_theme_style_settings[<?php echo esc_attr($field_name); ?>]"
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
 * Render Theme Style settings page.
 */
function mpc_render_theme_style_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_theme_style_settings();
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('Music Project Theme Style', 'music-project-core'); ?></h1>

        <p>
            <?php esc_html_e('Control reusable visual branding for this music project: colors, typography, and subtle background texture.', 'music-project-core'); ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_theme_style_settings_group'); ?>

            <h2><?php esc_html_e('Colors', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="color_background"><?php esc_html_e('Background Color', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="color_background"
                            name="mpc_theme_style_settings[color_background]"
                            value="<?php echo esc_attr($settings['color_background']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="color_surface"><?php esc_html_e('Surface / Card Color', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="color_surface"
                            name="mpc_theme_style_settings[color_surface]"
                            value="<?php echo esc_attr($settings['color_surface']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="color_text"><?php esc_html_e('Text Color', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="color_text"
                            name="mpc_theme_style_settings[color_text]"
                            value="<?php echo esc_attr($settings['color_text']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="color_muted"><?php esc_html_e('Muted Text Color', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="color_muted"
                            name="mpc_theme_style_settings[color_muted]"
                            value="<?php echo esc_attr($settings['color_muted']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="color_accent"><?php esc_html_e('Accent Color', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="color_accent"
                            name="mpc_theme_style_settings[color_accent]"
                            value="<?php echo esc_attr($settings['color_accent']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="color_button_background"><?php esc_html_e('Button Background', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="color_button_background"
                            name="mpc_theme_style_settings[color_button_background]"
                            value="<?php echo esc_attr($settings['color_button_background']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="color_button_text"><?php esc_html_e('Button Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="color_button_text"
                            name="mpc_theme_style_settings[color_button_text]"
                            value="<?php echo esc_attr($settings['color_button_text']); ?>"
                        >
                    </td>
                </tr>
            </table>

            <hr>

            <h2><?php esc_html_e('Typography', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="google_fonts_url"><?php esc_html_e('Google Fonts Stylesheet URL', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="url"
                            id="google_fonts_url"
                            name="mpc_theme_style_settings[google_fonts_url]"
                            class="large-text"
                            value="<?php echo esc_url($settings['google_fonts_url']); ?>"
                            placeholder="https://fonts.googleapis.com/css2?family=..."
                        >
                        <p class="description">
                            <?php esc_html_e('Optional. Paste the Google Fonts stylesheet URL only, not the full <link> tag.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="font_heading"><?php esc_html_e('Heading Font Family', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="font_heading"
                            name="mpc_theme_style_settings[font_heading]"
                            class="large-text"
                            value="<?php echo esc_attr($settings['font_heading']); ?>"
                        >
                        <p class="description">
                            <?php esc_html_e('Example: "Oswald", Impact, sans-serif', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="font_body"><?php esc_html_e('Body Font Family', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="font_body"
                            name="mpc_theme_style_settings[font_body]"
                            class="large-text"
                            value="<?php echo esc_attr($settings['font_body']); ?>"
                        >
                        <p class="description">
                            <?php esc_html_e('Example: "Lora", Georgia, serif', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="font_accent"><?php esc_html_e('Accent Font Family', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="font_accent"
                            name="mpc_theme_style_settings[font_accent]"
                            class="large-text"
                            value="<?php echo esc_attr($settings['font_accent']); ?>"
                        >
                        <p class="description">
                            <?php esc_html_e('Used for labels, nav, social links, and small accent text.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="heading_text_transform"><?php esc_html_e('Heading Text Transform', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <select
                            id="heading_text_transform"
                            name="mpc_theme_style_settings[heading_text_transform]"
                        >
                            <option value="none" <?php selected($settings['heading_text_transform'], 'none'); ?>>
                                <?php esc_html_e('None', 'music-project-core'); ?>
                            </option>
                            <option value="uppercase" <?php selected($settings['heading_text_transform'], 'uppercase'); ?>>
                                <?php esc_html_e('Uppercase', 'music-project-core'); ?>
                            </option>
                            <option value="lowercase" <?php selected($settings['heading_text_transform'], 'lowercase'); ?>>
                                <?php esc_html_e('Lowercase', 'music-project-core'); ?>
                            </option>
                            <option value="capitalize" <?php selected($settings['heading_text_transform'], 'capitalize'); ?>>
                                <?php esc_html_e('Capitalize', 'music-project-core'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="heading_letter_spacing"><?php esc_html_e('Heading Letter Spacing', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="heading_letter_spacing"
                            name="mpc_theme_style_settings[heading_letter_spacing]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['heading_letter_spacing']); ?>"
                            placeholder="-0.04em"
                        >
                        <p class="description">
                            <?php esc_html_e('Use values like -0.04em, 0.02em, 1px, etc.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <hr>

            <h2><?php esc_html_e('Background Texture', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Texture', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="hidden" name="mpc_theme_style_settings[texture_enabled]" value="0">
                            <input
                                type="checkbox"
                                name="mpc_theme_style_settings[texture_enabled]"
                                value="1"
                                <?php checked(1, $settings['texture_enabled']); ?>
                            >
                            <?php esc_html_e('Enable subtle background texture support', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Texture Image', 'music-project-core'); ?>
                    </th>
                    <td>
                        <?php mpc_render_theme_style_media_field('texture_image_id', $settings['texture_image_id'], 'image'); ?>
                        <p class="description">
                            <?php esc_html_e('Small repeating textures work best. Example: amp cloth, paper grain, scan texture.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="texture_opacity"><?php esc_html_e('Texture Opacity', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="number"
                            id="texture_opacity"
                            name="mpc_theme_style_settings[texture_opacity]"
                            class="small-text"
                            min="0"
                            max="1"
                            step="0.01"
                            value="<?php echo esc_attr($settings['texture_opacity']); ?>"
                        >
                        <p class="description">
                            <?php esc_html_e('Recommended: 0.04 to 0.14 for subtle texture.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="texture_size"><?php esc_html_e('Texture Size', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="texture_size"
                            name="mpc_theme_style_settings[texture_size]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['texture_size']); ?>"
                            placeholder="420px"
                        >
                        <p class="description">
                            <?php esc_html_e('Examples: auto, cover, contain, 300px, 420px, 50%.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="texture_repeat"><?php esc_html_e('Texture Repeat', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <select
                            id="texture_repeat"
                            name="mpc_theme_style_settings[texture_repeat]"
                        >
                            <option value="repeat" <?php selected($settings['texture_repeat'], 'repeat'); ?>>
                                <?php esc_html_e('Repeat', 'music-project-core'); ?>
                            </option>
                            <option value="no-repeat" <?php selected($settings['texture_repeat'], 'no-repeat'); ?>>
                                <?php esc_html_e('No Repeat', 'music-project-core'); ?>
                            </option>
                            <option value="repeat-x" <?php selected($settings['texture_repeat'], 'repeat-x'); ?>>
                                <?php esc_html_e('Repeat X', 'music-project-core'); ?>
                            </option>
                            <option value="repeat-y" <?php selected($settings['texture_repeat'], 'repeat-y'); ?>>
                                <?php esc_html_e('Repeat Y', 'music-project-core'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Apply Texture To', 'music-project-core'); ?>
                    </th>
                    <td>
                        <p>
                            <label>
                                <input type="hidden" name="mpc_theme_style_settings[texture_apply_body]" value="0">
                                <input
                                    type="checkbox"
                                    name="mpc_theme_style_settings[texture_apply_body]"
                                    value="1"
                                    <?php checked(1, $settings['texture_apply_body']); ?>
                                >
                                <?php esc_html_e('Body background / outer margins', 'music-project-core'); ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="hidden" name="mpc_theme_style_settings[texture_apply_footer]" value="0">
                                <input
                                    type="checkbox"
                                    name="mpc_theme_style_settings[texture_apply_footer]"
                                    value="1"
                                    <?php checked(1, $settings['texture_apply_footer']); ?>
                                >
                                <?php esc_html_e('Footer', 'music-project-core'); ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="hidden" name="mpc_theme_style_settings[texture_apply_buttons]" value="0">
                                <input
                                    type="checkbox"
                                    name="mpc_theme_style_settings[texture_apply_buttons]"
                                    value="1"
                                    <?php checked(1, $settings['texture_apply_buttons']); ?>
                                >
                                <?php esc_html_e('Buttons / CTAs', 'music-project-core'); ?>
                            </label>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Theme Style', 'music-project-core')); ?>
        </form>
    </div>

    <?php
}