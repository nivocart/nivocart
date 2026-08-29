<?php
// Heading
$_['heading_title']              = 'Data Retention';

// Text
$_['text_info']                  = 'The tasks below run automatically via the server cron job. You can also trigger them manually using the button below. All results are recorded in the log.';
$_['text_last_run']              = 'Last Run Status';
$_['text_task']                  = 'Task';
$_['text_rows']                  = 'Rows Affected';
$_['text_status']                = 'Status';
$_['text_date']                  = 'Date';
$_['text_never']                 = 'Never run';
$_['text_recent_log']            = 'Recent Activity Log';
$_['text_no_log']                = 'No log entries yet.';
$_['text_confirm']               = 'Run all data-retention tasks now? This cannot be undone.';
$_['text_cron_cmd']              = 'Recommended crontab entry (runs nightly at 02:00 — adjust path to your installation):';
$_['text_cron_example']          = '0 2 * * * php /path/to/upload/cron.php >> /var/log/nivocart_cron.log 2>&1';

// Task labels
$_['text_task_ip_columns']       = 'Anonymise registration IPs older than 90 days (nc_customer.ip)';
$_['text_task_ip_log']           = 'Delete login IP log rows older than 90 days (nc_customer_ip)';
$_['text_task_online_sessions']  = 'Delete stale online-session rows older than 2 hours (nc_customer_online)';
$_['text_task_deleted_accounts'] = 'Hard-delete soft-deleted accounts older than 2 years (nc_customer_deleted)';

// Button
$_['button_run_now']             = 'Run All Tasks Now';

// Success
$_['text_success']               = 'Success: Data retention tasks completed. %s row(s) affected in total.';

// Error
$_['error_permission']           = 'Warning: You do not have permission to modify <b>Data Retention</b>!';
