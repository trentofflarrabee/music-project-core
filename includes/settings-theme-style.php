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

        // Site chrome.
        'header_background_color' => '#000000',
        'header_text_color' => '#f5f5f5',
        'header_border_color' => '#1f1f1f',

        // Site chrome behavior.
        'header_behavior' => 'standard',
        'brand_display' => 'logo_name',

        'mobile_nav_background_color' => '#000000',
        'mobile_nav_text_color' => '#f5f5f5',
        'mobile_nav_border_color' => '#242424',

        'footer_background_color' => '#000000',
        'footer_text_color' => '#f5f5f5',
        'footer_muted_color' => '#b8b8b8',
        'footer_border_color' => '#1f1f1f',



        // Typography font library.
        'google_fonts_url' => '',

        'font_body' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'font_heading' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'font_accent' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'font_quote' => '',

        // Typography role assignments.
        'font_role_body' => 'body',
        'font_role_heading' => 'heading',
        'font_role_blog_heading' => 'heading',
        'font_role_hero_heading' => 'heading',
        'font_role_nav' => 'accent',
        'font_role_button' => 'accent',
        'font_role_accent' => 'accent',
        'font_role_quote' => 'quote',

        // Heading presentation.
        'heading_text_transform' => 'none',
        'heading_letter_spacing' => '-0.04em',
        'heading_alignment_scope' => 'none',

        'hero_heading_color' => '#ffffff',
        'hero_lead_color' => '#f5f5f5',
        'hero_text_shadow' => 'subtle',
        'hero_text_shadow_color' => '#000000',

// Design tokens.
'corner_style'      => 'rounded',
'card_shadow_style' => 'standard',
'border_strength'   => 'subtle',

// Texture V2.
        'texture_enabled' => 0,
        'texture_image_id' => 0,
        'texture_opacity' => '0.14',
        'texture_size' => '420px',
        'texture_repeat' => 'repeat',

        // Current environmental texture zones.
        'texture_apply_header' => 0,
        'texture_apply_mobile_nav' => 1,
        'texture_apply_footer' => 1,
        'texture_apply_sections' => 0,
        'texture_apply_editorial' => 0,

        /*
         * Legacy placement keys.
         *
         * These remain in the data contract so older saved settings are not
         * silently discarded. Texture V2 no longer presents them as primary
         * placement controls.
         */
        'texture_apply_body' => 1,
        'texture_apply_buttons' => 0,
        'texture_apply_cards' => 0,
        'texture_apply_media_frames' => 0,
    ];
}

/**
 * Get all theme style settings.
 */
function mpc_get_theme_style_settings() { $saved = get_option( 'mpc_theme_style_settings', [] ); if (!is_array($saved)) { $saved = []; } $settings = wp_parse_args( $saved, mpc_get_theme_style_defaults() ); /* * Before Texture V2, the broad Body Background option was the closest * equivalent to the new Pages and Posts zone. * * Carry that preference forward only until the new setting has been * explicitly saved. Afterward, the new setting is authoritative. */ if ( !array_key_exists( 'texture_apply_editorial', $saved ) ) { $settings['texture_apply_editorial'] = !empty( $saved['texture_apply_body'] ) ? 1 : 0; } return $settings; }

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
    $input = is_array($input)
        ? $input
        : [];

    $defaults = mpc_get_theme_style_defaults();
    $output = [];

// Core palette colors.
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
    $submitted_value = (
        isset($input[$field])
        && is_scalar($input[$field])
    )
        ? (string) $input[$field]
        : '';

    $color = sanitize_hex_color(
        $submitted_value
    );

    $output[$field] = $color
        ?: $defaults[$field];
}

// Site chrome colors.
$chrome_color_fields = [
    'header_background_color',
    'header_text_color',
    'header_border_color',
    'mobile_nav_background_color',
    'mobile_nav_text_color',
    'mobile_nav_border_color',
    'footer_background_color',
    'footer_text_color',
    'footer_muted_color',
    'footer_border_color',
];

foreach ($chrome_color_fields as $field) {
    $submitted_value = (
        isset($input[$field])
        && is_scalar($input[$field])
    )
        ? (string) $input[$field]
        : '';

    $color = sanitize_hex_color(
        $submitted_value
    );

    $output[$field] = $color
        ?: $defaults[$field];
}

// Site chrome behavior.
$allowed_header_behaviors = [
    'standard',
    'sticky',
    'transparent',
    'transparent_scroll',
];

$header_behavior = (
    isset($input['header_behavior'])
    && is_scalar($input['header_behavior'])
)
    ? sanitize_key(
        (string) $input['header_behavior']
    )
    : $defaults['header_behavior'];

$output['header_behavior'] = in_array(
    $header_behavior,
    $allowed_header_behaviors,
    true
)
    ? $header_behavior
    : $defaults['header_behavior'];

$allowed_brand_displays = [
    'logo_name',
    'logo_only',
    'name_only',
    'hidden',
];

$brand_display = (
    isset($input['brand_display'])
    && is_scalar($input['brand_display'])
)
    ? sanitize_key(
        (string) $input['brand_display']
    )
    : $defaults['brand_display'];

$output['brand_display'] = in_array(
    $brand_display,
    $allowed_brand_displays,
    true
)
    ? $brand_display
    : $defaults['brand_display'];

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

$allowed_transforms = [
    'none',
    'uppercase',
    'lowercase',
    'capitalize',
];

$transform = (
    isset($input['heading_text_transform'])
    && is_scalar(
        $input['heading_text_transform']
    )
)
    ? sanitize_key(
        (string) $input[
            'heading_text_transform'
        ]
    )
    : $defaults['heading_text_transform'];

$output['heading_text_transform'] = in_array(
    $transform,
    $allowed_transforms,
    true
)
    ? $transform
    : $defaults['heading_text_transform'];

