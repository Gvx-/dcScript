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
use Dotclear\Core\Process;

class Prepend extends Process {
	
    public static function init(): bool {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool {
        if (!self::status()) { return false; }

        return true;
    }
}