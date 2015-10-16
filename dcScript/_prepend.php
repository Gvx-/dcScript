<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_RC_PATH')) { return; }					# public & admin

# chargement des class du plugin
$__autoload['dcScript'] = dirname(__FILE__).'/inc/class.dcScript.php';

# initialisation
dcScript::init(array(
	'perm'		=> 'admin',								# permissions acces page administration
	'icons'		=> array(								# icones pour menu & tableau de bord
		'small' => '/inc/icon-small.png',
		'large' => '/inc/icon-large.png'
	)
));

if (!defined('DC_CONTEXT_ADMIN')) { return false; }		# admin only
