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

/**
 * Get normalized Link Hub settings.
 *
 * Stored data is treated as untrusted and normalized before it is returned
 * to administration, themes, or integrations.
 *
 * @return array
 */
function mpc_get_link_hub_settings() {
    $defaults = mpc_get_link_hub_defaults();

    $saved = get_option(
        'mpc_link_hub_settings',
        []
    );

    if (!is_array($saved)) {
        $saved = [];
    }

    $settings = wp_parse_args(
        $saved,
        $defaults
    );

    $settings = mpc_sanitize_link_hub_settings(
        $settings
    );

    if (!is_array($settings)) {
        return $defaults;
    }

    return wp_parse_args(
        $settings,
        $defaults
    );
}

/**
 * Get one normalized Link Hub setting.
 *
 * @param mixed $key     Setting key.
 * @param mixed $default Fallback value.
 * @return mixed
 */
function mpc_get_link_hub_setting(
    $key,
    $default = ''
) {
    if (!is_scalar($key)) {
        return $default;
    }

    $key = sanitize_key(
        (string) $key
    );

    if ($key === '') {
        return $default;
    }

    $settings = mpc_get_link_hub_settings();

    return array_key_exists(
        $key,
        $settings
    )
        ? $settings[$key]
        : $default;
}

/**
 * Determine whether Link Hub is enabled.
 *
 * This reflects the saved feature toggle only. Page validity is checked
 * separately through mpc_get_link_hub_page_id().
 *
 * @return bool
 */
function mpc_is_link_hub_enabled() {
    return mpc_normalize_link_hub_boolean(
        mpc_get_link_hub_setting(
            'enabled',
            false
        )
    );
}

/**
 * Determine whether a WordPress Page may be assigned to Link Hub.
 *
 * The normal WordPress Homepage and Posts page are reserved and must not
 * become Link Hub routes.
 *
 * @param mixed $page_id Page ID.
 * @return bool
 */
function mpc_is_link_hub_page_assignable($page_id) {
    if (!is_scalar($page_id)) {
        return false;
    }

    $page_id = absint(
        (string) $page_id
    );

    if (
        !$page_id
        || !function_exists(
            'mpc_is_valid_routing_page'
        )
        || !mpc_is_valid_routing_page(
            $page_id
        )
    ) {
        return false;
    }

    $reserved_ids = array_filter(
        [
            absint(
                get_option(
                    'page_on_front',
                    0
                )
            ),
            absint(
                get_option(
                    'page_for_posts',
                    0
                )
            ),
        ]
    );

    return !in_array(
        $page_id,
        $reserved_ids,
        true
    );
}

/**
 * Get the assigned Link Hub WordPress Page ID.
 *
 * The stored ID is only returned when it still references a real WordPress
 * Page that has not been moved to the trash.
 *
 * @return int
 */
function mpc_get_link_hub_page_id() {
    $page_id = absint(
        mpc_get_link_hub_setting(
            'page_id',
            0
        )
    );

    if (!$page_id) {
        return 0;
    }

    if (
        !mpc_is_link_hub_page_assignable(
            $page_id
        )
    ) {
        return 0;
    }

    return $page_id;
}

/**
 * Get normalized Link Hub items for frontend consumption.
 *
 * Disabled or incomplete links are omitted. Section headings are only
 * returned when followed by at least one usable link, preventing empty
 * section groups from reaching the frontend.
 *
 * @return array
 */
