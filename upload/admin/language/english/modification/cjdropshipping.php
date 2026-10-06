<?php
/**
 * CJDropshipping — English language file
 *
 * Intended location: admin/language/english/modification/cjdropshipping.php
 *
 * @package NivoCart
 */

// Heading
$_['heading_title']                  = 'CJDropshipping';

// Header
$_['header_set_option']              = 'Options';

// Text — general
$_['text_modification']              = 'Modifications';
$_['text_home']                      = 'Home';
$_['text_loading']                   = 'Loading...';
$_['text_confirm_delete']            = 'Are you sure you want to delete this channel and all its associated data?';
$_['text_confirm_uninstall']         = 'Are you sure you want to uninstall CJDropshipping? All CJ channel data will be permanently removed.';
$_['text_none']                      = '--- None ---';

// Text — install
$_['text_install_message']           = 'Missing database tables. Please install the CJDropshipping database tables before continuing.';
$_['text_database_installed']        = 'Success: CJDropshipping database tables have been installed.';
$_['text_upgrade']                   = 'Install Database';

// Text — success messages
$_['text_success']                   = 'Success: CJDropshipping settings have been saved.';
$_['text_channel_added']             = 'Success: Channel has been added.';
$_['text_channel_updated']           = 'Success: Channel has been updated.';
$_['text_channel_deleted']           = 'Success: Channel and all associated data have been deleted.';
$_['text_webhooks_registered']       = 'Success: Webhook URLs have been registered with CJDropshipping.';

// Text — sync results
$_['text_import_complete']           = 'Import complete: %s variants imported, %s products skipped.';
$_['text_stock_sync_complete']       = 'Stock sync complete: %s variants updated.';
$_['text_price_sync_complete']       = 'Price sync complete: %s.';
$_['text_tracking_sync_complete']    = 'Tracking sync complete: %s orders updated.';
$_['text_dispatch_complete']         = 'Dispatch complete: %s items sent to CJDropshipping.';
$_['text_sync_errors']               = 'Completed with errors: %s';
$_['text_no_channels']               = 'No channels configured yet. Click Add Channel to enter your CJ API key.';

// Text — channel form
$_['text_add_channel']               = 'Add Channel';
$_['text_edit_channel']              = 'Edit Channel';
$_['text_channel_active']            = 'Active';
$_['text_channel_inactive']          = 'Inactive';

// Text — dispatch status labels
$_['text_status_pending']            = 'Pending';
$_['text_status_dispatched']         = 'Dispatched';
$_['text_status_error']              = 'Error';

// Text — currency note
$_['text_cj_currency_note']          = 'CJ prices are in USD. Convert at display time using your store currency rate.';

// Text — dashboard
$_['text_note']                      = 'Note';
$_['text_cj_payment_note']           = '<strong>Important:</strong> Orders dispatched to CJ Dropshipping must be <strong>paid for</strong> from your CJ Dropshipping Account Dashboard before they can be processed and shipped.';
$_['text_orders_select']             = 'Select a channel and click Refresh.';
$_['text_no_orders']                 = 'No orders found for this channel.';
$_['text_importing']                 = 'Importing page %s...';
$_['text_sending']                   = 'Sending...';
$_['text_order_result']              = 'Order #%s: %s';
$_['text_track_17track']             = 'Track on 17track';
$_['text_x_of_y']                    = '%s of %s';
$_['text_prices_updated']            = '%s variants updated (%s PIDs)';
$_['text_progress_variants']         = '%s%% — %s of %s variants';
$_['text_progress_pids']             = '%s%% — %s of %s PIDs';
$_['text_page_variants']             = 'Page %s of %s (%s variants)';
$_['text_page_orders']               = 'Page %s of %s (%s orders)';
$_['text_prev']                      = '&laquo; Prev';
$_['text_next']                      = 'Next &raquo;';
$_['text_test_auth']                 = 'Auth';
$_['text_test_api']                  = 'Product API';
$_['text_test_token']                = 'Token';
$_['text_test_expires']              = 'Expires';

