<?php
/**
 * Provide a admin area view for the plugin license
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

// Get license information
$license_status = get_option( 'article_rewriter_license_status', 'inactive' );
$purchase_code = get_option( 'article_rewriter_license_key', '' );
$license_expires = get_option( 'article_rewriter_license_expires', '' );

// Get domain
$domain = home_url();
?>

<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <?php if ( isset( $_GET['activated'] ) ) : ?>
  <div class="notice notice-success is-dismissible">
    <p><?php _e( 'License activated successfully.', 'article-rewriter' ); ?></p>
  </div>
  <?php endif; ?>

  <?php if ( isset( $_GET['deactivated'] ) ) : ?>
  <div class="notice notice-success is-dismissible">
    <p><?php _e( 'License deactivated successfully.', 'article-rewriter' ); ?></p>
  </div>
  <?php endif; ?>

  <div class="article-rewriter-card">
    <h2><?php _e( 'License Information', 'article-rewriter' ); ?></h2>

    <?php if ( $license_status === 'active' ) : ?>
    <div class="article-rewriter-license-info">
      <p><strong><?php _e( 'Status:', 'article-rewriter' ); ?></strong> <span
          class="article-rewriter-license-active"><?php _e( 'Active', 'article-rewriter' ); ?></span></p>
      <p><strong><?php _e( 'Purchase Code:', 'article-rewriter' ); ?></strong> <?php echo esc_html( $purchase_code ); ?>
      </p>
      <p><strong><?php _e( 'Domain:', 'article-rewriter' ); ?></strong> <?php echo esc_html( $domain ); ?></p>
      <p><strong><?php _e( 'Expires:', 'article-rewriter' ); ?></strong>
        <?php 
                    if ( ! empty( $license_expires ) ) {
                        echo date_i18n( get_option( 'date_format' ), strtotime( $license_expires ) );
                    } else {
                        _e( 'Never', 'article-rewriter' );
                    }
                    ?>
      </p>
      <p><strong><?php _e( 'Support:', 'article-rewriter' ); ?></strong>
        <?php 
                    if ( ! empty( $license_expires ) && strtotime( $license_expires ) > time() ) {
                        echo date_i18n( get_option( 'date_format' ), strtotime( $license_expires ) );
                    } else {
                        _e( 'Expired', 'article-rewriter' );
                    }
                    ?>
      </p>

      <p class="description">
        <?php echo sprintf( __( 'Your license is active. You can use Article Rewriter on %s.', 'article-rewriter' ), esc_html($domain) ); ?>
      </p>
    </div>

    <div class="article-rewriter-license-actions">
      <h3><?php _e( 'Deactivate License', 'article-rewriter' ); ?></h3>
      <p>
        <?php _e( 'If you want to use your license on another domain, you need to deactivate it first.', 'article-rewriter' ); ?>
      </p>

      <!-- Removed form tags, button will trigger JS -->
      <?php // Nonce can be retrieved from localized script data in JS ?>

      <p class="submit">
        <button type="button" id="article-rewriter-deactivate-license-btn" class="button button-primary">
          <?php _e( 'Deactivate License', 'article-rewriter' ); ?>
        </button>
      </p>
      <div id="article-rewriter-deactivate-message" style="margin-top: 10px;"></div> <!-- Area for JS messages -->
    </div>
    <?php else : ?>
    <div class="article-rewriter-license-info">
      <p><strong><?php _e( 'Status:', 'article-rewriter' ); ?></strong> <span
          class="article-rewriter-license-inactive"><?php _e( 'Inactive', 'article-rewriter' ); ?></span></p>
      <p class="description"><?php _e( 'Please activate your license to use Article Rewriter.', 'article-rewriter' ); ?>
      </p>
    </div>

    <div class="article-rewriter-license-actions">
      <h3><?php _e( 'Activate License', 'article-rewriter' ); ?></h3>
      <p><?php _e( 'Enter your Envato purchase code to activate your license.', 'article-rewriter' ); ?></p>

      <!-- Changed to trigger JS instead of direct post -->
      <div id="article-rewriter-activate-form"> <?php // Changed form to div ?>
        <?php // Nonce will be handled by JS ?>

        <table class="form-table">
          <tr valign="top">
            <th scope="row"><?php _e( 'Purchase Code', 'article-rewriter' ); ?></th>
            <td>
              <input type="text" name="purchase_code" value="<?php echo esc_attr( $purchase_code ); ?>"
                class="regular-text"
                placeholder="<?php esc_attr_e( 'Enter your Envato purchase code', 'article-rewriter' ); ?>" required />
              <p class="description">
                <?php _e( 'You can find your purchase code in your Envato account.', 'article-rewriter' ); ?>
              </p>
            </td>
          </tr>
        </table>

        <p class="submit">
          <button type="button" id="article-rewriter-activate-license-btn" class="button button-primary">
            <?php _e( 'Activate License', 'article-rewriter' ); ?>
          </button>
        </p>
        <div id="article-rewriter-activate-message" style="margin-top: 10px;"></div> <!-- Area for JS messages -->
      </div> <?php // End #article-rewriter-activate-form div ?>

      <div class="article-rewriter-license-help">
        <h3><?php _e( 'How to find your purchase code', 'article-rewriter' ); ?></h3>
        <ol>
          <li><?php _e( 'Log in to your Envato account.', 'article-rewriter' ); ?></li>
          <li><?php _e( 'Go to your Downloads page.', 'article-rewriter' ); ?></li>
          <li><?php _e( 'Find Article Rewriter in your purchases.', 'article-rewriter' ); ?></li>
          <li><?php _e( 'Click on "Download" button.', 'article-rewriter' ); ?></li>
          <li><?php _e( 'Select "License certificate & purchase code" from the dropdown.', 'article-rewriter' ); ?></li>
          <li><?php _e( 'Open the downloaded text file.', 'article-rewriter' ); ?></li>
          <li><?php _e( 'Copy the Item Purchase Code.', 'article-rewriter' ); ?></li>
        </ol>

        <p>
          <?php _e( 'Note: Your purchase code looks like this: 8f0b5-d4c2-f8b5-c7d8-2c9b5-e3d4-f7a9-b8c3', 'article-rewriter' ); ?>
        </p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>