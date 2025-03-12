<?php
/**
 * Crawlaco API Handler Class
 */
class Crawlaco_API {
    /**
     * API Base URL
     */
    private $api_base_url = 'http://127.0.0.1:9001';

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize API endpoints
        add_action('rest_api_init', array($this, 'register_endpoints'));
        
        // Add AJAX handlers
        add_action('wp_ajax_crawlaco_validate_website_key', array($this, 'ajax_validate_website_key'));
    }

    /**
     * Register REST API endpoints
     */
    public function register_endpoints() {
        register_rest_route('crawlaco/v1', '/validate-key', array(
            'methods' => 'POST',
            'callback' => array($this, 'validate_website_key_endpoint'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));
    }

    /**
     * Check if user has admin permissions
     */
    public function check_admin_permissions() {
        return current_user_can('manage_options');
    }

    /**
     * Validate website key via REST API endpoint
     */
    public function validate_website_key_endpoint($request) {
        $website_key = sanitize_text_field($request->get_param('website_key'));
        return $this->validate_website_key($website_key);
    }

    /**
     * AJAX handler for website key validation
     */
    public function ajax_validate_website_key() {
        check_ajax_referer('crawlaco-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Permission denied.', 'crawlaco'),
                'code' => 'permission_denied'
            ));
        }

        $website_key = sanitize_text_field($_POST['website_key']);
        $result = $this->validate_website_key($website_key);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Validate website key with Crawlaco backend
     */
    public function validate_website_key($website_key) {
        if (empty($website_key)) {
            return array(
                'success' => false,
                'message' => __('Website key cannot be empty.', 'crawlaco'),
                'code' => 'empty_key'
            );
        }

        $response = wp_remote_get($this->api_base_url . '/websites/plugin/websites/', array(
            'headers' => array(
                'host' => 'api.crawlaco.com',
                'website-key' => $website_key,
                'website-address' => get_site_url(),
            ),
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'code' => 'request_failed'
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code === 200) {
            update_option('crawlaco_website_key', $website_key);
            return array(
                'success' => true,
                'message' => __('Website key validated successfully.', 'crawlaco'),
                'code' => 'success',
                'data' => $body
            );
        }

        error_log(print_r($body, true));

        return array(
            'success' => false,
            'message' => isset($body['message']) ? $body['message'] : __('Invalid website key.', 'crawlaco'),
            'code' => 'validation_failed'
        );
    }
} 