<?php
/**
 * Crawlaco API Class
 */
class Crawlaco_API {
    /**
     * API Base URL
     */
    // private $api_base_url = 'https://api.crawlaco.com';
    private $api_base_url = 'http://127.0.0.1:9001';

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize API endpoints
        add_action('rest_api_init', array($this, 'register_endpoints'));
        
        // Add AJAX handlers
        add_action('wp_ajax_validate_website_key', array($this, 'ajax_validate_website_key'));
        add_action('wp_ajax_initiate_data_fetch', array($this, 'ajax_initiate_data_fetch'));
        add_action('wp_ajax_check_task_status', array($this, 'ajax_check_task_status'));
        add_action('wp_ajax_save_attribute_mapping', array($this, 'ajax_save_attribute_mapping'));
        add_action('wp_ajax_finalize_setup', array($this, 'ajax_finalize_setup'));
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
                'message' => esc_html__('You do not have permission to perform this action.', 'crawlaco')
            ));
        }

        $website_key = isset($_POST['website_key']) ? sanitize_text_field(wp_unslash($_POST['website_key'])) : '';
        if (empty($website_key)) {
            wp_send_json_error(array(
                'message' => esc_html__('Website key is required.', 'crawlaco')
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
            'message' => esc_html__('Website key validated successfully!', 'crawlaco'),
            'redirect' => esc_url(admin_url('admin.php?page=crawlaco-setup-wizard'))
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
                'host' => 'api.crawlaco.com',
                'headers' => $headers,
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : esc_html__('Invalid website key. Please check and try again.', 'crawlaco');
            
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
                esc_html__('Website key not found. Please complete step 1 first.', 'crawlaco')
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
                    'host' => 'api.crawlaco.com',
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
                esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 201) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : esc_html__('Failed to send API keys to Crawlaco. Please try again.', 'crawlaco');
            
            return new WP_Error('send_failed', $error_message);
        }

        return true;
    }

    /**
     * Initiate data fetching via AJAX
     */
    public function ajax_initiate_data_fetch() {
        check_ajax_referer('crawlaco-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to perform this action.', 'crawlaco')
            ));
        }

        $response = $this->initiate_data_fetch();
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }

        wp_send_json_success(array(
            'message' => esc_html__('Data fetching initiated successfully!', 'crawlaco'),
            'taskId' => $response
        ));
    }

    /**
     * Check task status via AJAX
     */
    public function ajax_check_task_status() {
        check_ajax_referer('crawlaco-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to perform this action.', 'crawlaco')
            ));
        }

        $task_id = isset($_POST['task_id']) ? sanitize_text_field(wp_unslash($_POST['task_id'])) : '';
        if (empty($task_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('Task ID is required.', 'crawlaco')
            ));
        }

        $response = $this->check_task_status($task_id);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }

        wp_send_json_success($response);
    }

    /**
     * Initiate data fetching with Crawlaco API
     */
    private function initiate_data_fetch() {
        $website_key = get_option('crawlaco_website_key');
        
        if (empty($website_key)) {
            return new WP_Error(
                'missing_key',
                esc_html__('Website key not found. Please complete step 1 first.', 'crawlaco')
            );
        }

        $response = wp_remote_post(
            $this->api_base_url . '/websites/plugin/fetch-all/',
            array(
                'headers' => array(
                    'host' => 'api.crawlaco.com',
                    'website-key' => $website_key,
                    'website-address' => get_site_url(),
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 202) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : esc_html__('Failed to initiate data fetching. Please try again.', 'crawlaco');
            
            return new WP_Error('init_failed', $error_message);
        }

        if (!isset($response_data['taskId'])) {
            return new WP_Error(
                'invalid_response',
                esc_html__('Invalid response from Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        return $response_data['taskId'];
    }

    /**
     * Check task status with Crawlaco API
     */
    private function check_task_status($task_id) {
        $website_key = get_option('crawlaco_website_key');
        
        if (empty($website_key)) {
            return new WP_Error(
                'missing_key',
                esc_html__('Website key not found. Please complete step 1 first.', 'crawlaco')
            );
        }

        $response = wp_remote_get(
            $this->api_base_url . '/common/tasks/' . $task_id . '/',
            array(
                'headers' => array(
                    'host' => 'api.crawlaco.com',
                    'website-key' => $website_key,
                    'website-address' => get_site_url(),
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : esc_html__('Failed to check task status. Please try again.', 'crawlaco');
            
            return new WP_Error('status_check_failed', $error_message);
        }

        if (!isset($response_data['status'])) {
            return new WP_Error(
                'invalid_response',
                esc_html__('Invalid response from Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        // If task is successful, mark step 3 as complete
        if ($response_data['status'] === 'SUCCESS') {
            update_option('crawlaco_setup_step', 4);
        }

        return array(
            'status' => $response_data['status'],
            'message' => isset($response_data['message']) ? $response_data['message'] : ''
        );
    }

    /**
     * Save attribute mapping via AJAX
     */
    public function ajax_save_attribute_mapping() {
        check_ajax_referer('crawlaco-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to perform this action.', 'crawlaco')
            ));
        }

        $size_attr_id = isset($_POST['size_attr_id']) ? sanitize_text_field(wp_unslash($_POST['size_attr_id'])) : '';
        $color_attr_id = isset($_POST['color_attr_id']) ? sanitize_text_field(wp_unslash($_POST['color_attr_id'])) : '';
        $brand_attr_id = isset($_POST['brand_attr_id']) ? sanitize_text_field(wp_unslash($_POST['brand_attr_id'])) : '';

        // Save attribute mappings to WordPress options
        update_option('crawlaco_size_attr_id', $size_attr_id);
        update_option('crawlaco_color_attr_id', $color_attr_id);
        update_option('crawlaco_brand_attr_id', $brand_attr_id);

        // Prepare meta data for API
        $meta_data = array();
        
        if (!empty($size_attr_id)) {
            $meta_data[] = array(
                'key' => 'SIZE_ATTR_ID',
                'value' => $size_attr_id
            );
        }
        
        if (!empty($color_attr_id)) {
            $meta_data[] = array(
                'key' => 'COLOR_ATTR_ID',
                'value' => $color_attr_id
            );
        }
        
        if (!empty($brand_attr_id)) {
            $meta_data[] = array(
                'key' => 'BRAND_ATTR_ID',
                'value' => $brand_attr_id
            );
        }

        // Send attribute mappings to Crawlaco API
        if (!empty($meta_data)) {
            $website_key = get_option('crawlaco_website_key');
            
            $response = wp_remote_post(
                $this->api_base_url . '/websites/plugin/meta-data/',
                array(
                    'headers' => array(
                        'host' => 'api.crawlaco.com',
                        'website-key' => $website_key,
                        'website-address' => get_site_url(),
                        'Content-Type' => 'application/json'
                    ),
                    'body' => json_encode($meta_data),
                    'timeout' => 30
                )
            );

            if (is_wp_error($response)) {
                wp_send_json_error(array(
                    'message' => esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
                ));
            }

            $response_code = wp_remote_retrieve_response_code($response);
            
            if ($response_code !== 201) {
                wp_send_json_error(array(
                    'message' => esc_html__('Failed to save attribute mappings. Please try again.', 'crawlaco')
                ));
            }
        }

        wp_send_json_success(array(
            'message' => esc_html__('Attribute mappings saved successfully!', 'crawlaco')
        ));
    }

    /**
     * Finalize setup via AJAX
     */
    public function ajax_finalize_setup() {
        check_ajax_referer('crawlaco-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to perform this action.', 'crawlaco')
            ));
        }

        $website_key = get_option('crawlaco_website_key');
        
        // Send PATCH request to mark setup as complete
        $response = wp_remote_request(
            $this->api_base_url . '/websites/plugin/websites/',
            array(
                'method' => 'PATCH',
                'headers' => array(
                    'host' => 'api.crawlaco.com',
                    'website-key' => $website_key,
                    'website-address' => get_site_url(),
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'step' => 'done',
                    'is_active' => true
                )),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            ));
        }

        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to complete setup. Please try again.', 'crawlaco')
            ));
        }

        // Mark setup as complete in WordPress
        update_option('crawlaco_setup_complete', true);
        update_option('crawlaco_setup_step', 4);

        wp_send_json_success(array(
            'message' => esc_html__('Setup completed successfully!', 'crawlaco'),
            'redirect' => esc_url(admin_url('admin.php?page=crawlaco'))
        ));
    }

    /**
     * Update meta data with Crawlaco API
     */
    public function update_meta_data($meta_data, $method = 'POST') {
        $website_key = get_option('crawlaco_website_key');
        
        if (empty($website_key)) {
            return new WP_Error(
                'missing_key',
                esc_html__('Website key not found. Please complete step 1 first.', 'crawlaco')
            );
        }

        $response = wp_remote_request(
            $this->api_base_url . '/websites/plugin/meta-data/',
            array(
                'method' => $method,
                'headers' => array(
                    'host' => 'api.crawlaco.com',
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
                esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        $response_data = json_decode($response_body, true);

        if ($response_code !== 201 && $response_code !== 200) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : esc_html__('Failed to update meta data. Please try again.', 'crawlaco');
            
            return new WP_Error('update_failed', $error_message);
        }

        return true;
    }

    /**
     * Get website information from API
     */
    public function get_website_info() {
        $website_key = get_option('crawlaco_website_key');
        
        if (empty($website_key)) {
            return new WP_Error(
                'missing_key',
                esc_html__('Website key not found. Please complete step 1 first.', 'crawlaco')
            );
        }

        $response = wp_remote_get(
            $this->api_base_url . '/websites/plugin/websites/',
            array(
                'headers' => array(
                    'host' => 'api.crawlaco.com',
                    'website-key' => $website_key,
                    'website-address' => get_site_url(),
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                esc_html__('Failed to connect to Crawlaco API. Please try again.', 'crawlaco')
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($response_data['message']) 
                ? $response_data['message'] 
                : esc_html__('Failed to fetch website information. Please try again.', 'crawlaco');
            
            return new WP_Error('fetch_failed', $error_message);
        }

        return $response_data;
    }

    /**
     * Update website status on plugin deactivation
     *
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_website_status_on_deactivation() {
        $website_key = get_option('crawlaco_website_key', '');
        
        if (empty($website_key)) {
            return true;
        }

        $response = wp_remote_request(
            $this->api_base_url . '/websites/plugin/websites/',
            array(
                'method' => 'PATCH',
                'headers' => array(
                    'host' => 'api.crawlaco.com',
                    'website-key' => $website_key,
                    'website-address' => get_site_url(),
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'is_active' => false
                ))
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %d: HTTP response code */
                    esc_html__('API request failed with code: %d', 'crawlaco'),
                    $response_code
                )
            );
        }

        return true;
    }
} 