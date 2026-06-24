/**
 * pp_express.js
 *
 * PayPal Express gateway module for NivoCart.
 * Registers into window.GatewayModules for gateway_loader.js to call.
 *
 * Requires: window.NIVOCART_PAYMENT_DATA.pp_express (set by ModelCheckoutPaymentWidget)
 */

(function () {
	'use strict';

	var WIDGET_ID = 'payment-widget-pp_express';
	var PAYLATER_ID = 'payment-widget-paylater-pp_express';
	var SDK_ID = 'paypal-js-sdk';

	// ── Retrieve widget data passed from PHP ──────────────────────────────────
	function cfg() {
		return (window.NIVOCART_PAYMENT_DATA && window.NIVOCART_PAYMENT_DATA.pp_express) || {};
	}

	// ── Inject the PayPal JS SDK <script> tag once ────────────────────────────
	function loadSDK(callback) {
		if (document.getElementById(SDK_ID)) {
			// Already loaded
			if (typeof paypal !== 'undefined') {
				callback();
			} else {
				// Script tag exists but SDK not ready yet — wait
				document.getElementById(SDK_ID).addEventListener('load', callback);
			}
			return;
		}

		var data = cfg();
		var params = [
			'client-id=' + encodeURIComponent(data.client_id || ''),
			'currency=' + encodeURIComponent(data.currency || 'GBP'),
			'intent=' + encodeURIComponent((data.intent || 'capture').toLowerCase()),
			'components=buttons',
		];

		if (data.pay_later) {
			params.push('enable-funding=paylater');
		}

		var script = document.createElement('script');
		script.id = SDK_ID;
		script.src = 'https://www.paypal.com/sdk/js?' + params.join('&');
		script.setAttribute('data-namespace', 'paypal');

		if (data.sandbox) {
			// Sandbox buyer accounts require data-partner-attribution-id omitted
			script.setAttribute('data-sdk-integration-source', 'merchant-PPCP');
		}

		script.onload = callback;
		script.onerror = function () {
			showError('PayPal SDK could not be loaded. Please refresh and try again.');
		};

		document.head.appendChild(script);
	}

	// ── Render PayPal buttons into the widget container ───────────────────────
	function renderButtons() {
		var data = cfg();
		var container = document.getElementById(WIDGET_ID);

		if (!container) return;

		container.innerHTML = '';

		// Main PayPal button
		paypal.Buttons({
			style: {
				layout: 'vertical',
				color: 'gold',
				shape: 'rect',
				label: 'paypal',
				height: 45,
			},

			// ── Create order server-side ──────────────────────────────────────
			createOrder: function () {
				return fetch(data.url_create_order, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'token=' + encodeURIComponent(data.token || ''),
				})
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (json.error) {
						showError(json.error);
						return Promise.reject(json.error);
					}
					return json.id;
				});
			},

			// ── Customer approved in pop-up ───────────────────────────────────
			onApprove: function (ppData) {
				showLoading(true);

				return fetch(data.url_capture_order, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'token=' + encodeURIComponent(data.token || '') + '&pp_order_id=' + encodeURIComponent(ppData.orderID),
				})
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (json.error) {
						showLoading(false);
						showError(json.error);
						return;
					}

					// Capture succeeded — arm the flag and let checkout_confirm finalize
					window._ppExpressCaptured = true;

					if (typeof window._ppExpressResolve === 'function') {
						window._ppExpressResolve();
					}
				})
				.catch(function () {
					showLoading(false);
					showError('An error occurred processing your payment. Please try again.');
				});
			},

			// ── Customer cancelled in pop-up ──────────────────────────────────
			onCancel: function () {
				fetch(data.url_cancel_order, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'token=' + encodeURIComponent(data.token || ''),
				});
			},

			// ── SDK error ─────────────────────────────────────────────────────
			onError: function (err) {
				showLoading(false);
				showError('PayPal encountered an error. Please try again.');
				console.error('PayPal SDK error:', err);
			},

		}).render('#' + WIDGET_ID);

		// ── Optional Pay Later button ─────────────────────────────────────────
		if (data.pay_later) {
			// Ensure container exists
			var plContainer = document.getElementById(PAYLATER_ID);

			if (!plContainer) {
				plContainer = document.createElement('div');
				plContainer.id = PAYLATER_ID;
				plContainer.style.marginTop = '10px';
				container.parentNode.insertBefore(plContainer, container.nextSibling);
			} else {
				plContainer.innerHTML = '';
			}

			paypal.Buttons({
				fundingSource: paypal.FUNDING.PAYLATER,
				style: {
					layout: 'vertical',
					color: 'white',
					shape: 'rect',
					label: 'pay',
					height: 45,
				},

				createOrder: function () {
					return fetch(data.url_create_order, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'token=' + encodeURIComponent(data.token || ''),
					})
					.then(function (res) { return res.json(); })
					.then(function (json) {
						if (json.error) {
							showError(json.error);
							return Promise.reject(json.error);
						}
						return json.id;
					});
				},

				onApprove: function (ppData) {
					showLoading(true);

					return fetch(data.url_capture_order, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'token=' + encodeURIComponent(data.token || '') + '&pp_order_id=' + encodeURIComponent(ppData.orderID),
					})
					.then(function (res) { return res.json(); })
					.then(function (json) {
						if (json.error) {
							showLoading(false);
							showError(json.error);
							return;
						}
						window._ppExpressCaptured = true;
						if (typeof window._ppExpressResolve === 'function') {
							window._ppExpressResolve();
						}
					})
					.catch(function () {
						showLoading(false);
						showError('An error occurred processing your payment. Please try again.');
					});
				},

				onCancel: function () {
					fetch(data.url_cancel_order, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'token=' + encodeURIComponent(data.token || ''),
					});
				},

				onError: function (err) {
					showLoading(false);
					showError('PayPal Pay Later encountered an error. Please try again.');
					console.error('PayPal Pay Later error:', err);
				},

			}).render('#' + PAYLATER_ID);
		}
	}

	// ── UI helpers ────────────────────────────────────────────────────────────
	function showError(msg) {
		var el = document.getElementById('pp-express-error');
		if (!el) {
			el = document.createElement('div');
			el.id = 'pp-express-error';
			el.className = 'warning';
			var container = document.getElementById(WIDGET_ID);
			if (container) {
				container.parentNode.insertBefore(el, container);
			}
		}
		el.textContent = msg;
		el.style.display = 'block';
	}

	function clearError() {
		var el = document.getElementById('pp-express-error');
		if (el) el.style.display = 'none';
	}

	function showLoading(show) {
		var container = document.getElementById(WIDGET_ID);
		var loader = document.getElementById('pp-express-loading');

		if (show) {
			if (!loader && container) {
				loader = document.createElement('div');
				loader.id = 'pp-express-loading';
				loader.innerHTML = '<img src="catalog/view/theme/default/image/loading.gif" alt="" /> Processing...';
				container.parentNode.insertBefore(loader, container);
			}
			if (loader) loader.style.display = 'block';
		} else {
			if (loader) loader.style.display = 'none';
		}
	}

	// ── GatewayModules registration ───────────────────────────────────────────
	window.GatewayModules = window.GatewayModules || {};

	window.GatewayModules['pp_express'] = {
		/**
		 * Called by gateway_loader.js when pp_express payment method is selected.
		 * Loads the SDK and renders the buttons.
		 */
		init: function () {
			clearError();
			showLoading(false);
			window._ppExpressCaptured = false;
			window._ppExpressResolve  = null;

			loadSDK(function () {
				renderButtons();
			});
		},

		/**
		 * Called by gateway_loader.js when #button-order is clicked.
		 * For PayPal, the customer has already approved in the pop-up by this point,
		 * so we just resolve immediately — captureOrder already ran in onApprove.
		 *
		 * @param {Function} callback — call to proceed with form submit
		 */
		beforeSubmit: function (callback) {
			// Store the resolve function so onApprove can trigger it
			window._ppExpressResolve = callback;
			// The PayPal button click is what initiates the flow —
			// beforeSubmit just arms the callback here.
			// If the customer hasn't clicked the PayPal button yet,
			// show a nudge rather than proceeding.
			if (!window._ppExpressCaptured) {
				showError('Please complete your PayPal payment above before confirming your order.');
				return;
			}
			callback();
		},
	};

})();
