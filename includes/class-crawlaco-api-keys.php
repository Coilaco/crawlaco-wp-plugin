<?php
/**
 * Crawlaco API Keys Class
 */
class Crawlaco_API_Keys {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_generate_api_keys', array($this, 'ajax_generate_api_keys'));
    }

    /**
     * Generate API keys via AJAX
     */
    public function ajax_generate_api_keys() {
        check_ajax_referer('crawlaco-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'crawlaco')
            ));
        }

        // Generate WordPress API key
        $wp_api_key = $this->generate_wp_api_key();
        if (is_wp_error($wp_api_key)) {
            wp_send_json_error(array(
                'message' => $wp_api_key->get_error_message()
            ));
        }

        // Generate WooCommerce API keys if WooCommerce is active and properly loaded
        $wc_api_keys = array();
        if ($this->is_woocommerce_active() && $this->is_woocommerce_loaded()) {
            $wc_api_keys = $this->generate_wc_api_keys();
            if (is_wp_error($wc_api_keys)) {
                wp_send_json_error(array(
                    'message' => $wc_api_keys->get_error_message()
                ));
            }
        }

        // Send API keys to Crawlaco backend
        $api = new Crawlaco_API();
        $response = $api->send_api_keys($wp_api_key, $wc_api_keys);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }

        // Save API keys and update step
        update_option('crawlaco_wp_api_key', $wp_api_key);
        if (!empty($wc_api_keys)) {
            update_option('crawlaco_wc_api_keys', $wc_api_keys);
        }
        update_option('crawlaco_setup_step', 3);

        wp_send_json_success(array(
            'message' => __('API keys generated and sent successfully!', 'crawlaco'),
            'redirect' => admin_url('admin.php?page=crawlaco')
        ));
    }

    /**
     * Generate WordPress API key
     */
    private function generate_wp_api_key() {
        try {
            // Generate a unique key
            $api_key = 'ck_' . wp_generate_password(32, false);
            
            // Get current user
            $current_user = wp_get_current_user();
            
            // Create application password
            $app_pass = WP_Application_Passwords::create_new_application_password(
                $current_user->ID,
                array(
                    'name' => 'Crawlaco Integration',
                    'app_id' => 'crawlaco'
                )
            );

            if (is_wp_error($app_pass)) {
                return new WP_Error(
                    'app_pass_error',
                    __('Failed to generate WordPress API key.', 'crawlaco')
                );
            }

            return array(
                'key' => $api_key,
                'secret' => $app_pass[0], // The plain-text password
                'user_id' => $current_user->ID,
                'username' => $current_user->user_login
            );
        } catch (Exception $e) {
            return new WP_Error(
                'wp_api_error',
                __('Failed to generate WordPress API key.', 'crawlaco')
            );
        }
    }

    /**
     * Generate WooCommerce API keys
     */
    private function generate_wc_api_keys() {
        try {
            // Make sure WooCommerce is loaded
            if (!function_exists('WC')) {
                return new WP_Error(
                    'wc_not_loaded',
                    __('WooCommerce is not properly loaded.', 'crawlaco')
                );
            }

            // Include the admin API key class if not already included
            if (!class_exists('WC_Admin_API_Keys')) {
                include_once WC_ABSPATH . 'includes/admin/class-wc-admin-api-keys.php';
                
                if (!class_exists('WC_Admin_API_Keys')) {
                    return new WP_Error(
                        'wc_api_error',
                        __('WooCommerce API class could not be loaded.', 'crawlaco')
                    );
                }
            }

            // Get current user
            $current_user = wp_get_current_user();

            // Create WooCommerce API key
            $description = 'Crawlaco Integration';
            $permissions = 'read_write';
            $user_id = $current_user->ID;

            // Generate API key
            $key_id = wc_api_hash(uniqid('crawlaco_', true));
            
            // Create consumer key and secret
            $consumer_key = 'ck_' . wc_rand_hash();
            $consumer_secret = 'cs_' . wc_rand_hash();

            // Store the new key
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_api_keys',
                array(
                    'user_id' => $user_id,
                    'description' => $description,
                    'permissions' => $permissions,
                    'consumer_key' => wc_api_hash($consumer_key),
                    'consumer_secret' => $consumer_secret,
                    'truncated_key' => substr($consumer_key, -7)
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                )
            );

            if (!$wpdb->insert_id) {
                return new WP_Error(
                    'wc_api_error',
                    __('Failed to store WooCommerce API key in the database.', 'crawlaco')
                );
            }

            return array(
                'key' => $consumer_key,
                'secret' => $consumer_secret
            );
        } catch (Exception $e) {
            return new WP_Error(
                'wc_api_error',
                __('Failed to generate WooCommerce API keys.', 'crawlaco')
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
     * Check if WooCommerce is properly loaded
     */
    private function is_woocommerce_loaded() {
        return function_exists('WC') && defined('WC_ABSPATH');
    }
} 