<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2020 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DECRYPTION_PAGE')) { return; }

$header = html::escapeHTML(dcScript::decrypt($core->dcScript->settings('header_code'), $core->dcScript->getCryptKey(), $core->dcScript->getCryptLib()));
$footer = html::escapeHTML(dcScript::decrypt($core->dcScript->settings('footer_code'), $core->dcScript->getCryptKey(), $core->dcScript->getCryptLib()));
$formAction = html::escapeHTML($core->adminurl->get($core->dcScript->info('adminUrl')));
$downloadHeader = $core->adminurl->get($core->dcScript->info('adminUrl'), array('download' => 'header'));
$downloadFooter = $core->adminurl->get($core->dcScript->info('adminUrl'), array('download' => 'footer'));

?>
<html>
	<head>
		<?php
			echo '<title>'.html::escapeHTML($core->dcScript->info('name')).'</title>';
			// Begin CodeMirror
			echo $core->dcScript->cssLoad('/codemirror/lib/codemirror.css');
			echo $core->dcScript->jsLoad('/codemirror/lib/codemirror.js');
			echo $core->dcScript->jsLoad('/codemirror/mode/css/css.js');
			echo $core->dcScript->jsLoad('/codemirror/mode/htmlmixed/htmlmixed.js');
			echo $core->dcScript->jsLoad('/codemirror/mode/javascript/javascript.js');
			echo $core->dcScript->jsLoad('/codemirror/mode/xml/xml.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/comment/comment.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/dialog/dialog.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/display/fullscreen.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/edit/matchbrackets.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/edit/matchtags.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/edit/trailingspace.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/fold/brace-fold.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/fold/comment-fold.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/fold/foldcode.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/fold/foldgutter.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/fold/indent-fold.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/fold/xml-fold.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/search/search.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/search/searchcursor.js');
			echo $core->dcScript->jsLoad('/codemirror/addon/selection/active-line.js');
			// End CodeMirror
			echo $core->dcScript->jsLoad('/inc/admin.js');
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
					<form action="'.$formAction.'" method="post" id="'.html::escapeHTML($core->dcScript->info('id')).'-form-header">
						<p>'.$core->formNonce().'</p>
						<p>'.form::hidden('change_header', '')/*for check change in CodeMirror => jsConfirmClose()*/.'</p>
						<p>'.form::textArea('header_code', 120, 25, $header."\n", 'maximal', 0).'</p>
						<p class="button-bar clear">
							<input type="submit" id="update_header" name="update_header" title="'.__('Save the configuration').'" value="'.__('Save').'" />
							<input type="reset" id="reset_header" name="reset_header" title="'.__('Undo changes').'" value="'.__('Cancel').'" />
							<a id="export_header" class="button" title="'.__('Export').'" href="'.$downloadHeader.'">'.__('Download').'</a>
						</p>
					</form>
				</div>
			';
			# Tab 2
			echo
				'<div class="multi-part" id="tab-2" title="'.__('Footer code').' - ('.($core->dcScript->settings('footer_code_enabled') ? __('Enabled') : __('Disabled')).')">
					<form action="'.$formAction.'" method="post" id="'.html::escapeHTML($core->dcScript->info('id')).'-form-footer">
						<p>'.$core->formNonce().'</p>
						<p>'.form::hidden('change_footer', '')/*for check change in CodeMirror => jsConfirmClose()*/.'</p>
						<p>'.form::textArea('footer_code', 120, 25, $footer."\n", 'maximal', 0).'</p>
						<p class="button-bar clear">
							<input type="submit" id="update_footer" name="update_footer" title="'.__('Save the configuration').'" value="'.__('Save').'" />
							<input type="reset" id="reset_footer" name="reset_footer" title="'.__('Undo changes').'" value="'.__('Cancel').'" />
							<a id="export_footer" class="button" title="'.__('Export').'" href="'.$downloadFooter.'">'.__('Download').'</a>
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
