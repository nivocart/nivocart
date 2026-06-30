/**
 * bank_transfer.js
 *
 * Bank Transfer gateway module for NivoCart checkout.
 * Silent/direct gateway — no payment processing, just displays the
 * admin-configured bank instruction text and proceeds on submit.
 */
(function ($) {
    'use strict';

    var initialised = false;

    window.GatewayModules = window.GatewayModules || {};

    window.GatewayModules['bank_transfer'] = {
        init: function (data) {
            if (initialised) return;
            initialised = true;
            $('#bank-transfer-heading').text(data.heading || '');
            $('#bank-transfer-description').text(data.description || '');
            $('#bank-transfer-details').html(data.bank_details || '');
            $('#bank-transfer-payment-note').text(data.payment_note || '');
        },
        beforeSubmit: function (callback) {
            callback();
        }
    };
}(jQuery));
