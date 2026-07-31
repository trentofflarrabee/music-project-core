<?php

if (!defined('ABSPATH')) {
    exit;
}

function mpc_get_homepage_section_definitions() {
    return [
        'hero' => [
            'label' => __('Hero', 'music-project-core'),
            'description' => __('Main landing section with heading, media, CTA, and socials.', 'music-project-core'),
        ],
        'featured-content' => [
            'label' => __('Featured Content', 'music-project-core'),
            'description' => __('Promo section for a release, video, announcement, or featured message.', 'music-project-core'),
        ],
        'services' => [
            'label' => __('Services', 'music-project-core'),
            'description' => __('List of services offered.', 'music-project-core'),
        ],
        'quotes' => [
            'label' => __('Quotes / Testimonials', 'music-project-core'),
            'description' => __('Customer testimonials, client feedback, press quotes, or other social proof.', 'music-project-core'),
        ],
        'shows' => [
            'label' => __('Shows', 'music-project-core'),
            'description' => __('Tour dates or live show embed.', 'music-project-core'),
        ],
        'blog' => [
            'label' => __('Blog / News', 'music-project-core'),
            'description' => __('Recent posts from the site blog.', 'music-project-core'),
        ],
        'newsletter' => [
            'label' => __('Newsletter', 'music-project-core'),
            'description' => __('Signup form or newsletter embed.', 'music-project-core'),
        ],
    ];
}

function mpc_get_homepage_section_default_order() {
    return array_keys(mpc_get_homepage_section_definitions());
}

function mpc_get_homepage_section_default_visibility() {
    return array_fill_keys(mpc_get_homepage_section_default_order(), 1);
}

function mpc_normalize_homepage_section_order($order) {
    $known_sections =
        mpc_get_homepage_section_default_order();

    if (is_string($order)) {
        $order = array_filter(
            array_map(
                'trim',
                explode(',', $order)
            )
        );
    }

    if (!is_array($order)) {
        $order = [];
    }

    $normalized = [];

    foreach ($order as $section) {
        if (!is_scalar($section)) {
            continue;
        }

        $section = sanitize_key(
            (string) $section
        );

        if (
            $section !== ''
            && in_array(
                $section,
                $known_sections,
                true
            )
            && !in_array(
                $section,
                $normalized,
                true
            )
        ) {
            $normalized[] = $section;
        }
    }

    /*
     * Restore missing registered sections so malformed or older saved orders
     * cannot make a homepage section disappear from the manager.
     */
    foreach ($known_sections as $section) {
        if (
            !in_array(
                $section,
                $normalized,
                true
            )
        ) {
            $normalized[] = $section;
        }
    }

    return $normalized;
}

function mpc_get_homepage_section_order() {
    $settings = mpc_get_homepage_settings();
    $order = isset($settings['section_order']) ? $settings['section_order'] : '';

    return mpc_normalize_homepage_section_order($order);
}

/**
 * Build homepage visibility from legacy per-section settings.
 *
 * This is used only when a saved canonical section_visibility map does not
 * yet exist. It preserves behavior for sites created before Section Manager.
 *
 * @return array
 */
function mpc_get_legacy_homepage_section_visibility() {
    $visibility = mpc_get_homepage_section_default_visibility();

    $homepage_settings = get_option('mpc_homepage_settings', []);

    if (!is_array($homepage_settings)) {
        $homepage_settings = [];
    }

    $integration_settings = get_option('mpc_integrations_settings', []);

    if (!is_array($integration_settings)) {
        $integration_settings = [];
    }

    $homepage_legacy_map = [
        'hero'             => 'hero_enabled',
        'featured-content' => 'featured_enabled',
        'blog'             => 'blog_enabled',
    ];

    foreach ($homepage_legacy_map as $section => $legacy_key) {
        if (array_key_exists($legacy_key, $homepage_settings)) {
            $visibility[$section] = !empty(
                $homepage_settings[$legacy_key]
            ) ? 1 : 0;
        }
    }

    $integration_legacy_map = [
        'shows'      => 'shows_enabled',
        'newsletter' => 'newsletter_enabled',
    ];

    foreach ($integration_legacy_map as $section => $legacy_key) {
        if (array_key_exists($legacy_key, $integration_settings)) {
            $visibility[$section] = !empty(
                $integration_settings[$legacy_key]
            ) ? 1 : 0;
        }
    }

    return $visibility;
}

/**
 * Get normalized homepage section visibility.
 *
 * The Section Manager map is canonical. Legacy enable settings are consulted
 * only when the canonical map has never been saved or is malformed.
 *
 * @return array
 */
function mpc_get_homepage_section_visibility() {
    $saved = get_option('mpc_homepage_settings', []);

    if (!is_array($saved)) {
        $saved = [];
    }

    if (
        empty($saved['section_visibility'])
        || !is_array($saved['section_visibility'])
    ) {
        return mpc_get_legacy_homepage_section_visibility();
    }

    $defaults = mpc_get_homepage_section_default_visibility();
    $visibility = [];

    foreach (array_keys($defaults) as $section) {
        if (array_key_exists($section, $saved['section_visibility'])) {
            $visibility[$section] = !empty(
                $saved['section_visibility'][$section]
            ) ? 1 : 0;
        } else {
            $visibility[$section] = $defaults[$section];
        }
    }

    return $visibility;
}

/**
 * Determine whether a homepage section is visible.
 *
 * @param string $section Section identifier.
 * @return bool
 */
function mpc_is_homepage_section_visible($section) {
    $section = sanitize_key($section);
    $visibility = mpc_get_homepage_section_visibility();

    return !empty($visibility[$section]);
}

/**
 * Get the maximum number of homepage service items.
 *
 * @return int
 */
function mpc_get_services_item_limit() {
    return 8;
}

/**
 * Default homepage settings.
 */
function mpc_get_homepage_defaults() {
    return [
        'section_order' => implode(',', mpc_get_homepage_section_default_order()),
        'section_visibility' => mpc_get_homepage_section_default_visibility(),

       // Hero. 
        'hero_enabled' => 1,
        'hero_heading' => get_bloginfo('name'),
        'hero_layout' => 'split',
        'hero_height' => 'full_screen',
        'hero_overlay_opacity' => 45,
        'hero_text' => __(
            'A reusable WordPress theme for bands, artists, and music projects.',
            'music-project-core'
        ),        
        'hero_overlay_style' => 'side',

        // Legacy position setting. Keep for backward compatibility.
        'hero_content_position' => 'bottom_left',

        // Hero V2 placement settings.
        'hero_content_horizontal' => '',
        'hero_content_vertical' => '',
        'hero_text_align' => 'auto',

        'hero_mobile_image_id' => 0,
        
        'hero_desktop_video_id' => 0,
        'hero_cta_text' => '',
        'hero_cta_url' => '',

        // Featured Content.
        'featured_enabled' => 1,
        'featured_heading' => __(
            'Featured Content',
            'music-project-core'
        ),
        'featured_label' => __(
            'Latest Release',
            'music-project-core'
        ),
        'featured_title' => '',
        'featured_text' => '',
        'featured_image_id' => 0,
        'featured_cta_text' => '',
        'featured_cta_url' => '',
        'featured_show_quote' => 0,
        'featured_layout' => 'split_card',
        'featured_quote_position' => 'hidden',
        'featured_media_type' => 'image',
        'featured_video_url' => '',

        // Services.
        'services_heading' => __('Services', 'music-project-core'),
        'services_intro' => '',
        'services_layout' => 'grid',
        'services_columns' => '3',
        'services_cta_text' => '',
        'services_cta_url' => '',
        'services_items' => [],

        // Quotes / Testimonials.
        'quotes_heading' => __('Kind Words', 'music-project-core'),
        'quotes_intro' => '',
        'quotes_layout' => 'grid',
        'quotes_count' => 3,
        'quotes_featured_only' => 1,
        'quotes_background_tone' => 'surface',
        'quotes_show_attribution' => 1,

        // Blog.
        'blog_enabled' => 1,
        'blog_heading' => __(
            'Blog',
            'music-project-core'
        ),
        'blog_layout' => 'grid',
        'blog_featured_source' => 'latest',
        'blog_featured_post_id' => 0,
        'blog_posts_per_page' => 2,
        'blog_additional_posts' => 2,
        'blog_show_images' => 1,
        'blog_show_dates' => 1,
        'blog_show_excerpts' => 1,
        'blog_read_more_text' => __(
            'Read More',
            'music-project-core'
        ),
        'blog_view_all_text' => __(
            'View All Posts',
            'music-project-core'
        ),
        'blog_view_all_url' => '/blog',
    ];
}

/**
 * Get all homepage settings.
 */
function mpc_get_homepage_settings() {
    $saved = get_option('mpc_homepage_settings', []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, mpc_get_homepage_defaults());
}

/**
 * Get one homepage setting.
 */
function mpc_get_homepage_setting($key, $default = '') {
    /*
     * Preserve legacy public keys while making Section Manager the canonical
     * visibility source.
     */
    $legacy_visibility_keys = [
        'hero_enabled'     => 'hero',
        'featured_enabled' => 'featured-content',
        'blog_enabled'     => 'blog',
    ];

    if (isset($legacy_visibility_keys[$key])) {
        return mpc_is_homepage_section_visible(
            $legacy_visibility_keys[$key]
        ) ? 1 : 0;
    }

    $settings = mpc_get_homepage_settings();

    return isset($settings[$key]) ? $settings[$key] : $default;
}

