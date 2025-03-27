<?php
/**
 * Anthropic API Provider for Article Rewriter.
 *
 * @link       https://widigital.com
 * @since      1.1.0 // Version matches abstract class introduction
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes/api-providers
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Ensure the abstract class is loaded (though service class should handle this)
// require_once plugin_dir_path( __FILE__ ) . 'abstract-api-provider.php';

class Anthropic_Provider extends Abstract_API_Provider {

    protected function get_api_key_option_name() {
        return 'article_rewriter_anthropic_api_key';
    }

    protected function get_endpoint_url() {
        return 'https://api.anthropic.com/v1/messages';
    }

    protected function get_model_name() {
        // TODO: Make this configurable via settings?
        // Using Opus as default for now, matching the placeholder
        return get_option('article_rewriter_anthropic_model', 'claude-3-opus-20240229'); 
    }

     protected function get_error_prefix() {
        return 'anthropic';
    }

     protected function get_provider_name() {
        return 'Anthropic';
    }

    /**
     * Makes the actual HTTP request to the Anthropic API endpoint.
     * Overrides the method from Abstract_API_Provider.
     *
     * @since    1.1.0
     * @param    string $api_key The API key.
     * @param    array  $data    The data payload prepared by the abstract class's rewrite method.
     * @return   string|WP_Error The rewritten content or an error.
     */
    protected function _make_api_request($api_key, $data) {
        // Transform data to Anthropic format
        $anthropic_data = [
            'model' => $this->get_model_name(),
            'max_tokens' => isset($data['max_tokens']) ? intval($data['max_tokens']) : 4000, // Use value from abstract class if set
            'temperature' => isset($data['temperature']) ? floatval($data['temperature']) : 0.7, // Use value from abstract class if set
            'messages' => [
                // Ensure user message is present and correctly formatted
                [
                    'role' => 'user',
                    'content' => isset($data['messages'][1]['content']) ? $data['messages'][1]['content'] : '' 
                ]
            ],
            // Add system prompt if present
            'system' => isset($data['messages'][0]['content']) ? $data['messages'][0]['content'] : '' 
        ];

        // Remove empty system prompt if not provided
        if (empty($anthropic_data['system'])) {
            unset($anthropic_data['system']);
        }

        $response = wp_remote_post(
            $this->get_endpoint_url(),
            [
                'headers' => [
                    'x-api-key' => $api_key,
                    'anthropic-version' => '2023-06-01', // Required header for Anthropic
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($anthropic_data),
                'timeout' => 60, // Consider making timeout configurable
            ]
        );

        // --- Standard Error Handling (copied & adapted from abstract class) ---
        if (is_wp_error($response)) {
            // Log the specific WP_Error message if needed
            // error_log('Anthropic API Request Error: ' . $response->get_error_message());
            return new WP_Error($this->get_error_prefix() . '_request_failed', __('API request failed.', 'article-rewriter') . ' ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
             $error_message = sprintf(__('%s API error: ', 'article-rewriter'), $this->get_provider_name()) . $response_code;
             if ($body) {
                 $error_data = json_decode($body, true);
                 // Anthropic error structure might be different, adjust as needed
                 if (isset($error_data['error']['message'])) { // Check standard OpenAI-like error first
                      $error_message .= ' - ' . $error_data['error']['message'];
                 } elseif (isset($error_data['type']) && $error_data['type'] === 'error' && isset($error_data['error']['type'])) { // Check Anthropic specific error structure
                     $error_message .= ' - (' . $error_data['error']['type'] . ') ' . $error_data['error']['message'];
                 } else {
                      // Attempt to include raw body if JSON parsing fails or structure is unexpected
                      $error_message .= ' - ' . substr(strip_tags($body), 0, 200); // Limit raw body length
                 }
             }
             // error_log('Anthropic API Response Error: ' . $error_message); // Log error
             return new WP_Error($this->get_error_prefix() . '_response_error', $error_message);
        }
        // --- End Standard Error Handling ---


        $response_data = json_decode($body, true);
        
        // Anthropic-specific response structure check
        if (!isset($response_data['content'][0]['text'])) {
             // Log the full response for debugging if needed
             // error_log('Anthropic Invalid Response Structure: ' . $body);
            return new WP_Error($this->get_error_prefix() . '_invalid_response', sprintf(__('Invalid response structure from %s API.', 'article-rewriter'), $this->get_provider_name()));
        }

        return $response_data['content'][0]['text'];
    }
}