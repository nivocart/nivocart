<?php
/**
 * DS Margin Checker — English language file
 *
 * Intended location: admin/language/english/modification/ds_margin_checker.php
 *
 * @package NivoCart
 */

// Heading
$_['heading_title']             = 'DS Margin Checker';

// Text — general
$_['text_home']                 = 'Home';
$_['text_modification']         = 'Modifications';
$_['text_no_products']          = 'No products found matching the selected filter.';
$_['text_no_dropship']          = 'No active dropship products are configured.';
$_['text_settings_saved']       = 'Success: Settings have been saved.';
$_['text_price_updated']        = 'Price updated successfully.';
$_['text_rate_title']           = '1 %s = %s %s';
$_['text_no_rate_warning']      = 'Warning: %s product(s) have no exchange rate for the currency of their cost, so their cost and margin cannot be calculated. Add that currency under Localisation > Currencies.';

// Text — dashboard KPI tile labels
$_['text_total_products']       = 'Total Dropship Products';
$_['text_below_threshold']      = 'Below Threshold';
$_['text_negative_margin']      = 'Negative Margin';
$_['text_avg_margin']           = 'Avg Margin';
$_['text_worst_performers']     = 'Worst Performers (top 10)';

// Tabs / nav
$_['tab_dashboard']             = 'Dashboard';
$_['tab_products']              = 'Products';
$_['tab_settings']              = 'Settings';
$_['tab_about']                 = 'About';

// Column headings — product list
$_['column_product']            = 'Product';
$_['column_sku']                = 'SKU / Model';
$_['column_sale_price']         = 'Sale Price';
$_['column_cost_original']      = 'Original Cost';
$_['column_shipping_cost']      = 'Shipping Cost';
$_['column_total_cost']         = 'Total Cost';
$_['column_total_cost_default'] = 'Total Cost (%s)';
$_['column_margin_pct']         = 'Margin %';
$_['column_status']             = 'Status';
$_['column_channel']            = 'Channel';
$_['column_action']             = 'Action';

// Filter labels
$_['filter_all']                = 'All Products';
$_['filter_below']              = 'Below Threshold';
$_['filter_negative']           = 'Negative Margin';

// Margin status badge labels
$_['status_good']               = 'Good';
$_['status_low']                = 'Low';
$_['status_negative']           = 'Negative';
$_['status_no_rate']            = 'No rate';

// Entry — settings form
$_['entry_threshold']           = 'Minimum Margin Threshold (%)';
$_['help_threshold']            = 'Products with a margin (ex-VAT sale price against the cost) below this percentage are flagged as \'Low\'. Set to 0 to disable. Default: 20%.';

// Buttons
$_['button_save']               = 'Save Settings';
$_['button_cancel']             = 'Cancel';
$_['button_close']              = 'Close';
$_['button_sync_shipping']      = 'Sync Shipping from CJ';
$_['button_refresh']            = 'Refresh Page';
$_['text_sync_running']         = 'Syncing shipping costs from CJ, page %s of %s...';
$_['text_sync_progress']        = '%s% - page %s of %s';
$_['text_sync_retry']           = 'CJ is limiting requests, waiting 30 seconds before trying again...';
$_['text_sync_ranged']          = ' %s of the shop products got their cost from a CJ price range (the variants differ), and the higher end of the range was used.';
$_['text_sync_no_freight']     = ' %s shop products were skipped because CJ sent no shipping cost for their product (examples: %s).';
$_['text_sync_done']            = 'Shipping sync finished. %s shop products were given a shipping cost. %s were set to 0.00 because CJ reports a 0.00 freight for their product. %s UK-warehouse shop products were left unchanged. %s CJ products in your CJ list are not imported here. %s shop products belong to CJ products that are not in your CJ list%s.';
$_['button_update_price']       = 'Update Price';
$_['button_view_list']          = 'View All Products';
$_['button_settings']           = 'Settings';
$_['button_dashboard']          = 'Dashboard';

// About panel
$_['text_version']              = 'Version:';
$_['text_author']               = 'Author:';
$_['text_support']              = 'Support:';
$_['text_license']              = 'License:';
$_['mc_version']                = '1.0.0';
$_['mc_author']                 = 'NivoCart';
$_['mc_support']                = 'contact@nivocart.org';
$_['mc_license']                = 'GPLv3 (GNU General Public License)';

// Errors
$_['error_permission']          = 'Warning: You do not have permission to modify <b>DS Margin Checker</b>!';
$_['error_permission_product']  = 'Warning: You do not have permission to modify <b>Products</b>, so prices cannot be changed here!';
$_['error_method']              = 'Warning: Invalid request method.';
$_['error_server']              = 'Server error:';
$_['error_dependency']          = 'Warning: DS Margin Checker requires a Dropshipping connector (for example CJ Dropshipping) to be installed, and its database tables to exist, before this tool can be used.';
$_['error_product_id']          = 'Warning: Invalid or missing product ID.';
$_['error_sync_channel']         = 'Warning: No enabled CJ Dropshipping channel was found to sync shipping costs from.';
$_['error_shipping']           = 'Warning: Shipping cost must be 0.00 or greater.';
$_['error_price']               = 'Warning: Price must be 0.00 or greater.';
