<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2020 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::check('dcScript.edit');
define('DECRYPTION_PAGE', 'http://promenade.temporelle.free.fr/tools/decrypt.php');

if(!$core->dcScript->settings('enabled') && is_file(path::real($core->dcScript->info('root').'/_config.php'))) {
	if($core->auth->isSuperAdmin()) {
		$core->adminurl->redirect('admin.plugins', array(
			'module' => $core->dcScript->info('id'),'conf' => 1, 'redir' => $core->adminurl->get($core->dcScript->info('adminUrl'))
		));
	} else {
		dcPage::addNotice('message', sprintf(__('%s plugin is not configured.'), $core->dcScript->info('name')));
		$core->adminurl->redirect('admin.home');
	}
}

if (!empty($_POST)) {
	try {
		# submit later (warning page)
		if (isset($_POST['later'])) {
			$core->adminurl->redirect('admin.home');
		}
		# submit convert (warning page)
		if (isset($_POST['convert'])) {
			$core->dcScript->settings('header_code', dcScript::encrypt(trim($_POST['header_code']), $core->dcScript->getCryptKey(), dcScript::OPENSSL));
			$core->dcScript->settings('footer_code', dcScript::encrypt(trim($_POST['footer_code']), $core->dcScript->getCryptKey(), dcScript::OPENSSL));
			$core->dcScript->settings('crypt_lib', dcScript::OPENSSL);
			$core->blog->triggerBlog();
			dcPage::addSuccessNotice(__('Code successfully updated.'));
			$core->adminurl->redirect($core->dcScript->info('adminUrl'), array(), '#tab-1');
		}
		# submit tab 1 (standard page)
		if (isset($_POST['update_header'])) {
			$core->dcScript->settings('header_code', dcScript::encrypt(trim($_POST['header_code'])."\n", $core->dcScript->getCryptKey(), dcScript::OPENSSL));
			$core->blog->triggerBlog();
			dcPage::addSuccessNotice(__('Code successfully updated.'));
			$core->adminurl->redirect($core->dcScript->info('adminUrl'), array(), '#tab-1');
		}
		# submit tab 2 (standard page)
		if (isset($_POST['update_footer'])) {
			$core->dcScript->settings('footer_code', dcScript::encrypt(trim($_POST['footer_code'])."\n", $core->dcScript->getCryptKey(), dcScript::OPENSSL));
			$core->blog->triggerBlog();
			dcPage::addSuccessNotice(__('Code successfully updated.'));
			$core->adminurl->redirect($core->dcScript->info('adminUrl'), array(), '#tab-2');
		}
	} catch(exception $e) {
		//$core->error->add($e->getMessage());
		$core->error->add(__('Unable to save the code'));
	}
}

if (!empty($_GET)) {
	try {
		# download code (standard page)
		if(isset($_GET['download']) && in_array($_GET['download'], array('header', 'footer'), true)) {
			$filename = '"'.trim($core->blog->name).'_'.date('Y-m-d').'_'.$_GET['download'].'.'.trim($core->dcScript->settings('backup_ext'),'.').'"';
			header('Content-Disposition: attachment;filename='.$filename);
			header('Content-Type: text/plain; charset=UTF-8');
			echo dcScript::decrypt($core->dcScript->settings($_GET['download'].'_code'), $core->dcScript->getCryptKey(), $core->dcScript->getCryptLib());
			exit;
		}
	} catch(exception $e) {
		//$core->error->add($e->getMessage());
		$core->error->add(__('Unable to save the file'));
	}
}

if(version_compare(PHP_VERSION, '7.2', '>=') && ($core->dcScript->settings('crypt_lib') != dcScript::OPENSSL)) {
	require_once 'index_warning.php';
} else {
	require_once 'index_std.php';
}
