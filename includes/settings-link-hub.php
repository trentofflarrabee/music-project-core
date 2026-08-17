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

/**
 * Normalize a Link Hub boolean value.
 *
 * @param mixed $value   Submitted value.
 * @param bool  $default Fallback value.
 * @return bool
 */
function mpc_normalize_link_hub_boolean(
    $value,
    $default = false
) {
    if (is_bool($value)) {
        return $value;
    }

    if (!is_scalar($value)) {
        return (bool) $default;
    }

    $value = strtolower(
        trim(
            (string) $value
        )
    );

    if (
        in_array(
            $value,
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        )
    ) {
        return true;
    }

    if (
        in_array(
            $value,
            [
                '',
                '0',
                'false',
                'no',
                'off',
            ],
            true
        )
    ) {
        return false;
    }

    return (bool) $default;
}

/**
 * Sanitize a Link Hub destination URL.
 *
 * Supported protocols:
 *
 * - http
 * - https
 * - mailto
 * - tel
 *
 * Mail and telephone links receive additional validation rather than relying
 * only on the protocol allowlist.
 *
 * @param mixed $value Submitted URL.
 * @return string
 */
function mpc_sanitize_link_hub_url($value) {
    if (!is_scalar($value)) {
        return '';
    }

    $value = trim(
        (string) $value
    );

    if ($value === '') {
        return '';
    }

    $scheme = wp_parse_url(
        $value,
        PHP_URL_SCHEME
    );

    $scheme = is_string($scheme)
        ? strtolower($scheme)
        : '';

    /*
     * Require an explicit supported scheme.
     *
     * Link Hub destinations should not rely on browser interpretation of
     * protocol-relative or otherwise ambiguous values.
     */
    if (
        !in_array(
            $scheme,
            [
                'http',
                'https',
                'mailto',
                'tel',
            ],
            true
        )
    ) {
        return '';
    }

    if ($scheme === 'mailto') {
        $email = substr(
            $value,
            strlen('mailto:')
        );

        $email = sanitize_email($email);

        if (
            $email === ''
            || !is_email($email)
        ) {
            return '';
        }

        return 'mailto:' . $email;
    }

    if ($scheme === 'tel') {
        $number = substr(
            $value,
            strlen('tel:')
        );

        $number = trim($number);

        /*
         * Keep first-release telephone support intentionally conservative.
         * Allow common formatting characters but not parameters or arbitrary
         * URI content.
         */
        if (
            $number === ''
            || !preg_match(
                '/^\+?[0-9][0-9().\-\s]{1,31}$/',
                $number
            )
        ) {
            return '';
        }

        $digits = preg_replace(
            '/[^0-9]/',
            '',
            $number
        );

        if (
            !is_string($digits)
            || strlen($digits) < 3
        ) {
            return '';
        }

        $normalized = preg_replace(
            '/[^0-9+]/',
            '',
            $number
        );

        if (!is_string($normalized)) {
            return '';
        }

        return 'tel:' . $normalized;
    }

    $url = esc_url_raw(
        $value,
        [
            'http',
            'https',
        ]
    );

    if ($url === '') {
        return '';
    }

    $host = wp_parse_url(
        $url,
        PHP_URL_HOST
    );

    if (
        !is_string($host)
        || $host === ''
    ) {
        return '';
    }

    return $url;
}

/**
 * Generate a Link Hub item ID.
 *
 * @return string
 */
function mpc_generate_link_hub_item_id() {
    return 'lh_' . str_replace(
        '-',
        '',
        wp_generate_uuid4()
    );
}

/**
 * Normalize an ordered Link Hub item collection.
 *
 * @param mixed $items Submitted items.
 * @return array
 */
function mpc_normalize_link_hub_items($items) {
    if (!is_array($items)) {
        return [];
    }

    /*
     * Enforce the item limit against the submitted collection before further
     * processing. This prevents malformed requests from bypassing the limit by
     * mixing valid and invalid records.
     */
    $items = array_slice(
        array_values($items),
        0,
        mpc_get_link_hub_item_limit()
    );

    $normalized = [];
    $used_ids = [];
    $featured_seen = false;

    $allowed_icons = array_keys(
        mpc_get_link_hub_icon_options()
    );

    $allowed_variants = array_keys(
        mpc_get_link_hub_variant_options()
    );

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $type = (
            isset($item['type'])
            && is_scalar($item['type'])
        )
            ? sanitize_key(
                (string) $item['type']
            )
            : '';

        if (
            !in_array(
                $type,
                [
                    'link',
                    'section',
                ],
                true
            )
        ) {
            continue;
        }

        $id = (
            isset($item['id'])
            && is_scalar($item['id'])
        )
            ? sanitize_key(
                (string) $item['id']
            )
            : '';

        if (
            $id === ''
            || isset($used_ids[$id])
        ) {
            do {
                $id =
                    mpc_generate_link_hub_item_id();
            } while (isset($used_ids[$id]));
        }

        $used_ids[$id] = true;

        $label = (
            isset($item['label'])
            && is_scalar($item['label'])
        )
            ? sanitize_text_field(
                (string) $item['label']
            )
            : '';

        /*
         * Section headings contain only the data needed for presentation.
         */
        if ($type === 'section') {
            if ($label === '') {
                continue;
            }

            $normalized[] = [
                'id'    => $id,
                'type'  => 'section',
                'label' => $label,
            ];

            continue;
        }

        $enabled = isset($item['enabled'])
            ? mpc_normalize_link_hub_boolean(
                $item['enabled']
            )
            : false;

        $subtitle = (
            isset($item['subtitle'])
            && is_scalar($item['subtitle'])
        )
            ? sanitize_text_field(
                (string) $item['subtitle']
            )
            : '';

        $url = isset($item['url'])
            ? mpc_sanitize_link_hub_url(
                $item['url']
            )
            : '';

        $icon = (
            isset($item['icon'])
            && is_scalar($item['icon'])
        )
            ? sanitize_key(
                (string) $item['icon']
            )
            : 'link';

        if (
            !in_array(
                $icon,
                $allowed_icons,
                true
            )
        ) {
            $icon = 'link';
        }

        $variant = (
            isset($item['variant'])
            && is_scalar($item['variant'])
        )
            ? sanitize_key(
                (string) $item['variant']
            )
            : 'standard';

        if (
            !in_array(
                $variant,
                $allowed_variants,
                true
            )
        ) {
            $variant = 'standard';
        }

        /*
         * An enabled link must have both a label and a valid destination.
         *
         * Preserve incomplete records as disabled rather than deleting them.
         * This lets an administrator save work-in-progress links without
         * accidentally publishing malformed frontend content.
         */
        if (
            $enabled
            && (
                $label === ''
                || $url === ''
            )
        ) {
            $enabled = false;
        }

        /*
         * Only one enabled link may be featured.
         *
         * The first enabled featured link in the submitted order wins.
         */
        if (
            $enabled
            && $variant === 'featured'
        ) {
            if ($featured_seen) {
                $variant = 'standard';
            } else {
                $featured_seen = true;
            }
        }

        $new_window = isset($item['new_window'])
            ? mpc_normalize_link_hub_boolean(
                $item['new_window']
            )
            : false;

        $normalized[] = [
            'id'         => $id,
            'type'       => 'link',
            'enabled'    => $enabled,
            'label'      => $label,
            'subtitle'   => $subtitle,
            'url'        => $url,
            'icon'       => $icon,
            'variant'    => $variant,
            'new_window' => $new_window,
        ];
    }

    return $normalized;
}

