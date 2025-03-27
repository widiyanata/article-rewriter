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
    <h2><?php _e( 'API Keys', 'article-rewriter' ); ?></h2>

    <form method="post" action="options.php">
      <?php
            settings_fields( 'article_rewriter_settings' );
            // We will render fields manually instead of using do_settings_sections
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
          <th scope="row"><?php _e( 'OpenAI Model', 'article-rewriter' ); ?></th>
          <td>
            <select name="article_rewriter_openai_model">
              <?php
                    $openai_models = ['gpt-4' => 'GPT-4', 'gpt-4-turbo' => 'GPT-4 Turbo', 'gpt-3.5-turbo' => 'GPT-3.5 Turbo'];
                    $current_openai_model = get_option('article_rewriter_openai_model', 'gpt-4');
                    foreach ($openai_models as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '" ' . selected($current_openai_model, $value, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
            </select>
            <p class="description"><?php _e( 'Select the OpenAI model to use.', 'article-rewriter' ); ?></p>
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
          <th scope="row"><?php _e( 'DeepSeek Model', 'article-rewriter' ); ?></th>
          <td>
            <select name="article_rewriter_deepseek_model">
              <?php
                    $deepseek_models = ['deepseek-chat' => 'DeepSeek Chat', 'deepseek-coder' => 'DeepSeek Coder'];
                    $current_deepseek_model = get_option('article_rewriter_deepseek_model', 'deepseek-chat');
                    foreach ($deepseek_models as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '" ' . selected($current_deepseek_model, $value, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
            </select>
            <p class="description"><?php _e( 'Select the DeepSeek model to use.', 'article-rewriter' ); ?></p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'Anthropic API Key', 'article-rewriter' ); ?></th>
          <td>
            <input type="password" name="article_rewriter_anthropic_api_key"
              value="<?php echo esc_attr( get_option( 'article_rewriter_anthropic_api_key' ) ); ?>"
              class="regular-text" />
            <p class="description">
              <?php _e( 'Enter your Anthropic API key. You can get one from <a href="https://console.anthropic.com/settings/keys" target="_blank">Anthropic Console</a>.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e( 'Anthropic Model', 'article-rewriter' ); ?></th>
          <td>
            <select name="article_rewriter_anthropic_model">
              <?php
                    $anthropic_models = [
                        'claude-3-opus-20240229' => 'Claude 3 Opus',
                        'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                        'claude-3-haiku-20240307' => 'Claude 3 Haiku',
                    ];
                    $current_anthropic_model = get_option('article_rewriter_anthropic_model', 'claude-3-opus-20240229');
                    foreach ($anthropic_models as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '" ' . selected($current_anthropic_model, $value, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
            </select>
            <p class="description"><?php _e( 'Select the Anthropic model to use.', 'article-rewriter' ); ?></p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e( 'Google Gemini API Key', 'article-rewriter' ); ?></th>
          <td>
            <input type="password" name="article_rewriter_gemini_api_key"
              value="<?php echo esc_attr( get_option( 'article_rewriter_gemini_api_key' ) ); ?>" class="regular-text" />
            <p class="description">
              <?php _e( 'Enter your Google Gemini API key. You can get one from <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e( 'Google Gemini Model', 'article-rewriter' ); ?></th>
          <td>
            <select name="article_rewriter_gemini_model">
              <?php
                    $gemini_models = [
                        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                        'gemini-1.5-flash' => 'Gemini 1.5 Flash',
                        // Add other models like gemini-pro if needed
                    ];
                    $current_gemini_model = get_option('article_rewriter_gemini_model', 'gemini-1.5-pro');
                    foreach ($gemini_models as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '" ' . selected($current_gemini_model, $value, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
            </select>
            <p class="description"><?php _e( 'Select the Google Gemini model to use.', 'article-rewriter' ); ?></p>
          </td>
        </tr>

      </table>

      <h2><?php _e( 'General Settings', 'article-rewriter' ); ?></h2>
      <table class="form-table">

        <tr valign="top">
          <th scope="row"><?php _e( 'Default API', 'article-rewriter' ); ?></th>
          <td>
            <select name="article_rewriter_default_api">
              <option value="openai" <?php selected( get_option( 'article_rewriter_default_api' ), 'openai' ); ?>>
                <?php _e( 'OpenAI', 'article-rewriter' ); ?></option>
              <option value="deepseek" <?php selected( get_option( 'article_rewriter_default_api' ), 'deepseek' ); ?>>
                <?php _e( 'DeepSeek', 'article-rewriter' ); ?></option>
              <option value="anthropic" <?php selected( get_option( 'article_rewriter_default_api' ), 'anthropic' ); ?>>
                <?php _e( 'Anthropic', 'article-rewriter' ); ?></option>
              <option value="gemini" <?php selected( get_option( 'article_rewriter_default_api' ), 'gemini' ); ?>>
                <?php _e( 'Google Gemini', 'article-rewriter' ); ?></option>
            </select>
            <p class="description">
              <?php _e( 'Select the default API to use for rewriting.', 'article-rewriter' ); ?>
            </p>
          </td>
        </tr>

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

      <h2><?php _e( 'Editor Integration', 'article-rewriter' ); ?></h2>
      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php _e( 'Gutenberg Integration', 'article-rewriter' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="article_rewriter_enable_gutenberg" value="yes"
                <?php checked( get_option( 'article_rewriter_enable_gutenberg', 'yes' ), 'yes' ); ?> />
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
                <?php checked( get_option( 'article_rewriter_enable_classic_editor', 'yes' ), 'yes' ); ?> />
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