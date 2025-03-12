<?php
/**
 * Crawlaco API Class
 */
class Crawlaco_API {
    /**
     * API Base URL
     */
    // TODO: Change to the production URL
    private $api_base_url = 'http://127.0.0.1:9001';
    // private $api_base_url = 'https://api.crawlaco.com';

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize API endpoints
        add_action('rest_api_init', array($this, 'register_endpoints'));
        
        // Add AJAX handlers
        add_action('wp_ajax_validate_website_key', array($this, 'ajax_validate_website_key'));
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
     * Validate website key via AJAX
     */
    public function ajax_validate_website_key() {
        check_ajax_referer('crawlaco-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'crawlaco')
            ));
        }

        $website_key = sanitize_text_field($_POST['website_key']);
        if (empty($website_key)) {
            wp_send_json_error(array(
                'message' => __('Website key is required.', 'crawlaco')
            ));
        }

        $response = $this->validate_website_key($website_key);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }

        // Save the website key and mark step 1 as complete
        update_option('crawlaco_website_key', $website_key);
        update_option('crawlaco_setup_step', 2);
        
        wp_send_json_success(array(
            'message' => __('Website key validated successfully!', 'crawlaco'),
            'redirect' => admin_url('admin.php?page=crawlaco')
        ));
    }

    /**
     * Validate website key with Crawlaco API
     */
    private function validate_website_key($website_key) {
        $headers = array(
            'website-key' => $website_key,
            'website-address' => get_site_url()
        );

        $response = wp_remote_get(
            $this->api_base_url . '/websites/plugin/websites/',
            array(
                'headers' => $headers,
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                __('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : __('Invalid website key. Please check and try again.', 'crawlaco');
            
            return new WP_Error('invalid_key', $error_message);
        }

        return true;
    }

    /**
     * Send API keys to Crawlaco backend
     */
    public function send_api_keys($wp_api_key, $wc_api_keys = array()) {
        $website_key = get_option('crawlaco_website_key');
        
        if (empty($website_key)) {
            return new WP_Error(
                'missing_key',
                __('Website key not found. Please complete step 1 first.', 'crawlaco')
            );
        }

        $meta_data = array(
            array(
                'key' => 'WP_SECRET_KEY',
                'value' => $wp_api_key['secret'],
                'is_password' => true
            ),
            array(
                'key' => 'WP_USERNAME',
                'value' => $wp_api_key['username'],
                'is_password' => true
            )
        );

        if (!empty($wc_api_keys)) {
            $meta_data[] = array(
                'key' => 'WC_CONSUMER_KEY',
                'value' => $wc_api_keys['key'],
                'is_password' => true
            );
            $meta_data[] = array(
                'key' => 'WC_CONSUMER_SECRET',
                'value' => $wc_api_keys['secret'],
                'is_password' => true
            );
        }

        $response = wp_remote_post(
            $this->api_base_url . '/websites/plugin/meta-data/',
            array(
                'headers' => array(
                    'website-key' => $website_key,
                    'website-address' => get_site_url(),
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($meta_data),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                __('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 201) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : __('Failed to send API keys to Crawlaco. Please try again.', 'crawlaco');
            
            return new WP_Error('send_failed', $error_message);
        }

        return true;
    }
} 