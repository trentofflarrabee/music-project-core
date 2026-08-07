<?php
/**
 * Site Status settings and frontend gate.
 *
 * Handles Coming Soon / Parking Page / Maintenance Mode.
 */

if (!defined('ABSPATH')) {
    exit;
}

function mpc_get_site_status_defaults() {
    return [
        'mode' => 'disabled',
        'heading' => __('Coming Soon', 'music-project-core'),
        'message' => __('We’re getting things ready. Check back soon.', 'music-project-core'),
        'button_text' => '',
        'button_url' => '',
        'show_social_links' => 1,
    ];
}

/**
 * Get normalized Site Status settings.
 *
 * Stored options may come from an earlier release, direct database editing,
 * or third-party code. Normalize Core-owned values before they reach public
 * rendering while retaining unknown extension-owned settings.
 *
 * @return array
 */
function mpc_get_site_status_settings() {
    $defaults = mpc_get_site_status_defaults();

    $settings = get_option(
        'mpc_site_status_settings',
        []
    );

    if (!is_array($settings)) {
        $settings = [];
    }

    $settings = wp_parse_args(
        $settings,
        $defaults
    );

    /*
     * Retain unknown keys for future extensions, then overwrite Core-owned
     * values with their normalized forms.
     */
    $normalized = $settings;

    $allowed_modes = [
        'disabled',
        'coming_soon',
        'maintenance',
    ];

    $mode = (
        isset($settings['mode'])
        && is_scalar($settings['mode'])
    )
        ? sanitize_key(
            (string) $settings['mode']
        )
        : $defaults['mode'];

    $normalized['mode'] = in_array(
        $mode,
        $allowed_modes,
        true
    )
        ? $mode
        : $defaults['mode'];

    $normalized['heading'] = (
        isset($settings['heading'])
        && is_scalar($settings['heading'])
    )
        ? sanitize_text_field(
            (string) $settings['heading']
        )
        : $defaults['heading'];

    $normalized['message'] = (
        isset($settings['message'])
        && is_scalar($settings['message'])
    )
        ? sanitize_textarea_field(
            (string) $settings['message']
        )
        : $defaults['message'];

    $normalized['button_text'] = (
        isset($settings['button_text'])
        && is_scalar($settings['button_text'])
    )
        ? sanitize_text_field(
            (string) $settings['button_text']
        )
        : $defaults['button_text'];

    $normalized['button_url'] = (
        isset($settings['button_url'])
        && is_scalar($settings['button_url'])
    )
        ? esc_url_raw(
            (string) $settings['button_url'],
            [
                'http',
                'https',
            ]
        )
        : $defaults['button_url'];

    $show_social_links = (
        isset($settings['show_social_links'])
        && is_scalar(
            $settings['show_social_links']
        )
    )
        ? strtolower(
            trim(
                (string) $settings[
                    'show_social_links'
                ]
            )
        )
        : (
            !empty($defaults['show_social_links'])
                ? '1'
                : '0'
        );

    $normalized['show_social_links'] = in_array(
        $show_social_links,
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

    return $normalized;
}

function mpc_get_site_status_setting($key, $fallback = null) {
    $settings = mpc_get_site_status_settings();

    return array_key_exists($key, $settings) ? $settings[$key] : $fallback;
}

/**
 * Get the normalized Site Status mode.
 *
 * @return string
 */
function mpc_get_site_status_mode() {
    $mode = sanitize_key(
        (string) mpc_get_site_status_setting(
            'mode',
            'disabled'
        )
    );

    return in_array(
        $mode,
        ['disabled', 'coming_soon', 'maintenance'],
        true
    )
        ? $mode
        : 'disabled';
}

/**
 * Determine whether a public Site Status mode is active.
 *
 * @return bool
 */
function mpc_is_site_status_active() {
    return mpc_get_site_status_mode() !== 'disabled';
}

/**
 * Determine whether an administrator requested a status-page preview.
 *
 * Preview is read-only and remains restricted to manage_options users.
 *
 * @return bool
 */
function mpc_is_site_status_preview() {
    if (
        !current_user_can('manage_options')
        || !isset($_GET['mpc_preview_site_status'])
        || !is_string($_GET['mpc_preview_site_status'])
    ) {
        return false;
    }

    $preview_value = sanitize_key(
        wp_unslash(
            $_GET['mpc_preview_site_status']
        )
    );

    return $preview_value === '1';
}

/**
 * Get the Site Status preview URL.
 *
 * @return string
 */
function mpc_get_site_status_preview_url() {
    $url = add_query_arg(
        'mpc_preview_site_status',
        '1',
        home_url('/')
    );

    /**
     * Filter the Site Status preview URL.
     *
     * @param string $url Preview URL.
     */
    return (string) apply_filters(
        'mpc_site_status_preview_url',
        $url
    );
}

/**
 * Sanitize Site Status settings.
 *
 * @param mixed $input Submitted settings.
 * @return array
 */
function mpc_sanitize_site_status_settings($input) {
    $defaults = mpc_get_site_status_defaults();

    $input = is_array($input)
        ? $input
        : [];

    $allowed_modes = [
        'disabled',
        'coming_soon',
        'maintenance',
    ];

    $mode = (
        isset($input['mode'])
        && is_scalar($input['mode'])
    )
        ? sanitize_key(
            (string) $input['mode']
        )
        : $defaults['mode'];

    $heading = (
        isset($input['heading'])
        && is_scalar($input['heading'])
    )
        ? sanitize_text_field(
            (string) $input['heading']
        )
        : $defaults['heading'];

    $message = (
        isset($input['message'])
        && is_scalar($input['message'])
    )
        ? sanitize_textarea_field(
            (string) $input['message']
        )
        : $defaults['message'];

    $button_text = (
        isset($input['button_text'])
        && is_scalar($input['button_text'])
    )
        ? sanitize_text_field(
            (string) $input['button_text']
        )
        : $defaults['button_text'];

    $button_url = (
        isset($input['button_url'])
        && is_scalar($input['button_url'])
    )
        ? esc_url_raw(
            (string) $input['button_url'],
            [
                'http',
                'https',
            ]
        )
        : $defaults['button_url'];

    $show_social_links = (
        isset($input['show_social_links'])
        && is_scalar($input['show_social_links'])
        && in_array(
            strtolower(
                trim(
                    (string) $input['show_social_links']
                )
            ),
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        )
    )
        ? 1
        : 0;

    return [
        'mode' => in_array(
            $mode,
            $allowed_modes,
            true
        )
            ? $mode
            : $defaults['mode'],
        'heading'           => $heading,
        'message'           => $message,
        'button_text'       => $button_text,
        'button_url'        => $button_url,
        'show_social_links' => $show_social_links,
    ];
}

function mpc_register_site_status_settings() {
    register_setting(
        'mpc_site_status_settings_group',
        'mpc_site_status_settings',
        [
            'type'              => 'array',
            'sanitize_callback' =>
                'mpc_sanitize_site_status_settings',
            'default'           =>
                mpc_get_site_status_defaults(),
        ]
    );
}
add_action('admin_init', 'mpc_register_site_status_settings');

function mpc_register_site_status_submenu() {
    add_submenu_page(
        'mpc-homepage',
        __('Site Status', 'music-project-core'),
        __('Site Status', 'music-project-core'),
        'manage_options',
        'mpc-site-status',
        'mpc_render_site_status_settings_page'
    );
}
add_action('admin_menu', 'mpc_register_site_status_submenu', 30);

function mpc_render_site_status_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_site_status_settings();
    $preview_url = mpc_get_site_status_preview_url();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Site Status', 'music-project-core'); ?></h1>

        <p>
            <?php esc_html_e('Show a Coming Soon or Maintenance page to public visitors while admins continue to view and edit the real site.', 'music-project-core'); ?>
        </p>

        <?php if (($settings['mode'] ?? 'disabled') !== 'disabled') : ?>
            <div class="notice notice-warning inline">
                <p>
                    <strong><?php esc_html_e('Site Status is active.', 'music-project-core'); ?></strong>
                    <?php esc_html_e('Logged-out visitors will see the status page. Logged-in admins will see the real site.', 'music-project-core'); ?>
                </p>
            </div>

            <p>
                <a href="<?php echo esc_url($preview_url); ?>" class="button" target="_blank" rel="noopener">
                    <?php esc_html_e('Preview Status Page', 'music-project-core'); ?>
                </a>
            </p>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_site_status_settings_group'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="mpc_site_status_mode">
                            <?php esc_html_e('Mode', 'music-project-core'); ?>
                        </label>
                    </th>
                    <td>
                        <select
                            id="mpc_site_status_mode"
                            name="mpc_site_status_settings[mode]"
                        >
                            <option value="disabled" <?php selected($settings['mode'], 'disabled'); ?>>
                                <?php esc_html_e('Disabled', 'music-project-core'); ?>
                            </option>
                            <option value="coming_soon" <?php selected($settings['mode'], 'coming_soon'); ?>>
                                <?php esc_html_e('Coming Soon / Parking Page', 'music-project-core'); ?>
                            </option>
                            <option value="maintenance" <?php selected($settings['mode'], 'maintenance'); ?>>
                                <?php esc_html_e('Maintenance Mode', 'music-project-core'); ?>
                            </option>
                        </select>

                        <p class="description">
                            <?php esc_html_e('Admins can still view the real site while logged in.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="mpc_site_status_heading">
                            <?php esc_html_e('Heading', 'music-project-core'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="mpc_site_status_heading"
                            name="mpc_site_status_settings[heading]"
                            value="<?php echo esc_attr($settings['heading']); ?>"
                            class="regular-text"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="mpc_site_status_message">
                            <?php esc_html_e('Message', 'music-project-core'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            id="mpc_site_status_message"
                            name="mpc_site_status_settings[message]"
                            rows="5"
                            class="large-text"
                        ><?php echo esc_textarea($settings['message']); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="mpc_site_status_button_text">
                            <?php esc_html_e('Button Text', 'music-project-core'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="mpc_site_status_button_text"
                            name="mpc_site_status_settings[button_text]"
                            value="<?php echo esc_attr($settings['button_text']); ?>"
                            class="regular-text"
                        >

                        <p class="description">
                            <?php esc_html_e('Optional. Leave blank to hide the button.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="mpc_site_status_button_url">
                            <?php esc_html_e('Button URL', 'music-project-core'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="url"
                            id="mpc_site_status_button_url"
                            name="mpc_site_status_settings[button_url]"
                            value="<?php echo esc_attr($settings['button_url']); ?>"
                            class="regular-text"
                            placeholder="https://example.com/contact"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e('Social Links', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="mpc_site_status_settings[show_social_links]"
                                value="1"
                                <?php checked(!empty($settings['show_social_links'])); ?>
                            >
                            <?php esc_html_e('Show social links on the status page', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Site Status', 'music-project-core')); ?>
        </form>
    </div>
    <?php
}

/**
 * Determine whether Site Status should leave the current request alone.
 *
 * @return bool
 */
function mpc_site_status_request_should_bypass() {
    $should_bypass = false;

    if (is_admin()) {
        $should_bypass = true;
    } elseif (
        defined('DOING_CRON')
        && DOING_CRON
    ) {
        $should_bypass = true;
    } elseif (
        defined('REST_REQUEST')
        && REST_REQUEST
    ) {
        $should_bypass = true;
    } elseif (
        function_exists('wp_doing_ajax')
        && wp_doing_ajax()
    ) {
        $should_bypass = true;
    } elseif (
        defined('WP_CLI')
        && WP_CLI
    ) {
        $should_bypass = true;
    } elseif (
        function_exists('is_robots')
        && is_robots()
    ) {
        /*
         * Keep robots.txt available. Public HTML responses still receive
         * noindex directives from the status gate.
         */
        $should_bypass = true;
    } else {
        global $pagenow;

        if (
            in_array(
                $pagenow,
                ['wp-login.php', 'wp-register.php'],
                true
            )
        ) {
            $should_bypass = true;
        }
    }

    /**
     * Filter whether the current request bypasses Site Status.
     *
     * Integrations may use this for webhooks, custom API endpoints,
     * payment callbacks, or other machine-facing requests.
     *
     * @param bool $should_bypass Whether to bypass the status page.
     */
    return (bool) apply_filters(
        'mpc_site_status_request_should_bypass',
        $should_bypass
    );
}

/**
 * Determine whether the request expects machine-readable output.
 *
 * Feeds and WordPress sitemaps should not receive the HTML status template.
 *
 * @return bool
 */
function mpc_site_status_is_machine_request() {
    $is_machine_request = (
        function_exists('is_feed')
        && is_feed()
    );

    if (!$is_machine_request) {
        $sitemap = get_query_var(
            'sitemap',
            ''
        );

        $is_machine_request = (
            is_string($sitemap)
            && $sitemap !== ''
        );
    }

    /**
     * Filter whether the request expects machine-readable output.
     *
     * Plugins providing their own sitemap or feed routes can extend this.
     *
     * @param bool $is_machine_request Whether this is a machine request.
     */
    return (bool) apply_filters(
        'mpc_site_status_is_machine_request',
        $is_machine_request
    );
}

/**
 * Send the public Site Status response headers.
 *
 * @param string $mode Active status mode.
 * @return void
 */
function mpc_send_site_status_headers($mode) {
    $mode = sanitize_key((string) $mode);

    if ($mode === 'maintenance') {
        status_header(503);

        $retry_after = absint(
            apply_filters(
                'mpc_site_status_retry_after',
                3600
            )
        );

        if (
            !headers_sent()
            && $retry_after > 0
        ) {
            header(
                'Retry-After: ' . $retry_after,
                true
            );
        }
    } else {
        status_header(200);
    }

    nocache_headers();

    if (!headers_sent()) {
        header(
            'X-Robots-Tag: noindex, nofollow, noarchive',
            true
        );

        /*
         * The response differs for authenticated administrators and
         * logged-out visitors.
         */
        header(
            'Vary: Cookie',
            false
        );
    }
}

/**
 * Render a simple response for feeds and sitemaps.
 *
 * @param array  $settings Site Status settings.
 * @param string $mode     Active status mode.
 * @return void
 */
function mpc_render_site_status_machine_response(
    $settings,
    $mode
) {
    if (!headers_sent()) {
        header(
            'Content-Type: text/plain; charset='
                . get_bloginfo('charset'),
            true
        );
    }

    $heading = !empty($settings['heading'])
        ? sanitize_text_field($settings['heading'])
        : (
            $mode === 'maintenance'
                ? __(
                    'Temporarily Unavailable',
                    'music-project-core'
                )
                : __(
                    'Coming Soon',
                    'music-project-core'
                )
        );

    echo wp_strip_all_tags($heading);
    echo "\n";

    exit;
}

/**
 * Render the plugin-owned fallback when the theme template is unavailable.
 *
 * @param array  $settings   Site Status settings.
 * @param string $mode       Active status mode.
 * @param bool   $previewing Whether an administrator is previewing.
 * @return void
 */
function mpc_render_site_status_fallback(
    $settings,
    $mode,
    $previewing = false
) {
    $heading = !empty($settings['heading'])
        ? sanitize_text_field($settings['heading'])
        : (
            $mode === 'maintenance'
                ? __(
                    'Maintenance Mode',
                    'music-project-core'
                )
                : __(
                    'Coming Soon',
                    'music-project-core'
                )
        );

    $message = !empty($settings['message'])
        ? sanitize_textarea_field($settings['message'])
        : __(
            'We’re getting things ready. Check back soon.',
            'music-project-core'
        );

    $button_text = !empty($settings['button_text'])
        ? sanitize_text_field($settings['button_text'])
        : '';

    $button_url = !empty($settings['button_url'])
        ? esc_url($settings['button_url'])
        : '';

    $content = '<div class="mpc-site-status-fallback">';
    $content .= '<h1>' . esc_html($heading) . '</h1>';
    $content .= '<p>'
        . nl2br(esc_html($message))
        . '</p>';

    if (
        $button_text !== ''
        && $button_url !== ''
    ) {
        $content .= sprintf(
            '<p><a href="%1$s">%2$s</a></p>',
            $button_url,
            esc_html($button_text)
        );
    }

    if ($previewing) {
        $content .= sprintf(
            '<p><a href="%1$s">%2$s</a></p>',
            esc_url(home_url('/')),
            esc_html__(
                'Exit Preview',
                'music-project-core'
            )
        );
    }

    $content .= '</div>';

    wp_die(
        $content, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $heading,
        [
            'response'  => $mode === 'maintenance'
                ? 503
                : 200,
            'back_link' => false,
        ]
    );
}

/**
 * Intercept public requests when Site Status is active.
 *
 * @return void
 */
function mpc_maybe_render_site_status_page() {
    if (mpc_site_status_request_should_bypass()) {
        return;
    }

    $settings = mpc_get_site_status_settings();
    $mode = mpc_get_site_status_mode();

    if ($mode === 'disabled') {
        return;
    }

    $previewing = mpc_is_site_status_preview();

    if (
        current_user_can('manage_options')
        && !$previewing
    ) {
        return;
    }

    mpc_send_site_status_headers($mode);

    if (mpc_site_status_is_machine_request()) {
        mpc_render_site_status_machine_response(
            $settings,
            $mode
        );
    }

    $template = locate_template(
        'template-parts/site-status.php'
    );

    /**
     * Filter the Site Status template path.
     *
     * Returning an empty value causes Core to use its minimal fallback.
     *
     * @param string $template Located template path.
     * @param array  $settings Site Status settings.
     * @param string $mode     Active status mode.
     */
    $template = apply_filters(
        'mpc_site_status_template',
        $template,
        $settings,
        $mode
    );

    if (
        is_string($template)
        && $template !== ''
        && is_readable($template)
    ) {
        load_template(
            $template,
            false,
            [
                'settings'   => $settings,
                'mode'       => $mode,
                'previewing' => $previewing,
            ]
        );

        exit;
    }

    mpc_render_site_status_fallback(
        $settings,
        $mode,
        $previewing
    );
}
add_action(
    'template_redirect',
    'mpc_maybe_render_site_status_page',
    0
);

/**
 * Add an active Site Status warning to the WordPress admin bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 * @return void
 */
function mpc_add_site_status_admin_bar_node(
    $wp_admin_bar
) {
    if (
        !is_admin_bar_showing()
        || !current_user_can('manage_options')
        || !mpc_is_site_status_active()
    ) {
        return;
    }

    $mode = mpc_get_site_status_mode();

    $mode_label = $mode === 'maintenance'
        ? __(
            'Maintenance',
            'music-project-core'
        )
        : __(
            'Coming Soon',
            'music-project-core'
        );

    $wp_admin_bar->add_node(
        [
            'id'    => 'mpc-site-status',
            'title' => sprintf(
                /* translators: %s is the active Site Status mode. */
                __(
                    'Site Status: %s',
                    'music-project-core'
                ),
                $mode_label
            ),
            'href'  => admin_url(
                'admin.php?page=mpc-site-status'
            ),
        ]
    );

    $wp_admin_bar->add_node(
        [
            'id'     => 'mpc-site-status-preview',
            'parent' => 'mpc-site-status',
            'title'  => __(
                'Preview Status Page',
                'music-project-core'
            ),
            'href'   => mpc_get_site_status_preview_url(),
            'meta'   => [
                'target' => '_blank',
                'rel'    => 'noopener',
            ],
        ]
    );
}
add_action(
    'admin_bar_menu',
    'mpc_add_site_status_admin_bar_node',
    100
);