<?php
/**
 * Footer Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

function mpc_get_footer_layout_options() {
    return [
        'simple' => 'Simple',
        'stacked' => 'Stacked / Centered',
        'split' => 'Split',
    ];
}

function mpc_get_footer_defaults() {
    return [
        'footer_tagline' => '',
        'footer_copyright' => '© {year} {site_name}. All rights reserved.',
        'footer_show_brand' => 1,
        'footer_show_menu' => 1,
        'footer_show_socials' => 1,
        'footer_layout' => 'simple',
    ];
}

function mpc_get_footer_settings() {
    $settings = get_option('mpc_footer_settings', []);

    if (!is_array($settings)) {
        $settings = [];
    }

    return wp_parse_args($settings, mpc_get_footer_defaults());
}

function mpc_get_footer_setting($key, $default = '') {
    $settings = mpc_get_footer_settings();

    return isset($settings[$key]) ? $settings[$key] : $default;
}

function mpc_parse_footer_tokens($text) {
    $replacements = [
        '{year}' => date_i18n('Y'),
        '{site_name}' => get_bloginfo('name'),
    ];

    return strtr($text, $replacements);
}

function mpc_sanitize_footer_settings($input) {
    $input = is_array($input) ? $input : [];
    $output = mpc_get_footer_defaults();

    $output['footer_tagline'] = isset($input['footer_tagline'])
        ? sanitize_text_field($input['footer_tagline'])
        : '';

    $output['footer_copyright'] = isset($input['footer_copyright'])
        ? sanitize_text_field($input['footer_copyright'])
        : $output['footer_copyright'];

    $output['footer_show_brand'] = !empty($input['footer_show_brand']) ? 1 : 0;
    $output['footer_show_menu'] = !empty($input['footer_show_menu']) ? 1 : 0;
    $output['footer_show_socials'] = !empty($input['footer_show_socials']) ? 1 : 0;

    $allowed_layouts = array_keys(mpc_get_footer_layout_options());
    $layout = isset($input['footer_layout']) ? sanitize_key($input['footer_layout']) : 'simple';

    $output['footer_layout'] = in_array($layout, $allowed_layouts, true) ? $layout : 'simple';

    return $output;
}

function mpc_register_footer_settings() {
    register_setting(
        'mpc_footer_settings_group',
        'mpc_footer_settings',
        'mpc_sanitize_footer_settings'
    );
}
add_action('admin_init', 'mpc_register_footer_settings');

function mpc_add_footer_admin_menu() {
    add_submenu_page(
        'mpc-homepage',
        'Footer',
        'Footer',
        'manage_options',
        'mpc-footer',
        'mpc_render_footer_settings_page'
    );
}
add_action('admin_menu', 'mpc_add_footer_admin_menu', 12);

function mpc_render_footer_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_footer_settings();
    $layouts = mpc_get_footer_layout_options();
    ?>
    <div class="wrap">
        <h1>Footer Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_footer_settings_group'); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="mpc_footer_tagline">Footer Tagline</label>
                        </th>
                        <td>
                            <input
                                id="mpc_footer_tagline"
                                class="regular-text"
                                type="text"
                                name="mpc_footer_settings[footer_tagline]"
                                value="<?php echo esc_attr($settings['footer_tagline']); ?>"
                                placeholder="New record out now."
                            >
                            <p class="description">
                                Optional short line shown under the footer brand.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mpc_footer_copyright">Copyright Text</label>
                        </th>
                        <td>
                            <input
                                id="mpc_footer_copyright"
                                class="regular-text"
                                type="text"
                                name="mpc_footer_settings[footer_copyright]"
                                value="<?php echo esc_attr($settings['footer_copyright']); ?>"
                            >
                            <p class="description">
                                Available tokens: <code>{year}</code>, <code>{site_name}</code>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Footer Visibility</th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    name="mpc_footer_settings[footer_show_brand]"
                                    value="1"
                                    <?php checked($settings['footer_show_brand'], 1); ?>
                                >
                                Show footer brand/logo
                            </label>

                            <br>

                            <label>
                                <input
                                    type="checkbox"
                                    name="mpc_footer_settings[footer_show_menu]"
                                    value="1"
                                    <?php checked($settings['footer_show_menu'], 1); ?>
                                >
                                Show footer menu
                            </label>

                            <br>

                            <label>
                                <input
                                    type="checkbox"
                                    name="mpc_footer_settings[footer_show_socials]"
                                    value="1"
                                    <?php checked($settings['footer_show_socials'], 1); ?>
                                >
                                Show footer social links
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mpc_footer_layout">Footer Layout</label>
                        </th>
                        <td>
                            <select
                                id="mpc_footer_layout"
                                name="mpc_footer_settings[footer_layout]"
                            >
                                <?php foreach ($layouts as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['footer_layout'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <p class="description">
                                Simple is the default. Stacked centers the footer. Split places brand content left and nav/socials right on desktop.
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}