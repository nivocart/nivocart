<?php
// Heading
$_['heading_title']              = 'Gallery';

// Navigation
$_['text_home']                  = 'Home';
$_['text_modification']          = 'Modifications';
$_['text_install_message']       = 'The Gallery database table is missing. Click Install to set it up.';
$_['text_install_btn']           = 'Install Gallery';

// Tabs
$_['tab_submissions']            = 'Submissions';
$_['tab_settings']               = 'Settings';

// Text
$_['text_success']               = 'Success: Gallery settings have been saved!';
$_['text_success_edit']          = 'Success: The submission has been updated!';
$_['text_success_status']        = 'Success: %s submission(s) updated!';
$_['text_success_delete']        = 'Success: The selected submissions have been deleted!';
$_['text_uninstalled']           = 'Gallery has been uninstalled and the submitters\' details have been deleted. The images that were already published are still in the folder image/data/gallery, delete that folder by hand when you no longer need them.';
$_['text_yes']                   = 'Yes';
$_['text_no']                    = 'No';
$_['text_all']                   = ' --- All --- ';
$_['text_pending']               = 'Pending';
$_['text_approved']              = 'Approved';
$_['text_refused']               = 'Refused';
$_['text_add_filter']            = 'Add';
$_['text_no_results']            = 'No submissions yet.';
$_['text_none_selected']         = 'Please select at least one submission first!';
$_['text_info']                  = 'Images are sent in by visitors and wait here for your approval.<br />Approving publishes the image and its own page, setting it back to Pending unpublishes it.<br />Refusing deletes the image from the server for good and erases the submitter\'s name, email address and IP address, so send any message you want to send before you refuse.<br />Check every image for personal information (house numbers, street names, number plates) before approving.';
$_['text_pending_total']         = 'Pending: <b>%s</b> &nbsp; Approved: <b>%s</b> &nbsp; Refused: <b>%s</b>';
$_['text_confirm']               = 'This cannot be undone!';
$_['text_confirm_delete']        = 'Delete Submissions';
$_['text_image_gone']            = 'The image has been deleted from the server.';

// Columns
$_['column_image']               = 'Image';
$_['column_pet']                 = 'Name';
$_['column_type']                = 'Filter';
$_['column_filter_name']         = 'Name';
$_['column_sort_order']          = 'Sort Order';
$_['column_submitter']           = 'Submitted by';
$_['column_date_added']          = 'Date Submitted';
$_['column_status']              = 'Status';
$_['column_action']              = 'Action';

// Entries
$_['entry_status']               = 'Status:';
$_['entry_limit']                = 'Images per Page:';
$_['entry_rate_limit']           = 'Submissions per Visitor:';
$_['entry_notify']               = 'Email me New Submissions:';
$_['entry_watermark']            = 'Watermark Text:';
$_['entry_title']                = 'Page Title:';
$_['entry_meta_description']     = 'Meta Tag Description:';
$_['entry_meta_keyword']         = 'Meta Tag Keywords:';
$_['entry_description']          = 'Description:';
$_['entry_agreement']            = 'Publishing Agreement and Privacy Policy:';
$_['entry_pet_name']             = 'Name:';
$_['entry_pet_type']             = 'Filter:';
$_['entry_filters']              = 'Filters:';
$_['entry_reference']            = 'Submission Number:';
$_['entry_submitter']            = 'Owner\'s Full Name:';
$_['entry_email']                = 'Owner\'s Email Address:';
$_['entry_optin']                = 'Agreed to be Contacted:';
$_['entry_ip']                   = 'IP Address:';
$_['entry_date_added']           = 'Date Submitted:';
$_['entry_status_current']       = 'Status:';
$_['entry_page']                 = 'Gallery Page:';

// Help
$_['help_status']                = 'Switch the gallery pages on or off. Keep it off until you have checked the texts below.';
$_['help_limit']                 = 'Number of images on each page of the gallery (1 to 100).';
$_['help_rate_limit']            = 'How many images one visitor (IP address) may send in 24 hours (1 to 50).';
$_['help_notify']                = 'Send an email to the Store Email each time a new image is submitted.';
$_['help_filters']               = 'The groups visitors choose from when they send in an image, and filter the gallery by (e.g. Indoor, Outdoor). Enter the name in every language. A filter that is used by an image cannot be removed.';
$_['help_watermark']             = 'Printed in the bottom right corner of every new image, e.g. www.yourstore.com. Leave empty for no watermark. Images that were already submitted are not changed.';
$_['help_title']                 = 'The heading and the title of the gallery page. Leave empty to use &quot;Gallery&quot;.';
$_['help_meta_description']      = 'The optimal number of characters is around 155, including spaces.<br />SEO importance: High.';
$_['help_meta_keyword']          = 'Separate the keywords with a comma.';
$_['help_description']           = 'Shown above the images on the gallery page, good for the search engines.';
$_['help_agreement']             = 'Shown on the Publishing Agreement page and ticked by the submitter. Leave empty to use the standard text from the language file. Have this text checked before you go live.';
$_['help_description_edit']      = 'Up to 250 characters. This text is public.';
$_['help_submitter']             = 'Only you can see the details of the submitter, they are never shown on the website.';

// Buttons
$_['button_approve']             = 'Approve';
$_['button_pending']             = 'Set Pending';
$_['button_refuse']              = 'Refuse';
$_['button_delete']              = 'Delete';
$_['button_filter']              = 'Filter';
$_['button_edit']                = 'View';
$_['button_save']                = 'Save';
$_['button_cancel']              = 'Cancel';

// Email to the submitter when the image is published
$_['email_approved_subject']     = 'Your photo is now live on %s';
$_['email_approved_text']        = "Hello %s,\n\nGood news! The photo of %s has been approved and is now published in our Gallery:\n\n%s\n\nIf you ever want the photo removed, use this link and confirm on the page that opens:\n\n%s\n\nThank you for sharing your photo with us.\n\n%s";

// Errors
$_['error_permission']           = 'Warning: You do not have permission to modify the Gallery!';
$_['error_warning']              = 'Warning: Please check the form carefully for errors!';
$_['error_limit']                = 'Images per Page must be a number between 1 and 100!';
$_['error_rate_limit']           = 'Submissions per Visitor must be a number between 1 and 50!';
$_['error_filters']              = 'Add at least one Filter, with a name (1 to 32 characters) in every language!';
$_['error_filter_in_use']        = 'The Filter "%s" is used by %d image(s) and cannot be removed!';
$_['error_filter']               = 'Please choose a Filter!';
$_['error_watermark']            = 'Watermark Text must be 60 characters or less!';
$_['error_not_found']            = 'Warning: That submission does not exist any more!';
$_['error_status']               = 'Warning: %s submission(s) could not be changed (a refused submission cannot be approved again, its image is deleted).';
$_['error_pet_name']             = 'Name must be between 2 and 32 characters!';
$_['error_description']          = 'The description must be 250 characters or less!';
