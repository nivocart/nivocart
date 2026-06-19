/**
 * sagepay.js
 *
 * Sage Pay gateway module for NivoCart checkout.
 *
 * Registers into window.GatewayModules['sagepay'].
 *
 * Sage Pay is a redirect gateway — like PP Standard, the order must be
 * created first (by checkout_confirm), which then redirects to the
 * standalone payment/sagepay page where the encrypted payload is built
 * and auto-submitted to Sage Pay's hosted form.
 *
 * No browser-side payment work happens on the checkout page itself.
 */

(function ($) {
    'use strict';

    var initialised = false;

    window.GatewayModules = window.GatewayModules || {};

    window.GatewayModules['sagepay'] = {
        /**
         * Called by gateway_loader when sagepay radio is selected.
         * data = paymentData['sagepay'] from NIVOCART_PAYMENT_DATA.
         */
        init: function (data) {
            if (initialised) return;
            initialised = true;

            if (data.testmode) {
                $('#widget-sagepay .sagepay-testmode-warning').show();
            }
        },
        /**
         * Redirect gateway — no async browser-side work needed.
         * checkout_confirm will redirect to the standalone Sage Pay page
         * once the order is created.
         */
        beforeSubmit: function (callback) {
            callback();
        }
    };

}(jQuery));
