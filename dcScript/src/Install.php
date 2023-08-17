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
use dcCore;
use Dotclear\Core\Process;
use Dotclear\Core\Backend\Notices;
use Dotclear\Helper;
use dcSettings;

class Install extends Process {
	
    public static function init(): bool {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool {
        if (!self::status()) {
            return false;
        }

        try {
			self::putSettings();
			self::updates((string) dcCore::app()->getVersion(My::id()));

			return true;

        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return false;
        }
    }
	
	private static function putSettings(): void {
		# default settings
		My::settings()->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		My::settings()->put('header_code_enabled', false, 'boolean', __('Enable header code'), false, true);
		My::settings()->put('footer_code_enabled', false, 'boolean', __('Enable footer code'), false, true);
		My::settings()->put('header_code', _crypt::encrypt('', _crypt::getCryptKey()), 'string', __('Header code'), false, true);
		My::settings()->put('footer_code', _crypt::encrypt('', _crypt::getCryptKey()), 'string', __('Footer code'), false, true);
		My::settings()->put('backup_ext', '.html.txt', 'string', __('Extension Backup Files'), false, true);
		My::settings()->put('crypt_lib', _crypt::DEFAULT_CRYPT, 'string', __('Encryption library'), false, true);	// add v2.1.0
	}
	
	private static function updates(string $oldVersion): void {
		if(empty($oldVersion)) { return; }					// First installation

		# version < 2
		if(version_compare($oldVersion, '2', '<')) {		// /!\ timeout possible for a lot of blogs
			# upgrade global settings
			dcCore::app()->blog->settings->My::id()->dropAll(true);
			self::putSettings();
			# upgrade all blogs settings
			$rs = dcCore::app()->getBlogs();
			while ($rs->fetch()) {
				$settings = new dcSettings($rs->f('blog_id'));
				$settings->addNamespace(My::id());
				$settings->My::id()->put('enabled', $settings->My::id()->get('enabled'));
				$settings->My::id()->put('header_code_enabled', $settings->My::id()->get('header_code_enabled'));
				$settings->My::id()->put('footer_code_enabled', $settings->My::id()->get('footer_code_enabled'));
				$settings->My::id()->put('header_code', _crypt::encrypt(base64_decode($settings->My::id()->get('header_code')), DC_MASTER_KEY));
				$settings->My::id()->put('footer_code', _crypt::encrypt(base64_decode($settings->My::id()->get('footer_code')), DC_MASTER_KEY));
				$settings->My::id()->put('backup_ext', $settings->My::id()->get('backup_ext'));
				unset($settings);
			}
			Notices::addWarningNotice(__('Default settings update.'));
		}
		# version < 2.0.0-r0143
		if(version_compare($oldVersion, '2.0.0-r0143', '<')) {		// /!\ timeout possible for a lot of blogs
			# upgrade all blogs settings
			$rs = dcCore::app()->getBlogs();
			while ($rs->fetch()) {
				$settings = new dcSettings($rs->f('blog_id'));
				$settings->addNamespace(My::id());
				$settings->My::id()->put('header_code', _crypt::encrypt(_crypt::decrypt($settings->My::id()->get('header_code'), DC_MASTER_KEY), _crypt::getCryptKey()));
				$settings->My::id()->put('footer_code', _crypt::encrypt(_crypt::decrypt($settings->My::id()->get('footer_code'), DC_MASTER_KEY), _crypt::getCryptKey()));
				unset($settings);
			}
		}
		# version < 2.1.1-dev-r0001
		if(version_compare($oldVersion, '2.1.1-dev-r0001', '<')) {		// /!\ timeout possible for a lot of blogs
			dcCore::app()->blog->settings->My::id()->put('crypt_lib', _crypt::OPENSSL, 'string', __('Encryption library'), false, true);
			# upgrade all blogs settings
			$rs = dcCore::app()->getBlogs();
			while ($rs->fetch()) {
				$settings = new dcSettings($rs->f('blog_id'));
				$settings->addNamespace(My::id());
				if(version_compare(PHP_VERSION, '7.2', '<')) {
					$settings->My::id()->put('header_code', _crypt::encrypt(_crypt::decrypt($settings->My::id()->get('header_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::OPENSSL));
					$settings->My::id()->put('footer_code', _crypt::encrypt(_crypt::decrypt($settings->My::id()->get('footer_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::OPENSSL));
					$settings->My::id()->put('crypt_lib', _crypt::OPENSSL);
				} else {
					$settings->My::id()->put('crypt_lib', '');
				}
				unset($settings);
			}
		}
		# version < 2.5.0-dev-r0001
		if(version_compare($oldVersion, '2.4.0-dev-r0001', '<')) {		// /!\ timeout possible for a lot of blogs
			$rs = dcCore::app()->getBlogs();
			while ($rs->fetch()) {
				$settings = new dcSettings($rs->f('blog_id'));
				$settings->addNamespace(My::id());
				$settings->My::id()->put('header_code', _crypt::encrypt(_crypt::decrypt($settings->My::id()->get('header_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::NO_CRYPT));
				$settings->My::id()->put('footer_code', _crypt::encrypt(_crypt::decrypt($settings->My::id()->get('footer_code'), _crypt::getCryptKey()), _crypt::getCryptKey(), _crypt::NO_CRYPT));
				$settings->My::id()->put('crypt_lib', _crypt::NO_CRYPT);
				unset($settings);
			}
		}
	}

}