function mpc_get_link_hub_items() {
    $settings = mpc_get_link_hub_settings();

    $items = isset($settings['items'])
        && is_array($settings['items'])
            ? $settings['items']
            : [];

    /**
     * Filter normalized Link Hub items before frontend cleanup.
     *
     * Filtered values are normalized again before they are returned.
     *
     * @param array $items    Ordered Link Hub items.
     * @param array $settings Complete normalized Link Hub settings.
     */
    $items = apply_filters(
        'mpc_link_hub_items',
        $items,
        $settings
    );

    $items = mpc_normalize_link_hub_items(
        $items
    );

    $output = [];
    $pending_section = null;

    foreach ($items as $item) {
        if (
            !is_array($item)
            || empty($item['type'])
        ) {
            continue;
        }

        if ($item['type'] === 'section') {
            /*
             * Hold the heading until we know a usable link actually follows
             * it. If another section appears first, the previous group was
             * empty and is discarded.
             */
            $pending_section = $item;

            continue;
        }

        if ($item['type'] !== 'link') {
            continue;
        }

        if (
            empty($item['enabled'])
            || empty($item['label'])
            || empty($item['url'])
        ) {
            continue;
        }

        if ($pending_section !== null) {
            $output[] = $pending_section;
            $pending_section = null;
        }

        $output[] = $item;
    }

    return $output;
}

/**
 * Get the public Link Hub URL.
 *
 * The assigned WordPress Page remains the routing authority. The URL filter
 * allows integrations to adjust the resolved URL without requiring consumers
 * to infer a page slug.
 *
 * @return string
 */
function mpc_get_link_hub_url() {
    $page_id = mpc_get_link_hub_page_id();

    if (!$page_id) {
        return '';
    }

    $permalink = get_permalink($page_id);

    if (
        !is_string($permalink)
        || $permalink === ''
    ) {
        return '';
    }

    $url = esc_url_raw(
        $permalink,
        [
            'http',
            'https',
        ]
    );

    if ($url === '') {
        return '';
    }

    /**
     * Filter the resolved Link Hub URL.
     *
     * @param string $url     Valid assigned-page permalink.
     * @param int    $page_id Assigned WordPress Page ID.
     */
    $filtered = apply_filters(
        'mpc_link_hub_url',
        $url,
        $page_id
    );

    if (!is_scalar($filtered)) {
        return $url;
    }

    $filtered = esc_url_raw(
        (string) $filtered,
        [
            'http',
            'https',
        ]
    );

    return $filtered !== ''
        ? $filtered
        : $url;
}

/**
 * Assign a published WordPress Page to Link Hub.
 *
 * This updates only Link Hub's page assignment while preserving all other
 * normalized Link Hub configuration.
 *
 * Passing 0 intentionally clears the assignment.
 *
 * @param mixed $page_id Page ID.
 * @return true|WP_Error
 */
function mpc_assign_link_hub_page($page_id) {
    $page_id = absint($page_id);

    if (
        $page_id
        && !mpc_is_link_hub_page_assignable(
            $page_id
        )
    ) {
        return new WP_Error(
            'mpc_link_hub_invalid_page',
            __(
                'The selected Link Hub page must be a published WordPress Page that is not currently used as the Homepage or Posts page.',
                'music-project-core'
            )
        );
    } {
        return new WP_Error(
            'mpc_link_hub_invalid_page',
            __(
                'The selected Link Hub page must be a published WordPress Page.',
                'music-project-core'
            )
        );
    }

    $settings =
        mpc_get_link_hub_settings();

    $settings['page_id'] =
        $page_id;

    $settings =
        mpc_sanitize_link_hub_settings(
            $settings
        );

    $updated = update_option(
        'mpc_link_hub_settings',
        $settings
    );

    /*
     * update_option() returns false when the stored value is already
     * identical. That is still a successful assignment.
     */
    $stored = get_option(
        'mpc_link_hub_settings',
        []
    );

    $stored_page_id = (
        is_array($stored)
        && isset($stored['page_id'])
    )
        ? absint($stored['page_id'])
        : 0;

    if ($stored_page_id !== $page_id) {
        return new WP_Error(
            'mpc_link_hub_page_assignment_failed',
            __(
                'WordPress could not save the Link Hub page assignment.',
                'music-project-core'
            )
        );
    }

    return true;
}

/**
 * Find an existing published Page suitable for Link Hub.
 *
 * The preferred /links/ slug is checked first, followed by an exact title
 * match for "Links".
 *
 * The current WordPress Homepage and Posts page are never reused.
 *
 * @return int
 */