$letter_spacing = (
    isset($input['heading_letter_spacing'])
    && is_scalar(
        $input['heading_letter_spacing']
    )
)
    ? trim(
        wp_unslash(
            (string) $input[
                'heading_letter_spacing'
            ]
        )
    )
    : $defaults['heading_letter_spacing'];

$output['heading_letter_spacing'] = preg_match(
    '/^-?\d+(\.\d+)?(px|rem|em)$/',
    $letter_spacing
)
    ? $letter_spacing
    : $defaults['heading_letter_spacing'];

$allowed_heading_alignment_scopes = [
    'none',
    'home',
    'all',
];

$heading_alignment_scope = (
    isset($input['heading_alignment_scope'])
    && is_scalar(
        $input['heading_alignment_scope']
    )
)
    ? sanitize_key(
        (string) $input[
            'heading_alignment_scope'
        ]
    )
    : $defaults[
        'heading_alignment_scope'
    ];

$output['heading_alignment_scope'] = in_array(
    $heading_alignment_scope,
    $allowed_heading_alignment_scopes,
    true
)
    ? $heading_alignment_scope
    : $defaults['heading_alignment_scope'];

$hero_heading_color = (
    isset($input['hero_heading_color'])
    && is_scalar($input['hero_heading_color'])
)
    ? sanitize_hex_color(
        (string) $input['hero_heading_color']
    )
    : '';

$output['hero_heading_color'] =
    $hero_heading_color
        ?: $defaults['hero_heading_color'];

$hero_lead_color = (
    isset($input['hero_lead_color'])
    && is_scalar($input['hero_lead_color'])
)
    ? sanitize_hex_color(
        (string) $input['hero_lead_color']
    )
    : '';

$output['hero_lead_color'] =
    $hero_lead_color
        ?: $defaults['hero_lead_color'];

$allowed_hero_text_shadows = [
    'none',
    'subtle',
    'strong',
];

$hero_text_shadow = (
    isset($input['hero_text_shadow'])
    && is_scalar($input['hero_text_shadow'])
)
    ? sanitize_key(
        (string) $input['hero_text_shadow']
    )
    : $defaults['hero_text_shadow'];

$output['hero_text_shadow'] = in_array(
    $hero_text_shadow,
    $allowed_hero_text_shadows,
    true
)
    ? $hero_text_shadow
    : $defaults['hero_text_shadow'];

$hero_text_shadow_color = (
    isset($input['hero_text_shadow_color'])
    && is_scalar(
        $input['hero_text_shadow_color']
    )
)
    ? sanitize_hex_color(
        (string) $input[
            'hero_text_shadow_color'
        ]
    )
    : '';

$output['hero_text_shadow_color'] =
    $hero_text_shadow_color
        ?: $defaults[
            'hero_text_shadow_color'
        ];

// Design tokens.
$allowed_corner_styles = [
    'sharp',
    'subtle',
    'rounded',
    'soft',
];

$corner_style = (
    isset($input['corner_style'])
    && is_scalar($input['corner_style'])
)
    ? sanitize_key(
        (string) $input['corner_style']
    )
    : $defaults['corner_style'];

$output['corner_style'] = in_array(
    $corner_style,
    $allowed_corner_styles,
    true
)
    ? $corner_style
    : $defaults['corner_style'];

$allowed_card_shadow_styles = [
    'none',
    'subtle',
    'standard',
    'dramatic',
];

$card_shadow_style = (
    isset($input['card_shadow_style'])
    && is_scalar($input['card_shadow_style'])
)
    ? sanitize_key(
        (string) $input['card_shadow_style']
    )
    : $defaults['card_shadow_style'];

$output['card_shadow_style'] = in_array(
    $card_shadow_style,
    $allowed_card_shadow_styles,
    true
)
    ? $card_shadow_style
    : $defaults['card_shadow_style'];

$allowed_border_strengths = [
    'minimal',
    'subtle',
    'defined',
];

$border_strength = (
    isset($input['border_strength'])
    && is_scalar($input['border_strength'])
)
    ? sanitize_key(
        (string) $input['border_strength']
    )
    : $defaults['border_strength'];

$output['border_strength'] = in_array(
    $border_strength,
    $allowed_border_strengths,
    true
)
    ? $border_strength
    : $defaults['border_strength'];


        $output['font_quote'] = isset($input['font_quote'])
            ? mpc_sanitize_font_family($input['font_quote'])
            : $defaults['font_quote'];
            // Typography role assignments.
        $allowed_font_roles = [
            'body',
            'heading',
            'accent',
            'quote',
        ];

        $font_role_fields = [
            'font_role_body',
            'font_role_heading',
            'font_role_blog_heading',
            'font_role_hero_heading',
            'font_role_nav',
            'font_role_button',
            'font_role_accent',
            'font_role_quote',
        ];

        foreach ($font_role_fields as $field) {
            $role = isset($input[$field])
                ? sanitize_key($input[$field])
                : $defaults[$field];

            $output[$field] = in_array($role, $allowed_font_roles, true)
                ? $role
                : $defaults[$field];
        }


        // Texture V2.
$output['texture_enabled'] = !empty($input['texture_enabled']) ? 1 : 0;
$output['texture_image_id'] = isset($input['texture_image_id']) ? absint($input['texture_image_id']) : 0;
$output['texture_opacity'] = isset($input['texture_opacity']) ? mpc_sanitize_opacity($input['texture_opacity']) : $defaults['texture_opacity'];
$output['texture_size'] = isset($input['texture_size']) ? mpc_sanitize_texture_size($input['texture_size']) : $defaults['texture_size'];

$allowed_repeats = [
    'repeat',
    'no-repeat',
    'repeat-x',
    'repeat-y',
];

$repeat = isset($input['texture_repeat']) ? sanitize_key($input['texture_repeat']) : $defaults['texture_repeat'];
$output['texture_repeat'] = in_array($repeat, $allowed_repeats, true) ? $repeat : $defaults['texture_repeat'];

