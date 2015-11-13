<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::check('admin');

if(!$core->dcScript->settings('enabled') && is_file(path::real($core->dcScript->info('root').'/_config.php'))) {
	if($core->dcScript->checkConfig()) {
		$core->adminurl->redirect('admin.plugins', array(
			'module' => $core->dcScript->info('id'),'conf' => 1, 'redir' => $core->adminurl->get($core->dcScript->info('adminUrl'))
		));
	} else {
		dcPage::addNotice('message', sprintf(__('%s plugin is not configured.'), $core->dcScript->info('name')));
		$core->adminurl->redirect('admin.home');
	}
}

try {
	# submit tab 1
	if (isset($_POST['update_header'])) {
		//$core->dcScript->settings('header_code', base64_encode(trim($_POST['header_code'])));
		$core->dcScript->settings('header_code', $core->dcScript->encrypt(trim($_POST['header_code'])));
		$core->blog->triggerBlog();
		dcPage::addSuccessNotice(__('Code successfully updated.'));
		$core->adminurl->redirect($core->dcScript->info('adminUrl'), array(), '#tab-1');
	}
	# submit tab 2
	if (isset($_POST['update_footer'])) {
		//$core->dcScript->settings('footer_code', base64_encode(trim($_POST['footer_code'])));
		$core->dcScript->settings('footer_code', $core->dcScript->encrypt(trim($_POST['footer_code'])));
		$core->blog->triggerBlog();
		dcPage::addSuccessNotice(__('Code successfully updated.'));
		$core->adminurl->redirect($core->dcScript->info('adminUrl'), array(), '#tab-2');
	}
} catch(exception $e) {
		//$core->error->add($e->getMessage());
		$core->error->add(__('Unable to save the code'));
}

try {
	# download code
	if(isset($_GET['download']) && in_array($_GET['download'], array('header', 'footer'), true)) {
		$filename = '"'.trim($core->blog->name).'_'.date('Y-m-d').'_'.$_GET['download'].'.'.trim($core->dcScript->settings('backup_ext'),'.').'"';
		header('Content-Disposition: attachment;filename='.$filename);
		header('Content-Type: text/plain; charset=UTF-8');
		echo base64_decode($core->dcScript->settings($_GET['download'].'_code'));
		exit;
	}		
} catch(exception $e) {
		//$core->error->add($e->getMessage());
		$core->error->add(__('Unable to save the file'));
}

?>
<html>
	<head>
		<?php
			echo '<title>'.html::escapeHTML($core->dcScript->info('name')).'</title>';
			// Begin CodeMirror
			echo $core->dcScript->cssLoad('/codemirror/codemirror-custom.css');
			echo $core->dcScript->jsLoad('/codemirror/codemirror-compressed.js');
			echo $core->dcScript->jsLoad('/inc/admin.js');
			// End CodeMirror
			echo $core->dcScript->cssLoad('/inc/style.css');
			echo dcPage::jsConfirmClose('dcScript-form-tab-1','dcScript-form-tab-2');
			echo dcPage::jsPageTabs(isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'tab-1');
		?>
	</head>
	<body class="dcscript no-js">
		<?php
			// Baseline
			echo $core->dcScript->adminBaseline();
			// admin forms
			# Tab 1
			echo
				'<div class="multi-part" id="tab-1" title="'.__('Header code').' - ('.($core->dcScript->settings('header_code_enabled') ? __('Enabled') : __('Disabled')).')">
					<form action="'.html::escapeHTML($core->adminurl->get($core->dcScript->info('adminUrl'))).'" method="post" id="'.html::escapeHTML($core->dcScript->info('id')).'-form-tab-1">
						<p>'.$core->formNonce().'</p>
						<p>'.form::hidden('change_header','')/*for check change in CodeMirror => jsConfirmClose()*/.'</p>
						<p>'.form::textArea('header_code',120,25,html::escapeHTML($core->dcScript->decrypt($core->dcScript->settings('header_code'))),'maximal',0).'</p>
						<p class="button-bar clear">
							<input type="submit" id="update_header" name="update_header" title="'.__('Save the configuration').'" value="'.__('Save').'" />
							<input type="reset" title="'.__('Undo changes').'" value="'.__('Cancel').'" />
							<a id="export_header" class="button" title="'.__('Export').'" href="'.$core->adminurl->get($core->dcScript->info('adminUrl'), array('download' => 'header')).'">'.__('Download').'</a>
						</p>
					</form>
				</div>
			';
			# Tab 2
			echo
				'<div class="multi-part" id="tab-2" title="'.__('Footer code').' - ('.($core->dcScript->settings('footer_code_enabled') ? __('Enabled') : __('Disabled')).')">
					<form action="'.html::escapeHTML($core->adminurl->get($core->dcScript->info('adminUrl'))).'" method="post" id="'.html::escapeHTML($core->dcScript->info('id')).'-form-tab-2">
						<p>'.$core->formNonce().'</p>
						<p>'.form::hidden('change_footer','')/*for check change in CodeMirror => jsConfirmClose()*/.'</p>
						<p>'.form::textArea('footer_code',120,25,html::escapeHTML($core->dcScript->decrypt($core->dcScript->settings('footer_code'))),'maximal',0).'</p>
						<p class="button-bar clear">
							<input type="submit" id="update_footer" name="update_footer" title="'.__('Save the configuration').'" value="'.__('Save').'" />
							<input type="reset" title="'.__('Undo changes').'" value="'.__('Cancel').'" />
							<a id="export_footer" class="button" title="'.__('Export').'" href="'.$core->adminurl->get($core->dcScript->info('adminUrl'), array('download' => 'footer')).'">'.__('Download').'</a>
						</p>
					</form>
				</div>
			';
			// Footer plugin
			echo $core->dcScript->adminFooterInfo();
			// helpBlock
			dcPage::helpBlock('dcScript-edit'); 
		?>
	</body>
</html>
