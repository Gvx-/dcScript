<?php
/**
  * This file is part of dcScript plugin for Dotclear 2.
  *
  * @package Dotclear\plugin\dcScript
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2014-2023 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

declare(strict_types=1);

namespace Dotclear\Plugin\dcScript;

use dcCore;
use Dotclear\Module\MyPlugin;

use initDcScript;

class My extends MyPlugin {
	
	protected static function checkCustomContext(int $context): ?bool {
		return in_array($context, [self::MANAGE, self::MENU, self::BACKEND]) ? 
            defined('DC_CONTEXT_ADMIN')
            && !is_null(dcCore::app()->blog)
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([initDcScript::EDIT]), dcCore::app()->blog->id)
			&& My::settings()->get('enabled')
            : null;
    }

}
