jQuery(document).ready(function ($) {
    // Get the deactivation link
    const deactivateLink = $('tr[data-plugin="crawlaco/crawlaco.php"] span.deactivate a');
    const deactivateUrl = deactivateLink.attr('href');

    // Create modal HTML
    const modalHtml = `
        <div class="crawlaco-modal-overlay">
            <div class="crawlaco-modal">
                <div class="crawlaco-modal-header">
                    <span class="dashicons dashicons-warning"></span>
                    <h3>${crawlacoDeactivation.strings.modalTitle}</h3>
                </div>
                <div class="crawlaco-modal-content">
                    <p>${crawlacoDeactivation.strings.modalMessage}</p>
                </div>
                <div class="crawlaco-modal-footer">
                    <a href="#" class="crawlaco-modal-button crawlaco-modal-cancel">
                        ${crawlacoDeactivation.strings.cancelButton}
                    </a>
                    <a href="#" class="crawlaco-modal-button crawlaco-modal-deactivate">
                        ${crawlacoDeactivation.strings.deactivateButton}
                    </a>
                </div>
            </div>
        </div>
    `;

    // Append modal to body
    $('body').append(modalHtml);

    // Get modal elements
    const modal = $('.crawlaco-modal-overlay');
    const cancelButton = $('.crawlaco-modal-cancel');
    const deactivateButton = $('.crawlaco-modal-deactivate');

    // Show modal on deactivate link click
    deactivateLink.on('click', function (e) {
        e.preventDefault();
        modal.show();
    });

    // Handle deactivation
    deactivateButton.on('click', function (e) {
        e.preventDefault();

        // Disable the button and show loading state
        const $button = $(this);
        $button.prop('disabled', true).text(crawlacoDeactivation.strings.deactivating);

        // Make AJAX call to update website status
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'crawlaco_deactivate',
                nonce: crawlacoDeactivation.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Proceed with deactivation
                    window.location.href = deactivateUrl;
                } else {
                    // Show error and re-enable button
                    alert(response.data.message || crawlacoDeactivation.strings.errorMessage);
                    $button.prop('disabled', false).text(crawlacoDeactivation.strings.deactivateButton);
                }
            },
            error: function () {
                // Show error and re-enable button
                alert(crawlacoDeactivation.strings.errorMessage);
                $button.prop('disabled', false).text(crawlacoDeactivation.strings.deactivateButton);
            }
        });
    });

    // Hide modal on cancel
    cancelButton.on('click', function (e) {
        e.preventDefault();
        modal.hide();
    });

    // Hide modal when clicking outside
    modal.on('click', function (e) {
        if ($(e.target).is(modal)) {
            modal.hide();
        }
    });

    // Close modal on ESC key
    $(document).on('keyup', function (e) {
        if (e.key === 'Escape' && modal.is(':visible')) {
            modal.hide();
        }
    });
}); 