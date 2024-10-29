<?php

require_once __DIR__ . '/AudioTilly-Consts.php';

class AudioTilly_Common
{
    public function audiotilly_admin_danger()
    {
        if (get_option(AUDIO_TILLY_CONVERSIONS_LEFT) <= 0) {
            echo '<style type="text/css">
                #toplevel_page_audio_tilly {
                    background-color: #721a0d;
                }
            </style>';
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style('jquery-scroll-pane', plugin_dir_url(__FILE__) . 'vendor/jscrollpane/css/jquery.jscrollpane.min.css', array(), '2.2.1', 'all');
        wp_enqueue_style('jquery-ui-core');
        wp_enqueue_style('jquery-ui-progressbar');
        wp_enqueue_style('audio-tilly-datepicker-css', plugin_dir_url(__FILE__) . 'vendor/daterangepicker/css/daterangepicker.min.css');
        wp_enqueue_style('audio-tilly', plugin_dir_url(__FILE__) . 'css/audiotilly-admin.css', array(), '1.1.3', 'all');

        add_action('admin_head', [$this, 'audiotilly_admin_danger']);
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('jquery-mousewheel', plugin_dir_url(__FILE__) . 'vendor/jscrollpane/js/jquery.mousewheel.min.js', array('jquery'), null, false);
        wp_enqueue_script('jquery-scroll-pane', plugin_dir_url(__FILE__) . 'vendor/jscrollpane/js/jquery.jscrollpane.min.js', array('jquery'), '2.2.1', false);
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-progressbar');
        wp_enqueue_script('google-charts', 'https://www.gstatic.com/charts/loader.js');
        $nonce_array = array(
            'nonce' => wp_create_nonce('audiotillyajaxnonce'),
        );
        wp_localize_script('jquery', 'audiotillyajax', $nonce_array);

        wp_enqueue_script('moment');
        wp_enqueue_script('audio-tilly-daterangepicker-js', plugin_dir_url(__FILE__) . 'vendor/daterangepicker/js/daterangepicker.min.js', ['moment']);

        wp_enqueue_script('audio_tilly', plugin_dir_url(__FILE__) . 'js/audiotilly-admin.js', array('jquery'), '1.1.5', false);
    }

    public function get_source_language()
    {
        $value = get_option('audio_tilly_source_language', 'en');
        if (empty($value)) {
            $value = 'en';
        }

        return $value;
    }

    public function get_all_languages()
    {
        $supported_languages = [];

        foreach ($this->get_languages_array_audio_tilly() as $language_data) {
            $language_code = $language_data['code'];
            array_push($supported_languages, $language_code);
        }

        return $supported_languages;
    }

    public function get_language_name($provided_langauge_code)
    {
        if ($provided_langauge_code == LABEL_DEFAULT) {
            return LABEL_DEFAULT;
        }
        foreach ($this->get_languages_array_audio_tilly() as $language_data) {
            $language_code = $language_data['code'];
            $language_name = $language_data['name'];

            if ($language_code === $provided_langauge_code) {
                return $language_name;
            }
        }

        return "N/A";
    }

    public function get_languages_array_audio_tilly()
    {
        $result = $this->curl_get_audio_tilly(AUDIO_TILLY_LANGUAGES . $this->get_audio_tilly_license_key());

        if (!$result) {
            $this->show_error_notice("notice-error", "Can't get list of supported languages! please try again later or contact AudioTilly support: wp@audiotilly.com ");
            return [];
        }
        $tim_langs = $result;
        $languages = array();
        foreach ($tim_langs as $key => $lang) {
            $languages[] = ['code' => $key, 'name' => $lang['name']];
        }
        return $languages;
    }

    /**
     * Utility function which checks if checkbox for option input should be checked.
     *
     * @param   string $option Name of the option which should be checked.
     * @return  string
     * @since   1.0.0
     */
    public function checked_validator($option)
    {
        $option_value = get_option($option, 'on');
        if (empty($option_value)) {
            return '';
        } else {
            return ' checked ';
        }
    }

