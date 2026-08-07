<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default integration settings.
 */
function mpc_get_integrations_defaults() {
    return [
        'shows_enabled' => 1,
        'shows_heading' => __(
            'Shows',
            'music-project-core'
        ),
        'shows_embed' => '',

        'newsletter_enabled' => 1,
        'newsletter_heading' => __(
            'Newsletter',
            'music-project-core'
        ),
        'newsletter_text' => __(
            'Sign up for updates.',
            'music-project-core'
        ),
        'newsletter_embed' => '',
    ];
}

/**
 * Get normalized Integration settings.
 *
 * Stored values may originate from an earlier release, direct database
 * editing, or third-party code. Core-owned values are normalized before
 * reaching administration or frontend rendering.
 *
 * Trusted embed strings are preserved exactly as stored. They are sanitized
 * according to the saving user's capabilities when the settings are saved,
 * not re-filtered according to the current frontend visitor.
 *
 * Unknown extension-owned scalar values remain available.
 *
 * @return array
 */
function mpc_get_integrations_settings() {
    $defaults = mpc_get_integrations_defaults();

    $saved = get_option(
        'mpc_integrations_settings',
        []
    );

    if (!is_array($saved)) {
        $saved = [];
    }

    $settings = [];

    /*
     * Preserve unknown extension-owned scalar settings while rebuilding every
     * Core-owned field below.
     */
    foreach ($saved as $key => $value) {
        $key = sanitize_key(
            (string) $key
        );

        if (
            $key === ''
            || array_key_exists(
                $key,
                $defaults
            )
        ) {
            continue;
        }

        if (
            is_scalar($value)
            || $value === null
        ) {
            $settings[$key] = $value;
        }
    }

    /**
     * Normalize a stored checkbox-style value.
     *
     * @param mixed $value Stored value.
     * @return int
     */
    $normalize_toggle = static function ($value) {
        if (!is_scalar($value)) {
            return 0;
        }

        $value = strtolower(
            trim(
                (string) $value
            )
        );

        return in_array(
            $value,
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        )
            ? 1
            : 0;
    };

    /*
     * Homepage Section Manager is the canonical source for Shows and
     * Newsletter visibility.
     */
    if (
        function_exists(
            'mpc_is_homepage_section_visible'
        )
    ) {
        $settings['shows_enabled'] =
            mpc_is_homepage_section_visible(
                'shows'
            )
                ? 1
                : 0;

        $settings['newsletter_enabled'] =
            mpc_is_homepage_section_visible(
                'newsletter'
            )
                ? 1
                : 0;
    } else {
        $settings['shows_enabled'] = array_key_exists(
            'shows_enabled',
            $saved
        )
            ? $normalize_toggle(
                $saved['shows_enabled']
            )
            : $defaults['shows_enabled'];

        $settings['newsletter_enabled'] =
            array_key_exists(
                'newsletter_enabled',
                $saved
            )
                ? $normalize_toggle(
                    $saved['newsletter_enabled']
                )
                : $defaults[
                    'newsletter_enabled'
                ];
    }

    $settings['shows_heading'] = (
        isset($saved['shows_heading'])
        && is_scalar($saved['shows_heading'])
    )
        ? sanitize_text_field(
            (string) $saved['shows_heading']
        )
        : $defaults['shows_heading'];

    /*
     * Preserve trusted saved markup exactly. Non-string malformed values
     * safely fall back to an empty embed.
     */
    $settings['shows_embed'] = (
        isset($saved['shows_embed'])
        && is_string($saved['shows_embed'])
    )
        ? $saved['shows_embed']
        : $defaults['shows_embed'];

    $settings['newsletter_heading'] = (
        isset($saved['newsletter_heading'])
        && is_scalar(
            $saved['newsletter_heading']
        )
    )
        ? sanitize_text_field(
            (string) $saved[
                'newsletter_heading'
            ]
        )
        : $defaults['newsletter_heading'];

    $settings['newsletter_text'] = (
        isset($saved['newsletter_text'])
        && is_scalar($saved['newsletter_text'])
    )
        ? sanitize_textarea_field(
            (string) $saved['newsletter_text']
        )
        : $defaults['newsletter_text'];

    /*
     * Preserve trusted saved markup exactly. Non-string malformed values
     * safely fall back to an empty embed.
     */
    $settings['newsletter_embed'] = (
        isset($saved['newsletter_embed'])
        && is_string(
            $saved['newsletter_embed']
        )
    )
        ? $saved['newsletter_embed']
        : $defaults['newsletter_embed'];

    return wp_parse_args(
        $settings,
        $defaults
    );
}

/**
 * Get one integration setting.
 */
