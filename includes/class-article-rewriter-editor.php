<?php
/**
 * The editor integration functionality of the plugin.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 */

/**
 * The editor integration functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the editor integration.
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/includes
 * @author     WiDigital <info@widigital.com>
 */
class Article_Rewriter_Editor {

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
    }

    /**
     * Enqueue scripts for the block editor (Gutenberg).
     *
     * @since    1.0.0
     */
    public function enqueue_block_editor_assets() {
        // Check if license is active
        $license_status = get_option('article_rewriter_license_status');
        if ($license_status !== 'active') {
            return;
        }

        // Get the API settings
        $api = get_option('article_rewriter_default_api', 'openai');
        $style = get_option('article_rewriter_default_style', 'standard');
        $openai_api_key = get_option('article_rewriter_openai_api_key', '');
        $deepseek_api_key = get_option('article_rewriter_deepseek_api_key', '');
        $anthropic_api_key = get_option('article_rewriter_anthropic_api_key', '');
        $gemini_api_key = get_option('article_rewriter_gemini_api_key', '');

        // Enqueue the block editor script
        wp_enqueue_script(
            'article-rewriter-block-editor',
            plugin_dir_url(dirname(__FILE__)) . 'build/index.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch'),
            $this->version,
            true
        );

        // Localize the script with the plugin settings
        wp_localize_script(
            'article-rewriter-block-editor',
            'articleRewriterSettings',
            array(
                'apiEndpoint' => rest_url('article-rewriter/v1/rewrite'),
                'nonce' => wp_create_nonce('wp_rest'),
                'defaultApi' => $api,
                'defaultStyle' => $style,
                'strings' => array(
                    'rewriteArticle' => __('Rewrite Article', 'article-rewriter'),
                    'rewriteSelection' => __('Rewrite Selection', 'article-rewriter'),
                    'rewriting' => __('Rewriting...', 'article-rewriter'),
                    'selectApi' => __('Select API', 'article-rewriter'),
                    'selectStyle' => __('Select Style', 'article-rewriter'),
                    'openai' => __('OpenAI', 'article-rewriter'),
                    'deepseek' => __('DeepSeek', 'article-rewriter'),
                    'anthropic' => __('Anthropic', 'article-rewriter'),
                    'gemini' => __('Google Gemini', 'article-rewriter'),
                    'standard' => __('Standard', 'article-rewriter'),
                    'formal' => __('Formal', 'article-rewriter'),
                    'casual' => __('Casual', 'article-rewriter'),
                    'creative' => __('Creative', 'article-rewriter'),
                ),
                'apiKeys' => array(
                    'openai' => !empty($openai_api_key),
                    'deepseek' => !empty($deepseek_api_key),
                    'anthropic' => !empty($anthropic_api_key),
                    'gemini' => !empty($gemini_api_key),
                ),
            )
        );

        // Enqueue the block editor styles
        wp_enqueue_style(
            'article-rewriter-block-editor',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/article-rewriter-block-editor.css',
            array(),
            $this->version
        );
    }

    /**
     * Add the TinyMCE plugin for the classic editor.
     *
     * @since    1.0.0
     * @param    array $plugins An array of TinyMCE plugins.
     * @return   array The modified array of TinyMCE plugins.
     */
    public function add_tinymce_plugin($plugins) {
        // Check if license is active
        $license_status = get_option('article_rewriter_license_status');
        if ($license_status !== 'active') {
            return $plugins;
        }

        // Add the TinyMCE plugin
        $plugins['article_rewriter'] = plugin_dir_url(dirname(__FILE__)) . 'assets/js/article-rewriter-classic-editor.js';
        return $plugins;
    }

    /**
     * Register the TinyMCE button for the classic editor.
     *
     * @since    1.0.0
     * @param    array $buttons An array of TinyMCE buttons.
     * @return   array The modified array of TinyMCE buttons.
     */
    public function register_tinymce_button($buttons) {
        // Check if license is active
        $license_status = get_option('article_rewriter_license_status');
        if ($license_status !== 'active') {
            return $buttons;
        }

        // Get the API settings
        $api = get_option('article_rewriter_default_api', 'openai');
        $style = get_option('article_rewriter_default_style', 'standard');
        $openai_api_key = get_option('article_rewriter_openai_api_key', '');
        $deepseek_api_key = get_option('article_rewriter_deepseek_api_key', '');
        $anthropic_api_key = get_option('article_rewriter_anthropic_api_key', '');
        $gemini_api_key = get_option('article_rewriter_gemini_api_key', '');

        // Add the TinyMCE button
        array_push($buttons, 'article_rewriter');
        return $buttons;
    }

    /**
     * Enqueue scripts and styles for the classic editor.
     *
     * @since    1.0.0
     */
    public function enqueue_classic_editor_assets() {
        // Check if license is active
        $license_status = get_option('article_rewriter_license_status');
        if ($license_status !== 'active') {
            return;
        }

        // Get the API settings
        $api = get_option('article_rewriter_default_api', 'openai');
        $style = get_option('article_rewriter_default_style', 'standard');
        $openai_api_key = get_option('article_rewriter_openai_api_key', '');
        $deepseek_api_key = get_option('article_rewriter_deepseek_api_key', '');
        $anthropic_api_key = get_option('article_rewriter_anthropic_api_key', '');
        $gemini_api_key = get_option('article_rewriter_gemini_api_key', '');

        // Enqueue jQuery
        wp_enqueue_script('jquery');

        // Enqueue the classic editor script
        wp_enqueue_script(
            'article-rewriter-classic-editor-script',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/article-rewriter-classic-editor.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize the script with the plugin settings
        wp_localize_script(
            'article-rewriter-classic-editor-script',
            'articleRewriterClassic',
            array(
                'rest_url' => rest_url(),
                'nonce' => wp_create_nonce('wp_rest'),
                'defaultApi' => $api,
                'defaultStyle' => $style,
                'i18n' => array(
                    'rewrite' => __('Rewrite', 'article-rewriter'),
                    'rewrite_tooltip' => __('Rewrite article using AI', 'article-rewriter'),
                    'rewrite_article' => __('Rewrite Article', 'article-rewriter'),
                    'api_provider' => __('API Provider', 'article-rewriter'),
                    'rewriting_style' => __('Rewriting Style', 'article-rewriter'),
                    'original_content' => __('Original Content', 'article-rewriter'),
                    'rewritten_content' => __('Rewritten Content', 'article-rewriter'),
                    'standard' => __('Standard', 'article-rewriter'),
                    'formal' => __('Formal', 'article-rewriter'),
                    'casual' => __('Casual', 'article-rewriter'),
                    'creative' => __('Creative', 'article-rewriter'),
                    'rewriting' => __('Rewriting...', 'article-rewriter'),
                    'success' => __('Article rewritten successfully!', 'article-rewriter'),
                    'error' => __('Error:', 'article-rewriter'),
                    'apply_content' => __('Apply Rewritten Content', 'article-rewriter'),
                    'show_history' => __('Show History', 'article-rewriter'),
                    'hide_history' => __('Hide History', 'article-rewriter'),
                    'rewrite_history' => __('Rewrite History', 'article-rewriter'),
                    'no_history' => __('No rewrite history found for this post.', 'article-rewriter'),
                    'history_error' => __('Error loading history.', 'article-rewriter'),
                    'apply_version' => __('Apply This Version', 'article-rewriter'),
                    'style' => __('Style:', 'article-rewriter'),
                ),
                'apiKeys' => array(
                    'openai' => !empty($openai_api_key),
                    'deepseek' => !empty($deepseek_api_key),
                    'anthropic' => !empty($anthropic_api_key),
                    'gemini' => !empty($gemini_api_key),
                ),
            )
        );

        // Enqueue the classic editor styles
        wp_enqueue_style(
            'article-rewriter-classic-editor',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/article-rewriter-classic-editor.css',
            array(),
            $this->version
        );

        // Add the settings to the page
        add_action('admin_footer', array($this, 'add_classic_editor_settings'));
    }

    /**
     * Add the settings to the classic editor page.
     *
     * @since    1.0.0
     */
    public function add_classic_editor_settings() {
        // Get the API settings
        $api = get_option('article_rewriter_default_api', 'openai');
        $style = get_option('article_rewriter_default_style', 'standard');
        $openai_api_key = get_option('article_rewriter_openai_api_key', '');
        $deepseek_api_key = get_option('article_rewriter_deepseek_api_key', '');
        $anthropic_api_key = get_option('article_rewriter_anthropic_api_key', '');
        $gemini_api_key = get_option('article_rewriter_gemini_api_key', '');
        ?>
<div id="article-rewriter-modal" style="display: none;">
  <div class="article-rewriter-modal-content">
    <h2><?php _e('Rewrite Article', 'article-rewriter'); ?></h2>
    <div class="article-rewriter-modal-body">
      <div class="article-rewriter-form-group">
        <label for="article-rewriter-api"><?php _e('Select API', 'article-rewriter'); ?></label>
        <select id="article-rewriter-api">
          <option value="openai" <?php selected($api, 'openai'); ?>><?php esc_html_e('OpenAI', 'article-rewriter'); ?>
          </option>
          <option value="deepseek" <?php selected($api, 'deepseek'); ?>>
            <?php esc_html_e('DeepSeek', 'article-rewriter'); ?></option>
          <!-- Add more API options as needed -->
        </select>
      </div>
      <div class="article-rewriter-form-group">
        <label for="article-rewriter-style"><?php _e('Select Style', 'article-rewriter'); ?></label>
        <select id="article-rewriter-style">
          <option value="standard" <?php selected($style, 'standard'); ?>><?php _e('Standard', 'article-rewriter'); ?>
          </option>
          <option value="formal" <?php selected($style, 'formal'); ?>><?php _e('Formal', 'article-rewriter'); ?>
          </option>
          <option value="casual" <?php selected($style, 'casual'); ?>><?php _e('Casual', 'article-rewriter'); ?>
          </option>
          <option value="creative" <?php selected($style, 'creative'); ?>><?php _e('Creative', 'article-rewriter'); ?>
          </option>
        </select>
      </div>
      <div class="article-rewriter-form-group">
        <a href="<?php echo admin_url('admin.php?page=article-rewriter-settings'); ?>" target="_blank"
          class="article-rewriter-settings-link">
          <?php _e('API Settings', 'article-rewriter'); ?>
        </a>
      </div>
      <div class="article-rewriter-form-group">
        <button id="article-rewriter-submit" class="button button-primary">
          <?php _e('Rewrite', 'article-rewriter'); ?>
        </button>
        <button id="article-rewriter-cancel" class="button">
          <?php _e('Cancel', 'article-rewriter'); ?>
        </button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
var articleRewriterSettings = {
  apiEndpoint: '<?php echo esc_url(rest_url('article-rewriter/v1/rewrite')); ?>',
  nonce: '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>',
  strings: {
    rewriting: '<?php echo esc_js(__('Rewriting...', 'article-rewriter')); ?>',
    error: '<?php echo esc_js(__('Error:', 'article-rewriter')); ?>',
    success: '<?php echo esc_js(__('Rewriting completed successfully.', 'article-rewriter')); ?>'
  },
  apiKeys: {
    openai: <?php echo !empty($openai_api_key) ? 'true' : 'false'; ?>,
    deepseek: <?php echo !empty($deepseek_api_key) ? 'true' : 'false'; ?>,
    anthropic: <?php echo !empty($anthropic_api_key) ? 'true' : 'false'; ?>,
    gemini: <?php echo !empty($gemini_api_key) ? 'true' : 'false'; ?>
  }
};
</script>
<?php
    }
}