/**
 * pp_standard.js
 *
 * PayPal Standard gateway module for NivoCart checkout.
 *
 * Registers into window.GatewayModules['pp_standard'].
 *
 * init()         — shows the widget (already visible via gateway_loader),
 *                  attaches the button click handler once.
 * beforeSubmit() — for PP Standard the order must be created FIRST by
 *                  checkout_confirm, which then redirects to the standalone
 *                  payment/pp_standard page. So beforeSubmit() just calls
 *                  the normal form submission — no async work needed here.
 *
 * The actual PayPal form build + submit happens on the standalone
 * payment/pp_standard page (pp_standard.tpl + pp_standard_redirect.js),
 * after checkout_confirm has created the order and session['order_id'] exists.
 */

(function ($) {
    'use strict';

    var initialised = false;

    window.GatewayModules = window.GatewayModules || {};

    window.GatewayModules['pp_standard'] = {
        /**
         * Called by gateway_loader when pp_standard radio is selected.
         * data = paymentData['pp_standard'] from NIVOCART_PAYMENT_DATA.
         */
        init: function (data) {
            if (initialised) return;
            initialised = true;

            // Testmode banner
            if (data.testmode) {
                $('#widget-pp_standard .pp-testmode-warning').show();
            }
        },

        /**
         * PP Standard is a redirect gateway — no browser-side payment work.
         * Just proceed to form submission (checkout_confirm will handle the
         * redirect to the standalone PayPal page).
         */
        beforeSubmit: function (callback) {
            callback();
        }
    };

}(jQuery));
