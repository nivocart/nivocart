/**
 * pp_standard_redirect.js
 *
 * Loaded only on the standalone payment/pp_standard page.
 * Reads order data from the #pp-paypal-data element's data attributes,
 * builds a hidden form, and submits it to PayPal on button click.
 *
 * This is separate from pp_standard.js (which runs on the checkout page)
 * because by the time we reach this page the order_id exists in session
 * and all PayPal fields including invoice and custom are known.
 */

(function () {
    'use strict';

    var btn = document.getElementById('pp-redirect-btn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.classList.add('btn-paypal--loading');

        var src = document.getElementById('pp-paypal-data');

        var action = src.dataset.action;
        var products = JSON.parse(src.dataset.products);
        var discount = parseFloat(src.dataset.discount) || 0;

        var form = document.createElement('form');
        form.method = 'post';
        form.action = action;
        form.target = '_self';

        function addField(name, value) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }

        // Fixed PayPal cart fields
        addField('cmd', '_cart');
        addField('upload', '1');

        // Merchant & order
        addField('business', src.dataset.business);
        addField('currency_code', src.dataset.currency);
        addField('invoice', src.dataset.invoice);
        addField('custom', src.dataset.custom);
        addField('paymentaction', src.dataset.paymentaction);

        // Billing address
        addField('first_name', src.dataset.firstName);
        addField('last_name', src.dataset.lastName);
        addField('address1', src.dataset.address1);
        addField('address2', src.dataset.address2);
        addField('city', src.dataset.city);
        addField('zip', src.dataset.zip);
        addField('country', src.dataset.country);
        addField('email', src.dataset.email);
        addField('address_override', '0');

        // UX / misc
        addField('lc', src.dataset.lc);
        addField('rm', '2');
        addField('no_note', '1');
        addField('no_shipping', '1');
        addField('charset', 'utf-8');
        addField('bn', 'NivoCart_Cart_WPS');

        // URLs
        addField('return', src.dataset.returnUrl);
        addField('notify_url', src.dataset.notifyUrl);
        addField('cancel_return', src.dataset.cancelUrl);

        // Cart discount
        if (discount > 0) {
            addField('discount_amount_cart', discount.toFixed(2));
        }

        // Line items
        for (var i = 0; i < products.length; i++) {
            var n = i + 1;
            var p = products[i];

            addField('item_name_' + n, p.name);
            addField('item_number_' + n, p.model);
            addField('amount_' + n, parseFloat(p.price).toFixed(2));
            addField('quantity_' + n, p.quantity);
            addField('weight_' + n, p.weight);

            if (p.option) {
                for (var j = 0; j < p.option.length; j++) {
                    addField('on' + j + '_' + n, p.option[j].name);
                    addField('os' + j + '_' + n, p.option[j].value);
                }
            }
        }

        document.body.appendChild(form);
        form.submit();
    });

}());
