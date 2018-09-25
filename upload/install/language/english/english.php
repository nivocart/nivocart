<?php
// Heading
$_['heading_step_1']         = 'License agreement';
$_['heading_step_2']         = 'Pre-Installation';
$_['heading_step_3']         = 'Configuration';
$_['heading_step_4']         = 'Installation complete';
$_['heading_upgrade']        = 'Upgrade';
$_['heading_success']        = 'Upgrade complete';

$_['heading_next']           = 'What to do next?';
$_['heading_setting']        = '1 - Review your settings';
$_['heading_security']       = '2 - Secure your store';
$_['heading_server']         = '3 - Check your server';
$_['heading_update']         = 'Check your settings';

// Help
$_['help_setting']           = 'Log into your Administration by clicking the "Go to my Dashboard" button below.<br />Go to "System > Settings > (Your Store)", check all your store parameters and adjust them as required. Save.';
$_['help_security']          = 'Check "users" and "user group permissions" under "System > Users", then activate and configure the "User Log".<br />Block harmful IPs: if you already have a list of harmful IP addresses, enter them now under "System > Server > Block IPs".';
$_['help_server']            = 'Go to your Host and check that everything has been setup correctly, including your PHP.ini settings.<br />Using the File Manager, delete any "index.html" that you may find at the root of your store, as only "index.php" must be present.';
$_['help_installer']         = 'For security reasons, the install directory will be deleted automatically at the first login.';
$_['help_update']            = 'Log into your Administration by clicking the "Go to my Dashboard" button below.<br />Go to "System > Settings > (Your Store)", check that all your parameters are correctly set. Save.';

// Text
$_['text_follow_steps']      = 'Follow these steps carefully!';
$_['text_clear_cookie']      = 'After upgrade, clear any cookies in your browser to avoid getting token errors.';
$_['text_admin_page']        = 'Load the Admin page &amp; press Ctrl+F5 twice to force the browser to apply the css changes.';
$_['text_admin_user']        = 'Goto Admin -> Users -> User Groups and Edit the Top Administrator group. Check All boxes.';
$_['text_admin_setting']     = 'Goto Admin &amp; Edit the main System Settings. Update all fields, even if there is no change. Save.';
$_['text_store_front']       = 'Load the Store Front &amp; press Ctrl+F5 twice to force the browser to apply the css changes.';
$_['text_be_patient']        = '<b>Click "Upgrade" to install the latest version of NivoCart.</b><br /><br />The upgrade process will take a few minutes to complete. <b>Please be patient!</b>';
$_['text_success']           = 'Congratulations! You now have the latest version of NivoCart.';
$_['text_install_php']       = '1. Please configure your PHP settings to match requirements listed below.';
$_['text_install_extension'] = '2. Please make sure the PHP extensions listed below are installed.';
$_['text_install_file']      = '3. Please make sure you have set the correct permissions on the files list below.';
$_['text_install_directory'] = '4. Please make sure you have set the correct permissions on the directories list below.';
$_['text_db_connection']     = '1. Please enter your database connection details.';
$_['text_db_administration'] = '2. Please enter a username and password for the administration.';
$_['text_db_option']         = '3. Select advanced options.';
$_['text_congratulation']    = 'Congratulations! You have successfully installed NivoCart.';
$_['text_setting']           = 'PHP Settings';
$_['text_current']           = 'Current Settings';
$_['text_required']          = 'Required Settings';
$_['text_extension']         = 'Extension Settings';
$_['text_database']          = 'Database Driver';
$_['text_file']              = 'Files';
$_['text_directory']         = 'Directories';
$_['text_status']            = 'Status';
$_['text_version']           = 'PHP Version';
$_['text_global']            = 'Register Globals';
$_['text_magic']             = 'Magic Quotes GPC';
$_['text_file_upload']       = 'File Uploads';
$_['text_session']           = 'Session Auto Start';
$_['text_db']                = 'Database';
$_['text_mysqli']            = 'MySQLi';
$_['text_mpdo']              = 'PDO';
$_['text_gd']                = 'GD';
$_['text_curl']              = 'cURL';
$_['text_dom']               = 'DOM';
$_['text_xml']               = 'XML';
$_['text_openssl']           = 'OpenSSL Encrypt';
$_['text_zlib']              = 'ZLIB';
$_['text_zip']               = 'ZIP';
$_['text_mbstring']          = 'mbstring';
$_['text_on']                = 'On';
$_['text_off']               = 'Off';
$_['text_writable']          = 'Writable';
$_['text_unwritable']        = 'Unwritable';
$_['text_missing']           = 'Missing';
$_['text_activate']          = 'Yes, activate';
$_['text_remove']            = 'Yes, remove';
$_['text_is_installing']     = 'Please wait while NivoCart is being installed ...';
$_['text_is_upgrading']      = 'Please wait while NivoCart is being upgraded ...';
$_['text_login']             = 'Go to my Dashboard';
$_['text_project_home']      = 'Project Homepage';
$_['text_project_forum']     = 'Project Forum';
$_['text_footer']            = 'Copyright © 2018 NivoCart - All rights reserved';

