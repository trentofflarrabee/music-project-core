<?php
/**
 * Link Hub settings and public data API.
 *
 * Music Project Core owns Link Hub configuration, normalization, routing
 * state, and rendering-neutral data. Frontend presentation belongs to the
 * active theme.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the maximum number of ordered Link Hub items.
 *
 * @return int
 */
function mpc_get_link_hub_item_limit() {
    return 30;
}

/**
 * Get Link Hub defaults.
 *
 * @return array
 */
function mpc_get_link_hub_defaults() {
    return [
        'enabled'            => 0,
        'page_id'            => 0,

        'profile_image_id'   => 0,
        'profile_image_mode' => 'auto',
        'display_name'       => '',
        'tagline'            => '',

        'layout'              => 'spotlight',
        'show_social_links'   => 1,
        'show_footer_brand'   => 1,

        'items'               => [],
    ];
}

/**
 * Get supported profile-image modes.
 *
 * @return array
 */
function mpc_get_link_hub_profile_image_options() {
    return [
        'auto' => __(
            'Auto',
            'music-project-core'
        ),
        'custom' => __(
            'Custom',
            'music-project-core'
        ),
        'none' => __(
            'None',
            'music-project-core'
        ),
    ];
}

/**
 * Get supported Link Hub layouts.
 *
 * @return array
 */
function mpc_get_link_hub_layout_options() {
    return [
        'spotlight' => __(
            'Spotlight',
            'music-project-core'
        ),
        'stack' => __(
            'Stack',
            'music-project-core'
        ),
        'poster' => __(
            'Poster',
            'music-project-core'
        ),
    ];
}

/**
 * Get supported Link Hub icon keys.
 *
 * Music Project Base owns the actual icon markup.
 *
 * @return array
 */
function mpc_get_link_hub_icon_options() {
    return [
        'link' => __(
            'Link',
            'music-project-core'
        ),
        'music' => __(
            'Music',
            'music-project-core'
        ),
        'play' => __(
            'Play',
            'music-project-core'
        ),
        'video' => __(
            'Video',
            'music-project-core'
        ),
        'ticket' => __(
            'Ticket',
            'music-project-core'
        ),
        'shop' => __(
            'Shop',
            'music-project-core'
        ),
        'merch' => __(
            'Merch',
            'music-project-core'
        ),
        'newsletter' => __(
            'Newsletter',
            'music-project-core'
        ),
        'download' => __(
            'Download',
            'music-project-core'
        ),
        'calendar' => __(
            'Calendar',
            'music-project-core'
        ),
        'heart' => __(
            'Heart',
            'music-project-core'
        ),
        'star' => __(
            'Star',
            'music-project-core'
        ),
        'external' => __(
            'External',
            'music-project-core'
        ),
    ];
}

/**
 * Get supported Link Hub link variants.
 *
 * @return array
 */
function mpc_get_link_hub_variant_options() {
    return [
        'standard' => __(
            'Standard',
            'music-project-core'
        ),
        'featured' => __(
            'Featured',
            'music-project-core'
        ),
    ];
}