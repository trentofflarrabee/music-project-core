<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode:
 * [mpc_bandsintown_shows artist="Slim Volume" app_id="your_app_id"]
 */
function mpc_bandsintown_shows_shortcode($atts = []) {
    $atts = shortcode_atts(
        [
            'artist' => 'Slim Volume',
            'app_id' => getenv('BIT_APP_ID') ?: 'a59e1a1174bac4b50ee6e17bd06eb591',
            'signup_target' => '#signup',
        ],
        $atts,
        'mpc_bandsintown_shows'
    );

wp_enqueue_script(
    'mpc-bandsintown-shows',
    MPC_URL . 'assets/bandsintown-shows.js',
    [],
    mpc_get_asset_version('assets/bandsintown-shows.js'),
    true
);

    wp_localize_script(
        'mpc-bandsintown-shows',
        'MPCBandsintownShows',
        [
            'artistName' => $atts['artist'],
            'appId' => $atts['app_id'],
            'signupTarget' => $atts['signup_target'],
        ]
    );

    ob_start();
    ?>

    <div
        id="mpc-bandsintown-events"
        class="mpc-bandsintown-events"
        data-aos="fade-up"
        data-aos-delay="100"
    >
        <p style="color: lime;">Bandsintown shortcode PHP rendered.</p>
        <div class="shows-empty">
            <p class="fs-300">Loading shows…</p>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('mpc_bandsintown_shows', 'mpc_bandsintown_shows_shortcode');