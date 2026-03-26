<?php

/**
 * Plugin Name: Imitator
 * Plugin URI: https://github.com/subas-roy/imitator/
 * Description: Backup wp-content files and database from the WordPress admin.
 * Version: 2.0.0
 * Author: Subas Roy
 * Author URI: https://github.com/subas-roy/
 * Text Domain: imitator
 * License: GPLv2 or later
 */

defined('ABSPATH') || exit;

if (! defined('IMITATOR_VERSION')) {
    define('IMITATOR_VERSION', '2.0.0');
}

if (! defined('IMITATOR_FILE')) {
    define('IMITATOR_FILE', __FILE__);
}

if (! defined('IMITATOR_PATH')) {
    define('IMITATOR_PATH', plugin_dir_path(__FILE__));
}

if (! defined('IMITATOR_URL')) {
    define('IMITATOR_URL', plugin_dir_url(__FILE__));
}

require_once IMITATOR_PATH . 'includes/class-activator.php';
require_once IMITATOR_PATH . 'includes/class-plugin.php';

register_activation_hook(__FILE__, array('Imitator_Activator', 'activate'));

function imitator_run_plugin() {
    $plugin = new Imitator_Plugin();
    $plugin->run();
}

imitator_run_plugin();
