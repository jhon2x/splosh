define(['jquery'], function($) {
    var cartNAdded = function(params) {
        $.ajax({
            url: '/b2bcustomization/checkout/itemquantities',
            type: 'GET',
            data : {
                products: params.products
            },
            dataType: 'json'
        }).done(function(data) {
            var selector,
                products = data.products;

            for (p in products) {
                selector = '.product.n-added[data-product-id="' + p + '"]';
                $(selector).html('<span class="content">'+products[p]+' added</span>');
            }
        });
    };

    return cartNAdded;
});