$texture_zone_fields = [
    'texture_apply_header',
    'texture_apply_mobile_nav',
    'texture_apply_footer',
    'texture_apply_sections',
    'texture_apply_editorial',
];

foreach ($texture_zone_fields as $field) {
    $output[$field] = !empty($input[$field]) ? 1 : 0;
}

/*
 * Preserve retired placement settings without continuing to expose them
 * in the primary admin interface.
 */
$current_settings = get_option('mpc_theme_style_settings', []);
if (!is_array($current_settings)) {
    $current_settings = [];
}

$legacy_texture_fields = [
    'texture_apply_body',
    'texture_apply_buttons',
    'texture_apply_cards',
    'texture_apply_media_frames',
];

foreach ($legacy_texture_fields as $field) {
    if (array_key_exists($field, $input)) {
        $output[$field] = !empty($input[$field]) ? 1 : 0;
        continue;
    }
    $output[$field] = !empty($current_settings[$field]) ? 1 : 0;
}

    return $output;
}

/**
 * Render a typography font-assignment select.
 */
function mpc_render_font_assignment_select(
    $setting_key,
    $label,
    $settings,
    $description = ''
) {
    $choices = [
        'body' => __('Body Font', 'music-project-core'),
        'heading' => __('Display Font', 'music-project-core'),
        'accent' => __('Accent / UI Font', 'music-project-core'),
        'quote' => __('Quote Font', 'music-project-core'),
    ];

    $value = isset($settings[$setting_key])
        ? sanitize_key($settings[$setting_key])
        : 'body';

    ?>
<tr>
    <th scope="row">
        <label for="mpc_theme_style_<?php echo esc_attr($setting_key); ?>">
            <?php echo esc_html($label); ?>
        </label>
    </th>

    <td>
        <select id="mpc_theme_style_<?php echo esc_attr($setting_key); ?>"
            name="mpc_theme_style_settings[<?php echo esc_attr($setting_key); ?>]">
            <?php foreach ($choices as $choice_value => $choice_label) : ?>
            <option value="<?php echo esc_attr($choice_value); ?>" <?php selected($value, $choice_value); ?>>
                <?php echo esc_html($choice_label); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <?php if ($description) : ?>
        <p class="description">
            <?php echo esc_html($description); ?>
        </p>
        <?php endif; ?>
    </td>
</tr>
<?php
}

/**
 * Register settings.
 */
