<?php
// Heading
$_['heading_title']          = 'Pinterest / Google Product Feed';

// Text
$_['text_feed']              = 'Product Feeds';
$_['text_success']           = 'Success: You have modified <b>Pinterest / Google Product Feed</b> !';
$_['text_cache_cleared']     = 'Success: The feed cache has been cleared. It will be rebuilt the next time the feed URL is requested.';
$_['text_cache_none']        = 'No feed has been generated yet.';
$_['text_cache_info']        = 'Generated: %s &nbsp;|&nbsp; Items: %s &nbsp;|&nbsp; Skipped: %s (disabled/out of stock are never counted)';
$_['text_cache_skipped']     = 'No valid image: %s, no price: %s, other: %s';
$_['text_variants_on']       = 'Variants detected: items are grouped with <b>item_group_id</b>.';
$_['text_variants_off']      = 'Variants not detected: every product is exported as a single item.';
$_['text_clear_cache']       = 'Clear Cache';

// Entry
$_['entry_status']           = 'Status:';
$_['entry_data_feed']        = 'Data Feed Url:';
$_['entry_currency']         = 'Currency:';
$_['entry_cache_hours']      = 'Cache Lifetime (hours):';
$_['entry_min_image']        = 'Minimum Image Size (px):';
$_['entry_category_default'] = 'Default Google Product Category:';
$_['entry_category_map']     = 'Google Product Category per Category:';
$_['entry_cache']            = 'Feed Cache:';

// Help
$_['help_currency']          = 'Three letter ISO 4217 code. Prices are exported tax inclusive.';
$_['help_cache_hours']       = 'The feed file is rebuilt when it is older than this (1 to 168). Pinterest fetches about once a day.';
$_['help_min_image']         = 'Products whose main image is smaller than this (shortest side) are left out of the feed.';
$_['help_category']          = 'Used for products whose category (and its parent category) has no ID below. Enter the numeric ID only, from the <a href="https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt" target="_blank">Google Product Taxonomy</a>. Leave blank to omit the field.';
$_['help_category_map']      = 'Top level categories are listed with their sub-categories indented. Enter the <b>numeric ID</b> only (digits, not the text path) of the Google category that fits the products in that category. A product uses the ID of its most specific category; if that box is blank it uses its parent category, then the default.';

// Error
$_['error_permission']       = 'Warning: You do not have permission to modify <b>Pinterest / Google Product Feed</b> !';
$_['error_warning']          = 'Warning: Please check the form carefully for errors !';
$_['error_currency']         = 'Currency must be a three letter code, for example GBP !';
$_['error_cache_hours']      = 'Cache Lifetime must be between 1 and 168 hours !';
$_['error_min_image']        = 'Minimum Image Size must be between 100 and 5000 pixels !';
$_['error_category']         = 'Google Product Category must be a numeric ID !';
