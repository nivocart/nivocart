<?php
// Heading
$_['heading_title']               = 'PayPal Express';
$_['heading_order']               = 'Order';

// Link
$_['text_pp_express']             = '<a onclick="window.open(\'https://www.paypal.com/\');"><img src="view/image/payment/paypal.png" alt="PayPal" title="PayPal" style="border:1px solid #EEEEEE;" /></a>';

// Text
$_['text_payment']                = 'Payment';
$_['text_success']                = 'Success: PayPal Express settings have been saved!';
$_['text_debug_clear_success']    = 'Success: Debug log has been cleared!';
$_['text_capture']                = 'Capture';
$_['text_authorize']              = 'Authorize';

// Tabs (settings page)
$_['tab_api']                     = 'API Credentials';
$_['tab_general']                 = 'General';
$_['tab_order_status']            = 'Order Status';
$_['tab_debug_log']               = 'Debug Log';

// Entry labels (settings page)
$_['entry_client_id']             = 'Live Client ID';
$_['entry_client_secret']         = 'Live Client Secret';
$_['entry_sandbox_client_id']     = 'Sandbox Client ID';
$_['entry_sandbox_client_secret'] = 'Sandbox Client Secret';
$_['entry_webhook_id']            = 'Live Webhook ID';
$_['entry_sandbox_webhook_id']    = 'Sandbox Webhook ID';
$_['entry_sandbox']               = 'Sandbox Mode';
$_['entry_transaction_mode']      = 'Transaction Mode';
$_['entry_pay_later']             = 'Pay Later Button';
$_['entry_currency']              = 'Default Currency';
$_['entry_debug']                 = 'Debug Logging';
$_['entry_total']                 = 'Minimum Order Total';
$_['entry_total_max']             = 'Maximum Order Total';
$_['entry_geo_zone']              = 'Geo Zone';
$_['entry_status']                = 'Status';
$_['entry_sort_order']            = 'Sort Order';

// Entry labels (order status mappings)
$_['entry_completed_status']      = 'Completed Status';
$_['entry_pending_status']        = 'Pending Status';
$_['entry_failed_status']         = 'Failed Status';
$_['entry_refunded_status']       = 'Refunded Status';
$_['entry_voided_status']         = 'Voided Status';
$_['entry_denied_status']         = 'Denied Status';
$_['entry_expired_status']        = 'Expired Status';
$_['entry_webhook_url']           = 'Webhook URL';

// Help text (settings page)
$_['help_sandbox']                = 'Enable to use the PayPal sandbox environment for testing.';
$_['help_transaction_mode']       = 'Capture charges the customer immediately. Authorize places funds on hold for manual capture later.';
$_['help_pay_later']              = 'Show a separate Pay Later button alongside the standard PayPal button.';
$_['help_currency']               = 'The currency sent to PayPal. Must be supported by PayPal.';
$_['help_debug']                  = 'Logs API requests and responses to pp_express.log.';
$_['help_total']                  = 'Minimum order total required for this payment method to be available.';
$_['help_total_max']              = 'Maximum order total. Leave empty for no maximum.';
$_['help_webhook_id']             = 'The Webhook ID from your PayPal app dashboard. Required for server-side event verification.';

// Order action panel
$_['text_payment_info']           = 'PayPal Payment Information';
$_['text_intent']                 = 'Intent';
$_['text_pp_order_id']            = 'PayPal Order ID';
$_['text_status']                 = 'Status';
$_['text_capture_id']             = 'Capture / Auth ID';
$_['text_amount_authorised']      = 'Authorised';
$_['text_amount_captured']        = 'Captured';
$_['text_amount_refunded']        = 'Refunded';
$_['text_amount_remaining']       = 'Remaining';
$_['text_transactions']           = 'Transactions';
$_['text_confirm_void']           = 'Are you sure you want to void this authorization? This cannot be undone.';
$_['text_loading']                = 'Loading...';
$_['text_no_results']             = 'No transactions found.';
$_['text_already_refunded']       = 'already refunded';

// Order action panel — table columns
$_['column_type']                 = 'Type';
$_['column_capture_id']           = 'Capture / Auth ID';
$_['column_amount']               = 'Amount';
$_['column_currency']             = 'Currency';
$_['column_status']               = 'Status';
$_['column_note']                 = 'Note';
$_['column_created']              = 'Date';
$_['column_actions']              = 'Actions';

