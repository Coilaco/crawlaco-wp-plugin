<?php
/**
 * Crawlaco Admin Class
 */
class Crawlaco_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        // Add menu items
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Add deactivation confirmation
        add_action('admin_enqueue_scripts', array($this, 'enqueue_deactivation_script'));

        // Add AJAX handlers
        add_action('wp_ajax_crawlaco_update_settings', array($this, 'handle_settings_update'));
        add_action('wp_ajax_crawlaco_deactivate', array($this, 'handle_deactivation'));
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Add main menu item with custom icon
        add_menu_page(
            esc_html__('Crawlaco', 'crawlaco'),
            esc_html__('Crawlaco', 'crawlaco'),
            'manage_options',
            'crawlaco',
            array($this, 'render_status_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 120 120"><g fill="#262626"><path d="M103 74.4c-8 23-33 35.1-56 27.2s-35.1-33-27.2-56 33-35.1 56-27.2L81 3.3C49.8-7.5 15.6 9 4.7 40.4s5.7 65.5 37.1 76.3 65.5-5.7 76.3-37.1L103 74.4z"/><circle cx="110.6" cy="77" r="8"/><circle cx="78.5" cy="10.9" r="8"/><path d="M60 33.9c-14.4 0-26.1 11.7-26.1 26.1S45.6 86.1 60 86.1c14.4 0 26.1-11.7 26.1-26.1S74.4 33.9 60 33.9zm0 41.9c-8.7 0-15.8-7.1-15.8-15.8S51.3 44.2 60 44.2c8.7 0 15.8 7.1 15.8 15.8S68.7 75.8 60 75.8z"/></g></svg>'),
            100
        );

        // Add submenu items
        add_submenu_page(
            'crawlaco',
            esc_html__('Status', 'crawlaco'),
            esc_html__('Status', 'crawlaco'),
            'manage_options',
            'crawlaco',
            array($this, 'render_status_page')
        );
        
        add_submenu_page(
            'crawlaco',
            esc_html__('Setup Wizard', 'crawlaco'),
            esc_html__('Setup Wizard', 'crawlaco'),
            'manage_options',
            'crawlaco-setup-wizard',
            array($this, 'render_setup_wizard_page')
        );

        add_submenu_page(
            'crawlaco',
            esc_html__('Settings', 'crawlaco'),
            esc_html__('Settings', 'crawlaco'),
            'manage_options',
            'crawlaco-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'crawlaco',
            esc_html__('Dashboard', 'crawlaco'),
            esc_html__('Login to Crawlaco', 'crawlaco'),
            'manage_options',
            'crawlaco-dashboard',
            array($this, 'redirect_to_dashboard')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Website key setting
        register_setting(
            'crawlaco_settings',
            'crawlaco_website_key',
            array(
                'type' => 'string',
                'description' => 'Crawlaco website key for API authentication',
                'sanitize_callback' => array($this, 'sanitize_website_key'),
                'default' => '',
                'show_in_rest' => false
            )
        );
        
        // Setup complete flag
        register_setting(
            'crawlaco_settings',
            'crawlaco_setup_complete',
            array(
                'type' => 'boolean',
                'description' => 'Flag indicating if plugin setup is complete',
                'sanitize_callback' => array($this, 'sanitize_boolean'),
                'default' => false,
                'show_in_rest' => false
            )
        );
        
        // Setup step
        register_setting(
            'crawlaco_settings',
            'crawlaco_setup_step',
            array(
                'type' => 'integer',
                'description' => 'Current setup wizard step',
                'sanitize_callback' => array($this, 'sanitize_step'),
                'default' => 1,
                'show_in_rest' => false
            )
        );
        
        // WordPress API key
        register_setting(
            'crawlaco_settings',
            'crawlaco_wp_api_key',
            array(
                'type' => 'string',
                'description' => 'WordPress API key for Crawlaco integration',
                'sanitize_callback' => array($this, 'sanitize_api_key'),
                'default' => '',
                'show_in_rest' => false
            )
        );
        
        // WooCommerce API keys
        register_setting(
            'crawlaco_settings',
            'crawlaco_wc_api_keys',
            array(
                'type' => 'array',
                'description' => 'WooCommerce API keys for Crawlaco integration',
                'sanitize_callback' => array($this, 'sanitize_wc_api_keys'),
                'default' => array(),
                'show_in_rest' => false
            )
        );
        
        // Attribute mapping settings
        register_setting(
            'crawlaco_settings',
            'crawlaco_size_attr_id',
            array(
                'type' => 'integer',
                'description' => 'Size attribute ID for product mapping',
                'sanitize_callback' => array($this, 'sanitize_attribute_id'),
                'default' => 0,
                'show_in_rest' => false
            )
        );
        
        register_setting(
            'crawlaco_settings',
            'crawlaco_color_attr_id',
            array(
                'type' => 'integer',
                'description' => 'Color attribute ID for product mapping',
                'sanitize_callback' => array($this, 'sanitize_attribute_id'),
                'default' => 0,
                'show_in_rest' => false
            )
        );
        
        register_setting(
            'crawlaco_settings',
            'crawlaco_brand_attr_id',
            array(
                'type' => 'integer',
                'description' => 'Brand attribute ID for product mapping',
                'sanitize_callback' => array($this, 'sanitize_attribute_id'),
                'default' => 0,
                'show_in_rest' => false
            )
        );
    }

    /**
     * Sanitize website key
     *
     * @param string $key Website key to sanitize
     * @return string Sanitized website key
     */
    public function sanitize_website_key($key) {
        return sanitize_text_field($key);
    }

    /**
     * Sanitize boolean values
     *
     * @param mixed $value Value to sanitize
     * @return bool Sanitized boolean value
     */
    public function sanitize_boolean($value) {
        return (bool) $value;
    }

    /**
     * Sanitize setup step
     *
     * @param mixed $step Step value to sanitize
     * @return int Sanitized step value (1-5)
     */
    public function sanitize_step($step) {
        $step = absint($step);
        return min(max($step, 1), 5);
    }

    /**
     * Sanitize API key
     *
     * @param string $key API key to sanitize
     * @return string Sanitized API key
     */
    public function sanitize_api_key($key) {
        return sanitize_text_field($key);
    }

    /**
     * Sanitize WooCommerce API keys array
     *
     * @param array $keys Array of API keys
     * @return array Sanitized array of API keys
     */
    public function sanitize_wc_api_keys($keys) {
        if (!is_array($keys)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($keys as $key => $value) {
            $sanitized[sanitize_text_field($key)] = sanitize_text_field($value);
        }
        return $sanitized;
    }

    /**
     * Sanitize attribute ID
     *
     * @param mixed $id Attribute ID to sanitize
     * @return int Sanitized attribute ID
     */
    public function sanitize_attribute_id($id) {
        return absint($id);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'crawlaco') === false) {
            return;
        }

        wp_enqueue_style('dashicons');

        // Enqueue styles
        wp_enqueue_style(
            'crawlaco-admin',
            CRAWLACO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CRAWLACO_VERSION
        );

        // Enqueue scripts
        wp_enqueue_script(
            'crawlaco-admin',
            CRAWLACO_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            CRAWLACO_VERSION,
            true
        );

        // Localize script
        wp_localize_script('crawlaco-admin', 'crawlacoAdmin', array(
            'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => wp_create_nonce('crawlaco-admin-nonce'),
            'strings' => array(
                'validating' => esc_html__('Validating...', 'crawlaco'),
                'generating' => esc_html__('Generating API Keys...', 'crawlaco'),
                'success' => esc_html__('Success!', 'crawlaco'),
                'error' => esc_html__('Error:', 'crawlaco'),
                'validate' => esc_html__('Validate Key', 'crawlaco'),
                'generate' => esc_html__('Generate API Keys', 'crawlaco'),
                'fetching' => esc_html__('Fetching data...', 'crawlaco'),
                'fetching_timeout' => esc_html__('Data fetching timed out. Please try again.', 'crawlaco'),
                'fetching_failed' => esc_html__('Data fetching failed. Please try again.', 'crawlaco'),
                'fetching_success' => esc_html__('Data fetched successfully!', 'crawlaco'),
                'start_sync' => esc_html__('Start Data Sync', 'crawlaco'),
                'retry_sync' => esc_html__('Retry', 'crawlaco'),
                'saving_attributes' => esc_html__('Saving attribute mappings...', 'crawlaco'),
                'attributes_saved' => esc_html__('Attribute mappings saved successfully!', 'crawlaco'),
                'attributes_failed' => esc_html__('Failed to save attribute mappings.', 'crawlaco'),
                'finalizing_setup' => esc_html__('Finalizing setup...', 'crawlaco'),
                'setup_complete' => esc_html__('Setup completed successfully! Redirecting to dashboard...', 'crawlaco'),
                'setup_failed' => esc_html__('Failed to complete setup.', 'crawlaco'),
            ),
        ));
    }

    /**
     * Enqueue plugin deactivation confirmation script
     *
     * @param string $hook The current admin page
     */
    public function enqueue_deactivation_script($hook) {
        if ($hook !== 'plugins.php') {
            return;
        }

        // Enqueue styles
        wp_enqueue_style(
            'crawlaco-deactivation-modal',
            CRAWLACO_PLUGIN_URL . 'assets/css/deactivation-modal.css',
            array('dashicons'),
            CRAWLACO_VERSION
        );

        // Enqueue script
        wp_enqueue_script(
            'crawlaco-deactivation',
            CRAWLACO_PLUGIN_URL . 'assets/js/plugin-deactivation.js',
            array('jquery'),
            CRAWLACO_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'crawlaco-deactivation',
            'crawlacoDeactivation',
            array(
                'strings' => array(
                    'modalTitle' => esc_html__('Deactivate Crawlaco?', 'crawlaco'),
                    'modalMessage' => wp_kses_post(
                        __('Warning: Deactivating the <b>Crawlaco</b> plugin will result in the following:', 'crawlaco') . 
                        __('<ul> <li>All your settings and configurations will be permanently deleted</li> <li>Your website will be disabled in the Crawlaco dashboard</li> <li>You will need to go through the entire setup wizard process again when reactivating</li> <li>All API keys and connections will need to be reconfigured</li> </ul>', 'crawlaco') . 
                        __('Are you sure you want to deactivate?', 'crawlaco')
                    ),
                    'cancelButton' => esc_html__('Cancel', 'crawlaco'),
                    'deactivateButton' => esc_html__('Yes, Deactivate', 'crawlaco'),
                    'deactivating' => esc_html__('Deactivating...', 'crawlaco'),
                    'errorMessage' => esc_html__('An error occurred while deactivating the plugin. Please try again.', 'crawlaco')
                ),
                'nonce' => wp_create_nonce('crawlaco-deactivation-nonce'),
                'ajaxurl' => admin_url('admin-ajax.php')
            )
        );
    }

    /**
     * Render main page (Setup Wizard)
     */
    public function render_setup_wizard_page() {
        include CRAWLACO_PLUGIN_DIR . 'admin/pages/setup_wizard.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check if setup is complete
        $setup_complete = get_option('crawlaco_setup_complete', false);
        if (!$setup_complete) {
            ?>
            <div class="wrap">
                <h1 class="crawlaco-header"><?php esc_html_e('Crawlaco Settings', 'crawlaco'); ?></h1>
              
                <div class="crawlaco-admin">
                    <div class="notice notice-warning">
                        <p>
                            <?php esc_html_e('Please complete the setup process before accessing the settings page.', 'crawlaco'); ?>
                        </p>
                        <p>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-setup-wizard')); ?>" class="button button-primary">
                                <?php esc_html_e('Go to Setup Wizard', 'crawlaco'); ?>
                            </a>
                        </p>
                    </div>


                    <div class="crawlaco-completion-section">
                        <span class="dashicons dashicons-warning" style="color: #DBA617;"></span>
                        <h4><?php esc_html_e('Setup is not complete', 'crawlaco'); ?></h4>
                        <p><?php esc_html_e('Please complete the setup process before accessing the settings page.', 'crawlaco'); ?></p>
                    </div>

                    <div class="crawlaco-completion-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-setup-wizard')); ?>" 
                            class="button button-primary">
                                <?php esc_html_e('Go to Setup Wizard', 'crawlaco'); ?>
                            </a>
                    </div>
                </div>
            </div>
            <?php
            return;
        }

        // Get mapped attributes from both sources
        $mapped_attributes = get_option('crawlaco_mapped_attributes', array());
        
        // Convert mapped attributes to the format expected by the form
        $formatted_attributes = array();
        if (is_array($mapped_attributes)) {
            foreach ($mapped_attributes as $attr) {
                if (isset($attr['key']) && isset($attr['value'])) {
                    $formatted_attributes[$attr['key']] = $attr['value'];
                }
            }
        }
        
        // Get all WooCommerce attributes
        $wc_attributes = wc_get_attribute_taxonomies();

        include CRAWLACO_PLUGIN_DIR . 'admin/pages/settings.php';
    }

    /**
     * Render home page
     */
    public function render_status_page() {
        // Get website information
        $website_info = $this->get_website_info();
        if (is_wp_error($website_info)) {
            $error_message = $website_info->get_error_message();
        }

        include CRAWLACO_PLUGIN_DIR . 'admin/pages/status.php';
    }

    /**
     * Redirect to Crawlaco dashboard
     */
    public function redirect_to_dashboard() {
        wp_redirect(CRAWLACO_DASHBOARD_URL);
        exit;
    }

    /**
     * Get website information from API
     */
    private function get_website_info() {
        $api = new Crawlaco_API();
        return $api->get_website_info();
    }

    /**
     * Handle settings form submission
     */
    public function handle_settings_update() {
        // Verify nonce
        if (!isset($_POST['crawlaco_settings_nonce'])) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please try again.', 'crawlaco')));
        }

        if (!wp_verify_nonce($_POST['crawlaco_settings_nonce'], 'crawlaco_update_settings')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please try again.', 'crawlaco')));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have sufficient permissions to perform this action.', 'crawlaco')));
        }

        // Get mapped attributes
        $mapped_attributes = isset($_POST['mapped_attributes']) ? $_POST['mapped_attributes'] : array();

        // Format attributes for API
        $formatted_attributes = array();
        foreach ($mapped_attributes as $key => $value) {
            if (!empty($value)) {
                $formatted_attributes[] = array(
                    'key' => $key,
                    'value' => sanitize_text_field($value)
                );
            }
        }

        // Save to WordPress options
        update_option('crawlaco_mapped_attributes', $formatted_attributes);

        // Send to Crawlaco API
        $api = new Crawlaco_API();
        $response = $api->update_meta_data($formatted_attributes, 'PATCH');

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success(array('message' => esc_html__('Settings saved successfully.', 'crawlaco')));
    }

    /**
     * Handle plugin deactivation AJAX request
     */
    public function handle_deactivation() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'crawlaco-deactivation-nonce')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please try again.', 'crawlaco')));
        }

        // Check user capabilities
        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(array('message' => esc_html__('You do not have sufficient permissions to deactivate plugins.', 'crawlaco')));
        }

        // Call API to update website status
        $api = new Crawlaco_API();
        $result = $api->update_website_status_on_deactivation();

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success();
    }
} 