<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              audiotilly.com
 * @since             1.0.0
 * @package           AudioTilly
 *
 * @wordpress-plugin
 * Plugin Name:       AudioTilly
 * Plugin URI:        https://wordpress.org/plugins/audio-tilly/
 * Description:       Your Website Can Now Speak!
 * Version:           1.1.0
 * Author:            BigEngage Inc.
 * Author URI:        https://audiotilly.com/
 * License:           GPL-3.0 ONLY
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       audiotilly.com
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    wp_die();
}

define('AUDIOTILLY_DIR', plugin_dir_path( __FILE__ ));
define('AUDIOTILLY_PLUGIN_URL', plugins_url(trailingslashit(basename(AUDIOTILLY_DIR))));
define('AUDIOTILLY_UPLOAD_DIR', wp_get_upload_dir()['basedir'] . '/audiotilly/');
define('AUDIOTILLY_API_HOST', 'https://api.audiotilly.com');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-audiotilly-activator.php
 */
function activate_audiotilly()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-audiotilly-activator.php';
    AudioTilly_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-audiotilly-deactivator.php
 */
function deactivate_audiotilly()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-audiotilly-deactivator.php';
    AudioTilly_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_audiotilly');
register_deactivation_hook(__FILE__, 'deactivate_audiotilly');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-audiotilly.php';
require plugin_dir_path(__FILE__) . 'includes/class-audiotilly-mp3file.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_audiotilly()
{
    $plugin = new AudioTilly();
    $plugin->run();
}

run_audiotilly();
