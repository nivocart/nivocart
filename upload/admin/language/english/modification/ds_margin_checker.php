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
$_['column_cost_ex_vat']        = 'Cost (ex VAT)';
$_['column_cost_inc_vat']       = 'Cost (inc VAT)';
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

// Entry — settings form
$_['entry_threshold']           = 'Minimum Margin Threshold (%)';
$_['help_threshold']            = 'Products with a gross margin below this percentage are flagged as \'Low\'. Set to 0 to disable. Default: 20%.';

// Buttons
$_['button_save']               = 'Save Settings';
$_['button_cancel']             = 'Cancel';
$_['button_close']              = 'Close';
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
$_['error_price']               = 'Warning: Price must be 0.00 or greater.';
