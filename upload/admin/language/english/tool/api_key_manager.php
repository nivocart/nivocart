<?php
// Heading
$_['heading_title']             = 'API Key Manager';

// Text
$_['text_success']              = 'Success: You have modified <b>API Key Manager</b> !';
$_['text_success_setup']        = 'Success: The vault is set up and unlocked. Keys encrypted: %d.';
$_['text_success_unlock']       = 'Success: The vault is unlocked.';
$_['text_success_lock']         = 'Success: The vault is locked.';
$_['text_success_passphrase']   = 'Success: The passphrase has been changed.';
$_['text_success_migrate']      = 'Success: Keys encrypted: %d.';
$_['text_default']              = 'Default';
$_['text_api_info']             = 'The API Key Manager main purpose is to help you store all your important API Keys in one place. Key Codes are encrypted with a passphrase and are only readable while the vault is unlocked. The vault locks itself after 15 minutes without activity, and when you log out.';
$_['text_locked']               = 'The vault is locked. Unlock it to reveal, add or edit Key Codes.';
$_['text_legacy']               = 'Warning: %d Key Code(s) are not encrypted yet.';
$_['text_legacy_locked']        = 'Warning: Some Key Codes are not encrypted yet. Unlock the vault to encrypt them.';
$_['text_setup_info']           = 'Choose a passphrase to protect your Key Codes. It is never stored. If you forget it, the Key Codes cannot be recovered, so keep it in a password manager. Any Key Codes already saved will be encrypted when you continue.';
$_['text_unlock_info']          = 'Enter the vault passphrase to unlock the API Key Manager. This passphrase is separate from your Admin login.';
$_['text_change_info']          = 'Enter your current passphrase and choose a new one. Your Key Codes stay encrypted and are not changed.';

// Column
$_['column_name']               = 'Key Name';
$_['column_code']               = 'Key Code';
$_['column_status']             = 'Status';
$_['column_action']             = 'Action';

// Entry
$_['entry_name']                = 'Key Name:';
$_['entry_code']                = 'Key Code:';
$_['entry_status']              = 'Status:';
$_['entry_passphrase']          = 'Passphrase:';
$_['entry_passphrase_confirm']  = 'Confirm Passphrase:';
$_['entry_passphrase_current']  = 'Current Passphrase:';
$_['entry_passphrase_new']      = 'New Passphrase:';

// Help
$_['help_code']                 = 'Stored encrypted. Between 3 and 255 characters.';
$_['help_passphrase']           = 'At least 12 characters. A few random words make a strong passphrase.';

// Links
$_['link_google']               = 'Google APIs';
$_['link_facebook']             = 'Facebook APIs';
$_['link_twitter']              = 'X APIs';

// Button
$_['button_info']               = 'Information';
$_['button_setup']              = 'Set Up Vault';
$_['button_unlock']             = 'Unlock';
$_['button_lock']               = 'Lock';
$_['button_change']             = 'Change Passphrase';
$_['button_passphrase']         = 'Change Passphrase';
$_['button_migrate']            = 'Encrypt Now';
$_['button_reveal']             = 'Reveal';
$_['button_hide']               = 'Hide';

// Error
$_['error_permission']          = 'Warning: You do not have permission to modify <b>API Key Manager</b> !';
$_['error_name']                = 'Key Name must be between 3 and 64 characters!';
$_['error_code']                = 'Key Code must be between 3 and 255 characters!';
$_['error_status']              = 'Status must be Enabled or Disabled!';
$_['error_passphrase']          = 'Passphrase must be at least %d characters!';
$_['error_passphrase_empty']    = 'Please enter the passphrase!';
$_['error_passphrase_current']  = 'Please enter your current passphrase!';
$_['error_passphrase_wrong']    = 'Warning: The passphrase is not correct!';
$_['error_confirm']             = 'The passphrases do not match!';
$_['error_lockout']             = 'Warning: Too many wrong attempts. Try again in %d minute(s).';
$_['error_locked']              = 'Warning: The vault is locked. Unlock it first.';
$_['error_not_found']           = 'Warning: The API Key was not found!';
$_['error_vault']               = 'Warning: The vault could not complete the request. Please try again, and check the Error Log if it keeps happening.';
$_['error_table']               = 'Warning: The database is not ready for the vault. Run the upgrade first (it adds the <b>nc_api_key_vault</b> table and enlarges the <b>nc_api_key.code</b> column).';
