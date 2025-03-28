<?php
/**
 * Provide a admin area view for the plugin batch processing
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

// Check if license is active
$license_status = get_option( 'article_rewriter_license_status', 'inactive' );
if ( $license_status !== 'active' ) {
    ?>
<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
  <div class="notice notice-error">
    <p><?php _e( 'Your license is inactive. Please activate your license to use this feature.', 'article-rewriter' ); ?>
    </p>
  </div>
</div>
<?php
    return;
}

// Check if API keys are configured
$openai_api_key = get_option( 'article_rewriter_openai_api_key', '' );
$deepseek_api_key = get_option( 'article_rewriter_deepseek_api_key', '' );
if ( empty( $openai_api_key ) && empty( $deepseek_api_key ) ) {
    ?>
<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
  <div class="notice notice-error">
    <p><?php _e( 'No API keys configured. Please add API keys in the settings.', 'article-rewriter' ); ?></p>
  </div>
</div>
<?php
    return;
}

// Get posts
$posts = get_posts( array(
    'post_type' => array( 'post', 'page' ),
    'post_status' => 'publish',
    'posts_per_page' => -1,
) );
?>

<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <?php if ( isset( $_GET['batch_created'] ) ) : ?>
  <div class="notice notice-success is-dismissible">
    <p>
      <?php _e( 'Batch job created successfully. The posts will be rewritten in the background.', 'article-rewriter' ); ?>
    </p>
  </div>
  <?php endif; ?>

  <div class="article-rewriter-card">
    <h2><?php _e( 'Create Batch Job', 'article-rewriter' ); ?></h2>

    <form id="article-rewriter-batch-form"> <?php // Removed method and action, added ID ?>
      <?php // Nonce will be handled by JS using localized variable ?>

      <a href="<?php echo admin_url( 'admin.php?page=article-rewriter-settings' ); ?>" class="button">
        <?php _e( 'Settings', 'article-rewriter' ); ?>
      </a>

      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php _e( 'API', 'article-rewriter' ); ?></th>
          <td>
            <select name="api">
              <?php if ( ! empty( $openai_api_key ) ) : ?>
              <option value="openai" <?php selected( get_option( 'article_rewriter_default_api' ), 'openai' ); ?>>
                <?php _e( 'OpenAI', 'article-rewriter' ); ?></option>
              <?php endif; ?>
              <?php if ( ! empty( $deepseek_api_key ) ) : ?>
              <option value="deepseek" <?php selected( get_option( 'article_rewriter_default_api' ), 'deepseek' ); ?>>
                <?php _e( 'DeepSeek', 'article-rewriter' ); ?></option>
              <?php endif; ?>
            </select>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'Rewrite Style', 'article-rewriter' ); ?></th>
          <td>
            <select name="style">
              <option value="standard"
                <?php selected( get_option( 'article_rewriter_default_rewrite_style' ), 'standard' ); ?>>
                <?php _e( 'Standard', 'article-rewriter' ); ?></option>
              <option value="creative"
                <?php selected( get_option( 'article_rewriter_default_rewrite_style' ), 'creative' ); ?>>
                <?php _e( 'Creative', 'article-rewriter' ); ?></option>
              <option value="formal"
                <?php selected( get_option( 'article_rewriter_default_rewrite_style' ), 'formal' ); ?>>
                <?php _e( 'Formal', 'article-rewriter' ); ?></option>
              <option value="simple"
                <?php selected( get_option( 'article_rewriter_default_rewrite_style' ), 'simple' ); ?>>
                <?php _e( 'Simple', 'article-rewriter' ); ?></option>
            </select>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'Select Posts', 'article-rewriter' ); ?></th>
          <td>
            <p class="description">
              <?php echo sprintf( __( 'You can select up to %d posts to rewrite in a batch.', 'article-rewriter' ), get_option( 'article_rewriter_batch_size', 10 ) ); ?>
            </p>
          </td>
        </tr>
      </table>

      <div class="article-rewriter-post-list">
        <table class="wp-list-table widefat fixed striped">
          <thead>
            <tr>
              <th class="check-column"><input type="checkbox" id="select-all-posts" /></th>
              <th><?php _e( 'Title', 'article-rewriter' ); ?></th>
              <th><?php _e( 'Type', 'article-rewriter' ); ?></th>
              <th><?php _e( 'Date', 'article-rewriter' ); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $posts as $post ) : ?>
            <tr>
              <td class="check-column">
                <input type="checkbox" name="post_ids[]" value="<?php echo $post->ID; ?>" />
              </td>
              <td>
                <a href="<?php echo get_edit_post_link( $post->ID ); ?>" target="_blank">
                  <?php echo esc_html( get_the_title( $post->ID ) ); ?>
                </a>
              </td>
              <td>
                <?php echo get_post_type( $post->ID ) === 'post' ? __( 'Post', 'article-rewriter' ) : __( 'Page', 'article-rewriter' ); ?>
              </td>
              <td><?php echo get_the_date( '', $post->ID ); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <p class="submit">
        <button type="submit" id="article-rewriter-batch-submit" class="button button-primary">
          <?php // Changed to button and updated ID ?>
          <?php _e( 'Create Batch', 'article-rewriter' ); ?>
        </button>
        <span class="spinner" style="float: none; vertical-align: middle;"></span>
        <?php // Added spinner for AJAX feedback ?>
      </p>
    </form>
  </div>

  <div class="article-rewriter-card">
    <h2><?php _e( 'Recent Batch Jobs', 'article-rewriter' ); ?></h2>

    <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_rewriter_batch';
        $recent_jobs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10" );
        ?>

    <?php if ( ! empty( $recent_jobs ) ) : ?>
    <table id="article-rewriter-batch-jobs-table" class="wp-list-table widefat fixed striped"> <?php // Added ID ?>
      <thead>
        <tr>
          <th><?php _e( 'ID', 'article-rewriter' ); ?></th>
          <th><?php _e( 'Status', 'article-rewriter' ); ?></th>
          <th><?php _e( 'API', 'article-rewriter' ); ?></th>
          <th><?php _e( 'Style', 'article-rewriter' ); ?></th>
          <th><?php _e( 'Progress', 'article-rewriter' ); ?></th> <?php // Uncommented Progress ?>
          <th><?php _e( 'Created', 'article-rewriter' ); ?></th>
          <th><?php _e( 'Updated', 'article-rewriter' ); ?></th> <?php // Added Updated column ?>
          <th><?php _e( 'Actions', 'article-rewriter' ); ?></th> <?php // Added Actions column ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $recent_jobs as $job ) : ?>
        <tr data-job-id="<?php echo esc_attr( $job->id ); ?>"> <?php // Added data-job-id ?>
          <td><?php echo esc_html( $job->id ); ?></td>
          <td>
            <?php
                                $status_label = ucfirst( $job->status );
                                $status_class = sanitize_html_class( $job->status );
                                // Map processing to in-progress for JS polling
                                if ($status_class === 'processing') {
                                    $status_class = 'in-progress';
                                }
                                echo '<span class="article-rewriter-batch-status ' . $status_class . '">' . esc_html( $status_label ) . '</span>';
                                /* switch ( $job->status ) {
                                } */
                                ?>
          </td>
          <td><?php echo esc_html( ucfirst( $job->api ) ); ?></td>
          <td><?php echo esc_html( ucfirst( $job->style ) ); ?></td>
          <td> <?php // Progress column ?>
            <div class="article-rewriter-progress">
              <div class="article-rewriter-progress-bar"
                style="width: <?php echo $job->total > 0 ? round( ( $job->processed / $job->total * 100 ) ) : 0; ?>%">
              </div>
              <div class="article-rewriter-progress-text">
                <?php echo esc_html( $job->processed ); ?> / <?php echo esc_html( $job->total ); ?>
              </div>
            </div>
          </td>
          <td> <?php // Created column ?>
            <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $job->created_at ) ); ?>
          </td>
          <td class="article-rewriter-batch-updated"> <?php // Updated column ?>
            <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $job->updated_at ) ); ?>
          </td>
          <td> <?php // Actions column ?>
            <a href="#" class="article-rewriter-batch-action" data-action="view"
              data-job-id="<?php echo esc_attr( $job->id ); ?>"><?php _e('View', 'article-rewriter'); ?></a>
            <?php if ($job->status === 'pending' || $job->status === 'processing'): ?>
            | <a href="#" class="article-rewriter-batch-action" data-action="cancel"
              data-job-id="<?php echo esc_attr( $job->id ); ?>"><?php _e('Cancel', 'article-rewriter'); ?></a>
            <?php endif; ?>
            | <a href="#" class="article-rewriter-batch-action delete" data-action="delete"
              data-job-id="<?php echo esc_attr( $job->id ); ?>"><?php _e('Delete', 'article-rewriter'); ?></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else : ?>
    <p><?php _e( 'No batch jobs found.', 'article-rewriter' ); ?></p>
    <?php endif; ?>
  </div>

  <div class="article-rewriter-card">
    <h2><?php _e( 'Batch Job Details', 'article-rewriter' ); ?></h2>
    <div id="article-rewriter-batch-job-details"> <?php // Updated ID ?>
      <p><?php _e( 'Click "View" on a batch job to see details.', 'article-rewriter' ); ?></p>
      <?php // Updated placeholder text ?>
    </div>
  </div>
</div>

<?php // Removed inline script block ?>