<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'dcScript',
	/* Description*/	'Add script for DC 2.8+',
	/* Author */		'Gvx',
	/* Version */		'2.0.0-r0120',
	array(
		/* standard plugin options dotclear */
		'permissions' 			=>	'dcScript.edit',
		'type'					=>	'plugin',
		'Priority'				=>	1000,
		'support'	/* url */	=>	'http://forum.dotclear.org/index.php',
		'details' 	/* url */	=>	'http://plugins.dotaddict.org/dc2/',
		'requires'	/* id */	=>	array(

		),
		/* specific plugin options */
		'_dc_min_version'		=> '2.8',
		'_php_min_version'		=> '5.2',
		'_icon_small'			=> '/inc/icon-small.png',
		'_icon_large'			=> '/inc/icon-large.png'
	)
);

# ---------------------------------------------------------
# use codemirror version 5.7
# see: http://codemirror.net/
# ---------------------------------------------------------
