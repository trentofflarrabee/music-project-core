<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Supported social platforms.
 */
function mpc_get_social_platforms() {
    return [
        'instagram' => [
            'label' => __('Instagram', 'music-project-core'),
            'type'  => 'url',
        ],
        'spotify' => [
            'label' => __('Spotify', 'music-project-core'),
            'type'  => 'url',
        ],
        'apple_music' => [
            'label' => __('Apple Music', 'music-project-core'),
            'type'  => 'url',
        ],
        'bandcamp' => [
            'label' => __('Bandcamp', 'music-project-core'),
            'type'  => 'url',
        ],
        'youtube' => [
            'label' => __('YouTube', 'music-project-core'),
            'type'  => 'url',
        ],
        'tiktok' => [
            'label' => __('TikTok', 'music-project-core'),
            'type'  => 'url',
        ],
        'soundcloud' => [
            'label' => __('SoundCloud', 'music-project-core'),
            'type'  => 'url',
        ],
        'facebook' => [
            'label' => __('Facebook', 'music-project-core'),
            'type'  => 'url',
        ],
        'website' => [
            'label' => __('Website', 'music-project-core'),
            'type'  => 'url',
        ],
        'email' => [
            'label' => __('Email', 'music-project-core'),
            'type'  => 'email',
        ],
    ];
}

/**
 * Default social links.
 */
function mpc_get_social_links_defaults() {
    $defaults = [];

    foreach (mpc_get_social_platforms() as $platform => $data) {
        $defaults[$platform] = '';
    }

    return $defaults;
}

/**
 * Get all social links.
 */
function mpc_get_social_links() {
    $saved = get_option('mpc_social_links', []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, mpc_get_social_links_defaults());
}

/**
 * Get one social link.
 */
function mpc_get_social_link($platform) {
    $links = mpc_get_social_links();

    return isset($links[$platform]) ? $links[$platform] : '';
}

/**
 * Sanitize social links before saving.
 */
function mpc_sanitize_social_links($input) {
    $platforms = mpc_get_social_platforms();
    $output = [];

    foreach ($platforms as $platform => $data) {
        $value = isset($input[$platform]) ? trim($input[$platform]) : '';

        if ($data['type'] === 'email') {
            $output[$platform] = sanitize_email($value);
        } else {
            $output[$platform] = esc_url_raw($value);
        }
    }

    return $output;
}

/**
 * Register social settings.
 */
function mpc_register_social_links_settings() {
    register_setting(
        'mpc_social_links_group',
        'mpc_social_links',
        [
            'sanitize_callback' => 'mpc_sanitize_social_links',
        ]
    );
}
add_action('admin_init', 'mpc_register_social_links_settings');

/**
 * Add Social Links submenu page.
 */
function mpc_add_social_links_submenu() {
    add_submenu_page(
        'mpc-homepage',
        __('Social Links', 'music-project-core'),
        __('Social Links', 'music-project-core'),
        'manage_options',
        'mpc-social-links',
        'mpc_render_social_links_settings_page'
    );
}
add_action('admin_menu', 'mpc_add_social_links_submenu');

/**
 * Render Social Links settings page.
 */
function mpc_render_social_links_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $links = mpc_get_social_links();
    $platforms = mpc_get_social_platforms();
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('Music Project Social Links', 'music-project-core'); ?></h1>

        <p>
            <?php esc_html_e('Add the social/profile links for this artist or music project. Empty fields will not display on the frontend.', 'music-project-core'); ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_social_links_group'); ?>

            <table class="form-table" role="presentation">
                <?php foreach ($platforms as $platform => $data) : ?>
                    <tr>
                        <th scope="row">
                            <label for="mpc_social_<?php echo esc_attr($platform); ?>">
                                <?php echo esc_html($data['label']); ?>
                            </label>
                        </th>
                        <td>
                            <input
                                type="<?php echo esc_attr($data['type']); ?>"
                                id="mpc_social_<?php echo esc_attr($platform); ?>"
                                name="mpc_social_links[<?php echo esc_attr($platform); ?>]"
                                class="regular-text"
                                value="<?php echo esc_attr($links[$platform]); ?>"
                                placeholder="<?php echo $data['type'] === 'email' ? esc_attr('name@example.com') : esc_attr('https://...'); ?>"
                            >
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button(__('Save Social Links', 'music-project-core')); ?>
        </form>
    </div>

    <?php
}