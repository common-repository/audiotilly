<?php
/**
 * Fired during plugin activation
 *
 * @link       audiotilly.com
 * @since      1.0.0
 *
 * @package    AudioTilly
 * @subpackage AudioTilly/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AudioTilly
 * @subpackage AudioTilly/includes
 * @author     BigEngage Inc.
 */

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/AudioTilly-Common.php';

class AudioTilly_Activator
{

    /**
     * Initial configuration of the plugin.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        // Flush the permalinks.
        flush_rewrite_rules();
        $common = new AudioTilly_Common();
        $active = $common->create_audiotilly_installkey();

        if (!$active) {
            wp_die("Can't connect to AudioTilly! Please contact AudioTilly Support: support@audiotilly.com");
        }
    }

}
