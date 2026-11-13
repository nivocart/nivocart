<?php
// Heading
$_['heading_title']              = 'Trustpilot';

// Navigation
$_['text_home']                  = 'Home';
$_['text_modification']          = 'Modifications';
$_['text_install_message']       = 'The Trustpilot database table is missing. Click Install to set it up.';
$_['text_install_btn']           = 'Install Trustpilot';

// Tabs
$_['tab_settings']               = 'Settings';
$_['tab_invitations']            = 'Invitations';

// Text
$_['text_success']               = 'Success: Trustpilot settings have been saved!';
$_['text_enabled']               = 'Enabled';
$_['text_disabled']              = 'Disabled';
$_['text_yes']                   = 'Yes';
$_['text_no']                    = 'No';
$_['text_info']                  = 'When an order reaches the Complete status, NivoCart emails your Trustpilot Automatic Feedback Service (AFS) address with the customer\'s name, email address and order number. Trustpilot then sends the review invitation to the customer, using the delay, templates and reminders set in your Trustpilot account. This works on every Trustpilot plan and needs no API key.';
$_['text_trigger_status']        = '%s (the invitation is sent once per order)';
$_['text_trigger_not_set']       = 'Not set';
$_['text_month_count']           = 'Invitation requests sent this month: <b>%s</b> of <b>%s</b> (estimate, Trustpilot keeps the real count).';
$_['text_limit_reached']         = 'The monthly limit of your Trustpilot plan has been reached. Trustpilot will not send more invitations until next month.';
$_['text_no_results']            = 'No invitation requests yet.';
$_['text_status_sent']           = 'Sent';
$_['text_status_failed']         = 'Failed';
$_['text_status_skipped']        = 'Skipped';
$_['text_test']                  = 'Test';
$_['text_test_success']          = 'Test invitation request sent for %s. Check Invitation history in Trustpilot Business.';
$_['text_confirm_test']          = 'This sends a real invitation request to Trustpilot, which will email the address you entered and count towards your monthly limit. Continue?';

// Columns
$_['column_date']                = 'Date';
$_['column_order']               = 'Order';
$_['column_customer']            = 'Customer';
$_['column_status']              = 'Status';
$_['column_details']             = 'Details';

// Entries
$_['entry_status']               = 'Send Invitations:';
$_['entry_afs_email']            = 'Trustpilot AFS Email:';
$_['entry_sender_email']         = 'Send From:';
$_['entry_monthly_limit']        = 'Monthly Invitations:';
$_['entry_trigger_status']       = 'Trigger Order Status:';
$_['entry_test']                 = 'Test Invitation:';

// Help
$_['help_status']                = 'Send a review invitation request to Trustpilot when an order is completed.';
$_['help_afs_email']             = 'Your unique Automatic Feedback Service address. In Trustpilot Business open Invitation methods and copy the address shown under Automatic Invitations (AFS).';
$_['help_sender_email']          = 'The address the request is sent from. Leave empty to use the store email address.';
$_['help_monthly_limit']         = 'Invitations included in your Trustpilot plan (Free plan: 50). Only used for the counter on the Invitations tab, Trustpilot enforces the real limit.';
$_['help_trigger_status']        = 'This is the Complete Order Status of the store settings. Change it there if needed.';
$_['help_test']                  = 'Sends a real invitation request for this email address so you can check the result in Trustpilot (Invitation history). Save the settings first.';

// Buttons
$_['button_test']                = 'Send Test Invitation';
$_['button_test_title']          = 'Send Test Invitation';

// Errors
$_['error_permission']           = 'Warning: You do not have permission to modify <b>Trustpilot</b>!';
$_['error_warning']              = 'Warning: Please check the form carefully for errors!';
$_['error_afs_email']            = 'A valid Trustpilot AFS email address is required to enable invitations!';
$_['error_sender_email']         = 'The Send From address is not a valid email address!';
$_['error_monthly_limit']        = 'The monthly invitations must be a whole number of 1 or more!';
$_['error_email']                = 'Please enter a valid email address for the test invitation!';
$_['error_not_enabled']          = 'Save the settings with Send Invitations enabled and a valid AFS email address before sending a test.';
