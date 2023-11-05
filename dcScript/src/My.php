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

use Dotclear\App;
use Dotclear\Module\MyPlugin;

class My extends MyPlugin
{
    protected static function checkCustomContext(int $context): ?bool
    {
        return in_array($context, [self::MANAGE, self::MENU, self::BACKEND]) ?
            App::task()->checkContext('BACKEND')
            && App::blog()->isDefined()
            && App::auth()->check(App::auth()->makePermissions([dcScript::EDIT]), App::blog()->id())
            && My::settings()->get('enabled')
            : null;
    }
}
