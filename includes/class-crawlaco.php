<?php
// Prevent direct access
defined('ABSPATH') || exit;

class Crawlaco {
    private static $instance = null;
    private $api = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->init_api();
    }

    private function init_hooks() {
        // Initialize plugin functionality
        add_action('init', array($this, 'init'));
        
        // Register activation and deactivation hooks
        register_activation_hook(CRAWLACO_PLUGIN_DIR . 'crawlaco.php', array($this, 'activate'));
        register_deactivation_hook(CRAWLACO_PLUGIN_DIR . 'crawlaco.php', array($this, 'deactivate'));
    }

    private function init_api() {
        $this->api = new Crawlaco_API();
    }

    public function init() {
        // Load translations
        load_plugin_textdomain('crawlaco', false, dirname(plugin_basename(CRAWLACO_PLUGIN_DIR)) . '/languages/');
    }

    public function activate() {
        // Activation tasks
        if (!get_option('crawlaco_setup_complete')) {
            add_option('crawlaco_setup_complete', false);
        }
    }

    public function deactivate() {
        // Deactivation tasks
    }

    public function get_api() {
        return $this->api;
    }
} 