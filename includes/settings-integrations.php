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
        'shows_heading' => 'Shows',
        'shows_embed' => '',

        'newsletter_enabled' => 1,
        'newsletter_heading' => 'Newsletter',
        'newsletter_text' => 'Sign up for updates.',
        'newsletter_embed' => '',
    ];
}

/**
 * Get all integration settings.
 */
function mpc_get_integrations_settings() {
    $saved = get_option('mpc_integrations_settings', []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, mpc_get_integrations_defaults());
}

/**
 * Get one integration setting.
 */
function mpc_get_integration_setting($key, $default = '') {
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
 * Sanitize integration settings.
 */
function mpc_sanitize_integrations_settings($input) {
    $defaults = mpc_get_integrations_defaults();
    $output = [];

    $output['shows_enabled'] = !empty($input['shows_enabled']) ? 1 : 0;

    $output['shows_heading'] = isset($input['shows_heading'])
        ? sanitize_text_field($input['shows_heading'])
        : $defaults['shows_heading'];

    $output['shows_embed'] = isset($input['shows_embed'])
        ? mpc_sanitize_embed_content($input['shows_embed'])
        : '';

    $output['newsletter_enabled'] = !empty($input['newsletter_enabled']) ? 1 : 0;

    $output['newsletter_heading'] = isset($input['newsletter_heading'])
        ? sanitize_text_field($input['newsletter_heading'])
        : $defaults['newsletter_heading'];

    $output['newsletter_text'] = isset($input['newsletter_text'])
        ? sanitize_textarea_field($input['newsletter_text'])
        : $defaults['newsletter_text'];

    $output['newsletter_embed'] = isset($input['newsletter_embed'])
        ? mpc_sanitize_embed_content($input['newsletter_embed'])
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
            'sanitize_callback' => 'mpc_sanitize_integrations_settings',
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
            <?php esc_html_e('Use these fields for provider-agnostic embeds and shortcodes. This can be Bandsintown, Mailchimp, ConvertKit, Substack, Songkick, Seated, or any other trusted provider.', 'music-project-core'); ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields('mpc_integrations_settings_group'); ?>

            <h2><?php esc_html_e('Shows / Events', 'music-project-core'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Shows Section', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="hidden" name="mpc_integrations_settings[shows_enabled]" value="0">
                            <input
                                type="checkbox"
                                name="mpc_integrations_settings[shows_enabled]"
                                value="1"
                                <?php checked(1, $settings['shows_enabled']); ?>
                            >
                            <?php esc_html_e('Show the Shows section on the homepage', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>

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
                        <?php esc_html_e('Enable Newsletter Section', 'music-project-core'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="hidden" name="mpc_integrations_settings[newsletter_enabled]" value="0">
                            <input
                                type="checkbox"
                                name="mpc_integrations_settings[newsletter_enabled]"
                                value="1"
                                <?php checked(1, $settings['newsletter_enabled']); ?>
                            >
                            <?php esc_html_e('Show the Newsletter section on the homepage', 'music-project-core'); ?>
                        </label>
                    </td>
                </tr>

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
                        <?php esc_html_e('Newsletter Shortcode / Embed', 'music-project-core'); ?>
                    </th>
                    <td>
                        <?php mpc_render_embed_textarea('newsletter_embed', $settings['newsletter_embed']); ?>
                        <p class="description">
                            <?php esc_html_e('Paste any trusted newsletter or mailing list shortcode/embed code.', 'music-project-core'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Integrations', 'music-project-core')); ?>
        </form>
    </div>

    <?php
}