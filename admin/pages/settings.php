<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
$is_woocommerce_active = in_array(
    'woocommerce/woocommerce.php',
    apply_filters('active_plugins', get_option('active_plugins'))
);
?>

<div class="wrap">
    <h1 class="crawlaco-header"><?php esc_html_e('Crawlaco Settings', 'crawlaco'); ?></h1>
    
    <?php do_action('crawlaco_admin_notices'); ?>

    <div class="crawlaco-admin">
        <form id="crawlaco-settings-form" class="crawlaco-settings-form">
            <?php wp_nonce_field('crawlaco_update_settings', 'crawlaco_settings_nonce'); ?>

            <?php if ($is_woocommerce_active) : ?>
                <div class="crawlaco-card crawlaco-attribute-mapper">
                    <h2><?php echo esc_html__('Attribute Mapping', 'crawlaco'); ?></h2>
                    <p class="description">
                        <?php echo esc_html__('Map your WooCommerce product attributes to help Crawlaco understand your data structure.', 'crawlaco'); ?>
                    </p>

                    <table class="widefat">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Crawlaco Attribute', 'crawlaco'); ?></th>
                                <th><?php echo esc_html__('WooCommerce Attribute', 'crawlaco'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $required_attributes = array(
                                'SIZE_ATTR_ID' => esc_html__('Size', 'crawlaco'),
                                'COLOR_ATTR_ID' => esc_html__('Color', 'crawlaco'),
                                'BRAND_ATTR_ID' => esc_html__('Brand', 'crawlaco')
                            );

                            foreach ($required_attributes as $attr_key => $attr_label) :
                                $current_value = isset($formatted_attributes[$attr_key]) ? $formatted_attributes[$attr_key] : '';
                                $selected_attr = null;
                                
                                // Find the selected attribute object
                                if (!empty($current_value)) {
                                    foreach ($wc_attributes as $wc_attr) {
                                        if ($wc_attr->attribute_id == $current_value) {
                                            $selected_attr = $wc_attr;
                                            break;
                                        }
                                    }
                                }
                            ?>
                                <tr>
                                    <th><?php echo esc_html($attr_label); ?></th>
                                    <td>
                                        <select name="mapped_attributes[<?php echo esc_attr($attr_key); ?>]" class="regular-text">
                                            <option value=""><?php echo esc_html__('-- Select Attribute --', 'crawlaco'); ?></option>
                                            <?php foreach ($wc_attributes as $wc_attr) : ?>
                                                <option value="<?php echo esc_attr($wc_attr->attribute_id); ?>" 
                                                        <?php selected($current_value, $wc_attr->attribute_id); ?>>
                                                    <?php echo esc_html($wc_attr->attribute_label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($selected_attr) : ?>
                                            <p class="description">
                                                <?php echo esc_html__('Currently mapped to:', 'crawlaco'); ?> 
                                                <strong><?php echo esc_html($selected_attr->attribute_label); ?></strong>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="crawlaco-card crawlaco-attribute-mapper">
                    <h2><?php echo esc_html__('Attribute Mapping', 'crawlaco'); ?></h2>
                    <p>
                        <?php echo esc_html__('WooCommerce is required for attribute mapping. Please install and activate WooCommerce to access this feature. If your site is not using WooCommerce, you can skip this step.', 'crawlaco'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="crawlaco-card">
                <h2><?php echo esc_html__('Data Synchronization', 'crawlaco'); ?></h2>
                <p class="description">
                    <?php echo esc_html__('Fetch and sync your site data with Crawlaco. This will update your product information, categories, and other relevant data.', 'crawlaco'); ?>
                </p>

                <div class="crawlaco-data-sync-status">
                    <div class="crawlaco-message"></div>
                    <div class="crawlaco-progress" style="display: none;">
                        <div class="spinner is-active"></div>
                        <span class="progress-text"><?php echo esc_html__('Fetching data...', 'crawlaco'); ?></span>
                    </div>
                    <button type="button" 
                            class="button button-primary" 
                            id="start-data-sync"
                    >
                        <?php echo esc_html__('Start Data Sync', 'crawlaco'); ?>
                    </button>
                    <button type="button" 
                            class="button button-secondary" 
                            id="retry-data-sync"
                            style="display: none;"
                    >
                        <?php echo esc_html__('Retry', 'crawlaco'); ?>
                    </button>
                </div>
            </div>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php echo esc_html__('Save Settings', 'crawlaco'); ?>
                </button>
            </p>
        </form>
    </div>
</div> 