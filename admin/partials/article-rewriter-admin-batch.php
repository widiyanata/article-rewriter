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

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
      <input type="hidden" name="action" value="article_rewriter_start_batch" />

      <a href="<?php echo admin_url( 'admin.php?page=article-rewriter-settings' ); ?>" class="button">
        <?php _e( 'Settings', 'article-rewriter' ); ?>
      </a>

      <?php wp_nonce_field( 'article_rewriter_start_batch' ); ?>

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
                  <?php echo get_the_title( $post->ID ); ?>
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
        <input type="submit" name="submit" id="submit" class="button button-primary"
          value="<?php _e( 'Create Batch', 'article-rewriter' ); ?>" />
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
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th><?php _e( 'ID', 'article-rewriter' ); ?></th>
          <th><?php _e( 'Status', 'article-rewriter' ); ?></th>
          <th><?php _e( 'API', 'article-rewriter' ); ?></th>
          <th><?php _e( 'Style', 'article-rewriter' ); ?></th>
          <!-- <th><?php _e( 'Progress', 'article-rewriter' ); ?></th> -->
          <th><?php _e( 'Date', 'article-rewriter' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $recent_jobs as $job ) : ?>
        <tr>
          <td><?php echo esc_html( $job->id ); ?></td>
          <td>
            <?php
                                switch ( $job->status ) {
                                    case 'pending':
                                        echo '<span class="article-rewriter-status article-rewriter-status-pending">' . esc_html( ucfirst( $job->status ) ) . '</span>';
                                        break;
                                    case 'processing':
                                        echo '<span class="article-rewriter-status article-rewriter-status-processing">' . esc_html( ucfirst( $job->status ) ) . '</span>';
                                        break;
                                    case 'completed':
                                        echo '<span class="article-rewriter-status article-rewriter-status-completed">' . esc_html( ucfirst( $job->status ) ) . '</span>';
                                        break;
                                    default:
                                        echo '<span class="article-rewriter-status">' . esc_html( ucfirst( $job->status ) ) . '</span>';
                                }
                                ?>
          </td>
          <td><?php echo esc_html( ucfirst( $job->api ) ); ?></td>
          <td><?php echo esc_html( ucfirst( $job->style ) ); ?></td>
          <!-- <td>
            <div class="article-rewriter-progress">
              <div class="article-rewriter-progress-bar"
                style="width: <?php echo $job->total_items > 0 ? ( $job->processed_items / $job->total_items * 100 ) : 0; ?>%">
              </div>
              <div class="article-rewriter-progress-text">
                <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $job->created_at ) ); ?>
              </div>
            </div>
            <div class="article-rewriter-progress-info">
              <?php echo $job->processed_items; ?> / <?php echo $job->total_items; ?>
              <?php if ( $job->failed_items > 0 ) : ?>
              <span class="article-rewriter-failed-items">(<?php echo $job->failed_items; ?>
                <?php _e( 'failed', 'article-rewriter' ); ?>)</span>
              <?php endif; ?>
            </div>
          </td> -->
          <td>
            <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $job->created_at ) ); ?>
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
    <div id="article-rewriter-batch-details">
      <p><?php _e( 'Select a batch job to view details.', 'article-rewriter' ); ?></p>
    </div>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
  // Select all posts
  $('#select-all-posts').on('change', function() {
    var isChecked = $(this).prop('checked');
    $('input[name="post_ids[]"]').prop('checked', isChecked);
  });

  // Confirm batch creation
  $('form').on('submit', function(e) {
    var selectedPosts = $('input[name="post_ids[]"]:checked').length;

    if (selectedPosts === 0) {
      alert('<?php echo esc_js( __( 'Please select at least one post to rewrite.', 'article-rewriter' ) ); ?>');
      e.preventDefault();
      return false;
    }

    var batchSize = <?php echo get_option( 'article_rewriter_batch_size', 10 ); ?>;

    if (selectedPosts > batchSize) {
      alert('You can only select up to ' + batchSize + ' posts at once.');
      e.preventDefault();
      return false;
    }

    if (!confirm(
        'Are you sure you want to create a batch job to rewrite these posts? This may take some time.')) {
      e.preventDefault();
      return false;
    }

    return true;
  });

  // View batch job details
  $('.article-rewriter-status').on('click', function() {
    var jobId = $(this).closest('tr').find('td:first').text();

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'article_rewriter_get_batch_status',
        batch_id: jobId,
        nonce: '<?php echo wp_create_nonce( 'article_rewriter_nonce' ); ?>'
      },
      success: function(response) {
        if (response.success) {
          var job = response.data;
          var html = '<h3>Batch Job #' + job.id + '</h3>';
          html += '<p><strong>Status:</strong> ' + job.status + '</p>';
          html += '<p><strong>API:</strong> ' + job.api_used + '</p>';
          html += '<p><strong>Style:</strong> ' + job.rewrite_style + '</p>';
          html += '<p><strong>Progress:</strong> ' + job.processed_items + ' / ' + job.total_items +
            '</p>';
          html += '<p><strong>Failed:</strong> ' + job.failed_items + '</p>';
          html += '<p><strong>Created:</strong> ' + job.created_at + '</p>';
          html += '<p><strong>Updated:</strong> ' + job.updated_at + '</p>';

          $('#article-rewriter-batch-details').html(html);
        } else {
          $('#article-rewriter-batch-details').html('<p>Error loading batch job details.</p>');
        }
      },
      error: function() {
        $('#article-rewriter-batch-details').html('<p>Error loading batch job details.</p>');
      }
    });
  });
});
</script>