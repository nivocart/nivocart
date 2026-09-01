<?php
/**
 * Language: English — HMRC Making Tax Digital
 *
 * @package NivoCart
 */

// Heading
$_['heading_title']               = 'HMRC Making Tax Digital';

// Breadcrumb
$_['text_modification']           = 'Modifications';
$_['text_home']                   = 'Home';

// Tabs
$_['tab_core']                    = 'Connection &amp; Settings';
$_['tab_vat']                     = 'VAT MTD';
$_['tab_itsa']                    = 'Income Tax (ITSA)';

// -----------------------------------------------------------------------
// Core / Connection tab
// -----------------------------------------------------------------------
$_['text_credentials']            = 'HMRC Developer Hub Credentials';
$_['text_credentials_help']       = 'Register your software application at <a href="https://developer.service.hmrc.gov.uk" target="_blank">developer.service.hmrc.gov.uk</a> to obtain your Client ID and Client Secret. Set your redirect URI to the address shown below.';
$_['text_connection_status']      = 'Connection Status';
$_['text_components']             = 'Components';

$_['entry_client_id']             = 'Client ID';
$_['entry_client_secret']         = 'Client Secret';
$_['entry_sandbox']               = 'Sandbox Mode';
$_['entry_vat_enabled']           = 'VAT MTD';
$_['entry_redirect_uri']          = 'Redirect URI';

$_['text_sandbox_help']           = 'Use the HMRC sandbox for testing. Switch to Production only after HMRC compliance sign-off.';
$_['text_vat_enabled_help']       = 'Enable the VAT Making Tax Digital component to submit UK VAT returns directly to HMRC.';
$_['text_redirect_uri_help']      = 'Copy this URL and register it as your redirect URI in the HMRC Developer Hub application settings.';

$_['text_connected']              = 'Connected';
$_['text_not_connected']          = 'Not Connected';
$_['text_token_expires']          = 'Access token expires: %s';
$_['text_sandbox_mode']           = 'Sandbox (Testing)';
$_['text_production_mode']        = 'Production (Live)';

$_['text_enabled']                = 'Enabled';
$_['text_disabled']               = 'Disabled';
$_['text_yes']                    = 'Yes';
$_['text_no']                     = 'No';

// -----------------------------------------------------------------------
// VAT MTD tab
// -----------------------------------------------------------------------
$_['text_vat_settings']           = 'VAT Settings';
$_['text_vat_obligations']        = 'VAT Obligations';
$_['text_vat_history']            = 'Submitted Returns';
$_['text_vat_disabled_notice']    = 'The VAT MTD component is not enabled. Enable it on the Connection &amp; Settings tab.';
$_['text_not_connected_notice']   = 'You must connect to HMRC Government Gateway before using this feature.';
$_['text_no_obligations']         = 'No open obligations found. Use the Fetch button to retrieve current periods from HMRC.';
$_['text_no_history']             = 'No VAT returns have been submitted yet.';
$_['text_obligation_open']        = 'Open';
$_['text_obligation_fulfilled']   = 'Fulfilled';

$_['entry_vrn']                   = 'VAT Registration Number (VRN)';

$_['text_vrn_help']               = 'Your 9-digit UK VAT registration number (without the GB prefix).';

// Obligation table columns
$_['column_period']               = 'Period';
$_['column_due']                  = 'Due Date';
$_['column_status']               = 'Status';
$_['column_action']               = 'Action';
$_['column_received']             = 'Received';

// Return history table columns
$_['column_period_key']           = 'Period Key';
$_['column_submitted']            = 'Submitted';
$_['column_net_vat']              = 'Net VAT Due';

// -----------------------------------------------------------------------
// VAT Return Prepare page
// -----------------------------------------------------------------------
$_['heading_prepare']             = 'Prepare VAT Return';

$_['text_prepare_intro']          = 'Review and confirm the figures below before submitting to HMRC. All amounts in GBP, rounded to the nearest penny. You can edit any box before submitting.';
$_['text_vat_boxes']              = 'VAT Return Boxes';
$_['text_finalised_label']        = 'Declaration';
$_['text_finalised_confirm']      = 'I confirm that the information given in this return is correct and complete to the best of my knowledge and belief.';

