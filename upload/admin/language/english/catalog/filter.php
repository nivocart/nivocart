<?php
// Heading
$_['heading_title']     = 'Filters';

// Text
$_['text_success']      = 'Success: You have modified <b>Filters</b> !';
$_['text_filter_steps'] = '<b>Required steps to display Filters on the storefront:</b><br />'
	. '<b>1.</b> Create a Filter Group and its Filters on this page (Catalog &gt; Filters).<br />'
	. '<b>2.</b> Select the Filters on each product to be filtered: Product &gt; <b>Links</b> tab &gt; Filters.<br />'
	. '<b>3.</b> Select the Filters on each Category that must show them: Category &gt; <b>Data</b> tab &gt; Filters. Each category (and sub-category) needs its own selection.<br />'
	. '<b>4.</b> Install and enable the <b>Filter</b> module (Extensions &gt; Modules) with the <b>Category</b> layout, a position and a sort order.<br />'
	. '<b>Note:</b> the Filter module is only displayed on Category pages, and only when the category has Filters selected (step 3).';

// Column
$_['column_group']      = 'Filter Group';
$_['column_sort_order'] = 'Sort Order';
$_['column_action']     = 'Action';

// Button
$_['button_info']       = 'Information';

// Entry
$_['entry_group']       = 'Filter Group Name:';
$_['entry_name']        = 'Filter Name:';
$_['entry_sort_order']  = 'Sort Order:';

// Error
$_['error_permission']  = 'Warning: You do not have permission to modify <b>Filters</b> !';
$_['error_product']     = 'Warning: This filter cannot be deleted as it is currently assigned to %s products!';
$_['error_group']       = 'Filter Group Name must be between 1 and 64 characters!';
$_['error_name']        = 'Filter Name must be between 1 and 64 characters!';