function mpc_find_link_hub_page_candidate() {
    $excluded_ids = array_filter(
        [
            absint(
                get_option(
                    'page_on_front',
                    0
                )
            ),
            absint(
                get_option(
                    'page_for_posts',
                    0
                )
            ),
        ]
    );

    /*
     * Prefer an existing published page using the conventional "links" slug.
     */
    $page = get_page_by_path(
        'links',
        OBJECT,
        'page'
    );

    if (
        $page instanceof WP_Post
        && !in_array(
            (int) $page->ID,
            $excluded_ids,
            true
        )
        && function_exists(
            'mpc_is_valid_routing_page'
        )
        && mpc_is_valid_routing_page(
            $page->ID
        )
    ) {
        return (int) $page->ID;
    }

    /*
     * As a secondary fallback, look for an exact "Links" page title.
     */
    $candidate_ids = get_posts(
        [
            'post_type'           => 'page',
            'post_status'         => 'publish',
            'posts_per_page'      => 20,
            'orderby'             => 'ID',
            'order'               => 'ASC',
            'fields'              => 'ids',
            's'                   => 'Links',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
        ]
    );

    foreach ($candidate_ids as $candidate_id) {
        $candidate_id =
            absint($candidate_id);

        if (
            !$candidate_id
            || in_array(
                $candidate_id,
                $excluded_ids,
                true
            )
        ) {
            continue;
        }

        $title =
            get_the_title(
                $candidate_id
            );

        if (
            is_string($title)
            && strcasecmp(
                trim($title),
                'Links'
            ) === 0
            && function_exists(
                'mpc_is_valid_routing_page'
            )
            && mpc_is_valid_routing_page(
                $candidate_id
            )
        ) {
            return $candidate_id;
        }
    }

    return 0;
}

/**
 * Configure the canonical Link Hub WordPress Page.
 *
 * A valid current assignment is always preserved.
 *
 * Otherwise, an appropriate existing Links Page is reused when possible.
 * A new published Links Page is created only after this explicit
 * administrator action.
 *
 * @return int|WP_Error Assigned Link Hub Page ID on success.
 */
function mpc_configure_link_hub_page() {
    /*
     * Never replace a valid existing assignment.
     */
    $existing_page_id =
        mpc_get_link_hub_page_id();

    if ($existing_page_id) {
        return $existing_page_id;
    }

    $front_page_id = absint(
        get_option(
            'page_on_front',
            0
        )
    );

    $posts_page_id = absint(
        get_option(
            'page_for_posts',
            0
        )
    );

    /*
     * First look for an existing suitable Link Hub candidate using our
     * Link Hub-specific rules.
     */
    $page_id =
        mpc_find_link_hub_page_candidate();

    /*
     * If no suitable Page exists, reuse Core's established routing-page
     * creation helper rather than maintaining a second creation path.
     */
    if (!$page_id) {
        $exclude_id = $front_page_id
            ? $front_page_id
            : $posts_page_id;

        $page_id =
            mpc_get_or_create_routing_page(
                __(
                    'Links',
                    'music-project-core'
                ),
                'links',
                $exclude_id
            );

        if (is_wp_error($page_id)) {
            return $page_id;
        }

        $page_id = absint($page_id);
    }

    /*
     * The generic routing helper accepts one excluded ID, while Link Hub
     * reserves both the Homepage and Posts page. Verify both before assigning.
     */
    if (
        !$page_id
        || $page_id === $front_page_id
        || $page_id === $posts_page_id
        || !mpc_is_valid_routing_page(
            $page_id
        )
    ) {
        return new WP_Error(
            'mpc_link_hub_invalid_configured_page',
            __(
                'WordPress could not configure a suitable Link Hub page.',
                'music-project-core'
            )
        );
    }

    /*
     * Save the assignment directly after the Page has already passed the
     * routing and reserved-page checks above.
     */
    $settings =
        mpc_get_link_hub_settings();

    $settings['page_id'] =
        $page_id;

    $settings =
        mpc_sanitize_link_hub_settings(
            $settings
        );

    update_option(
        'mpc_link_hub_settings',
        $settings
    );

    /*
     * Verify that the assignment actually persisted.
     */
    $assigned_page_id =
        mpc_get_link_hub_page_id();

    if ($assigned_page_id !== $page_id) {
        return new WP_Error(
            'mpc_link_hub_page_assignment_failed',
            __(
                'The Link Hub Page was created, but WordPress could not save it as the Link Hub assignment.',
                'music-project-core'
            )
        );
    }

    return $page_id;
}

