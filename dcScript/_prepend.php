<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }
# common (public & admin)

# loading of plugin class
$__autoload['dcPluginHelper024'] = dirname(__FILE__).'/inc/class.dcPluginHelper.php';
$__autoload['dcScript'] = dirname(__FILE__).'/inc/class.dcScript.php';

# initialization
$core->dcScript = new dcScript(basename(dirname(__FILE__)));

if(defined('DC_CONTEXT_ADMIN')) {
	# admin only
	
} else {
	# public only
	
}
