<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2020 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DECRYPTION_PAGE')) { return; }

?>
<html>
	<head>
		<?php
			echo '<title>'.html::escapeHTML($core->dcScript->info('name')).'</title>';
			echo $core->dcScript->cssLoad('/inc/style.css');
			echo $core->dcScript->jsLoad('/inc/index_warning.js');
			dcPage::addNotice('message', __('See help for the procedure'));
		?>
	</head>
	<body class="dcscript no-js">
		<!-- Baseline -->
		<?php echo $core->dcScript->adminBaseline(); ?>
		<!-- datas -->
		<div id="datas-deliver">
			<p id="key_crypt" class="copy-element"><?php echo hash('sha256', $core->dcScript->getCryptKey()); ?></p>
			<p id="header_code" class="copy-element"><?php echo $core->dcScript->settings('header_code'); ?></p>
			<p id="footer_code" class="copy-element"><?php echo $core->dcScript->settings('footer_code'); ?></p>
		</div>
		<!-- admin forms -->
		<div>
			<h3><?php echo __('Convert fields to new encryption format'); ?></h3>
			<p>
				<button type="button" id="copy_key_crypt"><?php echo __('Copy key'); ?></button>
				<button type="button" id="copy_header_code"><?php echo __('Copy header code'); ?></button>
				<button type="button" id="copy_footer_code"><?php echo __('Copy footer code'); ?></button>
				<a href="<?php echo DECRYPTION_PAGE; ?>" class="button" id="decrypt"><?php echo __('Decryption page'); ?></a>
			</p>
			<?php
				echo
					'<form action="'.html::escapeHTML($core->adminurl->get($core->dcScript->info('adminUrl'))).'" method="post">
						<p>'.$core->formNonce().'</p>
						<h4>'.__('Header code').'</h4>
						<p>'.form::textArea('header_code',120,9,'','maximal',0,false,'placeholder="'.__('Paste the code here').'"').'</p>
						<h4>'.__('Footer code').'</h4>
						<p>'.form::textArea('footer_code',120,9,'','maximal',0,false,'placeholder="'.__('Paste the code here').'"').'</p>
						<p class="button-bar clear">
							<input type="submit" id="later" name="later" title="'.__('later').'" value="'.__('later').'" />
							<input type="submit" id="convert" name="convert" title="'.__('Convert the configuration').'" value="'.__('Convert').'" />
						</p>
					</form>
				';
			?>
		</div>
		<!-- Footer plugin -->
		<?php
			echo $core->dcScript->adminFooterInfo();
			// helpBlock
			dcPage::helpBlock('dcScript-warning');
		?>
	</body>
</html>