function mpc_get_integration_setting($key, $default = '') {
    /*
     * Keep the old integration enable keys available while making Homepage
     * Section Manager the canonical visibility source.
     */
    $legacy_visibility_keys = [
        'shows_enabled'      => 'shows',
        'newsletter_enabled' => 'newsletter',
    ];

    if (
        isset($legacy_visibility_keys[$key])
        && function_exists('mpc_is_homepage_section_visible')
    ) {
        return mpc_is_homepage_section_visible(
            $legacy_visibility_keys[$key]
        ) ? 1 : 0;
    }

    $settings = mpc_get_integrations_settings();

    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Sanitize shortcode/embed content.
 *
 * Admins with unfiltered_html can save trusted embeds/scripts.
 * Other users get safer post-like HTML filtering.
 */
function mpc_sanitize_embed_content($content) {
    $content = is_string($content) ? wp_unslash($content) : '';

    if (current_user_can('unfiltered_html')) {
        return $content;
    }

    return wp_kses_post($content);
}

/**
 * Render trusted integration content.
 *
 * Supported input:
 *
 * - WordPress shortcodes.
 * - Trusted widget or embed markup.
 * - A single URL supported by WordPress oEmbed.
 *
 * Content is sanitized when settings are saved. Administrators with the
 * unfiltered_html capability may intentionally save provider scripts.
 *
 * @param string $content Stored integration content.
 * @param string $context Integration context, such as shows or newsletter.
 * @return string
 */
function mpc_render_integration_content($content, $context = 'general') {
    $content = trim((string) $content);
    $context = sanitize_key((string) $context);

    if ($content === '') {
        return '';
    }

    $rendered = '';

    /*
     * A URL on its own is treated as an oEmbed candidate. When WordPress
     * cannot embed it, retain a safe link instead of displaying a raw URL.
     */
    if (preg_match('#^https?://[^\s]+$#i', $content)) {
        $url = esc_url_raw($content);

        if ($url && wp_http_validate_url($url)) {
            $oembed = wp_oembed_get($url);

            if ($oembed) {
                $rendered = $oembed;
            } else {
                $rendered = sprintf(
                    '<p class="mpc-integration-link"><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>',
                    esc_url($url),
                    esc_html__(
                        'View provider content',
                        'music-project-core'
                    )
                );
            }
        }
    }

    if ($rendered === '') {
        $rendered = do_shortcode(
            shortcode_unautop($content)
        );
    }

    /**
     * Filter rendered integration content.
     *
     * This provides an optional extension point for integrations that need
     * custom handling without making them hard dependencies of Core or Base.
     *
     * @param string $rendered Rendered output.
     * @param string $content  Original stored content.
     * @param string $context  Integration context.
     */
    return (string) apply_filters(
        'mpc_render_integration_content',
        $rendered,
        $content,
        $context
    );
}

/**
 * Sanitize Integration settings.
 *
 * Existing unknown scalar values are preserved so an extension temporarily
 * becoming unavailable does not silently delete its stored Integration data.
 *
 * @param mixed $input Submitted settings.
 * @return array
 */
function mpc_sanitize_integrations_settings($input) {
    $input = is_array($input)
        ? $input
        : [];

    $defaults = mpc_get_integrations_defaults();

    $current = get_option(
        'mpc_integrations_settings',
        []
    );

    if (!is_array($current)) {
        $current = [];
    }

    $output = [];

    $known_keys = array_keys($defaults);

    /*
     * Preserve extension-owned scalar settings that Core does not currently
     * manage. Known Core fields are rebuilt below using their normal
     * sanitization rules.
     */
    foreach ($current as $key => $value) {
        $key = sanitize_key(
            (string) $key
        );

        if (
            $key === ''
            || in_array(
                $key,
                $known_keys,
                true
            )
        ) {
            continue;
        }

        if (
            is_scalar($value)
            || $value === null
        ) {
            $output[$key] = $value;
        }
    }

    /**
     * Normalize a stored checkbox-style value.
     *
     * @param mixed $value Submitted or stored value.
     * @return int
     */
    $sanitize_toggle = static function ($value) {
        if (!is_scalar($value)) {
            return 0;
        }

        $value = strtolower(
            trim(
                (string) $value
            )
        );

        return in_array(
            $value,
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        )
            ? 1
            : 0;
    };

    /*
     * These keys remain stored for compatibility, but their values mirror
     * Homepage Section Manager rather than separate admin checkboxes.
     */
    if (
        function_exists(
            'mpc_is_homepage_section_visible'
        )
    ) {
        $output['shows_enabled'] =
            mpc_is_homepage_section_visible(
                'shows'
            )
                ? 1
                : 0;

        $output['newsletter_enabled'] =
            mpc_is_homepage_section_visible(
                'newsletter'
            )
                ? 1
                : 0;
    } else {
        $output['shows_enabled'] = array_key_exists(
            'shows_enabled',
            $current
        )
            ? $sanitize_toggle(
                $current['shows_enabled']
            )
            : $defaults['shows_enabled'];

        $output['newsletter_enabled'] =
            array_key_exists(
                'newsletter_enabled',
                $current
            )
                ? $sanitize_toggle(
                    $current['newsletter_enabled']
                )
                : $defaults[
                    'newsletter_enabled'
                ];
    }

    $output['shows_heading'] = (
        isset($input['shows_heading'])
        && is_scalar($input['shows_heading'])
    )
        ? sanitize_text_field(
            (string) $input['shows_heading']
        )
        : $defaults['shows_heading'];

    $output['shows_embed'] = (
        isset($input['shows_embed'])
        && is_string($input['shows_embed'])
    )
        ? mpc_sanitize_embed_content(
            $input['shows_embed']
        )
        : '';

    $output['newsletter_heading'] = (
        isset($input['newsletter_heading'])
        && is_scalar(
            $input['newsletter_heading']
        )
    )
        ? sanitize_text_field(
            (string) $input[
                'newsletter_heading'
            ]
        )
        : $defaults['newsletter_heading'];

    $output['newsletter_text'] = (
        isset($input['newsletter_text'])
        && is_scalar($input['newsletter_text'])
    )
        ? sanitize_textarea_field(
            (string) $input['newsletter_text']
        )
        : $defaults['newsletter_text'];

    $output['newsletter_embed'] = (
        isset($input['newsletter_embed'])
        && is_string(
            $input['newsletter_embed']
        )
    )
        ? mpc_sanitize_embed_content(
            $input['newsletter_embed']
        )
        : '';

    return $output;
}

/**
 * Register settings.
 */
function mpc_register_integrations_settings() {
    register_setting(
        'mpc_integrations_settings_group',
        'mpc_integrations_settings',
        [
            'type'              => 'array',
            'sanitize_callback' =>
                'mpc_sanitize_integrations_settings',
            'default'           =>
                mpc_get_integrations_defaults(),
        ]
    );
}
add_action('admin_init', 'mpc_register_integrations_settings');

/**
 * Add Integrations submenu.
 */
function mpc_add_integrations_submenu() {
    add_submenu_page(
        'mpc-homepage',
        __('Integrations', 'music-project-core'),
        __('Integrations', 'music-project-core'),
        'manage_options',
        'mpc-integrations',
        'mpc_render_integrations_settings_page'
    );
}
add_action('admin_menu', 'mpc_add_integrations_submenu');

/**
 * Render embed textarea field.
 */
function mpc_render_embed_textarea($field_name, $value, $rows = 6) {
    ?>
    <textarea
        name="mpc_integrations_settings[<?php echo esc_attr($field_name); ?>]"
        class="large-text code"
        rows="<?php echo esc_attr($rows); ?>"
        placeholder="<?php esc_attr_e('Paste shortcode or trusted embed code here.', 'music-project-core'); ?>"
    ><?php echo esc_textarea($value); ?></textarea>
    <?php
}

/**
 * Render Integrations settings page.
 */
function mpc_render_integrations_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_integrations_settings();
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('Music Project Integrations', 'music-project-core'); ?></h1>

<p>
    <?php
    esc_html_e(
        'Use provider shortcodes, trusted widget/embed code, or a URL supported by WordPress oEmbed. Music Project does not require a specific events or newsletter provider.',
        'music-project-core'
    );
    ?>
</p>

        <p>
            <?php
            esc_html_e(
                'Homepage visibility is controlled under Music Project → Homepage → Section Manager.',
                'music-project-core'
            );
            ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_integrations_settings_group'); ?>

            <h2><?php esc_html_e('Shows / Events', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">


                <tr>
                    <th scope="row">
                        <label for="shows_heading"><?php esc_html_e('Shows Heading', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="shows_heading"
                            name="mpc_integrations_settings[shows_heading]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['shows_heading']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Shows Shortcode / Embed', 'music-project-core'); ?>
                    </th>
                    <td>
                        <?php mpc_render_embed_textarea('shows_embed', $settings['shows_embed']); ?>
                        <p class="description">
                            <?php esc_html_e('Paste any trusted shows/events shortcode or embed code.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <hr>

            <h2><?php esc_html_e('Newsletter / Mailing List', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">


                <tr>
                    <th scope="row">
                        <label for="newsletter_heading"><?php esc_html_e('Newsletter Heading', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="newsletter_heading"
                            name="mpc_integrations_settings[newsletter_heading]"
                            class="regular-text"
                            value="<?php echo esc_attr($settings['newsletter_heading']); ?>"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="newsletter_text"><?php esc_html_e('Newsletter Intro Text', 'music-project-core'); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="newsletter_text"
                            name="mpc_integrations_settings[newsletter_text]"
                            class="large-text"
                            rows="3"
                        ><?php echo esc_textarea($settings['newsletter_text']); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php
                        esc_html_e(
                            'Newsletter Shortcode, Embed, or URL',
                            'music-project-core'
                        );
                        ?>                    
                    </th>
                    <td>
                        <?php mpc_render_embed_textarea('newsletter_embed', $settings['newsletter_embed']); ?>
                        <p class="description">
                            <?php
                            esc_html_e(
                                'Paste a trusted form shortcode, widget/embed code, or supported oEmbed URL from your newsletter provider.',
                                'music-project-core'
                            );
                            ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Integrations', 'music-project-core')); ?>
        </form>
    </div>

    <?php
}