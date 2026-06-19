/**
 * sagepay_redirect.js
 *
 * Loaded only on the standalone payment/sagepay page.
 * Reads the encrypted payload from #sagepay-data's data attributes and
 * auto-submits a hidden form to Sage Pay's hosted VSP Form endpoint.
 *
 * Sage Pay has no recognisable consumer-facing button (unlike PayPal),
 * so we auto-submit rather than waiting for a click — the page shows a
 * brief "Redirecting..." message with a spinner instead.
 */

(function () {
    'use strict';

    var src = document.getElementById('sagepay-data');
    if (!src) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = src.dataset.action;

    function addField(name, value) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    addField('VPSProtocol', '2.23');
    addField('TxType', src.dataset.transaction);
    addField('Vendor', src.dataset.vendor);
    addField('Crypt', src.dataset.crypt);

    document.body.appendChild(form);

    // Small delay so the "Redirecting..." message is visible before navigation
    setTimeout(function () {
        form.submit();
    }, 400);

}());