// Text — import warehouse countries
$_['text_country_cn']                = 'CN (China)';
$_['text_country_gb']                = 'GB (United Kingdom)';
$_['text_country_us']                = 'US (United States)';
$_['text_country_de']                = 'DE (Germany)';
$_['text_country_fr']                = 'FR (France)';

// Placeholders and titles
$_['placeholder_category']           = 'Type category name...';
$_['placeholder_keyword']            = 'e.g. dog harness';
$_['title_generate_secret']          = 'Generate random secret';

// Tabs
$_['tab_dashboard']                  = 'Dashboard';
$_['tab_channels']                   = 'Channels';
$_['tab_products']                   = 'Products';
$_['tab_orders']                     = 'Orders';
$_['tab_about']                      = 'About';

// Column headings — channel list
$_['column_channel_name']            = 'Channel Name';
$_['column_status']                  = 'Status';
$_['column_action']                  = 'Action';

// Column headings — product/variant list
$_['column_product_name']            = 'Product Name';
$_['column_sku']                     = 'CJ SKU';
$_['column_variant_key']             = 'Variant';
$_['column_stock']                   = 'Stock (GB)';
$_['column_supplier_cost']           = 'Cost (USD)';
$_['column_rrp']                     = 'RRP (USD)';
$_['column_currency']                = 'Currency';
$_['column_last_stock_sync']         = 'Last Stock Sync';
$_['column_last_price_sync']         = 'Last Price Sync';

// Column headings — order list
$_['column_order_id']                = 'Order ID';
$_['column_cj_order_ref']            = 'CJ Order Ref';
$_['column_dispatch_status']         = 'Dispatch Status';
$_['column_tracking_number']         = 'Tracking Number';
$_['column_tracking_carrier']        = 'Carrier';
$_['column_dispatched_at']           = 'Dispatched At';
$_['column_customer']                = 'Customer';
$_['column_date']                    = 'Date';
$_['column_total']                   = 'Total';
$_['column_order_status']            = 'Order Status';
$_['column_cjd_status']              = 'CJD Status';
$_['column_email_tracking']          = 'Email Tracking';
$_['column_email_sent']              = 'Email Sent';

// Entry — channel form fields
$_['entry_channel_name']             = 'Channel Name';
$_['entry_api_key']                  = 'CJ API Key';
$_['entry_webhook_secret']           = 'Webhook Secret';
$_['entry_location_name']            = 'Location Name';
$_['entry_shipping_map']             = 'Shipping Map (JSON)';
$_['entry_status']                   = 'Status';

// Entry — import form fields
$_['entry_import_channel']           = 'Channel';
$_['entry_import_category']          = 'Assign to Category';
$_['entry_import_keyword']           = 'Keyword / Product Name';
$_['entry_import_country']           = 'Warehouse Country';

// Help text
$_['help_api_key']                   = 'Found in your CJ developer account: Apps → Open API → API Key. Required for all API calls.';
$_['help_webhook_secret']            = 'Shared secret used to verify inbound CJ webhook requests (HMAC-SHA256). Set the same value in your CJ Webhook Settings.';
$_['help_shipping_map']              = 'JSON mapping of NivoCart shipping_code to CJ logistics service name. Example: {"flat_rate": "CJPacket Ordinary"}. Get service names from CJ logistics calculator.';
$_['help_import_category']           = 'All imported products will be assigned to this NivoCart category ID. Re-assign individually in the product list afterwards.';
$_['help_import_keyword']            = 'Filter CJ products by name or SKU. Leave blank to import all products in the selected category. Example: "dog harness", "cat collar".';

