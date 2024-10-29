<?php
/**
 * Class responsible for providing GUI for general configuration of the plugin
 *
 * @link       audiotilly.com
 * @since      1.0.0
 *
 * @package    AudioTilly
 * @subpackage AudioTilly/admin
 */

require_once __DIR__ . '/AudioTilly-Consts.php';

class AudioTilly_GeneralConfiguration
{
    /** @var AudioTilly_Common $common */
    private $common;

    public function audio_tilly_add_menu()
    {
        $this->plugin_screen_hook_suffix = add_menu_page(__('AudioTilly', 'audio-tilly'), __('AudioTilly', 'audio-tilly'), 'manage_options', 'audio_tilly', array(
            $this,
            'audiotilly_gui'
        ), 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAIAAAAC64paAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAAB3RJTUUH5AMPCysxdTmLAAAAAtFJREFUOMtlVE2LXFUQPafeff2VmZ5Jd8ZMhPEjJuBkYcCAIIqiGBQUzEYiLt37Z/wBbrIWFEJcSILMIkaMRkGiC51FyGh6kunpz5l+/d6r4+J2tzNYXLjFvXWqzi3uKX525RejCJEwihShmUMYoqNjMfOAINEBAgY5SMAABwwA5KABOH5CAgKB4CIBI1wkFJEQXKDBICfMaAYQFA0C4ASFIDEmjjVLRwhc36hunK03WwHAsDvd2R51H01K98TgmkWC88ozpHTqdOW1d9ur7bTzcNJ9nBnReqp6/uJKtzO5e3N3uJ+ZMbKjFrSBUnr6mfp7H61t/zG+9dXuwbAgRMpMzdXw8hvtyx9v3PpyZ3/3EDav56LE0tlYTt++svbrD8OtG93R0MH5W2mDXrl1vbN9f/T6B2cqtRAhAk2ii+586ZXlfjf/+fZApEDX7EoiQIk/be0Vuc5fXC1LuOiixS2tJs+/2Lj3/agouMD48TXN8NuP/ec2ly0kczDozqWVYMZHO3lIjWZyumbp3ekizZI06TycVGpJ7URwp8QgUUJaSULF1s5U3vnw5P1747tb/WqNi481zcpLb7Ze2Gzc+fZJqFiSJq4CsdsQSuez52rvXz117kK9s5Ovb9RefatJAyF33bm53z5d3bzUbJ5MV1pp5IJFZQkhsLGUuEPC3uOi83deb5DAwbjo7RUS5KgvJZbMOiIhAIi9ASFEY56jLOP/RVEgLxZXACEh8g0ASAx65V+/T0KFJECWJWsnkpVWIDWdelmSIIHx0P95kI2GLtAX4PHIv/j8ydp6uPppu98rswzffTMIiUi5Ky/Q75UPtidfX+t0d6fZocwIgZ9c/jPSkUBiadmKXNPMjwrbqFrd0hQHo8IgMxEgFf57CwFgOHAiShYgXFHePDzQhDJShAuMksRxiygBOqJzm/UOrpgzDob/gY9aHDJGLZS3GClR//8CrkC4hf3CLjAAAAAldEVYdGRhdGU6Y3JlYXRlADIwMjAtMDMtMTVUMTE6NDM6NDktMDQ6MDABqzkfAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDIwLTAzLTE1VDExOjQzOjQ5LTA0OjAwcPaBowAAAABJRU5ErkJggg==');
        $this->plugin_screen_hook_suffix = add_submenu_page('audio_tilly', 'General', 'General', 'manage_options', 'audio_tilly', array(
            $this,
            'audiotilly_gui'
        ));
    }

    public function audiotilly_settings_link($links)
    {
        $url = esc_url(add_query_arg('page', 'audio_tilly', get_admin_url() . 'admin.php'));
        $settings_link = '<a href="' . $url . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function audiotilly_gui()
    {
        ?>
        <div class="wrap audiotilly-wrapper">
            <div id="icon-options-general" class="icon32"></div>
            <h1 class="audiotilly-h1">AudioTilly</h1>
            <?php if (get_option(AUDIO_TILLY_CONVERSIONS_LEFT) <= 0): ?>
                <div class="alert alert-danger">You've reached your account limit. We have converted <span class="total-converted">5</span> articles this month. To convert more, please upgrade your account.</div>
            <?php endif; ?>
            <div class="tabs">
                <div class="tab dashboard-tab">
                    <input type="radio" id="dashboard" class="tab" name="tab-group" <?php echo get_option(AUDIO_TILLY_FIRST_SAVE) ? 'checked' : ''; ?>>
                    <label for="dashboard" class="tab-title">Dashboard</label>
                    <section class="tab-content">
                        <div class="dashboard">
                            <div class="statistic">
                                <input type="hidden" id="AUDIOTILLY_URL" value="<?php echo rtrim(get_site_url(), '/'); ?>">
                                <input type="hidden" id="AUDIOTILLY_API_URL" value="<?php echo rtrim(get_site_url(), '/') . '/' . rest_get_url_prefix() . '/audiotilly/v1/'; ?>">
                                <input type="hidden" id="ROOT_URL" value="<?php echo AUDIOTILLY_API_HOST; ?>">

                                <div class="form-group">
                                    <label for="report-interval">Report Dates:</label>
                                    <input type="text" class="form-control datepicker" id="report-interval" value="<?php echo date('m/01/y') . ' - ' . date('m/d/y'); ?>">
                                </div>

                                <div class="form-group" id="audiotilly-statistic-chart-container"></div>

                                <div class="form-group">
                                    <div class="top-converted-articles">
                                        <div class="caption">
                                            <div>Content converted to audio this month:</div>
                                            <div class="refresh-posts-statistic"><img src="<?php echo AUDIOTILLY_PLUGIN_URL; ?>admin/assets/loading.png"></div>
                                        </div>
                                        <div class="info grey-text">
                                            <div><span class="total-converted">0</span> converted, <span id="conversions-left">0</span> more left in plan.</div>
                                            <div>Want to convert more? <a href="http://www.audiotilly.com/pricing.html" target="_blank" class="button-primary">Upgrade Your Plan</a></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="top-heard-articles">
                                        <div class="caption">
                                            <div>Top Heard Articles</div>
                                            <div class="refresh-posts-statistic"><img src="<?php echo AUDIOTILLY_PLUGIN_URL; ?>admin/assets/loading.png"></div>
                                        </div>
                                        <div class="info grey-text">
                                            <div>Page & Post Names</div>
                                            <div></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="avg-group">
                                        <div class="avg-listen-time">
                                            <div>Lifetime Avg Listen Time:</div>
                                            <div>00:00:00</div>
                                        </div>
                                        <div class="avg-listen-player">
                                            <div>Lifetime Avg # player per visit:</div>
                                            <div>0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sidebar">
                                <div class="caption"><a href="http://www.audiotilly.com/faq.html" target="_blank">FAQ</a></div>
                                <div class="grey-text"><a href="#" class="tooltip">What does the dashboard show?<span class="tooltiptext">The dashboard displays a report on playback stats, top heard articles, and avg listening time. It will provide you clear insight into how your site visitors are enjoying listening to your content.</span></a></div>
                                <div class="grey-text"><a href="#" class="tooltip">Why is data missing in the dashboard?<span class="tooltiptext">We only track data when your site visitors listen to an article. If you aren't seeing data yet, please be patient as your users may need time to learn that they can now listen to your content.</span></a></div>
                                <div class="caption">Feedback</div>
                                <div class="grey-text">How can we make AudioTilly better?</div>
                                <div class="grey-text">- <a href="https://forms.gle/VKaoeucjgjK9NLMv5" target="_blank">click here</a> to share feedback</div>
                                <div class="caption">Support</div>
                                <div class="grey-text">Need help or have questions.</div>
                                <div class="grey-text">- read our FAQ for quick answers</div>
                                <div class="grey-text">- <a href="http://www.audiotilly.com/contact-us.html" target="_blank">contact us</a></div>
                                <div class="caption">Current Plan</div>
                                <div class="grey-text">You have currently on the <span id="audiotilly-plan">Free</span> Plan</div>
                                <div class="grey-text">- <span id="audiotilly-plan-conversions">5</span> articles converted per month</div>
                                <div class="caption">Upgrade Your Plan:</div>
                                <div class="grey-text">Convert more articles to audio format.</div>
                                <div class="grey-text">Select from our various fairly priced plans</div>
                                <a href="http://www.audiotilly.com/pricing.html" target="_blank" class="button-primary">Upgrade</a>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="tab">
                    <input type="radio" id="setup" class="tab" name="tab-group" <?php echo !get_option(AUDIO_TILLY_FIRST_SAVE) ? 'checked' : ''; ?>>
                    <label for="setup" class="tab-title">Setup</label>
                    <section class="tab-content">
                        <h2>Welcome to AudioTilly. Let's get you setup!</h2>

                        <h3>Quick Training Video</h3>
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/eU24eg1Yixw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        <h3>Setup Your Player:</h3>
                        <ul>
                            <li>AudioTilly is Free to use.</li>
                            <li>In the free account, each month we will convert up to 5 new posts or pages from text to audio.</li>
                            <li>If you write more than 5 posts or pages, consider upgrading to our affordable paid plans.</li>
                        </ul>
                        <form method="post" enctype="multipart/form-data" action="options.php">
                            <input type="hidden" name="<?php echo AUDIO_TILLY_FIRST_SAVE; ?>" value="1" />
                            <?php

                            settings_errors();
                            settings_fields("audio_tilly");
                            do_settings_sections("audio_tilly");
                            submit_button();

                            ?>
                        </form>
                    </section>
                </div>
            </div>

        </div>
        <?php
    }

    public function display_options()
    {
        $this->common = new AudioTilly_Common();

        // ************** Player SECTION ************** *
        add_settings_section('audio_tilly_playersettings', __('Player settings', AUDIOTILLY), array($this, 'playersettings_gui'), 'audio_tilly');
        add_settings_field('audio_tilly_player_skin', __('Player:', AUDIOTILLY), array($this, 'audio_tilly_player_skin_gui'), 'audio_tilly', 'audio_tilly_playersettings', array('label_for' => 'audio_tilly_player_skin'));
        add_settings_field('audio_tilly_position', __('Player position:', AUDIOTILLY), array($this, 'playerposition_gui'), 'audio_tilly', 'audio_tilly_playersettings', array('label_for' => 'audio_tilly_position'));
        add_settings_field('audio_tilly_align', __('Player align:', AUDIOTILLY), array($this, 'playeralign_gui'), 'audio_tilly', 'audio_tilly_playersettings', array('label_for' => 'audio_tilly_align'));
        add_settings_field('audio_tilly_voice', __('Voice:', AUDIOTILLY), array($this, 'player_voice_gui'), 'audio_tilly', 'audio_tilly_playersettings', array('label_for' => 'audio_tilly_voice'));
        add_settings_field('audio_tilly_clip', __('Add Your Intro & Exit clip:', AUDIOTILLY), array($this, 'player_clip_gui'), 'audio_tilly', 'audio_tilly_playersettings', array('label_for' => 'audio_tilly_clip'));
//        add_settings_field('audio_tilly_player_label', __('Player label:', AUDIOTILLY), array($this, 'playerlabel_gui'), 'audio_tilly', 'audio_tilly_playersettings', array('label_for' => 'audio_tilly_player_label'));

        //************** Additional configuration ************** *
        add_settings_section('audio_tilly_additional', __('Additional configuration', AUDIOTILLY), array($this, 'additional_gui'), 'audio_tilly');
        add_settings_field('audio_tilly_add_to', __('Add Player to every:', AUDIOTILLY), array($this, 'add_to_gui'), 'audio_tilly', 'audio_tilly_additional', array('label_for' => 'audio_tilly_add_to'));
//        add_settings_field('audio_tilly_add_post_title', __('Add post title to audio:', AUDIOTILLY), array($this, 'add_post_title_gui'), 'audio_tilly', 'audio_tilly_additional', array('label_for' => 'audio_tilly_add_post_title'));
//        add_settings_field('audio_tilly_add_post_excerpt', __('Add post excerpt to audio:', AUDIOTILLY), array($this, 'add_post_excerpt_gui'), 'audio_tilly', 'audio_tilly_additional', array('label_for' => 'audio_tilly_add_post_excerpt'));
//        add_settings_field('audio_tilly_skip_tags', __('Skip tags:', AUDIOTILLY), array($this, 'skiptags_gui'), 'audio_tilly', 'audio_tilly_additional', array('label_for' => 'audio_tilly_skip_tags'));

        // ************** OTHER SECTION ************** *
        add_settings_section('audio_tilly_other', __('', AUDIOTILLY), array($this, 'other_gui'), 'audio_tilly');

        add_settings_field('audio_tilly_license_key', __('License key:', AUDIOTILLY), array($this, 'license_key_gui'), 'audio_tilly', 'audio_tilly_other', array('label_for' => 'audio_tilly_license_key'));

        add_settings_field('audio_tilly_schema', __('Include Schema Tag:', AUDIOTILLY), array($this, 'schema_gui'), 'audio_tilly', 'audio_tilly_other', array('label_for' => 'audio_tilly_schema'));
        add_settings_field('audio_tilly_poweredby', __('Display Powered by AudioTilly:', AUDIOTILLY), array($this, 'poweredby_gui'), 'audio_tilly', 'audio_tilly_other', array('label_for' => 'audio_tilly_poweredby'));

        //************** Registration ************** *
//        register_setting(AUDIO_TILLY, 'audio_tilly_source_language');
        register_setting(AUDIO_TILLY, 'audio_tilly_license_key');
        register_setting(AUDIO_TILLY, 'audio_tilly_poweredby');
        register_setting(AUDIO_TILLY, 'audio_tilly_schema');
//        register_setting(AUDIO_TILLY, 'audio_tilly_gender_id');
        register_setting(AUDIO_TILLY, 'audio_tilly_player_skin');

        register_setting(AUDIO_TILLY, 'audio_tilly_position');
        register_setting(AUDIO_TILLY, 'audio_tilly_align');
        register_setting(AUDIO_TILLY, 'audio_tilly_player_voices');
        register_setting(AUDIO_TILLY, 'audio_tilly_player_once_clip');
//        register_setting(AUDIO_TILLY, 'audio_tilly_player_label');
//        register_setting(AUDIO_TILLY, 'audio_tilly_defconf');

        register_setting(AUDIO_TILLY, 'audio_tilly_add_to');
//        register_setting(AUDIO_TILLY, 'audio_tilly_add_post_title');
//        register_setting(AUDIO_TILLY, 'audio_tilly_add_post_excerpt');
//        register_setting(AUDIO_TILLY, 'audio_tilly_skip_tags');
        register_setting(AUDIO_TILLY, AUDIO_TILLY_FIRST_SAVE);
    }

    public function general_gui()
    {
        echo '<h2 id="general_configuration">General configuration</h2>';
    }

    public function other_gui()
    {
        echo '<h2 id="other_settings">Other settings</h2>';
    }

    public function schema_gui()
    {
        $checked = $this->common->checked_validator("audio_tilly_schema");

        echo '<input type="checkbox" name="audio_tilly_schema" id="audio_tilly_schema" ' . esc_attr($checked) . ' > <p class="description"></p>';
        echo '<p class="description">Inject Schema Tag (<a href="https://schema.org" target="_blank">https://schema.org</a>) for Structured Data.</p>';
    }

    public function poweredby_gui()
    {
        $checked = $this->common->checked_validator("audio_tilly_poweredby");

        echo '<input type="checkbox" name="audio_tilly_poweredby" id="audio_tilly_poweredby" ' . esc_attr($checked) . ' > <p class="description"></p>';
        echo '<p class="description">Hide the AudioTilly information under the player. (paid subscription)</p>';
    }

    public function license_key_gui()
    {
        $license_key = get_option(AUDIO_TILLY_LICENSE_KEY);
        echo '<input type="text" class="regular-text" name="' . AUDIO_TILLY_LICENSE_KEY . '" id="' . AUDIO_TILLY_LICENSE_KEY . '" value="' . esc_attr($license_key) . '"> ';
        echo '<p class="description">If you\'ve purchased a paid plan, enter your key here.</p>';
    }

    public function add_post_title_gui()
    {
        echo '<input type="checkbox" name="audio_tilly_add_post_title" id="audio_tilly_add_post_title" ' . $this->common->checked_validator('audio_tilly_add_post_title') . '> ';
        echo '<p class="description" for="audio_tilly_add_post_title">If enabled, each audio file will start from post title.</p>';
    }

    public function skiptags_gui()
    {
        $tags = get_option('audio_tilly_skip_tags');
        echo '<input type="text" class="regular-text" name="audio_tilly_skip_tags" id="audio_tilly_skip_tags" value="' . esc_attr($tags) . '"> ';
    }

    public function add_post_excerpt_gui()
    {
        if (get_option('audio_tilly_add_post_excerpt') === false) // Nothing yet saved
            update_option('audio_tilly_add_post_excerpt', '');//set default value to empty

        echo '<input type="checkbox" name="audio_tilly_add_post_excerpt" id="audio_tilly_add_post_excerpt" ' . $this->common->checked_validator('audio_tilly_add_post_excerpt') . '> ';
        echo '<p class="description" for="audio_tilly_add_post_excerpt">If enabled, each audio file will have post excerpt at the beginning.</p>';
    }

    public function playersettings_gui()
    {
        // Empty
    }

    public function playerposition_gui()
    {
        $positions_values = ['top' => 'Header (top of content)', 'bottom' => 'Footer (bottom of content)'];
        $selected_position = get_option('audio_tilly_position');
        if (empty($selected_position)) {
            $selected_position = 'bottom';
        }

        echo '<fieldset class="audiotilly-fieldset">';
        foreach ($positions_values as $position => $name) {
            $checked = $selected_position == $position ? 'checked' : '';
            echo '<div class="fieldset-row"><label><input type="radio" name="audio_tilly_position" value="' . esc_attr($position) . '" ' . esc_attr($checked) . '/>' . esc_html($name) . '</label></div>';
        }
        echo '</fieldset>';
    }

    public function playeralign_gui()
    {
        $align_values = ['center' => 'Center', 'left' => 'Left', 'right' => 'Right'];
        $selected_align = get_option('audio_tilly_align', 'center');
        if (empty($selected_align)) {
            $selected_align = 'center';
        }

        echo '<fieldset class="audiotilly-fieldset">';
        foreach ($align_values as $align => $name) {
            $checked = $selected_align == $align ? 'checked' : '';
            echo '<div class="fieldset-row"><label><input type="radio" name="audio_tilly_align" value="' . esc_attr($align) . '" ' . esc_attr($checked) . ' />' . esc_html($name) . '</label></div>';
        }
        echo '</fieldset>';
    }

    public function playerlabel_gui()
    {
        $player_label = get_option('audio_tilly_player_label');
        echo '<input type="text" class="regular-text" name="audio_tilly_player_label" id="audio_tilly_player_label" value="' . esc_attr($player_label) . '"> ';
    }

    public function additional_gui()
    {
        //Empty
    }

    public function audio_tilly_player_skin_gui()
    {
        $skins = [
            'green' => 'Standard',
            'zaudio' => 'Boxy',
            'mediaelement' => 'Slim'
        ];
        $selected_skin = get_option(AUDIO_TILLY_PLAYER_SKIN, 'green');

        $src = AUDIOTILLY_API_HOST . '/audiotilly/voices/' . rawurlencode(AUDIO_TILLY_AVAILABLE_VOICES['James']);

        echo '<fieldset class="audiotilly-fieldset player-skin">';
        foreach ($skins as $skin => $name) {
            $checked = $selected_skin == $skin ? 'checked' : '';
            $attrs = [
                'wp_url' => rtrim(get_site_url(), '/'),
                'is_menu' => 1,
                'src' => $src,
                'skin' => $skin,
                'copyright' => 0,
                'align' => get_option('audio_tilly_align', 'center')
            ];
            $player = '<iframe class="audiotilly-iframe ' . esc_attr($skin) . '"
                src="' . AUDIOTILLY_API_HOST . '/plugin/player?' . esc_attr(http_build_query($attrs)) . '"></iframe>';
            echo '<div class="fieldset-row skin"><label class="skin"><input type="radio" name="' . AUDIO_TILLY_PLAYER_SKIN . '" value="' . esc_attr($skin) . '" ' . esc_attr($checked) . ' />' . esc_html($name) . "</label>$player</div>";
        }
        echo '</fieldset>';
    }

    public function player_voice_gui()
    {
        $selected_voices = get_option(AUDIO_TILLY_VOICES, array_keys(AUDIO_TILLY_AVAILABLE_VOICES));

        echo '<p class="description" for="audio_tilly_add_post_excerpt">If you select more than 1 voice, we will randomly select one for each of the content converted.</p>';
        echo "<fieldset class=\"audiotilly-fieldset sample\">";
        foreach (AUDIO_TILLY_AVAILABLE_VOICES as $voice => $name) {
            $checked = in_array($voice, $selected_voices) ? 'checked' : '';
            echo '<div class="sample-item"><label><input type="checkbox" name="' . AUDIO_TILLY_VOICES . '[]" value="' . esc_attr($voice) . '" ' . esc_attr($checked) . ' />' . esc_html($voice) . '</label>' .
                wp_audio_shortcode(['src' => AUDIOTILLY_API_HOST . '/audiotilly/voices/' . rawurlencode($name)]) . '</div>';
        }
        echo '</fieldset>';
    }

    public function player_clip_gui()
    {
        $introUrl = AUDIOTILLY_API_HOST . '/audiotilly/clips/' . get_option(AUDIO_TILLY_LICENSE_ID) . '/intro.mp3?t=' . time();
        $headers = get_headers($introUrl);
        $introExists = (bool)strpos(reset($headers), '200 OK');
        $exitUrl = AUDIOTILLY_API_HOST . '/audiotilly/clips/' . get_option(AUDIO_TILLY_LICENSE_ID) . '/exit.mp3?t=' . time();
        $headers = get_headers($exitUrl);
        $exitExists = (bool)strpos(reset($headers), '200 OK');
        $checked = get_option('audio_tilly_player_once_clip') ? 'checked' : '';
        echo '<fieldset class="audiotilly-fieldset">' .
                '<div class="clip-container">' .
                    '<div class="grey">The mp3 audio clip you upload will play before & at the end of the content we convert.</div>' .
                    '<ul class="grey">' .
                        '<li>upload mp3 file up to 10 seconds max</li>' .
                    '</ul>' .
                    '<div class="clip-line">' .
                        '<span>Upload intro clip (10 seconds max)</span>' .
                        '<div id="intro-upload-wrapper">' .
                            '<label>' .
                                '<input type="file" accept=".mp3" id="intro-upload-input" data-clip="intro">' .
                                '<span>Upload</span>' .
                            '</label>' .
                        '</div>' .
                        '<div id="intro-controls">' .
                        ($introExists ?
                            '<img id="intro-play" src="' . plugins_url('/audiotilly/admin/assets/play.png') . '" data-clip="intro">' .
                            '<img id="intro-trash-button" src="' . plugins_url('/audiotilly/admin/assets/trash.png') . '" data-clip="intro">'
                        : '') .
                        '</div>' .
                        '<audio id="intro-audio" preload="none" src="' . $introUrl . '" data-clip="exit" style="display: none;"></audio>' .
                        '<div id="intro-reply"></div>' .
                    '</div>' .
                    '<div class="clip-line">' .
                        '<span>Upload exit clip (10 seconds max)</span>' .
                        '<div id="exit-upload-wrapper">' .
                            '<label>' .
                                '<input type="file" accept=".mp3" id="exit-upload-input" data-clip="exit">' .
                                '<span>Upload</span>' .
                            '</label>' .
                        '</div>' .
                        '<div id="exit-controls">' .
                        ($exitExists ?
                            '<img id="exit-play" src="' . plugins_url('/audiotilly/admin/assets/play.png') . '" data-clip="exit">' .
                            '<img id="exit-trash-button" src="' . plugins_url('/audiotilly/admin/assets/trash.png') . '" data-clip="exit">'
                        : '') .
                        '</div>' .
                        '<audio id="exit-audio" preload="none" src="' . $exitUrl . '" data-clip="exit" style="display: none;"></audio>' .
                        '<div id="exit-reply"></div>' .
                    '</div>' .
                    '<label><input type="checkbox" name="audio_tilly_player_once_clip" value="1" ' . esc_attr($checked) . ' />Play entry & exit audio clip once per user\'s session</label>' .
                '</div>' .
            '</fieldset>';
    }

    public function add_to_gui()
    {
        $types = ['post', 'page'];
        $selected_types = get_option('audio_tilly_add_to');
        if (!is_array($selected_types)) {
            $selected_types = [];
        }

        echo '<fieldset class="audiotilly-fieldset">';
        foreach ($types as $type) {
            $checked = in_array($type, $selected_types) ? 'checked' : '';
            echo '<div><label><input type="checkbox" name="audio_tilly_add_to[]" value="' . esc_attr($type) . '" ' . esc_attr($checked) . ' />' . esc_html($type) . '</label></div>';
        }
        echo '</fieldset>';
    }
}
