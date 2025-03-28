<?php
/**
 * The license functionality of the plugin.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 */

/**
 * The license functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the license management.
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 * @author     WiDigital <info@widigital.com>
 */
class Article_Rewriter_License {

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
     * The license server URL.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $license_server    The license server URL.
     */
    private $license_server;

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
        // Use the correct local development URL
        $this->license_server = 'http://php-license-server.test/'; 
    }

    /**
     * Handle the license activation.
     *
     * @since    1.0.0
     */
    public function handle_activate_license() {
        // Verify the nonce - Use the nonce created in Article_Rewriter_Admin::enqueue_scripts
        if (!check_ajax_referer('article_rewriter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Check if purchase code is provided
        if (empty($_POST['purchase_code'])) {
            wp_send_json_error(array('message' => __('Please enter a purchase code.', 'article-rewriter')));
        }

        // Sanitize the purchase code
        $purchase_code = sanitize_text_field($_POST['purchase_code']);

        // Verify the purchase code with the license server
        $response = $this->verify_purchase_code($purchase_code); // Placeholder logic
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        // Check server response structure and 'success' flag
        if (isset($response['success']) && $response['success']) {
            // Save the license information
            update_option('article_rewriter_license_key', $purchase_code);
            update_option('article_rewriter_license_status', 'active');

            // Get the current domain
            $domain = home_url();

            // Save the domain and activation date (use data from response if available)
            $expires_at = $response['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+1 year', current_time('timestamp')));
            $activation_data = array(
                'domain' => $domain,
                'activated_at' => current_time('mysql'),
                'expires_at' => $expires_at,
            );

            // Save the activation data
            foreach ($activation_data as $key => $value) {
                update_option('article_rewriter_license_' . $key, $value);
            }

            // Schedule a daily check for license validity
            if (!wp_next_scheduled('article_rewriter_license_check')) {
                wp_schedule_event(time(), 'daily', 'article_rewriter_license_check');
            }

            // Send success response
            wp_send_json_success(array('message' => __('License activated successfully.', 'article-rewriter')));

        } else {
            // Handle verification failure from server (if implemented)
             wp_send_json_error(array('message' => $response['message'] ?? __('License verification failed.', 'article-rewriter')));
        }
        // Ensure wp_die is called for AJAX handlers
        wp_die(); 
    }

    /**
     * Handle the license deactivation.
     *
     * @since    1.0.0
     */
    public function handle_deactivate_license() {
        // Verify the nonce - Use the nonce name sent by JS
        // Note: JS uses 'article_rewriter_nonce', let's assume that's correct for both activate/deactivate
        if (!check_ajax_referer('article_rewriter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Get the license key
        $license_key = get_option('article_rewriter_license_key');

        // Deactivate the license with the license server (Placeholder logic)
        $response = $this->deactivate_license($license_key);

        // Assuming placeholder deactivation is successful
        if (isset($response['success']) && $response['success']) {
            // Update the license status
            update_option('article_rewriter_license_status', 'inactive');

            // Clear the scheduled license check
            $timestamp = wp_next_scheduled('article_rewriter_license_check');
            if ($timestamp) {
                wp_clear_scheduled_hook('article_rewriter_license_check');
            }

            // Send success response
            wp_send_json_success(array('message' => __('License deactivated successfully.', 'article-rewriter')));
        } else {
             // Handle deactivation failure from server (if implemented)
             wp_send_json_error(array('message' => $response['message'] ?? __('License deactivation failed.', 'article-rewriter')));
        }
    }

    /**
     * Check the license validity.
     *
     * @since    1.0.0
     */
    public function check_license() {
        // Get the license key and status
        $license_key = get_option('article_rewriter_license_key');
        $license_status = get_option('article_rewriter_license_status');

        // If the license is not active, no need to check
        if ($license_status !== 'active' || empty($license_key)) {
            return;
        }

        // Verify the license status with the license server using the /verify endpoint.
        $response = $this->_call_verify_endpoint($license_key); // Call new helper method

        // Handle potential errors during the server check
        if (is_wp_error($response)) {
            // Log the error?
            // error_log('Article Rewriter: Daily license check failed. ' . $response->get_error_message());
            // Optionally, decide if connection errors should immediately deactivate, or just retry later.
            // For now, let's assume connection errors don't change the status, allowing grace period.
            return; // Exit without changing status on connection error
        }

        // Process the successful response from the server
        if (isset($response['success']) && $response['success']) {
            // Server confirms the license is valid in some way
            // Check the specific status returned by the server (assuming 'status' key)
            $server_status = $response['status'] ?? 'inactive'; // Default to inactive if status key missing

            if ($server_status === 'active') {
                // Server says active. Update local status and expiry if provided.
                update_option('article_rewriter_license_status', 'active');
                if (isset($response['expires_at'])) {
                    update_option('article_rewriter_license_expires_at', $response['expires_at']);
                }
            } elseif ($server_status === 'expired') {
                // Server says expired.
                update_option('article_rewriter_license_status', 'expired');
            } else {
                // Server says inactive, revoked, or any other non-active status.
                update_option('article_rewriter_license_status', 'inactive');
            }
        } else {
            // Server responded successfully but indicated verification failure (e.g., invalid key, domain mismatch)
            // error_log('Article Rewriter: License verification failed during daily check. Message: ' . ($response['message'] ?? 'Unknown reason'));
            update_option('article_rewriter_license_status', 'inactive');
        }
    }

    /**
     * Display admin notices for license status.
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        // Get the license status
        $license_status = get_option('article_rewriter_license_status');

        // Get the current screen
        $screen = get_current_screen();

        // Only show the notice on the plugin's pages or if the license is not active
        if (strpos($screen->id, 'article-rewriter') === false && $license_status === 'active') {
            return;
        }

        // If the license is not active, show a notice
        if ($license_status !== 'active') {
            echo '<div class="notice notice-error"><p>';
            printf(
                __('Your Article Rewriter license is not active. Please <a href="%s">activate your license</a> to use all features.', 'article-rewriter'),
                admin_url('admin.php?page=article-rewriter-license'),
                __('Activate License', 'article-rewriter')
            );
            echo '</p></div>';
        }

        // If the license is expired, show a notice
        if ($license_status === 'expired') {
            echo '<div class="notice notice-error"><p>';
            printf(
                __('Your Article Rewriter license has expired. Please <a href="%s">renew your license</a> to continue using all features.', 'article-rewriter'),
                admin_url('admin.php?page=article-rewriter-license'),
                __('Renew License', 'article-rewriter')
            );
            echo '</p></div>';
        }
    }

    /**
     * Verify the purchase code with the license server.
     *
     * @since    1.0.0
     * @param    string $purchase_code The purchase code to verify.
     * @return   array|WP_Error The response from the license server or an error.
     */
    private function verify_purchase_code($purchase_code) {
        // Removed API Key check

        $url = trailingslashit($this->license_server) . 'api/v1/licenses/activate'; // Assumed endpoint
        $domain = home_url();

        $body = array(
            'purchase_code' => $purchase_code, // Changed key name from licenseKey
            'domain' => $domain,             // Changed from domainName
            'pluginVersion' => $this->version,
            'product_id' => $this->plugin_name // Changed from product
        );

        $response = wp_remote_post($url, array(
            'timeout' => 30, // Set a reasonable timeout
            'headers' => array(
                'Content-Type' => 'application/json',
                // Removed X-API-Key header
                'Accept' => 'application/json'
            ),
            'body' => json_encode($body),
        ));

        // Handle WP HTTP API errors
        if (is_wp_error($response)) {
            // Optional logging: error_log('Article Rewriter: License activation request failed. ' . $response->get_error_message());
            return new WP_Error('request_failed', __('Could not connect to the license server.', 'article-rewriter') . ' (' . $response->get_error_code() . ' - ' . $response->get_error_message() . ')');
        }

        // Handle non-200 HTTP status codes
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            $error_message = sprintf(__('License server returned an error (Code: %d, Message: %s ).', 'article-rewriter'), $response_code, $response_body);
            $server_data = json_decode($response_body, true);
            if (isset($server_data['message'])) {
                $error_message .= ' ' . sanitize_text_field($server_data['message']);
            }
            // Optional logging: error_log('Article Rewriter: License activation failed. Server response: ' . $response_code . ' - ' . $response_body);
            return new WP_Error('server_error_' . $response_code, $error_message);
        }

        // Decode the successful JSON response
        $data = json_decode($response_body, true);
        if (is_null($data)) {
            // Optional logging: error_log('Article Rewriter: Failed to decode JSON response from license server: ' . $response_body);
            return new WP_Error('invalid_response', __('Received an invalid response from the license server.', 'article-rewriter'));
        }

        // Return the parsed data (expecting keys like 'success', 'message', 'licenseKey', 'expiresAt')
        return $data;
    }

    /**
     * Deactivate the license with the license server.
     *
     * @since    1.0.0
     * @param    string $license_key The license key to deactivate.
     * @return   array|WP_Error The response from the license server or an error.
     */
    private function deactivate_license($license_key) {
         // Removed API Key check

        if (empty($license_key)) {
             return new WP_Error('missing_license_key', __('License key not found for deactivation.', 'article-rewriter'));
        }

        $url = trailingslashit($this->license_server) . 'api/v1/licenses/deactivate'; // Assumed endpoint
        $domain = home_url();

        $body = array(
            'purchase_code' => $license_key, // Changed key name from licenseKey to purchaseCode
            'domain' => $domain,             // Changed from domainName
            'product_id' => $this->plugin_name // Changed from product
        );

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                // Removed X-API-Key header
                'Accept' => 'application/json'
            ),
            'body' => json_encode($body),
        ));

        // Handle WP HTTP API errors
        if (is_wp_error($response)) {
            // Optional logging: error_log('Article Rewriter: License deactivation request failed. ' . $response->get_error_message());
            return new WP_Error('request_failed', __('Could not connect to the license server.', 'article-rewriter') . ' (' . $response->get_error_code() . ')');
        }

        // Handle non-200 HTTP status codes
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            $error_message = sprintf(__('License server returned an error (Code: %d).', 'article-rewriter'), $response_code);
            $server_data = json_decode($response_body, true);
            if (isset($server_data['message'])) {
                $error_message .= ' ' . sanitize_text_field($server_data['message']);
            }
             // Optional logging: error_log('Article Rewriter: License deactivation failed. Server response: ' . $response_code . ' - ' . $response_body);
            return new WP_Error('server_error_' . $response_code, $error_message);
        }

        // Decode the successful JSON response
        $data = json_decode($response_body, true);
        if (is_null($data)) {
            // Optional logging: error_log('Article Rewriter: Failed to decode JSON response from license server: ' . $response_body);
            return new WP_Error('invalid_response', __('Received an invalid response from the license server.', 'article-rewriter'));
        }

        // Return the parsed data (expecting keys like 'success', 'message')
        return $data;
    }

    /**
     * Call the license server's verify endpoint.
     *
     * @since    1.1.1 // Assuming version bump for this change
     * @access   private
     * @param    string $license_key The license key (purchase code) to verify.
     * @return   array|WP_Error The response from the license server or an error.
     */
    private function _call_verify_endpoint($license_key) {
        // Removed API Key check

        if (empty($license_key)) {
             return new WP_Error('missing_license_key', __('License key not found for verification.', 'article-rewriter'));
        }

        $url = trailingslashit($this->license_server) . 'api/v1/licenses/verify'; // Target the /verify endpoint
        $domain = home_url();

        $body = array(
            'purchase_code' => $license_key, // Changed key name from licenseKey to purchaseCode
            'domain' => $domain,             // Changed from domainName
            'product_id' => $this->plugin_name // Changed from product
        );

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                 // Removed X-API-Key header
                'Accept' => 'application/json'
            ),
            'body' => json_encode($body),
        ));

        // Handle WP HTTP API errors
        if (is_wp_error($response)) {
            return new WP_Error('request_failed', __('Could not connect to the license server for verification.', 'article-rewriter') . ' (' . $response->get_error_code() . ')');
        }

        // Handle non-200 HTTP status codes
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            $error_message = sprintf(__('License server returned an error during verification (Code: %d).', 'article-rewriter'), $response_code);
            $server_data = json_decode($response_body, true);
            if (isset($server_data['message'])) {
                $error_message .= ' ' . sanitize_text_field($server_data['message']);
            }
            return new WP_Error('server_error_' . $response_code, $error_message);
        }

        // Decode the successful JSON response
        $data = json_decode($response_body, true);
        if (is_null($data)) {
            return new WP_Error('invalid_response', __('Received an invalid verification response from the license server.', 'article-rewriter'));
        }

        // Return the parsed data (expecting keys like 'success', 'status', 'expiresAt')
        return $data;
    }
}