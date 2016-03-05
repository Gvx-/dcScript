<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

__('dcScript');						// plugin name
__('Add script for DC 2.8+');		// description plugin

class dcScript extends dcPluginHelper025 {
	
	public static function publicHeadContent($core, $_ctx) {
		$html = self::decrypt($core->dcScript->settings('header_code'), $core->dcScript->getCryptKey());
		if($core->dcScript->settings('enabled') && $core->dcScript->settings('header_code_enabled') && !empty($html)) {
			echo "<!-- dcScript header begin -->\n".$html."\n<!-- dcScript header end -->";
		}
	}

	public static function publicFooterContent($core, $_ctx) {
		$html = self::decrypt($core->dcScript->settings('footer_code'), $core->dcScript->getCryptKey());
		if($core->dcScript->settings('enabled') && $core->dcScript->settings('footer_code_enabled') && !empty($html)) {
			echo "<!-- dcScript footer begin -->\n".$html."\n<!-- dcScript footer end -->";
		}
	}

	public static function encrypt($str, $key) {
		$key = pack('H*', hash('sha256', $key));
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, $iv)));
	}

	public static function decrypt($str, $key) {
		$key = pack('H*', hash('sha256', $key));
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($str), MCRYPT_MODE_ECB, $iv));
	}
	
	public function getCryptKey($salt=DC_MASTER_KEY) {
		return sha1($_SERVER['HTTP_HOST'].$salt);
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
	}

	protected function installActions($old_version) {
		# upgrade previous versions
		if(!empty($old_version)) {
			# version < 2
			if(version_compare($old_version, '2', '<')) {		// /!\ timeout possible for a lot blogs
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
			if(version_compare($old_version, '2.0.0-r0143', '<')) {		// /!\ timeout possible for a lot blogs
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
		}
	}

}
