<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'dcScript',
	/* Description*/	'Add script for DC 2.9+',
	/* Author */		'Gvx',
	/* Version */		'3.0.0-r0036',
	array(
		/* standard plugin options dotclear */
		'permissions' 				=>	'dcScript.edit'
		, 'type'					=>	'plugin'
		, 'Priority'				=>	1000
		, 'support'		/* url */	=>	'http://forum.dotclear.org/viewtopic.php?pid=335785#p335785'
		, 'details' 	/* url */	=>	'https://bitbucket.org/Gvx_/dcscript'
		, 'requires'	/* id(s) */	=>	array(
			array('core', '2.9')
		)
		/* specific plugin options */
		, '_icon_small'				=>	'/inc/icon-small.png'
		, '_icon_large'				=>	'/inc/icon-large.png'
	)
);

# ---------------------------------------------------------
# use codemirror version 5.14.2
# see: http://codemirror.net/
# ---------------------------------------------------------
