<?php
// Heading
$_['heading_title']             = 'Klarna';

// Link
$_['text_klarna']               = '<a onclick="window.open(\'https://klarna.com/sell-with-klarna\');"><img src="view/image/payment/klarna.png" alt="Klarna" title="Klarna" style="border:1px solid #EEEEEE;" /></a>';

// Text
$_['text_payment']              = 'Payment';
$_['text_success']              = 'Success: You have modified <b>Klarna</b> account details !';
$_['text_live']                 = 'Live';
$_['text_playground']           = 'Playground';
$_['text_region_eu']            = 'Europe / UK';
$_['text_region_na']            = 'Canada / USA';
$_['text_region_oc']            = 'Australia / NZL';

// Text Countries
$_['text_country_at']           = 'Austria';
$_['text_country_be']           = 'Belgium';
$_['text_country_de']           = 'Germany';
$_['text_country_dk']           = 'Denmark';
$_['text_country_fi']           = 'Finland';
$_['text_country_fr']           = 'France';
$_['text_country_gr']           = 'Greece';
$_['text_country_ie']           = 'Ireland';
$_['text_country_it']           = 'Italy';
$_['text_country_nl']           = 'The Netherland';
$_['text_country_no']           = 'Norway';
$_['text_country_pl']           = 'Poland';
$_['text_country_pt']           = 'Portugal';
$_['text_country_es']           = 'Spain';
$_['text_country_se']           = 'Sweden';
$_['text_country_ch']           = 'Switzerland';
$_['text_country_gb']           = 'Great Britain';
$_['text_country_us']           = 'USA';
$_['text_country_ca']           = 'Canada';
$_['text_country_au']           = 'Australia';
$_['text_country_nz']           = 'New Zealand';

// Entry
$_['entry_username']            = 'Username';
$_['entry_password']            = 'Password';
$_['entry_server']              = 'Server';
$_['entry_pending_status']      = 'Pending Status';
$_['entry_accepted_status']     = 'Accepted Status';
$_['entry_geo_zone']            = 'Geo Zone';
$_['entry_sort_order']          = 'Sort Order';
$_['entry_status']              = 'Status';
$_['entry_country']             = 'Country';

// Help
$_['help_username']             = 'Please enter your Klarna Username.';
$_['help_password']             = 'Please enter your Klarna Password.';
$_['help_server'] 				= 'Select "Live" for production.';
$_['help_pending_status']       = 'Default status is "Pending".';
$_['help_accepted_status']      = 'Default status is "Processed".';

// Error
$_['error_permission']          = 'Warning: You do not have permission to modify payment <b>Klarna</b> !';
$_['error_credentials_missing'] = 'Warning: Some credentials are missing for region %s !';
$_['error_credentials_invalid'] = 'Warning: Invalid credentials for region %s : Username: %s, Password: %s, Server: %s !';

// Setup Reference panel
$_['text_setup_title']     = 'Klarna Setup Reference';
$_['text_setup_url_label'] = 'Push endpoint';
$_['text_setup_url_hint']  = 'Click to select';
$_['setup_sections'] = [
	[
		'title' => 'Step 1 &mdash; Get your API credentials',
		'intro' => 'Log in to the Klarna Merchant Portal and create API credentials (Username and Password) for each region you sell in. Enter them on the matching region tab: Europe / UK, Canada / USA or Australia / NZL.',
	],
	[
		'title' => 'Step 2 &mdash; Choose the server',
		'intro' => 'Select <strong>Playground</strong> while testing with Klarna test credentials and <strong>Live</strong> only once Klarna has approved your account. Playground and Live credentials are different and cannot be swapped.',
	],
	[
		'title' => 'Step 3 &mdash; Push notifications (automatic)',
		'intro' => 'The push endpoint below is registered with Klarna automatically for every order, so nothing needs to be added in the Klarna portal. Klarna calls it when the fraud check on a pending order is resolved.',
		'url'   => true,
		'rows'  => [
			['Accepted', 'The order is moved to the region\'s <em>Accepted Status</em>.'],
			['Rejected', 'The order is moved to the store\'s <em>Failed</em> order status.'],
			['Pending', 'No change. Klarna pushes again when the decision is made.'],
			['Security', 'Klarna\'s push carries only the order ID. The handler fetches the order from Klarna with your credentials and acts only on the status returned.'],
		],
	],
	[
		'title' => 'Technical notes',
		'rows'  => [
			['.htaccess rule', 'Add this rule to your root <code>.htaccess</code> so the push endpoint is not rewritten by NivoCart\'s SEO router:<br /><code>RewriteRule ^catalog/webhooks/ - [L]</code>'],
			['Pending and Accepted status', '<em>Pending Status</em> is given when Klarna returns a pending fraud decision at checkout. <em>Accepted Status</em> is applied once Klarna approves the order.'],
			['Log file', 'Push activity is written to <code>system/logs/klarna_webhook.log</code>.'],
		],
	],
];
