<?php
/**
 * Music Project Core uninstall policy.
 *
 * Music Project Core intentionally preserves its settings, migration state,
 * and Quotes / Testimonials when the plugin is deleted.
 *
 * This prevents accidental data loss and allows the plugin to be reinstalled
 * without requiring the site owner to rebuild its configuration or content.
 *
 * A future explicitly opt-in data-removal tool may provide complete cleanup,
 * but uninstalling the plugin itself is non-destructive.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/*
 * Deliberately perform no database cleanup.
 *
 * Preserved data includes:
 *
 * - mpc_homepage_settings
 * - mpc_theme_style_settings
 * - mpc_integrations_settings
 * - mpc_footer_settings
 * - mpc_social_links_settings
 * - mpc_site_status_settings
 * - mpc_schema_versions
 * - mpc_press_quote posts and their post metadata
 */