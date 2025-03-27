<?php
/**
 * Shared service class for Article Rewriter plugin.
 * Contains common logic for API interactions and history saving.
 *
 * @link       https://widigital.com
 * @since      1.1.0 // Assuming a version bump for refactoring
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Article_Rewriter_Service {

    /**
     * Rewrite content using the selected API.
     *
     * @since    1.1.0
     * @param    string $content The content to rewrite.
     * @param    string $api     The API provider to use.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    public function rewrite_content($content, $api, $style) {
        switch ($api) {
            case 'openai':
                return $this->rewrite_with_openai($content, $style);
            case 'deepseek':
                return $this->rewrite_with_deepseek($content, $style);
            case 'anthropic':
                return $this->rewrite_with_anthropic($content, $style); // Placeholder
            case 'gemini':
                return $this->rewrite_with_gemini($content, $style); // Placeholder
            default:
                return new WP_Error('invalid_api', __('Invalid API provider selected in service.', 'article-rewriter'));
        }
    }

    /**
     * Rewrite content using the OpenAI API.
     *
     * @since    1.1.0
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
            'model' => 'gpt-4', // Consider making model configurable
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
            'temperature' => 0.7, // Consider making temperature configurable
            'max_tokens' => 4000, // Consider making max_tokens configurable
        );

        $response = wp_remote_post(
            'https://api.openai.com/v1/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($data),
                'timeout' => 60, // Consider making timeout configurable
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error_message = __('OpenAI API error: ', 'article-rewriter') . $response_code;
            if ($body) {
                 $error_data = json_decode($body, true);
                 if (isset($error_data['error']['message'])) {
                      $error_message .= ' - ' . $error_data['error']['message'];
                 } else {
                      $error_message .= ' - ' . $body;
                 }
            }
            return new WP_Error('openai_error', $error_message);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            // Log the full response for debugging if needed
            return new WP_Error('openai_error', __('Invalid response structure from OpenAI API.', 'article-rewriter'));
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Rewrite content using the DeepSeek API.
     *
     * @since    1.1.0
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
            'model' => 'deepseek-chat', // Consider making model configurable
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
            'temperature' => 0.7, // Consider making temperature configurable
            'max_tokens' => 4000, // Consider making max_tokens configurable
        );

        $response = wp_remote_post(
            'https://api.deepseek.com/v1/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($data),
                'timeout' => 60, // Consider making timeout configurable
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
             $body = wp_remote_retrieve_body($response);
             $error_message = __('DeepSeek API error: ', 'article-rewriter') . $response_code;
             if ($body) {
                 $error_data = json_decode($body, true);
                 if (isset($error_data['error']['message'])) {
                      $error_message .= ' - ' . $error_data['error']['message'];
                 } else {
                      $error_message .= ' - ' . $body;
                 }
             }
             return new WP_Error('deepseek_error', $error_message);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
             // Log the full response for debugging if needed
            return new WP_Error('deepseek_error', __('Invalid response structure from DeepSeek API.', 'article-rewriter'));
        }

        return $data['choices'][0]['message']['content'];
    }

     /**
     * Rewrite content using the Anthropic API (Placeholder).
     *
     * @since    1.1.0
     * @param    string $content The content to rewrite.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    private function rewrite_with_anthropic($content, $style) {
        // Implementation for Anthropic API
        // This is a placeholder for future implementation
        // return new WP_Error('not_implemented', __('Anthropic API integration is not yet implemented.', 'article-rewriter'));
        return $content; // Returning original content for now
    }

    /**
     * Rewrite content using the Google Gemini API (Placeholder).
     *
     * @since    1.1.0
     * @param    string $content The content to rewrite.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    private function rewrite_with_gemini($content, $style) {
        // Implementation for Google Gemini API
        // This is a placeholder for future implementation
        // return new WP_Error('not_implemented', __('Google Gemini API integration is not yet implemented.', 'article-rewriter'));
        return $content; // Returning original content for now
    }

    /**
     * Get the prompt for the selected style.
     *
     * @since    1.1.0
     * @param    string $style The rewriting style.
     * @return   string The prompt.
     */
    private function get_prompt_for_style($style) {
        // Centralized prompt logic
        switch ($style) {
            case 'formal':
                return __('You are a professional content rewriter. Rewrite the following article in a formal, academic style while maintaining the same meaning. Use sophisticated vocabulary and complex sentence structures. Keep the same headings and formatting.', 'article-rewriter');
            case 'casual':
                return __('You are a professional content rewriter. Rewrite the following article in a casual, conversational style while maintaining the same meaning. Use simple language and a friendly tone. Keep the same headings and formatting.', 'article-rewriter');
            case 'creative':
                return __('You are a professional content rewriter. Rewrite the following article in a creative, engaging style while maintaining the same meaning. Use vivid language, metaphors, and storytelling techniques. Keep the same headings and formatting.', 'article-rewriter');
            case 'standard':
            default:
                return __('You are a professional content rewriter. Rewrite the following article while maintaining the same meaning, but using different wording and sentence structure. Keep the same headings and formatting.', 'article-rewriter');
        }
    }

    /**
     * Save the rewrite history.
     *
     * @since    1.1.0
     * @param    int    $post_id  The post ID.
     * @param    string $api      The API provider used.
     * @param    string $style    The rewriting style used.
     * @param    string $content  The rewritten content.
     * @return   int|false The ID of the inserted history record or false on failure.
     */
    public function save_history($post_id, $api, $style, $content) {
        global $wpdb;

        // If post_id is not provided or invalid, try to get it from the global post object if available
        if (empty($post_id) || !is_numeric($post_id) || $post_id <= 0) {
            $global_post = get_post();
            if ($global_post) {
                $post_id = $global_post->ID;
            } else {
                // Cannot determine post ID, maybe log this?
                return false;
            }
        }

        // Get the current user ID
        $user_id = get_current_user_id();
        if (empty($user_id)) {
             $user_id = 0; // Assign to system if user ID not found (e.g., cron)
        }

        // Check if history saving is enabled
        if ('yes' !== get_option('article_rewriter_save_history', 'yes')) {
            return false; // History saving disabled
        }

        // Insert the history record
        $table_name = $wpdb->prefix . 'article_rewriter_history';
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'post_id' => intval($post_id),
                'user_id' => intval($user_id),
                'api' => sanitize_text_field($api),
                'style' => sanitize_text_field($style),
                'content' => wp_kses_post($content), // Basic sanitization for post content
                'created_at' => current_time('mysql', 1), // Use GMT time
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );

        return $inserted ? $wpdb->insert_id : false;
    }
}