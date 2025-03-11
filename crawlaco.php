<?php
/**
 * Plugin Name: Crawlaco | کرالاکو
 * Plugin URI: https://crawlaco.com
 * Description: Bridge between WordPress/WooCommerce and Crawlaco dashboard
 * Version: 1.0.0
 * Author: Crawlaco
 * Text Domain: crawlaco
 * Domain Path: /languages
 * Requires PHP: 7.4
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Define plugin constants
define('CRAWLACO_VERSION', '1.0.0');
define('CRAWLACO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CRAWLACO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRAWLACO_MIN_WP_VERSION', '5.0');
define('CRAWLACO_MIN_PHP_VERSION', '7.4');

// Include required files
require_once CRAWLACO_PLUGIN_DIR . 'includes/class-crawlaco.php';
require_once CRAWLACO_PLUGIN_DIR . 'includes/class-crawlaco-api.php';
require_once CRAWLACO_PLUGIN_DIR . 'includes/admin/class-crawlaco-admin.php';

// Plugin activation hook
register_activation_hook(__FILE__, 'crawlaco_plugin_activation');

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

    // Create database tables
    crawlaco_create_tables();
    
    // Set default options
    add_option('crawlaco_setup_complete', false);
    add_option('crawlaco_website_key', '');
    add_option('crawlaco_api_keys', array());
    add_option('crawlaco_attribute_mappings', array());

    // Clear permalinks
    flush_rewrite_rules();
}

function crawlaco_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create logs table
    $table_name = $wpdb->prefix . 'crawlaco_logs';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        level varchar(20) NOT NULL,
        message text NOT NULL,
        context text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'crawlaco_plugin_deactivation');
function crawlaco_plugin_deactivation() {
    // Clean up if needed
    flush_rewrite_rules();
}

// Initialize the plugin
add_action('plugins_loaded', 'crawlaco_init');

function crawlaco_init() {
    // Initialize the main plugin class
    $crawlaco = Crawlaco::get_instance();
    
    // Initialize admin interface if in admin area
    if (is_admin()) {
        require_once CRAWLACO_PLUGIN_DIR . 'includes/admin/class-crawlaco-admin.php';
        new Crawlaco_Admin();
    }
}

// Add AJAX handlers
add_action('wp_ajax_crawlaco_validate_website_key', 'crawlaco_validate_website_key');

function crawlaco_validate_website_key() {
    // Verify nonce
    check_ajax_referer('crawlaco-admin-nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Permission denied.', 'crawlaco'),
            'code' => 'permission_denied'
        ));
    }

    $website_key = sanitize_text_field($_POST['website_key']);
    
    if (empty($website_key)) {
        wp_send_json_error(array(
            'message' => __('Website key cannot be empty.', 'crawlaco'),
            'code' => 'empty_key'
        ));
    }

    // Initialize API class and validate key
    $api = new Crawlaco_API();
    $result = $api->validate_website_key($website_key);

    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'code' => 'success'
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message'],
            'code' => 'validation_failed'
        ));
    }
}

// Display warning notification if setup is not complete
add_action('admin_notices', 'crawlaco_admin_notices');

function crawlaco_admin_notices() {
    if (!get_option('crawlaco_setup_complete')) {
        ?>
        <div class="notice notice-warning">
            <p><?php _e('Please complete the Crawlaco plugin setup to start using its features.', 'crawlaco'); ?> 
               <a href="<?php echo admin_url('admin.php?page=crawlaco-setup'); ?>"><?php _e('Complete Setup', 'crawlaco'); ?></a>
            </p>
        </div>
        <?php
    }
} 