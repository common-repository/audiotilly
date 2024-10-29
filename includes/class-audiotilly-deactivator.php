<?php
/**
 * Fired during plugin deactivation
 *
 * @link       audiotilly.com
 * @since      1.0.0
 *
 * @package    AudioTilly
 * @subpackage AudioTilly/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    AudioTilly
 * @subpackage AudioTilly/includes
 * @author     BigEngage Inc.
 */
class AudioTilly_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {

        // Flush the permalinks.
        flush_rewrite_rules();

        delete_option(AUDIO_TILLY_FIRST_SAVE);
    }

}
