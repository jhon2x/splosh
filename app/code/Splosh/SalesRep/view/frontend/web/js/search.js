define([
    'jquery'
], function ($) {
    "use strict";
    return function (config, element) {
        $(document).ready(function () {
            $('form.form.salesrep-location-form').submit(function (event) {
                event.preventDefault();
                let query = $('.input-text.salesrep.query').val();
                let resultContainer = $('.salesrep-search-widget .content .results');

                $.ajax({
                    url: config.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {query: query},
                    complete: function (response) {
                        resultContainer.empty();

                        if (response.responseJSON.is_empty) {
                            let errorDiv = document.createElement('div');
                            errorDiv.className = config.errorMsgClass;
                            errorDiv.innerText = config.errorMsg;
                            resultContainer.append(errorDiv);
                        } else {

                            let result = response.responseJSON;

                            $.each(result, function (i, v) {
                                let divItem = document.createElement('div');
                                divItem.className = 'sales-rep item ' + config.getItemClass;
                                divItem.innerHTML =
                                    '<div class="image"><img class="' + config.getItemImageClass + '" src="' + v.photo + '" /></div>' +
                                    '<dl class="details">' +
                                    '<dt>Name</dt><dd>' + v.nickname + '</dd>' +
                                    '<dt>Job Title</dt><dd>' + v.jobtitle + '</dd>' +
                                    '<dt>Email</dt><dd>' + v.email + '</dd>' +
                                    '<dt>Phone Number</dt><dd>' + v.phone_no + '</dd>' +
                                    '</dl>';

                                resultContainer.append(divItem);
                            });
                        }
                    }
                });
            });
        });
    }
});