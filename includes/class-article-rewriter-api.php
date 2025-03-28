<?php
/**
 * The API functionality of the plugin.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 */

/**
 * The API functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the REST API.
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 * @author     WiDigital <info@widigital.com>
 */
class Article_Rewriter_API {

    /**
     * The shared service class instance.
     *
     * @since    1.1.0
     * @access   private
     * @var      Article_Rewriter_Service    $service    Shared service class.
     */
    private $service;

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
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Instantiate the service class
        // Ensure the service class file is loaded before this class is instantiated
        if (!class_exists('Article_Rewriter_Service')) {
             require_once plugin_dir_path( __FILE__ ) . 'class-article-rewriter-service.php';
        }
        $this->service = new Article_Rewriter_Service();
    }

    /**
     * Register the REST API routes.
     *
     * @since    1.0.0
     */
    public function register_routes() {
        register_rest_route('article-rewriter/v1', '/rewrite', array(
            'methods'  => 'POST',
            'callback' => array($this, 'rewrite_article'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route('article-rewriter/v1', '/history/(?P<post_id>\d+)', array(
            'methods'  => 'GET',
            'callback' => array($this, 'get_history'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    /**
     * Check if the user has permission to access the API.
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request The request object.
     * @return   bool|WP_Error True if permission is granted, WP_Error otherwise.
     */
    public function check_permission($request) {
        // 1. Nonce Check (for requests originating from WP admin)
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
             // Return a WP_Error for permission denied, REST API handles this correctly
             return new WP_Error(
                 'rest_forbidden_context',
                 __('Nonce verification failed.', 'article-rewriter'),
                 array('status' => 401) // Unauthorized or 403 Forbidden
             );
        }

        // 2. Capability Check
        if (!current_user_can('edit_posts')) {
             return new WP_Error(
                 'rest_forbidden_context',
                 __('Sorry, you are not allowed to perform this action.', 'article-rewriter'),
                 array('status' => 403) // Forbidden
             );
        }

        // 3. License Check
        $license_status = get_option('article_rewriter_license_status', 'inactive');
        if ($license_status !== 'active') {
             return new WP_Error(
                 'rest_license_inactive',
                 __('Your license is not active.', 'article-rewriter'),
                 array('status' => 403) // Forbidden
             );
        }

        // All checks passed
        return true;
    }

    /**
     * Rewrite an article using the selected API.
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request The request object.
     * @return   WP_REST_Response
     */
    public function rewrite_article($request) {
        $post_id = $request->get_param('post_id');
        $content = $request->get_param('content');
        $api = $request->get_param('api');
        $style = $request->get_param('style');

        if (empty($content)) {
            return new WP_REST_Response(
                array('message' => __('No content provided.', 'article-rewriter')),
                400
            );
        }

        if (empty($api)) {
            return new WP_REST_Response(
                array('message' => __('No API provider selected.', 'article-rewriter')),
                400
            );
        }

        if (empty($style)) {
            return new WP_REST_Response(
                array('message' => __('No rewriting style selected.', 'article-rewriter')),
                400
            );
        }

        // Rewrite the content using the service class
        $rewritten_content = $this->service->rewrite_content($content, $api, $style);

        if (is_wp_error($rewritten_content)) {
            return new WP_REST_Response(
                array('message' => $rewritten_content->get_error_message()),
                500 // Or potentially map WP_Error codes to HTTP status codes
            );
        }

        // Save the rewrite history using the service class
        $this->service->save_history($post_id, $api, $style, $rewritten_content);

        return new WP_REST_Response(
            array('content' => $rewritten_content),
            200
        );
    }

    /**
     * Get the rewrite history for a post.
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request The request object.
     * @return   WP_REST_Response
     */
    public function get_history($request) {
        global $wpdb;
        // Sanitize post_id as integer
        $post_id = intval($request->get_param('post_id')); 

        if ($post_id <= 0) {
             // Optionally return an error if post_id is invalid
             return new WP_Error('invalid_post_id', __('Invalid Post ID provided.', 'article-rewriter'), array('status' => 400));
        }

        $table_name = $wpdb->prefix . 'article_rewriter_history';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE post_id = %d ORDER BY created_at DESC",
                $post_id
            ), ARRAY_A
        );

        $history = array();
        foreach ($results as $row) {
            $history[] = array(
                'id' => $row['id'],
                'api' => $row['api'],
                'style' => $row['style'],
                'content' => $row['content'],
                'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row['created_at'])),
            );
        }

        return $history;
    }

    /**
     * Rewrite content using the selected API.
     *
     * @since    1.0.0
     * @param    string $content The content to rewrite.
     * @param    string $api     The API provider to use.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    // Removed duplicated methods: rewrite_with_api, rewrite_with_openai, rewrite_with_deepseek,
    // rewrite_with_anthropic, rewrite_with_gemini, get_prompt_for_style, save_history
    // These are now handled by Article_Rewriter_Service
}