/**
 * Sanitize Link Hub settings.
 *
 * Unknown existing scalar top-level values are retained so extension-owned
 * settings are not silently destroyed if an extension is temporarily
 * unavailable.
 *
 * Nested unknown values are discarded.
 *
 * @param mixed $input Submitted settings.
 * @return array
 */
function mpc_sanitize_link_hub_settings($input) {
    $input = is_array($input)
        ? $input
        : [];

    $defaults =
        mpc_get_link_hub_defaults();

    $current = get_option(
        'mpc_link_hub_settings',
        []
    );

    $output = [];

    /*
     * Preserve unknown scalar extension-owned settings.
     */
    if (is_array($current)) {
        foreach ($current as $key => $value) {
            $key = sanitize_key(
                (string) $key
            );

            if (
                $key === ''
                || array_key_exists(
                    $key,
                    $defaults
                )
            ) {
                continue;
            }

            if (
                is_scalar($value)
                || $value === null
            ) {
                $output[$key] = $value;
            }
        }
    }

    $output['enabled'] =
        isset($input['enabled'])
            ? (
                mpc_normalize_link_hub_boolean(
                    $input['enabled']
                )
                    ? 1
                    : 0
            )
            : 0;

    $output['page_id'] =
        isset($input['page_id'])
            ? absint($input['page_id'])
            : 0;

    $output['profile_image_id'] =
        isset($input['profile_image_id'])
            ? absint(
                $input['profile_image_id']
            )
            : 0;

    $profile_image_mode = (
        isset($input['profile_image_mode'])
        && is_scalar(
            $input['profile_image_mode']
        )
    )
        ? sanitize_key(
            (string)
                $input['profile_image_mode']
        )
        : $defaults['profile_image_mode'];

    if (
        !array_key_exists(
            $profile_image_mode,
            mpc_get_link_hub_profile_image_options()
        )
    ) {
        $profile_image_mode =
            $defaults['profile_image_mode'];
    }

    $output['profile_image_mode'] =
        $profile_image_mode;

    $output['display_name'] = (
        isset($input['display_name'])
        && is_scalar($input['display_name'])
    )
        ? sanitize_text_field(
            (string) $input['display_name']
        )
        : '';

    $output['tagline'] = (
        isset($input['tagline'])
        && is_scalar($input['tagline'])
    )
        ? sanitize_text_field(
            (string) $input['tagline']
        )
        : '';

    $layout = (
        isset($input['layout'])
        && is_scalar($input['layout'])
    )
        ? sanitize_key(
            (string) $input['layout']
        )
        : $defaults['layout'];

    if (
        !array_key_exists(
            $layout,
            mpc_get_link_hub_layout_options()
        )
    ) {
        $layout = $defaults['layout'];
    }

    $output['layout'] = $layout;

    $output['show_social_links'] =
        isset($input['show_social_links'])
            ? (
                mpc_normalize_link_hub_boolean(
                    $input['show_social_links']
                )
                    ? 1
                    : 0
            )
            : 0;

    $output['show_footer_brand'] =
        isset($input['show_footer_brand'])
            ? (
                mpc_normalize_link_hub_boolean(
                    $input['show_footer_brand']
                )
                    ? 1
                    : 0
            )
            : 0;

    $output['items'] =
        mpc_normalize_link_hub_items(
            $input['items'] ?? []
        );

    return $output;
}

/**
 * Register Link Hub settings.
 *
 * @return void
 */
function mpc_register_link_hub_settings() {
    register_setting(
        'mpc_link_hub_settings_group',
        'mpc_link_hub_settings',
        [
            'type'              => 'array',
            'sanitize_callback' =>
                'mpc_sanitize_link_hub_settings',
            'default'           =>
                mpc_get_link_hub_defaults(),
        ]
    );
}

add_action(
    'admin_init',
    'mpc_register_link_hub_settings'
);