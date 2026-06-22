/**
 * klarna.js
 *
 * Klarna Payments gateway module for NivoCart checkout.
 *
 * Registers into window.GatewayModules['klarna'].
 *
 * init()          — calls sessionCreate to get a client_token, loads
 *                   Klarna's SDK, initialises the widget and renders it
 *                   into #klarna-payments-container.
 *
 * beforeSubmit()  — calls Klarna.Payments.authorize(), on success posts
 *                   the authorization_token to storeAuthorization, then
 *                   calls the checkout callback to submit the order.
 *
 * sessionUpdate() — public, called externally (e.g. on shipping method
 *                   change) to re-sync the Klarna session amount before
 *                   authorize() fires. See gateway_loader.js notes.
 *
 * Flow mirrors stripe_payments.js: async work happens in beforeSubmit(),
 * callback() fires only once everything server-side is confirmed ready.
 */

(function ($) {
    'use strict';

    // -------------------------------------------------------------------------
    // Module state
    // -------------------------------------------------------------------------

    var data = {};               // Slice of NIVOCART_PAYMENT_DATA['klarna']
    var initialised = false;     // Prevents double-init on radio re-select
    var sdkLoaded = false;       // Klarna SDK script injected
    var sessionReady = false;    // client_token received + widget loaded
    var clientToken = null;      // From sessionCreate response
    var sessionId = null;        // From sessionCreate response
    var paymentCategory = null;  // First available payment_method_category

    var SDK_URL = 'https://x.klarnacdn.net/kp/lib/v1/api.js';

    window.GatewayModules = window.GatewayModules || {};

    // -------------------------------------------------------------------------
    // Module registration
    // -------------------------------------------------------------------------

    window.GatewayModules['klarna'] = {

        /**
         * Called by gateway_loader.js when the Klarna radio is selected.
         * Safe to call multiple times — only does real work once.
         */
        init: function (gatewayData) {
            data = gatewayData;

            if (initialised) return;
            initialised = true;

            $('#button-order').attr('disabled', true);
            showError('');

            fetchSession();
        },

        /**
         * Called by gateway_loader.js when #button-order is clicked.
         * Runs Klarna's client-side authorization flow, then stashes the
         * resulting token server-side before calling back into the normal
         * checkout form submission.
         */
        beforeSubmit: function (callback) {
            var $btn = $('#button-order');

            if (!sessionReady || !clientToken) {
                showError('Klarna is not ready yet. Please wait a moment and try again.');
                return;
            }

            $btn.attr('disabled', true);
            showError('');

            Klarna.Payments.authorize(
                { payment_method_category: paymentCategory },
                {},
                function (response) {
                    if (!response.approved) {
                        // Customer cancelled, was declined, or needs to
                        // correct something — show_form: true means the
                        // widget stays open for another attempt.
                        showError(response.error ? response.error.invalid_fields
                            ? 'Please check your details and try again.'
                            : 'Payment was not approved. Please try again or choose a different payment method.'
                            : 'Payment was not approved. Please try again.');

                        $btn.attr('disabled', false);
                        return;
                    }

                    // Authorization approved — stash token server-side
                    // then fire the normal checkout form submission.
                    storeAuthorization(response.authorization_token, callback, $btn);
                }
            );
        },

        /**
         * Re-syncs the Klarna session when the cart total changes
         * (shipping method change, coupon applied, etc).
         * Called externally — see checkout.tpl's shipping_method change
         * handler where relevant.
         */
        sessionUpdate: function () {
            if (!sessionId) return;

            $.ajax({
                url: data.update_url,
                type: 'post',
                dataType: 'json',
                success: function (json) {
                    if (json['error']) {
                        // Non-fatal — log but don't surface to customer.
                        // The authorize() call will fail server-side if
                        // the amounts are genuinely mismatched, which is
                        // the correct safety net.
                        console.warn('Klarna sessionUpdate failed: ' + json['error']);
                    }
                }
            });
        }
    };

    // -------------------------------------------------------------------------
    // Session creation
    // -------------------------------------------------------------------------

    function fetchSession() {
        $.ajax({
            url: data.session_url,
            type: 'post',
            dataType: 'json',
            success: function (json) {
                if (json['error']) {
                    showError('Klarna is currently unavailable: ' + json['error']);
                    $('#button-order').attr('disabled', false);
                    return;
                }

                clientToken = json['client_token'];
                sessionId = json['session_id'];

                // Pick the first available payment method category.
                // Klarna returns categories like 'pay_now', 'pay_later',
                // 'pay_over_time' depending on the merchant configuration
                // and the customer's country. The widget renders whichever
                // category we load — if multiple are available, the
                // customer sees them as tabs inside the widget itself.
                var categories = json['payment_method_categories'] || [];

                if (categories.length === 0) {
                    showError('No Klarna payment options are available for your location.');
                    $('#button-order').attr('disabled', false);
                    return;
                }

                paymentCategory = categories[0]['identifier'];

                loadSdk(function () {
                    initWidget(categories);
                });
            },
            error: function () {
                showError('Could not connect to Klarna. Please refresh and try again.');
                $('#button-order').attr('disabled', false);
            }
        });
    }

    // -------------------------------------------------------------------------
    // SDK loading
    // -------------------------------------------------------------------------

    function loadSdk(callback) {
        if (sdkLoaded) {
            callback();
            return;
        }

        // Klarna's SDK is loaded on demand — only when the Klarna radio
        // is actually selected — to avoid adding a third-party script to
        // every checkout page load regardless of payment method chosen.
        var script = document.createElement('script');
        script.src = SDK_URL;

        script.onload = function () {
            sdkLoaded = true;
            callback();
        };

        script.onerror = function () {
            showError('Could not load Klarna payment library. Please check your connection and try again.');
            $('#button-order').attr('disabled', false);
        };

        document.head.appendChild(script);
    }

    // -------------------------------------------------------------------------
    // Widget initialisation
    // -------------------------------------------------------------------------

    function initWidget(categories) {
        Klarna.Payments.init({
            client_token: clientToken
        });

        // Load the first payment category into the container. If Klarna
        // returns multiple categories, they all share the same container
        // div — Klarna renders internal tab navigation automatically.
        Klarna.Payments.load(
            {
                container: '#klarna-payments-container',
                payment_method_category: paymentCategory
            },
            {},
            function (response) {
                if (response.show_form === false) {
                    // Klarna declined to show the widget for this
                    // customer/cart combination — hide the container and
                    // inform the customer.
                    showError('Klarna is not available for this order. Please choose a different payment method.');
                    $('#button-order').attr('disabled', false);
                    return;
                }

                sessionReady = true;
                $('#button-order').attr('disabled', false);
            }
        );
    }

    // -------------------------------------------------------------------------
    // Authorization storage
    // -------------------------------------------------------------------------

    function storeAuthorization(token, callback, $btn) {
        $.ajax({
            url: data.store_url,
            type: 'post',
            data: { authorization_token: token },
            dataType: 'json',
            success: function (json) {
                if (json['error']) {
                    showError(json['error']);
                    $btn.attr('disabled', false);
                    return;
                }

                // Token safely stashed server-side — proceed with the
                // normal checkout form submission (addOrder etc).
                callback();
            },
            error: function () {
                showError('Network error confirming payment. Please try again.');
                $btn.attr('disabled', false);
            }
        });
    }

    // -------------------------------------------------------------------------
    // UI helpers
    // -------------------------------------------------------------------------

    function showError(message) {
        $('#klarna-payments-errors').text(message);
    }

}(jQuery));
