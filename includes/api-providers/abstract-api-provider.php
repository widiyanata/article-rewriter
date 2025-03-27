<?php
/**
 * Abstract Base Class for API Providers.
 *
 * @link       https://widigital.com
 * @since      1.1.1 // Version bump for further refactoring
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes/api-providers
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

abstract class Abstract_API_Provider {

    protected $service; // Reference to the main service

    /**
     * Constructor.
     * @param Article_Rewriter_Service $service Instance of the main service class.
     */
    public function __construct( $service ) {
        $this->service = $service;
    }

    /**
     * Abstract method to get the option name for the API key.
     * @return string Option name.
     */
    abstract protected function get_api_key_option_name();

    /**
     * Abstract method to get the API endpoint URL.
     * @return string Endpoint URL.
     */
    abstract protected function get_endpoint_url();

    /**
     * Abstract method to get the model name for the API request.
     * @return string Model name.
     */
    abstract protected function get_model_name();

    /**
     * Abstract method to get the error code prefix for WP_Error.
     * @return string Error prefix (e.g., 'openai_error').
     */
    abstract protected function get_error_prefix();

    /**
     * Abstract method to get the provider name for user-facing errors.
     * @return string Provider name (e.g., 'OpenAI').
     */
    abstract protected function get_provider_name();


    /**
     * Rewrite content using the specific API implementation.
     * This method now primarily calls the shared request logic.
     *
     * @since    1.1.1
     * @param    string $content The content to rewrite.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    public function rewrite($content, $style) {
        $api_key = get_option($this->get_api_key_option_name());
        
        if (empty($api_key)) {
            return new WP_Error('api_key_missing', sprintf(__('%s API key is missing.', 'article-rewriter'), $this->get_provider_name()));
        }

        $prompt = $this->service->get_prompt_for_style($style);
        
        $data = array(
            'model' => $this->get_model_name(),
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
            // These could also be made abstract or configurable if they differ significantly
            'temperature' => 0.7, 
            'max_tokens' => 4000, 
        );

        return $this->_make_api_request($api_key, $data);
    }

    /**
     * Makes the actual HTTP request to the API endpoint.
     *
     * @since    1.1.1
     * @param    string $api_key The API key.
     * @param    array  $data    The data payload for the request body.
     * @return   string|WP_Error The rewritten content or an error.
     */
    protected function _make_api_request($api_key, $data) {
        $response = wp_remote_post(
            $this->get_endpoint_url(),
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
            // Log the specific WP_Error message if needed
            return new WP_Error($this->get_error_prefix() . '_request_failed', __('API request failed.', 'article-rewriter') . ' ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
             $error_message = sprintf(__('%s API error: ', 'article-rewriter'), $this->get_provider_name()) . $response_code;
             if ($body) {
                 $error_data = json_decode($body, true);
                 if (isset($error_data['error']['message'])) {
                      $error_message .= ' - ' . $error_data['error']['message'];
                 } else {
                      // Attempt to include raw body if JSON parsing fails or structure is unexpected
                      $error_message .= ' - ' . substr(strip_tags($body), 0, 200); // Limit raw body length
                 }
             }
             return new WP_Error($this->get_error_prefix() . '_response_error', $error_message);
        }

        $data = json_decode($body, true);
        
        // Standard OpenAI/DeepSeek structure check
        if (!isset($data['choices'][0]['message']['content'])) {
             // Log the full response for debugging if needed
            return new WP_Error($this->get_error_prefix() . '_invalid_response', sprintf(__('Invalid response structure from %s API.', 'article-rewriter'), $this->get_provider_name()));
        }

        return $data['choices'][0]['message']['content'];
    }
}