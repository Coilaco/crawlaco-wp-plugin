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
            array($this, 'render_main_page')
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
                // Add new strings for attribute mapping
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
    public function render_main_page() {
        $current_step = get_option('crawlaco_setup_step', 1);
        $website_key = get_option('crawlaco_website_key', '');
        $setup_complete = get_option('crawlaco_setup_complete', false);
        ?>
        <div class="wrap crawlaco-admin">
            <h1><?php _e('Crawlaco Setup Wizard', 'crawlaco'); ?></h1>
            
            <div class="crawlaco-setup-progress">
                <ul class="steps">
                    <li class="<?php echo $current_step >= 1 ? 'active' : ''; ?>">
                        <?php _e('Website Key', 'crawlaco'); ?>
                    </li>
                    <li class="<?php echo $current_step >= 2 ? 'active' : ''; ?>">
                        <?php _e('API Keys', 'crawlaco'); ?>
                    </li>
                    <li class="<?php echo $current_step >= 3 ? 'active' : ''; ?>">
                        <?php _e('Data Sync', 'crawlaco'); ?>
                    </li>
                    <li class="<?php echo $current_step >= 4 ? 'active' : ''; ?>">
                        <?php _e('Attributes', 'crawlaco'); ?>
                    </li>
                </ul>
            </div>
            
            <div class="crawlaco-setup-wizard">
                <?php
                switch ($current_step) {
                    case 1:
                        $this->render_step_one();
                        break;
                    case 2:
                        $this->render_step_two();
                        break;
                    case 3:
                        $this->render_step_three();
                        break;
                    case 4:
                        $this->render_step_four();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render step one (Website Key validation)
     */
    private function render_step_one() {
        $website_key = get_option('crawlaco_website_key', '');
        $setup_complete = get_option('crawlaco_setup_complete', false);
        ?>
        <div class="crawlaco-setup-step active">
            <h2><?php _e('Step 1: Website Key Validation', 'crawlaco'); ?></h2>
            <p><?php _e('Enter your Website Key generated from the Crawlaco dashboard:', 'crawlaco'); ?></p>
            
            <form id="crawlaco-website-key-form" method="post" action="options.php">
                <?php settings_fields('crawlaco_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="crawlaco_website_key"><?php _e('Website Key', 'crawlaco'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="crawlaco_website_key" 
                                   name="crawlaco_website_key" 
                                   value="<?php echo esc_attr($website_key); ?>" 
                                   class="regular-text"
                                   <?php echo $setup_complete ? 'disabled' : ''; ?>
                            >
                            
                            <p class="description">
                                <?php _e('You can find your Website Key in your Crawlaco dashboard.', 'crawlaco'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="crawlaco-submit-wrapper">
                    <div class="crawlaco-message"></div>
                    <button type="submit" 
                            class="button button-primary" 
                            id="validate-website-key"
                            <?php echo $setup_complete ? 'disabled' : ''; ?>
                    >
                        <?php _e('Validate Key', 'crawlaco'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Render step two (API Key Generation)
     */
    private function render_step_two() {
        $wp_api_key = get_option('crawlaco_wp_api_key', '');
        $wc_api_keys = get_option('crawlaco_wc_api_keys', array());
        $has_woocommerce = in_array(
            'woocommerce/woocommerce.php',
            apply_filters('active_plugins', get_option('active_plugins'))
        );
        ?>
        <div class="crawlaco-setup-step active">
            <h2><?php _e('Step 2: API Key Generation', 'crawlaco'); ?></h2>
            <p><?php _e('We need to generate API keys to enable communication between your WordPress site and Crawlaco:', 'crawlaco'); ?></p>
            
            <div class="crawlaco-api-keys-status">
                <h3><?php _e('API Keys Status', 'crawlaco'); ?></h3>
                
                <table class="widefat">
                    <tr>
                        <td><strong><?php _e('WordPress API Key:', 'crawlaco'); ?></strong></td>
                        <td>
                            <?php if ($wp_api_key): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                <?php _e('Generated', 'crawlaco'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-no-alt" style="color: red;"></span>
                                <?php _e('Not Generated', 'crawlaco'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($has_woocommerce): ?>
                    <tr>
                        <td><strong><?php _e('WooCommerce API Keys:', 'crawlaco'); ?></strong></td>
                        <td>
                            <?php if (!empty($wc_api_keys)): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                <?php _e('Generated', 'crawlaco'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-no-alt" style="color: red;"></span>
                                <?php _e('Not Generated', 'crawlaco'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="crawlaco-submit-wrapper">
                <div class="crawlaco-message"></div>
                <button type="button" 
                        class="button button-primary" 
                        id="generate-api-keys"
                        <?php echo ($wp_api_key && (!$has_woocommerce || !empty($wc_api_keys))) ? 'disabled' : ''; ?>
                >
                    <?php 
                    if ($wp_api_key && (!$has_woocommerce || !empty($wc_api_keys))) {
                        _e('API Keys Generated', 'crawlaco');
                    } else {
                        _e('Generate API Keys', 'crawlaco');
                    }
                    ?>
                </button>

                <?php if ($wp_api_key && (!$has_woocommerce || !empty($wc_api_keys))): ?>
                    <button type="button" 
                            class="button button-secondary" 
                            id="proceed-to-step-three"
                    >
                        <?php _e('Proceed to Next Step', 'crawlaco'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render step three (Data Synchronization)
     */
    private function render_step_three() {
        ?>
        <div class="crawlaco-setup-step active">
            <h2><?php _e('Step 3: Data Synchronization', 'crawlaco'); ?></h2>
            <p><?php _e('We will now fetch essential data from your WordPress site to sync with Crawlaco:', 'crawlaco'); ?></p>
            
            <div class="crawlaco-data-sync-status">
                <div class="crawlaco-message"></div>
                <div class="crawlaco-progress" style="display: none;">
                    <div class="spinner is-active"></div>
                    <span class="progress-text"><?php _e('Fetching data...', 'crawlaco'); ?></span>
                </div>
                <button type="button" 
                        class="button button-primary" 
                        id="start-data-sync"
                >
                    <?php _e('Start Data Sync', 'crawlaco'); ?>
                </button>
                <button type="button" 
                        class="button button-secondary" 
                        id="retry-data-sync"
                        style="display: none;"
                >
                    <?php _e('Retry', 'crawlaco'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render step four (Attribute Mapping)
     */
    private function render_step_four() {
        // Check if WooCommerce is installed and active
        $has_woocommerce = in_array(
            'woocommerce/woocommerce.php',
            apply_filters('active_plugins', get_option('active_plugins'))
        );

        // Get saved attribute mappings
        $size_attr_id = get_option('crawlaco_size_attr_id', '');
        $color_attr_id = get_option('crawlaco_color_attr_id', '');
        $brand_attr_id = get_option('crawlaco_brand_attr_id', '');
        ?>
        <div class="crawlaco-setup-step active">
            <h2><?php _e('Step 4: Map Product Attributes', 'crawlaco'); ?></h2>
            
            <?php if (!$has_woocommerce): ?>
                <div class="notice notice-warning">
                    <p>
                        <?php _e('WooCommerce is not installed or inactive. You can skip this step.', 'crawlaco'); ?>
                    </p>
                    <p>
                        <button type="button" class="button button-primary" id="crawlaco-finish-setup">
                            <?php _e('Finish Setup', 'crawlaco'); ?>
                        </button>
                    </p>
                </div>
            <?php else: ?>
                <p class="description">
                    <?php _e('Map your WooCommerce product attributes to help Crawlaco understand your data structure:', 'crawlaco'); ?>
                </p>

                <form id="crawlaco-attribute-mapping-form" method="post">
                    <table class="form-table">
                        <?php
                        // Get all product attributes
                        $attribute_taxonomies = wc_get_attribute_taxonomies();
                        ?>
                        <tr>
                            <th scope="row">
                                <label for="crawlaco_size_attr_id"><?php _e('Size Attribute', 'crawlaco'); ?></label>
                            </th>
                            <td>
                                <select name="crawlaco_size_attr_id" id="crawlaco_size_attr_id">
                                    <option value=""><?php _e('-- Select Size Attribute --', 'crawlaco'); ?></option>
                                    <?php foreach ($attribute_taxonomies as $attribute): ?>
                                        <option value="<?php echo esc_attr($attribute->attribute_id); ?>"
                                                <?php selected($size_attr_id, $attribute->attribute_id); ?>>
                                            <?php echo esc_html($attribute->attribute_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="crawlaco_color_attr_id"><?php _e('Color Attribute', 'crawlaco'); ?></label>
                            </th>
                            <td>
                                <select name="crawlaco_color_attr_id" id="crawlaco_color_attr_id">
                                    <option value=""><?php _e('-- Select Color Attribute --', 'crawlaco'); ?></option>
                                    <?php foreach ($attribute_taxonomies as $attribute): ?>
                                        <option value="<?php echo esc_attr($attribute->attribute_id); ?>"
                                                <?php selected($color_attr_id, $attribute->attribute_id); ?>>
                                            <?php echo esc_html($attribute->attribute_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="crawlaco_brand_attr_id"><?php _e('Brand Attribute', 'crawlaco'); ?></label>
                            </th>
                            <td>
                                <select name="crawlaco_brand_attr_id" id="crawlaco_brand_attr_id">
                                    <option value=""><?php _e('-- Select Brand Attribute --', 'crawlaco'); ?></option>
                                    <?php foreach ($attribute_taxonomies as $attribute): ?>
                                        <option value="<?php echo esc_attr($attribute->attribute_id); ?>"
                                                <?php selected($brand_attr_id, $attribute->attribute_id); ?>>
                                            <?php echo esc_html($attribute->attribute_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <div class="crawlaco-submit-wrapper">
                        <div class="crawlaco-message"></div>
                        <button type="submit" class="button button-primary" id="save-attribute-mapping">
                            <?php _e('Save Attribute Mapping', 'crawlaco'); ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php
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
                <h1><?php _e('Crawlaco Settings', 'crawlaco'); ?></h1>
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