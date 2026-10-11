<?php
// Heading
$_['heading_title']         = 'Stripe Payments';

// Link
$_['text_stripe_payments']  = '<a onclick="window.open(\'http://www.stripe.com/\');"><img src="view/image/payment/stripe.png" alt="Stripe" title="Stripe" style="border:1px solid #EEEEEE;" /></a>';

// Text
$_['text_payment']          = 'Payment';
$_['text_success']          = 'Success: You have modified <b>Stripe Payments</b> account details!';
$_['text_test']             = 'Test';
$_['text_live']             = 'Live';
$_['text_authorization']    = 'Authorization';
$_['text_charge']           = 'Charge';

// Entry
$_['entry_publishable_key'] = 'Publishable Key:';
$_['entry_secret_key']      = 'Secret Key:';
$_['entry_webhook_secret']  = 'Webhook Secret:<span class="help">Webhook Secret code must start with <b>whsec_</b>.</span>';
$_['entry_mode']            = 'Transaction Mode:';
$_['entry_method']          = 'Transaction Method:';
$_['entry_total']           = 'Total:<span class="help">The checkout total the order must reach before this payment method becomes <b>active</b>.</span>';
$_['entry_order_status']    = 'Order Status:<span class="help">Paid: Select Processing.</span>';
$_['entry_order_failed']    = 'Order failed:<span class="help">Failed: Select Failed.</span>';
$_['entry_order_disputed']  = 'Order disputed:<span class="help">Disputed: Select Chargeback.</span>';
$_['entry_geo_zone']        = 'Geo Zone:';
$_['entry_wallets']         = 'Apple Pay / Google Pay:<span class="help">Shows Apple Pay and Google Pay buttons on the checkout when the customer\'s device offers them. See the setup notes below before enabling.</span>';
$_['entry_status']          = 'Status:';
$_['entry_sort_order']      = 'Sort Order:';

// Error
$_['error_permission']      = 'Warning: You do not have permission to modify <b>Stripe Payments</b>!';
$_['error_secret_key']      = 'A Secret Key is Required!';
$_['error_publishable_key'] = 'A Publishable Key is Required!';
$_['error_webhook_secret']  = 'A Webhook Secret is Required!';

// Setup Reference panel
$_['text_setup_title']     = 'Stripe Webhook Setup Reference';
$_['text_setup_url_label'] = 'Endpoint URL';
$_['text_setup_url_hint']  = 'Click to select';
$_['setup_sections'] = [
	[
		'title' => 'Step 1 &mdash; Register the endpoint in the Stripe Dashboard',
		'intro' => 'Go to <em>Developers &rarr; Webhooks &rarr; Add endpoint</em> and paste the URL below.',
		'url'   => true,
	],
	[
		'title' => 'Step 2 &mdash; Subscribe to exactly these 3 events',
		'rows'  => [
			['<code>payment_intent.succeeded</code>', 'Async payment confirmation fallback &mdash; fires when Stripe collects payment successfully. Used to mark the order as <em>Paid</em> when the customer\'s browser closed before the redirect completed. Idempotent: skipped if the order is already in the configured paid status.'],
			['<code>payment_intent.payment_failed</code>', 'Fires on card decline, insufficient funds, SCA / 3D-Secure failure, or any other payment error. Updates the order to the configured <em>Failed</em> status and writes the Stripe error code and message to the order history for admin review.'],
			['<code>charge.dispute.created</code>', 'Fires when a customer raises a chargeback. Looks up the order via the payment intent ID stored in order history and moves it to the configured <em>Disputed</em> status so it is flagged for manual review before responding to Stripe.'],
		],
	],
	[
		'title' => 'Step 3 &mdash; Copy the Signing Secret and save it above',
		'intro' => 'After adding the endpoint, Stripe reveals a <em>Signing Secret</em> (starts with <code>whsec_</code>). Paste it into the <strong>Webhook Secret</strong> field at the top of this page. The webhook handler verifies every incoming request against this secret &mdash; without it all webhook calls are rejected with HTTP&nbsp;400.',
	],
	[
		'title' => 'Apple Pay &amp; Google Pay (optional)',
		'intro' => 'Both wallets are processed as normal card payments, so the fees, payouts and webhooks are the same. Turn on the <strong>Apple Pay / Google Pay</strong> setting above only after the steps below.',
		'rows'  => [
			['Stripe Dashboard', 'Under <em>Settings &rarr; Payment methods</em> make sure Apple Pay and Google Pay are switched on.'],
			['Register the domain', 'Under <em>Settings &rarr; Payment method domains</em> add every domain that shows the checkout (live shop, demo, bench). Do this once per domain.'],
			['Apple Pay file', 'Stripe gives you a file called <code>apple-developer-merchantid-domain-association</code>. Upload it to <code>.well-known/</code> in the web root of each domain so that <code>https://your-domain/.well-known/apple-developer-merchantid-domain-association</code> opens in a browser, then click Verify in Stripe.'],
			['Google Pay', 'No registration is needed. The domain must use HTTPS.'],
			['Where the buttons show', 'Apple Pay appears in Safari on a device with a card in Wallet. Google Pay appears in Chrome when the customer is signed in to a Google account with a saved card. Other browsers just show the card form.'],
			['Live and test keys', 'Wallets use the same keys as the card form. Domain registration is separate for test and live mode in the Stripe Dashboard.'],
		],
	],
	[
		'title' => 'Technical notes',
		'rows'  => [
			['API version', 'In the Stripe Dashboard under <em>Developers &rarr; Webhooks &rarr; API version</em> select <strong>2026-08-26.dahlia</strong> (or the latest Dahlia release). The three events above are stable across all Dahlia versions; the new granular error codes in this release automatically enrich the failure messages logged to order history.'],
			['Payload field used', 'The handler reads <code>data.object.metadata.order_ref</code> from <code>payment_intent.succeeded</code> and <code>payment_intent.payment_failed</code> to resolve the NivoCart order ID. This metadata key is written by the payment controller at intent-creation time &mdash; do not rename it.'],
			['Dispute lookup', 'For <code>charge.dispute.created</code> there is no <code>order_ref</code> metadata. The handler resolves the order by searching the order history table for the payment intent ID. Ensure order history comments are never cleared.'],
			['.htaccess rule', 'Add this rule to your root <code>.htaccess</code> so the webhook URL is not rewritten by NivoCart\'s SEO router:<br /><code>RewriteRule ^catalog/webhooks/ - [L]</code>'],
			['Stripe retry policy', 'Stripe retries failed deliveries (non-2xx responses) up to 3&nbsp;days with exponential back-off. The handler sends HTTP&nbsp;200 immediately and flushes the response before any DB work, so server-side timeouts will not cause duplicate order-status updates &mdash; the idempotency guard handles retries safely.'],
			['Log file', 'All webhook activity is written to <code>system/logs/stripe_webhook.log</code>. Check this file first when debugging missing order updates.'],
		],
	],
];
