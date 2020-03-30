<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2020Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::checkSuper();

if (isset($_POST['save'])) {
	try {
		$core->dcScript->settings('enabled', !empty($_POST['enabled']), true);
		$core->dcScript->settings('header_code_enabled', !empty($_POST['header_code_enabled']), true);
		$core->dcScript->settings('footer_code_enabled', !empty($_POST['footer_code_enabled']), true);
		$core->dcScript->settings('backup_ext', html::escapeHTML($_POST['backup']), true);
		$core->blog->triggerBlog();
		dcPage::addSuccessNotice(__('Configuration successfully updated.'));
	} catch(exception $e) {
		//$core->error->add($e->getMessage());
		$core->error->add(__('Unable to save the configuration'));
	}
	if(!empty($_GET['redir']) && strpos($_GET['redir'], 'p='.$core->dcScript->info('id')) === false) {
		$core->error->add(__('Redirection not found'));
		$core->adminurl->redirect('admin.home');
	}
	http::redirect($_REQUEST['redir']);
}

echo
	'<div class="fieldset">
		<h3>'.__('Activation').'</h3>
		<p>
			'.form::checkbox('enabled','1',$core->dcScript->settings('enabled', null, true)).
			'<label class="classic" for="enabled">
				'.sprintf(__('Enable %s on this blog'), html::escapeHTML(__($core->dcScript->info('name')))).
			'</label>
		</p>
		<p class="form-note">'.__('Enable the plugin on this blog.').'</p>
	</div>
	<div id="options">
		<div class="fieldset">
			<h3>'.__('Active codes').'</h3>
			<div class="two-cols clear">
				<div class="col">
					<p>
						'.form::checkbox('header_code_enabled','1',$core->dcScript->settings('header_code_enabled', null, true))
						.'<label class="classic" for="header_code_enabled">'.__('Enable header code').'</label>
					</p>
					<p class="form-note">'.__('Enable public header code.').'</p>
				</div>
				<div class="col">
					<p>
						'.form::checkbox('footer_code_enabled','1',$core->dcScript->settings('footer_code_enabled', null, true))
						.'<label class="classic" for="footer_code_enabled">'.__('Enable footer code').'</label>
					</p>
					<p class="form-note">'.__('Enable public footer code.').'</p>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="fieldset clear">
			<h3>'.__('Options').'</h3>
			<p>
				<label class="classic" for="backup">'.__('Extension Backup Files').' : </label>
				'.form::field('backup',25,255,$core->dcScript->settings('backup_ext', null, true),'classic').'
			</p>
			<p class="form-note">'.__('Default extension backup files.').'</p>
		</div>
	</div>
';
dcPage::helpBlock('dcScript-config');
