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

use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

class Backend extends Process {
    public static function init(): bool {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool {
        if (self::status()) {
            My::addBackendMenuItem(Menus::MENU_SYSTEM);
        }

        return self::status();
    }
}