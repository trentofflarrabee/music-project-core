<?php
/**
 * Optional Bandsintown shows adapter.
 *
 * The main Shows / Events integration remains provider-agnostic. This
 * shortcode is supplied as a convenience for sites that use Bandsintown.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue and localize the Bandsintown adapter.
 *
 * Enqueueing is idempotent, and localization happens once even when a page
 * contains multiple shortcode instances.
 *
 * @return void
 */
function mpc_enqueue_bandsintown_shows_assets() {
    wp_enqueue_script(
        'mpc-bandsintown-shows',
        MPC_URL . 'assets/bandsintown-shows.js',
        [],
        mpc_get_asset_version('assets/bandsintown-shows.js'),
        true
    );

    static $localized = false;

    if ($localized) {
        return;
    }

    wp_localize_script(
        'mpc-bandsintown-shows',
        'MPCBandsintownShowsL10n',
        [
            'locale'          => str_replace('_', '-', get_locale()),
            'loading'         => __(
                'Loading shows…',
                'music-project-core'
            ),
            'emptyTitle'      => __(
                'Nothing on the calendar just yet.',
                'music-project-core'
            ),
            'emptyText'       => __(
                'Join the mailing list and be the first to hear about new dates.',
                'music-project-core'
            ),
            'errorTitle'      => __(
                'Shows are temporarily unavailable.',
                'music-project-core'
            ),
            'errorText'       => __(
                'Please try again later or join the mailing list for updates.',
                'music-project-core'
            ),
            'emailUpdates'    => __(
                'Get Email Updates',
                'music-project-core'
            ),
            'venueTba'        => __(
                'Venue TBA',
                'music-project-core'
            ),
            'notifyMe'        => __(
                'Notify Me',
                'music-project-core'
            ),
            'waitlist'        => __(
                'Waitlist',
                'music-project-core'
            ),
            'rsvp'            => __(
                'RSVP',
                'music-project-core'
            ),
            'eventDetails'    => __(
                'Event details',
                'music-project-core'
            ),
        ]
    );

    $localized = true;
}

/**
 * Render the Bandsintown shows shortcode.
 *
 * Usage:
 *
 * [mpc_bandsintown_shows
 *     artist="Artist Name"
 *     app_id="your-app-id"
 *     signup_target="#signup"
 * ]
 *
 * The BIT_APP_ID environment variable may supply app_id when the shortcode
 * attribute is omitted. No artist or application ID is bundled by Core.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function mpc_bandsintown_shows_shortcode($atts = []) {
    $atts = shortcode_atts(
        [
            'artist'        => '',
            'app_id'        => getenv('BIT_APP_ID') ?: '',
            'signup_target' => '#signup',
        ],
        is_array($atts) ? $atts : [],
        'mpc_bandsintown_shows'
    );

    $artist = trim(
        sanitize_text_field((string) $atts['artist'])
    );

    $app_id = trim(
        sanitize_text_field((string) $atts['app_id'])
    );

    $signup_target = trim(
        sanitize_text_field((string) $atts['signup_target'])
    );

    if ($artist === '' || $app_id === '') {
        if (!current_user_can('manage_options')) {
            return '';
        }

        $example = sprintf(
            '[mpc_bandsintown_shows artist="%s" app_id="%s"]',
            __('Artist Name', 'music-project-core'),
            __('your-app-id', 'music-project-core')
        );

        return sprintf(
            '<div class="shows-empty mpc-integration-admin-notice"><p><strong>%1$s</strong></p><p>%2$s</p><p><code>%3$s</code></p></div>',
            esc_html__(
                'Bandsintown is not fully configured.',
                'music-project-core'
            ),
            esc_html__(
                'Add both an artist name and an application ID to the shortcode.',
                'music-project-core'
            ),
            esc_html($example)
        );
    }

    mpc_enqueue_bandsintown_shows_assets();

    $container_id = wp_unique_id(
        'mpc-bandsintown-events-'
    );

    ob_start();
    ?>
    <div
        id="<?php echo esc_attr($container_id); ?>"
        class="mpc-bandsintown-events"
        data-provider="bandsintown"
        data-artist="<?php echo esc_attr($artist); ?>"
        data-app-id="<?php echo esc_attr($app_id); ?>"
        data-signup-target="<?php echo esc_attr($signup_target); ?>"
        role="region"
        aria-label="<?php esc_attr_e('Upcoming shows', 'music-project-core'); ?>"
        aria-live="polite"
        aria-busy="true"
    >
        <div class="shows-empty">
            <p class="fs-300">
                <?php
                esc_html_e(
                    'Loading shows…',
                    'music-project-core'
                );
                ?>
            </p>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode(
    'mpc_bandsintown_shows',
    'mpc_bandsintown_shows_shortcode'
);