<?php
// Heading
$_['heading_title']          = 'Gallery';
$_['heading_submit']         = 'Share Your Photo';
$_['heading_agreement']      = 'Gallery Publishing Agreement and Privacy Policy';
$_['heading_remove']         = 'Remove Your Photo';

// Text
$_['text_gallery']           = 'Gallery';
$_['text_all']               = 'All';
$_['text_grid']              = 'Grid';
$_['text_list']              = 'List';
$_['text_limit']             = 'Show:';
$_['text_empty']             = 'There are no photos here yet. Be the first to share yours!';
$_['text_alt']               = 'Photo of %s (%s)';
$_['text_submit']            = 'Share Your Photo';
$_['text_back']              = 'Back to the Gallery';
$_['text_previous']          = 'Previous Photo';
$_['text_next']              = 'Next Photo';
$_['text_credit']            = 'Photo supplied by a visitor';
$_['text_info_title']        = '%s - %s';
$_['text_info_meta']         = '%s, %s - from the %s Gallery. Share a photo of your own too!';
$_['text_pagination']        = 'Showing {start}-{end} of {total} Photos ({pages} Pages)';

// Submission form
$_['text_form_intro']        = 'Send us a photo and see it in our Gallery! Every photo is checked before it is published. Anyone can take part, you do not need an account.';
$_['text_pet']               = 'Your Submission';
$_['text_photo']             = 'The Photo';
$_['text_owner']             = 'Your Details';
$_['text_owner_help']        = 'Your name and email address are never shown on the website. We only use them to confirm your submission and to send you the link to remove your photo.';
$_['text_choose']            = 'Choose a photo';
$_['text_photo_help']        = 'JPG or PNG, up to 8 MB. One photo per submission. You can move and resize the square to choose the part of the photo that is published (it is shown as a square).';
$_['text_crop_hint']         = 'Drag the square to choose the part of the photo to publish. Use the mouse wheel or pinch to zoom.';
$_['text_select']            = 'Select';
$_['text_no_file']           = 'No photo selected';
$_['text_wait']              = 'Please wait, your photo is being sent...';
$_['text_watermark_note']    = 'A small watermark with our website address is added to the photo.';
$_['text_optin']             = 'You may contact me about my submission and my photo (for example to tell me when it is published).';
$_['text_agree_age']         = 'I am 18 years old or older.';
$_['text_agree_publish']     = 'I own this photo and I agree to the <a href="%s" target="_blank">Gallery Publishing Agreement and Privacy Policy</a>.';
$_['text_success']           = '<p>Thank you! Your photo has been received.</p><p>Your submission number is <b>%s</b>. We have sent a confirmation to your email address with a link to remove the photo at any time.</p><p>Every photo is checked before it is published, which can take a few days.</p>';
$_['text_removed']           = '<p>Your submission has been deleted and the photo has been removed from our website. A confirmation has been sent to your email address.</p>';
$_['text_remove_confirm']    = '<p>You are about to remove the photo of <b>%s</b> from the Gallery.</p><p>The photo and your details will be deleted from our website for good. This cannot be undone.</p>';
$_['text_remove_invalid']    = '<p>This link is not valid, or the photo has already been removed.</p>';
$_['button_remove']          = 'Remove the Photo';
$_['button_send']            = 'Send My Photo';

// Entries
$_['entry_pet_name']         = 'Name';
$_['entry_filter']           = 'Category';
$_['entry_description']      = 'Short Description (optional)';
$_['entry_fullname']         = 'Your Full Name';
$_['entry_email']            = 'Your Email Address';
$_['entry_captcha']          = 'Enter the code below';
$_['help_description']       = 'A short description of the photo (maximum 100 characters).';
$_['help_pet_name']          = 'Do not use a surname or any address details.';

// Email: submission received
$_['email_submitted_subject'] = 'Your photo has been received (%s)';
$_['email_submitted_text']    = "Hello %s,\n\nThank you for sending us the photo of %s for our Gallery.\n\nYour submission number is %s.\n\nEvery photo is checked before it is published. If you ticked the box to be contacted, we will let you know when it is live.\n\nIf you ever want the photo removed, use this link and confirm on the page that opens:\n\n%s\n\nThank you,\n%s";

// Email: photo deleted
$_['email_removed_subject']   = 'Your photo has been removed (%s)';
$_['email_removed_text']      = "Hello %s,\n\nAs you asked, the photo of %s (submission %s) and your details have been deleted from our Gallery.\n\nThank you,\n%s";

