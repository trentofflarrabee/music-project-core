<?php
/**
 * Music Project Core settings migrations.
 *
 * Schema versions are stored separately from the settings arrays so normal
 * Settings API sanitizers cannot remove or overwrite migration state.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the current settings schema versions supported by this release.
 *
 * Increase only the affected module when its stored option shape changes.
 *
 * @return int[]
 */
function mpc_get_current_schema_versions() {
    return [
        'homepage'   => 1,
        'theme_style' => 1,
    ];
}

/**
 * Get normalized stored schema versions.
 *
 * Unknown module keys are retained so future modules can be introduced
 * without this reader discarding their migration state.
 *
 * @return int[]
 */
function mpc_get_stored_schema_versions() {
    $stored = get_option('mpc_schema_versions', []);

    if (!is_array($stored)) {
        return [];
    }

    $versions = [];

    foreach ($stored as $module => $version) {
        $module = sanitize_key((string) $module);

        if ($module === '') {
            continue;
        }

        $versions[$module] = absint($version);
    }

    return $versions;
}

/**
 * Determine whether a WordPress option currently exists.
 *
 * This avoids creating full settings rows on a brand-new installation when
 * no legacy data needs to be migrated.
 *
 * @param string $option_name Option name.
 * @return bool
 */
function mpc_option_exists($option_name) {
    $missing = new stdClass();
    $value = get_option($option_name, $missing);

    return $value !== $missing;
}

/**
 * Migrate Homepage settings to schema version 1.
 *
 * Version 1 establishes section_visibility as the canonical visibility map
 * while preserving the older per-section enable keys as compatibility
 * mirrors.
 *
 * Existing unknown visibility keys are preserved rather than discarded.
 *
 * @return void
 */
