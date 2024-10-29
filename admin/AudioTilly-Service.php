<?php

class AudioTilly_Service
{
    //https://codex.wordpress.org/Plugin_API/Action_Reference/upgrader_process_complete
    function upgrade_complete($upgrader_object, $options)
    {
        $current_plugin_path_name = plugin_basename(__FILE__);

        if ($options['action'] == 'update' && $options['type'] == 'plugin') {
            foreach ($options['plugins'] as $each_plugin) {
                if ($each_plugin == $current_plugin_path_name) {
                    $common = new AudioTilly_Common();
                    $data = $common->getPluginDetailsObject();
                    $data['details_event_type'] = 'upgrade';
                    $common->curl_post_audio_tilly($data, AUDIO_TILLY_UPDATE_PLUGIN_DETAILS_URL);
                }
            }
        }
    }
}
