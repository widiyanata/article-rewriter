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
        $this->license_server = 'https://widigital.com/license-server/';
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

        // Assuming placeholder verification is successful
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

        // Verify the license with the license server
        $response = $this->verify_purchase_code($license_key);
        if (is_wp_error($response)) {
            // If there's an error, deactivate the license
            update_option('article_rewriter_license_status', 'inactive');
            return;
        }

        // Check if the license has expired
        $expires_at = get_option('article_rewriter_license_expires_at');
        if (!empty($expires_at) && strtotime($expires_at) < current_time('timestamp')) {
            // If the license has expired, deactivate it
            update_option('article_rewriter_license_status', 'expired');
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
        // This is a placeholder for the actual verification process
        // In a real implementation, you would make an API call to the Envato API
        // or your own license server to verify the purchase code

        // For demonstration purposes, we'll just check if the purchase code is not empty
        if (empty($purchase_code)) {
            return new WP_Error('invalid_purchase_code', __('Invalid purchase code.', 'article-rewriter'));
        }

        // Check if the purchase code is already in use on another domain
        // This would involve checking with your license server
        $domain = home_url();

        // For demonstration purposes, we'll just return a success response
        return array(
            'success' => true,
            'message' => 'Purchase code verified successfully.',
            'domain' => $domain,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year', current_time('timestamp'))),
        );
    }

    /**
     * Deactivate the license with the license server.
     *
     * @since    1.0.0
     * @param    string $license_key The license key to deactivate.
     * @return   array|WP_Error The response from the license server or an error.
     */
    private function deactivate_license($license_key) {
        // This is a placeholder for the actual deactivation process
        // In a real implementation, you would make an API call to your license server
        // to deactivate the license for this domain

        // For demonstration purposes, we'll just return a success response
        return array(
            'success' => true,
            'message' => 'License deactivated successfully.',
        );
    }
}