<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2020 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

__('dcScript');						// plugin name
__('Add script for DC 2.8+');		// description plugin

class dcScript extends dcPluginHelper024b {

	### Constants ###
	const MCRYPT = 'mcrypt';
	const OPENSSL = 'openssl';
	const OPENSSL_METHOD = 'AES-256-CBC';

	public static function publicHeadContent($core, $_ctx) {
		if(version_compare(PHP_VERSION, '7.2', '>=') && ($core->dcScript->settings('crypt_lib') != self::OPENSSL)) { return; }
		$html = self::decrypt($core->dcScript->settings('header_code'), $core->dcScript->getCryptKey());
		if($core->dcScript->settings('enabled') && $core->dcScript->settings('header_code_enabled') && !empty($html)) {
			echo "<!-- dcScript header begin -->\n".$html."\n<!-- dcScript header end -->\n";
		}
	}

	public static function publicFooterContent($core, $_ctx) {
		if(version_compare(PHP_VERSION, '7.2', '>=') && ($core->dcScript->settings('crypt_lib') != self::OPENSSL)) { return; }
		$html = self::decrypt($core->dcScript->settings('footer_code'), $core->dcScript->getCryptKey());
		if($core->dcScript->settings('enabled') && $core->dcScript->settings('footer_code_enabled') && !empty($html)) {
			echo "<!-- dcScript footer begin -->\n".$html."\n<!-- dcScript footer end -->\n";
		}
	}

