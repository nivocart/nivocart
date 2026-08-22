<?php
/**
 * Language — DS Cost Calculator modification
 *
 * Location: admin/language/english/modification/ds_cost_calculator.php
 *
 * @package NivoCart
 */

// Heading
$_['heading_title']             = 'DS Cost Calculator';

// Breadcrumbs / tabs
$_['tab_global']                = 'Global Costs';
$_['tab_channels']              = 'Platform Costs';
$_['tab_help']                  = 'Help';

// Section headings
$_['text_global_costs']         = 'Global Business Costs';
$_['text_global_costs_desc']    = 'Costs that apply across all dropshipping platforms and the whole store.';
$_['text_channel_costs']        = 'Platform Costs';
$_['text_channel_costs_desc']   = 'Costs specific to each dropshipping platform / channel.';
$_['text_no_channels']          = 'No active dropshipping channels found. Please install and configure at least one DS connector (Avasam, CJ Dropshipping, etc.) before setting platform costs.';
$_['text_dependency']           = 'The DS Cost Calculator requires at least one Dropshipping connector to be installed (Avasam or CJ Dropshipping). Please install a connector first.';
$_['text_install_message']      = 'The DS Cost Calculator database table is missing. Click Install to set it up.';
$_['text_install_btn']          = 'Install DS Cost Calculator';
$_['text_home']                 = 'Home';
$_['text_loading']              = 'Loading…';
$_['text_saved']                = 'Cost configuration saved successfully.';
$_['text_vat_yes']              = 'Yes — supplier costs are ex-VAT (VAT reclaimable)';
$_['text_vat_no']               = 'No — VAT is included in the true supplier cost';

// Global cost field labels
$_['entry_hosting_monthly']     = 'Web Hosting (per month)';
$_['entry_domain_annual']       = 'Domain Name (per year)';
$_['entry_tools_annual']        = 'Annual Tools (per year)';
$_['entry_tools_annual_help']   = 'e.g. accounting software, email marketing, SEO tools';
$_['entry_chargeback_pct']      = 'Chargeback / Fraud Provision (%)';
$_['entry_chargeback_help']     = 'Percentage of revenue set aside for chargebacks (e.g. 0.50)';
$_['entry_vat_registered']      = 'VAT Registered?';
$_['entry_other_monthly']       = 'Other Monthly Costs';
$_['entry_other_description']   = 'Description of Other Costs';

// Per-channel field labels
$_['entry_platform_monthly']    = 'Platform Subscription (per month)';
$_['entry_advertising_monthly'] = 'Advertising / Marketing (per month)';
$_['entry_gateway_fee_pct']     = 'Payment Gateway Fee (%)';
$_['entry_gateway_fee_fixed']   = 'Payment Gateway Fixed Fee (per transaction)';
$_['entry_fx_fee_pct']          = 'Currency Conversion Fee (%)';
$_['entry_fx_fee_help']         = 'Applied to supplier cost payments in foreign currency (e.g. 1.50)';
$_['entry_returns_pct']         = 'Expected Returns Allowance (%)';
$_['entry_returns_help']        = 'Percentage of revenue reserved for customer returns / refunds (e.g. 3.00)';

// Buttons
$_['button_save']               = 'Save';
$_['button_close']              = 'Close';

// Help tab
$_['help_intro']                = 'The DS Cost Calculator stores all your dropshipping overhead costs so that the <strong>Dropshipping Profit Report</strong> can calculate accurate net profit figures.';
$_['help_global']               = '<strong>Global Costs</strong> — apply across all platforms: hosting, domain, annual tools, and any miscellaneous monthly overhead.';
$_['help_channels']             = '<strong>Platform Costs</strong> — specific to each connected DS channel: platform subscription, advertising spend, payment gateway fees, currency conversion, and expected returns.';
$_['help_vat']                  = '<strong>VAT Registered</strong> — if your business is VAT-registered you reclaim VAT on supplier invoices, so the true product cost is the ex-VAT supplier cost. If you are not VAT-registered, the VAT is a real cost and is added to the supplier cost in the profit calculation.';
$_['help_proration']            = 'Monthly and annual costs are prorated daily within each report period so the profit figures remain accurate for any date range you choose.';

// Errors
$_['error_permission']          = 'Warning: You do not have permission to modify DS Cost Calculator!';
$_['error_database']            = 'DS Cost Calculator database table not found.';
