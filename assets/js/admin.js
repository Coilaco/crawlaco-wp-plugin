jQuery(document).ready(function ($) {
    // Handle website key validation
    $('#crawlaco-website-key-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $('#validate-website-key');
        const $message = $('.crawlaco-message');
        const websiteKey = $('#crawlaco_website_key').val();

        // Clear previous messages
        $message.removeClass('success error').empty();

        // Disable submit button and show loading state
        $submitButton.prop('disabled', true).text(crawlacoAdmin.strings.validating);

        // Send AJAX request
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'crawlaco_validate_website_key',
                website_key: websiteKey,
                nonce: crawlacoAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    $message
                        .addClass('success')
                        .text(crawlacoAdmin.strings.success + ' ' + response.data.message);

                    // Disable the form after successful validation
                    $('#crawlaco_website_key').prop('disabled', true);
                    $submitButton.hide();

                    // Reload page after 2 seconds to show next step
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                } else {
                    $message
                        .addClass('error')
                        .text(crawlacoAdmin.strings.error + ' ' + response.data.message);
                    $submitButton
                        .prop('disabled', false)
                        .text(crawlacoAdmin.strings.validate);
                }
            },
            error: function (xhr, status, error) {
                $message
                    .addClass('error')
                    .text(crawlacoAdmin.strings.error + ' ' + error);
                $submitButton
                    .prop('disabled', false)
                    .text(crawlacoAdmin.strings.validate);
            }
        });
    });
}); 