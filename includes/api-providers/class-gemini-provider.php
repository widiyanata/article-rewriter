<?php
/**
 * Google Gemini API Provider for Article Rewriter.
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

class Gemini_Provider extends Abstract_API_Provider {

    protected function get_api_key_option_name() {
        return 'article_rewriter_gemini_api_key';
    }

    protected function get_endpoint_url() {
        // Base URL, API key will be added as query param in _make_api_request
        // Model name is also added dynamically there.
        return 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->get_model_name() . ':generateContent';
    }

    protected function get_model_name() {
        // Retrieve the saved model option, fallback to 'gemini-1.5-pro'
        return get_option('article_rewriter_gemini_model', 'gemini-1.5-pro'); 
    }

     protected function get_error_prefix() {
        return 'gemini';
    }

     protected function get_provider_name() {
        return 'Google Gemini';
    }

    /**
     * Makes the actual HTTP request to the Google Gemini API endpoint.
     * Overrides the method from Abstract_API_Provider.
     *
     * @since    1.1.0
     * @param    string $api_key The API key.
     * @param    array  $data    The data payload prepared by the abstract class's rewrite method.
     * @return   string|WP_Error The rewritten content or an error.
     */
    protected function _make_api_request($api_key, $data) {
        // Transform data to Gemini format
        $gemini_data = [
            'contents' => [
                [
                    // Role is implicitly 'user' for the first message in this structure
                    'parts' => [
                        [
                            'text' => isset($data['messages'][1]['content']) ? $data['messages'][1]['content'] : '' // User message
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => isset($data['temperature']) ? floatval($data['temperature']) : 0.7, // Use value from abstract class if set
                'maxOutputTokens' => isset($data['max_tokens']) ? intval($data['max_tokens']) : 4000, // Use value from abstract class if set
                // Add other relevant config if needed, e.g., topP, topK
            ],
            // Add system prompt if present
            'systemInstruction' => isset($data['messages'][0]['content']) ? [
                'parts' => [
                    [
                        'text' => $data['messages'][0]['content'] // System message
                    ]
                ]
            ] : null // Set to null if no system prompt
        ];

        // Remove null systemInstruction if not provided
        if (is_null($gemini_data['systemInstruction'])) {
            unset($gemini_data['systemInstruction']);
        }

        // Add API key as query parameter to the dynamically generated URL
        $url = $this->get_endpoint_url() . '?key=' . urlencode($api_key);

        $response = wp_remote_post(
            $url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($gemini_data),
                'timeout' => 60, // Consider making timeout configurable
            ]
        );

        // --- Standard Error Handling (copied & adapted from abstract class) ---
        if (is_wp_error($response)) {
            // Log the specific WP_Error message if needed
            return new WP_Error($this->get_error_prefix() . '_request_failed', __('API request failed.', 'article-rewriter') . ' ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
             $error_message = sprintf(__('%s API error: ', 'article-rewriter'), $this->get_provider_name()) . $response_code;
             if ($body) {
                 $error_data = json_decode($body, true);
                 // Gemini error structure might be different, adjust as needed
                 if (isset($error_data['error']['message'])) { 
                      $error_message .= ' - ' . $error_data['error']['message'];
                 } else {
                      // Attempt to include raw body if JSON parsing fails or structure is unexpected
                      $error_message .= ' - ' . substr(strip_tags($body), 0, 200); // Limit raw body length
                 }
             }
             return new WP_Error($this->get_error_prefix() . '_response_error', $error_message);
        }
        // --- End Standard Error Handling ---


        $response_data = json_decode($body, true);
        
        // Gemini-specific response structure check
        // It might return no candidates if blocked, etc.
        if (!isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
             // Check for prompt feedback if no candidates
             if (isset($response_data['promptFeedback']['blockReason'])) {
                 $block_reason = $response_data['promptFeedback']['blockReason'];
                 $block_message = isset($response_data['promptFeedback']['blockReasonMessage']) ? $response_data['promptFeedback']['blockReasonMessage'] : 'Content blocked by safety settings.';
                 return new WP_Error($this->get_error_prefix() . '_content_blocked', sprintf(__('Content blocked by %s: %s', 'article-rewriter'), $this->get_provider_name(), $block_reason));
             }
             // Log the full response for debugging if structure is unexpected
            return new WP_Error($this->get_error_prefix() . '_invalid_response', sprintf(__('Invalid response structure from %s API.', 'article-rewriter'), $this->get_provider_name()));
        }

        return $response_data['candidates'][0]['content']['parts'][0]['text'];
    }
}