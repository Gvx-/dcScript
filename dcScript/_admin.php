<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_CONTEXT_ADMIN')) { return; }

# define new permissions
$core->auth->setPermissionType('dcScript.edit',__('Edit public scripts'));

# menu & dashboard
$core->addBehavior('adminDashboardFavorites', array($core->dcScript, 'adminDashboardFavs'));
$core->dcScript->adminMenu('System');

if(!$core->auth->check('admin', $core->blog->id)) { return; }
# admin only

if(!$core->auth->isSuperAdmin()) { return; }
# super admin only
