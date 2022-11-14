<?php
/**
  * This file is part of dcScript plugin for Dotclear 2.
  *
  * @package Dotclear\plungin\dcScript
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2014-2022 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if(!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'dcScript',
	/* Description*/	'Add script for DC',
	/* Author */		'Gvx',
	/* Version */		'2.3.0',
	array(
		/* standard plugin options dotclear */
		'permissions' 				=>	dcCore::app()->auth->makePermissions([dcScriptPerms::EDIT]),
		'type'						=>	'plugin',
		'Priority'					=>	1010,
		'support'	/* url */		=>	'http://forum.dotclear.org/viewtopic.php?pid=335785#p335785',
		'details' 	/* url */		=>	'https://github.com/Gvx-/dcScript',
		'requires'	/* id(s) */		=>	array(
			array('core', '2.24')
		),
		'settings'		=> array(
			//'self'		=> '', 														// Optionnal: '#onglet' (or false since 2.17)
			//'blog'		=> '#params.id',											// Optionnal: '#params.id'
			//'pref'		=> '#user-options.id',										// Optionnal: '#user-options.id'
		),
		'repository'	=> 'https://raw.githubusercontent.com/Gvx-/dcScript/master/dcstore.xml',	// Optionnal: URL
		/* specific plugin options */
		'_class_name'				=>	'dcScript',								// Required: plugin master class name
		'_class_path'				=>	'/inc/class.dcScript.php',				// Required: plugin master class path (relative)
		'_icon_small'				=>	'/inc/icon-small.png',					// Required: plugin small icon path (16*16 px) (relative)
		'_icon_large'				=>	'/inc/icon-large.png',					// Required: plugin large icon path (64*64 px) (relative)
	)
);

# ---------------------------------------------------------
# use codemirror version 5.65.9
# see: http://codemirror.net/
# ---------------------------------------------------------