// Entry
$_['entry_db_driver']        = 'DB Driver';
$_['entry_db_hostname']      = 'Hostname';
$_['entry_db_username']      = 'Username';
$_['entry_db_password']      = 'Password';
$_['entry_db_database']      = 'Database';
$_['entry_db_port']          = 'Port';
$_['entry_db_prefix']        = 'Prefix';
$_['entry_username']         = 'Username';
$_['entry_password']         = 'Password';
$_['entry_email']            = 'Email';
$_['entry_rewrite']          = 'Activate Seo-url Rewrite ?';
$_['entry_maintenance']      = 'Activate Maintenance mode ?';
$_['entry_demo_data']        = 'Remove Demo Data ?';

// Error
$_['error_php_version']      = 'Warning: You need to use PHP 7.0 or above for NivoCart to work!';
$_['error_php_uploads']      = 'Warning: PHP file_uploads needs to be enabled!';
$_['error_php_session']      = 'Warning: NivoCart will not work with session.auto_start enabled!';
$_['error_php_extension']    = 'Warning: A database extension needs to be loaded in the php.ini for NivoCart to work!';
$_['error_php_gd']           = 'Warning: GD extension needs to be loaded for NivoCart to work!';
$_['error_php_curl']         = 'Warning: CURL extension needs to be loaded for NivoCart to work!';
$_['error_php_dom']          = 'Warning: DOM extension needs to be loaded for NivoCart to work!';
$_['error_php_xml']          = 'Warning: XML extension needs to be loaded for NivoCart to work!';
$_['error_php_openssl']      = 'Warning: OpenSSL Encrypt extension needs to be loaded for NivoCart to work!';
$_['error_php_zlib']         = 'Warning: ZLIB extension needs to be loaded for NivoCart to work!';
$_['error_php_zip']          = 'Warning: ZIP extension needs to be loaded for NivoCart to work!';
$_['error_php_mbstring']     = 'Warning: mbstring extension needs to be loaded for NivoCart to work!';
$_['error_config_rename']    = 'Warning: config.php does not exist. You need to rename config-dist.php to config.php!';
$_['error_config_write']     = 'Warning: config.php needs to be writable for NivoCart to be installed!';
$_['error_cache_write']      = 'Warning: Cache directory needs to be writable for NivoCart to work!';
$_['error_logs_write']       = 'Warning: Logs directory needs to be writable for NivoCart to work!';
$_['error_upload_write']     = 'Warning: Upload directory needs to be writable for NivoCart to work!';
$_['error_download_write']   = 'Warning: Download directory needs to be writable for NivoCart to work!';
$_['error_image_write']      = 'Warning: Image directory needs to be writable for NivoCart to work!';
$_['error_imagecache_write'] = 'Warning: Image cache directory needs to be writable for NivoCart to work!';
$_['error_imagedata_write']  = 'Warning: Image data directory needs to be writable for NivoCart to work!';

$_['error_db_hostname']      = 'Hostname is required!';
$_['error_db_username']      = 'Username is required!';
$_['error_db_database']      = 'Database Name is required!';
$_['error_db_port']          = 'Database Port is required and must be numeric!';
$_['error_db_prefix']        = 'DB Prefix can only contain lowercase characters in the a-z range, 0-9 and underscores.';
$_['error_db_connect']       = 'Error: Could not connect to the database please make sure the database server, username and password is correct!';
$_['error_db_not_exist']     = 'Error: Database does not exist!';
$_['error_db_mysqli']        = 'MySQLi is not supported on your server! Try using PDO.';
$_['error_username']         = 'Username is required!';
$_['error_password']         = 'Password is required!';
$_['error_email']            = 'Invalid Email address!';
$_['error_config']           = 'Error: Could not write to config.php please check you have set the correct permissions on: ';

// Buttons
$_['button_install']         = 'Install NivoCart';
$_['button_upgrade']         = 'Upgrade NivoCart';
$_['button_continue']        = 'Continue &nbsp; &#9655;';
$_['button_back']            = '&#9665; &nbsp; Back';

