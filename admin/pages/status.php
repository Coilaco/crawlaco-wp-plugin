<?php
/**
 * Crawlaco Status Page Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="crawlaco-header"><?php _e('Crawlaco Status', 'crawlaco'); ?></h1>
    
    <?php do_action('crawlaco_admin_notices'); ?>

    <div class="crawlaco-admin">
        <?php if (isset($error_message)): ?>
            <div class="notice notice-error">
                <p><?php echo esc_html($error_message); ?></p>
            </div>
        <?php else: ?>
            <div class="crawlaco-dashboard-overview">
                <div class="crawlaco-card">
                    <h2><?php _e('Website Information', 'crawlaco'); ?></h2>
                    <table class="widefat crawlaco-widefat">
                        <tr>
                            <th scope="row"><?php _e('Website ID', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['id']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Website Address', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['address']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Website Name', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['name']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Wallet Balance', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['wallet']['balance']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Status', 'crawlaco'); ?></th>
                            <td>
                                <?php if ($website_info['isActive']): ?>
                                    <span class="crawlaco-status active">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php _e('Active', 'crawlaco'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="crawlaco-status inactive">
                                        <span class="dashicons dashicons-no-alt"></span>
                                        <?php _e('Inactive', 'crawlaco'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if (get_option('crawlaco_setup_complete', false)): ?>
                    <div class="crawlaco-card">
                        <h2><?php _e('Quick Actions', 'crawlaco'); ?></h2>
                        <div class="crawlaco-quick-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-settings')); ?>"
                                class="button button-secondary">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php _e('Settings', 'crawlaco'); ?>
                            </a>
                            <a href="<?php echo esc_url(CRAWLACO_DASHBOARD_URL); ?>" class="button button-primary" target="_blank">
                                <span class="dashicons dashicons-external"></span>
                                <?php _e('Go to Crawlaco Dashboard', 'crawlaco'); ?>
                            </a>
                        </div>
                    </div>

                    <?php if (class_exists('WooCommerce')): ?>
                        <div class="crawlaco-card">
                            <h2><?php _e('WooCommerce Integration', 'crawlaco'); ?></h2>
                            <div class="crawlaco-wc-info">
                                <?php
                                // Get mapped attributes
                                $mapped_attributes = get_option('crawlaco_mapped_attributes', array());
                                $size_attr = '';
                                $color_attr = '';
                                $brand_attr = '';

                                foreach ($mapped_attributes as $attr) {
                                    if ($attr['key'] === 'SIZE_ATTR_ID') {
                                        $size_attr = wc_attribute_label($attr['value']);
                                    } elseif ($attr['key'] === 'COLOR_ATTR_ID') {
                                        $color_attr = wc_attribute_label($attr['value']);
                                    } elseif ($attr['key'] === 'BRAND_ATTR_ID') {
                                        $brand_attr = wc_attribute_label($attr['value']);
                                    }
                                }
                                ?>
                                <table class="widefat crawlaco-widefat">
                                    <tr>
                                        <th scope="row"><?php _e('Size Attribute', 'crawlaco'); ?></th>
                                        <td><?php echo $size_attr ? esc_html($size_attr) : __('Not mapped', 'crawlaco'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Color Attribute', 'crawlaco'); ?></th>
                                        <td><?php echo $color_attr ? esc_html($color_attr) : __('Not mapped', 'crawlaco'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Brand Attribute', 'crawlaco'); ?></th>
                                        <td><?php echo $brand_attr ? esc_html($brand_attr) : __('Not mapped', 'crawlaco'); ?></td>
                                    </tr>
                                </table>
                                <p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-settings')); ?>"
                                        class="button button-secondary">
                                        <?php _e('Update Attribute Mapping', 'crawlaco'); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div>
                        <div class="notice notice-warning">
                            <p>
                                <?php _e('Please complete the initial setup process before accessing these features.', 'crawlaco'); ?>
                                </p>
                                <p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-setup-wizard')); ?>"
                                        class="button button-primary">
                                        <?php _e('Complete Setup', 'crawlaco'); ?>
                                    </a>
                                </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .crawlaco-dashboard-overview {
        margin-top: 20px;
    }

    .crawlaco-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        margin-bottom: 20px;
        padding: 20px;
    }

    .crawlaco-card h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .crawlaco-status {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 3px;
    }

    .crawlaco-status.active {
        background: #ecf7ed;
        color: #46b450;
    }

    .crawlaco-status.inactive {
        background: #fbeaea;
        color: #dc3232;
    }

    .crawlaco-status .dashicons {
        margin-right: 4px;
    }

    .crawlaco-quick-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .crawlaco-quick-actions .button {
        display: inline-flex;
        align-items: center;
    }

    .crawlaco-quick-actions .dashicons {
        margin-right: 5px;
    }

    .crawlaco-wc-info {
        margin-top: 15px;
    }

    .crawlaco-wc-info p {
        margin-top: 15px;
    }
</style>