function mpc_sanitize_featured_video_url($url) {
    if (!is_scalar($url)) {
        return '';
    }

    $url = esc_url_raw(
        trim((string) $url)
    );

    if ($url === '') {
        return '';
    }

    $host = wp_parse_url(
        $url,
        PHP_URL_HOST
    );

    if (!is_string($host) || $host === '') {
        return '';
    }

    $host = strtolower($host);

    $host = preg_replace(
        '/^www\./',
        '',
        $host
    );

    if (!is_string($host)) {
        return '';
    }

    $allowed_hosts = [
        'youtube.com',
        'youtu.be',
        'vimeo.com',
        'player.vimeo.com',
    ];

    return in_array(
        $host,
        $allowed_hosts,
        true
    )
        ? $url
        : '';
}

/**
 * Sanitize homepage settings before saving.
 */
function mpc_sanitize_homepage_settings($input) {
    $input = is_array($input)
        ? $input
        : [];

    $defaults = mpc_get_homepage_defaults();
    $output = [];

    /**
     * Normalize checkbox-style Homepage values.
     *
     * @param mixed $value Submitted value.
     * @return int
     */
    $sanitize_homepage_toggle = static function (
        $value
    ) {
        if (!is_scalar($value)) {
            return 0;
        }

        $value = strtolower(
            trim((string) $value)
        );

        return in_array(
            $value,
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        )
            ? 1
            : 0;
    };

    // Section Manager.
    $known_sections = mpc_get_homepage_section_default_order();

    $output['section_order'] = isset($input['section_order'])
        ? implode(',', mpc_normalize_homepage_section_order($input['section_order']))
        : implode(',', $known_sections);

$submitted_visibility = (
    isset($input['section_visibility'])
    && is_array($input['section_visibility'])
)
    ? $input['section_visibility']
    : [];

$section_visibility = [];

foreach ($known_sections as $section) {
    $section_visibility[$section] = !empty(
        $submitted_visibility[$section]
    )
        ? 1
        : 0;
}

$output['section_visibility'] =
    $section_visibility;

// Hero.
$allowed_hero_layouts = [
    'split',
    'full_bleed',
];

$hero_layout = (
    isset($input['hero_layout'])
    && is_scalar($input['hero_layout'])
)
    ? sanitize_key(
        (string) $input['hero_layout']
    )
    : $defaults['hero_layout'];

$output['hero_layout'] = in_array(
    $hero_layout,
    $allowed_hero_layouts,
    true
)
    ? $hero_layout
    : $defaults['hero_layout'];

$allowed_hero_heights = [
    'compact',
    'standard',
    'full_screen',
];

$hero_height = (
    isset($input['hero_height'])
    && is_scalar($input['hero_height'])
)
    ? sanitize_key(
        (string) $input['hero_height']
    )
    : $defaults['hero_height'];

$output['hero_height'] = in_array(
    $hero_height,
    $allowed_hero_heights,
    true
)
    ? $hero_height
    : $defaults['hero_height'];

$allowed_hero_overlay_styles = [
    'side',
    'bottom',
    'center',
    'even',
];

$hero_overlay_style = (
    isset($input['hero_overlay_style'])
    && is_scalar($input['hero_overlay_style'])
)
    ? sanitize_key(
        (string) $input['hero_overlay_style']
    )
    : $defaults['hero_overlay_style'];

$output['hero_overlay_style'] = in_array(
    $hero_overlay_style,
    $allowed_hero_overlay_styles,
    true
)
    ? $hero_overlay_style
    : $defaults['hero_overlay_style'];

/*
 * Legacy content-position setting retained for backward compatibility.
 */
$allowed_hero_content_positions = [
    'bottom_left',
    'center_left',
    'bottom_center',
    'center_center',
];

$hero_content_position = (
    isset($input['hero_content_position'])
    && is_scalar(
        $input['hero_content_position']
    )
)
    ? sanitize_key(
        (string) $input[
            'hero_content_position'
        ]
    )
    : $defaults['hero_content_position'];

$output['hero_content_position'] = in_array(
    $hero_content_position,
    $allowed_hero_content_positions,
    true
)
    ? $hero_content_position
    : $defaults['hero_content_position'];

// Hero V2 placement.
$allowed_hero_content_horizontal = [
    'left',
    'center',
    'right',
];

$hero_content_horizontal = (
    isset($input['hero_content_horizontal'])
    && is_scalar(
        $input['hero_content_horizontal']
    )
)
    ? sanitize_key(
        (string) $input[
            'hero_content_horizontal'
        ]
    )
    : '';

$output['hero_content_horizontal'] = in_array(
    $hero_content_horizontal,
    $allowed_hero_content_horizontal,
    true
)
    ? $hero_content_horizontal
    : '';

$allowed_hero_content_vertical = [
    'top',
    'center',
    'bottom',
];

$hero_content_vertical = (
    isset($input['hero_content_vertical'])
    && is_scalar(
        $input['hero_content_vertical']
    )
)
    ? sanitize_key(
        (string) $input[
            'hero_content_vertical'
        ]
    )
    : '';

$output['hero_content_vertical'] = in_array(
    $hero_content_vertical,
    $allowed_hero_content_vertical,
    true
)
    ? $hero_content_vertical
    : '';

$allowed_hero_text_alignments = [
    'auto',
    'left',
    'center',
    'right',
];

$hero_text_align = (
    isset($input['hero_text_align'])
    && is_scalar($input['hero_text_align'])
)
    ? sanitize_key(
        (string) $input['hero_text_align']
    )
    : $defaults['hero_text_align'];

$output['hero_text_align'] = in_array(
    $hero_text_align,
    $allowed_hero_text_alignments,
    true
)
    ? $hero_text_align
    : $defaults['hero_text_align'];

$hero_overlay_opacity = (
    isset($input['hero_overlay_opacity'])
    && is_scalar(
        $input['hero_overlay_opacity']
    )
)
    ? absint(
        (string) $input[
            'hero_overlay_opacity'
        ]
    )
    : absint(
        $defaults['hero_overlay_opacity']
    );

$output['hero_overlay_opacity'] = min(
    100,
    max(0, $hero_overlay_opacity)
);

/*
 * Legacy mirror retained for older theme versions and integrations.
 * Section Manager is the canonical visibility source.
 */
$output['hero_enabled'] =
    $section_visibility['hero'];

$output['hero_heading'] = (
    isset($input['hero_heading'])
    && is_scalar($input['hero_heading'])
)
    ? sanitize_text_field(
        (string) $input['hero_heading']
    )
    : $defaults['hero_heading'];

$output['hero_text'] = (
    isset($input['hero_text'])
    && is_scalar($input['hero_text'])
)
    ? sanitize_textarea_field(
        (string) $input['hero_text']
    )
    : $defaults['hero_text'];

$output['hero_mobile_image_id'] = (
    isset($input['hero_mobile_image_id'])
    && is_scalar(
        $input['hero_mobile_image_id']
    )
)
    ? absint(
        (string) $input[
            'hero_mobile_image_id'
        ]
    )
    : 0;

$output['hero_desktop_video_id'] = (
    isset($input['hero_desktop_video_id'])
    && is_scalar(
        $input['hero_desktop_video_id']
    )
)
    ? absint(
        (string) $input[
            'hero_desktop_video_id'
        ]
    )
    : 0;

$output['hero_cta_text'] = (
    isset($input['hero_cta_text'])
    && is_scalar($input['hero_cta_text'])
)
    ? sanitize_text_field(
        (string) $input['hero_cta_text']
    )
    : $defaults['hero_cta_text'];

$output['hero_cta_url'] = (
    isset($input['hero_cta_url'])
    && is_scalar($input['hero_cta_url'])
)
    ? esc_url_raw(
        (string) $input['hero_cta_url']
    )
    : $defaults['hero_cta_url'];


// Featured Content.

/*
 * Legacy mirror retained for backward compatibility.
 * Section Manager is the canonical visibility source.
 */
$output['featured_enabled'] =
    $section_visibility['featured-content'];

$output['featured_heading'] = (
    isset($input['featured_heading'])
    && is_scalar($input['featured_heading'])
)
    ? sanitize_text_field(
        (string) $input['featured_heading']
    )
    : $defaults['featured_heading'];

$output['featured_label'] = (
    isset($input['featured_label'])
    && is_scalar($input['featured_label'])
)
    ? sanitize_text_field(
        (string) $input['featured_label']
    )
    : $defaults['featured_label'];

$output['featured_title'] = (
    isset($input['featured_title'])
    && is_scalar($input['featured_title'])
)
    ? sanitize_text_field(
        (string) $input['featured_title']
    )
    : $defaults['featured_title'];

$output['featured_text'] = (
    isset($input['featured_text'])
    && is_scalar($input['featured_text'])
)
    ? wp_kses_post(
        (string) $input['featured_text']
    )
    : $defaults['featured_text'];

$output['featured_image_id'] = (
    isset($input['featured_image_id'])
    && is_scalar($input['featured_image_id'])
)
    ? absint(
        (string) $input['featured_image_id']
    )
    : $defaults['featured_image_id'];

$output['featured_cta_text'] = (
    isset($input['featured_cta_text'])
    && is_scalar($input['featured_cta_text'])
)
    ? sanitize_text_field(
        (string) $input['featured_cta_text']
    )
    : $defaults['featured_cta_text'];

$output['featured_cta_url'] = (
    isset($input['featured_cta_url'])
    && is_scalar($input['featured_cta_url'])
)
    ? esc_url_raw(
        (string) $input['featured_cta_url']
    )
    : $defaults['featured_cta_url'];

$allowed_featured_layouts = [
    'split_card',
    'media_left',
    'media_right',
    'stacked',
];

$featured_layout = (
    isset($input['featured_layout'])
    && is_scalar($input['featured_layout'])
)
    ? sanitize_key(
        (string) $input['featured_layout']
    )
    : $defaults['featured_layout'];

$output['featured_layout'] = in_array(
    $featured_layout,
    $allowed_featured_layouts,
    true
)
    ? $featured_layout
    : $defaults['featured_layout'];

$allowed_featured_quote_positions = [
    'beside',
    'below',
    'hidden',
];

$featured_quote_position = (
    isset($input['featured_quote_position'])
    && is_scalar(
        $input['featured_quote_position']
    )
)
    ? sanitize_key(
        (string) $input[
            'featured_quote_position'
        ]
    )
    : $defaults['featured_quote_position'];

$output['featured_quote_position'] = in_array(
    $featured_quote_position,
    $allowed_featured_quote_positions,
    true
)
    ? $featured_quote_position
    : $defaults['featured_quote_position'];

/*
 * Keep the legacy featured_show_quote setting synchronized for older theme
 * versions and integrations.
 */
$output['featured_show_quote'] = (
    $output['featured_quote_position']
    === 'hidden'
)
    ? 0
    : 1;

$allowed_featured_media_types = [
    'image',
    'video',
];

$featured_media_type = (
    isset($input['featured_media_type'])
    && is_scalar($input['featured_media_type'])
)
    ? sanitize_key(
        (string) $input['featured_media_type']
    )
    : $defaults['featured_media_type'];

$output['featured_media_type'] = in_array(
    $featured_media_type,
    $allowed_featured_media_types,
    true
)
    ? $featured_media_type
    : $defaults['featured_media_type'];

$output['featured_video_url'] = isset(
    $input['featured_video_url']
)
    ? mpc_sanitize_featured_video_url(
        $input['featured_video_url']
    )
    : $defaults['featured_video_url'];

// Quotes / Testimonials.
$output['quotes_heading'] = (
    isset($input['quotes_heading'])
    && is_scalar($input['quotes_heading'])
)
    ? sanitize_text_field(
        (string) $input['quotes_heading']
    )
    : $defaults['quotes_heading'];

$output['quotes_intro'] = (
    isset($input['quotes_intro'])
    && is_scalar($input['quotes_intro'])
)
    ? sanitize_textarea_field(
        (string) $input['quotes_intro']
    )
    : $defaults['quotes_intro'];

$allowed_quotes_layouts = [
    'single',
    'grid',
    'featured_first',
];

$quotes_layout = (
    isset($input['quotes_layout'])
    && is_scalar($input['quotes_layout'])
)
    ? sanitize_key(
        (string) $input['quotes_layout']
    )
    : $defaults['quotes_layout'];

$output['quotes_layout'] = in_array(
    $quotes_layout,
    $allowed_quotes_layouts,
    true
)
    ? $quotes_layout
    : $defaults['quotes_layout'];

$quotes_count = (
    isset($input['quotes_count'])
    && is_scalar($input['quotes_count'])
)
    ? absint(
        (string) $input['quotes_count']
    )
    : absint($defaults['quotes_count']);

$output['quotes_count'] = min(
    12,
    max(1, $quotes_count)
);

$output['quotes_featured_only'] = isset(
    $input['quotes_featured_only']
)
    ? $sanitize_homepage_toggle(
        $input['quotes_featured_only']
    )
    : 0;

$output['quotes_show_attribution'] = isset(
    $input['quotes_show_attribution']
)
    ? $sanitize_homepage_toggle(
        $input['quotes_show_attribution']
    )
    : 0;

$allowed_quotes_background_tones = [
    'default',
    'surface',
    'contrast',
];

$quotes_background_tone = (
    isset($input['quotes_background_tone'])
    && is_scalar(
        $input['quotes_background_tone']
    )
)
    ? sanitize_key(
        (string) $input[
            'quotes_background_tone'
        ]
    )
    : $defaults['quotes_background_tone'];

$output['quotes_background_tone'] = in_array(
    $quotes_background_tone,
    $allowed_quotes_background_tones,
    true
)
    ? $quotes_background_tone
    : $defaults['quotes_background_tone'];


    // Blog.

/*
 * Legacy mirror retained for backward compatibility.
 * Section Manager is the canonical visibility source.
 */
$output['blog_enabled'] =
    $section_visibility['blog'];

$output['blog_heading'] = (
    isset($input['blog_heading'])
    && is_scalar($input['blog_heading'])
)
    ? sanitize_text_field(
        (string) $input['blog_heading']
    )
    : $defaults['blog_heading'];

$allowed_blog_layouts = [
    'grid',
    'featured_first',
    'compact',
];

$blog_layout = (
    isset($input['blog_layout'])
    && is_scalar($input['blog_layout'])
)
    ? sanitize_key(
        (string) $input['blog_layout']
    )
    : $defaults['blog_layout'];

$output['blog_layout'] = in_array(
    $blog_layout,
    $allowed_blog_layouts,
    true
)
    ? $blog_layout
    : $defaults['blog_layout'];

$allowed_featured_sources = [
    'latest',
    'manual',
];

$blog_featured_source = (
    isset($input['blog_featured_source'])
    && is_scalar(
        $input['blog_featured_source']
    )
)
    ? sanitize_key(
        (string) $input[
            'blog_featured_source'
        ]
    )
    : $defaults['blog_featured_source'];

$output['blog_featured_source'] = in_array(
    $blog_featured_source,
    $allowed_featured_sources,
    true
)
    ? $blog_featured_source
    : $defaults['blog_featured_source'];

$output['blog_featured_post_id'] = (
    isset($input['blog_featured_post_id'])
    && is_scalar(
        $input['blog_featured_post_id']
    )
)
    ? absint(
        (string) $input[
            'blog_featured_post_id'
        ]
    )
    : $defaults['blog_featured_post_id'];

$blog_posts_per_page = (
    isset($input['blog_posts_per_page'])
    && is_scalar(
        $input['blog_posts_per_page']
    )
)
    ? absint(
        (string) $input[
            'blog_posts_per_page'
        ]
    )
    : absint(
        $defaults['blog_posts_per_page']
    );

$output['blog_posts_per_page'] = min(
    12,
    max(1, $blog_posts_per_page)
);

$blog_additional_posts = (
    isset($input['blog_additional_posts'])
    && is_scalar(
        $input['blog_additional_posts']
    )
)
    ? absint(
        (string) $input[
            'blog_additional_posts'
        ]
    )
    : absint(
        $defaults['blog_additional_posts']
    );

$output['blog_additional_posts'] = min(
    6,
    $blog_additional_posts
);

$output['blog_show_images'] = isset(
    $input['blog_show_images']
)
    ? $sanitize_homepage_toggle(
        $input['blog_show_images']
    )
    : 0;

$output['blog_show_dates'] = isset(
    $input['blog_show_dates']
)
    ? $sanitize_homepage_toggle(
        $input['blog_show_dates']
    )
    : 0;

$output['blog_show_excerpts'] = isset(
    $input['blog_show_excerpts']
)
    ? $sanitize_homepage_toggle(
        $input['blog_show_excerpts']
    )
    : 0;

$output['blog_read_more_text'] = (
    isset($input['blog_read_more_text'])
    && is_scalar(
        $input['blog_read_more_text']
    )
)
    ? sanitize_text_field(
        (string) $input[
            'blog_read_more_text'
        ]
    )
    : $defaults['blog_read_more_text'];

$output['blog_view_all_text'] = (
    isset($input['blog_view_all_text'])
    && is_scalar(
        $input['blog_view_all_text']
    )
)
    ? sanitize_text_field(
        (string) $input[
            'blog_view_all_text'
        ]
    )
    : $defaults['blog_view_all_text'];

$output['blog_view_all_url'] = (
    isset($input['blog_view_all_url'])
    && is_scalar(
        $input['blog_view_all_url']
    )
)
    ? esc_url_raw(
        (string) $input[
            'blog_view_all_url'
        ]
    )
    : $defaults['blog_view_all_url'];

// Services.
$output['services_heading'] = (
    isset($input['services_heading'])
    && is_scalar($input['services_heading'])
)
    ? sanitize_text_field(
        (string) $input['services_heading']
    )
    : $defaults['services_heading'];

$output['services_intro'] = (
    isset($input['services_intro'])
    && is_scalar($input['services_intro'])
)
    ? wp_kses_post(
        (string) $input['services_intro']
    )
    : $defaults['services_intro'];

$allowed_services_layouts = [
    'grid',
    'featured_first',
    'compact',
];

$services_layout = (
    isset($input['services_layout'])
    && is_scalar($input['services_layout'])
)
    ? sanitize_key(
        (string) $input['services_layout']
    )
    : $defaults['services_layout'];

$output['services_layout'] = in_array(
    $services_layout,
    $allowed_services_layouts,
    true
)
    ? $services_layout
    : $defaults['services_layout'];

$allowed_services_columns = [
    '2',
    '3',
    '4',
];

$services_columns = (
    isset($input['services_columns'])
    && is_scalar($input['services_columns'])
)
    ? sanitize_key(
        (string) $input['services_columns']
    )
    : $defaults['services_columns'];

$output['services_columns'] = in_array(
    $services_columns,
    $allowed_services_columns,
    true
)
    ? $services_columns
    : $defaults['services_columns'];

$output['services_cta_text'] = (
    isset($input['services_cta_text'])
    && is_scalar($input['services_cta_text'])
)
    ? sanitize_text_field(
        (string) $input['services_cta_text']
    )
    : $defaults['services_cta_text'];

$output['services_cta_url'] = (
    isset($input['services_cta_url'])
    && is_scalar($input['services_cta_url'])
)
    ? esc_url_raw(
        (string) $input['services_cta_url']
    )
    : $defaults['services_cta_url'];
    
    $services_items = [];

    $submitted_service_items = (
        isset($input['services_items'])
        && is_array($input['services_items'])
    )
        ? array_values($input['services_items'])
        : [];

    $submitted_service_items = array_slice(
        $submitted_service_items,
        0,
        mpc_get_services_item_limit()
    );

    foreach ($submitted_service_items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $title = (
            isset($item['title'])
            && is_scalar($item['title'])
        )
            ? sanitize_text_field(
                (string) $item['title']
            )
            : '';

        $description = (
            isset($item['description'])
            && is_scalar($item['description'])
        )
            ? wp_kses_post(
                (string) $item['description']
            )
            : '';

        $link_text = (
            isset($item['link_text'])
            && is_scalar($item['link_text'])
        )
            ? sanitize_text_field(
                (string) $item['link_text']
            )
            : '';

        $link_url = (
            isset($item['link_url'])
            && is_scalar($item['link_url'])
        )
            ? esc_url_raw(
                (string) $item['link_url']
            )
            : '';

        if (
            $title === ''
            && $description === ''
            && $link_text === ''
            && $link_url === ''
        ) {
            continue;
        }

        $services_items[] = [
            'title'       => $title,
            'description' => $description,
            'link_text'   => $link_text,
            'link_url'    => $link_url,
        ];
    }

    $output['services_items'] = $services_items;        
    return $output;
}

