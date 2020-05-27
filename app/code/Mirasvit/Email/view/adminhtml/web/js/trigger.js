define([
    'jquery'
], function ($) {
    'use strict';

    function getMessage (message, isSuccess) {
        var status = isSuccess ? 'success' : 'error';

        return '<div id="messages">' +
            '<div class="messages">' +
                '<div class="message message-' + status + ' ' + status + '">' +
                    '<div data-ui-id="messages-message-' + status + '">' +
                        message +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    window.trigger = {
        sendTestEmail: function (button, send, email, url) {
            if (send) {
                $.ajax({
                    showLoader: true,
                    url: url,
                    data: {email: $('#test_email').val(), 'form_key': FORM_KEY, 'rand': new Date().getTime()},
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        var $msg = $(getMessage(response.message, response.success))
                            .insertAfter('.email_campaign_view_email_campaign_view_modals_chain_edit_form_modal .page-main-actions');

                        setTimeout(function() {
                            $msg.hide('slow', function() {
                                $msg.remove();
                            });
                        }, 3000);
                    }
                });

            } else {
                $(button).hide();
                $('<input type="text" id="test_email" value="' + email + '" class="input-text admin__control-text" style="margin:5px 0 0 5px; width:200px">'
                    + '<button type="button" class="scalable" onclick="trigger.sendTestEmail(this, true, null, \'' + url + '\')">'
                    + '<span>Send</span></button>').insertAfter($(button).next());
            }
        }
    };
});