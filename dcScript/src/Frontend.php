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
use Dotclear\Core\Process;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('publicHeadContent', [self::class, 'publicHeadContent']);
        App::behavior()->addBehavior('publicFooterContent', [self::class, 'publicFooterContent']);

        return true;
    }

    /**
     * publicHeadContent
     */
    public static function publicHeadContent(): void
    {
        if (_crypt::getCryptLib() == _crypt::MCRYPT) {
            return;
        } // deprecated
        $html = _crypt::decrypt((string) My::settings()->get('header_code'), _crypt::getCryptKey(), _crypt::getCryptLib());
        if (My::settings()->get('enabled') && My::settings()->get('header_code_enabled') && !empty($html)) {
            echo "<!-- dcScript header begin -->\n" . $html . "\n<!-- dcScript header end -->\n";
        }
    }

    /**
     * publicFooterContent
     */
    public static function publicFooterContent(): void
    {
        if (_crypt::getCryptLib() == _crypt::MCRYPT) {
            return;
        } // deprecated
        $html = _crypt::decrypt((string) My::settings()->get('footer_code'), _crypt::getCryptKey(), _crypt::getCryptLib());
        if (My::settings()->get('enabled') && My::settings()->get('footer_code_enabled') && !empty($html)) {
            echo "<!-- dcScript footer begin -->\n" . $html . "\n<!-- dcScript footer end -->\n";
        }
    }
}