function mpc_register_theme_style_settings() {
    register_setting(
        'mpc_theme_style_settings_group',
        'mpc_theme_style_settings',
        [
            'type'              => 'array',
            'sanitize_callback' =>
                'mpc_sanitize_theme_style_settings',
            'default'           =>
                mpc_get_theme_style_defaults(),
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
$page = (
    isset($_GET['page'])
    && is_string($_GET['page'])
)
    ? sanitize_key(
        wp_unslash(
            $_GET['page']
        )
    )
    : '';

    if ($page !== 'mpc-theme-style') {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_script(
        'mpc-admin',
        MPC_URL . 'assets/admin.js',
        ['jquery'],
        mpc_get_asset_version('assets/admin.js'),
        true
    );

    mpc_localize_admin_script();

    wp_enqueue_style(
        'mpc-admin',
        MPC_URL . 'assets/admin.css',
        [],
        mpc_get_asset_version('assets/admin.css')
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

            if ($url) {
                $file_path = wp_parse_url(
                    $url,
                    PHP_URL_PATH
                );

                $file_name = wp_basename(
                    $file_path ?: $url
                );

                $preview = sprintf(
                    '<p><strong>%1$s</strong><br>%2$s</p>',
                    esc_html__(
                        'Selected file:',
                        'music-project-core'
                    ),
                    esc_html($file_name)
                );
            }
        }
    }
    ?>

<div class="mpc-media-field">
    <input type="hidden" id="<?php echo esc_attr($field_id); ?>"
        name="mpc_theme_style_settings[<?php echo esc_attr($field_name); ?>]"
        value="<?php echo esc_attr($attachment_id); ?>">

    <div class="mpc-media-preview" data-preview-for="<?php echo esc_attr($field_id); ?>">
        <?php echo wp_kses_post($preview); ?>
    </div>

    <button type="button" class="button mpc-media-upload" data-target="<?php echo esc_attr($field_id); ?>"
        data-type="<?php echo esc_attr($media_type); ?>">
        <?php echo $attachment_id ? esc_html__('Replace File', 'music-project-core') : esc_html__('Choose File', 'music-project-core'); ?>
    </button>

    <button type="button" class="button mpc-media-remove" data-target="<?php echo esc_attr($field_id); ?>">
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

        <div
            class="mpc-theme-style-tabs"
            data-theme-style-tabs
        >
            <div
                class="mpc-theme-style-tabs__tablist"
                role="tablist"
                aria-label="<?php esc_attr_e('Theme Style sections', 'music-project-core'); ?>"
                aria-orientation="horizontal"
                hidden
            >
                <button
                    type="button"
                    id="mpc-theme-style-tab-colors"
                    class="mpc-theme-style-tabs__tab"
                    role="tab"
                    aria-selected="true"
                    aria-controls="mpc-theme-style-panel-colors"
                    tabindex="0"
                >
                    <?php esc_html_e('Colors', 'music-project-core'); ?>
                </button>

                <button
                    type="button"
                    id="mpc-theme-style-tab-design"
                    class="mpc-theme-style-tabs__tab"
                    role="tab"
                    aria-selected="false"
                    aria-controls="mpc-theme-style-panel-design"
                    tabindex="-1"
                >
                    <?php esc_html_e('Design Details', 'music-project-core'); ?>
                </button>

                <button
                    type="button"
                    id="mpc-theme-style-tab-chrome"
                    class="mpc-theme-style-tabs__tab"
                    role="tab"
                    aria-selected="false"
                    aria-controls="mpc-theme-style-panel-chrome"
                    tabindex="-1"
                >
                    <?php esc_html_e('Site Chrome', 'music-project-core'); ?>
                </button>

                <button
                    type="button"
                    id="mpc-theme-style-tab-typography"
                    class="mpc-theme-style-tabs__tab"
                    role="tab"
                    aria-selected="false"
                    aria-controls="mpc-theme-style-panel-typography"
                    tabindex="-1"
                >
                    <?php esc_html_e('Typography', 'music-project-core'); ?>
                </button>

                <button
                    type="button"
                    id="mpc-theme-style-tab-texture"
                    class="mpc-theme-style-tabs__tab"
                    role="tab"
                    aria-selected="false"
                    aria-controls="mpc-theme-style-panel-texture"
                    tabindex="-1"
                >
                    <?php esc_html_e('Texture', 'music-project-core'); ?>
                </button>
            </div>

            <div class="mpc-theme-style-tabs__panels">
                <section
                    id="mpc-theme-style-panel-colors"
                    class="mpc-theme-style-tabs__panel"
                    role="tabpanel"
                    aria-labelledby="mpc-theme-style-tab-colors"
                    tabindex="0"
                >
                    <h2>
                        <?php esc_html_e('Colors', 'music-project-core'); ?>
                    </h2>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="color_background"><?php esc_html_e('Background Color', 'music-project-core'); ?></label>
                </th>
                <td>
                    <input type="color" id="color_background" name="mpc_theme_style_settings[color_background]"
                        value="<?php echo esc_attr($settings['color_background']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label
                        for="color_surface"><?php esc_html_e('Surface / Card Color', 'music-project-core'); ?></label>
                </th>
                <td>
                    <input type="color" id="color_surface" name="mpc_theme_style_settings[color_surface]"
                        value="<?php echo esc_attr($settings['color_surface']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="color_text"><?php esc_html_e('Text Color', 'music-project-core'); ?></label>
                </th>
                <td>
                    <input type="color" id="color_text" name="mpc_theme_style_settings[color_text]"
                        value="<?php echo esc_attr($settings['color_text']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="color_muted"><?php esc_html_e('Muted Text Color', 'music-project-core'); ?></label>
                </th>
                <td>
                    <input type="color" id="color_muted" name="mpc_theme_style_settings[color_muted]"
                        value="<?php echo esc_attr($settings['color_muted']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="color_accent"><?php esc_html_e('Accent Color', 'music-project-core'); ?></label>
                </th>
                <td>
                    <input type="color" id="color_accent" name="mpc_theme_style_settings[color_accent]"
                        value="<?php echo esc_attr($settings['color_accent']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label
                        for="color_button_background"><?php esc_html_e('Button Background', 'music-project-core'); ?></label>
                </th>
                <td>
                    <input type="color" id="color_button_background"
                        name="mpc_theme_style_settings[color_button_background]"
                        value="<?php echo esc_attr($settings['color_button_background']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="color_button_text"><?php esc_html_e('Button Text', 'music-project-core'); ?></label>
                </th>
                <td>
                    <input type="color" id="color_button_text" name="mpc_theme_style_settings[color_button_text]"
                        value="<?php echo esc_attr($settings['color_button_text']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_hero_heading_color">
                        <?php esc_html_e('Hero Heading Color', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_hero_heading_color"
                        name="mpc_theme_style_settings[hero_heading_color]"
                        value="<?php echo esc_attr($settings['hero_heading_color'] ?? '#ffffff'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_hero_lead_color">
                        <?php esc_html_e('Hero Lead Text Color', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_hero_lead_color"
                        name="mpc_theme_style_settings[hero_lead_color]"
                        value="<?php echo esc_attr($settings['hero_lead_color'] ?? '#f5f5f5'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_hero_text_shadow">
                        <?php esc_html_e('Hero Text Shadow', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_theme_style_hero_text_shadow" name="mpc_theme_style_settings[hero_text_shadow]">
                        <option value="none" <?php selected($settings['hero_text_shadow'] ?? 'subtle', 'none'); ?>>
                            <?php esc_html_e('None', 'music-project-core'); ?>
                        </option>

                        <option value="subtle" <?php selected($settings['hero_text_shadow'] ?? 'subtle', 'subtle'); ?>>
                            <?php esc_html_e('Subtle', 'music-project-core'); ?>
                        </option>

                        <option value="strong" <?php selected($settings['hero_text_shadow'] ?? 'subtle', 'strong'); ?>>
                            <?php esc_html_e('Strong', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Useful when the hero sits over photos or videos.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_hero_text_shadow_color">
                        <?php esc_html_e('Hero Text Shadow Color', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="mpc_theme_style_hero_text_shadow_color"
                        name="mpc_theme_style_settings[hero_text_shadow_color]"
                        value="<?php echo esc_attr($settings['hero_text_shadow_color'] ?? '#000000'); ?>"
                        class="mpc-color-field" data-default-color="#000000">

                    <p class="description">
                        <?php esc_html_e('Controls the color used by the hero text shadow preset. Use dark colors for light text, or light colors for dark text.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

        </table>
    </section>

    <section
        id="mpc-theme-style-panel-design"
        class="mpc-theme-style-tabs__panel"
        role="tabpanel"
        aria-labelledby="mpc-theme-style-tab-design"
        tabindex="0"
    >
        <h2>
            <?php esc_html_e('Design Details', 'music-project-core'); ?>
        </h2>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_corner_style">
                        <?php esc_html_e('Corner Style', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_theme_style_corner_style" name="mpc_theme_style_settings[corner_style]">
                        <option value="sharp" <?php selected($settings['corner_style'] ?? 'rounded', 'sharp'); ?>>
                            <?php esc_html_e('Sharp', 'music-project-core'); ?>
                        </option>

                        <option value="subtle" <?php selected($settings['corner_style'] ?? 'rounded', 'subtle'); ?>>
                            <?php esc_html_e('Subtle', 'music-project-core'); ?>
                        </option>

                        <option value="rounded" <?php selected($settings['corner_style'] ?? 'rounded', 'rounded'); ?>>
                            <?php esc_html_e('Rounded', 'music-project-core'); ?>
                        </option>

                        <option value="soft" <?php selected($settings['corner_style'] ?? 'rounded', 'soft'); ?>>
                            <?php esc_html_e('Soft', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls the overall roundness of cards, media, buttons, and form controls.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_card_shadow_style">
                        <?php esc_html_e('Card Shadow Style', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_theme_style_card_shadow_style" name="mpc_theme_style_settings[card_shadow_style]">
                        <option value="none" <?php selected($settings['card_shadow_style'] ?? 'standard', 'none'); ?>>
                            <?php esc_html_e('None', 'music-project-core'); ?>
                        </option>

                        <option value="subtle"
                            <?php selected($settings['card_shadow_style'] ?? 'standard', 'subtle'); ?>>
                            <?php esc_html_e('Subtle', 'music-project-core'); ?>
                        </option>

                        <option value="standard"
                            <?php selected($settings['card_shadow_style'] ?? 'standard', 'standard'); ?>>
                            <?php esc_html_e('Standard', 'music-project-core'); ?>
                        </option>

                        <option value="dramatic"
                            <?php selected($settings['card_shadow_style'] ?? 'standard', 'dramatic'); ?>>
                            <?php esc_html_e('Dramatic', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls elevation on card-style elements like featured content, blog cards, event cards, and panels.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_border_strength">
                        <?php esc_html_e('Border Strength', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_theme_style_border_strength" name="mpc_theme_style_settings[border_strength]">
                        <option value="minimal" <?php selected($settings['border_strength'] ?? 'subtle', 'minimal'); ?>>
                            <?php esc_html_e('Minimal', 'music-project-core'); ?>
                        </option>

                        <option value="subtle" <?php selected($settings['border_strength'] ?? 'subtle', 'subtle'); ?>>
                            <?php esc_html_e('Subtle', 'music-project-core'); ?>
                        </option>

                        <option value="defined" <?php selected($settings['border_strength'] ?? 'subtle', 'defined'); ?>>
                            <?php esc_html_e('Defined', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Useful when shadows are disabled or when you want sharper editorial card edges.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </section>

    <section
        id="mpc-theme-style-panel-chrome"
        class="mpc-theme-style-tabs__panel"
        role="tabpanel"
        aria-labelledby="mpc-theme-style-tab-chrome"
        tabindex="0"
    >
        <h2>
            <?php esc_html_e('Site Chrome', 'music-project-core'); ?>
        </h2>

        <p>
            <?php esc_html_e('Controls the header, navbar behavior, mobile navigation overlay, branding display, and footer colors.', 'music-project-core'); ?>
        </p>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_header_behavior">
                        <?php esc_html_e('Header Behavior', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_theme_style_header_behavior" name="mpc_theme_style_settings[header_behavior]">
                        <option value="standard"
                            <?php selected($settings['header_behavior'] ?? 'standard', 'standard'); ?>>
                            <?php esc_html_e('Standard', 'music-project-core'); ?>
                        </option>
                        <option value="sticky" <?php selected($settings['header_behavior'] ?? 'standard', 'sticky'); ?>>
                            <?php esc_html_e('Sticky', 'music-project-core'); ?>
                        </option>
                        <option value="transparent"
                            <?php selected($settings['header_behavior'] ?? 'standard', 'transparent'); ?>>
                            <?php esc_html_e('Transparent Over Hero', 'music-project-core'); ?>
                        </option>
                        <option value="transparent_scroll"
                            <?php selected($settings['header_behavior'] ?? 'standard', 'transparent_scroll'); ?>>
                            <?php esc_html_e('Transparent, Solid On Scroll', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php
                esc_html_e(
                    'Transparent modes apply to the homepage hero only. Inner pages use a solid sticky header. Choose Standard for a non-sticky header.',
                    'music-project-core'
                );
                ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_brand_display">
                        <?php esc_html_e('Brand Display', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_theme_style_brand_display" name="mpc_theme_style_settings[brand_display]">
                        <option value="logo_name"
                            <?php selected($settings['brand_display'] ?? 'logo_name', 'logo_name'); ?>>
                            <?php esc_html_e('Logo + Site Name', 'music-project-core'); ?>
                        </option>
                        <option value="logo_only"
                            <?php selected($settings['brand_display'] ?? 'logo_name', 'logo_only'); ?>>
                            <?php esc_html_e('Logo Only', 'music-project-core'); ?>
                        </option>
                        <option value="name_only"
                            <?php selected($settings['brand_display'] ?? 'logo_name', 'name_only'); ?>>
                            <?php esc_html_e('Site Name Only', 'music-project-core'); ?>
                        </option>
                        <option value="hidden" <?php selected($settings['brand_display'] ?? 'logo_name', 'hidden'); ?>>
                            <?php esc_html_e('Hidden', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls how the site brand appears in the header.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_header_background_color">
                        <?php esc_html_e('Header Background', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_header_background_color"
                        name="mpc_theme_style_settings[header_background_color]"
                        value="<?php echo esc_attr($settings['header_background_color'] ?? '#000000'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_header_text_color">
                        <?php esc_html_e('Header Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_header_text_color"
                        name="mpc_theme_style_settings[header_text_color]"
                        value="<?php echo esc_attr($settings['header_text_color'] ?? '#f5f5f5'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_header_border_color">
                        <?php esc_html_e('Header Border', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_header_border_color"
                        name="mpc_theme_style_settings[header_border_color]"
                        value="<?php echo esc_attr($settings['header_border_color'] ?? '#1f1f1f'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_mobile_nav_background_color">
                        <?php esc_html_e('Mobile Nav Background', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_mobile_nav_background_color"
                        name="mpc_theme_style_settings[mobile_nav_background_color]"
                        value="<?php echo esc_attr($settings['mobile_nav_background_color'] ?? '#000000'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_mobile_nav_text_color">
                        <?php esc_html_e('Mobile Nav Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_mobile_nav_text_color"
                        name="mpc_theme_style_settings[mobile_nav_text_color]"
                        value="<?php echo esc_attr($settings['mobile_nav_text_color'] ?? '#f5f5f5'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_mobile_nav_border_color">
                        <?php esc_html_e('Mobile Nav Border', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_mobile_nav_border_color"
                        name="mpc_theme_style_settings[mobile_nav_border_color]"
                        value="<?php echo esc_attr($settings['mobile_nav_border_color'] ?? '#242424'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_footer_background_color">
                        <?php esc_html_e('Footer Background', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_footer_background_color"
                        name="mpc_theme_style_settings[footer_background_color]"
                        value="<?php echo esc_attr($settings['footer_background_color'] ?? '#000000'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_footer_text_color">
                        <?php esc_html_e('Footer Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_footer_text_color"
                        name="mpc_theme_style_settings[footer_text_color]"
                        value="<?php echo esc_attr($settings['footer_text_color'] ?? '#f5f5f5'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_footer_muted_color">
                        <?php esc_html_e('Footer Muted Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_footer_muted_color"
                        name="mpc_theme_style_settings[footer_muted_color]"
                        value="<?php echo esc_attr($settings['footer_muted_color'] ?? '#b8b8b8'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_footer_border_color">
                        <?php esc_html_e('Footer Border', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="color" id="mpc_theme_style_footer_border_color"
                        name="mpc_theme_style_settings[footer_border_color]"
                        value="<?php echo esc_attr($settings['footer_border_color'] ?? '#1f1f1f'); ?>">
                </td>
            </tr>
        </table>
    </section>

    <section
        id="mpc-theme-style-panel-typography"
        class="mpc-theme-style-tabs__panel"
        role="tabpanel"
        aria-labelledby="mpc-theme-style-tab-typography"
        tabindex="0"
    >
        <h2>
            <?php esc_html_e('Typography', 'music-project-core'); ?>
        </h2>

        <p>
            <?php esc_html_e('Define your available fonts, then assign them to different typography roles across the site.', 'music-project-core'); ?>
        </p>

        <h3><?php esc_html_e('Font Library', 'music-project-core'); ?></h3>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="google_fonts_url">
                        <?php esc_html_e('Google Fonts Stylesheet URL', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <input type="url" id="google_fonts_url" name="mpc_theme_style_settings[google_fonts_url]"
                        class="large-text" value="<?php echo esc_url($settings['google_fonts_url'] ?? ''); ?>"
                        placeholder="https://fonts.googleapis.com/css2?family=...">

                    <p class="description">
                        <?php esc_html_e('Optional. Paste the Google Fonts stylesheet URL only, not the full link tag.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="font_body">
                        <?php esc_html_e('Body Font', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <input type="text" id="font_body" name="mpc_theme_style_settings[font_body]" class="large-text"
                        value="<?php echo esc_attr($settings['font_body'] ?? ''); ?>">

                    <p class="description">
                        <?php esc_html_e('Example: "Lora", Georgia, serif', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="font_heading">
                        <?php esc_html_e('Display Font', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <input type="text" id="font_heading" name="mpc_theme_style_settings[font_heading]"
                        class="large-text" value="<?php echo esc_attr($settings['font_heading'] ?? ''); ?>">

                    <p class="description">
                        <?php esc_html_e('Usually used for headings and prominent display text. Example: "Oswald", Impact, sans-serif', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="font_accent">
                        <?php esc_html_e('Accent / UI Font', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <input type="text" id="font_accent" name="mpc_theme_style_settings[font_accent]" class="large-text"
                        value="<?php echo esc_attr($settings['font_accent'] ?? ''); ?>">

                    <p class="description">
                        <?php esc_html_e('Useful for navigation, buttons, metadata, labels, and other interface text.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_font_quote">
                        <?php esc_html_e('Quote Font', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <input id="mpc_theme_style_font_quote" class="large-text" type="text"
                        name="mpc_theme_style_settings[font_quote]"
                        value="<?php echo esc_attr($settings['font_quote'] ?? ''); ?>"
                        placeholder='"Playfair Display", Georgia, serif'>

                    <p class="description">
                        <?php esc_html_e('Optional. Leave blank to make the Quote Font slot use the Display Font.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h3><?php esc_html_e('Font Assignments', 'music-project-core'); ?></h3>

        <p>
            <?php esc_html_e('Choose which font from the library should style each part of the site.', 'music-project-core'); ?>
        </p>

        <table class="form-table" role="presentation">
            <?php
    mpc_render_font_assignment_select(
        'font_role_body',
        __('Body Text', 'music-project-core'),
        $settings,
        __('Paragraphs, long-form content, descriptions, and general text.', 'music-project-core')
    );

    mpc_render_font_assignment_select(
        'font_role_heading',
        __('Headings', 'music-project-core'),
        $settings,
        __(
            'General page titles, homepage section headings, and promotional card headings.',
            'music-project-core'
        )
    );

    mpc_render_font_assignment_select(
        'font_role_blog_heading',
        __('Blog / Editorial Headings', 'music-project-core'),
        $settings,
        __(
            'Blog and archive titles, post cards, single-post titles, article headings, and previous or next post titles.',
            'music-project-core'
        )
    );

    mpc_render_font_assignment_select(
        'font_role_hero_heading',
        __('Hero Heading', 'music-project-core'),
        $settings,
        __('The main homepage hero heading and site-status headline.', 'music-project-core')
    );

    mpc_render_font_assignment_select(
        'font_role_nav',
        __('Navigation / Branding', 'music-project-core'),
        $settings,
        __('Header and footer menus, site name, and navigation controls.', 'music-project-core')
    );

    mpc_render_font_assignment_select(
        'font_role_button',
        __('Buttons / CTAs', 'music-project-core'),
        $settings,
        __('Buttons, calls to action, read-more links, and pagination controls.', 'music-project-core')
    );

    mpc_render_font_assignment_select(
        'font_role_accent',
        __('Accent / UI Text', 'music-project-core'),
        $settings,
        __('Dates, metadata, labels, social links, quote attribution, and eyebrow text.', 'music-project-core')
    );

    mpc_render_font_assignment_select(
        'font_role_quote',
        __('Quotes / Testimonials', 'music-project-core'),
        $settings,
        __('Testimonials, press quotes, pull quotes, and content blockquotes.', 'music-project-core')
    );
    ?>
        </table>

        <h3><?php esc_html_e('Heading Presentation', 'music-project-core'); ?></h3>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="heading_text_transform">
                        <?php esc_html_e('Heading Text Transform', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <select id="heading_text_transform" name="mpc_theme_style_settings[heading_text_transform]">
                        <option value="none" <?php selected($settings['heading_text_transform'] ?? 'none', 'none'); ?>>
                            <?php esc_html_e('None', 'music-project-core'); ?>
                        </option>

                        <option value="uppercase"
                            <?php selected($settings['heading_text_transform'] ?? 'none', 'uppercase'); ?>>
                            <?php esc_html_e('Uppercase', 'music-project-core'); ?>
                        </option>

                        <option value="lowercase"
                            <?php selected($settings['heading_text_transform'] ?? 'none', 'lowercase'); ?>>
                            <?php esc_html_e('Lowercase', 'music-project-core'); ?>
                        </option>

                        <option value="capitalize"
                            <?php selected($settings['heading_text_transform'] ?? 'none', 'capitalize'); ?>>
                            <?php esc_html_e('Capitalize', 'music-project-core'); ?>
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="heading_letter_spacing">
                        <?php esc_html_e('Heading Letter Spacing', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <input type="text" id="heading_letter_spacing"
                        name="mpc_theme_style_settings[heading_letter_spacing]" class="regular-text"
                        value="<?php echo esc_attr($settings['heading_letter_spacing'] ?? '-0.04em'); ?>"
                        placeholder="-0.04em">

                    <p class="description">
                        <?php esc_html_e('Use values like -0.04em, 0.02em, 1px, etc.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_theme_style_heading_alignment_scope">
                        <?php esc_html_e('Heading Alignment', 'music-project-core'); ?>
                    </label>
                </th>

                <td>
                    <select id="mpc_theme_style_heading_alignment_scope"
                        name="mpc_theme_style_settings[heading_alignment_scope]">
                        <option value="none" <?php selected($settings['heading_alignment_scope'] ?? 'none', 'none'); ?>>
                            <?php esc_html_e('Default', 'music-project-core'); ?>
                        </option>

                        <option value="home" <?php selected($settings['heading_alignment_scope'] ?? 'none', 'home'); ?>>
                            <?php esc_html_e('Center homepage headings', 'music-project-core'); ?>
                        </option>

                        <option value="all" <?php selected($settings['heading_alignment_scope'] ?? 'none', 'all'); ?>>
                            <?php esc_html_e('Center all headings', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Choose whether headings keep the default layout, center only on homepage sections, or center across the whole site.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </section>

<?php
$texture_opacity_value = mpc_sanitize_opacity(
    $settings['texture_opacity'] ?? '0.14'
);

$texture_opacity_presets = [
    '0.08',
    '0.14',
    '0.22',
];

$texture_size_value = mpc_sanitize_texture_size(
    $settings['texture_size'] ?? '420px'
);

$texture_size_presets = [
    '280px',
    '420px',
    '720px',
    'auto',
];

$texture_repeat_value = sanitize_key(
    $settings['texture_repeat'] ?? 'repeat'
);

$primary_repeat_options = [
    'repeat',
    'no-repeat',
];
?>

<section
    id="mpc-theme-style-panel-texture"
    class="mpc-theme-style-tabs__panel"
    role="tabpanel"
    aria-labelledby="mpc-theme-style-tab-texture"
    tabindex="0"
>
<h2>
    <?php
    esc_html_e(
        'Background Texture',
        'music-project-core'
    );
    ?>
</h2>
<p>
	<?php esc_html_e( 'Use one texture as a subtle environmental material across selected site backgrounds. Content cards, controls, and media remain clean for readability.', 'music-project-core' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Enable Texture', 'music-project-core' ); ?>
		</th>
		<td>
			<input type="hidden" name="mpc_theme_style_settings[texture_enabled]" value="0">
			<label>
				<input type="checkbox" name="mpc_theme_style_settings[texture_enabled]" value="1" <?php checked( 1, $settings['texture_enabled'] ); ?>>
				<?php esc_html_e( 'Enable site texture', 'music-project-core' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'A texture image and at least one placement must also be configured.', 'music-project-core' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Texture Image', 'music-project-core' ); ?>
		</th>
		<td>
			<?php mpc_render_theme_style_media_field( 'texture_image_id', $settings['texture_image_id'], 'image' ); ?>
			<p class="description">
				<?php esc_html_e( 'Seamless paper grain, fabric, print, scan, or amplifier-cloth images work best. Avoid images with prominent subjects or readable text.', 'music-project-core' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="texture_opacity"><?php esc_html_e( 'Texture Intensity', 'music-project-core' ); ?></label>
		</th>
		<td>
			<select id="texture_opacity" name="mpc_theme_style_settings[texture_opacity]">
				<option value="0.08" <?php selected( $texture_opacity_value, '0.08' ); ?>>
					<?php esc_html_e( 'Soft', 'music-project-core' ); ?>
				</option>
				<option value="0.14" <?php selected( $texture_opacity_value, '0.14' ); ?>>
					<?php esc_html_e( 'Standard', 'music-project-core' ); ?>
				</option>
				<option value="0.22" <?php selected( $texture_opacity_value, '0.22' ); ?>>
					<?php esc_html_e( 'Strong', 'music-project-core' ); ?>
				</option>
				<?php if ( ! in_array( $texture_opacity_value, $texture_opacity_presets, true ) ) : ?>
					<option value="<?php echo esc_attr( $texture_opacity_value ); ?>" selected>
						<?php
						printf(
							/* translators: %s is a decimal opacity value. */
							esc_html__( 'Current Custom Value (%s)', 'music-project-core' ),
							esc_html( $texture_opacity_value )
						);
						?>
					</option>
				<?php endif; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'Standard is designed to remain visible without competing with text or navigation.', 'music-project-core' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="texture_size"><?php esc_html_e( 'Texture Scale', 'music-project-core' ); ?></label>
		</th>
		<td>
			<select id="texture_size" name="mpc_theme_style_settings[texture_size]">
				<option value="280px" <?php selected( $texture_size_value, '280px' ); ?>>
					<?php esc_html_e( 'Fine', 'music-project-core' ); ?>
				</option>
				<option value="420px" <?php selected( $texture_size_value, '420px' ); ?>>
					<?php esc_html_e( 'Medium', 'music-project-core' ); ?>
				</option>
				<option value="720px" <?php selected( $texture_size_value, '720px' ); ?>>
					<?php esc_html_e( 'Large', 'music-project-core' ); ?>
				</option>
				<option value="auto" <?php selected( $texture_size_value, 'auto' ); ?>>
					<?php esc_html_e( 'Original Image Size', 'music-project-core' ); ?>
				</option>
				<?php if ( ! in_array( $texture_size_value, $texture_size_presets, true ) ) : ?>
					<option value="<?php echo esc_attr( $texture_size_value ); ?>" selected>
						<?php
						printf(
							/* translators: %s is a CSS background-size value. */
							esc_html__( 'Current Custom Value (%s)', 'music-project-core' ),
							esc_html( $texture_size_value )
						);
						?>
					</option>
				<?php endif; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'Fine produces a denser pattern. Large makes the texture feel broader and less repetitive.', 'music-project-core' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="texture_repeat"><?php esc_html_e( 'Texture Repeat', 'music-project-core' ); ?></label>
		</th>
		<td>
			<select id="texture_repeat" name="mpc_theme_style_settings[texture_repeat]">
				<option value="repeat" <?php selected( $texture_repeat_value, 'repeat' ); ?>>
					<?php esc_html_e( 'Repeat', 'music-project-core' ); ?>
				</option>
				<option value="no-repeat" <?php selected( $texture_repeat_value, 'no-repeat' ); ?>>
					<?php esc_html_e( 'No Repeat', 'music-project-core' ); ?>
				</option>
				<?php if ( ! in_array( $texture_repeat_value, $primary_repeat_options, true ) ) : ?>
					<option value="<?php echo esc_attr( $texture_repeat_value ); ?>" selected>
						<?php
						printf(
							/* translators: %s is a CSS repeat value. */
							esc_html__( 'Current Setting (%s)', 'music-project-core' ),
							esc_html( $texture_repeat_value )
						);
						?>
					</option>
				<?php endif; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'Repeat is recommended for seamless texture images.', 'music-project-core' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Texture Placements', 'music-project-core' ); ?>
		</th>
		<td>
			<fieldset>
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Choose where the texture appears', 'music-project-core' ); ?>
				</legend>

				<p>
					<input type="hidden" name="mpc_theme_style_settings[texture_apply_header]" value="0">
					<label>
						<input type="checkbox" name="mpc_theme_style_settings[texture_apply_header]" value="1" <?php checked( 1, $settings['texture_apply_header'] ); ?>>
						<?php esc_html_e( 'Header / Desktop Navigation', 'music-project-core' ); ?>
					</label>
				</p>

				<p>
					<input type="hidden" name="mpc_theme_style_settings[texture_apply_mobile_nav]" value="0">
					<label>
						<input type="checkbox" name="mpc_theme_style_settings[texture_apply_mobile_nav]" value="1" <?php checked( 1, $settings['texture_apply_mobile_nav'] ); ?>>
						<?php esc_html_e( 'Mobile Navigation Overlay', 'music-project-core' ); ?>
					</label>
				</p>

				<p>
					<input type="hidden" name="mpc_theme_style_settings[texture_apply_footer]" value="0">
					<label>
						<input type="checkbox" name="mpc_theme_style_settings[texture_apply_footer]" value="1" <?php checked( 1, $settings['texture_apply_footer'] ); ?>>
						<?php esc_html_e( 'Footer', 'music-project-core' ); ?>
					</label>
				</p>

				<p>
					<input type="hidden" name="mpc_theme_style_settings[texture_apply_sections]" value="0">
					<label>
						<input type="checkbox" name="mpc_theme_style_settings[texture_apply_sections]" value="1" <?php checked( 1, $settings['texture_apply_sections'] ); ?>>
						<?php esc_html_e( 'Homepage Section Backgrounds', 'music-project-core' ); ?>
					</label>
					<span class="description">
						<?php esc_html_e( 'The homepage hero is intentionally excluded.', 'music-project-core' ); ?>
					</span>
				</p>

				<p>
					<input type="hidden" name="mpc_theme_style_settings[texture_apply_editorial]" value="0">
					<label>
						<input type="checkbox" name="mpc_theme_style_settings[texture_apply_editorial]" value="1" <?php checked( 1, $settings['texture_apply_editorial'] ); ?>>
						<?php esc_html_e( 'Pages and Posts', 'music-project-core' ); ?>
					</label>
					<span class="description">
						<?php esc_html_e( 'Applies texture to the outer canvas while keeping article and page content on clean reading surfaces.', 'music-project-core' ); ?>
					</span>
				</p>
			</fieldset>
		</td>
	</tr>
</table>
                </section>
            </div>
        </div>

        <?php submit_button(__('Save Theme Style', 'music-project-core')); ?>    </form>
</div>

<?php
}