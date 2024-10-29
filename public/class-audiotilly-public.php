<?php

require_once __DIR__ . '/../admin/AudioTilly-Consts.php';

class AudioTilly_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param   string $plugin_name The name of the plugin.
     * @param   string $version The version of this plugin.
     * @since   1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * WordPress filter, responsible for adding public part of
     * the plugin (audio player & translate part)
     *
     * @param   string $content The content of the new post.
     * @return  string
     * @since   1.0.0
     */
    public function content_filter($content)
    {
        //Check if we're inside the main loop
        if (!is_single() && !in_the_loop() && !is_main_query()) {
            return $content;
        }
        $post_id = get_the_ID();
        $common = new AudioTilly_Common();
        $player_content = '';

        if ($this->is_audiotilly_enabled_for_post() && $common->is_audiotilly_enough_credits($post_id)) {
            $player_label = get_option('audio_tilly_player_label');
            // Create player area.
            if (is_singular()) {
                $player_content = $this->include_audio_player();
            }
        }

        if (get_option('audio_tilly_schema', true)) {
            $content .= $this->includeSchema();
        }

        // Put plugin content in the correct position.
        $selected_position = get_option('audio_tilly_position');
        switch ($selected_position) {
            case 'top':
                return $player_content . $content;
            case 'bottom':
                return $content . $player_content;
            default:
                return $content;
        }
    }

    /**
     * @return  bool
     * @since   1.0.0
     */
    private function is_audiotilly_enabled_for_post()
    {
        $add_to = get_option('audio_tilly_add_to');
        if (!is_array($add_to)) {
            return false;
        }
        return in_array(get_post_type(), $add_to);
    }

    /**
     * Get html markup for player injection to the page content
     *
     * @return  string
     * @since   1.0.0
     */
    private function include_audio_player()
    {
        if (!session_id()) {
            @session_start();
        }
        if (!isset($_SESSION['audiotilly_sess_id'])) {
            $_SESSION['audiotilly_sess_id'] = uniqid('', true);
        }

        $common = new AudioTilly_Common;

        $attrs = [
            'wp_url' => rtrim(get_site_url(), '/'),
            'post_id' => get_the_ID(),
            'src' => rtrim(get_site_url(), '/') . '/' . rest_get_url_prefix() . '/audiotilly/v1/audiotilly_' . get_the_ID() . '.mp3',
            'skin' => $common->get_player_skin(),
            'copyright' => (int)$common->is_poweredby_enabled(),
            'align' => get_option('audio_tilly_align', 'center'),
            'audiotilly_sess_id' => $_SESSION['audiotilly_sess_id'],
            '_dc' => time()
        ];

        return '<div style="clear:both"></div>
<div id="audiotilly-container"></div>
<script type="text/javascript">
/* <![CDATA[ */
var audiotilly_iframe = document.createElement("iframe");
audiotilly_iframe.className = "audiotilly-iframe ' . $attrs['skin'] . '";
audiotilly_iframe.src = "' . AUDIOTILLY_API_HOST . '/plugin/player?' . http_build_query($attrs) . '";
var audiotilly_element = document.getElementById("audiotilly-container");
if (audiotilly_element) {
    audiotilly_element.appendChild(audiotilly_iframe)
}
/* ]]> */
</script>';
    }

    /**
     * Inject Google Structured Data
     *
     * @return  string
     * @since   1.0.0
     */
    private function includeSchema()
    {
        $title = get_the_title();
        $url = home_url($_SERVER['REQUEST_URI']);
        $keywords = get_post_meta(get_the_ID(), 'keywords', true);
        $blogname = get_bloginfo('name');
        $published = get_the_date('m-d-Y');
        $list = [
            get_the_author_meta('first_name'),
            get_the_author_meta('last_name')
        ];
        $author = !empty($list) ? implode(' ', $list): '';
        $image = $this->getThumbnailUrl();

        $file = AUDIOTILLY_UPLOAD_DIR . 'audiotilly_' . get_the_ID() . '.mp3';
        if (file_exists($file)) {
            $filesize = intval(filesize($file) / 1024);
            $audio = new AudioTilly_MP3File($file);
            $duration = $audio->getDurationEstimate();
            $duration = AudioTilly_MP3File::formatTimeT0H0M0S($duration);
        } else {
            $filesize = 0;
            $duration = 'T0H0M0S';
        }

        return '<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "AudioObject",
    "schemaVersion": "http://schema.org/version/2.0/",
    "associatedArticle": {
        "datePublished": "' . esc_js($published) . '",
        "dateModified": "' . esc_js($published) . '",
        "headline": "' . esc_js($title) . '",
        "image": "' . esc_js($image) . '",
        "author": "' . esc_js($author) . '",
        "publisher": {
            "@type": "Organization",
            "name": "' . esc_js($blogname) . '",
            "logo": {
                "@type": "ImageObject",
                "url": "' . esc_url($this->getBlogLogo()) . '"
            }
        },
        "mainEntityOfPage": "' . esc_url($url) . '",
        "speakable": {
            "@type": "SpeakableSpecification",
            "cssSelector": [
                ".entry-title",
                ".entry-content"
            ]
        }
    },
    "contentSize": "' . esc_js($filesize) . ' kB",
    "contentUrl": "' . esc_url($url) . '",
    "description": "' . esc_js($title) . '",
    "duration": "' . esc_js($duration) . '",
    "playerType": "HTML5",
    "encodingFormat": "audio/mpeg",
    "productionCompany": "AudioTilly.com",
    "requiresSubscription": false,
    "name": "' . esc_js($title) . '",
    "abstract": "' . esc_js($title) . '",
    "accessMode": "auditory,textual",
    "audio": "AudioObject ",
    "author": "' . esc_js($author) . '",
    "contentRating": "MPAA G",
    "copyrightHolder": "' . esc_url($url) . '",
    "discussionUrl": "' . esc_url($url) . '",
    "editor": "AudioTilly.com",
    "creator": "AudioTilly.com",
    "headline": "' . esc_js($title) . '",
    "inLanguage": "English",
    "keywords": "' . esc_js($keywords) . '",
    "thumbnailUrl": "' . esc_js($image) . '",
    "image": "' . esc_js($image) . '"
}
</script>
';
    }

    /**
     * Get blog's logo
     *
     * @return string
     */
    private function getBlogLogo()
    {
        $logo = '';
        if (function_exists('get_custom_logo')) {
            $logo = get_custom_logo();
        }
        if (empty($logo)) {
            $logo = 'https://s.w.org/style/images/about/WordPress-logotype-standard.png';
        }
        return $logo;
    }

    /**
     * Get post's thumbnail
     *
     * @return string
     */
    private function getThumbnailUrl()
    {
        $image = get_the_post_thumbnail_url();
        if (!$image) {
            $image = 'https://s.w.org/style/images/about/WordPress-logotype-standard.png';
        }
        return $image;
    }

    /**
     * @param string $type
     * @return mixed
     */
    private function getPlayer($type)
    {
        return require_once AUDIOTILLY_DIR . 'players' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . 'player.php';
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/audiotilly-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/audiotilly-public.js', array('jquery'), $this->version, false);
    }
}
