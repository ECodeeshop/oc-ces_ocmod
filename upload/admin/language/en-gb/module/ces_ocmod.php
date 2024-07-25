<?php
// Heading
$_['heading_title']    			= '      CES OCMOD';

// Text
$_['text_extension']    		= 'Extensions';
$_['text_success']      		= 'Success: You have modified ' . $_['heading_title'] . ' module!';
$_['text_edit']         		= $_['heading_title'] . ' Module';

// Entry
$_['entry_heading_title'] 	    = 'Heading Title';
$_['entry_text_pages']      	= 'Text Pages';
$_['entry_status']      		= 'Status';
$_['entry_refresh_modification_on_install']      		= 'Auto Refresh Modification (Install)';
$_['entry_refresh_modification_on_uninstall']      		= 'Auto Refresh Modification (Uninstall)';

$_['instructions'] = 'Make sure OCMOD is installed correctly. Read the full installation instructions inside the <a href="%s">Documentation tab</a> carefully. There is also a README.md inside the zip file.';


// Help
$_['help_height'] 				= 'Height of the barcode';
$_['text_instructions'] 		= 'Instructions';

// tab
$_['tab_general'] 				= 'General';

// Error
$_['error_permission']  		= 'Warning: You do not have permission to modify ' . $_['heading_title'] . ' module!';
$_['error_required']    		= '%s required!';

$_['full_instructions'] = '<h4>Auto Refresh Modification (Install)</h4>
<span>Upon installing an extension containing an ocmod.xml file, the modification cache is automatically refreshed.</span>
<br /><br />
<h4>Auto Refresh Modification (Uninstall)</h4>
<span>Upon uninstalling an extension containing an ocmod.xml file, the modification cache is automatically refreshed.</span>
<br /><br />
<h4>Modification</h4>
<span>Modification generated files will be available in path<br /><code>%s</code></span>
<br /><br />
<h4>Ocmod Install Files for Developers</h4>
<code>%s</code>
<br />
You can add multiple ocmod files (with a .ocmod.xml extension) to modify your OpenCart store.<br />
Ocmod files will be searched for in the following directory structure: extension folder > module name > system folder > *.ocmod.xml
';