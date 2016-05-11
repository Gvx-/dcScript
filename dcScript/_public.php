<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent', array($core->dcScript, 'publicHeadContent'));
$core->addBehavior('publicFooterContent', array($core->dcScript, 'publicFooterContent'));
$core->addBehavior('publicEntryBeforeContent', array($core->dcScript, 'publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent', array($core->dcScript, 'publicEntryAfterContent'));
$core->addBehavior('publicCommentBeforeContent', array($core->dcScript, 'publicCommentBeforeContent'));
$core->addBehavior('publicCommentAfterContent', array($core->dcScript, 'publicCommentAfterContent'));
$core->addBehavior('publicCommentFormBeforeContent', array($core->dcScript, 'publicCommentFormBeforeContent'));
$core->addBehavior('publicCommentFormAfterContent', array($core->dcScript, 'publicCommentFormAfterContent'));
$core->addBehavior('publicPingBeforeContent', array($core->dcScript, 'publicPingBeforeContent'));
$core->addBehavior('publicPingAfterContent', array($core->dcScript, 'publicPingAfterContent'));
$core->addBehavior('publicTopAfterContent', array($core->dcScript, 'publicTopAfterContent'));
$core->addBehavior('publicInsideFooter', array($core->dcScript, 'publicInsideFooter'));
