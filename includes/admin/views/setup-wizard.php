<div class="wrap crawlaco-setup-wizard" dir="rtl">
    <h1><?php _e('Crawlaco Setup Wizard', 'crawlaco'); ?></h1>
    
    <div class="crawlaco-steps">
        <div class="step active" data-step="1">
            <?php _e('Website Key Validation', 'crawlaco'); ?>
        </div>
        <div class="step" data-step="2">
            <?php _e('API Keys', 'crawlaco'); ?>
        </div>
        <div class="step" data-step="3">
            <?php _e('Data Fetch', 'crawlaco'); ?>
        </div>
        <div class="step" data-step="4">
            <?php _e('Attribute Mapping', 'crawlaco'); ?>
        </div>
    </div>

    <div class="crawlaco-step-content">
        <div class="step-1 active">
            <h2><?php _e('Enter Your Website Key', 'crawlaco'); ?></h2>
            <p><?php _e('Please enter the Website Key generated from your Crawlaco dashboard.', 'crawlaco'); ?></p>
            
            <form id="website-key-form">
                <input type="text" id="website-key" name="website-key" required>
                <button type="submit" class="button button-primary">
                    <?php _e('Validate Key', 'crawlaco'); ?>
                </button>
            </form>
        </div>

        <div class="step-2">
            <h2><?php _e('API Keys', 'crawlaco'); ?></h2>
            <p><?php _e('Generating API keys for your website...', 'crawlaco'); ?></p>
            <div class="loading"><?php _e('Please wait...', 'crawlaco'); ?></div>
        </div>

        <div class="step-3">
            <h2><?php _e('Data Fetch', 'crawlaco'); ?></h2>
            <p><?php _e('Fetching your website data...', 'crawlaco'); ?></p>
            <div class="loading"><?php _e('Please wait...', 'crawlaco'); ?></div>
        </div>

        <div class="step-4">
            <h2><?php _e('Attribute Mapping', 'crawlaco'); ?></h2>
            <p><?php _e('Configure your product attributes...', 'crawlaco'); ?></p>
            <div class="loading"><?php _e('Please wait...', 'crawlaco'); ?></div>
        </div>
    </div>
</div> 