/**
 * Register settings.
 */
function mpc_register_homepage_settings() {
    register_setting(
        'mpc_homepage_settings_group',
        'mpc_homepage_settings',
        [
            'type'              => 'array',
            'sanitize_callback' =>
                'mpc_sanitize_homepage_settings',
            'default'           =>
                mpc_get_homepage_defaults(),
        ]
    );
}
add_action('admin_init', 'mpc_register_homepage_settings');

/**
 * Add admin menu.
 */
function mpc_add_admin_menu() {
    add_menu_page(
        __('Music Project', 'music-project-core'),
        __('Music Project', 'music-project-core'),
        'manage_options',
        'mpc-homepage',
        'mpc_render_homepage_settings_page',
        'dashicons-album',
        30
    );

    add_submenu_page(
        'mpc-homepage',
        __('Homepage', 'music-project-core'),
        __('Homepage', 'music-project-core'),
        'manage_options',
        'mpc-homepage',
        'mpc_render_homepage_settings_page'
    );
}
add_action('admin_menu', 'mpc_add_admin_menu', 5);

/**
 * Enqueue admin assets only on our settings page.
 */
function mpc_enqueue_admin_assets($hook) {
    if ($hook !== 'toplevel_page_mpc-homepage') {
        return;
    }

    wp_enqueue_media();

wp_enqueue_script(
    'mpc-admin',
    MPC_URL . 'assets/admin.js',
    ['jquery', 'jquery-ui-sortable'],
    mpc_get_asset_version('assets/admin.js'),
    true
);

mpc_localize_admin_script();

    wp_enqueue_style(
        'mpc-admin',
        MPC_URL . 'assets/admin.css',
        [],
        mpc_get_asset_version('assets/admin.css')
    );
}
add_action('admin_enqueue_scripts', 'mpc_enqueue_admin_assets');

