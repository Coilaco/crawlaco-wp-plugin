<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap crawlaco-admin">
    <h1><?php echo esc_html__('Crawlaco Settings', 'crawlaco'); ?></h1>

    <form id="crawlaco-settings-form" class="crawlaco-settings-form">
        <?php wp_nonce_field('crawlaco_update_settings', 'crawlaco_settings_nonce'); ?>

        <div class="crawlaco-card">
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
                        'SIZE_ATTR_ID' => __('Size', 'crawlaco'),
                        'COLOR_ATTR_ID' => __('Color', 'crawlaco'),
                        'BRAND_ATTR_ID' => __('Brand', 'crawlaco')
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

        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php echo esc_html__('Save Settings', 'crawlaco'); ?>
            </button>
        </p>
    </form>
</div> 