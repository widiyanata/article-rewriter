<?php
/**
 * OpenAI API Provider for Article Rewriter.
 *
 * @link       https://widigital.com
 * @since      1.1.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes/api-providers
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Ensure the abstract class is loaded
require_once plugin_dir_path( __FILE__ ) . 'abstract-api-provider.php';

class OpenAI_Provider extends Abstract_API_Provider {

    protected function get_api_key_option_name() {
        return 'article_rewriter_openai_api_key';
    }

    protected function get_endpoint_url() {
        return 'https://api.openai.com/v1/chat/completions';
    }

    protected function get_model_name() {
        // TODO: Make this configurable via settings?
        return 'gpt-4'; 
    }

     protected function get_error_prefix() {
        return 'openai';
    }

     protected function get_provider_name() {
        return 'OpenAI';
    }
}