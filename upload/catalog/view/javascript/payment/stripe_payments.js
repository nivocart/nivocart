/**
 * stripe_payments.js
 *
 * Stripe gateway module for NivoCart checkout.
 *
 * Registers into window.GatewayModules['stripe_payments'].
 *
 * init()         — fetches a PaymentIntent, mounts Stripe Elements.
 * beforeSubmit() — calls confirmCardPayment(), stores intent ID in session
 *                  via storeIntent endpoint, then calls callback().
 */

(function ($) {
    'use strict';

    var stripeInstance = null;
    var stripeCard = null;
    var clientSecret = null;
    var initialised = false;
    var intentFetched = false;
    var data = {};

    window.GatewayModules = window.GatewayModules || {};

    window.GatewayModules['stripe_payments'] = {

        init: function (gatewayData) {
            data = gatewayData;

            if (initialised) return;
            initialised = true;

            fetchIntent();
        },

        beforeSubmit: function (callback) {
            var $btn = $('#button-order');
            var $errorDiv = $('#stripe-card-errors');
            var owner = $('#stripe-cc-owner').val();

            $btn.attr('disabled', true);
            $errorDiv.text('');

            if (!clientSecret) {
                $errorDiv.text('Payment not ready. Please wait a moment and try again.');
                $btn.attr('disabled', false);
                return;
            }

            stripeInstance.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: stripeCard,
                    billing_details: { name: owner }
                }
            }).then(function (result) {
                if (result.error) {
                    $errorDiv.text(result.error.message);
                    $btn.attr('disabled', false);
                    return;
                }

                if (result.paymentIntent.status === 'succeeded') {
                    $.ajax({
                        url: data.store_url,
                        type: 'post',
                        data: { payment_intent_id: result.paymentIntent.id },
                        dataType: 'json',
                        success: function (json) {
                            if (json['error']) {
                                $errorDiv.text(json['error']);
                                $btn.attr('disabled', false);
                                return;
                            }
                            callback();
                        },
                        error: function () {
                            $errorDiv.text('Network error verifying payment. Please contact support.');
                            $btn.attr('disabled', false);
                        }
                    });
                }
            });
        }
    };

    // -------------------------------------------------------------------------

    function fetchIntent() {
        if (intentFetched) return;
        intentFetched = true;

        $.ajax({
            url: data.intent_url,
            type: 'post',
            data: { cart_total: data.total, currency_code: data.currency },
            dataType: 'json',
            success: function (json) {
                if (json['error']) {
                    $('#stripe-card-errors').text(json['error']);
                    $('#button-order').attr('disabled', true);
                    return;
                }

                clientSecret = json['client_secret'];
                mountElements(json['publishable_key']);
                $('#button-order').attr('disabled', false);
            },
            error: function () {
                $('#stripe-card-errors').text('Payment system unavailable. Please refresh and try again.');
                $('#button-order').attr('disabled', true);
            }
        });
    }

    function mountElements(publishableKey) {
        stripeInstance = Stripe(publishableKey);

        var elements = stripeInstance.elements();

        stripeCard = elements.create('card', {
            style: {
                base: {
                    fontSize: '14px',
                    color: '#333',
                    '::placeholder': { color: '#aaa' }
                },
                invalid: { color: '#c0392b' }
            }
        });

        stripeCard.mount('#stripe-card-element');

        stripeCard.on('change', function (event) {
            document.getElementById('stripe-card-errors').textContent = event.error ? event.error.message : '';
        });
    }

}(jQuery));
