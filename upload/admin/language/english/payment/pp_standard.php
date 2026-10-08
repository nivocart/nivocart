<?php
// Heading
$_['heading_title']                  = 'PayPal Standard';

// Link
$_['text_pp_standard']               = '<a onclick="window.open(\'https://www.paypal.com/\');"><img src="view/image/payment/paypal.png" alt="PayPal Payments Standard" title="PayPal Payments Standard" style="border:1px solid #EEEEEE;" /></a>';

// Text
$_['text_payment']                   = 'Payment';
$_['text_success']                   = 'Success: You have modified <b>PayPal Standard</b> account details!';
$_['text_authorization']             = 'Authorization';
$_['text_sale']                      = 'Sale';

// Entry
$_['entry_email']                    = 'Email';
$_['entry_test']                     = 'Sandbox Mode';
$_['entry_transaction']              = 'Transaction Method';
$_['entry_debug']                    = 'Debug Mode';
$_['entry_total']                    = 'Total';
$_['entry_total_max']                = 'Total Maximum';
$_['entry_canceled_reversal_status'] = 'Canceled Reversal Status';
$_['entry_completed_status']         = 'Completed Status';
$_['entry_denied_status']            = 'Denied Status';
$_['entry_expired_status']           = 'Expired Status';
$_['entry_failed_status']            = 'Failed Status';
$_['entry_pending_status']           = 'Pending Status';
$_['entry_processed_status']         = 'Processed Status';
$_['entry_refunded_status']          = 'Refunded Status';
$_['entry_reversed_status']          = 'Reversed Status';
$_['entry_voided_status']            = 'Voided Status';
$_['entry_geo_zone']                 = 'Geo Zone';
$_['entry_status']                   = 'Status';
$_['entry_sort_order']               = 'Sort Order';

// Tab
$_['tab_order_status']               = 'Order Status';

// Help
$_['help_test']                      = 'Use the live or testing (sandbox) gateway server to process transactions?';
$_['help_debug']                     = 'Logs additional information to the system log.';
$_['help_total']                     = 'The checkout total the order must reach before this payment method becomes <b>active</b>.';
$_['help_total_max']                 = 'The maximum checkout total the order must reach before this payment method becomes <b>inactive</b>.<br />Leave empty for no maximum.';
$_['help_transaction']               = 'Sale will charge customer immediately. Authorization will put funds on hold for future capture.';

// Error
$_['error_permission']               = 'Warning: You do not have permission to modify <b>PayPal Standard</b>!';
$_['error_email']                    = 'Email is required!';

// Setup Reference panel
$_['text_setup_title']     = 'PayPal Standard Setup Reference';
$_['text_setup_url_label'] = 'IPN URL';
$_['text_setup_url_hint']  = 'Click to select';
$_['setup_sections'] = [
	[
		'title' => 'Step 1 &mdash; Enter your PayPal Business email',
		'intro' => 'Enter the email address of your PayPal <strong>Business</strong> account in the <strong>Email</strong> field above. The IPN handler compares it with the <code>receiver_email</code> sent by PayPal, so the two must match exactly or the payment will not be marked as completed.',
	],
	[
		'title' => 'Step 2 &mdash; Instant Payment Notification (IPN)',
		'intro' => 'NivoCart sends the notification URL below to PayPal with every payment, so nothing needs to be registered. Make sure IPN is not switched off in your PayPal account (<em>Notifications &rarr; Instant payment notifications</em>) and that your server accepts POST requests from PayPal. Every notification is sent back to PayPal for verification before any order is changed.',
		'url'   => true,
	],
	[
		'title' => 'Step 3 &mdash; Map the order statuses',
		'intro' => 'Use the <strong>Order Status</strong> tab to choose what each PayPal payment status does to the order:',
		'rows'  => [
			['Completed', 'Payment received. The receiver email and the amount are checked against the order before it is accepted.'],
			['Pending / Processed', 'Payment is waiting for PayPal (for example an e-check or a manual review) or has been accepted but not yet completed.'],
			['Denied / Expired / Failed', 'The payment did not go through. The order is moved to the status you select so it can be reviewed.'],
			['Refunded', 'A refund was issued from the PayPal account.'],
			['Reversed / Canceled Reversal', 'A chargeback was raised by the buyer, or later resolved in your favour.'],
			['Voided', 'An authorization was voided before it was captured.'],
		],
	],
	[
		'title' => 'Testing and troubleshooting',
		'rows'  => [
			['Sandbox Mode', 'Set Sandbox Mode to <strong>Yes</strong>, use a PayPal sandbox Business email (from developer.paypal.com) and pay with a sandbox buyer account. Set it back to <strong>No</strong> before going live.'],
			['Debug Mode', 'When enabled, IPN requests and responses are written to the system log. Check it first if an order stays at the default status after payment.'],
			['Common causes', 'IPN not verified by PayPal, a receiver email that differs from the Email field, or an amount that differs from the order total.'],
		],
	],
];