	public static function encrypt($str, $key, $cryptLib=self::OPENSSL) {
		global $core;
		$key = pack('H*', hash('sha256', $key));
		if($cryptLib == self::MCRYPT) { // REMOVED in PHP 7.2
			if(version_compare(PHP_VERSION, '7.2', '>=')) { throw new Exception(__('Encryption incompatible with PHP 7.2 and more')); }
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
			return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, $iv)));
		} elseif($cryptLib == self::OPENSSL) {
			$ivlen = openssl_cipher_iv_length(self::OPENSSL_METHOD);
			$iv = openssl_random_pseudo_bytes($ivlen);
			return trim(base64_encode($iv.openssl_encrypt($str, self::OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, $iv)));
		} else { // unknown cryptLib
			return self::encrypt($str, $key, $core->dcScript->getCryptLib());
		}
	}

	public static function decrypt($str, $key, $cryptLib=self::MCRYPT) {
		global $core;
		$key = pack('H*', hash('sha256', $key));
		if($cryptLib == self::MCRYPT) { // REMOVED in PHP 7.2
			if(version_compare(PHP_VERSION, '7.2', '>=')) { throw new Exception(__('Encryption incompatible with PHP 7.2 and more')); }
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($str), MCRYPT_MODE_ECB, $iv));
		} elseif($cryptLib == self::OPENSSL) {
			$ivlen = openssl_cipher_iv_length(self::OPENSSL_METHOD);
			$str = base64_decode($str);
			return trim(openssl_decrypt(substr($str, $ivlen), self::OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, substr($str, 0, $ivlen)));
		} else { // unknown cryptLib
			return self::decrypt($str, $key, $core->dcScript->getCryptLib());
		}
	}

	public function getCryptKey($salt=DC_MASTER_KEY) {
		return sha1($_SERVER['HTTP_HOST'].$salt);
	}

	Public function getCryptLib() {
		$lib =  $this->core->dcScript->settings('crypt_lib');
		return (empty($lib) ? (version_compare(PHP_VERSION, '7.2', '>=')? self::OPENSSL : self::MCRYPT) : $lib);
	}

	Public function debugGetinfos() {
		// debug
		$this->core->dcScript->debugLog('Header code', $this->core->dcScript->settings('header_code'));
		$this->core->dcScript->debugLog('Footer code', $this->core->dcScript->settings('footer_code'));
		//$this->core->dcScript->debugLog('Key crypt', pack('H*', hash('sha256', $this->core->dcScript->getCryptKey())));
		$this->core->dcScript->debugLog('Key crypt', hash('sha256', $this->core->dcScript->getCryptKey()));
		//$this->core->dcScript->debugLog('Key crypt', base64_decode(pack('H*', hash('sha256', $this->core->dcScript->getCryptKey()))));
	}

	protected function setDefaultSettings() {
		# create config plugin
		$this->core->blog->settings->addNamespace($this->plugin_id);
		$this->core->blog->settings->{$this->plugin_id}->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('header_code_enabled', false, 'boolean', __('Enable header code'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('footer_code_enabled', false, 'boolean', __('Enable footer code'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('header_code', self::encrypt('', $this->getCryptKey()), 'string', __('Header code'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('footer_code', self::encrypt('', $this->getCryptKey()), 'string', __('Footer code'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('backup_ext', '.html.txt', 'string', __('Extension Backup Files'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('crypt_lib', self::OPENSSL, 'string', __('Encryption library'), false, true);	// add v2.1.0
	}

	protected function installActions($old_version) {
		# upgrade previous versions
		if(!empty($old_version)) {

			# version < 2
			if(version_compare($old_version, '2', '<')) {		// /!\ timeout possible for a lot of blogs
				# upgrade global settings
				$this->core->blog->settings->{$this->plugin_id}->dropAll(true);
				$this->setDefaultSettings();
				# upgrade all blogs settings
				$rs = $this->core->getBlogs();
				while ($rs->fetch()) {
					$settings = new dcSettings($this->core, $rs->blog_id);
					$settings->addNamespace($this->plugin_id);
					$settings->{$this->plugin_id}->put('enabled', $settings->{$this->plugin_id}->get('enabled'));
					$settings->{$this->plugin_id}->put('header_code_enabled', $settings->{$this->plugin_id}->get('header_code_enabled'));
					$settings->{$this->plugin_id}->put('footer_code_enabled', $settings->{$this->plugin_id}->get('footer_code_enabled'));
					$settings->{$this->plugin_id}->put('header_code', self::encrypt(base64_decode($settings->{$this->plugin_id}->get('header_code')), DC_MASTER_KEY));
					$settings->{$this->plugin_id}->put('footer_code', self::encrypt(base64_decode($settings->{$this->plugin_id}->get('footer_code')), DC_MASTER_KEY));
					$settings->{$this->plugin_id}->put('backup_ext', $settings->{$this->plugin_id}->get('backup_ext'));
					unset($settings);
				}
				dcPage::addWarningNotice(__('Default settings update.'));
			}

			# version < 2.0.0-r0143
			if(version_compare($old_version, '2.0.0-r0143', '<')) {		// /!\ timeout possible for a lot of blogs
				# upgrade all blogs settings
				$rs = $this->core->getBlogs();
				while ($rs->fetch()) {
					$settings = new dcSettings($this->core, $rs->blog_id);
					$settings->addNamespace($this->plugin_id);
					$settings->{$this->plugin_id}->put('header_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('header_code'), DC_MASTER_KEY), $this->getCryptKey()));
					$settings->{$this->plugin_id}->put('footer_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('footer_code'), DC_MASTER_KEY), $this->getCryptKey()));
					unset($settings);
				}
			}

			# version < 2.1.1-dev-r0001
			if(version_compare($old_version, '2.1.1-dev-r0001', '<')) {		// /!\ timeout possible for a lot of blogs
				$this->core->blog->settings->{$this->plugin_id}->put('crypt_lib', self::OPENSSL, 'string', __('Encryption library'), false, true);
				# upgrade all blogs settings
				$rs = $this->core->getBlogs();
				while ($rs->fetch()) {
					$settings = new dcSettings($this->core, $rs->blog_id);
					$settings->addNamespace($this->plugin_id);
					if(version_compare(PHP_VERSION, '7.2', '<')) {
						$settings->{$this->plugin_id}->put('header_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('header_code'), $this->getCryptKey()), $this->getCryptKey(),self::OPENSSL));
						$settings->{$this->plugin_id}->put('footer_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('footer_code'), $this->getCryptKey()), $this->getCryptKey(),self::OPENSSL));
						$settings->{$this->plugin_id}->put('crypt_lib', self::OPENSSL);
					} else {
						$settings->{$this->plugin_id}->put('crypt_lib', '');
					}
					unset($settings);
				}
			}
		}
	}

}