// Email to the store owner
$_['email_admin_subject']     = 'New Gallery photo to approve (%s)';
$_['email_admin_text']        = "A new photo is waiting for your approval.\n\nSubmission: %s\nName: %s (%s)\n\nOpen Extensions > Modifications > Gallery in your administration to check it.";

// Errors
$_['error_disabled']         = 'The Gallery is not open at the moment. Please come back soon!';
$_['error_token']            = 'Your session has expired. Please reload the page and try again.';
$_['error_rate']             = 'You have sent several photos today already. Please try again tomorrow.';
$_['error_general']          = 'Please check the form, some information is missing or not correct.';
$_['error_pet_name']         = 'Name must be between 2 and 32 characters!';
$_['error_filter']           = 'Please choose a Category!';
$_['error_description']      = 'The description must be 250 characters or less!';
$_['error_fullname']         = 'Your Full Name must be between 2 and 64 characters!';
$_['error_email']            = 'Email Address does not appear to be valid!';
$_['error_agree_age']        = 'You must be 18 years old or older to send a photo!';
$_['error_agree_publish']    = 'You must confirm that you own the photo and accept the Publishing Agreement!';
$_['error_captcha']          = 'Warning: Verification codes do not match!';
$_['error_no_photo']         = 'Please choose a photo!';
$_['error_upload']           = 'The photo could not be received. Please try again.';
$_['error_size']             = 'The photo is too big. The maximum is 8 MB.';
$_['error_type']             = 'The photo must be a JPG or PNG picture.';
$_['error_dimensions']       = 'The photo is too small. It must be at least 300 x 300 pixels.';
$_['error_too_large']        = 'The photo is too large. Please send a smaller picture.';
$_['error_server']           = 'Sorry, the photo could not be processed. Please try another photo or come back later.';
$_['error_js_type']          = 'Please choose a JPG or PNG photo up to 8 MB.';
$_['error_js_small']         = 'The chosen area is too small. Make the square bigger or choose a larger photo (at least 300 x 300 pixels).';

// Publishing Agreement and Privacy Policy (used until you write your own in the Gallery settings)
$_['text_agreement_default'] = '<h2>Publishing Agreement</h2>
<p>This agreement is for the %1$s Gallery. By sending a photo you confirm that:</p>
<ul>
<li>you are 18 years old or older;</li>
<li>you took the photo, or the owner of the photo has allowed you to send it, and it is a photo you are happy to share publicly;</li>
<li>nobody else can be recognised in the photo, or they have agreed to it being published;</li>
<li>the photo contains no personal information, for example house numbers, street names, car or motorcycle number plates or any other information that could be used to trace a person or a place.</li>
</ul>
<p>Photos that do not follow these rules, or that we consider unsuitable, are refused and deleted.</p>
<h2>Your Photo and Copyright</h2>
<p>The photo stays your property. You only give %1$s the right to display it in the Gallery on our website, together with its name and short description, for as long as it is published.<br />We add a small watermark with our website address, and we may crop it to a square and make it smaller.</p>
<p>We do not use the photos for marketing, advertising, social media or any other purpose, and we do not sell, share or give them to anyone else.<br />The photo is a public web page, so search engines may show it in their image results, and we cannot control copies that others make.</p>
<h2>Privacy Policy</h2>
<p>This policy is only for the Gallery. It is separate from the Privacy Policy of our online store.</p>
<ul>
<li><b>Who we are:</b> %1$s is responsible for your information. You can contact us at %2$s.</li>
<li><b>What we keep:</b> the photo, its name and description, your name and email address, and the IP address you sent the photo from (to stop abuse).</li>
<li><b>What is public:</b> only the photo, its name and the short description. Your name and email address are never shown.</li>
<li><b>Why we keep it:</b> to check and publish your photo, to confirm your submission and to let you remove it. If you ticked the box, we may also email you about your submission. We do not send you newsletters or offers because of the Gallery.</li>
<li><b>How long:</b> until you ask us to remove the photo, or until we remove it. If we refuse a photo, we delete it straight away together with your name, email address and IP address.</li>
<li><b>Removing your photo:</b> use the link in the confirmation email at any time. The photo and your details are deleted, and you receive a confirmation. If you lose the link, email us at %2$s.</li>
<li><b>Your rights:</b> you can ask to see, correct or delete the information we hold about you. If you are not happy with how we use it, you can complain to the data protection authority in your country.</li>
</ul>';
