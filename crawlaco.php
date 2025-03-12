<?php
/**
 * Plugin Name: Crawlaco
 * Plugin URI: https://crawlaco.com
 * Description: A bridge between WordPress/WooCommerce and the Crawlaco dashboard for seamless integration and data synchronization.
 * Version: 1.0.0
 * Author: Crawlaco
 * Author URI: https://crawlaco.com
 * Text Domain: crawlaco
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CRAWLACO_VERSION', '1.0.0');
define('CRAWLACO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CRAWLACO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRAWLACO_MIN_WP_VERSION', '5.0');
define('CRAWLACO_MIN_PHP_VERSION', '7.2');

// Include required files
require_once CRAWLACO_PLUGIN_DIR . 'includes/class-crawlaco-api.php';
require_once CRAWLACO_PLUGIN_DIR . 'includes/class-crawlaco-admin.php';
require_once CRAWLACO_PLUGIN_DIR . 'includes/class-crawlaco-api-keys.php';

/**
 * Plugin activation hook
 */
function crawlaco_plugin_activation() {
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), CRAWLACO_MIN_WP_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(sprintf(
            __('Crawlaco requires WordPress version %s or higher.', 'crawlaco'),
            CRAWLACO_MIN_WP_VERSION
        ));
    }

    // Check PHP version
    if (version_compare(PHP_VERSION, CRAWLACO_MIN_PHP_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(sprintf(
            __('Crawlaco requires PHP version %s or higher.', 'crawlaco'),
            CRAWLACO_MIN_PHP_VERSION
        ));
    }

    // Create necessary options
    add_option('crawlaco_website_key', '');
    add_option('crawlaco_setup_complete', false);
    add_option('crawlaco_setup_step', 1);
    add_option('crawlaco_plugin_activated', true);
    add_option('crawlaco_wp_api_key', '');
    add_option('crawlaco_wc_api_keys', array());
    
    // Create plugin directories if they don't exist
    $upload_dir = wp_upload_dir();
    $crawlaco_dir = $upload_dir['basedir'] . '/crawlaco';
    
    if (!file_exists($crawlaco_dir)) {
        wp_mkdir_p($crawlaco_dir);
    }
    
    // Create or update .htaccess to protect the directory
    $htaccess_file = $crawlaco_dir . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        $htaccess_content = "Order deny,allow\nDeny from all";
        file_put_contents($htaccess_file, $htaccess_content);
    }
    
    // Create index.php to prevent directory listing
    $index_file = $crawlaco_dir . '/index.php';
    if (!file_exists($index_file)) {
        file_put_contents($index_file, '<?php // Silence is golden');
    }
    
    // Clear permalinks
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'crawlaco_plugin_activation');

/**
 * Plugin deactivation hook
 */
function crawlaco_plugin_deactivation() {
    // Remove activation flag
    delete_option('crawlaco_plugin_activated');
    
    // Clear API keys
    delete_option('crawlaco_wp_api_key');
    delete_option('crawlaco_wc_api_keys');
    
    // Clear permalinks
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'crawlaco_plugin_deactivation');

/**
 * Initialize the plugin
 */
function crawlaco_init() {
    // Initialize admin class
    if (is_admin()) {
        new Crawlaco_Admin();
    }
    
    // Initialize API class
    new Crawlaco_API();
    
    // Initialize API Keys class
    new Crawlaco_API_Keys();
    
    // Load text domain for translations
    load_plugin_textdomain('crawlaco', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'crawlaco_init');

/**
 * Display admin notice if setup is not complete
 */
function crawlaco_admin_notice() {
    if (!get_option('crawlaco_setup_complete') && current_user_can('manage_options')) {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('Please complete the Crawlaco plugin setup to start using its features.', 'crawlaco'); ?>
                <a href="<?php echo admin_url('admin.php?page=crawlaco'); ?>"><?php _e('Complete Setup', 'crawlaco'); ?></a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'crawlaco_admin_notice');

/**
 * Add settings link on plugin page
 */
function crawlaco_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=crawlaco') . '">' . __('Settings', 'crawlaco') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'crawlaco_plugin_action_links'); 