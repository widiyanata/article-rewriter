<?php
/**
 * Fired during plugin activation
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 * @author     WiDigital <info@widigital.com>
 */
class Article_Rewriter_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
    }

    /**
     * Create the necessary database tables.
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for storing rewrite history
        $table_history = $wpdb->prefix . 'article_rewriter_history';
        $sql_history = "CREATE TABLE $table_history (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            api varchar(50) NOT NULL,
            style varchar(50) NOT NULL,
            content longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Table for storing batch jobs
        $table_batch = $wpdb->prefix . 'article_rewriter_batch';
        $sql_batch = "CREATE TABLE $table_batch (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            api varchar(50) NOT NULL,
            style varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            total int(11) NOT NULL,
            processed int(11) NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";

        // Table for storing batch job items
        $table_batch_items = $wpdb->prefix . 'article_rewriter_batch_items';
        $sql_batch_items = "CREATE TABLE $table_batch_items (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            batch_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY batch_id (batch_id),
            KEY post_id (post_id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_history);
        dbDelta($sql_batch);
        dbDelta($sql_batch_items);

        add_option('article_rewriter_db_version', '1.0.0');
        add_option('article_rewriter_openai_api_key', '');
        add_option('article_rewriter_deepseek_api_key', '');
        add_option('article_rewriter_anthropic_api_key', '');
        add_option('article_rewriter_gemini_api_key', '');
        add_option('article_rewriter_default_api', 'openai');
        add_option('article_rewriter_default_style', 'standard');
    }

    /**
     * Set default options for the plugin.
     *
     * @since    1.0.0
     */
    private static function set_default_options() {
        // License options
        if (!get_option('article_rewriter_license_key')) {
            add_option('article_rewriter_license_key', '');
        }
        if (!get_option('article_rewriter_license_status')) {
            add_option('article_rewriter_license_status', 'inactive');
        }
        if (!get_option('article_rewriter_license_expires')) {
            add_option('article_rewriter_license_expires', '');
        }
        if (!get_option('article_rewriter_license_domain')) {
            add_option('article_rewriter_license_domain', '');
        }
        if (!get_option('article_rewriter_license_activated_at')) {
            add_option('article_rewriter_license_activated_at', '');
        }

        // Batch processing options
        if (!get_option('article_rewriter_batch_post_types')) {
            add_option('article_rewriter_batch_post_types', array('post'));
        }
        if (!get_option('article_rewriter_batch_limit')) {
            add_option('article_rewriter_batch_limit', 10);
        }

        // Advanced options
        if (!get_option('article_rewriter_enable_logging')) {
            add_option('article_rewriter_enable_logging', 'no');
        }
        if (!get_option('article_rewriter_save_history')) {
            add_option('article_rewriter_save_history', 'yes');
        }
    }
}