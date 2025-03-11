<?php
class Crawlaco_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        // Add main menu
        add_menu_page(
            __('Crawlaco', 'crawlaco'),
            __('Crawlaco', 'crawlaco'),
            'manage_options',
            'crawlaco',
            array($this, 'render_main_page'),
            'dashicons-admin-plugins',
            30
        );

        // Add submenus
        add_submenu_page(
            'crawlaco',
            __('Dashboard', 'crawlaco'),
            __('Dashboard', 'crawlaco'),
            'manage_options',
            'crawlaco',
            array($this, 'render_main_page')
        );

        add_submenu_page(
            'crawlaco',
            __('Setup Wizard', 'crawlaco'),
            __('Setup Wizard', 'crawlaco'),
            'manage_options',
            'crawlaco-setup',
            array($this, 'render_setup_wizard')
        );

        add_submenu_page(
            'crawlaco',
            __('Settings', 'crawlaco'),
            __('Settings', 'crawlaco'),
            'manage_options',
            'crawlaco-settings',
            array($this, 'render_settings_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'crawlaco') === false) {
            return;
        }

        // Enqueue admin styles
        wp_enqueue_style(
            'crawlaco-admin',
            CRAWLACO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CRAWLACO_VERSION
        );

        // Enqueue admin scripts
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
                'validate' => __('Validate Key', 'crawlaco'),
                'error' => __('Error occurred', 'crawlaco'),
                'success' => __('Success!', 'crawlaco'),
            )
        ));
    }

    public function render_main_page() {
        include CRAWLACO_PLUGIN_DIR . 'includes/admin/views/main-page.php';
    }

    public function render_setup_wizard() {
        include CRAWLACO_PLUGIN_DIR . 'includes/admin/views/setup-wizard.php';
    }

    public function render_settings_page() {
        include CRAWLACO_PLUGIN_DIR . 'includes/admin/views/settings-page.php';
    }
} 