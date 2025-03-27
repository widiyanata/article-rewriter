<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/admin
 * @author     Widigital <info@widigital.com>
 */
class Article_Rewriter_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Article_Rewriter_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Article_Rewriter_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/css/article-rewriter-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Article_Rewriter_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Article_Rewriter_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/js/article-rewriter-admin.js', array( 'jquery' ), $this->version, false );

        // Localize the script with data for JavaScript - Use 'articleRewriterAdmin' to match JS usage
        wp_localize_script( $this->plugin_name, 'articleRewriterAdmin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'article_rewriter_nonce' ), // This nonce is used by JS
            'i18n' => array( // Changed 'strings' key to 'i18n' and added missing keys
                // Original keys (some might be unused by admin.js now, but keep for safety)
                'maxItemsExceeded' => __( 'You can only select up to %d items at once.', 'article-rewriter' ),
                'noPostsSelected' => __( 'Please select at least one post to rewrite.', 'article-rewriter' ),
                'confirmBatch' => __( 'Are you sure you want to create a batch job to rewrite these posts? This may take some time.', 'article-rewriter' ), // Used by old inline JS, keep for now
                'selectContent' => __( 'Please select some content to rewrite.', 'article-rewriter' ),
                'confirmRewrite' => __( 'Are you sure you want to rewrite this content? This will replace your current content.', 'article-rewriter' ),
                'rewriteError' => __( 'An error occurred while rewriting the content.', 'article-rewriter' ), // Generic error
                'rewriteSuccess' => __( 'Content rewritten successfully!', 'article-rewriter' ),
                'rewrite' => __( 'Rewrite', 'article-rewriter' ),
                'rewriting' => __( 'Rewriting...', 'article-rewriter' ),
                'rewriteWith' => __( 'Rewrite with', 'article-rewriter' ),
                'rewriteStyle' => __( 'Rewrite style', 'article-rewriter' ),

                // Keys specifically used in article-rewriter-admin.js
                'processing' => __('Processing...', 'article-rewriter'),
                'submit' => __('Create Batch', 'article-rewriter'), // Default submit text
                'error' => __('An error occurred. Please try again.', 'article-rewriter'), // More specific generic error
                'loading' => __('Loading...', 'article-rewriter'),
                'job_details' => __('Job Details', 'article-rewriter'),
                'job_id' => __('Job ID', 'article-rewriter'),
                'status' => __('Status', 'article-rewriter'),
                'created' => __('Created', 'article-rewriter'),
                'updated' => __('Updated', 'article-rewriter'),
                'api' => __('API', 'article-rewriter'),
                'style' => __('Style', 'article-rewriter'),
                'progress' => __('Progress', 'article-rewriter'),
                'posts' => __('Posts', 'article-rewriter'),
                'post_title' => __('Post Title', 'article-rewriter'),
                'actions' => __('Actions', 'article-rewriter'),
                'view' => __('View', 'article-rewriter'),
                'confirm_cancel' => __('Are you sure you want to cancel this batch job?', 'article-rewriter'),
                'confirm_delete' => __('Are you sure you want to delete this batch job? This cannot be undone.', 'article-rewriter'),
                'enter_purchase_code' => __('Please enter your purchase code.', 'article-rewriter'),
                'activating' => __('Activating...', 'article-rewriter'),
                'activation_error' => __('License activation failed. Please check the code and try again.', 'article-rewriter'),
                'activate' => __('Activate License', 'article-rewriter'),
                'confirm_deactivate' => __('Are you sure you want to deactivate this license?', 'article-rewriter'),
                'deactivating' => __('Deactivating...', 'article-rewriter'),
                'deactivation_error' => __('License deactivation failed.', 'article-rewriter'),
                'deactivate' => __('Deactivate License', 'article-rewriter'),
            ),
            'apis' => array(
                'openai' => get_option( 'article_rewriter_openai_api_key' ) ? true : false,
                'deepseek' => get_option( 'article_rewriter_deepseek_api_key' ) ? true : false,
                'anthropic' => get_option( 'article_rewriter_anthropic_api_key' ) ? true : false, // Added
                'gemini' => get_option( 'article_rewriter_gemini_api_key' ) ? true : false, // Added
            ),
            'styles' => array(
                'standard' => __( 'Standard', 'article-rewriter' ),
                'creative' => __( 'Creative', 'article-rewriter' ),
                'formal' => __( 'Formal', 'article-rewriter' ),
                'simple' => __( 'Simple', 'article-rewriter' ),
            ),
            'defaultApi' => get_option( 'article_rewriter_default_api', 'openai' ),
            'defaultStyle' => get_option( 'article_rewriter_default_rewrite_style', 'standard' ),
        ) );
    }

    /**
     * Add plugin admin menu
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        // Main menu item
        add_menu_page(
            __( 'Article Rewriter', 'article-rewriter' ),
            __( 'Article Rewriter', 'article-rewriter' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_plugin_admin_page' ),
            'dashicons-edit',
            26
        );

        // Settings submenu
        add_submenu_page(
            $this->plugin_name,
            __( 'Settings', 'article-rewriter' ),
            __( 'Settings', 'article-rewriter' ),
            'manage_options',
            $this->plugin_name . '-settings',
            array( $this, 'display_plugin_admin_settings' )
        );

        // Batch Processing submenu
        add_submenu_page(
            $this->plugin_name,
            __( 'Batch Processing', 'article-rewriter' ),
            __( 'Batch Processing', 'article-rewriter' ),
            'manage_options',
            $this->plugin_name . '-batch',
            array( $this, 'display_plugin_admin_batch' )
        );

        // License submenu
        add_submenu_page(
            $this->plugin_name,
            __( 'License', 'article-rewriter' ),
            __( 'License', 'article-rewriter' ),
            'manage_options',
            $this->plugin_name . '-license',
            array( $this, 'display_plugin_admin_license' )
        );
    }

    /**
     * Register plugin settings
     *
     * @since    1.0.0
     */
    public function register_settings() {

        // OpenAI API Key
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_openai_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );

        // DeepSeek API Key
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_deepseek_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );

        // Anthropic API Key
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_anthropic_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );

        // Gemini API Key
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_gemini_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );

        // Default API
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_default_api',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'openai',
            )
        );

        // Default Rewrite Style
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_default_rewrite_style',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'standard',
            )
        );

        // Batch Size
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_batch_size',
            array(
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 10,
            )
        );

        // Enable Gutenberg Integration
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_enable_gutenberg',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'yes',
            )
        );

        // Enable Classic Editor Integration
        register_setting(
            'article_rewriter_settings',
            'article_rewriter_enable_classic_editor',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'yes',
            )
        );
    }

    /**
     * Render the main admin page
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/article-rewriter-admin-display.php';
    }

    /**
     * Render the settings page
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_settings() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/article-rewriter-admin-settings.php';
    }

    /**
     * Render the batch processing page
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_batch() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/article-rewriter-admin-batch.php';
    }

    /**
     * Render the license page
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_license() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/article-rewriter-admin-license.php';
    }
}