<?php
/**
 * Social Links Settings
 *
 * Stores global social/profile links and display preferences.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the canonical social-link platform definitions.
 *
 * Core owns the data model. Themes remain responsible for icons, markup,
 * layout, and presentation.
 *
 * @return array
 */
function mpc_get_social_link_items() {
    $items = [
        'instagram' => [
            'label'       => __('Instagram', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://instagram.com/yourproject',
            'external'    => true,
        ],
        'spotify' => [
            'label'       => __('Spotify', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://open.spotify.com/artist/...',
            'external'    => true,
        ],
        'apple_music' => [
            'label'       => __('Apple Music', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://music.apple.com/...',
            'external'    => true,
        ],
        'bandcamp' => [
            'label'       => __('Bandcamp', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://yourproject.bandcamp.com',
            'external'    => true,
        ],
        'youtube' => [
            'label'       => __('YouTube', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://youtube.com/@yourproject',
            'external'    => true,
        ],
        'tiktok' => [
            'label'       => __('TikTok', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://tiktok.com/@yourproject',
            'external'    => true,
        ],
        'soundcloud' => [
            'label'       => __('SoundCloud', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://soundcloud.com/yourproject',
            'external'    => true,
        ],
        'facebook' => [
            'label'       => __('Facebook', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://facebook.com/yourproject',
            'external'    => true,
        ],
        'website' => [
            'label'       => __('Website', 'music-project-core'),
            'type'        => 'url',
            'placeholder' => 'https://example.com',
            'external'    => true,
        ],
        'email' => [
            'label'       => __('Email', 'music-project-core'),
            'type'        => 'email',
            'placeholder' => 'booking@example.com',
            'external'    => false,
        ],
    ];

    /**
     * Filter the available social-link platforms.
     *
     * Extensions may add a platform by supplying a unique key and:
     *
     * - label
     * - type: url or email
     * - placeholder
     * - external
     *
     * @param array $items Platform definitions.
     */
    $items = apply_filters(
        'mpc_social_link_items',
        $items
    );

    if (!is_array($items)) {
        return [];
    }

    $normalized = [];

    foreach ($items as $key => $item) {
        $key = sanitize_key((string) $key);

        if (
            $key === ''
            || !is_array($item)
        ) {
            continue;
        }

        $type = isset($item['type'])
            ? sanitize_key((string) $item['type'])
            : 'url';

        if (!in_array($type, ['url', 'email'], true)) {
            $type = 'url';
        }

        $label = isset($item['label'])
            ? sanitize_text_field((string) $item['label'])
            : '';

        if ($label === '') {
            $label = ucwords(
                str_replace(
                    ['_', '-'],
                    ' ',
                    $key
                )
            );
        }

        $normalized[$key] = [
            'label'       => $label,
            'type'        => $type,
            'placeholder' => isset($item['placeholder'])
                ? sanitize_text_field(
                    (string) $item['placeholder']
                )
                : '',
            'external'    => $type === 'email'
                ? false
                : (
                    !array_key_exists('external', $item)
                    || !empty($item['external'])
                ),
        ];
    }

    return $normalized;
}

/**
 * Get the supported frontend display modes.
 *
 * @return array
 */
function mpc_get_social_display_options() {
    return [
        'labels' => __(
            'Text Labels',
            'music-project-core'
        ),
        'icons' => __(
            'Icons Only',
            'music-project-core'
        ),
        'icons_labels' => __(
            'Icons + Labels',
            'music-project-core'
        ),
    ];
}

/**
 * Get Social Links defaults.
 *
 * @return array
 */
function mpc_get_social_links_defaults() {
    $defaults = [];

    foreach (mpc_get_social_link_items() as $key => $item) {
        $defaults[$key] = '';
    }

    $defaults['hero_display'] = 'labels';
    $defaults['footer_display'] = 'labels';

    return $defaults;
}

/**
 * Get normalized Social Links settings.
 *
 * @return array
 */
function mpc_get_social_links_settings() {
    $settings = get_option(
        'mpc_social_links_settings',
        []
    );

    if (!is_array($settings)) {
        $settings = [];
    }

    return wp_parse_args(
        $settings,
        mpc_get_social_links_defaults()
    );
}

/**
 * Get one Social Links setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function mpc_get_social_links_setting(
    $key,
    $default = ''
) {
    $settings = mpc_get_social_links_settings();

    return array_key_exists($key, $settings)
        ? $settings[$key]
        : $default;
}

/**
 * Get a validated display mode for a rendering context.
 *
 * Hero and Footer have saved display settings. Other contexts may pass
 * their own default, such as Icons Only for the mobile navigation.
 *
 * @param string $context Context identifier.
 * @param string $default Default display mode.
 * @return string
 */
function mpc_get_social_display_mode(
    $context,
    $default = 'labels'
) {
    $context = sanitize_key((string) $context);
    $allowed = array_keys(
        mpc_get_social_display_options()
    );

    $default = sanitize_key((string) $default);

    if (!in_array($default, $allowed, true)) {
        $default = 'labels';
    }

    $setting_key = $context !== ''
        ? $context . '_display'
        : '';

    $display = $setting_key !== ''
        ? sanitize_key(
            (string) mpc_get_social_links_setting(
                $setting_key,
                $default
            )
        )
        : $default;

    if (!in_array($display, $allowed, true)) {
        $display = $default;
    }

    /**
     * Filter the social-link display mode.
     *
     * @param string $display Validated display mode.
     * @param string $context Rendering context.
     */
    return (string) apply_filters(
        'mpc_social_display_mode',
        $display,
        $context
    );
}

/**
 * Get normalized, configured social links.
 *
 * This helper supplies a rendering-neutral contract for themes and other
 * integrations. It does not generate icons or markup.
 *
 * @param string $context Optional rendering context.
 * @return array
 */
function mpc_get_social_links($context = '') {
    $context = sanitize_key((string) $context);
    $settings = mpc_get_social_links_settings();
    $items = mpc_get_social_link_items();
    $links = [];

    foreach ($items as $key => $item) {
        $value = isset($settings[$key])
            ? trim((string) $settings[$key])
            : '';

        if ($value === '') {
            continue;
        }

        if ($item['type'] === 'email') {
            $email = sanitize_email($value);

            if (
                $email === ''
                || !is_email($email)
            ) {
                continue;
            }

            $url = 'mailto:' . $email;
        } else {
            $url = esc_url_raw(
                $value,
                ['http', 'https']
            );

            if ($url === '') {
                continue;
            }
        }

        $links[] = [
            'key'      => $key,
            'label'    => $item['label'],
            'url'      => $url,
            'type'     => $item['type'],
            'external' => !empty($item['external']),
        ];
    }

    /**
     * Filter normalized social links before rendering.
     *
     * @param array  $links    Configured social links.
     * @param string $context  Rendering context.
     * @param array  $settings Complete saved settings.
     * @param array  $items    Platform definitions.
     */
    $links = apply_filters(
        'mpc_social_links',
        $links,
        $context,
        $settings,
        $items
    );

    if (!is_array($links)) {
        return [];
    }

    $normalized = [];

    foreach ($links as $link) {
        if (!is_array($link)) {
            continue;
        }

        $key = isset($link['key'])
            ? sanitize_key((string) $link['key'])
            : '';

        $label = isset($link['label'])
            ? sanitize_text_field(
                (string) $link['label']
            )
            : '';

        $url = isset($link['url'])
            ? esc_url_raw(
                (string) $link['url'],
                ['http', 'https', 'mailto']
            )
            : '';

        if (
            $key === ''
            || $label === ''
            || $url === ''
        ) {
            continue;
        }

        $type = isset($link['type'])
            ? sanitize_key((string) $link['type'])
            : 'url';

        $external = (
            !empty($link['external'])
            && strpos($url, 'mailto:') !== 0
        );

        $normalized[] = [
            'key'      => $key,
            'label'    => $label,
            'url'      => $url,
            'type'     => $type,
            'external' => $external,
        ];
    }

    return $normalized;
}

/**
 * Sanitize Social Links settings.
 *
 * Unknown existing scalar keys are retained so temporarily unavailable
 * platform extensions do not silently lose their saved data.
 *
 * @param mixed $input Submitted settings.
 * @return array
 */
function mpc_sanitize_social_links_settings($input) {
    $input = is_array($input)
        ? $input
        : [];

    $defaults = mpc_get_social_links_defaults();

    $current = get_option(
        'mpc_social_links_settings',
        []
    );

    $output = [];

    /*
     * Preserve unknown scalar settings so temporarily unavailable platform
     * extensions do not silently lose their saved data.
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

    foreach (
        mpc_get_social_link_items()
        as $key => $item
    ) {
        $value = (
            isset($input[$key])
            && is_scalar($input[$key])
        )
            ? trim(
                (string) $input[$key]
            )
            : '';

        if ($item['type'] === 'email') {
            $output[$key] = sanitize_email(
                $value
            );
        } else {
            $output[$key] = esc_url_raw(
                $value,
                ['http', 'https']
            );
        }
    }

    $allowed_displays = array_keys(
        mpc_get_social_display_options()
    );

    foreach (
        [
            'hero_display',
            'footer_display',
        ]
        as $display_key
    ) {
        $default_display = isset(
            $defaults[$display_key]
        )
            ? sanitize_key(
                (string) $defaults[$display_key]
            )
            : 'labels';

        $value = (
            isset($input[$display_key])
            && is_scalar($input[$display_key])
        )
            ? sanitize_key(
                (string) $input[$display_key]
            )
            : $default_display;

        $output[$display_key] = in_array(
            $value,
            $allowed_displays,
            true
        )
            ? $value
            : $default_display;
    }

    return $output;
}

/**
 * Register Social Links settings.
 *
 * @return void
 */
function mpc_register_social_links_settings() {
    register_setting(
        'mpc_social_links_settings_group',
        'mpc_social_links_settings',
        [
            'type'              => 'array',
            'sanitize_callback' =>
                'mpc_sanitize_social_links_settings',
            'default'           =>
                mpc_get_social_links_defaults(),
        ]
    );
}
add_action(
    'admin_init',
    'mpc_register_social_links_settings'
);

/**
 * Register the Social Links submenu.
 *
 * @return void
 */
function mpc_add_social_links_admin_menu() {
    add_submenu_page(
        'mpc-homepage',
        __(
            'Social Links',
            'music-project-core'
        ),
        __(
            'Social Links',
            'music-project-core'
        ),
        'manage_options',
        'mpc-social-links',
        'mpc_render_social_links_settings_page'
    );
}
add_action(
    'admin_menu',
    'mpc_add_social_links_admin_menu',
    10
);

/**
 * Render one social-display select field.
 *
 * @param array  $settings    Saved settings.
 * @param string $key         Setting key.
 * @param string $label       Field label.
 * @param string $description Field description.
 * @return void
 */
function mpc_render_social_display_select(
    $settings,
    $key,
    $label,
    $description = ''
) {
    $options = mpc_get_social_display_options();
    ?>
    <tr>
        <th scope="row">
            <label
                for="mpc_social_<?php echo esc_attr($key); ?>"
            >
                <?php echo esc_html($label); ?>
            </label>
        </th>

        <td>
            <select
                id="mpc_social_<?php echo esc_attr($key); ?>"
                name="mpc_social_links_settings[<?php echo esc_attr($key); ?>]"
            >
                <?php foreach ($options as $value => $option_label) : ?>
                    <option
                        value="<?php echo esc_attr($value); ?>"
                        <?php
                        selected(
                            $settings[$key] ?? '',
                            $value
                        );
                        ?>
                    >
                        <?php echo esc_html($option_label); ?>
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
 * Render the Social Links settings page.
 *
 * @return void
 */
function mpc_render_social_links_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_social_links_settings();
    $items = mpc_get_social_link_items();
    ?>
    <div class="wrap">
        <h1>
            <?php
            esc_html_e(
                'Social Links',
                'music-project-core'
            );
            ?>
        </h1>

        <p>
            <?php
            esc_html_e(
                'Add the profiles and contact links used throughout the site. Leave any field blank to hide that platform.',
                'music-project-core'
            );
            ?>
        </p>

        <form method="post" action="options.php">
            <?php
            settings_fields(
                'mpc_social_links_settings_group'
            );
            ?>

            <h2>
                <?php
                esc_html_e(
                    'Links',
                    'music-project-core'
                );
                ?>
            </h2>

            <table
                class="form-table"
                role="presentation"
            >
                <tbody>
                    <?php foreach ($items as $key => $item) : ?>
                        <tr>
                            <th scope="row">
                                <label
                                    for="mpc_social_<?php echo esc_attr($key); ?>"
                                >
                                    <?php
                                    echo esc_html(
                                        $item['label']
                                    );
                                    ?>
                                </label>
                            </th>

                            <td>
                                <input
                                    id="mpc_social_<?php echo esc_attr($key); ?>"
                                    class="regular-text"
                                    type="<?php echo esc_attr($item['type']); ?>"
                                    name="mpc_social_links_settings[<?php echo esc_attr($key); ?>]"
                                    value="<?php echo esc_attr($settings[$key] ?? ''); ?>"
                                    placeholder="<?php echo esc_attr($item['placeholder']); ?>"
                                    autocomplete="<?php echo $item['type'] === 'email' ? 'email' : 'url'; ?>"
                                >
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>
                <?php
                esc_html_e(
                    'Display Options',
                    'music-project-core'
                );
                ?>
            </h2>

            <table
                class="form-table"
                role="presentation"
            >
                <tbody>
                    <?php
                    mpc_render_social_display_select(
                        $settings,
                        'hero_display',
                        __(
                            'Hero Social Display',
                            'music-project-core'
                        ),
                        __(
                            'Controls how social links appear in the homepage hero.',
                            'music-project-core'
                        )
                    );

                    mpc_render_social_display_select(
                        $settings,
                        'footer_display',
                        __(
                            'Footer Social Display',
                            'music-project-core'
                        ),
                        __(
                            'Controls how social links appear in the site footer.',
                            'music-project-core'
                        )
                    );
                    ?>
                </tbody>
            </table>

            <p class="description">
                <?php
                esc_html_e(
                    'Profile and website links open in a new browser tab. Email opens the visitor’s email application.',
                    'music-project-core'
                );
                ?>
            </p>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}