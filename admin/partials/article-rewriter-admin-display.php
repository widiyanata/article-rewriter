<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <div class="article-rewriter-dashboard">
    <div class="article-rewriter-card">
      <h2><?php _e( 'Quick Links', 'article-rewriter' ); ?></h2>
      <p><?php _e( 'Access the main features of Article Rewriter.', 'article-rewriter' ); ?></p>

      <div class="article-rewriter-quick-links">
        <?php 
                // Check if license is active
                $license_status = get_option( 'article_rewriter_license_status', 'inactive' );
                ?>
        <div class="article-rewriter-quick-link">
          <a href="<?php echo admin_url( 'admin.php?page=article-rewriter-settings' ); ?>">
            <span class="dashicons dashicons-admin-settings article-rewriter-quick-link-icon"></span>
            <span class="article-rewriter-quick-link-label"><?php _e( 'Settings', 'article-rewriter' ); ?></span>
            <span
              class="article-rewriter-quick-link-description"><?php _e( 'Configure API keys and default settings', 'article-rewriter' ); ?></span>
          </a>
        </div>
        <?php if ( $license_status === 'active' ) : ?>
        <?php 
                    // Check if API keys are configured
                    $openai_api_key = get_option( 'article_rewriter_openai_api_key', '' );
                    $deepseek_api_key = get_option( 'article_rewriter_deepseek_api_key', '' );
                    ?>
        <?php if ( ! empty( $openai_api_key ) || ! empty( $deepseek_api_key ) ) : ?>
        <div class="article-rewriter-quick-link">
          <a href="<?php echo admin_url( 'admin.php?page=article-rewriter-batch' ); ?>">
            <span class="dashicons dashicons-update article-rewriter-quick-link-icon"></span>
            <span
              class="article-rewriter-quick-link-label"><?php _e( 'Batch Processing', 'article-rewriter' ); ?></span>
            <span
              class="article-rewriter-quick-link-description"><?php _e( 'Rewrite multiple articles at once', 'article-rewriter' ); ?></span>
          </a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="article-rewriter-quick-link">
          <a href="<?php echo admin_url( 'admin.php?page=article-rewriter-license' ); ?>">
            <span class="dashicons dashicons-admin-network article-rewriter-quick-link-icon"></span>
            <span class="article-rewriter-quick-link-label"><?php _e( 'License', 'article-rewriter' ); ?></span>
            <span
              class="article-rewriter-quick-link-description"><?php _e( 'Manage your plugin license', 'article-rewriter' ); ?></span>
          </a>
        </div>
        <div class="article-rewriter-quick-link">
          <a href="<?php echo admin_url( 'edit.php' ); ?>">
            <span class="dashicons dashicons-admin-post article-rewriter-quick-link-icon"></span>
            <span class="article-rewriter-quick-link-label"><?php _e( 'Posts', 'article-rewriter' ); ?></span>
            <span
              class="article-rewriter-quick-link-description"><?php _e( 'Rewrite your blog posts', 'article-rewriter' ); ?></span>
          </a>
        </div>
        <div class="article-rewriter-quick-link">
          <a href="<?php echo admin_url( 'edit.php?post_type=page' ); ?>">
            <span class="dashicons dashicons-admin-page article-rewriter-quick-link-icon"></span>
            <span class="article-rewriter-quick-link-label"><?php _e( 'Pages', 'article-rewriter' ); ?></span>
            <span
              class="article-rewriter-quick-link-description"><?php _e( 'Rewrite your WordPress pages', 'article-rewriter' ); ?></span>
          </a>
        </div>
      </div>
    </div>

    <div class="article-rewriter-card">
      <h2><?php _e( 'Recent Activity', 'article-rewriter' ); ?></h2>

      <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'article_rewriter_history';
            $recent_activity = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 5" );
            ?>

      <?php if ( ! empty( $recent_activity ) ) : ?>
      <table class="article-rewriter-activity-table">
        <thead>
          <tr>
            <th><?php _e( 'Post', 'article-rewriter' ); ?></th>
            <th><?php _e( 'API', 'article-rewriter' ); ?></th>
            <th><?php _e( 'Style', 'article-rewriter' ); ?></th>
            <th><?php _e( 'Date', 'article-rewriter' ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $recent_activity as $activity ) : ?>
          <tr>
            <td>
              <a href="<?php echo get_edit_post_link( $activity->post_id ); ?>">
                <?php echo get_the_title( $activity->post_id ); ?>
              </a>
            </td>
            <td><?php echo esc_html( ucfirst( $activity->api ) ); ?></td>
            <td><?php echo esc_html( ucfirst( $activity->style ) ); ?></td>
            <td>
              <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $activity->created_at ) ); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else : ?>
      <p><?php _e( 'No recent activity found.', 'article-rewriter' ); ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>