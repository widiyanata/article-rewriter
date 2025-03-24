<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://widigital.com
 * @since             1.0.0
 * @package           Article_Rewriter
 *
 * @wordpress-plugin
 * Plugin Name:       Article Rewriter
 * Plugin URI:        https://widigital.com/article-rewriter
 * Description:       Automatically rewrite articles using various cloud-based APIs such as DeepSeek, OpenAI, and others.
 * Version:           1.0.0
 * Author:            WiDigital
 * Author URI:        https://widigital.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       article-rewriter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('ARTICLE_REWRITER_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-article-rewriter-activator.php
 */
function activate_article_rewriter() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-article-rewriter-activator.php';
    Article_Rewriter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-article-rewriter-deactivator.php
 */
function deactivate_article_rewriter() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-article-rewriter-deactivator.php';
    Article_Rewriter_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_article_rewriter');
register_deactivation_hook(__FILE__, 'deactivate_article_rewriter');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-article-rewriter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_article_rewriter() {
    $plugin = new Article_Rewriter();
    $plugin->run();
}
run_article_rewriter();