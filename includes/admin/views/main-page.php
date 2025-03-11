<div class="wrap crawlaco-dashboard" dir="rtl">
    <h1><?php _e('Crawlaco Dashboard', 'crawlaco'); ?></h1>

    <div class="crawlaco-dashboard-overview">
        <div class="crawlaco-card">
            <h2><?php _e('Plugin Status', 'crawlaco'); ?></h2>
            <?php
            $setup_complete = get_option('crawlaco_setup_complete');
            if ($setup_complete) {
                echo '<p class="status success">' . __('Setup Complete', 'crawlaco') . '</p>';
            } else {
                echo '<p class="status warning">' . __('Setup Required', 'crawlaco') . '</p>';
                echo '<a href="' . admin_url('admin.php?page=crawlaco-setup') . '" class="button button-primary">' . 
                     __('Complete Setup', 'crawlaco') . '</a>';
            }
            ?>
        </div>

        <div class="crawlaco-card">
            <h2><?php _e('Quick Links', 'crawlaco'); ?></h2>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=crawlaco-setup'); ?>"><?php _e('Setup Wizard', 'crawlaco'); ?></a></li>
                <li><a href="<?php echo admin_url('admin.php?page=crawlaco-settings'); ?>"><?php _e('Settings', 'crawlaco'); ?></a></li>
                <li><a href="https://crawlaco.com" target="_blank"><?php _e('Crawlaco Website', 'crawlaco'); ?></a></li>
            </ul>
        </div>
    </div>
</div> 