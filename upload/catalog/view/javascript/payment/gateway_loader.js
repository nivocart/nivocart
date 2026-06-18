/**
 * gateway_loader.js
 *
 * Central coordinator for payment gateway widgets on the checkout page.
 *
 * Each gateway JS file (pp_standard.js, stripe_payments.js, etc.) registers
 * itself into window.GatewayModules with two methods:
 *
 *   init(data)               — called when the gateway radio is selected.
 *                              data = the gateway's slice of NIVOCART_PAYMENT_DATA.
 *   beforeSubmit(callback)   — called when #button-order is clicked.
 *                              Must call callback() when ready to proceed.
 *                              For sync gateways: call callback() immediately.
 *                              For async gateways (Stripe): do async work first.
 *
 * Adding a new gateway:
 *   1. Create javascript/payment/{code}.js and register into GatewayModules.
 *   2. That's it — this loader handles the rest automatically.
 */

window.GatewayModules = window.GatewayModules || {};

(function ($) {
    'use strict';

    var paymentData = window.NIVOCART_PAYMENT_DATA || {};
    var activeGateway = null;

    // -------------------------------------------------------------------------
    // Widget container management
    // -------------------------------------------------------------------------

    /**
     * Hide all payment widget containers.
     */
    function hideAllWidgets() {
        $('.payment-gateway-widget').hide();
    }

    /**
     * Show the widget container for a given gateway code.
     * Container must have id="widget-{code}".
     */
    function showWidget(code) {
        $('#widget-' + code).slideDown(250);
    }

    // -------------------------------------------------------------------------
    // Gateway activation
    // -------------------------------------------------------------------------

    function activateGateway(code) {
        hideAllWidgets();
        activeGateway = code;

        if (GatewayModules[code] && typeof GatewayModules[code].init === 'function') {
            GatewayModules[code].init(paymentData[code] || {});
        }

        if (paymentData[code]) {
            showWidget(code);
        }
    }

    // -------------------------------------------------------------------------
    // Payment method radio change
    // -------------------------------------------------------------------------

    $('body').on('change', 'input[name="payment_method"]', function () {
        activateGateway($(this).val());
    });

    // -------------------------------------------------------------------------
    // Activate whichever method is pre-selected on page load
    // -------------------------------------------------------------------------

    $(document).ready(function () {
        var preSelected = $('input[name="payment_method"]:checked').val();
        if (preSelected) {
            activateGateway(preSelected);
        }
    });

    // -------------------------------------------------------------------------
    // Place Order button
    // -------------------------------------------------------------------------

    $('#button-order').on('click', function () {
        if (!activeGateway) {
            submitCheckoutForm();
            return;
        }

        var module = GatewayModules[activeGateway];

        if (module && typeof module.beforeSubmit === 'function') {
            module.beforeSubmit(function () {
                submitCheckoutForm();
            });
        } else {
            submitCheckoutForm();
        }
    });

    // -------------------------------------------------------------------------
    // Core form submission (shared by all gateways)
    // -------------------------------------------------------------------------

    window.submitCheckoutForm = function () {
        var $btn = $('#button-order');
        var $form = $('#form');
        var template = window.NIVOCART_TEMPLATE || 'default';

        $.ajax({
            url: 'index.php?route=checkout/checkout',
            type: 'post',
            data: $form.serialize(),
            dataType: 'json',
            beforeSend: function () {
                $btn.attr('disabled', true);
                $btn.after('<span class="wait">&nbsp;<img src="catalog/view/theme/' + template + '/image/loading.gif" alt="" /></span>');
                $('#order-errors').hide().empty();
            },
            complete: function () {
                $btn.attr('disabled', false);
                $('.wait').remove();
            },
            success: function (json) {
                $('.warning, .error, .attention').remove();

                if (json['redirect']) {
                    window.location = json['redirect'];
                } else if (json['error']) {
                    var html = '<div class="warning">';
                    $.each(json['error'], function (field, message) {
                        html += message + '<img src="catalog/view/theme/' + template + '/image/close.png" alt="" class="close" /><br />';
                    });
                    html += '</div>';
                    $('#order-errors').html(html).show();
                    $('html, body').animate({ scrollTop: $('#order-errors').offset().top - 20 }, 500);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    };

}(jQuery));
