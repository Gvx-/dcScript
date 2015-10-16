<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'dcScript',
	/* Description*/	'Add script for DC 2.7+',
	/* Author */		'Gvx',
	/* Version */		'2.0.0-r0034',
	array(
		'permissions' 			=>	'admin',
		'type'					=>	'plugin',
		'Priority'				=>	1000,
		'support'	/* url */	=>	'http://forum.dotclear.org/index.php',
		'details' 	/* url */	=>	'http://plugins.dotaddict.org/dc2/',
		'requires'	/*id*/		=>	array(
		
		)
	)
);

# ---------------------------------------------------------
# use codemirror version 5.7
# see: http://codemirror.net/
# ---------------------------------------------------------