/**
 * Register the Link in Bio submenu.
 *
 * @return void
 */
function mpc_add_link_hub_admin_menu() {
    add_submenu_page(
        'mpc-homepage',
        __(
            'Link in Bio',
            'music-project-core'
        ),
        __(
            'Link in Bio',
            'music-project-core'
        ),
        'manage_options',
        'mpc-link-hub',
        'mpc_render_link_hub_settings_page'
    );
}

add_action(
    'admin_menu',
    'mpc_add_link_hub_admin_menu',
    11
);

/**
 * Save the Link Hub status and manually assigned Page.
 *
 * This is intentionally a partial settings update so future Link Hub identity,
 * presentation, and item data are preserved.
 *
 * @return void
 */
function mpc_handle_save_link_hub_status() {
    if (!current_user_can('manage_options')) {
        wp_die(
            esc_html__(
                'You do not have permission to manage Link in Bio settings.',
                'music-project-core'
            )
        );
    }

    check_admin_referer(
        'mpc_save_link_hub_status'
    );

    $page_id = 0;

    if (
        isset($_POST['mpc_link_hub_page_id'])
        && is_scalar(
            $_POST['mpc_link_hub_page_id']
        )
    ) {
        $page_id = absint(
            wp_unslash(
                (string)
                    $_POST[
                        'mpc_link_hub_page_id'
                    ]
            )
        );
    }

    if (
        $page_id
        && !mpc_is_link_hub_page_assignable(
            $page_id
        )
    ) {
        $redirect_url = add_query_arg(
            [
                'page' => 'mpc-link-hub',
                'mpc_link_hub_notice' =>
                    'invalid_page',
            ],
            admin_url('admin.php')
        );

        wp_safe_redirect(
            $redirect_url
        );

        exit;
    }

    $settings =
        mpc_get_link_hub_settings();

    $settings['enabled'] =
        isset($_POST['mpc_link_hub_enabled'])
            ? 1
            : 0;

    $settings['page_id'] =
        $page_id;

    $settings =
        mpc_sanitize_link_hub_settings(
            $settings
        );

    update_option(
        'mpc_link_hub_settings',
        $settings
    );

    $redirect_url = add_query_arg(
        [
            'page' => 'mpc-link-hub',
            'mpc_link_hub_notice' =>
                'saved',
        ],
        admin_url('admin.php')
    );

    wp_safe_redirect(
        $redirect_url
    );

    exit;
}

add_action(
    'admin_post_mpc_save_link_hub_status',
    'mpc_handle_save_link_hub_status'
);

/**
 * Handle the explicit Configure Link Hub Page administrator action.
 *
 * @return void
 */
function mpc_handle_configure_link_hub_page() {
    if (!current_user_can('manage_options')) {
        wp_die(
            esc_html__(
                'You do not have permission to configure the Link Hub page.',
                'music-project-core'
            )
        );
    }

    check_admin_referer(
        'mpc_configure_link_hub_page'
    );

    $page_id =
        mpc_configure_link_hub_page();

    if (is_wp_error($page_id)) {
        wp_die(
            esc_html(
                $page_id->get_error_message()
            ),
            esc_html__(
                'Link Hub Page Setup',
                'music-project-core'
            ),
            [
                'back_link' => true,
            ]
        );
    }

    $redirect_url = add_query_arg(
        [
            'page' => 'mpc-link-hub',
            'mpc_link_hub_notice' =>
                'configured',
        ],
        admin_url('admin.php')
    );

    wp_safe_redirect(
        $redirect_url
    );

    exit;
}

