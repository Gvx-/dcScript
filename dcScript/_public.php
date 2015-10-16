<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent', array('dcScript', 'publicHeadContent'));
$core->addBehavior('publicFooterContent', array('dcScript', 'publicFooterContent'));
