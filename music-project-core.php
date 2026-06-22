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

require_once MPC_PATH . 'includes/settings-homepage.php';
require_once MPC_PATH . 'includes/settings-social-links.php';
require_once MPC_PATH . 'includes/settings-integrations.php';
require_once MPC_PATH . 'includes/shortcodes-bandsintown.php';
require_once MPC_PATH . 'includes/press-quotes.php';
require_once MPC_PATH . 'includes/settings-theme-style.php';
require_once MPC_PATH . 'includes/settings-footer.php';
require_once MPC_PATH . 'includes/settings-site-status.php';