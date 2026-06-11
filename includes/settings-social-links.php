<?php
/**
 * Social Links Settings
 *
 * Stores global social/profile links and display preferences.
 */

if (!defined('ABSPATH')) {
    exit;
}

function mpc_get_social_link_items() {
    return [
        'instagram' => [
            'label' => 'Instagram',
            'type' => 'url',
            'placeholder' => 'https://instagram.com/yourband',
        ],
        'spotify' => [
            'label' => 'Spotify',
            'type' => 'url',
            'placeholder' => 'https://open.spotify.com/artist/...',
        ],
        'apple_music' => [
            'label' => 'Apple Music',
            'type' => 'url',
            'placeholder' => 'https://music.apple.com/...',
        ],
        'bandcamp' => [
            'label' => 'Bandcamp',
            'type' => 'url',
            'placeholder' => 'https://yourband.bandcamp.com',
        ],
        'youtube' => [
            'label' => 'YouTube',
            'type' => 'url',
            'placeholder' => 'https://youtube.com/@yourband',
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'type' => 'url',
            'placeholder' => 'https://tiktok.com/@yourband',
        ],
        'soundcloud' => [
            'label' => 'SoundCloud',
            'type' => 'url',
            'placeholder' => 'https://soundcloud.com/yourband',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'type' => 'url',
            'placeholder' => 'https://facebook.com/yourband',
        ],
        'website' => [
            'label' => 'Website',
            'type' => 'url',
            'placeholder' => 'https://yourband.com',
        ],
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'placeholder' => 'booking@example.com',
        ],
    ];
}

function mpc_get_social_display_options() {
    return [
        'labels' => 'Text Labels',
        'icons' => 'Icons Only',
        'icons_labels' => 'Icons + Labels',
    ];
}

function mpc_get_social_links_defaults() {
    $defaults = [];

    foreach (mpc_get_social_link_items() as $key => $item) {
        $defaults[$key] = '';
    }

    $defaults['hero_display'] = 'labels';
    $defaults['footer_display'] = 'labels';

    return $defaults;
}

function mpc_get_social_links_settings() {
    $settings = get_option('mpc_social_links_settings', []);

    if (!is_array($settings)) {
        $settings = [];
    }

    return wp_parse_args($settings, mpc_get_social_links_defaults());
}

function mpc_get_social_links_setting($key, $default = '') {
    $settings = mpc_get_social_links_settings();

    return isset($settings[$key]) ? $settings[$key] : $default;
}

function mpc_sanitize_social_links_settings($input) {
    $input = is_array($input) ? $input : [];
    $output = mpc_get_social_links_defaults();

    foreach (mpc_get_social_link_items() as $key => $item) {
        $value = isset($input[$key]) ? trim((string) $input[$key]) : '';

        if ($item['type'] === 'email') {
            $output[$key] = sanitize_email($value);
        } else {
            $output[$key] = esc_url_raw($value);
        }
    }

    $allowed_displays = array_keys(mpc_get_social_display_options());

    foreach (['hero_display', 'footer_display'] as $display_key) {
        $value = isset($input[$display_key]) ? sanitize_key($input[$display_key]) : 'labels';
        $output[$display_key] = in_array($value, $allowed_displays, true) ? $value : 'labels';
    }

    return $output;
}

function mpc_register_social_links_settings() {
    register_setting(
        'mpc_social_links_settings_group',
        'mpc_social_links_settings',
        'mpc_sanitize_social_links_settings'
    );
}
add_action('admin_init', 'mpc_register_social_links_settings');

function mpc_add_social_links_admin_menu() {
    add_submenu_page(
        'mpc-homepage',
        'Social Links',
        'Social Links',
        'manage_options',
        'mpc-social-links',
        'mpc_render_social_links_settings_page'
    );
}
add_action('admin_menu', 'mpc_add_social_links_admin_menu', 10);

function mpc_render_social_display_select($settings, $key, $label, $description = '') {
    $options = mpc_get_social_display_options();
    ?>
    <tr>
        <th scope="row">
            <label for="mpc_social_<?php echo esc_attr($key); ?>">
                <?php echo esc_html($label); ?>
            </label>
        </th>
        <td>
            <select
                id="mpc_social_<?php echo esc_attr($key); ?>"
                name="mpc_social_links_settings[<?php echo esc_attr($key); ?>]"
            >
                <?php foreach ($options as $value => $option_label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings[$key], $value); ?>>
                        <?php echo esc_html($option_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($description) : ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

function mpc_render_social_links_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_social_links_settings();
    $items = mpc_get_social_link_items();
    ?>
    <div class="wrap">
        <h1>Social Links</h1>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_social_links_settings_group'); ?>

            <h2>Links</h2>

            <table class="form-table" role="presentation">
                <tbody>
                    <?php foreach ($items as $key => $item) : ?>
                        <tr>
                            <th scope="row">
                                <label for="mpc_social_<?php echo esc_attr($key); ?>">
                                    <?php echo esc_html($item['label']); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    id="mpc_social_<?php echo esc_attr($key); ?>"
                                    class="regular-text"
                                    type="<?php echo $item['type'] === 'email' ? 'email' : 'url'; ?>"
                                    name="mpc_social_links_settings[<?php echo esc_attr($key); ?>]"
                                    value="<?php echo esc_attr($settings[$key]); ?>"
                                    placeholder="<?php echo esc_attr($item['placeholder']); ?>"
                                >
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Display Options</h2>

            <table class="form-table" role="presentation">
                <tbody>
                    <?php
                    mpc_render_social_display_select(
                        $settings,
                        'hero_display',
                        'Hero Social Display',
                        'Controls how social links appear in the homepage hero.'
                    );

                    mpc_render_social_display_select(
                        $settings,
                        'footer_display',
                        'Footer Social Display',
                        'Controls how social links appear in the site footer.'
                    );
                    ?>
                </tbody>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}