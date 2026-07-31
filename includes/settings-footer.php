<?php
/**
 * Footer Settings
 *
 * Stores content-oriented footer configuration that should survive
 * a theme change. The active theme owns footer markup and presentation.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get available footer layout presets.
 *
 * @return array
 */
function mpc_get_footer_layout_options() {
    return [
        'simple' => __(
            'Simple',
            'music-project-core'
        ),
        'stacked' => __(
            'Stacked / Centered',
            'music-project-core'
        ),
        'split' => __(
            'Split',
            'music-project-core'
        ),
    ];
}

/**
 * Get Footer settings defaults.
 *
 * @return array
 */
function mpc_get_footer_defaults() {
    return [
        'footer_tagline'     => '',
        'footer_copyright'   => __(
            '© {year} {site_name}. All rights reserved.',
            'music-project-core'
        ),
        'footer_show_brand'  => 1,
        'footer_show_menu'   => 1,
        'footer_show_socials' => 1,
        'footer_layout'      => 'simple',
    ];
}

/**
 * Get normalized Footer settings.
 *
 * @return array
 */
function mpc_get_footer_settings() {
    $settings = get_option(
        'mpc_footer_settings',
        []
    );

    if (!is_array($settings)) {
        $settings = [];
    }

    return wp_parse_args(
        $settings,
        mpc_get_footer_defaults()
    );
}

/**
 * Get one Footer setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function mpc_get_footer_setting(
    $key,
    $default = ''
) {
    $settings = mpc_get_footer_settings();

    return array_key_exists($key, $settings)
        ? $settings[$key]
        : $default;
}

/**
 * Replace supported tokens in footer text.
 *
 * @param string $text Footer text containing optional tokens.
 * @return string
 */
function mpc_parse_footer_tokens($text) {
    $text = (string) $text;

    $replacements = [
        '{year}'      => date_i18n('Y'),
        '{site_name}' => get_bloginfo('name'),
    ];

    /**
     * Filter footer token replacements.
     *
     * Extensions can add tokens without changing the stored Footer
     * settings shape.
     *
     * @param array  $replacements Token/value pairs.
     * @param string $text         Original footer text.
     */
    $replacements = apply_filters(
        'mpc_footer_token_replacements',
        $replacements,
        $text
    );

    if (!is_array($replacements)) {
        return $text;
    }

    $normalized = [];

    foreach ($replacements as $token => $replacement) {
        $token = (string) $token;

        if (
            $token === ''
            || !is_scalar($replacement)
        ) {
            continue;
        }

        $normalized[$token] = (string) $replacement;
    }

    return strtr(
        $text,
        $normalized
    );
}

/**
 * Sanitize Footer settings.
 *
 * Existing unknown scalar values are preserved so an extension temporarily
 * becoming unavailable does not silently delete its stored data.
 *
 * @param mixed $input Submitted settings.
 * @return array
 */
function mpc_sanitize_footer_settings($input) {
    $input = is_array($input)
        ? $input
        : [];

    $defaults = mpc_get_footer_defaults();

    $current = get_option(
        'mpc_footer_settings',
        []
    );

    $output = [];

    /*
     * Preserve unknown scalar settings so temporarily unavailable extensions
     * do not silently lose their stored Footer data.
     */
    if (is_array($current)) {
        foreach ($current as $key => $value) {
            $key = sanitize_key(
                (string) $key
            );

            if (
                $key !== ''
                && (
                    is_scalar($value)
                    || $value === null
                )
            ) {
                $output[$key] = $value;
            }
        }
    }

    $footer_tagline = (
        isset($input['footer_tagline'])
        && is_scalar($input['footer_tagline'])
    )
        ? sanitize_text_field(
            (string) $input['footer_tagline']
        )
        : '';

    $footer_copyright = (
        isset($input['footer_copyright'])
        && is_scalar($input['footer_copyright'])
    )
        ? sanitize_text_field(
            (string) $input['footer_copyright']
        )
        : $defaults['footer_copyright'];

    $layout = (
        isset($input['footer_layout'])
        && is_scalar($input['footer_layout'])
    )
        ? sanitize_key(
            (string) $input['footer_layout']
        )
        : $defaults['footer_layout'];

    $allowed_layouts = array_keys(
        mpc_get_footer_layout_options()
    );

    $output['footer_tagline'] = $footer_tagline;

    $output['footer_copyright'] =
        $footer_copyright;

    $output['footer_show_brand'] = !empty(
        $input['footer_show_brand']
    )
        ? 1
        : 0;

    $output['footer_show_menu'] = !empty(
        $input['footer_show_menu']
    )
        ? 1
        : 0;

    $output['footer_show_socials'] = !empty(
        $input['footer_show_socials']
    )
        ? 1
        : 0;

    $output['footer_layout'] = in_array(
        $layout,
        $allowed_layouts,
        true
    )
        ? $layout
        : $defaults['footer_layout'];

    return $output;
}

/**
 * Register Footer settings.
 *
 * @return void
 */
function mpc_register_footer_settings() {
    register_setting(
        'mpc_footer_settings_group',
        'mpc_footer_settings',
        [
            'type'              => 'array',
            'sanitize_callback' =>
                'mpc_sanitize_footer_settings',
            'default'           =>
                mpc_get_footer_defaults(),
        ]
    );
}
add_action(
    'admin_init',
    'mpc_register_footer_settings'
);

/**
 * Add the Footer submenu.
 *
 * @return void
 */
function mpc_add_footer_admin_menu() {
    add_submenu_page(
        'mpc-homepage',
        __(
            'Footer',
            'music-project-core'
        ),
        __(
            'Footer',
            'music-project-core'
        ),
        'manage_options',
        'mpc-footer',
        'mpc_render_footer_settings_page'
    );
}
add_action(
    'admin_menu',
    'mpc_add_footer_admin_menu',
    12
);