// Terms
$_['text_terms'] = '<h3>Open Software License v. 3.0 (OSL-3.0)</h3>
    <p>This Open Software License (the &ldquo;License&rdquo;) applies to any original work
     of authorship (the &ldquo;Original Work&rdquo;) whose owner (the &ldquo;Licensor&rdquo;)
     has placed the following licensing notice adjacent to the copyright notice for the Original Work:</p>
    <p>Licensed under the Open Software License version 3.0</p>
    <p>1) <b>Grant of Copyright License.</b> Licensor grants You a worldwide, royalty-free,
     non-exclusive, sublicensable license, for the duration of the copyright, to do the following:</p>
    <p>a) to reproduce the Original Work in copies, either alone or as part of a collective work;</p>
    <p>b) to translate, adapt, alter, transform, modify, or arrange the Original Work,
     thereby creating derivative works (&ldquo;Derivative Works&rdquo;) based upon the Original Work;</p>
    <p>c) to distribute or communicate copies of the Original Work and Derivative Works to the public,
     <u>with the proviso that copies of Original Work or Derivative Works that You distribute or
     communicate shall be licensed under this Open Software License</u>;</p>
    <p>d) to perform the Original Work publicly; and</p>
    <p>e) to display the Original Work publicly.</p>
    <p>2) <b>Grant</b> of Patent License. Licensor grants You a worldwide, royalty-free, non-exclusive,
     sublicensable license, under patent claims owned or controlled by the Licensor that are embodied
     in the Original Work as furnished by the Licensor, for the duration of the patents, to make, use, sell,
     offer for sale, have made, and import the Original Work and Derivative Works.</p>
    <p>3) <b>Grant</b> of Source Code License. The term "Source Code" means the preferred form
     of the Original Work for making modifications to it and all available documentation describing how
     to modify the Original Work. Licensor agrees to provide a machine-readable copy of the Source Code
     of the Original Work along with each copy of the Original Work that Licensor distributes.
     Licensor reserves the right to satisfy this obligation by placing a machine-readable copy
     of the Source Code in an information repository reasonably calculated to permit inexpensive
     and convenient access by You for as long as Licensor continues to distribute the Original Work.</p>
    <p>4) <b>Exclusions From License Grant.</b> Neither the names of Licensor, nor the names
     of any contributors to the Original Work, nor any of their trademarks or service marks,
     may be used to endorse or promote products derived from this Original Work without express
     prior permission of the Licensor. Except as expressly stated herein, nothing in this License grants
     any license to Licensor\'s trademarks, copyrights, patents, trade secrets or any other
     intellectual property. No patent license is granted to make, use, sell, offer for sale, have made,
     or import embodiments of any patent claims other than the licensed claims defined in Section 2.
     No license is granted to the trademarks of Licensor even if such marks are included in the Original Work.
     Nothing in this License shall be interpreted to prohibit Licensor from licensing under terms different
     from this License any Original Work that Licensor otherwise would have a right to license.</p>
    <p>5) <b>External Deployment.</b> The term &ldquo;External Deployment&rdquo; means the use, distribution,
     or communication of the Original Work or Derivative Works in any way such that the Original Work
     or Derivative Works may be used by anyone other than You, whether those works are distributed
     or communicated to those persons or made available as an application intended for use over a network.
     As an express condition for the grants of license hereunder, You must treat any External Deployment by You
     of the Original Work or a Derivative Work as a distribution under section 1(c).</p>
    <p>6) <b>Attribution Rights.</b> You must retain, in the Source Code of any Derivative Works that You create,
     all copyright, patent, or trademark notices from the Source Code of the Original Work, as well as
     any notices of licensing and any descriptive text identified therein as an &ldquo;Attribution Notice&rdquo;.
     You must cause the Source Code for any Derivative Works that You create to carry a prominent
     Attribution Notice reasonably calculated to inform recipients that You have modified the Original Work.</p>
    <p>7) <b>Warranty of Provenance and Disclaimer of Warranty.</b> Licensor warrants that the copyright in
     and to the Original Work and the patent rights granted herein by Licensor are owned by the Licensor
     or are sublicensed to You under the terms of this License with the permission of the contributor(s)
     of those copyrights and patent rights. Except as expressly stated in the immediately preceding sentence,
     the Original Work is provided under this License on an &ldquo;AS IS&rdquo; BASIS and WITHOUT WARRANTY,
     either express or implied, including, without limitation, the warranties of non-infringement, merchantability
     or fitness for a particular purpose. THE ENTIRE RISK AS TO THE QUALITY OF THE ORIGINAL WORK IS WITH YOU.
     This DISCLAIMER OF WARRANTY constitutes an essential part of this License.
     No license to the Original Work is granted by this License except under this disclaimer.</p>
    <p>8) <b>Limitation of Liability.</b> Under no circumstances and under no legal theory,
     whether in tort (including negligence), contract, or otherwise, shall the Licensor be liable to anyone
     for any indirect, special, incidental, or consequential damages of any character arising as a result of this License
     or the use of the Original Work including, without limitation, damages for loss of goodwill, work stoppage,
     computer failure or malfunction, or any and all other commercial damages or losses.
     This limitation of liability shall not apply to the extent applicable law prohibits such limitation.</p>
    <p>9) <b>Acceptance and Termination.</b> If, at any time, You expressly assented to this License,
     that assent indicates your clear and irrevocable acceptance of this License and all of its terms and conditions.
     If You distribute or communicate copies of the Original Work or a Derivative Work, You must make
     a reasonable effort under the circumstances to obtain the express assent of recipients to the terms of this License.
     This License conditions your rights to undertake the activities listed in Section 1, including your right to create
     Derivative Works based upon the Original Work, and doing so without honoring these terms and conditions
     is prohibited by copyright law and international treaty. Nothing in this License is intended to affect
     copyright exceptions and limitations (including &ldquo;fair use&rdquo; or &ldquo;fair dealing&rdquo;).
     This License shall terminate immediately and You may no longer exercise any of the rights granted to You
     by this License upon your failure to honor the conditions in Section 1(c).</p>
    <p>10) <b>Termination for Patent Action.</b> This License shall terminate automatically and You may
     no longer exercise any of the rights granted to You by this License as of the date You commence an action,
     including a cross-claim or counterclaim, against Licensor or any licensee alleging that the Original Work
     infringes a patent. This termination provision shall not apply for an action alleging patent infringement
     by combinations of the Original Work with other software or hardware.</p>
    <p>11) <b>Jurisdiction, Venue and Governing Law.</b> Any action or suit relating to this License
     may be brought only in the courts of a jurisdiction wherein the Licensor resides or in which Licensor conducts
     its primary business, and under the laws of that jurisdiction excluding its conflict-of-law provisions.
     The application of the United Nations Convention on Contracts for the International Sale of Goods
     is expressly excluded. Any use of the Original Work outside the scope of this License or after its termination
     shall be subject to the requirements and penalties of copyright or patent law in the appropriate jurisdiction.
     This section shall survive the termination of this License.</p>
    <p>12) <b>Attorneys\' Fees.</b> In any action to enforce the terms of this License or seeking damages
     relating thereto, the prevailing party shall be entitled to recover its costs and expenses, including,
     without limitation, reasonable attorneys\' fees and costs incurred in connection with such action,
     including any appeal of such action. This section shall survive the termination of this License.</p>
    <p>13) <b>Miscellaneous.</b> If any provision of this License is held to be unenforceable,
     such provision shall be reformed only to the extent necessary to make it enforceable.</p>
    <p>14) <b>Definition of &ldquo;You&rdquo; in This License.</b> &ldquo;You&rdquo; throughout this License,
     whether in upper or lower case, means an individual or a legal entity exercising rights under, and complying with
     all of the terms of, this License. For legal entities, &ldquo;You&rdquo; includes any entity that controls, is controlled by,
     or is under common control with you. For purposes of this definition, &ldquo;control&rdquo; means (i) the power,
     direct or indirect, to cause the direction or management of such entity, whether by contract or otherwise,
     or (ii) ownership of fifty percent (50%) or more of the outstanding shares, or (iii) beneficial ownership of such entity.</p>
    <p>15) <b>Right to Use.</b> You may use the Original Work in all ways not otherwise restricted or conditioned
     by this License or by law, and Licensor promises not to interfere with or be responsible for such uses by You.</p>
    <p>16) <b>Modification of This License.</b> This License is Copyright © 2005 Lawrence Rosen.
     Permission is granted to copy, distribute, or communicate this License without modification.
     Nothing in this License permits You to modify this License as applied to the Original Work or to Derivative Works.
     However, You may modify the text of this License and copy, distribute or communicate
     your modified version (the &ldquo;Modified License&rdquo;) and apply it to other original works of authorship
     subject to the following conditions: (i) You may not indicate in any way that your Modified License
     is the &ldquo;Open Software License&rdquo; or &ldquo;OSL&rdquo; and you may not use those names
     in the name of your Modified License; (ii) You must replace the notice specified in the first paragraph above
     with the notice &ldquo;Licensed under &lt;insert your license name here&gt;&rdquo;
     or with a notice of your own that is not confusingly similar to the notice in this License;
     and (iii) You may not claim that your original works are open source software unless your Modified License
     has been approved by Open Source Initiative (OSI) and You comply with its license review and certification process.</p>';
