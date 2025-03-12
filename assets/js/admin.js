jQuery(document).ready(function ($) {
    // Handle website key validation
    $('#crawlaco-website-key-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $('#validate-website-key');
        const $message = $('.crawlaco-message');

        // Get the website key
        const websiteKey = $('#crawlaco_website_key').val().trim();

        if (!websiteKey) {
            $message
                .removeClass('success')
                .addClass('error')
                .html(crawlacoAdmin.strings.error + ' ' + 'Please enter a website key.');
            return;
        }

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
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html(crawlacoAdmin.strings.error + ' ' + response.data.message);

                    $submitButton
                        .prop('disabled', false)
                        .html(crawlacoAdmin.strings.validate);
                }
            },
            error: function () {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html(crawlacoAdmin.strings.error + ' ' + 'Failed to connect to the server. Please try again.');

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
}); 