/**
 * Render the Footer settings page.
 *
 * @return void
 */
function mpc_render_footer_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_footer_settings();
    $layouts = mpc_get_footer_layout_options();
    ?>
    <div class="wrap">
        <h1>
            <?php
            esc_html_e(
                'Footer Settings',
                'music-project-core'
            );
            ?>
        </h1>

        <?php settings_errors(); ?>

        <p>
            <?php
            esc_html_e(
                'Choose a curated footer layout and control which global site elements it displays.',
                'music-project-core'
            );
            ?>
        </p>

        <form method="post" action="options.php">
            <?php
            settings_fields(
                'mpc_footer_settings_group'
            );
            ?>

            <table
                class="form-table"
                role="presentation"
            >
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="mpc_footer_tagline">
                                <?php
                                esc_html_e(
                                    'Footer Tagline',
                                    'music-project-core'
                                );
                                ?>
                            </label>
                        </th>

                        <td>
                            <input
                                id="mpc_footer_tagline"
                                class="regular-text"
                                type="text"
                                name="mpc_footer_settings[footer_tagline]"
                                value="<?php echo esc_attr($settings['footer_tagline']); ?>"
                                placeholder="<?php esc_attr_e('New record out now.', 'music-project-core'); ?>"
                            >

                            <p class="description">
                                <?php
                                esc_html_e(
                                    'Optional short line shown beneath the footer brand.',
                                    'music-project-core'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mpc_footer_copyright">
                                <?php
                                esc_html_e(
                                    'Copyright Text',
                                    'music-project-core'
                                );
                                ?>
                            </label>
                        </th>

                        <td>
                            <input
                                id="mpc_footer_copyright"
                                class="regular-text"
                                type="text"
                                name="mpc_footer_settings[footer_copyright]"
                                value="<?php echo esc_attr($settings['footer_copyright']); ?>"
                                aria-describedby="mpc-footer-copyright-description"
                            >

                            <p
                                id="mpc-footer-copyright-description"
                                class="description"
                            >
                                <?php
                                printf(
                                    /* translators: 1: year token, 2: site name token. */
                                    esc_html__(
                                        'Available tokens: %1$s and %2$s.',
                                        'music-project-core'
                                    ),
                                    '<code>{year}</code>',
                                    '<code>{site_name}</code>'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php
                            esc_html_e(
                                'Footer Visibility',
                                'music-project-core'
                            );
                            ?>
                        </th>

                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <?php
                                    esc_html_e(
                                        'Choose which footer elements to display',
                                        'music-project-core'
                                    );
                                    ?>
                                </legend>

                                <label>
                                    <input
                                        type="checkbox"
                                        name="mpc_footer_settings[footer_show_brand]"
                                        value="1"
                                        <?php checked($settings['footer_show_brand'], 1); ?>
                                    >

                                    <?php
                                    esc_html_e(
                                        'Show footer brand or logo',
                                        'music-project-core'
                                    );
                                    ?>
                                </label>

                                <br>

                                <label>
                                    <input
                                        type="checkbox"
                                        name="mpc_footer_settings[footer_show_menu]"
                                        value="1"
                                        <?php checked($settings['footer_show_menu'], 1); ?>
                                    >

                                    <?php
                                    esc_html_e(
                                        'Show footer menu',
                                        'music-project-core'
                                    );
                                    ?>
                                </label>

                                <br>

                                <label>
                                    <input
                                        type="checkbox"
                                        name="mpc_footer_settings[footer_show_socials]"
                                        value="1"
                                        <?php checked($settings['footer_show_socials'], 1); ?>
                                    >

                                    <?php
                                    esc_html_e(
                                        'Show footer social links',
                                        'music-project-core'
                                    );
                                    ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mpc_footer_layout">
                                <?php
                                esc_html_e(
                                    'Footer Layout',
                                    'music-project-core'
                                );
                                ?>
                            </label>
                        </th>

                        <td>
                            <select
                                id="mpc_footer_layout"
                                name="mpc_footer_settings[footer_layout]"
                                aria-describedby="mpc-footer-layout-description"
                            >
                                <?php foreach ($layouts as $value => $label) : ?>
                                    <option
                                        value="<?php echo esc_attr($value); ?>"
                                        <?php
                                        selected(
                                            $settings['footer_layout'],
                                            $value
                                        );
                                        ?>
                                    >
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div
                                id="mpc-footer-layout-description"
                                class="description"
                            >
                                <p>
                                    <strong>
                                        <?php
                                        esc_html_e(
                                            'Simple:',
                                            'music-project-core'
                                        );
                                        ?>
                                    </strong>

                                    <?php
                                    esc_html_e(
                                        'A straightforward left-aligned vertical footer.',
                                        'music-project-core'
                                    );
                                    ?>
                                </p>

                                <p>
                                    <strong>
                                        <?php
                                        esc_html_e(
                                            'Stacked / Centered:',
                                            'music-project-core'
                                        );
                                        ?>
                                    </strong>

                                    <?php
                                    esc_html_e(
                                        'Centers the brand, menu, social links, and copyright.',
                                        'music-project-core'
                                    );
                                    ?>
                                </p>

                                <p>
                                    <strong>
                                        <?php
                                        esc_html_e(
                                            'Split:',
                                            'music-project-core'
                                        );
                                        ?>
                                    </strong>

                                    <?php
                                    esc_html_e(
                                        'Places branding on the left and navigation or social links on the right at wider screen sizes.',
                                        'music-project-core'
                                    );
                                    ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}