<div class="wrap crawlaco-settings" dir="rtl">
    <h1><?php _e('Crawlaco Settings', 'crawlaco'); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('crawlaco_settings');
        do_settings_sections('crawlaco_settings');
        ?>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Website Key', 'crawlaco'); ?></th>
                <td>
                    <input type="text" name="crawlaco_website_key" 
                           value="<?php echo esc_attr(get_option('crawlaco_website_key')); ?>" 
                           class="regular-text" readonly>
                    <p class="description"><?php _e('Your website key (read-only)', 'crawlaco'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div> 