function mpc_migrate_homepage_schema_1() {
    $homepage_exists = mpc_option_exists(
        'mpc_homepage_settings'
    );

    $integrations_exist = mpc_option_exists(
        'mpc_integrations_settings'
    );

    /*
     * A new installation has nothing to migrate. Defaults and sanitizers
     * already create data in the current shape when settings are first saved.
     */
    if (
        !$homepage_exists
        && !$integrations_exist
    ) {
        return;
    }

    $original_homepage = $homepage_exists
        ? get_option(
            'mpc_homepage_settings',
            []
        )
        : [];

    $original_integrations = $integrations_exist
        ? get_option(
            'mpc_integrations_settings',
            []
        )
        : [];

    $homepage = is_array($original_homepage)
        ? $original_homepage
        : [];

    $integrations = is_array(
        $original_integrations
    )
        ? $original_integrations
        : [];

    $default_visibility = function_exists(
        'mpc_get_homepage_section_default_visibility'
    )
        ? mpc_get_homepage_section_default_visibility()
        : [
            'hero'             => 1,
            'featured-content' => 1,
            'services'         => 1,
            'quotes'           => 1,
            'shows'            => 1,
            'blog'             => 1,
            'newsletter'       => 1,
        ];

    /**
     * Normalize legacy or canonical visibility values.
     *
     * @param mixed $value Stored visibility value.
     * @return int
     */
    $normalize_visibility = static function ($value) {
        if (
            function_exists(
                'mpc_normalize_homepage_visibility_value'
            )
        ) {
            return
                mpc_normalize_homepage_visibility_value(
                    $value
                );
        }

        if (!is_scalar($value)) {
            return 0;
        }

        $value = strtolower(
            trim(
                (string) $value
            )
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

    $saved_visibility = (
        isset($homepage['section_visibility'])
        && is_array(
            $homepage['section_visibility']
        )
    )
        ? $homepage['section_visibility']
        : [];

    /*
     * Begin with every existing key so migration does not silently discard
     * data added by an older or customized installation.
     */
    $visibility = [];

    foreach (
        $saved_visibility
        as $section => $visible
    ) {
        $section = sanitize_key(
            (string) $section
        );

        if ($section === '') {
            continue;
        }

        $visibility[$section] =
            $normalize_visibility($visible);
    }

    $homepage_legacy_map = [
        'hero'             => 'hero_enabled',
        'featured-content' => 'featured_enabled',
        'blog'             => 'blog_enabled',
    ];

    $integration_legacy_map = [
        'shows'      => 'shows_enabled',
        'newsletter' => 'newsletter_enabled',
    ];

    foreach (
        $default_visibility
        as $section => $default
    ) {
        /*
         * An existing canonical value always takes precedence.
         */
        if (
            array_key_exists(
                $section,
                $saved_visibility
            )
        ) {
            $visibility[$section] =
                $normalize_visibility(
                    $saved_visibility[$section]
                );

            continue;
        }

        /*
         * Older Homepage enable fields are the next compatibility source.
         */
        if (
            isset($homepage_legacy_map[$section])
            && array_key_exists(
                $homepage_legacy_map[$section],
                $homepage
            )
        ) {
            $visibility[$section] =
                $normalize_visibility(
                    $homepage[
                        $homepage_legacy_map[$section]
                    ]
                );

            continue;
        }

        /*
         * Shows and Newsletter historically stored visibility with
         * Integration settings.
         */
        if (
            isset(
                $integration_legacy_map[$section]
            )
            && array_key_exists(
                $integration_legacy_map[$section],
                $integrations
            )
        ) {
            $visibility[$section] =
                $normalize_visibility(
                    $integrations[
                        $integration_legacy_map[
                            $section
                        ]
                    ]
                );

            continue;
        }

        $visibility[$section] =
            $normalize_visibility($default);
    }

    $homepage['section_visibility'] =
        $visibility;

    /*
     * Preserve the old keys as mirrors for older theme versions and custom
     * integrations that still read them directly.
     */
    $homepage['hero_enabled'] =
        $normalize_visibility(
            $visibility['hero']
        );

    $homepage['featured_enabled'] =
        $normalize_visibility(
            $visibility['featured-content']
        );

    $homepage['blog_enabled'] =
        $normalize_visibility(
            $visibility['blog']
        );

    $integrations['shows_enabled'] =
        $normalize_visibility(
            $visibility['shows']
        );

    $integrations['newsletter_enabled'] =
        $normalize_visibility(
            $visibility['newsletter']
        );

    /*
     * Establish an order only when one has never been stored. Existing order
     * data is left untouched and remains normalized by its normal reader.
     */
    if (
        empty($homepage['section_order'])
        && function_exists(
            'mpc_get_homepage_section_default_order'
        )
    ) {
        $homepage['section_order'] = implode(
            ',',
            mpc_get_homepage_section_default_order()
        );
    }

    if ($homepage !== $original_homepage) {
        update_option(
            'mpc_homepage_settings',
            $homepage
        );
    }

    if (
        $integrations
        !== $original_integrations
    ) {
        update_option(
            'mpc_integrations_settings',
            $integrations
        );
    }
}

/**
 * Migrate Theme Style settings to schema version 1.
 *
 * Version 1 establishes a complete saved settings shape without deleting
 * unknown legacy values. Typography-role fields are validated only when
 * they exist in the current defaults.
 *
 * @return void
 */
function mpc_migrate_theme_style_schema_1() {
    if (!mpc_option_exists('mpc_theme_style_settings')) {
        return;
    }

    $original = get_option(
        'mpc_theme_style_settings',
        []
    );

    $settings = is_array($original)
        ? $original
        : [];

    $defaults = function_exists('mpc_get_theme_style_defaults')
        ? mpc_get_theme_style_defaults()
        : [];

    /*
     * Saved values take precedence. Missing current values are persisted,
     * and unknown older values remain in the array.
     */
    $settings = wp_parse_args(
        $settings,
        $defaults
    );

    $allowed_font_slots = [
        'body',
        'heading',
        'accent',
        'quote',
    ];

    $font_role_keys = [
        'font_role_body',
        'font_role_heading',
        'font_role_hero_heading',
        'font_role_nav',
        'font_role_button',
        'font_role_accent',
        'font_role_quote',
    ];

    foreach ($font_role_keys as $key) {
        /*
         * This keeps the migration compatible with local branches that do
         * not yet contain the Typography role-assignment settings.
         */
        if (!array_key_exists($key, $defaults)) {
            continue;
        }

        $value = isset($settings[$key])
            ? sanitize_key((string) $settings[$key])
            : '';

        $settings[$key] = in_array(
            $value,
            $allowed_font_slots,
            true
        )
            ? $value
            : $defaults[$key];
    }

    if ($settings !== $original) {
        update_option(
            'mpc_theme_style_settings',
            $settings
        );
    }
}

/**
 * Run outstanding Music Project Core settings migrations.
 *
 * Migrations run only for an administrator in the normal WordPress admin.
 * Each migration is idempotent, so an interrupted request can safely retry.
 *
 * @return void
 */
function mpc_run_schema_migrations(
    $force = false
) {
    if (!$force) {
        if (!is_admin()) {
            return;
        }

        if (
            defined('DOING_AJAX')
            && DOING_AJAX
        ) {
            return;
        }

        if (
            !current_user_can(
                'manage_options'
            )
        ) {
            return;
        }
    }

    $current = mpc_get_current_schema_versions();

    $stored = mpc_get_stored_schema_versions();
    $changed = false;

    $homepage_version = isset($stored['homepage'])
        ? absint($stored['homepage'])
        : 0;

    if (
        $homepage_version
        < $current['homepage']
    ) {
        mpc_migrate_homepage_schema_1();

        $stored['homepage'] = 1;
        $changed = true;
    }

    $theme_style_version = isset($stored['theme_style'])
        ? absint($stored['theme_style'])
        : 0;

    if (
        $theme_style_version
        < $current['theme_style']
    ) {
        mpc_migrate_theme_style_schema_1();

        $stored['theme_style'] = 1;
        $changed = true;
    }

    if ($changed) {
        /*
         * Migration state is not needed on normal frontend requests, so the
         * option should not be autoloaded.
         */
        update_option(
            'mpc_schema_versions',
            $stored,
            false
        );
    }
}
add_action(
    'admin_init',
    'mpc_run_schema_migrations',
    1
);