// Buttons
$_['button_add_channel']             = 'Add Channel';
$_['button_edit']                    = 'Edit';
$_['button_delete']                  = 'Delete';
$_['button_save']                    = 'Save';
$_['button_cancel']                  = 'Cancel';
$_['button_close']                   = 'Close';
$_['button_refresh']                 = 'Refresh';
$_['button_import']                  = 'Import Products';
$_['button_sync_stock']              = 'Sync Stock';
$_['button_sync_prices']             = 'Sync Prices';
$_['button_sync_tracking']           = 'Sync Tracking';
$_['button_dispatch']                = 'Dispatch to CJ';
$_['button_register_webhooks']       = 'Register Webhooks';
$_['button_test_connection']         = 'Test Connection';
$_['button_email_tracking']          = 'Email Tracking';
$_['button_cj_dashboard']            = 'Go to CJ Dropshipping Dashboard &rsaquo;';
$_['button_purge_confirm']           = 'Yes, Purge';

// About
$_['text_cj_version']                = 'Version:';
$_['text_cj_author']                 = 'Author:';
$_['text_cj_support']                = 'Support:';
$_['text_cj_license']                = 'License:';
$_['text_cj_tables']                 = 'Database Tables:';
$_['cj_version']                     = '1.0.0';
$_['cj_author']                      = 'NivoCart';
$_['cj_support']                     = 'contact@nivocart.org';
$_['cj_license']                     = 'GPLv3 (GNU General Public License)';

// Products Maintenance
$_['text_products_maintenance']      = 'Products Maintenance';

// Purge Stale Products
$_['button_purge_stale']             = 'Purge Stale Products';
$_['text_purge_confirm']             = 'This will permanently delete all dropship records for products that no longer exist in the catalogue. Continue?';
$_['text_purge_found']               = '%s stale variant records found.';
$_['text_purge_complete']            = 'Purge complete: %s stale variant records deleted.';
$_['text_purge_none']                = 'No stale records found &mdash; all clean!';

$_['button_purge_duplicates']        = 'Purge Duplicate Products';
$_['text_purge_dup_confirm']         = 'This will permanently delete all duplicate products, keeping only the original (lowest ID) for each CJ product. Continue?';
$_['text_purge_dup_found']           = '%s duplicate product(s) found.';
$_['text_purge_dup_complete']        = 'Purge complete: %s duplicate product(s) deleted.';
$_['text_purge_dup_none']            = 'No duplicate products found &mdash; all clean!';
$_['text_purge_dup_sample']          = 'First %s flagged (to be deleted):';

// Errors
$_['error_permission']               = 'Warning: You do not have permission to modify <b>CJDropshipping</b>!';
$_['error_database']                 = 'Database tables not found!';
$_['error_channel_id']               = 'Warning: Invalid or missing channel ID.';
$_['error_order_id']                 = 'Warning: Invalid or missing order ID.';
$_['error_channel_name']             = 'Warning: Channel name is required.';
$_['error_api_key']                  = 'Warning: CJ API key is required.';
$_['error_shipping_map_json']        = 'Warning: Shipping map must be valid JSON.';
$_['error_import_failed']            = 'Error: Product import failed. Check the error log for details.';
$_['error_sync_failed']              = 'Error: Sync failed. Check the error log for details.';
$_['error_dispatch_failed']          = 'Error: Order dispatch failed. Check the error log for details.';
$_['error_webhooks_failed']          = 'Error: Webhook registration failed. Check the error log for details.';
$_['error_connection']               = 'Error: Could not connect to CJDropshipping. Check your API key.';
$_['error_no_tracking']              = 'Error: No tracking number found for this order. Please sync tracking first.';
$_['error_ajax']                     = 'Error: The request failed. Check the error log for details.';

// Tracking email
$_['text_tracking_email_sent']       = 'Tracking notification sent successfully.';
$_['email_tracking_subject']         = 'Your %1$s order #%2$s has been dispatched';
$_['email_tracking_body']            = "Dear %1\$s,\n\nGreat news! Your order #%2\$d has been dispatched and is on its way to you.\n\nTracking Number: %3\$s\nCarrier: %4\$s\n\nYou can track your parcel at:\nhttps://t.17track.net/en#nums=%3\$s\n\nIf you have any questions, please do not hesitate to contact us.\n\nThank you for shopping with %5\$s.\n\nKind regards,\n%5\$s Team";
