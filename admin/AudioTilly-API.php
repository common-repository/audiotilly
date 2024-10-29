<?php

require_once __DIR__ . '/../admin/AudioTilly-Consts.php';

class AudioTilly_API
{
    public function enqueue_routes()
    {
//        register_rest_route('audiotilly/v1', '/posts/(?P<post_id>\d+)/upload-audio', [
//            'methods' => 'POST',
//            'callback' => array($this, 'audiotilly_upload_audio')
//        ]);
        register_rest_route('audiotilly/v1', '/audiotilly_(?P<post_id>\d+).mp3', [
            'methods' => 'GET',
            'callback' => array($this, 'audiotilly_listen_audio')
        ]);
        register_rest_route('audiotilly/v1', '/statistic', [
            'methods' => 'GET',
            'callback' => array($this, 'audiotilly_get_statistic')
        ]);
    }

//    public function audiotilly_upload_audio(WP_REST_Request $request)
//    {
//        $license_key = get_option(AUDIO_TILLY_LICENSE_KEY);
//        if ($request->get_header('key') != $license_key) {
//            return new WP_REST_Response(['success' => false, 'message' => 'no correct key'], 400);
//        }
//
//        if (empty($request->get_file_params()['audio'])) {
//            return new WP_REST_Response(['success' => false, 'message' => 'empty file field audio'], 400);
//        }
//
//        $audio = $request->get_file_params()['audio'];
//        if ($audio['error'] || $audio['type'] != 'audio/mpeg') {
//            return new WP_REST_Response(['success' => false, 'message' => $audio['error'] ?: 'no available audio type'], 400);
//        }
//
//        $post = get_post($request->get_url_params()['post_id']);
//        if (!$post) {
//            return new WP_REST_Response(['success' => false, 'message' => 'specified post_id not exists'], 400);
//        }
//
//        $file = AUDIOTILLY_UPLOAD_DIR . "audiotilly_$post->ID.mp3";
//
//        move_uploaded_file($audio['tmp_name'], $file);
//
//        $uploadId = wp_insert_attachment([
//            'guid' => $file,
//            'post_mime_type' => 'audio/mpeg',
//            'post_title' => $post->post_title,
//            'post_content' => '',
//            'post_status' => 'inherit'
//        ], $file);
//
//        require_once ABSPATH . 'wp-admin/includes/image.php';
//        require_once ABSPATH . 'wp-admin/includes/media.php';
//
//        wp_update_attachment_metadata($uploadId, wp_generate_attachment_metadata($uploadId, $file));
//
//        return new WP_REST_Response(['success' => true, 'message' => 'audio successfully saved']);
//    }

    /**
     * Implement handler for wp-json/audiotilly/v1/audiotilly_XXX.mp3 route
     * that is used by player
     *
     * @param   WP_REST_Request $request
     * @return  WP_Error|WP_REST_Response
     * @throws  Exception
     * @since   1.0.0
     */
    public function audiotilly_listen_audio(WP_REST_Request $request)
    {
        header('Access-Control-Allow-Origin: ' . AUDIOTILLY_API_HOST);

        $postId = $request->get_url_params()['post_id'];
        $post = get_post($postId);
        if (!$post) {
            return new WP_REST_Response(['success' => false, 'message' => 'specified post_id not exists'], 400);
        }

        $content = apply_filters('the_content', $post->post_content);
        $content = preg_replace('/(?:<div[^>]+id="audiotilly-container"[^>]*>((?:(?:(?!<div[^>]*>|<\/div>).)++|<div[^>]*>(?1)<\/div>)*)<\/div>)/usi', '', $content);
        $content = preg_replace('#<script[^>]+type="application/ld\+json"[^>]*>[^<]+</script>#usi', '', $content);
        $selected_voices = get_option(AUDIO_TILLY_VOICES, array_keys(AUDIO_TILLY_AVAILABLE_VOICES));
        $license_key = get_option(AUDIO_TILLY_LICENSE_KEY);

        $params = [
            'post_id' => $post->ID,
            'title' => trim(strip_tags($post->post_title, '<p>')),
            'content' => trim(strip_tags($content, '<p>')),
            'author' => $post->post_author,
            'published' => $post->post_date,
            'link' => get_page_link($post),
            'voice' => $selected_voices[random_int(0, count($selected_voices) - 1)]
        ];

        $body = wp_json_encode($params);

        $url = AUDIOTILLY_API_HOST . '/plugin/add-news';
        $result = wp_remote_post($url, [
            'timeout' => AUDIO_TILLY_LISTEN_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($body),
                'wp_url' => rtrim(get_site_url(), '/'),
                'key' => $license_key
            ],
            'body' => $body,
            'data_format' => 'body'
        ]);

