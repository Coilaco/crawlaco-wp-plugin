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

        // Add AJAX handlers
        add_action('wp_ajax_crawlaco_update_settings', array($this, 'handle_settings_update'));
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Add main menu item with custom icon
        add_menu_page(
            __('Crawlaco', 'crawlaco'),
            __('Crawlaco', 'crawlaco'),
            'manage_options',
            'crawlaco',
            array($this, 'render_status_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 18C5.59 18 2 14.41 2 10C2 5.59 5.59 2 10 2C14.41 2 18 5.59 18 10C18 14.41 14.41 18 10 18Z" fill="#A0A5AA"/>'),
            100
        );

        // Add submenu items
        add_submenu_page(
            'crawlaco',
            __('Status', 'crawlaco'),
            __('Status', 'crawlaco'),
            'manage_options',
            'crawlaco',
            array($this, 'render_status_page')
        );
        
        add_submenu_page(
            'crawlaco',
            __('Setup Wizard', 'crawlaco'),
            __('Setup Wizard', 'crawlaco'),
            'manage_options',
            'crawlaco-setup-wizard',
            array($this, 'render_setup_wizard_page')
        );

        add_submenu_page(
            'crawlaco',
            __('Settings', 'crawlaco'),
            __('Settings', 'crawlaco'),
            'manage_options',
            'crawlaco-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'crawlaco',
            __('Dashboard', 'crawlaco'),
            __('Login to Crawlaco', 'crawlaco'),
            'manage_options',
            'crawlaco-dashboard',
            array($this, 'redirect_to_dashboard')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('crawlaco_settings', 'crawlaco_website_key');
        register_setting('crawlaco_settings', 'crawlaco_setup_complete');
        register_setting('crawlaco_settings', 'crawlaco_setup_step');
        register_setting('crawlaco_settings', 'crawlaco_wp_api_key');
        register_setting('crawlaco_settings', 'crawlaco_wc_api_keys');
        // Add attribute mapping settings
        register_setting('crawlaco_settings', 'crawlaco_size_attr_id');
        register_setting('crawlaco_settings', 'crawlaco_color_attr_id');
        register_setting('crawlaco_settings', 'crawlaco_brand_attr_id');
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'crawlaco') === false) {
            return;
        }

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
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crawlaco-admin-nonce'),
            'strings' => array(
                'validating' => __('Validating...', 'crawlaco'),
                'generating' => __('Generating API Keys...', 'crawlaco'),
                'success' => __('Success!', 'crawlaco'),
                'error' => __('Error:', 'crawlaco'),
                'validate' => __('Validate Key', 'crawlaco'),
                'generate' => __('Generate API Keys', 'crawlaco'),
                'fetching' => __('Fetching data...', 'crawlaco'),
                'fetching_timeout' => __('Data fetching timed out. Please try again.', 'crawlaco'),
                'fetching_failed' => __('Data fetching failed. Please try again.', 'crawlaco'),
                'fetching_success' => __('Data fetched successfully!', 'crawlaco'),
                'start_sync' => __('Start Data Sync', 'crawlaco'),
                'retry_sync' => __('Retry', 'crawlaco'),
                'saving_attributes' => __('Saving attribute mappings...', 'crawlaco'),
                'attributes_saved' => __('Attribute mappings saved successfully!', 'crawlaco'),
                'attributes_failed' => __('Failed to save attribute mappings.', 'crawlaco'),
                'finalizing_setup' => __('Finalizing setup...', 'crawlaco'),
                'setup_complete' => __('Setup completed successfully! Redirecting to dashboard...', 'crawlaco'),
                'setup_failed' => __('Failed to complete setup.', 'crawlaco'),
            ),
        ));
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
            <div class="wrap crawlaco-admin">
                <h1 class="crawlaco-header"><?php _e('Crawlaco Settings', 'crawlaco'); ?></h1>
                <div class="notice notice-warning">
                    <p>
                        <?php _e('Please complete the setup process before accessing the settings page.', 'crawlaco'); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco')); ?>" class="button button-primary">
                            <?php _e('Go to Setup Wizard', 'crawlaco'); ?>
                        </a>
                    </p>
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
        // Debug logging
        error_log('Crawlaco Settings Update - POST data: ' . print_r($_POST, true));
        
        // Verify nonce
        if (!isset($_POST['crawlaco_settings_nonce'])) {
            error_log('Crawlaco Settings Update - Nonce field not found in POST data');
            wp_send_json_error(array('message' => __('Security check failed. Please try again.', 'crawlaco')));
        }

        if (!wp_verify_nonce($_POST['crawlaco_settings_nonce'], 'crawlaco_update_settings')) {
            error_log('Crawlaco Settings Update - Nonce verification failed');
            wp_send_json_error(array('message' => __('Security check failed. Please try again.', 'crawlaco')));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions to perform this action.', 'crawlaco')));
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

        wp_send_json_success(array('message' => __('Settings saved successfully.', 'crawlaco')));
    }
} 