    /**
     * Get of create a new api key
     *
     * @return bool
     * @since  1.0.0
     */
    public function create_audiotilly_installkey()
    {
//        $data = $this->getPluginDetailsObject();
//        $data['details_event_type'] = 'activating';
        $args = [
            'body' => [
                'wp_url' => rtrim(get_site_url(), '/'),
                'rest_url_prefix' => rest_get_url_prefix()
            ]
        ];
        if ($this->get_audio_tilly_license_key()) {//reactivating plugin
//            $data['details_event_type'] = 'reactivating';
            $response = $this->curl_get_audio_tilly(AUDIO_TILLY_UPDATE_PLUGIN_DETAILS_URL, $args);
        } else {
            $response = $this->curl_get_audio_tilly(AUDIO_TILLY_KEYS_URL, $args);
        }

        if ($response) {
            update_option(AUDIO_TILLY_LICENSE_KEY, $response['key']);
            update_option(AUDIO_TILLY_LICENSE_ID, $response['id']);
            update_option(AUDIO_TILLY_CONVERSIONS_LEFT, $response['conversions_left']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get general information about the blog
     *
     * @return array
     * @since  1.0.0
     */
    public function getPluginDetailsObject()
    {
        global $wp_version;
        $plugin_version = $this->getPluginVersion();
        $php_version = phpversion();
        $all_plugins = $this->getAllPluginsInstalled();
        $installkey = $this->get_audio_tilly_license_key();
        $site_url = rtrim(get_site_url(), '/');
        $site_domain = parse_url($site_url, PHP_URL_HOST);
        $email = $this->getUserEmail();
        $data = array(
            'wp_version' => $wp_version,
            'plugin_version' => $plugin_version,
            'php_version' => $php_version,
            'all_plugins' => $all_plugins,
            'installkey' => $installkey,
            'site_domain' => $site_domain,
            'email' => $email,
            'details_event_type' => 'not_set'
        );
        return $data;
    }

    public function getAllPluginsInstalled()
    {
        if (is_admin()) {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins = get_plugins();
            $plugins_str = "";
            foreach ($all_plugins as $plugin) {
                $plugins_str .= $plugin['Name'] . ',';
            }
            $plugins_str = preg_replace('/\s+/', '', $plugins_str);//remove white spaces

            return $plugins_str;
        }
        return "unknown";
    }

    /**
     * Get current plugin version
     *
     * @return string
     */
    public function getPluginVersion()
    {
        return '1.0.0';
    }

    /**
     * Get current api key
     *
     * @return false|mixed|void
     */
    public function get_audio_tilly_license_key()
    {
        return get_option(AUDIO_TILLY_LICENSE_KEY);
    }

    public function show_error_notice($type, $message)
    {
        add_action('admin_notices',
            function () use ($type, $message) {
                ?>
                <div class="notice  <?php
                echo $type ?>  is-dismissible">
                    <p><?php
                        _e($message, 'audio_tilly'); ?></p>
                </div>

                <?php
            });
    }

    public function clean_text($post_id, $with_title, $only_title, $with_excerpt = false)
    {
        $article_text = '';

        // Depending on the plugin configurations, post's title will be added to the audio.
        if ($with_title) {
            $article_text = get_the_title($post_id) . '. ';
        }


        // Depending on the plugin configurations, post's excerpt will be added to the audio.

        if ($with_excerpt) {
            $my_excerpt = apply_filters('the_excerpt', get_post_field('post_excerpt', $post_id));
            $article_text = $article_text . $my_excerpt . '. ';
        }

        $article_text = $article_text . get_post_field('post_content', $post_id);
        $article_text = apply_filters('audio_tilly_content', $article_text);

        if ($only_title) {
            $article_text = get_the_title($post_id);
        }

        return $this->cleanTextByText($article_text);
    }

    public function get_clean_text_by_saved_text($post_id, $with_title, $only_title, $with_excerpt = false)
    {
        $article_text = '';

        // Depending on the plugin configurations, post's title will be added to the audio.
        if ($with_title) {
            $article_text = get_post_meta($post_id, AUDIO_TILLY_SAVED_TITLE, true) . '. ';
        }


        // Depending on the plugin configurations, post's excerpt will be added to the audio.

        if ($with_excerpt) {
            $my_excerpt = get_post_meta($post_id, AUDIO_TILLY_SAVED_EXCERPT, true);
            if (!empty($my_excerpt)) {
                $article_text = $article_text . $my_excerpt . '. ';
            }
        }

        $article_text = $article_text . get_post_meta($post_id, AUDIO_TILLY_SAVED_BODY, true);
        $article_text = apply_filters('audio_tilly_content', $article_text);

        if ($only_title) {
            $article_text = get_the_title($post_id);
        }

        return $this->cleanTextByText($article_text);
    }

    public function cleanTextByText($clean_text)
    {
        $clean_text = str_replace('&nbsp;', ' ', $clean_text);
        $clean_text = do_shortcode($clean_text);

        $clean_text = $this->skip_tags($clean_text);

        // Creating text description for images
        $clean_text = $this->replace_images($clean_text);
        $clean_text = strip_tags($clean_text, '<break>');
        $clean_text = esc_html($clean_text);
        $clean_text = str_replace('&nbsp;', ' ', $clean_text);
        $clean_text = preg_replace("/https:\/\/([^\s]+)/", "", $clean_text);
        $clean_text_temp = '';
        $paragraphs = explode("\n", $clean_text);
        foreach ($paragraphs as $paragraph) {
            $paragraph_size = strlen(trim($paragraph));
            if ($paragraph_size > 0) {
                $clean_text_temp = $clean_text_temp . "\n " . $paragraph;
            }
        }

        $clean_text = $clean_text_temp;
        $clean_text = html_entity_decode($clean_text, ENT_QUOTES, 'UTF-8');
        $clean_text = str_replace('&', ' and ', $clean_text);
        $clean_text = str_replace('<', ' ', $clean_text);
        $clean_text = str_replace('>', ' ', $clean_text);

        //a.B to a. B
        $pattern = '/([a-z])\.([A-Z])/';
        $replacement = '$1. $2';
        $clean_text = preg_replace($pattern, $replacement, $clean_text);

        return $clean_text;
    }

    public function getExcerptByPost($post_id)
    {
        return apply_filters('the_excerpt', get_post_field('post_excerpt', $post_id));
    }

    public function getTitleByPost($post_id)
    {
        return get_the_title($post_id);
    }

    private function skip_tags($text)
    {

        $skip_tags_array = $this->get_skiptags_array();

        foreach ($skip_tags_array as $value) {
            $text = preg_replace('/<' . $value . '>(\s*?)(.*?)(\s*?)<\/' . $value . '>/', '', $text);
        }

        return $text;
    }

    public function get_skiptags_array()
    {
        $array = get_option('audio_tilly_skip_tags');
        return explode(' ', $array);
    }

    private function replace_images($clean_text)
    {

        return preg_replace('/<img.*?alt="(.*?)"[^\>]+>/', '$1.', $clean_text);
    }

    /**
     * Make a GET request to the AudioTilly API.
     *
     * @param   string $url
     * @param   array $args
     * @return  false|mixed
     * @since   1.0.0
     */
    public function curl_get_audio_tilly($url, $args = [])
    {
        $response = wp_remote_get($url, $args);

        AudioTilly_Common::log(compact('url', 'args', 'response'));

        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code != 200) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    /**
     * Make a POST request to the AudioTilly API.
     *
     * @param   array $postData
     * @param   string $url
     * @return  false|mixed
     * @since   1.0.0
     */
    public function curl_post_audio_tilly($postData, $url)
    {
        $args = array(
            'body' => $postData,
            'timeout' => 15,
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'cookies' => array()
        );

        $response = wp_remote_post($url, $args);

        AudioTilly_Common::log(compact('url', 'args', 'response'));

        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code != 200) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function get_post_source_language($post_id)
    {
        $value = get_post_meta($post_id, 'audio_tilly_source_language', true);

        if (empty($value)) {
            $value = $this->get_source_language();
        }

        return $value;
    }

    public function is_audiotilly_enough_credits($post_id)
    {
        $clean_text = $this->clean_text($post_id, $this->is_title_adder_enabled(), false, $this->is_excerpt_adder_enabled());
        $installkey = $this->get_audio_tilly_license_key();
        // The data to send to the API
        $postData = array(
            'text' => $clean_text,
            'license_key' => $installkey,
        );
//        $responseData = $this->curl_post_audio_tilly($postData, AUDIO_TILLY_IS_ENOUGH_CREDITS_URL);
        return true;
    }

    public function is_excerpt_adder_enabled()
    {
        return (bool)get_option('audio_tilly_add_post_excerpt', 'on');
    }

    public function is_title_adder_enabled()
    {
        return (bool)get_option('audio_tilly_add_post_title', 'on');
    }

    public function is_poweredby_enabled()
    {
        return (bool)get_option('audio_tilly_poweredby', 'on');
    }

    public function is_audio_tilly_enabled_for_new_posts()
    {
        return 'AudioTilly enabled' === get_option('audio_tilly_defconf');
    }

    /**
     * Get blog admin's email address
     *
     * @return  string
     * @since   1.0.0
     */
    private function getUserEmail()
    {
        global $current_user;
        wp_get_current_user();
        return $current_user->user_email;
    }

    public function get_plugin_settings()
    {
        return array(
            'source_language' => $this->get_source_language(),
            'powered_by' => $this->is_poweredby_enabled(),
            'player_position' => get_option('audio_tilly_position'),
            'player_align' => get_option('audio_tilly_align', 'center'),
            'player_label' => get_option('audio_tilly_player_label'),
            'new_post_default' => get_option('audio_tilly_defconf'),
            'add_post_title_to_audio' => $this->is_title_adder_enabled(),
            'add_post_excerpt_to_audio' => $this->is_excerpt_adder_enabled(),
            'audio_tilly_skip_tags' => get_option('audio_tilly_skip_tags'),
            'installkey' => $this->get_audio_tilly_license_key()
        );
    }

    /**
     * Get player's skin
     *
     * @return  string
     * @since   1.0.0
     */
    public function get_player_skin()
    {
        return get_option(AUDIO_TILLY_PLAYER_SKIN, 'green');
    }

    /**
     * Log debugging info to the error log.
     *
     * Enabled when WP_DEBUG_LOG is enabled (and WP_DEBUG, since according to
     * core, "WP_DEBUG_DISPLAY and WP_DEBUG_LOG perform no function unless
     * WP_DEBUG is true), but can be disabled via the audiotilly_debug_log filter.
     *
     * @param   mixed $audiotilly_debug The data to log.
     * @since   1.0.0
     */
    public static function log($audiotilly_debug)
    {
        if (apply_filters('audiotilly_debug_log', defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('AUDIOTILLY_DEBUG') && AUDIOTILLY_DEBUG)) {
            error_log(print_r(compact('audiotilly_debug'), true));
        }
    }
}