/**
 * Render reusable media upload field.
 */
/**
 * Render a reusable media upload field.
 *
 * @param string $field_name   Setting field name.
 * @param int    $attachment_id Selected attachment ID.
 * @param string $media_type    WordPress media-library type.
 * @return void
 */
function mpc_render_media_field(
    $field_name,
    $attachment_id,
    $media_type = 'image'
) {
    $attachment_id = absint(
        $attachment_id
    );

    $field_id = 'mpc_' . sanitize_key(
        $field_name
    );

    $media_type = sanitize_key(
        $media_type
    );

    $preview = '';

    if ($attachment_id) {
        if ($media_type === 'image') {
            $preview = wp_get_attachment_image(
                $attachment_id,
                'medium'
            );
        } else {
            $url = wp_get_attachment_url(
                $attachment_id
            );

            if ($url) {
                $file_path = wp_parse_url(
                    $url,
                    PHP_URL_PATH
                );

                $file_name = wp_basename(
                    $file_path ?: $url
                );

                $preview = sprintf(
                    '<p><strong>%1$s</strong><br>%2$s</p>',
                    esc_html__(
                        'Selected file:',
                        'music-project-core'
                    ),
                    esc_html($file_name)
                );
            }
        }
    }
    ?>

    <div class="mpc-media-field">
        <input
            type="hidden"
            id="<?php echo esc_attr($field_id); ?>"
            name="mpc_homepage_settings[<?php echo esc_attr($field_name); ?>]"
            value="<?php echo esc_attr($attachment_id); ?>"
        >

        <div
            class="mpc-media-preview"
            data-preview-for="<?php echo esc_attr($field_id); ?>"
        >
            <?php echo wp_kses_post($preview); ?>
        </div>

        <button
            type="button"
            class="button mpc-media-upload"
            data-target="<?php echo esc_attr($field_id); ?>"
            data-type="<?php echo esc_attr($media_type); ?>"
        >
            <?php
            echo $attachment_id
                ? esc_html__(
                    'Replace File',
                    'music-project-core'
                )
                : esc_html__(
                    'Choose File',
                    'music-project-core'
                );
            ?>
        </button>

        <button
            type="button"
            class="button mpc-media-remove"
            data-target="<?php echo esc_attr($field_id); ?>"
        >
            <?php
            esc_html_e(
                'Remove',
                'music-project-core'
            );
            ?>
        </button>
    </div>

    <?php
}

function mpc_admin_panel_open($id, $title, $description = '', $open = false) {
    ?>
<details id="mpc-panel-<?php echo esc_attr($id); ?>" class="mpc-admin-panel"
    data-panel-id="<?php echo esc_attr($id); ?>" <?php echo $open ? 'open' : ''; ?>>
    <summary class="mpc-admin-panel__summary">
        <span class="mpc-admin-panel__title">
            <?php echo esc_html($title); ?>
        </span>

        <?php if ($description) : ?>
        <span class="mpc-admin-panel__description">
            <?php echo esc_html($description); ?>
        </span>
        <?php endif; ?>

        <span class="mpc-admin-panel__toggle" aria-hidden="true">
            <span class="mpc-admin-panel__toggle-label mpc-admin-panel__toggle-label--closed">
                <?php esc_html_e('Expand', 'music-project-core'); ?>
            </span>

            <span class="mpc-admin-panel__toggle-label mpc-admin-panel__toggle-label--open">
                <?php esc_html_e('Collapse', 'music-project-core'); ?>
            </span>
        </span>
    </summary>

    <div class="mpc-admin-panel__body">
        <?php
}

function mpc_admin_panel_close() {
    ?>
    </div>
</details>
<?php
}

// HTML editor?
function mpc_render_homepage_rich_text_editor($editor_id, $textarea_name, $value = '', $rows = 5) {
    wp_editor(
        $value,
        $editor_id,
        [
            'textarea_name' => $textarea_name,
            'textarea_rows' => $rows,
            'media_buttons' => false,
            'teeny' => true,
            'quicktags' => true,
            'tinymce' => [
                'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                'toolbar2' => '',
            ],
        ]
    );
}

/**
 * Render Homepage settings page.
 */
function mpc_render_homepage_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mpc_get_homepage_settings();
    ?>

<div class="wrap">
    <h1><?php esc_html_e('Music Project Homepage', 'music-project-core'); ?></h1>

    <form method="post" action="options.php" class="mpc-homepage-settings-form">
        <?php settings_fields('mpc_homepage_settings_group'); ?>

        <?php
            $section_definitions = mpc_get_homepage_section_definitions();
            $section_order = mpc_get_homepage_section_order();
            $section_visibility = mpc_get_homepage_section_visibility();
            ?>

        <?php
