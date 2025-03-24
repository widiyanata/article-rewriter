<?php
/**
 * The batch processing functionality of the plugin.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 */

/**
 * The batch processing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the batch processing.
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 * @author     WiDigital <info@widigital.com>
 */
class Article_Rewriter_Batch {

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
     * Handle the start of a batch job.
     *
     * @since    1.0.0
     */
    public function handle_start_batch() {
        // Verify the nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'article_rewriter_start_batch')) {
            wp_die(__('Security check failed.', 'article-rewriter'));
        }

        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'article-rewriter'));
        }

        // Check if license is active
        $license_status = get_option('article_rewriter_license_status');
        if ($license_status !== 'active') {
            wp_die(__('Your license is not active. Please activate your license to use this feature.', 'article-rewriter'));
        }

        // Check if post IDs are provided
        if (empty($_POST['post_ids'])) {
            wp_die(__('Please select at least one post.', 'article-rewriter'));
        }

        // Get the form data
        $post_ids = array_map('intval', $_POST['post_ids']);
        $api = sanitize_text_field($_POST['api'] ?? get_option('article_rewriter_default_api', 'openai'));
        $style = sanitize_text_field($_POST['style'] ?? get_option('article_rewriter_default_style', 'standard'));

        // Get the posts to rewrite
        $posts = $this->get_posts_for_batch($post_ids);

        // Create the batch job
        $batch_id = $this->create_batch_job($posts, $api, $style);

        // Schedule the batch processing
        $this->schedule_batch_processing($batch_id);

        // Redirect back to the batch page
        wp_redirect(admin_url('admin.php?page=article-rewriter-batch&batch_started=true&batch_id=' . $batch_id));
        exit;
    }

    /**
     * Get posts for batch processing.
     *
     * @since    1.0.0
     * @param    array $post_ids The post IDs to process.
     * @return   array  The posts to process.
     */
    private function get_posts_for_batch($post_ids) {
        global $wpdb;

        // Get the posts
        $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE ID IN ($placeholders) AND post_status = 'publish'",
            $post_ids
        );

        $posts = $wpdb->get_results($query);

        return $posts;
    }

    /**
     * Create a batch job.
     *
     * @since    1.0.0
     * @param    array  $posts The posts to process.
     * @param    string $api   The API to use.
     * @param    string $style The style to use.
     * @return   int    The batch ID.
     */
    private function create_batch_job($posts, $api, $style) {
        global $wpdb;

        // Get the current user ID
        $user_id = get_current_user_id();

        // Insert the batch job
        $table_name = $wpdb->prefix . 'article_rewriter_batch';
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'api' => $api,
                'style' => $style,
                'status' => 'pending',
                'total' => count($posts),
                'processed' => 0,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
        );

        $batch_id = $wpdb->insert_id;

        // Insert the batch items
        $table_name = $wpdb->prefix . 'article_rewriter_batch_items';
        foreach ($posts as $post) {
            $wpdb->insert(
                $table_name,
                array(
                    'batch_id' => $batch_id,
                    'post_id' => $post->ID,
                    'status' => 'pending',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ),
                array('%d', '%d', '%s', '%s', '%s')
            );
        }

        return $batch_id;
    }

    /**
     * Schedule the batch processing.
     *
     * @since    1.0.0
     * @param    int $batch_id The batch ID.
     */
    private function schedule_batch_processing($batch_id) {
        // Schedule the batch processing
        wp_schedule_single_event(time(), 'article_rewriter_process_batch', array($batch_id));
    }

    /**
     * Process a batch job.
     *
     * @since    1.0.0
     * @param    int $batch_id The batch ID.
     */
    public function process_batch($batch_id) {
        global $wpdb;

        // Get the batch job
        $table_name = $wpdb->prefix . 'article_rewriter_batch';
        $batch = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $batch_id
            )
        );

        if (!$batch) {
            return;
        }

        // Update the batch status
        $wpdb->update(
            $table_name,
            array(
                'status' => 'processing',
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $batch_id),
            array('%s', '%s'),
            array('%d')
        );

        // Get the batch items
        $table_name = $wpdb->prefix . 'article_rewriter_batch_items';
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE batch_id = %d AND status = 'pending' ORDER BY id ASC",
                $batch_id
            )
        );

        // Process each item
        foreach ($items as $item) {
            // Get the post
            $post = get_post($item->post_id);

            if (!$post) {
                // Update the item status
                $wpdb->update(
                    $table_name,
                    array(
                        'status' => 'failed',
                        'updated_at' => current_time('mysql'),
                    ),
                    array('id' => $item->id),
                    array('%s', '%s'),
                    array('%d')
                );
                continue;
            }

            // Rewrite the post content
            $rewritten_content = $this->rewrite_post_content($post->post_content, $batch->api, $batch->style);

            if (is_wp_error($rewritten_content)) {
                // Update the item status
                $wpdb->update(
                    $table_name,
                    array(
                        'status' => 'failed',
                        'updated_at' => current_time('mysql'),
                    ),
                    array('id' => $item->id),
                    array('%s', '%s'),
                    array('%d')
                );
                continue;
            }

            // Update the post
            $post_data = array(
                'ID' => $post->ID,
                'post_content' => $rewritten_content,
            );

            $result = wp_update_post($post_data);

            if (is_wp_error($result)) {
                // Update the item status
                $wpdb->update(
                    $table_name,
                    array(
                        'status' => 'failed',
                        'updated_at' => current_time('mysql'),
                    ),
                    array('id' => $item->id),
                    array('%s', '%s'),
                    array('%d')
                );
                continue;
            }

            // Save the rewrite history
            $this->save_history($post->ID, $batch->api, $batch->style, $rewritten_content);

            // Update the item status
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'completed',
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $item->id),
                array('%s', '%s'),
                array('%d')
            );

            // Update the batch processed count
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $wpdb->prefix" . "article_rewriter_batch SET processed = processed + 1, updated_at = %s WHERE id = %d",
                    current_time('mysql'),
                    $batch_id
                )
            );
        }

        // Check if all items are processed
        $pending_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->prefix" . "article_rewriter_batch_items WHERE batch_id = %d AND status = 'pending'",
                $batch_id
            )
        );

        if ($pending_count == 0) {
            // Update the batch status
            $wpdb->update(
                $wpdb->prefix . 'article_rewriter_batch',
                array(
                    'status' => 'completed',
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $batch_id),
                array('%s', '%s'),
                array('%d')
            );
        } else {
            // Schedule the next batch processing
            wp_schedule_single_event(time() + 60, 'article_rewriter_process_batch', array($batch_id));
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
     * Handle the AJAX request to get batch jobs status.
     *
     * @since    1.0.0
     */
    public function handle_get_batch_jobs_status() {
        // Verify the nonce
        if (!wp_verify_nonce($_POST['nonce'], 'article_rewriter_batch_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Get the batch jobs
        global $wpdb;
        $batch_jobs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}article_rewriter_batch ORDER BY created_at DESC",
            ARRAY_A
        );

        if (!$batch_jobs) {
            wp_send_json_error(array('message' => __('No batch jobs found.', 'article-rewriter')));
        }

        wp_send_json_success(array('batch_jobs' => $batch_jobs));
    }

    /**
     * Rewrite post content using the selected API.
     *
     * @since    1.0.0
     * @param    string $content The content to rewrite.
     * @param    string $api     The API provider to use.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    private function rewrite_post_content($content, $api, $style) {
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