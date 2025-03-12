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
            array($this, 'render_main_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 18C5.59 18 2 14.41 2 10C2 5.59 5.59 2 10 2C14.41 2 18 5.59 18 10C18 14.41 14.41 18 10 18Z" fill="#A0A5AA"/>'),
            30
        );

        // Add submenu items
        add_submenu_page(
            'crawlaco',
            __('Setup Wizard', 'crawlaco'),
            __('Setup Wizard', 'crawlaco'),
            'manage_options',
            'crawlaco',
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
            __('Status', 'crawlaco'),
            __('Status', 'crawlaco'),
            'manage_options',
            'crawlaco-status',
            array($this, 'render_status_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('crawlaco_settings', 'crawlaco_website_key');
        register_setting('crawlaco_settings', 'crawlaco_setup_complete');
        register_setting('crawlaco_settings', 'crawlaco_setup_step');
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
                'success' => __('Success!', 'crawlaco'),
                'error' => __('Error:', 'crawlaco'),
                'validate' => __('Validate Key', 'crawlaco'),
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
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap crawlaco-admin">
            <h1><?php _e('Crawlaco Settings', 'crawlaco'); ?></h1>
            <div class="crawlaco-settings-content">
                <p><?php _e('Configure your Crawlaco plugin settings here.', 'crawlaco'); ?></p>
                <!-- Settings content will be added in Phase 2 -->
            </div>
        </div>
        <?php
    }

    /**
     * Render status page
     */
    public function render_status_page() {
        ?>
        <div class="wrap crawlaco-admin">
            <h1><?php _e('Crawlaco Status', 'crawlaco'); ?></h1>
            <div class="crawlaco-status-content">
                <p><?php _e('View your Crawlaco integration status here.', 'crawlaco'); ?></p>
                <!-- Status content will be added in Phase 2 -->
            </div>
        </div>
        <?php
    }
} 