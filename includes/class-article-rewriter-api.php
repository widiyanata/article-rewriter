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
     * @return   bool
     */
    public function check_permission($request) {
        // Check if user can edit posts
        if (!current_user_can('edit_posts')) {
            return false;
        }

        // Check if license is active
        $license_status = get_option('article_rewriter_license_status');
        if ($license_status !== 'active') {
            return false;
        }

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

        // Rewrite the content using the selected API
        $rewritten_content = $this->rewrite_with_api($content, $api, $style);

        if (is_wp_error($rewritten_content)) {
            return new WP_REST_Response(
                array('message' => $rewritten_content->get_error_message()),
                500
            );
        }

        // Save the rewrite history
        $this->save_history($post_id, $api, $style, $rewritten_content);

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
        $post_id = $request->get_param('post_id');

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
    private function rewrite_with_api($content, $api, $style) {
        switch ($api) {
            case 'openai':
                return $this->rewrite_with_openai($content, $style);
            case 'deepseek':
                return $this->rewrite_with_deepseek($content, $style);
            case 'anthropic':
                return $this->rewrite_with_anthropic($content, $style);
            case 'gemini':
                return $this->rewrite_with_gemini($content, $style);
            default:
                return new WP_Error('invalid_api', __('Invalid API provider.', 'article-rewriter'));
        }
    }

    /**
     * Rewrite content using the OpenAI API.
     *
     * @since    1.0.0
     * @param    string $content The content to rewrite.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    private function rewrite_with_openai($content, $style) {
        $api_key = get_option('article_rewriter_openai_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('api_key_missing', __('OpenAI API key is missing.', 'article-rewriter'));
        }

        $prompt = $this->get_prompt_for_style($style);
        
        $data = array(
            'model' => 'gpt-4',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $prompt,
                ),
                array(
                    'role' => 'user',
                    'content' => $content,
                ),
            ),
            'temperature' => 0.7,
            'max_tokens' => 4000,
        );

        $response = wp_remote_post(
            'https://api.openai.com/v1/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($data),
                'timeout' => 60,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            return new WP_Error('openai_error', __('OpenAI API error: ', 'article-rewriter') . $body);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('openai_error', __('Invalid response from OpenAI API.', 'article-rewriter'));
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Rewrite content using the DeepSeek API.
     *
     * @since    1.0.0
     * @param    string $content The content to rewrite.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    private function rewrite_with_deepseek($content, $style) {
        $api_key = get_option('article_rewriter_deepseek_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('api_key_missing', __('DeepSeek API key is missing.', 'article-rewriter'));
        }

        $prompt = $this->get_prompt_for_style($style);
        
        $data = array(
            'model' => 'deepseek-chat',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $prompt,
                ),
                array(
                    'role' => 'user',
                    'content' => $content,
                ),
            ),
            'temperature' => 0.7,
            'max_tokens' => 4000,
        );

        $response = wp_remote_post(
            'https://api.deepseek.com/v1/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($data),
                'timeout' => 60,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            return new WP_Error('deepseek_error', __('DeepSeek API error: ', 'article-rewriter') . $body);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('deepseek_error', __('Invalid response from DeepSeek API.', 'article-rewriter'));
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Get the prompt for the selected style.
     *
     * @since    1.0.0
     * @param    string $style The rewriting style.
     * @return   string The prompt.
     */
    private function get_prompt_for_style($style) {
        switch ($style) {
            case 'standard':
                return __('You are a professional content rewriter. Rewrite the following article while maintaining the same meaning, but using different wording and sentence structure. Keep the same headings and formatting.', 'article-rewriter');
            case 'formal':
                return __('You are a professional content rewriter. Rewrite the following article in a formal, academic style while maintaining the same meaning. Use sophisticated vocabulary and complex sentence structures. Keep the same headings and formatting.', 'article-rewriter');
            case 'casual':
                return __('You are a professional content rewriter. Rewrite the following article in a casual, conversational style while maintaining the same meaning. Use simple language and a friendly tone. Keep the same headings and formatting.', 'article-rewriter');
            case 'creative':
                return __('You are a professional content rewriter. Rewrite the following article in a creative, engaging style while maintaining the same meaning. Use vivid language, metaphors, and storytelling techniques. Keep the same headings and formatting.', 'article-rewriter');
            default:
                return __('You are a professional content rewriter. Rewrite the following article while maintaining the same meaning, but using different wording and sentence structure. Keep the same headings and formatting.', 'article-rewriter');
        }
    }

    /**
     * Save the rewrite history.
     *
     * @since    1.0.0
     * @param    int    $post_id  The post ID.
     * @param    string $api      The API provider used.
     * @param    string $style    The rewriting style used.
     * @param    string $content  The rewritten content.
     */
    private function save_history($post_id, $api, $style, $content) {
        global $wpdb;

        // If post_id is not provided, try to get it from the current post
        if (empty($post_id)) {
            $post_id = get_the_ID();
        }

        // Get the current user ID
        $user_id = get_current_user_id();

        // Insert the history record
        $table_name = $wpdb->prefix . 'article_rewriter_history';
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'api' => $api,
                'style' => $style,
                'content' => $content,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Rewrite content using the Anthropic API.
     *
     * @since    1.0.0
     * @param    string $content The content to rewrite.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    private function rewrite_with_anthropic($content, $style) {
        // Implementation for Anthropic API
        // This is a placeholder for future implementation
        return $content;
    }

    /**
     * Rewrite content using the Google Gemini API.
     *
     * @since    1.0.0
     * @param    string $content The content to rewrite.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    private function rewrite_with_gemini($content, $style) {
        // Implementation for Google Gemini API
        // This is a placeholder for future implementation
        return $content;
    }
}