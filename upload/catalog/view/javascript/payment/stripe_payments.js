/**
 * stripe_payments.js
 *
 * Stripe gateway module for NivoCart checkout.
 *
 * Registers into window.GatewayModules['stripe_payments'].
 *
 * init()          - mounts the Stripe card element and, when enabled in the
 *                   admin, the Apple Pay / Google Pay (Express Checkout) buttons.
 * sessionUpdate() - called after the shipping / payment method changes so the
 *                   wallet sheet shows the current total.
 * beforeSubmit()  - asks the server for the PaymentIntent (the server works out
 *                   the amount), confirms the card, stores the intent in the
 *                   session, then calls callback(). If a wallet payment has
 *                   already been made it just calls callback().
 *
 * The amount is never taken from the browser: payment/stripe_payments/intentCreate
 * always works it out on the server from the cart.
 */

(function ($) {
    'use strict';

    var stripeInstance = null;
    var stripeCard = null;
    var elementsExpress = null;
    var expressElement = null;
    var expressSecret = '';
    var expressAmount = 0;
    var walletPaid = false;
    var mounted = false;
    var data = {};

    window.GatewayModules = window.GatewayModules || {};

    window.GatewayModules['stripe_payments'] = {

        init: function (gatewayData) {
            data = gatewayData;

            if (mounted) return;
            mounted = true;

            if (typeof Stripe === 'undefined') {
                showError('Payment system unavailable. Please refresh and try again.');
                $('#button-order').attr('disabled', true);
                return;
            }

            stripeInstance = Stripe(data.publishable_key);

            mountCard();

            if (data.wallets) {
                mountWallets();
            }
        },

        sessionUpdate: function () {
            refreshWallets();
        },

        beforeSubmit: function (callback) {
            var $btn = $('#button-order');
            var owner = $('#stripe-cc-owner').val();

            // Paid already with Apple Pay / Google Pay (or an earlier attempt for this exact total)
            if (walletPaid) {
                callback();
                return;
            }

            $btn.attr('disabled', true);
            showError('');

            if (!stripeInstance || !stripeCard) {
                showError('Payment not ready. Please wait a moment and try again.');
                $btn.attr('disabled', false);
                return;
            }

            intentRequest().done(function (json) {
                if (json['error']) {
                    showError(json['error']);
                    $btn.attr('disabled', false);
                    return;
                }

                if (json['paid']) {
                    walletPaid = true;
                    callback();
                    return;
                }

                stripeInstance.confirmCardPayment(json['client_secret'], {
                    payment_method: {
                        card: stripeCard,
                        billing_details: { name: owner }
                    }
                }).then(function (result) {
                    if (result.error) {
                        showError(result.error.message);
                        $btn.attr('disabled', false);
                        return;
                    }

                    if (result.paymentIntent.status === 'succeeded') {
                        storeIntent(result.paymentIntent.id, function () {
                            callback();
                        }, function () {
                            $btn.attr('disabled', false);
                        });
                    } else {
                        showError('Your payment could not be completed. Please try again.');
                        $btn.attr('disabled', false);
                    }
                });
            }).fail(function () {
                showError('Payment system unavailable. Please refresh and try again.');
                $btn.attr('disabled', false);
            });
        }
    };

    // -------------------------------------------------------------------------
    // Server calls
    // -------------------------------------------------------------------------

    function intentRequest() {
        return $.ajax({
            url: data.intent_url,
            type: 'post',
            dataType: 'json'
        });
    }

    function storeIntent(intentId, onSuccess, onFailure) {
        $.ajax({
            url: data.store_url,
            type: 'post',
            data: { payment_intent_id: intentId },
            dataType: 'json',
            success: function (json) {
                if (json['error']) {
                    showError(json['error']);
                    onFailure();
                    return;
                }

                onSuccess();
            },
            error: function () {
                showError('Network error verifying payment. Please contact support.');
                onFailure();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Card element
    // -------------------------------------------------------------------------

    function mountCard() {
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
            showError(event.error ? event.error.message : '');
        });
    }

    // -------------------------------------------------------------------------
    // Apple Pay / Google Pay (Stripe Express Checkout Element)
    // -------------------------------------------------------------------------

    function mountWallets() {
        intentRequest().done(function (json) {
            if (json['paid']) {
                walletPaid = true;
                showNotice(data.text_wallet_paid);
                return;
            }

            // No intent (empty cart, error): leave the card form on its own
            if (json['error']) return;

            buildExpress(json);
        });
    }

    function buildExpress(json) {
        expressSecret = json['client_secret'];
        expressAmount = json['amount'];

        elementsExpress = stripeInstance.elements({ clientSecret: expressSecret });

        expressElement = elementsExpress.create('expressCheckout', {
            buttonType: { applePay: 'buy', googlePay: 'buy' },
            paymentMethods: { applePay: 'auto', googlePay: 'auto', link: 'never' }
        });

        expressElement.mount('#stripe-express-element');

        // Only show the wallet area when this browser / device offers a wallet
        expressElement.on('ready', function (event) {
            if (event.availablePaymentMethods) {
                $('#stripe-express-or').text(data.text_wallet_or || '');
                $('#stripe-express-wrap').prop('hidden', false);
            }
        });

        expressElement.on('confirm', function (event) {
            showError('');

            intentRequest().done(function (fresh) {
                if (fresh['error'] || fresh['paid']) {
                    event.paymentFailed({ reason: 'fail' });
                    showError(fresh['error'] || data.text_wallet_paid);
                    return;
                }

                // The total changed after the wallet sheet was opened: stop, nothing is charged
                if (fresh['client_secret'] !== expressSecret || fresh['amount'] !== expressAmount) {
                    event.paymentFailed({ reason: 'fail' });
                    showError(data.error_wallet_total_changed);
                    refreshWallets();
                    return;
                }

                stripeInstance.confirmPayment({
                    elements: elementsExpress,
                    confirmParams: { return_url: window.location.href },
                    redirect: 'if_required'
                }).then(function (result) {
                    if (result.error) {
                        event.paymentFailed({ reason: 'fail' });
                        showError(result.error.message);
                        return;
                    }

                    if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                        storeIntent(result.paymentIntent.id, function () {
                            walletPaid = true;
                            showNotice(data.text_wallet_paid);

                            // Place the order now. If the form needs attention the customer
                            // fixes it and presses the order button: no second payment is taken.
                            if (typeof window.submitCheckoutForm === 'function') {
                                window.submitCheckoutForm();
                            }
                        }, function () {});
                    } else {
                        event.paymentFailed({ reason: 'fail' });
                        showError('Your payment could not be completed. Please try again.');
                    }
                });
            }).fail(function () {
                event.paymentFailed({ reason: 'fail' });
                showError('Payment system unavailable. Please refresh and try again.');
            });
        });
    }

    // Bring the intent (and the wallet sheet amount) up to date with the current cart
    function refreshWallets() {
        if (!data.wallets || !elementsExpress || walletPaid) return;

        intentRequest().done(function (json) {
            if (json['error']) return;

            if (json['paid']) {
                walletPaid = true;
                showNotice(data.text_wallet_paid);
                return;
            }

            if (json['client_secret'] !== expressSecret) {
                // The server had to start a new intent: rebuild the buttons around it
                expressElement.destroy();
                $('#stripe-express-wrap').prop('hidden', true);
                buildExpress(json);
                return;
            }

            expressAmount = json['amount'];
            elementsExpress.fetchUpdates();
        });
    }

    // The payment method also changes the total (fees), so refresh after it is saved
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.url && settings.url.indexOf('checkout/checkout/paymentMethod') !== -1) {
            refreshWallets();
        }
    });

    // -------------------------------------------------------------------------
    // Messages
    // -------------------------------------------------------------------------

    function showError(message) {
        $('#stripe-card-errors').text(message);
    }

    function showNotice(message) {
        $('#stripe-wallet-notice').text(message || '').prop('hidden', !message);
    }

}(jQuery));
