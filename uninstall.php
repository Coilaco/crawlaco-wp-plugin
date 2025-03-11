<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('crawlaco_setup_complete');
delete_option('crawlaco_website_key');
delete_option('crawlaco_api_keys');
delete_option('crawlaco_attribute_mappings');

// Delete any custom database tables if they exist
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}crawlaco_logs"); 