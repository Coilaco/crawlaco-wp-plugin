jQuery(document).ready(function ($) {
    // Handle website key validation
    $('#crawlaco-website-key-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $('#validate-website-key');
        const $message = $('.crawlaco-message');
        const $errorMessage = $('.crawlaco-error-message');

        // Get the website key
        const websiteKey = $('#crawlaco_website_key').val().trim();

        if (!websiteKey) {
            $errorMessage
                .html(crawlacoAdmin.strings.error + ' ' + 'Please enter a website key.')
                .show();
            return;
        }

        // Hide any existing messages
        $message.empty().removeClass('success error');
        $errorMessage.empty().hide();

        // Disable form and show loading state
        $submitButton
            .prop('disabled', true)
            .html(crawlacoAdmin.strings.validating);

        // Send AJAX request
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'validate_website_key',
                website_key: websiteKey,
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message);

                    // Redirect to next step after a short delay
                    setTimeout(function () {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    $errorMessage
                        .html(response.data.message)
                        .show();

                    $submitButton
                        .prop('disabled', false)
                        .html(crawlacoAdmin.strings.validate);
                }
            },
            error: function () {
                $errorMessage
                    .html(crawlacoAdmin.strings.error + ' ' + 'Failed to connect to the server. Please try again.')
                    .show();

                $submitButton
                    .prop('disabled', false)
                    .html(crawlacoAdmin.strings.validate);
            }
        });
    });

    // Handle API key generation
    $('#generate-api-keys').on('click', function (e) {
        e.preventDefault();

        const $button = $(this);
        const $message = $('.crawlaco-message');

        // Disable button and show loading state
        $button
            .prop('disabled', true)
            .html(crawlacoAdmin.strings.generating);

        // Send AJAX request
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_api_keys',
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message);

                    // Redirect to next step after a short delay
                    setTimeout(function () {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html(crawlacoAdmin.strings.error + ' ' + response.data.message);

                    $button
                        .prop('disabled', false)
                        .html(crawlacoAdmin.strings.generate);
                }
            },
            error: function () {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html(crawlacoAdmin.strings.error + ' ' + 'Failed to connect to the server. Please try again.');

                $button
                    .prop('disabled', false)
                    .html(crawlacoAdmin.strings.generate);
            }
        });
    });

    // Handle proceeding to next step
    $('#proceed-to-step-three').on('click', function (e) {
        e.preventDefault();

        // Update setup step in WordPress
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'crawlaco_update_setup_step',
                step: 3,
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Reload page to show next step
                    window.location.reload();
                }
            }
        });
    });

    // Handle data synchronization
    let taskId = null;
    let pollInterval = null;
    let pollAttempts = 0;
    const MAX_POLL_ATTEMPTS = 10;
    const POLL_INTERVAL = 3000; // 3 seconds

    function startDataSync() {
        const $message = $('.crawlaco-message');
        const $progress = $('.crawlaco-progress');
        const $startButton = $('#start-data-sync');
        const $retryButton = $('#retry-data-sync');

        // Reset state
        pollAttempts = 0;
        if (pollInterval) {
            clearInterval(pollInterval);
        }

        // Disable start button and show progress
        $startButton.prop('disabled', true);
        $progress.show();
        $retryButton.hide();

        // Send AJAX request to initiate data fetch
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'initiate_data_fetch',
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    taskId = response.data.taskId;
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message);

                    // Start polling for task status
                    pollTaskStatus();
                } else {
                    handleDataSyncError(response.data.message);
                }
            },
            error: function () {
                handleDataSyncError('Failed to connect to the server. Please try again.');
            }
        });
    }

    function pollTaskStatus() {
        if (!taskId) return;

        pollInterval = setInterval(function () {
            pollAttempts++;

            $.ajax({
                url: crawlacoAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'check_task_status',
                    task_id: taskId,
                    nonce: crawlacoAdmin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        const status = response.data.status;
                        const message = response.data.message;

                        switch (status) {
                            case 'SUCCESS':
                                clearInterval(pollInterval);
                                handleDataSyncSuccess(message);
                                break;
                            case 'FAILURE':
                                clearInterval(pollInterval);
                                handleDataSyncError(message);
                                break;
                            case 'PENDING':
                                if (pollAttempts >= MAX_POLL_ATTEMPTS) {
                                    clearInterval(pollInterval);
                                    handleDataSyncError('Data fetching timed out. Please try again.');
                                }
                                break;
                            default:
                                pollAttempts++;
                                break;
                        }
                    } else {
                        handleDataSyncError(response.data.message);
                    }
                },
                error: function () {
                    handleDataSyncError('Failed to check task status. Please try again.');
                }
            });
        }, POLL_INTERVAL);
    }

    function handleDataSyncSuccess(message) {
        const $message = $('.crawlaco-message');
        const $progress = $('.crawlaco-progress');
        const $startButton = $('#start-data-sync');
        const $retryButton = $('#retry-data-sync');

        $message
            .removeClass('error')
            .addClass('success')
            .html(message);

        $progress.hide();
        $startButton.hide();
        $retryButton.hide();

        // Only redirect if we're in the setup wizard
        if ($('.crawlaco-setup-wizard').length > 0) {
            setTimeout(function () {
                window.location.reload();
            }, 1200);
        } else {
            // In settings page, show success message for 3 seconds
            setTimeout(function () {
                $message.fadeOut(function () {
                    $(this).removeClass('success').html('').show();
                });
                $startButton.show();
            }, 3000);
        }
    }

    function handleDataSyncError(message) {
        const $message = $('.crawlaco-message');
        const $progress = $('.crawlaco-progress');
        const $startButton = $('#start-data-sync');
        const $retryButton = $('#retry-data-sync');

        $message
            .removeClass('success')
            .addClass('error')
            .html(crawlacoAdmin.strings.error + ' ' + message);

        $progress.hide();
        $startButton.prop('disabled', false);
        $retryButton.show();
    }

    // Bind event handlers
    $('#start-data-sync').on('click', startDataSync);
    $('#retry-data-sync').on('click', startDataSync);

    // Handle attribute mapping form submission
    $('#crawlaco-attribute-mapping-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $('#save-attribute-mapping');
        const $message = $('.crawlaco-message');

        // Get attribute mappings
        const sizeAttrId = $('#crawlaco_size_attr_id').val();
        const colorAttrId = $('#crawlaco_color_attr_id').val();
        const brandAttrId = $('#crawlaco_brand_attr_id').val();

        // Disable form and show loading state
        $submitButton.prop('disabled', true);

        // Send AJAX request
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_attribute_mapping',
                size_attr_id: sizeAttrId,
                color_attr_id: colorAttrId,
                brand_attr_id: brandAttrId,
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message);

                    // Send request to finalize setup
                    finalizeSetup();
                } else {
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html(crawlacoAdmin.strings.error + ' ' + response.data.message);

                    $submitButton.prop('disabled', false);
                }
            },
            error: function () {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html(crawlacoAdmin.strings.error + ' ' + 'Failed to connect to the server. Please try again.');

                $submitButton.prop('disabled', false);
            }
        });
    });

    // Handle setup finalization when WooCommerce is not installed
    $('#crawlaco-finish-setup').on('click', function (e) {
        e.preventDefault();
        finalizeSetup();
    });

    function finalizeSetup() {
        const $message = $('.crawlaco-message');

        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'finalize_setup',
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message);

                    // Redirect to Crawlaco dashboard after a short delay
                    setTimeout(function () {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html(crawlacoAdmin.strings.error + ' ' + response.data.message);
                }
            },
            error: function () {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html(crawlacoAdmin.strings.error + ' ' + 'Failed to connect to the server. Please try again.');
            }
        });
    }

    // Handle settings form submission
    $('#crawlaco-settings-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const originalButtonText = $submitButton.text();

        // Disable submit button and show loading state
        $submitButton.prop('disabled', true).text('Saving...');

        // Get form data
        const formData = new FormData($form[0]);
        formData.append('action', 'crawlaco_update_settings');

        // Send AJAX request
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    // Show success message
                    const $notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                    $form.before($notice);

                    // Remove notice after 3 seconds
                    setTimeout(function () {
                        $notice.fadeOut(function () {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    // Show error message
                    const $notice = $('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>');
                    $form.before($notice);
                }
            },
            error: function (xhr, status, error) {
                // Show error message
                const $notice = $('<div class="notice notice-error is-dismissible"><p>An error occurred while saving settings. Please try again.</p></div>');
                $form.before($notice);
            },
            complete: function () {
                // Re-enable submit button and restore original text
                $submitButton.prop('disabled', false).text(originalButtonText);
            }
        });
    });

    // Handle dismissible notices
    $(document).on('click', '.notice-dismiss', function () {
        $(this).parent().fadeOut(function () {
            $(this).remove();
        });
    });

    // Handle WooCommerce API key generation in settings page
    $('#generate-wc-api-keys').on('click', function (e) {
        e.preventDefault();

        const $button = $(this);
        const $message = $('.crawlaco-message-wc-api-keys');

        // Disable button and show loading state
        $button
            .prop('disabled', true)
            .html(crawlacoAdmin.strings.generating);

        // Send AJAX request
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_wc_api_keys',
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message);

                    // Redirect to settings page after a short delay
                    setTimeout(function () {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html(crawlacoAdmin.strings.error + ' ' + response.data.message);

                    $button
                        .prop('disabled', false)
                        .html(crawlacoAdmin.strings.generate);
                }
            },
            error: function () {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html(crawlacoAdmin.strings.error + ' ' + 'Failed to connect to the server. Please try again.');

                $button
                    .prop('disabled', false)
                    .html(crawlacoAdmin.strings.generate);
            }
        });
    });
}); 