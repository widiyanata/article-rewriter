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
     * The shared service class instance.
     *
     * @since    1.1.0
     * @access   private
     * @var      Article_Rewriter_Service    $service    Shared service class.
     */
    private $service;

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

        // Instantiate the service class
        // Ensure the service class file is loaded before this class is instantiated
        if (!class_exists('Article_Rewriter_Service')) {
             require_once plugin_dir_path( __FILE__ ) . 'class-article-rewriter-service.php';
        }
        $this->service = new Article_Rewriter_Service();
    }

    /**
     * Handle the start of a batch job (AJAX).
     *
     * @since    1.0.0
     */
    public function handle_start_batch() {
        // Verify the nonce - Use the nonce created in Article_Rewriter_Admin::enqueue_scripts
        if (!check_ajax_referer('article_rewriter_nonce', 'nonce', false)) {
             wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Check if license is active
        $license_status = get_option('article_rewriter_license_status');
        if ($license_status !== 'active') {
            wp_send_json_error(array('message' => __('Your license is not active. Please activate your license to use this feature.', 'article-rewriter')));
        }

        // Check if post IDs are provided
        if (empty($_POST['post_ids'])) {
            wp_send_json_error(array('message' => __('Please select at least one post.', 'article-rewriter')));
        }

        // Get the form data
        $post_ids = array_map('intval', $_POST['post_ids']);
        $api = sanitize_text_field($_POST['api'] ?? get_option('article_rewriter_default_api', 'openai'));
        $style = sanitize_text_field($_POST['style'] ?? get_option('article_rewriter_default_style', 'standard'));

        // Get the posts to rewrite
        $posts = $this->get_posts_for_batch($post_ids);

        if (empty($posts)) {
             wp_send_json_error(array('message' => __('No valid posts found for the selected IDs.', 'article-rewriter')));
        }

        // Create the batch job
        $batch_id = $this->create_batch_job($posts, $api, $style);

        if (!$batch_id) {
             wp_send_json_error(array('message' => __('Failed to create batch job.', 'article-rewriter')));
        }

        // Schedule the batch processing
        $this->schedule_batch_processing($batch_id);

        // Send success response
        wp_send_json_success(array(
            'message' => __('Batch job started successfully.', 'article-rewriter'),
            'batch_id' => $batch_id
        ));
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
     * @return   int|false    The batch ID or false on failure.
     */
    private function create_batch_job($posts, $api, $style) {
        global $wpdb;

        // Get the current user ID
        $user_id = get_current_user_id();

        // Insert the batch job
        $table_name = $wpdb->prefix . 'article_rewriter_batch';
        $inserted = $wpdb->insert(
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

        if (!$inserted) {
            return false;
        }

        $batch_id = $wpdb->insert_id;

        // Insert the batch items
        $items_table_name = $wpdb->prefix . 'article_rewriter_batch_items';
        foreach ($posts as $post) {
            $wpdb->insert(
                $items_table_name,
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
        // Schedule the batch processing if not already scheduled
        if (!wp_next_scheduled('article_rewriter_process_batch', array($batch_id))) {
             wp_schedule_single_event(time(), 'article_rewriter_process_batch', array($batch_id));
        }
    }

    /**
     * Process a batch job (WP Cron callback).
     *
     * @since    1.0.0
     * @param    int $batch_id The batch ID.
     */
    public function process_batch($batch_id) {
        global $wpdb;

        $batch_table = $wpdb->prefix . 'article_rewriter_batch';
        $items_table = $wpdb->prefix . 'article_rewriter_batch_items';

        // Get the batch job
        $batch = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $batch_table WHERE id = %d",
                $batch_id
            )
        );

        // Exit if job not found or already completed/cancelled
        if (!$batch || in_array($batch->status, array('completed', 'cancelled', 'failed'))) {
            return;
        }

        // Update the batch status to 'processing' if it's 'pending'
        if ($batch->status === 'pending') {
            $wpdb->update(
                $batch_table,
                array(
                    'status' => 'processing',
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $batch_id),
                array('%s', '%s'),
                array('%d')
            );
        }

        // Get a limited number of pending items to process in this run
        $batch_size = get_option('article_rewriter_batch_size', 1); // Process one item per run by default
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $items_table WHERE batch_id = %d AND status = 'pending' ORDER BY id ASC LIMIT %d",
                $batch_id,
                $batch_size
            )
        );

        if (empty($items)) {
            // No pending items left, mark job as completed
            $wpdb->update(
                $batch_table,
                array(
                    'status' => 'completed',
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $batch_id),
                array('%s', '%s'),
                array('%d')
            );
            return; // Stop processing
        }

        $processed_in_run = 0;
        $failed_in_run = 0;

        // Process each item
        foreach ($items as $item) {
            $post = get_post($item->post_id);
            $item_status = 'failed'; // Default to failed

            if ($post) {
                // Use the service class to rewrite content
                $rewritten_content = $this->service->rewrite_content($post->post_content, $batch->api, $batch->style);

                if (!is_wp_error($rewritten_content)) {
                    $post_data = array(
                        'ID' => $post->ID,
                        'post_content' => $rewritten_content,
                    );
                    $result = wp_update_post($post_data);

                    if (!is_wp_error($result)) {
                        // Use the service class to save history
                        $this->service->save_history($post->ID, $batch->api, $batch->style, $rewritten_content);
                        $item_status = 'completed';
                        $processed_in_run++;
                    } else {
                        // Log wp_update_post error if needed
                        $failed_in_run++;
                    }
                } else {
                    // Log rewrite error if needed
                    $failed_in_run++;
                }
            } else {
                 $failed_in_run++; // Post not found
            }

            // Update the item status
            $wpdb->update(
                $items_table,
                array(
                    'status' => $item_status,
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $item->id),
                array('%s', '%s'),
                array('%d')
            );
        }

        // Update the main batch processed count
        if ($processed_in_run > 0) {
             $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $batch_table SET processed = processed + %d, updated_at = %s WHERE id = %d",
                    $processed_in_run,
                    current_time('mysql'),
                    $batch_id
                )
            );
        } else {
             // Update timestamp even if only failures occurred
             $wpdb->update(
                $batch_table,
                array('updated_at' => current_time('mysql')),
                array('id' => $batch_id),
                array('%s'),
                array('%d')
            );
        }


        // Check if there are still pending items
        $remaining_pending = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $items_table WHERE batch_id = %d AND status = 'pending'",
                $batch_id
            )
        );

        if ($remaining_pending > 0) {
            // Schedule the next run if not already scheduled
            if (!wp_next_scheduled('article_rewriter_process_batch', array($batch_id))) {
                 wp_schedule_single_event(time() + 5, 'article_rewriter_process_batch', array($batch_id)); // Short delay for next batch
            }
        } else {
            // No more pending items, mark job as completed
             $wpdb->update(
                $batch_table,
                array(
                    'status' => 'completed',
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $batch_id),
                array('%s', '%s'),
                array('%d')
            );
        }
    }

    /**
     * Handle the AJAX request to get batch jobs status.
     *
     * @since    1.0.0
     */
    public function handle_get_batch_jobs_status() {
        // Verify the nonce
        if (!check_ajax_referer('article_rewriter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Get the batch jobs
        global $wpdb;
        $batch_table = $wpdb->prefix . 'article_rewriter_batch';
        $batch_jobs = $wpdb->get_results(
            "SELECT * FROM {$batch_table} ORDER BY created_at DESC",
            ARRAY_A
        );

        if ($batch_jobs === null) { // Check for DB error
             wp_send_json_error(array('message' => __('Database error retrieving batch jobs.', 'article-rewriter')));
        }

        $formatted_jobs = array();
        if (!empty($batch_jobs)) {
            foreach ($batch_jobs as $job) {
                 $job['created_at'] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($job['created_at']));
                 $job['updated_at'] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($job['updated_at']));
                 $job['status_label'] = ucfirst($job['status']);
                 $job['progress'] = ($job['total'] > 0) ? round(($job['processed'] / $job['total']) * 100) : 0;
                 $formatted_jobs[] = $job;
            }
        }


        wp_send_json_success($formatted_jobs); // Send formatted jobs or empty array
    }

    /**
     * Handle the AJAX request to get details for a specific batch job.
     *
     * @since    1.0.0
     */
    public function handle_get_batch_job() {
        // Verify the nonce
        if (!check_ajax_referer('article_rewriter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Check if job ID is provided
        if (empty($_POST['job_id'])) {
            wp_send_json_error(array('message' => __('Batch job ID not provided.', 'article-rewriter')));
        }

        $job_id = intval($_POST['job_id']);

        global $wpdb;

        // Get the batch job details
        $batch_table = $wpdb->prefix . 'article_rewriter_batch';
        $job = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $batch_table WHERE id = %d", $job_id),
            ARRAY_A
        );

        if (!$job) {
            wp_send_json_error(array('message' => __('Batch job not found.', 'article-rewriter')));
        }

        // Get the batch job items
        $items_table = $wpdb->prefix . 'article_rewriter_batch_items';
        $items = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $items_table WHERE batch_id = %d ORDER BY id ASC", $job_id),
            ARRAY_A
        );

        // Add post details to items
        $job['posts'] = array();
        if (!empty($items)) {
            foreach ($items as $item) {
                $post = get_post($item['post_id']);
                if ($post) {
                     $job['posts'][] = array(
                         'id' => $item['id'],
                         'post_id' => $item['post_id'],
                         'title' => get_the_title($post),
                         'edit_url' => get_edit_post_link($post->ID, 'raw'),
                         'view_url' => get_permalink($post->ID),
                         'status' => $item['status'],
                         'status_label' => ucfirst($item['status']), // Simple label
                         'updated_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['updated_at'])),
                     );
                } else {
                     $job['posts'][] = array(
                         'id' => $item['id'],
                         'post_id' => $item['post_id'],
                         'title' => __('Post not found', 'article-rewriter'),
                         'edit_url' => '#',
                         'view_url' => '#',
                         'status' => $item['status'],
                         'status_label' => ucfirst($item['status']),
                         'updated_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['updated_at'])),
                     );
                }
            }
        }

        // Format dates
        $job['created_at'] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($job['created_at']));
        $job['updated_at'] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($job['updated_at']));
        $job['status_label'] = ucfirst($job['status']); // Simple label
        $job['progress'] = ($job['total'] > 0) ? round(($job['processed'] / $job['total']) * 100) : 0;


        wp_send_json_success($job);
    }

    /**
     * Handle the AJAX request to cancel a batch job.
     *
     * @since    1.0.0
     */
    public function handle_cancel_batch_job() {
        // Verify the nonce
        if (!check_ajax_referer('article_rewriter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Check if job ID is provided
        if (empty($_POST['job_id'])) {
            wp_send_json_error(array('message' => __('Batch job ID not provided.', 'article-rewriter')));
        }

        $job_id = intval($_POST['job_id']);

        global $wpdb;
        $batch_table = $wpdb->prefix . 'article_rewriter_batch';
        $items_table = $wpdb->prefix . 'article_rewriter_batch_items';

        // Get the current status
        $current_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $batch_table WHERE id = %d", $job_id));

        if (!$current_status) {
             wp_send_json_error(array('message' => __('Batch job not found.', 'article-rewriter')));
        }

        if ($current_status !== 'pending' && $current_status !== 'processing') {
             wp_send_json_error(array('message' => __('Only pending or processing jobs can be cancelled.', 'article-rewriter')));
        }

        // Update batch job status to 'cancelled'
        $updated = $wpdb->update(
            $batch_table,
            array(
                'status' => 'cancelled',
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $job_id),
            array('%s', '%s'),
            array('%d')
        );

        if (false === $updated) {
             wp_send_json_error(array('message' => __('Failed to update batch job status.', 'article-rewriter')));
        }

        // Update pending items status to 'cancelled'
        $wpdb->update(
            $items_table,
            array(
                'status' => 'cancelled',
                'updated_at' => current_time('mysql'),
            ),
            array('batch_id' => $job_id, 'status' => 'pending'), // Only cancel pending items
            array('%s', '%s'),
            array('%d', '%s')
        );

        // Clear any scheduled cron job for this batch
        wp_clear_scheduled_hook('article_rewriter_process_batch', array($job_id));

        wp_send_json_success(array('message' => __('Batch job cancelled successfully.', 'article-rewriter')));
    }

    /**
     * Handle the AJAX request to delete a batch job.
     *
     * @since    1.0.0
     */
    public function handle_delete_batch_job() {
         // Verify the nonce
        if (!check_ajax_referer('article_rewriter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'article-rewriter')));
        }

        // Check if user has permission - Changed to manage_options for deletion
        if (!current_user_can('manage_options')) { 
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'article-rewriter')));
        }

        // Check if job ID is provided
        if (empty($_POST['job_id'])) {
            wp_send_json_error(array('message' => __('Batch job ID not provided.', 'article-rewriter')));
        }

        $job_id = intval($_POST['job_id']);

        global $wpdb;
        $batch_table = $wpdb->prefix . 'article_rewriter_batch';
        $items_table = $wpdb->prefix . 'article_rewriter_batch_items';

        // First, delete the items associated with the batch job
        $items_deleted = $wpdb->delete(
            $items_table,
            array('batch_id' => $job_id),
            array('%d')
        );

        // Then, delete the batch job itself
        $job_deleted = $wpdb->delete(
            $batch_table,
            array('id' => $job_id),
            array('%d')
        );

        if (false === $job_deleted) {
             wp_send_json_error(array('message' => __('Failed to delete batch job.', 'article-rewriter')));
        }

        // Clear any scheduled cron job just in case
        wp_clear_scheduled_hook('article_rewriter_process_batch', array($job_id));

        wp_send_json_success(array('message' => __('Batch job deleted successfully.', 'article-rewriter')));
    }

    // Removed duplicated methods: rewrite_post_content, rewrite_with_openai, rewrite_with_deepseek,
    // rewrite_with_anthropic, rewrite_with_gemini, get_prompt_for_style, save_history
    // These are now handled by Article_Rewriter_Service
}