/**
 * @license Copyright (c) 2003-2025, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Suppress v4.22.1 warning message.
	config.versionCheck = false;

	config.skin = 'moono';

	// Define changes to default configuration here. For example:
	// config.language = 'en';
	// config.uiColor = '#AADC6E';

	// Define Responsive values
	config.filebrowserWindowWidth = '640';
	config.filebrowserWindowHeight = '440';

	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;

	// Define Usage rules
	config.allowedContent = true;
	config.htmlEncodeOutput = false;
	config.resize_enabled = true;
	config.scayt_autoStartup = false;
	config.toolbarCanCollapse = true;
	config.toolbarStartupExpanded = true;
};
