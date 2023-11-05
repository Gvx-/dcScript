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

use Exception;
use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Core\Backend\Notices;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            self::putSettings();
            self::updates((string) App::version()->getVersion(My::id()));

            return true;
        } catch (Exception $e) {
            App::error()->add($e->getMessage());

            return false;
        }
    }

    private static function putSettings(): void
    {
        # default settings
        My::settings()->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
        My::settings()->put('header_code_enabled', false, 'boolean', __('Enable header code'), false, true);
        My::settings()->put('footer_code_enabled', false, 'boolean', __('Enable footer code'), false, true);
        My::settings()->put('header_code', _crypt::encrypt('', _crypt::getCryptKey()), 'string', __('Header code'), false, true);
        My::settings()->put('footer_code', _crypt::encrypt('', _crypt::getCryptKey()), 'string', __('Footer code'), false, true);
        My::settings()->put('backup_ext', '.html.txt', 'string', __('Extension Backup Files'), false, true);
        My::settings()->put('crypt_lib', _crypt::DEFAULT_CRYPT, 'string', __('Encryption library'), false, true);	// add v2.1.0
    }

    private static function updates(string $oldVersion): void
    {
        if (empty($oldVersion)) {
            return;
        }					// First installation

        # version < 2
        if (version_compare($oldVersion, '2', '<')) {		// /!\ timeout possible for a lot of blogs
            # upgrade global settings
            App::blog()->settings()->delWorkspace(My::id());
            self::putSettings();
            # upgrade all blogs settings
            $rs = App::blogs()->getBlogs();
            while ($rs->fetch()) {
                $settings = App::blogSettings()->createFromBlog($rs->f('blog_id'));
                $settings->addWorkspace(My::id());
                $blogSettings = App::blog()->settings()->get($rs->f('blog_id'));
                $blogSettings->put('enabled', $blogSettings->get('enabled'));
                $blogSettings->put('header_code_enabled', $blogSettings->get('header_code_enabled'));
                $blogSettings->put('footer_code_enabled', $blogSettings->get('footer_code_enabled'));
                $blogSettings->put('header_code', _crypt::encrypt(base64_decode($blogSettings->get('header_code')), App::config()->masterKey()));
                $blogSettings->put('footer_code', _crypt::encrypt(base64_decode($blogSettings->get('footer_code')), App::config()->masterKey()));
                $blogSettings->put('backup_ext', $blogSettings->get('backup_ext'));
                unset($blogSettings, $settings);

            }
            Notices::addWarningNotice(__('Default settings update.'));
        }
        # version < 2.0.0-r0143
        if (version_compare($oldVersion, '2.0.0-r0143', '<')) {		// /!\ timeout possible for a lot of blogs
            # upgrade all blogs settings
            $rs = App::blogs()->getBlogs();
            while ($rs->fetch()) {
                $settings = App::blogSettings()->createFromBlog($rs->f('blog_id'));
                $settings->addWorkspace(My::id());
                $blogSettings = App::blog()->settings()->get($rs->f('blog_id'));
                $blogSettings->put('header_code', _crypt::encrypt(_crypt::decrypt($blogSettings->get('header_code'), App::config()->masterKey()), _crypt::getCryptKey()));
                $blogSettings->put('footer_code', _crypt::encrypt(_crypt::decrypt($blogSettings->get('footer_code'), App::config()->masterKey()), _crypt::getCryptKey()));
                unset($blogSettings, $settings);

            }
        }
        # version < 2.1.1-dev-r0001
        if (version_compare($oldVersion, '2.1.1-dev-r0001', '<')) {		// /!\ timeout possible for a lot of blogs
            My::settings()->put('crypt_lib', _crypt::OPENSSL, 'string', __('Encryption library'), false, true);

            # upgrade all blogs settings
            $rs = App::blogs()->getBlogs();
            while ($rs->fetch()) {
                $settings = App::blogSettings()->createFromBlog($rs->f('blog_id'));
                $settings->addWorkspace(My::id());
                $blogSettings = App::blog()->settings()->get($rs->f('blog_id'));
                if (version_compare(PHP_VERSION, '7.2', '<')) {
                    $blogSettings->put('header_code', _crypt::encrypt(_crypt::decrypt($blogSettings->get('header_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::OPENSSL));
                    $blogSettings->put('footer_code', _crypt::encrypt(_crypt::decrypt($blogSettings->get('footer_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::OPENSSL));
                    $blogSettings->put('crypt_lib', _crypt::OPENSSL);
                } else {
                    $blogSettings->put('crypt_lib', '');
                }
                unset($blogSettings, $settings);

            }
        }
        # version < 2.5.0-dev-r0001
        if (version_compare($oldVersion, '2.4.0-dev-r0001', '<')) {		// /!\ timeout possible for a lot of blogs
            $rs = App::blogs()->getBlogs();
            while ($rs->fetch()) {
                $settings = App::blogSettings()->createFromBlog($rs->f('blog_id'));
                $settings->addWorkspace(My::id());
                $blogSettings = App::blog()->settings()->get($rs->f('blog_id'));
                $blogSettings->put('header_code', _crypt::encrypt(_crypt::decrypt($blogSettings->get('header_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::NO_CRYPT));
                $blogSettings->put('footer_code', _crypt::encrypt(_crypt::decrypt($blogSettings->get('footer_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::NO_CRYPT));
                $blogSettings->put('crypt_lib', _crypt::NO_CRYPT);
                unset($blogSettings, $settings);

            }
        }
    }
}
