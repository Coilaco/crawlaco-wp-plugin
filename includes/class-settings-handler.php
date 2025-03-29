<?php
/**
 * Settings Handler
 *
 * @package Crawlaco
 */

if (!defined('ABSPATH')) {
    exit;
}

class Crawlaco_Settings_Handler {
    /**
     * Initialize the settings handler
     */
    public function __construct() {
        add_action('admin_post_crawlaco_update_settings', array($this, 'handle_settings_update'));
    }

    /**
     * Handle settings form submission
     */
    public function handle_settings_update() {
        // Verify nonce with proper sanitization
        if (!isset($_POST['crawlaco_settings_nonce']) || 
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['crawlaco_settings_nonce'])), 
                'crawlaco_update_settings'
            )
        ) {
            wp_send_json_error(array('message' => esc_html__('Invalid nonce. Please try again.', 'crawlaco')));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have sufficient permissions to perform this action.', 'crawlaco')));
        }

        // Get and sanitize mapped attributes
        $mapped_attributes = isset($_POST['mapped_attributes']) ? wp_unslash($_POST['mapped_attributes']) : array();
        $mapped_attributes = map_deep($mapped_attributes, 'sanitize_text_field');

        // Validate mapped attributes
        $validated_attributes = array();
        foreach ($mapped_attributes as $key => $value) {
            if (!empty($value)) {
                // Validate the key is a valid attribute ID
                $key = absint($key);
                if ($key > 0) {
                    // Sanitize the value based on its expected type
                    $value = sanitize_text_field($value);
                    $validated_attributes[$key] = $value;
                }
            }
        }

        // Save to WordPress options
        update_option('crawlaco_mapped_attributes', $validated_attributes);

        // Send to Crawlaco API
        $api = new Crawlaco_API();
        $response = $api->update_meta_data($validated_attributes);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success(array('message' => esc_html__('Settings saved successfully.', 'crawlaco')));
    }
}

// Initialize the settings handler
new Crawlaco_Settings_Handler(); 