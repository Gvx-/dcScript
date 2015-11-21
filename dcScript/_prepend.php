<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }					# public & admin

# check PHP version
$_id = basename(dirname(__FILE__));
$_version = $core->plugins->moduleInfo($_id, '_php_min_version');
if(empty($_version)) { $_version = '5.2'; }
if(version_compare(PHP_VERSION, $_version, '<')) {
	if(defined('DC_CONTEXT_ADMIN')) {
		dcPage::addErrorNotice(sprintf(__('%1$s require PHP version %2$s. (your PHP version is %3$s)'), $_id, $_version, PHP_VERSION));
	}
	$core->plugins->deactivateModule($_id);
	unset($_id, $_version);
	return;
}
unset($_id, $_version);

# chargement des class du plugin
$__autoload['dcPluginHelper022'] = dirname(__FILE__).'/inc/class.dcPluginHelper.php';
$__autoload['dcScript'] = dirname(__FILE__).'/inc/class.dcScript.php';

# initialisation
//dcScript::init();
dcScript::init();

if (!defined('DC_CONTEXT_ADMIN')) { return false; }
# admin only
