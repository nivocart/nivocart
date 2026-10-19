<?php
// Heading
$_['heading_title']          = 'Competitor Prices Report';

// Tab
$_['tab_scan_report']          = 'Scan Report';
$_['tab_settings']             = 'Settings';

// Text
$_['text_info']              = 'Lists the products that another seller offers for less. Each product is matched to eBay listings by its exact EAN, then the lowest new, fixed-price listing from a seller in the chosen country is compared with your price, VAT included. Products that nobody undercuts are not listed, so the list shrinks as you adjust your prices.';
$_['text_ean_required']      = '<b>An EAN is required.</b> Only products with a valid EAN (barcode) in the Data tab of the product form can be compared. Products without an EAN, or with an invalid one, are skipped.';
$_['text_special_note']      = 'Your price is the active Special price when there is one (shown in red), otherwise the normal price. Competitor prices are the price found plus its postage when this is switched on in the settings.';
$_['text_install_message']   = 'The report needs its database table before it can store competitor prices.';
$_['text_database_installed'] = 'Success: The Competitor Prices table has been created.';
$_['text_settings_saved']    = 'Success: The settings have been saved.';
$_['text_cache_cleared']     = 'Success: The stored competitor prices for this country have been cleared.';
$_['text_confirm_clear']     = 'This deletes the stored competitor prices for this country, so the next scan checks every product again. Continue?';
$_['text_no_results']        = 'No competitor is cheaper than you for the products scanned so far. Run a scan to check more products.';
$_['text_never_scanned']     = 'Nothing has been scanned for this country yet.';
$_['text_showing']           = 'Showing %s of %s products that a competitor sells for less.';
$_['text_stats']             = 'Scan status';
$_['text_stat_enabled']      = 'Enabled products:';
$_['text_stat_no_ean']       = 'Skipped, no EAN:';
$_['text_stat_invalid']      = 'Skipped, invalid EAN:';
$_['text_stat_checked']      = 'Products checked:';
$_['text_stat_cheaper']      = 'Competitor cheaper:';
$_['text_yes']               = 'Yes';
$_['text_no']                = 'No';
$_['text_unknown']           = 'Unknown';
$_['text_postage_added']     = '+ %s postage';
$_['text_settings']          = 'Settings';
$_['text_scan']              = 'Scan';
$_['text_scan_help']         = 'Looks up the products that have not been checked yet, or not within the recheck period. It stops once enough products are found that a competitor sells for less (the results limit), so each scan is quick and gets shorter as prices are adjusted.';

// Scan messages (used by the page script)
$_['text_scanning']          = 'Scanning...';
$_['text_scan_progress']     = '%s products checked, %s of %s competitor-cheaper products found.';
$_['text_scan_complete']     = 'Scan complete: %s products checked, %s of them cheaper elsewhere.';
$_['text_scan_limit']        = 'Results limit reached after %s products checked. Adjust those prices, then scan again to continue.';
$_['text_scan_nothing']      = 'Nothing to scan: every product with an EAN was checked recently.';
$_['text_scan_errors']       = '%s lookups failed (see the error log).';
$_['text_test_ok']           = 'Connection works: eBay accepted the credentials.';
$_['error_ajax']             = 'Error: the request failed. Please try again.';

// Countries (eBay marketplace used for the lookup)
$_['text_country_gb']        = 'United Kingdom';
$_['text_country_ie']        = 'Ireland';
$_['text_country_de']        = 'Germany';
$_['text_country_fr']        = 'France';
$_['text_country_it']        = 'Italy';
$_['text_country_es']        = 'Spain';
$_['text_country_nl']        = 'Netherlands';
$_['text_country_us']        = 'United States';
$_['text_country_ca']        = 'Canada';
$_['text_country_au']        = 'Australia';

// Entry
$_['entry_country']          = 'Country:';
$_['entry_limit']            = 'Results limit:';
$_['entry_client_id']        = 'eBay Client ID:';
$_['entry_client_secret']    = 'eBay Client Secret:';
$_['entry_default_country']  = 'Default country:';
$_['entry_recheck_days']     = 'Recheck after (days):';
$_['entry_results_limit']    = 'Default results limit:';
$_['entry_include_postage']  = 'Add competitor postage:';
$_['entry_ignore_sellers']   = 'Ignore these sellers:';
$_['text_secret_saved']      = 'A secret is saved. Leave empty to keep it.';

// Help
$_['help_client_id']         = 'From the free eBay Developers Program (developer.ebay.com), application keys, Production: the "App ID (Client ID)".';
$_['help_client_secret']     = 'The "Cert ID (Client Secret)" of the same Production keyset.';
$_['help_recheck_days']      = 'A product checked more recently than this is not looked up again by a scan (1 to 90).';
$_['help_results_limit']     = 'Maximum rows shown, and the number of competitor-cheaper products after which a scan stops (1 to 1000).';
$_['help_include_postage']   = 'Compare your price with the competitor price plus their postage, when eBay states it.';
$_['help_ignore_sellers']    = 'eBay user names, separated by commas, left out of the comparison (for example your own eBay shop).';

// Column
$_['column_product']         = 'Product';
$_['column_ean']             = 'EAN';
$_['column_price']           = 'Your Price';
$_['column_competitor']      = 'Lowest Found';
$_['column_seller']          = 'Seller';
$_['column_gap']             = 'Difference';
$_['column_floor']           = 'Cost Floor';
$_['column_match']           = 'Can Match';
$_['column_checked']         = 'Checked';

// Button
$_['button_show']            = 'Show';
$_['button_save']            = 'Save';
$_['button_scan']            = 'Scan Prices';
$_['button_test']            = 'Test Connection';
$_['button_install']         = 'Install Database';
$_['button_clear_cache']     = 'Clear Stored Prices';

// Error
$_['error_permission']       = 'Warning: You do not have permission to modify the Competitor Prices report!';
$_['error_not_installed']    = 'Error: Install the database table first.';
$_['error_country']          = 'Error: Unsupported country.';
$_['error_credentials']      = 'Error: Enter and save the eBay Client ID and Client Secret in the settings first.';
