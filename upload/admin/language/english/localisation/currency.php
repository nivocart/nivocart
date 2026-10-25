<?php
// Heading
$_['heading_title']        = 'Currency';

// Text
$_['text_success']         = 'Success: You have modified <b>Currencies</b> !';

// Column
$_['column_title']         = 'Currency Title';
$_['column_code']          = 'Code';
$_['column_status']        = 'Status';
$_['column_value']         = 'Value';
$_['column_date_modified'] = 'Last Update';
$_['column_action']        = 'Action';

// Entry
$_['entry_title']          = 'Currency Title:';
$_['entry_code']           = 'Code:<span class="help">Do not change if this is your default currency. Must be a valid <a onclick="window.open(\'http://www.xe.com/iso4217.php\');"><b>ISO code</b></a>.</span>';
$_['entry_value']          = 'Value:<span class="help">Set to 1.00000 if this is your default currency.</span>';
$_['entry_symbol_left']    = 'Symbol Left:';
$_['entry_symbol_right']   = 'Symbol Right:';
$_['entry_decimal_place']  = 'Decimal Places:';
$_['entry_status']         = 'Status:';

// Help
$_['help_currencies']      = 'Rates are fetched from Frankfurter (European Central Bank and other central banks), with FloatRates as the backup source. Without a cron job the rates are refreshed when an administrator opens the Dashboard; with the cron jobs below they update every day on their own. Automatic updates are switched on or off with the store setting for automatic currency updates.';

// Rate updates
$_['tab_rates']                   = 'Automatic Rate Updates';
$_['text_rates_last_success']     = 'Last successful rate update: <b>%s</b>';
$_['text_rates_none']             = 'Currency rates have not been updated automatically yet.';
$_['text_rates_overdue']          = 'Warning: no successful rate update in the last %s hours. Check the cron jobs and the details below.';
$_['text_rates_auto_off']         = 'Automatic updates are switched off in the store settings, so only the cron jobs below can update the rates.';
$_['text_rates_failed']           = 'Latest attempt failed:';
$_['text_rates_command_primary']  = 'Frankfurter cron job:';
$_['text_rates_command_backup']   = 'FloatRates cron job:';
$_['text_rates_time']             = 'Runs at %s UTC, a quiet time for the servers in Germany (%s CET / %s CEST). If your hosting provider asks for the schedule in another time zone, convert it.';
$_['text_rates_note']             = 'Add the cron job at your hosting provider (in your hosting control panel, usually under Cron Jobs). The backup job is optional: it runs one hour earlier so Frankfurter corrects its rates. With only one cron job, use the Frankfurter command but remove the word frankfurter at the end: it then tries Frankfurter first and FloatRates second. A rate that moves more than 25% from the stored one is rejected and logged. The script only runs from the command line, never from a browser. Use the PHP path shown by your hosting provider if it differs from /usr/bin/php. Log file: system/logs/currency_cron.log.';
$_['text_rates_last_run']         = 'Last successful update:';
$_['text_rates_never']            = 'No update has been recorded yet.';
$_['text_rates_success']          = 'Success';
$_['text_rates_error']            = 'Error';

// Error
$_['error_permission']     = 'Warning: You do not have permission to modify <b>Currencies</b> !';
$_['error_title']          = 'Currency Title must be between 3 and 32 characters!';
$_['error_code']           = 'Currency Code must contain 3 characters!';
$_['error_default']        = 'Warning: This currency cannot be deleted as it is currently assigned as the default store currency!';
$_['error_store']          = 'Warning: This currency cannot be deleted as it is currently assigned to %s stores!';
$_['error_order']          = 'Warning: This currency cannot be deleted as it is currently assigned to %s orders!';
