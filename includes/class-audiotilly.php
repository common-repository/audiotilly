<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       audiotilly.com
 * @since      1.0.0
 *
 * @package    AudioTilly
 * @subpackage AudioTilly/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    AudioTilly
 * @subpackage AudioTilly/includes
 * @author     BigEngage Inc.
 */

require_once __DIR__ . '/../admin/AudioTilly-Consts.php';

class AudioTilly
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      AudioTilly_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->plugin_name = AUDIOTILLY;
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - AudioTilly_Loader. Orchestrates the hooks of the plugin.
     * - AudioTilly_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-audiotilly-loader.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/AudioTilly-Common.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/AudioTilly-Service.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/AudioTilly-GeneralConfiguration.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/AudioTilly-API.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-audiotilly-public.php';

        $this->loader = new AudioTilly_Loader();
    }


    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $general_configuration = new AudioTilly_GeneralConfiguration();
        $audiotilly_service = new AudioTilly_Service();
        $common = new AudioTilly_Common();

        $this->loader->add_action('admin_enqueue_scripts', $common, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $common, 'enqueue_scripts');
        $this->loader->add_action('upgrader_process_complete', $audiotilly_service, 'upgrade_complete', 10, 2);

        $this->loader->add_action('admin_menu', $general_configuration, 'audio_tilly_add_menu');
        $this->loader->add_action('admin_init', $general_configuration, 'display_options');

        $this->loader->add_filter('plugin_action_links_audiotilly/audiotilly.php', $general_configuration, 'audiotilly_settings_link');

        $plugin = plugin_basename(plugin_dir_path(dirname(__FILE__)) . 'audiotilly.php');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        // Front-end
        $plugin_public = new AudioTilly_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_filter('the_content', $plugin_public, 'content_filter', 99999);

        $plugin_api = new AudioTilly_API();
        $this->loader->add_action('rest_api_init', $plugin_api, 'enqueue_routes');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    AudioTilly_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }
}
