<?php
/**
 * Setup Wizard Page Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$current_step = get_option('crawlaco_setup_step', 1);
$website_key = get_option('crawlaco_website_key', '');
$setup_complete = get_option('crawlaco_setup_complete', false);

// If setup is complete, show completion message and status page button
if ($setup_complete) {
    ?>
    <div class="wrap">
        <h1 class="crawlaco-header"><?php _e('Crawlaco Setup Complete', 'crawlaco'); ?></h1>
        
        <?php do_action('crawlaco_admin_notices'); ?>

        <div class="crawlaco-admin">
            <div class="notice notice-success">
                <p><?php _e('Great! Your Crawlaco plugin has been successfully set up.', 'crawlaco'); ?></p>
                <p><?php _e('You can now start using Crawlaco to manage your website data.', 'crawlaco'); ?></p>
            </div>

            <div class="crawlaco-completion-actions">
                <a href="<?php echo admin_url('admin.php?page=crawlaco'); ?>" class="button button-primary">
                    <?php _e('Go to Status Page', 'crawlaco'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
    return;
}

/**
 * Render step one (Website Key validation)
 */
function render_step_one() {
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
function render_step_two() {
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
function render_step_three() {
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
function render_step_four() {
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
    <div class="crawlaco-setup-step crawlaco-attribute-mapper active">
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
?>
<div class="wrap">
    <h1 class="crawlaco-header"><?php _e('Crawlaco Setup Wizard', 'crawlaco'); ?></h1>
    
    <?php do_action('crawlaco_admin_notices'); ?>

    <div class="crawlaco-admin">
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
                    render_step_one();
                    break;
                case 2:
                    render_step_two();
                    break;
                case 3:
                    render_step_three();
                    break;
                case 4:
                    render_step_four();
                    break;
            }
            ?>
        </div>
    </div>
</div> 