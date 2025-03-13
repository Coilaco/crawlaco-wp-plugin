<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap crawlaco-admin">
    <h1><?php echo esc_html__('Crawlaco Dashboard', 'crawlaco'); ?></h1>

    <?php if (isset($error_message)) : ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php else : ?>
        <div class="crawlaco-dashboard-grid">
            <div class="crawlaco-card">
                <h2><?php echo esc_html__('Website Information', 'crawlaco'); ?></h2>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <th><?php echo esc_html__('Website ID', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['id']); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html__('Website Address', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['address']); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html__('Website Name', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['name']); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html__('Wallet Balance', 'crawlaco'); ?></th>
                            <td><?php echo esc_html($website_info['wallet']['balance']); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html__('Status', 'crawlaco'); ?></th>
                            <td>
                                <span class="crawlaco-status <?php echo $website_info['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $website_info['is_active'] ? esc_html__('Active', 'crawlaco') : esc_html__('Inactive', 'crawlaco'); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="crawlaco-card">
                <h2><?php echo esc_html__('Quick Actions', 'crawlaco'); ?></h2>
                <div class="crawlaco-quick-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=crawlaco-settings')); ?>" class="button button-primary">
                        <?php echo esc_html__('Settings', 'crawlaco'); ?>
                    </a>
                    <a href="<?php echo esc_url(CRAWLACO_DASHBOARD_URL); ?>" class="button button-secondary" target="_blank">
                        <?php echo esc_html__('Open Crawlaco Dashboard', 'crawlaco'); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div> 