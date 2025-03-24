<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
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
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 * @author     WiDigital <info@widigital.com>
 */
class Article_Rewriter {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Article_Rewriter_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
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
    public function __construct() {
        if (defined('ARTICLE_REWRITER_VERSION')) {
            $this->version = ARTICLE_REWRITER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'article-rewriter';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_editor_hooks();
        $this->define_api_hooks();
        $this->define_batch_hooks();
        $this->define_license_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Article_Rewriter_Loader. Orchestrates the hooks of the plugin.
     * - Article_Rewriter_i18n. Defines internationalization functionality.
     * - Article_Rewriter_Admin. Defines all hooks for the admin area.
     * - Article_Rewriter_Editor. Defines all hooks for the editor integration.
     * - Article_Rewriter_API. Defines all hooks for the REST API.
     * - Article_Rewriter_Batch. Defines all hooks for batch processing.
     * - Article_Rewriter_License. Defines all hooks for license management.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-article-rewriter-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-article-rewriter-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-article-rewriter-api.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-article-rewriter-admin.php';

        /**
         * The class responsible for defining all actions that occur in the editor.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-article-rewriter-editor.php';

        /**
         * The class responsible for batch processing.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-article-rewriter-batch.php';

        /**
         * The class responsible for license management.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-article-rewriter-license.php';

        $this->loader = new Article_Rewriter_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Article_Rewriter_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Article_Rewriter_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Article_Rewriter_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the editor functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_editor_hooks() {

        $plugin_editor = new Article_Rewriter_Editor($this->get_plugin_name(), $this->get_version());

        // Block editor (Gutenberg)
        $this->loader->add_action('enqueue_block_editor_assets', $plugin_editor, 'enqueue_block_editor_assets');

        // Classic editor (TinyMCE)
        $this->loader->add_filter('mce_external_plugins', $plugin_editor, 'add_tinymce_plugin');
        $this->loader->add_filter('mce_buttons', $plugin_editor, 'register_tinymce_button');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_editor, 'enqueue_classic_editor_assets');
    }

    /**
     * Register all of the hooks related to the REST API functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {

        $plugin_api = new Article_Rewriter_API($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('rest_api_init', $plugin_api, 'register_routes');
    }

    /**
     * Register all of the hooks related to batch processing
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_batch_hooks() {

        $plugin_batch = new Article_Rewriter_Batch($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_post_article_rewriter_start_batch', $plugin_batch, 'handle_start_batch');
        $this->loader->add_action('wp_ajax_article_rewriter_get_batch_jobs_status', $plugin_batch, 'handle_get_batch_jobs_status');
        $this->loader->add_action('article_rewriter_process_batch', $plugin_batch, 'process_batch');
    }

    /**
     * Register all of the hooks related to license management
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_license_hooks() {

        $plugin_license = new Article_Rewriter_License($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_post_article_rewriter_activate_license', $plugin_license, 'handle_activate_license');
        $this->loader->add_action('admin_post_article_rewriter_deactivate_license', $plugin_license, 'handle_deactivate_license');
        $this->loader->add_action('article_rewriter_license_check', $plugin_license, 'check_license');
        $this->loader->add_action('admin_notices', $plugin_license, 'admin_notices');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Article_Rewriter_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}