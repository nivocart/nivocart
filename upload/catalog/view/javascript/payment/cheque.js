/**
 * cheque.js
 *
 * Cheque / Money Order gateway module for NivoCart checkout.
 * Silent/direct gateway — no payment processing, just displays the
 * "payable to" instruction text and proceeds on submit.
 */
(function ($) {
    'use strict';

    var initialised = false;

    window.GatewayModules = window.GatewayModules || {};

    window.GatewayModules['cheque'] = {
        init: function (data) {
            if (initialised) return;
            initialised = true;
            $('#cheque-heading').text(data.heading || '');
            $('#cheque-payable-label').text(data.payable_label || '');
            $('#cheque-payable-to').text(data.payable_to || '');
            $('#cheque-address-label').text(data.address_label || '');
            $('#cheque-address').html(data.address || '');
            $('#cheque-payment-note').text(data.payment_note || '');
        },
        beforeSubmit: function (callback) {
            callback();
        }
    };
}(jQuery));