$_['entry_vat_due_sales']         = 'Box 1 — VAT due on sales and other outputs';
$_['entry_vat_due_acquisitions']  = 'Box 2 — VAT due on acquisitions from other EC member states';
$_['entry_total_vat_due']         = 'Box 3 — Total VAT due (Box 1 + Box 2)';
$_['entry_vat_reclaimed']         = 'Box 4 — VAT reclaimed on purchases and other inputs';
$_['entry_net_vat_due']           = 'Box 5 — Net VAT to pay to HMRC or reclaim (difference between Box 3 and Box 4)';
$_['entry_total_value_sales']     = 'Box 6 — Total value of sales and other outputs (ex-VAT)';
$_['entry_total_value_purchases'] = 'Box 7 — Total value of purchases and other inputs (ex-VAT)';
$_['entry_total_goods_supplied']  = 'Box 8 — Total value of supplies of goods to other EC member states (ex-VAT)';
$_['entry_total_acquisitions']    = 'Box 9 — Total value of acquisitions of goods from other EC member states (ex-VAT)';

$_['text_box_auto']               = 'Auto-calculated from your orders';
$_['text_box_manual']             = 'Enter manually';
$_['text_box_derived']            = 'Calculated from Boxes 1 &amp; 2';
$_['text_box_derived_diff']       = 'Calculated from Boxes 3 &amp; 4';
$_['text_period_label']           = 'Period: %s to %s';

// -----------------------------------------------------------------------
// Buttons
// -----------------------------------------------------------------------
$_['button_save']                 = 'Save';
$_['button_cancel']               = 'Cancel';
$_['button_connect']              = 'Connect to HMRC';
$_['button_disconnect']           = 'Disconnect';
$_['button_fetch_obligations']    = 'Fetch Obligations from HMRC';
$_['button_prepare']              = 'Prepare Return';
$_['button_submit']               = 'Submit to HMRC';
$_['button_back']                 = 'Back';

// -----------------------------------------------------------------------
// Success messages
// -----------------------------------------------------------------------
$_['text_success_save']           = 'Settings have been saved.';
$_['text_success_disconnect']     = 'HMRC account disconnected.';
$_['text_success_obligations']    = 'Obligations fetched from HMRC and updated.';
$_['text_success_submit']         = 'VAT return submitted successfully. Receipt stored.';

// -----------------------------------------------------------------------
// Error messages
// -----------------------------------------------------------------------
$_['error_permission']            = 'Warning: You do not have permission to modify this modification.';
$_['error_client_id']             = 'Client ID is required.';
$_['error_client_secret']         = 'Client Secret is required.';
$_['error_vrn']                   = 'VAT Registration Number (VRN) is required to use VAT MTD.';
$_['error_vrn_format']            = 'VRN must be 9 digits (without the GB prefix).';
$_['error_not_connected']         = 'You must connect to HMRC before performing this action.';
$_['error_api']                   = 'HMRC API error: %s';
$_['error_token_refresh']         = 'Could not refresh HMRC access token: %s';
$_['error_finalised']             = 'You must confirm the declaration before submitting.';
$_['error_vat_box']               = 'All VAT return boxes must contain a valid numeric value.';
$_['error_credentials']           = 'Please save your Client ID and Client Secret before connecting.';
$_['error_vrn_required']          = 'Please save a valid VRN before fetching obligations.';
$_['error_nino_required']         = 'Please save a valid National Insurance Number before fetching ITSA periods.';
$_['error_nino_format']           = 'National Insurance Number must be in the format AA999999A.';
$_['error_itsa_box']              = 'All income and expense fields must contain a valid numeric value.';
$_['error_itsa_period']           = 'The specified ITSA period could not be found.';
$_['error_eops_finalised']        = 'You must confirm the EOPS declaration before submitting.';
$_['error_declaration_finalised'] = 'You must confirm the Final Declaration before submitting.';
$_['error_business_id']           = 'Could not retrieve your Self-Employment Business ID from HMRC. Ensure your NINO is correct and you have an active self-employment business registered with HMRC.';

