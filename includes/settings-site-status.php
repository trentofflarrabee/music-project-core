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

function mpc_get_site_status_settings() {
    $defaults = mpc_get_site_status_defaults();
    $settings = get_option('mpc_site_status_settings', []);

    if (!is_array($settings)) {
        $settings = [];
    }

    return wp_parse_args($settings, $defaults);
}

function mpc_get_site_status_setting($key, $fallback = null) {
    $settings = mpc_get_site_status_settings();

    return array_key_exists($key, $settings) ? $settings[$key] : $fallback;
}

function mpc_sanitize_site_status_settings($input) {
    $defaults = mpc_get_site_status_defaults();
    $input = is_array($input) ? $input : [];
    $output = [];

    $allowed_modes = ['disabled', 'coming_soon', 'maintenance'];

    $mode = isset($input['mode'])
        ? sanitize_key($input['mode'])
        : $defaults['mode'];

    $output['mode'] = in_array($mode, $allowed_modes, true)
        ? $mode
        : $defaults['mode'];

    $output['heading'] = isset($input['heading'])
        ? sanitize_text_field($input['heading'])
        : $defaults['heading'];

    $output['message'] = isset($input['message'])
        ? sanitize_textarea_field($input['message'])
        : $defaults['message'];

    $output['button_text'] = isset($input['button_text'])
        ? sanitize_text_field($input['button_text'])
        : $defaults['button_text'];

    $output['button_url'] = isset($input['button_url'])
        ? esc_url_raw($input['button_url'])
        : $defaults['button_url'];

    $output['show_social_links'] = !empty($input['show_social_links']) ? 1 : 0;

    return $output;
}

function mpc_register_site_status_settings() {
    register_setting(
        'mpc_site_status_settings_group',
        'mpc_site_status_settings',
        'mpc_sanitize_site_status_settings'
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
    $preview_url = add_query_arg('mpc_preview_site_status', '1', home_url('/'));
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

function mpc_site_status_request_should_bypass() {
    if (is_admin()) {
        return true;
    }

    if (defined('DOING_CRON') && DOING_CRON) {
        return true;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return true;
    }

    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        return true;
    }

    if (defined('WP_CLI') && WP_CLI) {
        return true;
    }

    global $pagenow;

    if (in_array($pagenow, ['wp-login.php', 'wp-register.php'], true)) {
        return true;
    }

    return false;
}

function mpc_maybe_render_site_status_page() {
    if (mpc_site_status_request_should_bypass()) {
        return;
    }

    $settings = mpc_get_site_status_settings();
    $mode = sanitize_key($settings['mode'] ?? 'disabled');

    if ($mode === 'disabled') {
        return;
    }

    $preview_requested = isset($_GET['mpc_preview_site_status']) && current_user_can('manage_options');

    if (current_user_can('manage_options') && !$preview_requested) {
        return;
    }

    if ($mode === 'maintenance') {
        status_header(503);

        if (!headers_sent()) {
            header('Retry-After: 3600');
        }
    } else {
        status_header(200);
    }

    nocache_headers();

    if (!headers_sent()) {
        header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    }

    $template = locate_template('template-parts/site-status.php');

    if ($template) {
        load_template($template, false, [
            'settings' => $settings,
            'mode' => $mode,
        ]);
        exit;
    }

    $heading = !empty($settings['heading'])
        ? $settings['heading']
        : __('Coming Soon', 'music-project-core');

    $message = !empty($settings['message'])
        ? $settings['message']
        : __('We’re getting things ready. Check back soon.', 'music-project-core');

    wp_die(
        '<h1>' . esc_html($heading) . '</h1><p>' . nl2br(esc_html($message)) . '</p>',
        esc_html($heading),
        [
            'response' => $mode === 'maintenance' ? 503 : 200,
        ]
    );
}
add_action('template_redirect', 'mpc_maybe_render_site_status_page', 0);