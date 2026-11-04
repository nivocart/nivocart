<?php
// Heading
$_['heading_title']          = 'Gift Voucher';

// Text
$_['text_send']              = 'Send';
$_['text_resend']            = 'Resend';
$_['text_success']           = 'Success: You have modified <b>Vouchers</b> !';
$_['text_sent']              = 'Success: Gift Voucher email has been sent!';
$_['text_wait']              = 'Please Wait!';
$_['text_confirm_send']      = 'Email this Gift Voucher?';
$_['text_confirm_send_text'] = 'The recipient will receive the voucher by email now. The first send starts the validity period; sending again does not change it.';
$_['text_not_sent']          = 'Not sent';
$_['text_never']             = 'Never';

// Occasions (voucher designs)
$_['text_design_general']         = 'Just because';
$_['text_design_birthday']        = 'Birthday';
$_['text_design_christmas']       = 'Christmas';
$_['text_design_thank_you']       = 'Thank you';
$_['text_design_congratulations'] = 'Congratulations';

// States
$_['text_state_active']      = 'Active';
$_['text_state_pending']     = 'Awaiting order';
$_['text_state_used']        = 'Used';
$_['text_state_expired']     = 'Expired';
$_['text_state_disabled']    = 'Disabled';

// Column
$_['column_name']            = 'Voucher Name';
$_['column_code']            = 'Code';
$_['column_from']            = 'From';
$_['column_to']              = 'To';
$_['column_design']          = 'Occasion';
$_['column_amount']          = 'Amount';
$_['column_balance']         = 'Balance';
$_['column_state']           = 'Status';
$_['column_sent']            = 'Sent';
$_['column_expire']          = 'Expires';
$_['column_order_id']        = 'Order ID';
$_['column_customer']        = 'Customer';
$_['column_date_added']      = 'Date Added';
$_['column_action']          = 'Action';

// Entry
$_['entry_code']             = 'Code:<span class="help">Leave empty on a new voucher to generate a secure code. Letters and numbers only, 3 to 20 characters.</span>';
$_['entry_from_name']        = 'From Name:';
$_['entry_from_email']       = 'From Email:';
$_['entry_to_name']          = 'To Name:';
$_['entry_to_email']         = 'To Email:';
$_['entry_design']           = 'Occasion:';
$_['entry_message']          = 'Message:';
$_['entry_amount']           = 'Amount:';
$_['entry_expire']           = 'Expiry Date:<span class="help">Leave empty to use the expiry period in Settings, counted from the first time the voucher is emailed.</span>';
$_['entry_balance']          = 'Balance:';
$_['entry_state']            = 'Status:';
$_['entry_sent']             = 'Emailed:';
$_['entry_status']           = 'Enabled:';

// Button
$_['button_generate']        = 'Generate';

// Error
$_['error_permission']       = 'Warning: You do not have permission to modify <b>Vouchers</b> !';
$_['error_warning']          = 'Warning: Please check the form carefully for errors!';
$_['error_exists']           = 'Warning: Voucher code is already in use!';
$_['error_code']             = 'Code must be 3 to 20 letters or numbers!';
$_['error_to_name']          = 'Recipient\'s Name must be between 1 and 64 characters!';
$_['error_from_name']        = 'Your Name must be between 1 and 64 characters!';
$_['error_email']            = 'Email Address does not appear to be valid!';
$_['error_design']           = 'Please select a valid occasion!';
$_['error_message']          = 'Message must contain between 3 and 128 characters!';
$_['error_amount']           = 'Amount must be a number greater than or equal to 1.00!';
$_['error_expire']           = 'Expiry date must be a valid date (YYYY-MM-DD)!';
$_['error_order']            = 'Warning: This voucher cannot be deleted as it is part of an <a href="%s">order</a>!';
$_['error_send_state']       = 'Warning: This voucher cannot be emailed because its status is "%s"!';
$_['error_send']             = 'Warning: The email could not be sent. Please check the mail settings and try again!';
