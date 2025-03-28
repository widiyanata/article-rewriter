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

// Include provider classes
require_once plugin_dir_path( __FILE__ ) . 'api-providers/abstract-api-provider.php'; // Add abstract class include
require_once plugin_dir_path( __FILE__ ) . 'api-providers/class-openai-provider.php';
require_once plugin_dir_path( __FILE__ ) . 'api-providers/class-deepseek-provider.php';
require_once plugin_dir_path( __FILE__ ) . 'api-providers/class-anthropic-provider.php'; // Placeholder
require_once plugin_dir_path( __FILE__ ) . 'api-providers/class-gemini-provider.php'; // Placeholder


class Article_Rewriter_Service {

    /**
     * Holds instances of the API providers.
     * @var array<string, object>
     */
    private $providers = [];

    public function __construct() {
        // Instantiate providers and pass a reference to this service if needed by the provider
        $this->providers['openai'] = new OpenAI_Provider($this); // Pass $this as it uses get_prompt_for_style
        $this->providers['deepseek'] = new DeepSeek_Provider($this); // Pass $this as it uses get_prompt_for_style
        $this->providers['anthropic'] = new Anthropic_Provider($this); // Pass $this for consistency, even if placeholder doesn't use it
        $this->providers['gemini'] = new Gemini_Provider($this); // Pass $this for consistency, even if placeholder doesn't use it
    }

    /**
     * Rewrite content using the selected API provider.
     *
     * @since    1.1.0
     * @param    string $content The content to rewrite.
     * @param    string $api     The API provider to use.
     * @param    string $style   The rewriting style to use.
     * @return   string|WP_Error The rewritten content or an error.
     */
    public function rewrite_content($content, $api, $style) {
        // Sanitize API and Style parameters
        $api = sanitize_key($api);
        $style = sanitize_key($style);

        // Check if the provider instance exists and has the rewrite method
        if (isset($this->providers[$api]) && method_exists($this->providers[$api], 'rewrite')) {
            // Pass potentially unsanitized $content, but sanitized $api and $style
            return $this->providers[$api]->rewrite($content, $style); 
        } else {
            // Log this? Could indicate a configuration issue or attempt to use an unsupported API.
            return new WP_Error('invalid_api_provider', __('Invalid or unsupported API provider selected.', 'article-rewriter'));
        }
    }

    // Note: The individual private rewrite_with_* methods have been moved to their respective provider classes.

    /**
     * Get the prompt for the selected style. (Public, as providers need it)
     *
     * @since    1.1.0
     * @param    string $style The rewriting style.
     * @return   string The prompt.
     */
    public function get_prompt_for_style($style) { // Changed visibility to public
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