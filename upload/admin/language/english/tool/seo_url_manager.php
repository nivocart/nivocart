<?php
// Heading
$_['heading_title']                      = 'SEO URL Manager';
$_['heading_title_audit']                = 'SEO URL Audit';

// Text
$_['text_success']                       = 'Success: You have modified <b>SEO URL Manager</b> !';
$_['text_default']                       = 'Default';
$_['text_summary']                       = 'Summary';
$_['text_total']                         = 'Total';
$_['text_problems_found']                = '%d problem(s) found. Enabled Products, Categories, Manufacturers, Information pages and News items are checked.';
$_['text_audit_clean']                   = 'No SEO URL problems found.';
$_['text_filtered']                      = 'Showing a filtered list.';
$_['text_edit_entry']                    = 'Edit Entry';
$_['text_edit_alias']                    = 'Edit Alias';

// Text - Entry types
$_['text_type_product']                  = 'Product';
$_['text_type_category']                 = 'Category';
$_['text_type_manufacturer']             = 'Manufacturer';
$_['text_type_information']              = 'Information';
$_['text_type_news']                     = 'News';
$_['text_type_other']                    = 'Other';

// Text - Problems
$_['text_problem_missing']               = 'Missing';
$_['text_problem_duplicate']             = 'Duplicated';
$_['text_problem_mistyped']              = 'Mistyped';
$_['text_problem_orphan']                = 'Orphaned';

// Text - Problem details
$_['text_detail_missing_none']           = 'No SEO URL entry';
$_['text_detail_missing_empty']          = 'SEO URL entry has an empty keyword';
$_['text_detail_duplicate_keyword']      = 'Keyword is used more than once';
$_['text_detail_duplicate_multiple']     = 'Entry has more than one SEO URL';
$_['text_detail_mistyped_query']         = 'Route is malformed (expected e.g. product_id=12)';
$_['text_detail_mistyped_space']         = 'Contains spaces';
$_['text_detail_mistyped_uppercase']     = 'Contains uppercase letters';
$_['text_detail_mistyped_characters']    = 'Contains characters other than a-z, 0-9 and -';
$_['text_detail_mistyped_hyphen_edge']   = 'Starts or ends with a hyphen';
$_['text_detail_mistyped_hyphen_double'] = 'Contains a double hyphen';
$_['text_detail_mistyped_short']         = 'Shorter than 3 characters';
$_['text_detail_mistyped_long']          = 'Longer than 255 characters';
$_['text_detail_orphan_deleted']         = 'The entry no longer exists';

// Column
$_['column_url_alias_id']                = 'URL Alias ID';
$_['column_query']                       = 'Route';
$_['column_keyword']                     = 'Keyword';
$_['column_action']                      = 'Action';
$_['column_type']                        = 'Type';
$_['column_problem']                     = 'Problem';
$_['column_name']                        = 'Name';
$_['column_detail']                      = 'Details';

// Button
$_['button_audit']                       = 'Audit SEO URLs';
$_['button_run_audit']                   = 'Run Audit Again';
$_['button_show_all']                    = 'Show All Problems';
$_['button_back']                        = 'Back';

// Entry
$_['entry_query']                        = 'Route (Query):';
$_['entry_keyword']                      = 'SEO Keyword:<span class="help">Do not use spaces instead replace spaces with - and make sure the keyword is globally unique</span>';

// Links
$_['link_seo_category']                  = 'Categories';
$_['link_seo_product']                   = 'Products';
$_['link_seo_manufacturer']              = 'Manufacturers';
$_['link_seo_information']               = 'Information';
$_['link_seo_news']                      = 'News';

// Error
$_['error_url_status']                   = 'Attention: SEO URL\'s are Not activated in the Store Settings!';
$_['error_permission']                   = 'Warning: You do not have permission to modify <b>SEO URL Manager</b> !';
$_['error_query']                        = 'Query Route must be between 3 and 255 characters!';
$_['error_keyword']                      = 'SEO Keyword must be between 3 and 255 characters!';
$_['error_keyword_exist']                = 'Warning: SEO Keyword already exists!';
