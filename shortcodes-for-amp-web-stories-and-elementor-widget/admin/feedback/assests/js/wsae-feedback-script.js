jQuery(document).ready(function ($) {
    $(document).on('click', '.wsae_dismiss_notice', function (e) {
        e.preventDefault();

        var $this = $(this);
        var wrapper = $this.closest('.cool-feedback-notice-wrapper');

        $.post(wsaeFeedback.ajaxUrl, {
            action: wsaeFeedback.action,
            nonce: wsaeFeedback.nonce
        }, function (data) {
            if (data && data.success) {
                wrapper.slideUp('fast');
            }
        }, 'json');
    });
});