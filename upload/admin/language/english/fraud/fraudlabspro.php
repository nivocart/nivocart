<?php
// Heading
$_['heading_title']                 = 'FraudLabs Pro';

// Text
$_['text_fraud']                    = 'Fraud';
$_['text_success']                  = 'Success: You have modified <b>FraudLabs Pro</b> !';
$_['text_edit']                     = 'Edit';
$_['text_signup']                   = 'FraudLabsPro is a fraud detection service. If you don\'t have an API key you can <a onclick="window.open(\'https://www.fraudlabspro.com/plan?ref=670\');"><u>sign up here</u></a>.';
$_['text_rules']                    = 'Validation Rules';
$_['text_testing']                  = 'Testing';
$_['text_fraudlabspro_id']          = 'FraudLabs Pro ID<span class="help">Unique identifier to identify a transaction screened by FraudLabs Pro system.</span>';
$_['text_transaction_id']           = 'Transaction ID<span class="help">Click the link to view the detailed fraud analysis.</span>';
$_['text_score']                    = 'FraudLabsPro Score<span class="help">Risk score, 0 (low risk) - 100 (high risk).</span>';
$_['text_status']                   = 'FraudLabs Pro Status';
$_['text_ip_address']               = 'IP Address';
$_['text_ip_net_speed']             = 'IP Net Speed<span class="help">Connection speed.</span>';
$_['text_ip_isp_name']              = 'IP ISP Name<span class="help">Estimated ISP of the IP address.</span>';
$_['text_ip_usage_type']            = 'IP Usage Type<span class="help">Estimated usage type of the IP address. E.g. ISP, Commercial, Residential.</span>';
$_['text_ip_domain']                = 'IP Domain<span class="help">Estimated domain name of the IP address.</span>';
$_['text_ip_time_zone']             = 'IP Time Zone<span class="help">Estimated time zone of the IP address.</span>';
$_['text_ip_location']              = 'IP Location<span class="help">Estimated location of the IP address.</span>';
$_['text_ip_distance']              = 'IP Distance<span class="help">Distance between the IP address and the Billing Location.</span>';
$_['text_ip_latitude']              = 'IP Latitude<span class="help">Estimated latitude of the IP address.</span>';
$_['text_ip_longitude']             = 'IP Longitude<span class="help">Estimated longitude of the IP address.</span>';
$_['text_risk_country']             = 'High Risk Country<span class="help">Whether the IP address or billing address country is in the latest high risk list.</span>';
$_['text_free_email']               = 'Free Email<span class="help">Whether the email is from a free email provider.</span>';
$_['text_email_disposable']         = 'Disposable Email<span class="help">Whether the email address is from a disposable/temporary provider.</span>';
$_['text_phone_disposable']         = 'Disposable Phone<span class="help">Whether the phone number is a disposable/virtual number.</span>';
$_['text_phone_blacklist']          = 'Phone Blacklisted<span class="help">Whether the phone number is in the FraudLabs Pro blacklist.</span>';
$_['text_card_brand']               = 'Card Brand<span class="help">Credit card brand, e.g. VISA, MASTERCARD.</span>';
$_['text_card_type']                = 'Card Type<span class="help">Credit card type, e.g. DEBIT, CREDIT.</span>';
$_['text_ship_forward']             = 'Ship Forward<span class="help">Whether the shipping address is in the database of known mail drops.</span>';
$_['text_ship_export_controlled']   = 'Export Controlled Country<span class="help">Whether the shipping destination is an export-controlled country.</span>';
$_['text_using_proxy']              = 'Using Proxy<span class="help">Whether the IP address is from an Anonymous Proxy Server.</span>';
$_['text_bin_found']                = 'BIN Found<span class="help">Whether the BIN information matches the BIN list.</span>';
$_['text_email_blacklist']          = 'Email Blacklisted<span class="help">Whether the email address is in the blacklist database.</span>';
$_['text_credit_card_blacklist']    = 'Credit Card Blacklisted<span class="help">Whether the credit card is in the blacklist database.</span>';
$_['text_message']                  = 'Message<span class="help">FraudLabs Pro message description.</span>';
$_['text_credits']                  = 'Balance<span class="help">Remaining query balance in your account after this transaction.</span>';
$_['text_error']                    = 'Error:';
$_['text_flp_upgrade']              = '<a onclick="window.open(\'https://www.fraudlabspro.com/plan\');"><b>[ Upgrade ]</b></a>';
$_['text_flp_merchant_area']        = 'Please login to <a onclick="window.open(\'https://www.fraudlabspro.com/merchant/login\');">FraudLabs Pro Merchant Area</a> for more information about this order.';
$_['text_comment_approve']          = 'Approved using FraudLabs Pro.';
$_['text_comment_reject']           = 'Rejected using FraudLabs Pro.';
$_['text_comment_reject_blacklist'] = 'Rejected and blacklisted using FraudLabs Pro.';

// Entry
$_['entry_status']                  = 'Status';
$_['entry_key']                     = 'API Key';
$_['entry_score']                   = 'Risk Score';
$_['entry_order_status']            = 'Order Status<span class="help">Orders that have a score over your set risk score will be assigned this order status and will not be allowed to reach the complete status automatically.</span>';
$_['entry_review_status']           = 'Review Status<span class="help">Orders marked as review by FraudLabs Pro will be assigned this order status.</span>';
$_['entry_approve_status']          = 'Approve Status<span class="help">Orders marked as approve by FraudLabs Pro will be assigned this order status.</span>';
$_['entry_reject_status']           = 'Reject Status<span class="help">Orders marked as reject by FraudLabs Pro will be assigned this order status.</span>';
$_['entry_simulate_ip']             = 'Simulate IP<span class="help">Simulate the visitor IP address for testing. Leave blank for production use.</span>';

// Button
$_['button_approve']                = 'Approve';
$_['button_reject']                 = 'Reject';
$_['button_reject_blacklist']       = 'Reject &amp; Blacklist';

// Error
$_['error_permission']              = 'Warning: You do not have permission to modify <b>FraudLabs Pro</b> !';
$_['error_key']                     = 'A license key is required!';
