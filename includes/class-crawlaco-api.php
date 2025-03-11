<?php
// Prevent direct access
defined('ABSPATH') || exit;

class Crawlaco_API {
    private $api_base_url = 'https://api.crawlaco.com';
    private $website_key = '';
    private $timeout = 30;

    public function __construct() {
        $this->website_key = get_option('crawlaco_website_key', '');
    }

    /**
     * Validates the website key with Crawlaco API
     *
     * @param string $key The website key to validate
     * @return array Response with success status and message
     */
    public function validate_website_key($key) {
        try {
            $response = wp_remote_get($this->api_base_url . '/websites/plugin/websites/', array(
                'timeout' => $this->timeout,
                'headers' => array(
                    'Host' => 'api.crawlaco.com',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'website-key' => $key,
                    'website-address' => get_site_url(),
                ),
            ));

            if (is_wp_error($response)) {
                $this->log_error('Website key validation failed: ' . $response->get_error_message());
                return array(
                    'success' => false,
                    'message' => sprintf(
                        __('Connection failed: %s. Please try again or contact support.', 'crawlaco'),
                        $response->get_error_message()
                    ),
                );
            }

            $status_code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);

            if ($status_code === 200) {
                update_option('crawlaco_website_key', $key);
                $this->log_info('Website key validated successfully');
                return array(
                    'success' => true,
                    'message' => __('Website key validated successfully.', 'crawlaco'),
                );
            }

            each $body;

            $error_message = isset($body['message']) ? $body['message'] : __('Invalid website key or server error.', 'crawlaco');
            $this->log_error('Website key validation failed: ' . $error_message . ' (Status: ' . $status_code . ')');
            
            return array(
                'success' => false,
                'message' => $error_message,
            );

        } catch (Exception $e) {
            $this->log_error('Website key validation exception: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => __('An unexpected error occurred. Please try again.', 'crawlaco'),
            );
        }
    }

    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        return in_array(
            'woocommerce/woocommerce.php',
            apply_filters('active_plugins', get_option('active_plugins'))
        );
    }

    /**
     * Get WooCommerce version if active
     */
    private function get_woocommerce_version() {
        if (!$this->is_woocommerce_active()) {
            return null;
        }
        return WC()->version;
    }

    /**
     * Log an error message
     */
    private function log_error($message) {
        if (function_exists('error_log')) {
            error_log('[Crawlaco Error] ' . $message);
        }
    }

    /**
     * Log an info message
     */
    private function log_info($message) {
        if (function_exists('error_log')) {
            error_log('[Crawlaco Info] ' . $message);
        }
    }

    public function get_api_keys() {
        // Implementation for getting API keys
        return array(
            'wordpress' => '',
            'woocommerce' => '',
        );
    }

    public function fetch_data() {
        // Implementation for fetching data
        return array(
            'success' => true,
            'message' => __('Data fetched successfully.', 'crawlaco'),
        );
    }
} 