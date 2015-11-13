<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }					# public & admin

# check PHP version
if(version_compare(PHP_VERSION, '5.2', '<')) {
	$_id = basename(dirname(__FILE__));
	if(defined('DC_CONTEXT_ADMIN')) {
		dcPage::addErrorNotice(sprintf(__('%1$s require PHP version %2$s. (your PHP version is %3$s)'), $_id, '5.2', PHP_VERSION));
	}
	$core->plugins->deactivateModule($_id);
	unset($_id);
	return;
}

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