// Order action panel — capture form
$_['entry_capture_amount']        = 'Capture Amount';
$_['entry_capture_note']          = 'Note to Payer';

// Refund page
$_['entry_capture_id']            = 'Capture ID';
$_['entry_refund_full']           = 'Full Refund';
$_['entry_amount']                = 'Refund Amount';
$_['entry_note']                  = 'Note to Payer';

// Buttons
$_['button_capture']              = 'Capture';
$_['button_capture_full']         = 'Capture Full Amount';
$_['button_void']                 = 'Void Authorization';
$_['button_refund']               = 'Issue Refund';

// Errors
$_['error_permission']            = 'Warning: You do not have permission to modify <b>PayPal Express</b>!';
$_['error_client_id']             = 'Live Client ID is required!';
$_['error_client_secret']         = 'Live Client Secret is required!';
$_['error_sandbox_client_id']     = 'Sandbox Client ID is required!';
$_['error_sandbox_client_secret'] = 'Sandbox Client Secret is required!';
$_['error_connection']            = 'Could not connect to PayPal!';
$_['error_general']               = 'An error occurred. Please try again.';
$_['error_missing_data']          = 'Required data is missing!';
$_['error_missing_order']         = 'PayPal order record not found!';
$_['error_missing_transaction']   = 'Transaction not found!';
$_['error_capture_amt']           = 'Please enter a valid capture amount.';
$_['error_partial_amt']           = 'Please enter a refund amount for a partial refund.';
$_['error_positive_amt']          = 'Refund amount must be greater than zero.';
$_['error_timeout']               = 'The request timed out. Please try again.';

// Setup Reference panel
$_['text_setup_title']     = 'PayPal Express Setup Reference';
$_['text_setup_url_label'] = 'Webhook URL';
$_['text_setup_url_hint']  = 'Click to select';
$_['setup_sections'] = array(
	array(
		'title' => 'Step 1 &mdash; Create a REST app in PayPal',
		'intro' => 'Log in to <em>developer.paypal.com &rarr; Apps &amp; Credentials</em>. Create a <strong>Live</strong> app (and a <strong>Sandbox</strong> app for testing) and copy the Client ID and Secret into the matching fields on the <strong>API Credentials</strong> tab.',
	),
	array(
		'title' => 'Step 2 &mdash; Add a webhook to the app',
		'intro' => 'In the same app, add a webhook and paste the URL below. The URL must be reachable over HTTPS.',
		'url'   => true,
	),
	array(
		'title' => 'Step 3 &mdash; Subscribe to these 5 events',
		'rows'  => array(
			array('<code>PAYMENT.CAPTURE.COMPLETED</code>', 'The payment was captured. Moves the order to the Completed status.'),
			array('<code>PAYMENT.CAPTURE.PENDING</code>', 'The capture is waiting for PayPal. Moves the order to the Pending status.'),
			array('<code>PAYMENT.CAPTURE.DENIED</code>', 'The capture was denied. Moves the order to the Denied status.'),
			array('<code>PAYMENT.CAPTURE.REFUNDED</code>', 'A refund was issued. Moves the order to the Refunded status.'),
			array('<code>PAYMENT.AUTHORIZATION.VOIDED</code>', 'An authorization was voided. Moves the order to the Voided status.'),
		),
	),
	array(
		'title' => 'Step 4 &mdash; Copy the Webhook ID and save it above',
		'intro' => 'After saving the webhook, PayPal shows a <strong>Webhook ID</strong>. Paste it into the Live or Sandbox <strong>Webhook ID</strong> field on the <strong>API Credentials</strong> tab. Every incoming event is verified against this ID using PayPal\'s signature check; if the field is left empty the check is skipped, so always fill it in.',
	),
	array(
		'title' => 'Technical notes',
		'rows'  => array(
			array('Sandbox and Live', 'Sandbox Mode uses the Sandbox credentials and Webhook ID; otherwise the Live ones are used. Create a separate webhook in each PayPal environment.'),
			array('Transaction Mode', '<strong>Capture</strong> charges the customer immediately. <strong>Authorize</strong> places funds on hold, and you capture or void them from the order page.'),
			array('Order Status tab', 'Choose which NivoCart order status each webhook event above should set.'),
			array('Debug Logging', 'When enabled, API requests and responses are written to <code>pp_express.log</code> and shown on the Debug Log tab.'),
		),
	),
);