        AudioTilly_Common::log(compact('url', 'params', 'result'));

        if ($result instanceof WP_Error) {
            return $result;
        }

        $json = json_decode($result['body'], true);
        if (empty($json['success'])) {
            return new WP_REST_Response($json, $result['response']['code']);
        }

        $headers = array_map(function ($value, $header) {
            $header = implode('-', array_map('ucfirst', explode('_', $header)));
            return "$header: " . implode(', ', $value);
        }, $request->get_headers(), array_keys($request->get_headers()));

        $getParams = ['key' => $license_key];
        if (get_option('audio_tilly_player_once_clip')) {
            $getParams['play_with_clips'] = $request->get_param('play_with_clips');
        } else {
            $getParams['play_with_clips'] = 1;
        }

        $this->readfile($json['url'] . '?' . http_build_query($getParams), $headers);
        wp_die();
    }

    private function readfile($file, $headers = [])
    {
        $result = wp_remote_get($file, compact('headers'));

        AudioTilly_Common::log(compact('file', 'headers', 'result'));

        if ($result instanceof WP_Error) {
            wp_die($result);
        }

        $resHeaders = [];
        foreach ($result['headers'] as $header => $value) {
            $header = implode('-', array_map('ucfirst', explode('-', $header)));
            $resHeaders[] = "$header: $value";
        }

        array_map('header', $resHeaders);
        http_response_code($result['response']['code']);

        echo $result['body'];
    }

    /**
     * Get statistics of usage for the current key
     *
     * @param   WP_REST_Request $request
     * @return  WP_Error|WP_REST_Response
     * @since   1.0.0
     */
    public function audiotilly_get_statistic(WP_REST_Request $request)
    {
        $license_key = get_option(AUDIO_TILLY_LICENSE_KEY);
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');

        $args = [
            'headers' => ['key' => $license_key],
            'body' => compact('start_date', 'end_date')
        ];

        $url = AUDIOTILLY_API_HOST . '/plugin/statistic';
        $result = wp_remote_get($url, $args);

        AudioTilly_Common::log(compact('url', 'args', 'result'));

        if ($result instanceof WP_Error) {
            return $result;
        }

        $json = json_decode($result['body'], true);
        if (empty($json['success'])) {
            return new WP_REST_Response($json, $result['response']['code']);
        }

        $response = new WP_REST_Response($json, $result['response']['code']);

        if ($response->data['statistic']) {
            global $wpdb;
            $posts = $wpdb->get_results('SELECT `ID` AS `post_id`, `post_title` AS `title` FROM `' . $wpdb->prefix . 'posts` WHERE `ID` IN(' .
                implode(',', array_unique(array_column($response->data['statistic'], 'post_id'))) . ');', ARRAY_A);

            foreach ($response->data['statistic_per_post'] as $key => $data) {
                $postKey = array_search($data['post_id'], array_column($posts, 'post_id'));
                if ($postKey !== false) {
                    $response->data['statistic_per_post'][$key]['title'] = $posts[$postKey]['title'];
                } else {
                    unset($response->data['statistic_per_post'][$key]);
                }
            }
        }

        update_option(AUDIO_TILLY_CONVERSIONS_LEFT, $response->data['conversions_left']);

        return $response;
    }
}
