<?php
/**
 * Plugin Name: Music Project Core
 * Description: Core admin settings and reusable content tools for music project websites.
 * Version: 0.1.0
 * Author: Your Name
 * Text Domain: music-project-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MPC_VERSION', '0.1.0');
define('MPC_PATH', plugin_dir_path(__FILE__));
define('MPC_URL', plugin_dir_url(__FILE__));
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
require_once MPC_PATH . 'includes/settings-integrations.php';
require_once MPC_PATH . 'includes/shortcodes-bandsintown.php';
require_once MPC_PATH . 'includes/press-quotes.php';
require_once MPC_PATH . 'includes/settings-theme-style.php';
require_once MPC_PATH . 'includes/settings-footer.php';
require_once MPC_PATH . 'includes/settings-site-status.php';