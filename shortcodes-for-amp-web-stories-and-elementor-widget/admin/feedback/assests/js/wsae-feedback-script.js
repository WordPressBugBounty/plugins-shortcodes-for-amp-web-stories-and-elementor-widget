jQuery(document).ready(function ($) {
    $(document).on('click', '.wsae_dismiss_notice', function () {
        var $this = $(this);
        var wrapper = $this.closest('.cool-feedback-notice-wrapper');

        $.post(wsaeFeedback.ajaxUrl, {
            action: wsaeFeedback.action
        }, function (data) {
            wrapper.slideUp('fast');
        }, 'json');
    });
});