// -----------------------------------------------------------------------
// ITSA MTD tab
// -----------------------------------------------------------------------
$_['text_itsa_settings']             = 'ITSA Settings';
$_['text_itsa_periods']              = 'Quarterly Periods';
$_['text_itsa_history']              = 'Submitted Updates';
$_['text_itsa_year_actions']         = 'Year-End Actions';
$_['text_itsa_disabled_notice']      = 'The Income Tax (ITSA) component is not enabled. Enable it on the Connection &amp; Settings tab.';
$_['text_no_periods']                = 'No ITSA periods found. Use the Fetch button to retrieve current quarterly periods from HMRC.';
$_['text_no_itsa_history']           = 'No quarterly updates have been submitted yet.';
$_['text_itsa_status_open']          = 'Open';
$_['text_itsa_status_fulfilled']     = 'Fulfilled';
$_['text_eops_not_submitted']        = 'Not submitted';
$_['text_eops_submitted']            = 'Submitted';
$_['text_declaration_not_submitted'] = 'Not submitted';
$_['text_declaration_submitted']     = 'Submitted';

$_['entry_itsa_enabled']             = 'Enable Income Tax MTD (ITSA)';
$_['entry_nino']                     = 'National Insurance Number (NINO)';
$_['entry_itsa_business_id']         = 'Self-Employment Business ID';

$_['text_itsa_enabled_help']         = 'Enables quarterly income and expense updates to HMRC under Making Tax Digital for Income Tax Self-Assessment (ITSA). Mandatory from April 2026 for self-employed traders with qualifying income.';
$_['text_nino_help']                 = 'Your National Insurance Number in the format AA999999A (e.g. QQ123456C). Used to identify your self-assessment record with HMRC.';
$_['text_itsa_business_help']        = 'Your HMRC Self-Employment Business ID is retrieved automatically when you first fetch ITSA periods. You do not normally need to enter this manually.';

// ITSA table columns
$_['column_tax_year']                = 'Tax Year';
$_['column_income']                  = 'Income (£)';
$_['column_expenses']                = 'Expenses (£)';
$_['column_eops']                    = 'EOPS';
$_['column_declaration']             = 'Final Declaration';

// ITSA buttons
$_['button_fetch_periods']           = 'Fetch Periods from HMRC';
$_['button_prepare_update']          = 'Prepare Update';
$_['button_submit_eops']             = 'Submit EOPS';
$_['button_submit_declaration']      = 'Submit Final Declaration';

// -----------------------------------------------------------------------
// ITSA Prepare page
// -----------------------------------------------------------------------
$_['heading_itsa_prepare']               = 'Prepare Quarterly Income &amp; Expenses Update';

$_['text_itsa_prepare_intro']            = 'Review and confirm your income and expenses for the period below. Income is calculated from completed orders. Enter any allowable business expenses in the fields provided. All amounts in GBP.';
$_['text_itsa_income_section']           = 'Income';
$_['text_itsa_expenses_section']         = 'Allowable Expenses';
$_['text_itsa_finalised_confirm']        = 'I confirm that the information given in this update is correct and complete to the best of my knowledge and belief.';
$_['text_eops_finalised_confirm']        = 'I confirm that to the best of my knowledge the information given in this End of Period Statement is correct and complete.';
$_['text_declaration_finalised_confirm'] = 'I confirm that I have reviewed the information provided to calculate my final tax due and that it is correct and complete to the best of my knowledge and belief.';

$_['text_turnover_help']             = 'Total income from your business (auto-calculated from completed orders in this period).';
$_['text_other_income_help']         = 'Any other business income not included in turnover. Enter 0 if none.';

$_['entry_turnover']                 = 'Turnover';
$_['entry_other_income']             = 'Other Income';
$_['entry_cost_of_goods']            = 'Cost of Goods Sold';
$_['entry_admin_costs']              = 'Administrative Costs';
$_['entry_travel_costs']             = 'Travel Costs';
$_['entry_staff_costs']              = 'Staff Costs';
$_['entry_advertising_costs']        = 'Advertising &amp; Marketing';
$_['entry_premises_costs']           = 'Premises Costs';
$_['entry_other_expenses']           = 'Other Allowable Expenses';

// ITSA success messages
$_['text_success_itsa_periods']      = 'ITSA quarterly periods fetched from HMRC and updated.';
$_['text_success_itsa_submit']       = 'Quarterly income update submitted to HMRC successfully.';
$_['text_success_eops']              = 'End of Period Statement (EOPS) submitted to HMRC successfully.';
$_['text_success_declaration']       = 'Final Declaration submitted to HMRC successfully.';
