jQuery(document).ready(function ($) {
    // Handle website key validation
    $('#website-key-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const websiteKey = $('#website-key').val();

        console.log(crawlacoAdmin.ajaxUrl, 'URLLLL');


        // Disable form during submission
        $submitButton.prop('disabled', true)
            .text(crawlacoAdmin.strings.validating);

        // Make API request
        $.ajax({
            url: crawlacoAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'crawlaco_validate_website_key',
                nonce: crawlacoAdmin.nonce,
                website_key: websiteKey
            },
            success: function (response) {
                console.log(response.data);

                if (response.success) {
                    // Show success message and proceed to next step
                    alert(crawlacoAdmin.strings.success);
                    // Move to next step logic here
                } else {
                    alert(response.data.message || crawlacoAdmin.strings.error);
                }
            },
            error: function () {
                alert(crawlacoAdmin.strings.error);
            },
            complete: function () {
                $submitButton.prop('disabled', false)
                    .text(crawlacoAdmin.strings.validate);
            }
        });
    });
}); 