<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if(!$core->auth->check('admin',$core->blog->id)) { return; }

if($core->dcScript->checkConfig()) {
	$core->addBehavior('adminDashboardFavorites', array($core->dcScript, 'adminDashboardFavs'));
	$core->dcScript->adminMenu('System');
}
