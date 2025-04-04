<?php
/**
 * Crawlaco Status Page Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue admin styles
function crawlaco_enqueue_admin_styles() {
    wp_enqueue_style(
        'crawlaco-admin-styles',
        CRAWLACO_PLUGIN_URL . 'admin/css/admin.css',
        array(),
        CRAWLACO_VERSION
    );
}
add_action('admin_enqueue_scripts', 'crawlaco_enqueue_admin_styles');
?>

<div class="wrap">
    <h1 class="crawlaco-header"><?php esc_html_e('Crawlaco Status', 'crawlaco'); ?></h1>
    
    <?php do_action('crawlaco_admin_notices'); ?>

    <div class="crawlaco-admin">
        <?php if (isset($error_message)): ?>
            <div class="notice notice-error">
                <p><?php echo esc_html($error_message); ?></p>
            </div>
                <div class="crawlaco-dashboard-overview">
                    <div class="crawlaco-completion-section">
                        <h4>
                            <span class="dashicons dashicons-dismiss" style="color: red;"></span>
                            <?php esc_html_e('The setup wizard has not started.', 'crawlaco'); ?>
                        </h4>
                        <p>
                            <?php esc_html_e('To see the information on this page, you must first complete the setup wizard steps.', 'crawlaco'); ?>
                        </p>
                    </div>

                    <div class="crawlaco-completion-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-setup-wizard')); ?>" class="button button-primary">
                            <?php esc_html_e('Go to Setup Wizard', 'crawlaco'); ?>
                        </a>
                    </div>
            </div>
        <?php else: ?>
            <div class="crawlaco-dashboard-overview">
                <div class="crawlaco-card">
                    <h2><?php esc_html_e('Website Information', 'crawlaco'); ?></h2>
                    <table class="widefat crawlaco-widefat">
                        <tr>
                            <th scope="row"><?php esc_html_e('Website ID', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['id']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Website Address', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['address']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Website Name', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['name']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Wallet Balance', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['wallet']['balance']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Status', 'crawlaco'); ?></th>
                            <td>
                                <?php if ($website_info['isActive']): ?>
                                    <span class="crawlaco-status active">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('Active', 'crawlaco'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="crawlaco-status inactive">
                                        <span class="dashicons dashicons-no-alt"></span>
                                        <?php esc_html_e('Inactive', 'crawlaco'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if (get_option('crawlaco_setup_complete', false)): ?>
                    <div class="crawlaco-card">
                        <h2><?php esc_html_e('Quick Actions', 'crawlaco'); ?></h2>
                        <div class="crawlaco-quick-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-settings')); ?>"
                                class="button button-secondary">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php esc_html_e('Settings', 'crawlaco'); ?>
                            </a>
                            <a href="<?php echo esc_url(CRAWLACO_DASHBOARD_URL); ?>" class="button button-primary" target="_blank">
                                <span class="dashicons dashicons-external"></span>
                                <?php esc_html_e('Go to Crawlaco Dashboard', 'crawlaco'); ?>
                            </a>
                        </div>
                    </div>

                    <?php if (class_exists('WooCommerce')): ?>
                        <div class="crawlaco-card">
                            <h2><?php esc_html_e('WooCommerce Integration', 'crawlaco'); ?></h2>
                            <div class="crawlaco-wc-info">
                                <?php
                                // Get mapped attributes
                                $size_attr_id = get_option('crawlaco_size_attr_id', '');
                                $color_attr_id = get_option('crawlaco_color_attr_id', '');
                                $brand_attr_id = get_option('crawlaco_brand_attr_id', '');

                                $size_attr = $size_attr_id ? wc_attribute_label($size_attr_id) : '';
                                $color_attr = $color_attr_id ? wc_attribute_label($color_attr_id) : '';
                                $brand_attr = $brand_attr_id ? wc_attribute_label($brand_attr_id) : '';
                                ?>
                                <table class="widefat crawlaco-widefat">
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Size Attribute', 'crawlaco'); ?></th>
                                        <td><?php echo $size_attr ? esc_html($size_attr) : esc_html__('Not mapped', 'crawlaco'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Color Attribute', 'crawlaco'); ?></th>
                                        <td><?php echo $color_attr ? esc_html($color_attr) : esc_html__('Not mapped', 'crawlaco'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Brand Attribute', 'crawlaco'); ?></th>
                                        <td><?php echo $brand_attr ? esc_html($brand_attr) : esc_html__('Not mapped', 'crawlaco'); ?></td>
                                    </tr>
                                </table>
                                <p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-settings')); ?>"
                                        class="button button-secondary">
                                        <?php esc_html_e('Update Attribute Mapping', 'crawlaco'); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div>
                        <div class="notice notice-warning">
                            <p>
                                <?php esc_html_e('Please complete the initial setup process before accessing these features.', 'crawlaco'); ?>
                                </p>
                                <p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-setup-wizard')); ?>"
                                        class="button button-primary">
                                        <?php esc_html_e('Complete Setup', 'crawlaco'); ?>
                                    </a>
                                </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>