mpc_admin_panel_open(
    'section-manager',
    __('Section Manager', 'music-project-core'),
    __(
        'Choose which homepage sections appear, then drag the rows or use the move buttons to change their order.',
        'music-project-core'
    ),
    true
);
?>

        <div class="mpc-section-manager" data-moved-template="<?php echo esc_attr__(
        '%1$s moved to position %2$d of %3$d.',
        'music-project-core'
    ); ?>">
            <input type="hidden" class="mpc-section-order-input" name="mpc_homepage_settings[section_order]"
                value="<?php echo esc_attr(implode(',', $section_order)); ?>">

            <p id="mpc-section-manager-instructions" class="description mpc-section-manager__instructions">
                <?php
        esc_html_e(
            'Use the checkbox to show or hide a section. Drag the reorder handle, or use Move up and Move down.',
            'music-project-core'
        );
        ?>
            </p>

            <ul class="mpc-section-list" aria-describedby="mpc-section-manager-instructions">
                <?php
        $section_count = count($section_order);

        foreach ($section_order as $section_index => $section) :
            if (!isset($section_definitions[$section])) {
                continue;
            }

            $definition = $section_definitions[$section];
            ?>

                <li class="mpc-section-list__item" data-section="<?php echo esc_attr($section); ?>"
                    data-section-label="<?php echo esc_attr($definition['label']); ?>">
                    <span class="mpc-section-list__handle" aria-hidden="true"
                        title="<?php esc_attr_e('Drag to reorder', 'music-project-core'); ?>">
                        ↕
                    </span>

                    <label class="mpc-section-list__label">
                        <input type="checkbox"
                            name="mpc_homepage_settings[section_visibility][<?php echo esc_attr($section); ?>]"
                            value="1" <?php checked(!empty($section_visibility[$section]), true); ?>>

                        <span class="mpc-section-list__content">
                            <strong>
                                <?php echo esc_html($definition['label']); ?>
                            </strong>

                            <small>
                                <?php echo esc_html($definition['description']); ?>
                            </small>
                        </span>
                    </label>

                    <div class="mpc-section-list__controls" role="group" aria-label="<?php
                    printf(
                        /* translators: %s is a homepage section name. */
                        esc_attr__('Reorder %s', 'music-project-core'),
                        esc_attr($definition['label'])
                    );
                    ?>">
                        <button type="button"
                            class="button button-small mpc-section-list__move mpc-section-list__move--up"
                            data-direction="up" <?php disabled($section_index, 0); ?>>
                            <span aria-hidden="true">↑</span>

                            <span class="mpc-section-list__move-text">
                                <?php
                            esc_html_e(
                                'Move up',
                                'music-project-core'
                            );
                            ?>
                            </span>
                        </button>

                        <button type="button"
                            class="button button-small mpc-section-list__move mpc-section-list__move--down"
                            data-direction="down" <?php disabled($section_index, $section_count - 1); ?>>
                            <span aria-hidden="true">↓</span>

                            <span class="mpc-section-list__move-text">
                                <?php
                            esc_html_e(
                                'Move down',
                                'music-project-core'
                            );
                            ?>
                            </span>
                        </button>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

            <p class="screen-reader-text mpc-section-manager__status" aria-live="polite" aria-atomic="true"></p>
        </div>

        <?php mpc_admin_panel_close(); ?>

        <?php
            mpc_admin_panel_open(
                'hero',
                __('Hero Section', 'music-project-core'),
                __('Homepage hero content, media, layout, height, overlay, and positioning.', 'music-project-core'),
                true
            );
            ?>

        <table class="form-table" role="presentation">


            <tr>
                <th scope="row">
                    <label for="mpc_homepage_hero_layout">
                        <?php esc_html_e('Hero Layout', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_hero_layout" class="mpc-hero-layout-select"
                        name="mpc_homepage_settings[hero_layout]">
                        <option value="split" <?php selected($settings['hero_layout'], 'split'); ?>>
                            <?php esc_html_e('Split Media / Text', 'music-project-core'); ?>
                        </option>
                        <option value="full_bleed" <?php selected($settings['hero_layout'], 'full_bleed'); ?>>
                            <?php esc_html_e('Full-Bleed Media', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Split shows media beside the text. Full-Bleed places text over a large background image or video.', 'music-project-core'); ?>
                    </p>

                    <div class="mpc-admin-helper mpc-admin-helper--split">
                        <strong><?php esc_html_e('Split Media / Text:', 'music-project-core'); ?></strong>
                        <?php esc_html_e('Best for a traditional landing section with clear text and a framed image or video.', 'music-project-core'); ?>
                    </div>

                    <div class="mpc-admin-helper mpc-admin-helper--full-bleed">
                        <strong><?php esc_html_e('Full-Bleed Media:', 'music-project-core'); ?></strong>
                        <?php esc_html_e('Best for a cinematic hero. Uses desktop video when available; otherwise falls back to the hero image.', 'music-project-core'); ?>
                    </div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_hero_height">
                        <?php esc_html_e('Hero Height', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_hero_height" name="mpc_homepage_settings[hero_height]">
                        <option value="compact"
                            <?php selected($settings['hero_height'] ?? 'full_screen', 'compact'); ?>>
                            <?php esc_html_e('Compact', 'music-project-core'); ?>
                        </option>
                        <option value="standard"
                            <?php selected($settings['hero_height'] ?? 'full_screen', 'standard'); ?>>
                            <?php esc_html_e('Standard', 'music-project-core'); ?>
                        </option>
                        <option value="full_screen"
                            <?php selected($settings['hero_height'] ?? 'full_screen', 'full_screen'); ?>>
                            <?php esc_html_e('Full Screen', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls the visual height of the homepage hero section.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-hero-full-bleed-row mpc-hero-full-bleed-field">
                <th scope="row">
                    <label for="mpc_homepage_hero_overlay_opacity">
                        <?php esc_html_e('Hero Overlay Strength', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input id="mpc_homepage_hero_overlay_opacity" type="range" min="0" max="100" step="5"
                        name="mpc_homepage_settings[hero_overlay_opacity]"
                        value="<?php echo esc_attr($settings['hero_overlay_opacity']); ?>"
                        oninput="this.nextElementSibling.value = this.value">
                    <output><?php echo esc_html($settings['hero_overlay_opacity']); ?></output>

                    <p class="description">
                        <?php esc_html_e('Controls the darkness of the full-bleed hero overlay. Lower numbers show more image/video.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-hero-full-bleed-row mpc-hero-full-bleed-field">
                <th scope="row">
                    <label for="mpc_homepage_hero_overlay_style">
                        <?php esc_html_e('Hero Overlay Style', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_hero_overlay_style" name="mpc_homepage_settings[hero_overlay_style]">
                        <option value="side" <?php selected($settings['hero_overlay_style'], 'side'); ?>>
                            <?php esc_html_e('Side Gradient', 'music-project-core'); ?>
                        </option>
                        <option value="bottom" <?php selected($settings['hero_overlay_style'], 'bottom'); ?>>
                            <?php esc_html_e('Bottom Gradient', 'music-project-core'); ?>
                        </option>
                        <option value="center" <?php selected($settings['hero_overlay_style'], 'center'); ?>>
                            <?php esc_html_e('Center Vignette', 'music-project-core'); ?>
                        </option>
                        <option value="even" <?php selected($settings['hero_overlay_style'], 'even'); ?>>
                            <?php esc_html_e('Even Wash', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls the direction/style of the full-bleed hero overlay.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <?php
                $legacy_content_position = $settings['hero_content_position'] ?? 'bottom_left';

                $legacy_position_map = [
                    'bottom_left' => [
                        'horizontal' => 'left',
                        'vertical' => 'bottom',
                    ],
                    'center_left' => [
                        'horizontal' => 'left',
                        'vertical' => 'center',
                    ],
                    'bottom_center' => [
                        'horizontal' => 'center',
                        'vertical' => 'bottom',
                    ],
                    'center_center' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ];

                $legacy_position = $legacy_position_map[$legacy_content_position] ?? $legacy_position_map['bottom_left'];

                $hero_content_horizontal = $settings['hero_content_horizontal'] ?? '';
                $hero_content_vertical = $settings['hero_content_vertical'] ?? '';
                $hero_text_align = $settings['hero_text_align'] ?? 'auto';

                if (!in_array($hero_content_horizontal, ['left', 'center', 'right'], true)) {
                    $hero_content_horizontal = $legacy_position['horizontal'];
                }

                if (!in_array($hero_content_vertical, ['top', 'center', 'bottom'], true)) {
                    $hero_content_vertical = $legacy_position['vertical'];
                }

                if (!in_array($hero_text_align, ['auto', 'left', 'center', 'right'], true)) {
                    $hero_text_align = 'auto';
                }
                ?>

            <tr style="display: none;">
                <td colspan="2">
                    <input type="hidden" name="mpc_homepage_settings[hero_content_position]"
                        value="<?php echo esc_attr($legacy_content_position); ?>">
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-hero-full-bleed-row mpc-hero-full-bleed-field">
                <th scope="row">
                    <label for="mpc_homepage_hero_content_horizontal">
                        <?php esc_html_e('Full-Bleed Horizontal Position', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_hero_content_horizontal"
                        name="mpc_homepage_settings[hero_content_horizontal]">
                        <option value="left" <?php selected($hero_content_horizontal, 'left'); ?>>
                            <?php esc_html_e('Left', 'music-project-core'); ?>
                        </option>
                        <option value="center" <?php selected($hero_content_horizontal, 'center'); ?>>
                            <?php esc_html_e('Center', 'music-project-core'); ?>
                        </option>
                        <option value="right" <?php selected($hero_content_horizontal, 'right'); ?>>
                            <?php esc_html_e('Right', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls where the hero content block sits horizontally in full-bleed layout.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-hero-full-bleed-row mpc-hero-full-bleed-field">
                <th scope="row">
                    <label for="mpc_homepage_hero_content_vertical">
                        <?php esc_html_e('Full-Bleed Vertical Position', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_hero_content_vertical" name="mpc_homepage_settings[hero_content_vertical]">
                        <option value="top" <?php selected($hero_content_vertical, 'top'); ?>>
                            <?php esc_html_e('Top', 'music-project-core'); ?>
                        </option>
                        <option value="center" <?php selected($hero_content_vertical, 'center'); ?>>
                            <?php esc_html_e('Center', 'music-project-core'); ?>
                        </option>
                        <option value="bottom" <?php selected($hero_content_vertical, 'bottom'); ?>>
                            <?php esc_html_e('Bottom', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls where the hero content block sits vertically in full-bleed layout.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_hero_text_align">
                        <?php esc_html_e('Hero Text Alignment', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_hero_text_align" name="mpc_homepage_settings[hero_text_align]">
                        <option value="auto" <?php selected($hero_text_align, 'auto'); ?>>
                            <?php esc_html_e('Auto', 'music-project-core'); ?>
                        </option>
                        <option value="left" <?php selected($hero_text_align, 'left'); ?>>
                            <?php esc_html_e('Left', 'music-project-core'); ?>
                        </option>
                        <option value="center" <?php selected($hero_text_align, 'center'); ?>>
                            <?php esc_html_e('Center', 'music-project-core'); ?>
                        </option>
                        <option value="right" <?php selected($hero_text_align, 'right'); ?>>
                            <?php esc_html_e('Right', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Auto matches the horizontal position. You can override it manually.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="hero_heading">
                        <?php esc_html_e('Hero Heading', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="hero_heading" name="mpc_homepage_settings[hero_heading]" class="regular-text"
                        value="<?php echo esc_attr($settings['hero_heading']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="hero_text">
                        <?php esc_html_e('Hero Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <textarea id="hero_text" name="mpc_homepage_settings[hero_text]" class="large-text"
                        rows="4"><?php echo esc_textarea($settings['hero_text']); ?></textarea>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Mobile Hero Image', 'music-project-core'); ?>
                </th>
                <td>
                    <?php mpc_render_media_field('hero_mobile_image_id', $settings['hero_mobile_image_id'], 'image'); ?>

                    <p class="description">
                        <?php
                            esc_html_e(
                                'Used on mobile and as the fallback when desktop video is unavailable or reduced motion is preferred.',
                                'music-project-core'
                            );
                            ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Desktop Hero Video', 'music-project-core'); ?>
                </th>
                <td>
                    <?php mpc_render_media_field('hero_desktop_video_id', $settings['hero_desktop_video_id'], 'video'); ?>

                    <p class="description">
                        <?php esc_html_e('MP4 recommended. Used for desktop hero background/video area.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="hero_cta_text">
                        <?php esc_html_e('CTA Button Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="hero_cta_text" name="mpc_homepage_settings[hero_cta_text]"
                        class="regular-text" value="<?php echo esc_attr($settings['hero_cta_text']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="hero_cta_url">
                        <?php esc_html_e('CTA Button URL', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="url" id="hero_cta_url" name="mpc_homepage_settings[hero_cta_url]" class="regular-text"
                        value="<?php echo esc_url($settings['hero_cta_url']); ?>">
                </td>
            </tr>
        </table>

        <?php mpc_admin_panel_close(); ?>
        <?php
mpc_admin_panel_open(
    'featured-content',
    __('Featured Content', 'music-project-core'),
    __('Promo section for a release, video, announcement, or featured message.', 'music-project-core')
);
?>

        <table class="form-table" role="presentation">


            <tr>
                <th scope="row">
                    <label for="featured_heading">
                        <?php esc_html_e('Section Heading', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="featured_heading" name="mpc_homepage_settings[featured_heading]"
                        class="regular-text" value="<?php echo esc_attr($settings['featured_heading']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_featured_layout">
                        <?php esc_html_e('Featured Layout', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_featured_layout" name="mpc_homepage_settings[featured_layout]">
                        <option value="split_card" <?php selected($settings['featured_layout'], 'split_card'); ?>>
                            <?php esc_html_e('Split Card', 'music-project-core'); ?>
                        </option>
                        <option value="media_left" <?php selected($settings['featured_layout'], 'media_left'); ?>>
                            <?php esc_html_e('Media Left / Text Right', 'music-project-core'); ?>
                        </option>
                        <option value="media_right" <?php selected($settings['featured_layout'], 'media_right'); ?>>
                            <?php esc_html_e('Text Left / Media Right', 'music-project-core'); ?>
                        </option>
                        <option value="stacked" <?php selected($settings['featured_layout'], 'stacked'); ?>>
                            <?php esc_html_e('Stacked / Poster', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls the visual layout of the featured promo card.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_featured_quote_position">
                        <?php esc_html_e('Optional Quote Position', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_featured_quote_position"
                        name="mpc_homepage_settings[featured_quote_position]">
                        <option value="hidden" <?php selected($settings['featured_quote_position'], 'hidden'); ?>>
                            <?php esc_html_e('Hidden', 'music-project-core'); ?>
                        </option>
                        <option value="beside" <?php selected($settings['featured_quote_position'], 'beside'); ?>>
                            <?php esc_html_e('Beside Featured Content', 'music-project-core'); ?>
                        </option>
                        <option value="below" <?php selected($settings['featured_quote_position'], 'below'); ?>>
                            <?php esc_html_e('Below Featured Content', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Legacy option. For most sites, use the standalone Quotes / Testimonials homepage section instead.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="featured_label">
                        <?php esc_html_e('Featured Label', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="featured_label" name="mpc_homepage_settings[featured_label]"
                        class="regular-text" value="<?php echo esc_attr($settings['featured_label']); ?>"
                        placeholder="<?php esc_attr_e('Latest Release, New Video, Announcement, etc.', 'music-project-core'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="featured_title">
                        <?php esc_html_e('Featured Title', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="featured_title" name="mpc_homepage_settings[featured_title]"
                        class="regular-text" value="<?php echo esc_attr($settings['featured_title']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="featured_text">
                        <?php esc_html_e('Featured Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <?php
            mpc_render_homepage_rich_text_editor(
                'mpc_featured_text_editor',
                'mpc_homepage_settings[featured_text]',
                $settings['featured_text'] ?? '',
                6
            );
            ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_featured_media_type">
                        <?php esc_html_e('Featured Media Type', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_featured_media_type" class="mpc-featured-media-type-select"
                        name="mpc_homepage_settings[featured_media_type]">
                        <option value="image" <?php selected($settings['featured_media_type'], 'image'); ?>>
                            <?php esc_html_e('Image / Artwork', 'music-project-core'); ?>
                        </option>
                        <option value="video" <?php selected($settings['featured_media_type'], 'video'); ?>>
                            <?php esc_html_e('Video Embed', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Choose whether the featured media area shows an uploaded image or a YouTube/Vimeo video.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Featured Image', 'music-project-core'); ?>
                </th>
                <td>
                    <?php mpc_render_media_field('featured_image_id', $settings['featured_image_id'], 'image'); ?>

                    <p class="description">
                        <?php esc_html_e('Used when Featured Media Type is set to Image / Artwork.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-featured-video-row">
                <th scope="row">
                    <label for="mpc_homepage_featured_video_url">
                        <?php esc_html_e('Featured Video URL', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input id="mpc_homepage_featured_video_url" class="regular-text" type="url"
                        name="mpc_homepage_settings[featured_video_url]"
                        value="<?php echo esc_url($settings['featured_video_url']); ?>"
                        placeholder="https://www.youtube.com/watch?v=...">

                    <p class="description">
                        <?php esc_html_e('Supports YouTube and Vimeo URLs only. Used when Featured Media Type is set to Video Embed.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="featured_cta_text">
                        <?php esc_html_e('CTA Button Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="featured_cta_text" name="mpc_homepage_settings[featured_cta_text]"
                        class="regular-text" value="<?php echo esc_attr($settings['featured_cta_text']); ?>"
                        placeholder="<?php esc_attr_e('Listen Now, Watch Video, Read More, etc.', 'music-project-core'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="featured_cta_url">
                        <?php esc_html_e('CTA Button URL', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="url" id="featured_cta_url" name="mpc_homepage_settings[featured_cta_url]"
                        class="regular-text" value="<?php echo esc_url($settings['featured_cta_url']); ?>"
                        placeholder="https://...">

                    <p class="description">
                        <?php esc_html_e('Can be a streaming link, video link, internal page URL, or any promo URL.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php mpc_admin_panel_close(); ?>

        <?php
mpc_admin_panel_open(
    'services',
    __('Services', 'music-project-core'),
    __('Text-based service cards for weddings, sessions, lessons, packages, or offerings.', 'music-project-core')
);
?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mpc_homepage_services_heading">
                        <?php esc_html_e('Services Heading', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="mpc_homepage_services_heading" name="mpc_homepage_settings[services_heading]"
                        value="<?php echo esc_attr($settings['services_heading'] ?? __('Services', 'music-project-core')); ?>"
                        class="regular-text">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_services_intro">
                        <?php esc_html_e('Services Intro Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <?php
            mpc_render_homepage_rich_text_editor(
                'mpc_services_intro_editor',
                'mpc_homepage_settings[services_intro]',
                $settings['services_intro'] ?? '',
                5
            );
            ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_services_layout">
                        <?php esc_html_e('Services Layout', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_services_layout" name="mpc_homepage_settings[services_layout]">
                        <option value="grid" <?php selected($settings['services_layout'] ?? 'grid', 'grid'); ?>>
                            <?php esc_html_e('Grid', 'music-project-core'); ?>
                        </option>
                        <option value="featured_first"
                            <?php selected($settings['services_layout'] ?? 'grid', 'featured_first'); ?>>
                            <?php esc_html_e('Featured First', 'music-project-core'); ?>
                        </option>
                        <option value="compact" <?php selected($settings['services_layout'] ?? 'grid', 'compact'); ?>>
                            <?php esc_html_e('Compact List', 'music-project-core'); ?>
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_services_columns">
                        <?php esc_html_e('Cards Per Row', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_services_columns" name="mpc_homepage_settings[services_columns]">
                        <option value="2" <?php selected($settings['services_columns'] ?? '3', '2'); ?>>
                            <?php esc_html_e('2', 'music-project-core'); ?>
                        </option>
                        <option value="3" <?php selected($settings['services_columns'] ?? '3', '3'); ?>>
                            <?php esc_html_e('3', 'music-project-core'); ?>
                        </option>
                        <option value="4" <?php selected($settings['services_columns'] ?? '3', '4'); ?>>
                            <?php esc_html_e('4', 'music-project-core'); ?>
                        </option>
                    </select>
                </td>
            </tr>
        </table>

        <?php
$service_item_defaults = [
    'title'       => '',
    'description' => '',
    'link_text'   => '',
    'link_url'    => '',
];

$service_item_limit = mpc_get_services_item_limit();

$services_items = isset($settings['services_items'])
    && is_array($settings['services_items'])
        ? array_values($settings['services_items'])
        : [];

$services_items = array_slice(
    $services_items,
    0,
    $service_item_limit
);

/**
 * Render one Service editor card.
 *
 * JavaScript reindexes every field after adding, removing, or moving cards.
 *
 * @param array      $item  Service item data.
 * @param int|string $index Item index or template placeholder.
 * @return void
 */
$render_service_item = static function (
    $item,
    $index
) use (
    $service_item_defaults
) {
    $item = wp_parse_args(
        is_array($item) ? $item : [],
        $service_item_defaults
    );

    $index_token = (string) $index;

    $visible_number = is_numeric($index)
        ? absint($index) + 1
        : 1;

    $field_name_base = sprintf(
        'mpc_homepage_settings[services_items][%s]',
        $index_token
    );

    $field_id_base = sprintf(
        'mpc_service_item_%s',
        $index_token
    );
    ?>

        <div class="mpc-service-item" data-service-item>
            <header class="mpc-service-item__header">
                <button type="button" class="mpc-service-item__handle"
                    aria-label="<?php esc_attr_e('Drag service item to reorder', 'music-project-core'); ?>"
                    title="<?php esc_attr_e('Drag to reorder', 'music-project-core'); ?>">
                    <span aria-hidden="true">↕</span>
                </button>

                <h4 class="mpc-service-item__title">
                    <?php esc_html_e('Service', 'music-project-core'); ?>

                    <span data-service-number>
                        <?php echo esc_html($visible_number); ?>
                    </span>
                </h4>

                <div class="mpc-service-item__controls" role="group"
                    aria-label="<?php esc_attr_e('Reorder or remove service item', 'music-project-core'); ?>">
                    <button type="button" class="button button-small mpc-service-item__move mpc-service-item__move--up"
                        data-service-direction="up">
                        <span aria-hidden="true">↑</span>

                        <span>
                            <?php esc_html_e('Move up', 'music-project-core'); ?>
                        </span>
                    </button>

                    <button type="button"
                        class="button button-small mpc-service-item__move mpc-service-item__move--down"
                        data-service-direction="down">
                        <span aria-hidden="true">↓</span>

                        <span>
                            <?php esc_html_e('Move down', 'music-project-core'); ?>
                        </span>
                    </button>

                    <button type="button" class="button button-small mpc-service-item__remove">
                        <?php esc_html_e('Remove', 'music-project-core'); ?>
                    </button>
                </div>
            </header>

            <div class="mpc-service-item__fields">
                <div class="mpc-service-item__field">
                    <label data-service-label-for="title" for="<?php echo esc_attr($field_id_base . '_title'); ?>">
                        <?php esc_html_e('Heading', 'music-project-core'); ?>
                    </label>

                    <input id="<?php echo esc_attr($field_id_base . '_title'); ?>" type="text" class="regular-text"
                        data-service-field="title" name="<?php echo esc_attr($field_name_base . '[title]'); ?>"
                        value="<?php echo esc_attr($item['title']); ?>">
                </div>

                <div class="mpc-service-item__field mpc-service-item__field--description">
                    <label data-service-label-for="description"
                        for="<?php echo esc_attr($field_id_base . '_description'); ?>">
                        <?php esc_html_e('Description', 'music-project-core'); ?>
                    </label>

                    <textarea id="<?php echo esc_attr($field_id_base . '_description'); ?>" class="large-text" rows="4"
                        data-service-field="description"
                        name="<?php echo esc_attr($field_name_base . '[description]'); ?>"
                        aria-describedby="mpc-services-description-help"><?php echo esc_textarea($item['description']); ?></textarea>
                </div>

                <div class="mpc-service-item__field">
                    <label data-service-label-for="link_text"
                        for="<?php echo esc_attr($field_id_base . '_link_text'); ?>">
                        <?php esc_html_e('Link Text', 'music-project-core'); ?>
                    </label>

                    <input id="<?php echo esc_attr($field_id_base . '_link_text'); ?>" type="text" class="regular-text"
                        data-service-field="link_text" name="<?php echo esc_attr($field_name_base . '[link_text]'); ?>"
                        value="<?php echo esc_attr($item['link_text']); ?>"
                        placeholder="<?php esc_attr_e('Learn More', 'music-project-core'); ?>">
                </div>

                <div class="mpc-service-item__field">
                    <label data-service-label-for="link_url"
                        for="<?php echo esc_attr($field_id_base . '_link_url'); ?>">
                        <?php esc_html_e('Link URL', 'music-project-core'); ?>
                    </label>

                    <input id="<?php echo esc_attr($field_id_base . '_link_url'); ?>" type="url" class="regular-text"
                        data-service-field="link_url" name="<?php echo esc_attr($field_name_base . '[link_url]'); ?>"
                        value="<?php echo esc_url($item['link_url']); ?>" placeholder="https://example.com/service">
                </div>
            </div>
        </div>
        <?php
};
?>

        <div class="mpc-services-editor" data-service-max="<?php echo esc_attr($service_item_limit); ?>"
            data-service-item-label="<?php esc_attr_e('Service', 'music-project-core'); ?>"
            data-service-added-message="<?php esc_attr_e('Service item added.', 'music-project-core'); ?>"
            data-service-removed-template="<?php echo esc_attr__('%s removed.', 'music-project-core'); ?>"
            data-service-moved-template="<?php echo esc_attr__('%1$s moved to position %2$d of %3$d.', 'music-project-core'); ?>"
            data-service-limit-message="<?php
    echo esc_attr(
        sprintf(
            /* translators: %d is the maximum number of services. */
            __(
                'You can add up to %d services.',
                'music-project-core'
            ),
            $service_item_limit
        )
    );
    ?>" data-service-drag-template="<?php echo esc_attr__('Drag %s to reorder', 'music-project-core'); ?>"
            data-service-controls-template="<?php echo esc_attr__('Reorder or remove %s', 'music-project-core'); ?>"
            data-service-remove-template="<?php echo esc_attr__('Remove %s', 'music-project-core'); ?>">
            <div class="mpc-services-editor__toolbar">
                <div>
                    <h3 class="mpc-services-editor__heading">
                        <?php esc_html_e('Service Items', 'music-project-core'); ?>
                    </h3>

                    <p id="mpc-services-editor-instructions" class="description">
                        <?php
                printf(
                    /* translators: %d is the maximum number of services. */
                    esc_html__(
                        'Add up to %d services. Drag the cards or use the move buttons to reorder them.',
                        'music-project-core'
                    ),
                    $service_item_limit
                );
                ?>
                    </p>

                    <p id="mpc-services-description-help" class="description">
                        <?php
                esc_html_e(
                    'Service descriptions may contain basic formatting and links.',
                    'music-project-core'
                );
                ?>
                    </p>
                </div>

                <button type="button" class="button button-secondary mpc-services-editor__add"
                    <?php disabled(count($services_items) >= $service_item_limit); ?>>
                    <?php esc_html_e('Add Service', 'music-project-core'); ?>
                </button>
            </div>

            <div class="mpc-services-editor__list" aria-describedby="mpc-services-editor-instructions">
                <?php foreach ($services_items as $index => $item) : ?>
                <?php $render_service_item($item, $index); ?>
                <?php endforeach; ?>
            </div>

            <p class="mpc-services-editor__empty" <?php if (!empty($services_items)) : ?> hidden <?php endif; ?>>
                <?php
        esc_html_e(
            'No service items have been added yet.',
            'music-project-core'
        );
        ?>
            </p>

            <div class="mpc-services-editor__footer">
                <p class="mpc-services-editor__count">
                    <span data-service-count>
                        <?php echo esc_html(count($services_items)); ?>
                    </span>

                    <?php
            printf(
                /* translators: %d is the maximum number of service items. */
                esc_html__(
                    'of %d service items',
                    'music-project-core'
                ),
                $service_item_limit
            );
            ?>
                </p>
            </div>

            <p class="screen-reader-text mpc-services-editor__status" aria-live="polite" aria-atomic="true"></p>

            <template class="mpc-service-item-template">
                <?php
        $render_service_item(
            $service_item_defaults,
            '__INDEX__'
        );
        ?>
            </template>

            <noscript>
                <p class="notice notice-warning inline">
                    <?php
            esc_html_e(
                'JavaScript is required to add, remove, or reorder service items.',
                'music-project-core'
            );
            ?>
                </p>
            </noscript>
        </div>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mpc_homepage_services_cta_text">
                        <?php esc_html_e('Bottom CTA Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="mpc_homepage_services_cta_text"
                        name="mpc_homepage_settings[services_cta_text]"
                        value="<?php echo esc_attr($settings['services_cta_text'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_services_cta_url">
                        <?php esc_html_e('Bottom CTA URL', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="url" id="mpc_homepage_services_cta_url" name="mpc_homepage_settings[services_cta_url]"
                        value="<?php echo esc_url($settings['services_cta_url'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>
        </table>

        <?php mpc_admin_panel_close(); ?>
        <?php
mpc_admin_panel_open(
    'quotes',
    __('Quotes / Testimonials', 'music-project-core'),
    __('Standalone social proof section using saved quotes or testimonials.', 'music-project-core')
);
?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mpc_homepage_quotes_heading">
                        <?php esc_html_e('Section Heading', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="mpc_homepage_quotes_heading" name="mpc_homepage_settings[quotes_heading]"
                        value="<?php echo esc_attr($settings['quotes_heading'] ?? __('Kind Words', 'music-project-core')); ?>"
                        class="regular-text">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_quotes_intro">
                        <?php esc_html_e('Section Intro', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <textarea id="mpc_homepage_quotes_intro" name="mpc_homepage_settings[quotes_intro]" rows="3"
                        class="large-text"><?php echo esc_textarea($settings['quotes_intro'] ?? ''); ?></textarea>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_quotes_layout">
                        <?php esc_html_e('Layout', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_quotes_layout" name="mpc_homepage_settings[quotes_layout]">
                        <option value="single" <?php selected($settings['quotes_layout'] ?? 'grid', 'single'); ?>>
                            <?php esc_html_e('Single Featured Quote', 'music-project-core'); ?>
                        </option>
                        <option value="grid" <?php selected($settings['quotes_layout'] ?? 'grid', 'grid'); ?>>
                            <?php esc_html_e('Grid', 'music-project-core'); ?>
                        </option>
                        <option value="featured_first"
                            <?php selected($settings['quotes_layout'] ?? 'grid', 'featured_first'); ?>>
                            <?php esc_html_e('Featured First', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Controls how quotes/testimonials are displayed on the homepage.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_quotes_count">
                        <?php esc_html_e('Number of Quotes', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" id="mpc_homepage_quotes_count" name="mpc_homepage_settings[quotes_count]"
                        value="<?php echo esc_attr($settings['quotes_count'] ?? 3); ?>" min="1" max="12" step="1"
                        class="small-text">

                    <p class="description">
                        <?php esc_html_e('Single Featured Quote layout will only show one quote.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Quote Source', 'music-project-core'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="mpc_homepage_settings[quotes_featured_only]" value="1"
                            <?php checked(!empty($settings['quotes_featured_only'])); ?>>
                        <?php esc_html_e('Only show featured quotes/testimonials', 'music-project-core'); ?>
                    </label>

                    <p class="description">
                        <?php esc_html_e('Featured quotes are managed under Music Project → Quotes / Testimonials.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Attribution', 'music-project-core'); ?>
                </th>
                <td>
                    <input type="hidden" name="mpc_homepage_settings[quotes_show_attribution]" value="0">

                    <label>
                        <input type="checkbox" name="mpc_homepage_settings[quotes_show_attribution]" value="1"
                            <?php checked(!empty($settings['quotes_show_attribution'])); ?>>
                        <?php esc_html_e('Show quote/testimonial attribution', 'music-project-core'); ?>
                    </label>

                    <p class="description">
                        <?php esc_html_e('Shows the source/client and context below each quote when available.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_quotes_background_tone">
                        <?php esc_html_e('Background Tone', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_quotes_background_tone"
                        name="mpc_homepage_settings[quotes_background_tone]">
                        <option value="default"
                            <?php selected($settings['quotes_background_tone'] ?? 'surface', 'default'); ?>>
                            <?php esc_html_e('Default', 'music-project-core'); ?>
                        </option>
                        <option value="surface"
                            <?php selected($settings['quotes_background_tone'] ?? 'surface', 'surface'); ?>>
                            <?php esc_html_e('Surface', 'music-project-core'); ?>
                        </option>
                        <option value="contrast"
                            <?php selected($settings['quotes_background_tone'] ?? 'surface', 'contrast'); ?>>
                            <?php esc_html_e('Contrast', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Use Contrast when you want the quotes section to visually stand apart from the surrounding homepage sections.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php mpc_admin_panel_close(); ?>

        <?php
mpc_admin_panel_open(
    'blog',
    __('Blog / News', 'music-project-core'),
    __('Recent posts, featured article layout, and homepage blog display settings.', 'music-project-core')
);
?>

        <table class="form-table" role="presentation">


            <tr>
                <th scope="row">
                    <label for="blog_heading">
                        <?php esc_html_e('Section Heading', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="blog_heading" name="mpc_homepage_settings[blog_heading]" class="regular-text"
                        value="<?php echo esc_attr($settings['blog_heading']); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mpc_homepage_blog_layout">
                        <?php esc_html_e('Blog / News Layout', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_blog_layout" class="mpc-blog-layout-select"
                        name="mpc_homepage_settings[blog_layout]">
                        <option value="grid" <?php selected($settings['blog_layout'], 'grid'); ?>>
                            <?php esc_html_e('Grid', 'music-project-core'); ?>
                        </option>
                        <option value="featured_first" <?php selected($settings['blog_layout'], 'featured_first'); ?>>
                            <?php esc_html_e('Featured First', 'music-project-core'); ?>
                        </option>
                        <option value="compact" <?php selected($settings['blog_layout'], 'compact'); ?>>
                            <?php esc_html_e('Compact List', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Featured First is best for editorial posts, song deep-dives, studio dispatches, or evergreen updates.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-blog-featured-first-row">
                <th scope="row">
                    <label for="mpc_homepage_blog_featured_source">
                        <?php esc_html_e('Featured Post Source', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <select id="mpc_homepage_blog_featured_source" class="mpc-blog-featured-source-select"
                        name="mpc_homepage_settings[blog_featured_source]">
                        <option value="latest" <?php selected($settings['blog_featured_source'], 'latest'); ?>>
                            <?php esc_html_e('Latest Post', 'music-project-core'); ?>
                        </option>
                        <option value="manual" <?php selected($settings['blog_featured_source'], 'manual'); ?>>
                            <?php esc_html_e('Manually Selected Post', 'music-project-core'); ?>
                        </option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Choose whether the large featured card uses the latest post or a specific selected post.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-blog-featured-first-row mpc-blog-manual-featured-row">
                <th scope="row">
                    <label for="mpc_homepage_blog_featured_post_id">
                        <?php esc_html_e('Featured Post', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <?php
            $blog_posts_for_select = get_posts([
                'post_type' => 'post',
                'post_status' => ['publish', 'draft', 'private'],
                'posts_per_page' => 100,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            ?>

                    <select id="mpc_homepage_blog_featured_post_id" name="mpc_homepage_settings[blog_featured_post_id]">
                        <option value="0">
                            <?php esc_html_e('Select a post', 'music-project-core'); ?>
                        </option>

                        <?php foreach ($blog_posts_for_select as $blog_post_option) : ?>
                        <option value="<?php echo esc_attr($blog_post_option->ID); ?>"
                            <?php selected((int) $settings['blog_featured_post_id'], $blog_post_option->ID); ?>>
                            <?php echo esc_html(get_the_title($blog_post_option)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Useful for pinning a song deep-dive, studio dispatch, announcement, or evergreen article.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mpc-conditional-row mpc-blog-featured-first-row">
                <th scope="row">
                    <label for="mpc_homepage_blog_additional_posts">
                        <?php esc_html_e('Additional Posts', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input id="mpc_homepage_blog_additional_posts" type="number" min="0" max="6"
                        name="mpc_homepage_settings[blog_additional_posts]"
                        value="<?php echo esc_attr($settings['blog_additional_posts']); ?>">

                    <p class="description">
                        <?php esc_html_e('Number of smaller posts shown after the featured post.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Display Options', 'music-project-core'); ?>
                </th>
                <td>
                    <input type="hidden" name="mpc_homepage_settings[blog_show_images]" value="0">
                    <label>
                        <input type="checkbox" name="mpc_homepage_settings[blog_show_images]" value="1"
                            <?php checked($settings['blog_show_images'], 1); ?>>
                        <?php esc_html_e('Show featured images', 'music-project-core'); ?>
                    </label>

                    <br>

                    <input type="hidden" name="mpc_homepage_settings[blog_show_dates]" value="0">
                    <label>
                        <input type="checkbox" name="mpc_homepage_settings[blog_show_dates]" value="1"
                            <?php checked($settings['blog_show_dates'], 1); ?>>
                        <?php esc_html_e('Show dates', 'music-project-core'); ?>
                    </label>

                    <br>

                    <input type="hidden" name="mpc_homepage_settings[blog_show_excerpts]" value="0">
                    <label>
                        <input type="checkbox" name="mpc_homepage_settings[blog_show_excerpts]" value="1"
                            <?php checked($settings['blog_show_excerpts'], 1); ?>>
                        <?php esc_html_e('Show excerpts', 'music-project-core'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="blog_posts_per_page">
                        <?php esc_html_e('Number of Posts', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" id="blog_posts_per_page" name="mpc_homepage_settings[blog_posts_per_page]"
                        class="small-text" min="1" max="12"
                        value="<?php echo esc_attr($settings['blog_posts_per_page']); ?>">

                    <p class="description">
                        <?php esc_html_e('How many recent posts should appear on the homepage. Maximum 12.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="blog_read_more_text">
                        <?php esc_html_e('Post Link Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="blog_read_more_text" name="mpc_homepage_settings[blog_read_more_text]"
                        class="regular-text" value="<?php echo esc_attr($settings['blog_read_more_text']); ?>"
                        placeholder="<?php esc_attr_e('Read More', 'music-project-core'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="blog_view_all_text">
                        <?php esc_html_e('View All Button Text', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="blog_view_all_text" name="mpc_homepage_settings[blog_view_all_text]"
                        class="regular-text" value="<?php echo esc_attr($settings['blog_view_all_text']); ?>"
                        placeholder="<?php esc_attr_e('View All Posts', 'music-project-core'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="blog_view_all_url">
                        <?php esc_html_e('View All Button URL', 'music-project-core'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="blog_view_all_url" name="mpc_homepage_settings[blog_view_all_url]"
                        class="regular-text" value="<?php echo esc_attr($settings['blog_view_all_url']); ?>"
                        placeholder="/blog">

                    <p class="description">
                        <?php esc_html_e('Usually /blog or the URL of your posts page.', 'music-project-core'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php mpc_admin_panel_close(); ?>

        <div class="mpc-sticky-submit">
            <div class="mpc-sticky-submit__inner">
                <?php
                    submit_button(
                        __('Save Homepage Settings', 'music-project-core'),
                        'primary',
                        'submit',
                        false
                    );
                    ?>

                <span class="mpc-sticky-submit__status" aria-live="polite">
                    <?php esc_html_e('Unsaved changes', 'music-project-core'); ?>
                </span>
            </div>
        </div>
    </form>
</div>

<?php
}