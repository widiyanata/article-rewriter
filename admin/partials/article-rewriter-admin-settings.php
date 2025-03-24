<?php
/**
 * Provide a admin area view for the plugin settings
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

  <div class="article-rewriter-card">
    <h2><?php _e( 'API Settings', 'article-rewriter' ); ?></h2>
    <p><?php _e( 'Configure your API settings for the Article Rewriter plugin.', 'article-rewriter' ); ?></p>
    <p>
      <?php _e( 'You need to provide at least one API key to use the rewriting functionality.', 'article-rewriter' ); ?>
    </p>
  </div>

  <div class="article-rewriter-card">
    <h2><?php _e( 'OpenAI API Settings', 'article-rewriter' ); ?></h2>

    <form method="post" action="options.php">
      <?php
            settings_fields( 'article_rewriter_settings' );
            do_settings_sections( 'article_rewriter_settings' );
            ?>

      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php _e( 'OpenAI API Key', 'article-rewriter' ); ?></th>
          <td>
            <input type="password" name="article_rewriter_openai_api_key"
              value="<?php echo esc_attr( get_option( 'article_rewriter_openai_api_key' ) ); ?>" class="regular-text" />
            <p class="description">
              <?php _e( 'Enter your OpenAI API key. You can get one from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI API Keys</a>.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'DeepSeek API Key', 'article-rewriter' ); ?></th>
          <td>
            <input type="password" name="article_rewriter_deepseek_api_key"
              value="<?php echo esc_attr( get_option( 'article_rewriter_deepseek_api_key' ) ); ?>"
              class="regular-text" />
            <p class="description">
              <?php _e( 'Enter your DeepSeek API key. You can get one from <a href="https://platform.deepseek.com/" target="_blank">DeepSeek Platform</a>.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'Default API', 'article-rewriter' ); ?></th>
          <td>
            <select name="article_rewriter_default_api">
              <option value="openai" <?php selected( get_option( 'article_rewriter_default_api' ), 'openai' ); ?>>
                <?php _e( 'OpenAI', 'article-rewriter' ); ?></option>
              <option value="deepseek" <?php selected( get_option( 'article_rewriter_default_api' ), 'deepseek' ); ?>>
                <?php _e( 'DeepSeek', 'article-rewriter' ); ?></option>
            </select>
            <p class="description">
              <?php _e( 'Select the default API to use for rewriting.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>
      </table>

      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php _e( 'Default Rewrite Style', 'article-rewriter' ); ?></th>
          <td>
            <select name="article_rewriter_default_rewrite_style">
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
            <p class="description">
              <?php _e( 'Select the default style to use for rewriting.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'Batch Size', 'article-rewriter' ); ?></th>
          <td>
            <input type="number" name="article_rewriter_batch_size"
              value="<?php echo esc_attr( get_option( 'article_rewriter_batch_size', 10 ) ); ?>" class="small-text"
              min="1" max="50" />
            <p class="description">
              <?php _e( 'Maximum number of posts that can be processed in a batch.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>
      </table>

      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php _e( 'Gutenberg Integration', 'article-rewriter' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="article_rewriter_enable_gutenberg" value="yes"
                <?php checked( get_option( 'article_rewriter_enable_gutenberg' ), 'yes' ); ?> />
              <?php _e( 'Enable Gutenberg integration', 'article-rewriter' ); ?>
            </label>
            <p class="description">
              <?php _e( 'Add Article Rewriter sidebar to the Gutenberg editor.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'Classic Editor Integration', 'article-rewriter' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="article_rewriter_enable_classic_editor" value="yes"
                <?php checked( get_option( 'article_rewriter_enable_classic_editor' ), 'yes' ); ?> />
              <?php _e( 'Enable Classic Editor integration', 'article-rewriter' ); ?>
            </label>
            <p class="description">
              <?php _e( 'Add Article Rewriter button to the Classic Editor toolbar.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>
      </table>

      <?php submit_button(); ?>
    </form>
  </div>


</div>