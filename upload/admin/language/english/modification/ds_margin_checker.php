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
$_['text_below_threshold']      = 'Below Target Price';
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
$_['column_profit']             = 'Profit';
$_['text_target_price']         = 'Target price: %s';
$_['column_status']             = 'Status';
$_['column_channel']            = 'Channel';
$_['column_action']             = 'Action';

// Filter labels
$_['filter_all']                = 'All Products';
$_['filter_below']              = 'Below Target Price';
$_['filter_negative']           = 'Negative Margin';

// Margin status badge labels
$_['status_good']               = 'Good';
$_['status_low']                = 'Low';
$_['status_negative']           = 'Negative';
$_['status_no_rate']            = 'No rate';

// Entry — settings form
$_['entry_threshold']           = 'Margin up to the Breakpoint (%)';
$_['entry_rounding']            = 'Sale Price Rounding Rule';
$_['help_rounding']             = 'Used by \'Set All Prices Automatically\'. The price is always rounded UP, so the margin never falls below the target. The rule is applied to the ex-VAT sale price, after the margins and the minimum profit.';
$_['text_rounding_none']        = 'No Rounding';
$_['text_rounding_five']        = 'Rounding by next 5 (7.22 becomes 7.25, 7.26 becomes 7.30)';
$_['text_rounding_nine']        = 'Rounding by next 9 (7.23 becomes 7.29)';
$_['help_threshold']            = 'The margin (ex-VAT sale price against the Total Cost) that \'Set All Prices Automatically\' aims for on the part of the Total Cost up to the Breakpoint. Products priced below their target price are flagged \'Low\'. Default: 20%.';
$_['entry_margin_high']         = 'Margin above the Breakpoint (%)';
$_['help_margin_high']          = 'The lower margin used on the part of the Total Cost above the Breakpoint, so high value products are not over-priced. Choose \'Same as above\' to switch the tiers off. The price rises smoothly with the cost: there is no jump at the Breakpoint.';
$_['entry_breakpoint']          = 'Breakpoint (Total Cost)';
$_['help_breakpoint']           = 'The Total Cost, in the store default currency, where the margin changes. Only used when a margin above the Breakpoint is chosen.';
$_['entry_min_profit']          = 'Minimum Profit';
$_['help_min_profit']           = 'The lowest profit (ex-VAT, in the store default currency) a product is priced for, so low value products still cover payment fees and shipping changes. Choose \'No minimum\' to switch it off.';
$_['entry_samples']             = 'Sample Prices';
$_['help_samples']              = 'The price, profit and margin that some Total Costs would get with the options chosen above (also before saving), including the rounding rule.';
$_['text_margin_same']          = 'Same as above (no tiers)';
$_['text_no_minimum']           = 'No minimum';

// Buttons
$_['button_save']               = 'Save Settings';
$_['button_cancel']             = 'Cancel';
$_['button_close']              = 'Close';
$_['button_sync_shipping']      = 'Sync Shipping from CJ';
$_['button_set_prices']         = 'Set All Prices Automatically';
$_['text_set_prices_done']      = 'Prices set. %s sale prices were changed. %s were already right. %s were skipped because they have no cost (no exchange rate).';
$_['text_set_prices_specials']  = ' %s product(s) have a Special at or above their new price, or below their cost. Specials were not changed: please check them.';
$_['text_preview_counts']       = 'Preview: %s prices will go up, %s will go down and %s stay the same. %s are skipped because they have no cost (no exchange rate).';
$_['text_preview_drops']        = 'Biggest drops:';
$_['text_preview_rises']        = 'Biggest rises:';
$_['text_preview_specials']     = '%s product(s) will have a Special at or above their new price, or below their cost. Specials are not changed by this tool: please check them afterwards.';
$_['text_preview_warning']      = 'Existing sale prices will be overwritten and this cannot be undone. Take a database backup first.';
$_['text_preview_nothing']      = 'All sale prices are already right: there is nothing to change.';
$_['text_rules_flat']           = 'Margin %s on the Total Cost.';
$_['text_rules_tiered']         = 'Margin %s on the Total Cost up to %s, and %s above it.';
$_['text_rules_floor']          = ' Minimum profit %s.';
$_['text_rules_rounding']       = ' Rounding rule: %s.';
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
