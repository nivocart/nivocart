<?php
// Heading
$_['heading_title']          = 'Google Merchant Center Product Feed';

// Text
$_['text_feed']              = 'Product Feeds';
$_['text_success']           = 'Success: You have modified <b>Google Merchant Center Product Feed</b> !';
$_['text_cache_cleared']     = 'Success: The feed cache has been cleared. It will be rebuilt the next time the feed URL is requested.';
$_['text_cache_none']        = 'No feed has been generated yet.';
$_['text_cache_info']        = 'Generated: %s &nbsp;|&nbsp; Items: %s &nbsp;|&nbsp; Skipped: %s (disabled/out of stock are never counted)';
$_['text_cache_skipped']     = 'No valid image: %s, no price: %s, other: %s';
$_['text_variants_on']       = 'Variants detected: items are grouped with <b>item_group_id</b>.';
$_['text_variants_off']      = 'Variants not detected: every product is exported as a single item.';
$_['text_clear_cache']       = 'Clear Cache';
$_['text_taxonomy_link']     = 'Open the Google Product Taxonomy';
$_['text_taxonomy_placeholder'] = 'Search the taxonomy...';
$_['text_taxonomy_target']   = 'Click a result to fill: %s';
$_['text_taxonomy_empty']    = 'No matching category found.';
$_['text_handling_to']       = 'to';
$_['text_handling_days']     = 'business days';

// Entry
$_['entry_status']           = 'Status:';
$_['entry_data_feed']        = 'Data Feed Url:';
$_['entry_currency']         = 'Currency:';
$_['entry_cache_hours']      = 'Cache Lifetime (hours):';
$_['entry_min_image']        = 'Minimum Image Size (px):';
$_['entry_extra_images']     = 'Additional Images per Product:';
$_['entry_category_default'] = 'Default Google Product Category:';
$_['entry_category_map']     = 'Google Product Category per Category:';
$_['entry_cache']            = 'Feed Cache:';
$_['entry_taxonomy']         = 'Find Google Category ID:';
$_['entry_group_variants']   = 'Group Variants:';
$_['entry_handling']         = 'Handling Time:';

// Help
$_['help_currency']          = 'Three letter ISO 4217 code. Prices are exported tax inclusive.';
$_['help_cache_hours']       = 'The feed file is rebuilt when it is older than this (1 to 168). Google fetches the feed about once a day.';
$_['help_min_image']         = 'Products whose main image is smaller than this (shortest side) are left out of the feed.';
$_['help_extra_images']      = 'Maximum number of extra gallery images listed for each product (0 to 10). A lower number puts less load on the server when Google downloads the images. 0 sends the main image only.';
$_['help_group_variants']    = 'Sends <b>item_group_id</b> for dropship variants. Google expects the variants of a group to differ by colour, size or similar attributes, which this feed does not send, so leave this disabled unless your variants are set up that way.';
$_['help_handling']          = 'Optional. Business days from order to dispatch (minimum and maximum). Leave both blank to rely on the handling time set in your Merchant Center shipping settings.';
$_['help_taxonomy']          = 'Type a word, for example <b>dog beds</b>, and click a result to put its numeric ID into the category box you last clicked in (the Default box if none).';
$_['help_category']          = 'Used for products whose category (and its parent category) has no ID below. Enter the numeric ID only, from the <a href="https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt" target="_blank">Google Product Taxonomy</a>. Leave blank to omit the field.';
$_['help_category_map']      = 'Top level categories are listed with their sub-categories indented. Enter the <b>numeric ID</b> only (digits, not the text path) of the Google category that fits the products in that category. A product uses the ID of its most specific category; if that box is blank it uses its parent category, then the default.';

// Error
$_['error_permission']       = 'Warning: You do not have permission to modify <b>Google Merchant Center Product Feed</b> !';
$_['error_warning']          = 'Warning: Please check the form carefully for errors !';
$_['error_currency']         = 'Currency must be a three letter code, for example GBP !';
$_['error_cache_hours']      = 'Cache Lifetime must be between 1 and 168 hours !';
$_['error_min_image']        = 'Minimum Image Size must be between 100 and 5000 pixels !';
$_['error_extra_images']     = 'Additional Images must be a whole number between 0 and 10 !';
$_['error_handling']         = 'Handling Time must be two whole numbers (0 to 60), with the minimum not above the maximum, or both blank !';
$_['error_category']         = 'Google Product Category must be a numeric ID !';
$_['error_taxonomy']         = 'The Google Product Taxonomy could not be downloaded. Use the link to look up the ID manually !';