add_action(
    'admin_post_mpc_configure_link_hub_page',
    'mpc_handle_configure_link_hub_page'
);

/**
 * Render the Link in Bio administration screen.
 *
 * @return void
 */
function mpc_render_link_hub_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings =
        mpc_get_link_hub_settings();

    $stored_page_id = isset(
        $settings['page_id']
    )
        ? absint(
            $settings['page_id']
        )
        : 0;

    $page_id =
        mpc_get_link_hub_page_id();

    $enabled =
        mpc_is_link_hub_enabled();

    $notice = '';

    if (
        isset(
            $_GET['mpc_link_hub_notice']
        )
        && is_scalar(
            $_GET['mpc_link_hub_notice']
        )
    ) {
        $notice = sanitize_key(
            wp_unslash(
                (string)
                    $_GET[
                        'mpc_link_hub_notice'
                    ]
            )
        );
    }

    $front_page_id = absint(
        get_option(
            'page_on_front',
            0
        )
    );

    $posts_page_id = absint(
        get_option(
            'page_for_posts',
            0
        )
    );

    $pages = get_pages(
        [
            'post_status' => 'publish',
            'sort_column' => 'post_title',
            'sort_order'  => 'ASC',
        ]
    );
    ?>
    <div class="wrap">
        <h1>
            <?php
            esc_html_e(
                'Link in Bio',
                'music-project-core'
            );
            ?>
        </h1>

        <p>
            <?php
            esc_html_e(
                'Create a focused Link Hub hosted on your own WordPress site. Music Project Core owns the configuration while compatible themes control the frontend presentation.',
                'music-project-core'
            );
            ?>
        </p>

        <?php if ($notice === 'saved') : ?>
            <div
                class="notice notice-success is-dismissible"
            >
                <p>
                    <?php
                    esc_html_e(
                        'Link in Bio settings saved.',
                        'music-project-core'
                    );
                    ?>
                </p>
            </div>
        <?php elseif ($notice === 'configured') : ?>
            <div
                class="notice notice-success is-dismissible"
            >
                <p>
                    <?php
                    esc_html_e(
                        'The Link Hub page is configured.',
                        'music-project-core'
                    );
                    ?>
                </p>
            </div>
        <?php elseif ($notice === 'invalid_page') : ?>
            <div
                class="notice notice-error is-dismissible"
            >
                <p>
                    <?php
                    esc_html_e(
                        'The selected Page cannot be used for Link Hub. Choose another published Page.',
                        'music-project-core'
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($enabled && $page_id) : ?>
            <div class="notice notice-success inline">
                <p>
                    <strong>
                        <?php
                        esc_html_e(
                            'Link Hub is enabled and has a valid assigned Page.',
                            'music-project-core'
                        );
                        ?>
                    </strong>
                </p>
            </div>
        <?php elseif ($enabled) : ?>
            <div class="notice notice-warning inline">
                <p>
                    <strong>
                        <?php
                        esc_html_e(
                            'Link Hub is enabled but needs a valid assigned Page.',
                            'music-project-core'
                        );
                        ?>
                    </strong>
                </p>
            </div>
        <?php else : ?>
            <div class="notice notice-info inline">
                <p>
                    <?php
                    esc_html_e(
                        'Link Hub is currently disabled.',
                        'music-project-core'
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php
        if (
            $stored_page_id
            && !$page_id
        ) :
            ?>
            <div class="notice notice-warning inline">
                <p>
                    <?php
                    esc_html_e(
                        'The previously assigned Link Hub Page is no longer a valid published Page or is reserved for WordPress Homepage/Posts routing. Choose another Page or run the configuration action below.',
                        'music-project-core'
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <h2>
            <?php
            esc_html_e(
                'Status & Page',
                'music-project-core'
            );
            ?>
        </h2>

        <form
            method="post"
            action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
        >
            <input
                type="hidden"
                name="action"
                value="mpc_save_link_hub_status"
            >

            <?php
            wp_nonce_field(
                'mpc_save_link_hub_status'
            );
            ?>

            <table
                class="form-table"
                role="presentation"
            >
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php
                            esc_html_e(
                                'Enable Link Hub',
                                'music-project-core'
                            );
                            ?>
                        </th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    name="mpc_link_hub_enabled"
                                    value="1"
                                    <?php checked($enabled); ?>
                                >
                                <?php
                                esc_html_e(
                                    'Enable the Link Hub frontend when a valid Page is assigned.',
                                    'music-project-core'
                                );
                                ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label
                                for="mpc_link_hub_page_id"
                            >
                                <?php
                                esc_html_e(
                                    'Assigned Page',
                                    'music-project-core'
                                );
                                ?>
                            </label>
                        </th>

                        <td>
                            <select
                                id="mpc_link_hub_page_id"
                                name="mpc_link_hub_page_id"
                            >
                                <option value="0">
                                    <?php
                                    esc_html_e(
                                        '— No Page assigned —',
                                        'music-project-core'
                                    );
                                    ?>
                                </option>

                                <?php foreach ($pages as $page) : ?>
                                    <?php
                                    if (
                                        !($page instanceof WP_Post)
                                        || (int) $page->ID
                                            === $front_page_id
                                        || (int) $page->ID
                                            === $posts_page_id
                                    ) {
                                        continue;
                                    }
                                    ?>

                                    <option
                                        value="<?php echo esc_attr($page->ID); ?>"
                                        <?php
                                        selected(
                                            $page_id,
                                            $page->ID
                                        );
                                        ?>
                                    >
                                        <?php
                                        echo esc_html(
                                            get_the_title(
                                                $page->ID
                                            )
                                        );
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <p class="description">
                                <?php
                                esc_html_e(
                                    'The Page ID is the routing contract. Its slug may be changed later without breaking Link Hub.',
                                    'music-project-core'
                                );
                                ?>
                            </p>

                            <?php if ($page_id) : ?>
                                <p>
                                    <a
                                        href="<?php echo esc_url(get_edit_post_link($page_id)); ?>"
                                    >
                                        <?php
                                        esc_html_e(
                                            'Edit assigned Page',
                                            'music-project-core'
                                        );
                                        ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php
            submit_button(
                __(
                    'Save Status & Page',
                    'music-project-core'
                )
            );
            ?>
        </form>

        <hr>

        <h2>
            <?php
            esc_html_e(
                'Configure Link Hub Page',
                'music-project-core'
            );
            ?>
        </h2>

        <p>
            <?php
            esc_html_e(
                'This action preserves a valid current assignment. Otherwise, Music Project will reuse a suitable existing Links Page when possible and create a new published Links Page only when necessary.',
                'music-project-core'
            );
            ?>
        </p>

        <form
            method="post"
            action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
        >
            <input
                type="hidden"
                name="action"
                value="mpc_configure_link_hub_page"
            >

            <?php
            wp_nonce_field(
                'mpc_configure_link_hub_page'
            );

            submit_button(
                __(
                    'Configure Link Hub Page',
                    'music-project-core'
                ),
                'secondary',
                'submit',
                false
            );
            ?>

            <?php if ($page_id) : ?>
                <?php
                $link_hub_url =
                    mpc_get_link_hub_url();
                ?>

                <?php if ($link_hub_url) : ?>
                    <a
                        class="button"
                        href="<?php echo esc_url($link_hub_url); ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <?php
                        esc_html_e(
                            'View Link Hub',
                            'music-project-core'
                        );
                        ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </form>

        <p class="description">
            <?php
            esc_html_e(
                'Page creation never runs automatically during Core activation or updates.',
                'music-project-core'
            );
            ?>
        </p>
    </div>
    <?php
}