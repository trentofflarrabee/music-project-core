<?php
/**
 * Plugin Name: Music Project Core
 * Plugin URI: https://github.com/trentofflarrabee/music-project-core
 * Description: Core admin settings and reusable content tools for music project websites.
 * Version: 1.4.0
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Author: Trent Larrabee
 * License: GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/trentofflarrabee/music-project-core
 * Text Domain: music-project-core
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MPC_VERSION', '1.4.0');
define('MPC_PATH', plugin_dir_path(__FILE__));
define('MPC_URL', plugin_dir_url(__FILE__));

/**
 * Load bundled plugin translations.
 *
 * WordPress language-pack translations continue to work normally. This also
 * supports translation files placed in the plugin's local languages folder.
 */
function mpc_load_plugin_textdomain() {
    load_plugin_textdomain(
        'music-project-core',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('init', 'mpc_load_plugin_textdomain');

/**
 * Get translated strings used by the shared admin script.
 *
 * @return array
 */
function mpc_get_admin_script_strings() {
    return [
        'chooseFile'   => __('Choose File', 'music-project-core'),
        'useThisFile'  => __('Use this file', 'music-project-core'),
        'selectedFile' => __('Selected file:', 'music-project-core'),
        'replaceFile'  => __('Replace File', 'music-project-core'),

        /* translators: 1: section name, 2: new position, 3: total sections. */
        'sectionMoved' => __(
            '%1$s moved to position %2$d of %3$d.',
            'music-project-core'
        ),

        'service'      => __('Service', 'music-project-core'),
        'serviceAdded' => __('Service item added.', 'music-project-core'),

        /* translators: %s is a service name. */
        'serviceRemoved' => __('%s removed.', 'music-project-core'),

        /* translators: 1: service name, 2: new position, 3: total services. */
        'serviceMoved' => __(
            '%1$s moved to position %2$d of %3$d.',
            'music-project-core'
        ),

        /* translators: %d is the maximum number of services. */
        'serviceLimit' => __(
            'You can add up to %d services.',
            'music-project-core'
        ),

        /* translators: %s is a service name. */
        'dragService' => __(
            'Drag %s to reorder',
            'music-project-core'
        ),

        /* translators: %s is a service name. */
        'serviceControls' => __(
            'Reorder or remove %s',
            'music-project-core'
        ),

        /* translators: %s is a service name. */
        'removeService' => __(
            'Remove %s',
            'music-project-core'
        ),
    ];
}

/**
 * Attach translated strings to the shared admin script.
 *
 * Call this after the mpc-admin script has been enqueued.
 */
function mpc_localize_admin_script() {
    if (!wp_script_is('mpc-admin', 'enqueued')) {
        return;
    }

    wp_localize_script(
        'mpc-admin',
        'mpcAdminI18n',
        mpc_get_admin_script_strings()
    );
}
/**
 * Get a cache-busting version for a plugin asset.
 *
 * @param string $relative_path Path relative to the plugin directory.
 * @return string
 */
function mpc_get_asset_version($relative_path = '') {
    $relative_path = ltrim((string) $relative_path, '/');

    if ($relative_path !== '') {
        $absolute_path = MPC_PATH . $relative_path;

        if (file_exists($absolute_path)) {
            $modified_time = filemtime($absolute_path);

            if ($modified_time) {
                return (string) $modified_time;
            }
        }
    }

    return MPC_VERSION;
}

require_once MPC_PATH . 'includes/settings-homepage.php';
require_once MPC_PATH . 'includes/settings-social-links.php';
require_once MPC_PATH . 'includes/settings-link-hub.php';
require_once MPC_PATH . 'includes/page-title-presentation.php';
require_once MPC_PATH . 'includes/settings-integrations.php';
require_once MPC_PATH . 'includes/shortcodes-bandsintown.php';
require_once MPC_PATH . 'includes/press-quotes.php';
require_once MPC_PATH . 'includes/settings-theme-style.php';
require_once MPC_PATH . 'includes/settings-footer.php';
require_once MPC_PATH . 'includes/settings-site-status.php';
require_once MPC_PATH . 'includes/migrations.php';


/**
 * Run outstanding schema migrations when Core is activated.
 *
 * Normal plugin updates continue to use the admin_init migration check.
 *
 * @param bool $network_wide Whether Core is network-activated.
 * @return void
 */
function mpc_activate_plugin(
    $network_wide = false
) {
    /*
     * Core currently maintains per-site settings. Other sites in a
     * multisite network will run the normal migration check when their
     * administrators enter WordPress admin.
     */
    unset($network_wide);

    mpc_run_schema_migrations(true);
}

register_activation_hook(
    __FILE__,
    'mpc_activate_plugin'
);