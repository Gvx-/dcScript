<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

__('dcScript');						// plugin name
__('Add script for DC 2.9+');		// description plugin

class dcScript extends dcPluginHelper029 {

	# behaviors functions
	public function publicHeadContent($core, $_ctx) {$this->codeInsert('publicHeadContent');}
	public function publicFooterContent($core, $_ctx) { $this->codeInsert('publicFooterContent'); }
	public function publicEntryBeforeContent($core, $_ctx) { $this->codeInsert('publicEntryBeforeContent'); }
	public function publicEntryAfterContent($core, $_ctx) { $this->codeInsert('publicEntryAfterContent'); }
	public function publicCommentBeforeContent($core, $_ctx) { $this->codeInsert('publicCommentBeforeContent'); }
	public function publicCommentAfterContent($core, $_ctx) { $this->codeInsert('publicCommentAfterContent'); }
	public function publicCommentFormBeforeContent($core, $_ctx) { $this->codeInsert('publicCommentFormBeforeContent'); }
	public function publicCommentFormAfterContent($core, $_ctx) { $this->codeInsert('publicCommentFormAfterContent'); }
	public function publicPingBeforeContent($core, $_ctx) { $this->codeInsert('publicPingBeforeContent'); }
	public function publicPingAfterContent($core, $_ctx) { $this->codeInsert('publicPingAfterContent'); }
	public function publicTopAfterContent($core, $_ctx) { $this->codeInsert('publicTopAfterContent'); }
	public function publicInsideFooter($core, $_ctx) { $this->codeInsert('publicInsideFooter'); }

	# admin configuration page
	public function configPage() {
		if(!defined('DC_CONTEXT_ADMIN') || !$this->core->auth->check('admin', $this->core->blog->id)) { return; }
		$scope = $this->configScope();
		if (isset($_POST['save'])) {
			try {
				$behaviors = $this->settings('behaviors', null, $scope);
				foreach($behaviors as $k => &$v) {
					$v['enabled'] = !empty($_POST['enabled_'.$k]);
					$v['active'] = !empty($_POST['active_'.$k]);
				}
				$this->settings('enabled', !empty($_POST['enabled']), $scope);
				$this->settings('backup_ext', html::escapeHTML($_POST['backup']), $scope);
				$this->settings('behaviors', $behaviors, $scope);
				$this->core->blog->triggerBlog();
				dcPage::addSuccessNotice(__('Configuration successfully updated.'));
			} catch(exception $e) {
				//$this->core->error->add($e->getMessage());
				$this->core->error->add(__('Unable to save the configuration'));
			}
			if(!empty($_GET['redir']) && strpos($_GET['redir'], 'p='.$this->info('id')) === false) {
				$this->core->error->add(__('Redirection not found'));
				$this->core->adminurl->redirect('admin.home');
			}
			http::redirect($_REQUEST['redir']);
		}
		$behaviors = $this->settings('behaviors', null, $scope);
		$behaviors_table = '
			<div class="table-outer">
				<table>
					<caption class="hidden">'.__('Behaviors list').'</caption>
					<tbody>
						<tr>
							<th scope="col">'.__('Name').'</th>
							'.($this->core->auth->isSuperAdmin() ? '<th scope="col">'.__('Allowed').'</th>' : '').'
							<th scope="col">'.__('Enabled').'</th>
							<th scope="col">'.__('content').'</th>
							<th scope="col">'.''.'</th>
						</tr>
		';
		foreach($behaviors as $k => $v) {
			if($this->core->auth->isSuperAdmin() || $v['enabled']) {
				$behaviors_table .= '
					<tr class="line">
						<td class="nowrap">'.html::escapeHTML($k).'</td>
						'.($this->core->auth->isSuperAdmin() ? '<td class="nowrap">'.form::checkbox('enabled_'.$k, html::escapeHTML($k), $v['enabled']).'</td>' : '').'
						<td class="nowrap">'.form::checkbox('active_'.$k, html::escapeHTML($k), $v['active']).'</td>
						<td class="nowrap">'.(empty(html::escapeHTML($v['content'])) ? __('Empty') : __('Filled')).'</td>
						<td class="maximal nowrap">'.''.'</td>
					</tr>
				';
			}
		}
		$behaviors_table .= '
					</tbody>
				</table>
			</div>
		';
		echo
			$this->configBaseline($scope).
			'<div class="fieldset clear">
				<h3>'.__('Selecting behaviors').'</h3>
				'.$behaviors_table.'
				<p class="form-note">'.__('Allowed: Available for editing').'<br/>'.__('Enabled: Enable insertion in the public pages').'</p>
			</div>
			<div class="fieldset clear">
				<h3>'.__('Options').'</h3>
				<p>
					<label class="classic" for="backup">'.__('Extension Backup Files').' : </label>
					'.form::field('backup', 25, 255, $this->settings('backup_ext', null, $scope),'classic').'&nbsp;&nbsp;&nbsp;
					<span class="form-note">'.__('Default extension backup files.').'</span>
				</p>
			</div>
		';
		//dcPage::helpBlock('dcScript-config');
	}

	# admin index page
	public function indexPage() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		dcPage::check('dcScript.edit');
		if(!$this->settings('enabled') && is_file(path::real($this->info('root').'/_config.php'))) {
			if($$this->core->auth->check('admin', $this->core->blog->id)) {
				$this->core->adminurl->redirect('admin.plugins', array(
					'module' => $this->info('id'),'conf' => 1, 'redir' => $this->core->adminurl->get($this->info('adminUrl'))
				));
			} else {
				dcPage::addNotice('message', sprintf(__('%s plugin is not configured.'), $this->info('name')));
				$this->core->adminurl->redirect('admin.home');
			}
		}
		$behaviors_list = $this->settings('behaviors');
		$behaviors = array();
		foreach($behaviors_list as $k => &$v) {
			if($v['enabled']) {
				$behaviors[(empty($v['content']) ? '&#9647;' : '&#9646;').'&nbsp;'.($v['active'] ? '&#10004;' : '&#10008;').'&nbsp;-&nbsp;'.$k] = $k;
			}
		}
		//$this->debugLog('POST', $_POST);
		try {
			# submit
			if (isset($_POST['update'])) {
				$behaviors_list[$_POST['behavior_edit']]['content'] = trim($_POST['code']);
				$behaviors_list[$_POST['behavior_edit']]['active'] = empty($_POST['active']) ? false : true;
				$this->settings('behaviors', $behaviors_list);
				$this->core->blog->triggerBlog();
				dcPage::addSuccessNotice(__('Code successfully updated.'));
				$this->core->adminurl->redirect($this->info('adminUrl'), array('behaviors' => $_POST['behavior_edit']));
			}
		} catch(exception $e) {
			//$this->core->error->add($e->getMessage());
			$this->core->error->add(__('Unable to save the code'));
		}
		try {
			# Check selected behavior
			$behavior = isset($_REQUEST['behaviors']) ? $_REQUEST['behaviors'] : 'publicHeadContent';
		} catch(exception $e) {
			//$this->core->error->add($e->getMessage());
			$this->core->error->add(__('Unable to select the code'));
			$behavior = 'publicHeadContent';
		}
		try {
			# download code
			if(isset($_GET['download']) && in_array($_GET['download'], $behaviors, true)) {
				$filename = '"'.trim($this->core->blog->name).'_'.date('Y-m-d').'_'.$_GET['download'].'.'.trim($this->settings('backup_ext'),'.').'"';
				header('Content-Disposition: attachment;filename='.$filename);
				header('Content-Type: text/plain; charset=UTF-8');
				echo $behaviors_list[$_GET['download']]['content'];
				exit;
			}
		} catch(exception $e) {
			//$this->core->error->add($e->getMessage());
			$this->core->error->add(__('Unable to save the file'));
		}
		echo
			'<html>
				<head>
					<title>'.html::escapeHTML($this->info('name')).'</title>
					'.$this->cssLoad('/codemirror/codemirror-custom.css').NL.
					//$this->jsLoad('/codemirror/codemirror-custom.js').NL.
					$this->jsLoad('/codemirror/codemirror-compressed.js').NL.
					$this->jsLoad('/inc/index.js').NL.
					$this->cssLoad('/inc/index.css').NL.
					dcPage::jsConfirmClose('dcScript-form').NL.'
				</head>
				<body class="dcscript no-js">
					'.$this->adminBaseline().NL.'
					<form action="'.html::escapeHTML($this->core->adminurl->get($this->info('adminUrl'))).'" method="post" id="dcScript-form">
						<p>'.$this->core->formNonce().'</p>
						<p>'.form::hidden('behavior_edit', $behavior)/*behavior in edit*/.'</p>
						<p>'.form::hidden('change', '')/*for check change in CodeMirror => jsConfirmClose()*/.'</p>
						<p class="anchor-nav">
							<label class="classic">'.__('Behavior').'&nbsp;:&nbsp;
								'.form::combo('behaviors', $behaviors, html::escapeHTML($behavior)).'
								<input id="behaviors_go" name="behaviors_go" type="submit" value="'.__('Go').'" />
							</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="classic">'.form::checkbox('active', html::escapeHTML('1'), $behaviors_list[$behavior]['active']).__('Active').'</label>
						</p>
						<p>'.form::textArea('code', 120, 25, html::escapeHTML($behaviors_list[$behavior]['content']), 'maximal', 0).'</p>
						<p class="button-bar clear">
							<input type="submit" id="update" name="update" title="'.__('Save the configuration').'" value="'.__('Save').'" />
							<input type="reset" id="reset" title="'.__('Undo changes').'" value="'.__('Cancel').'" />
							<a id="export" class="button" title="'.__('Export').'" href="'.$this->core->adminurl->get($this->info('adminUrl'), array('download' => $behavior)).'">'.__('Download').'</a>
						</p>
					</form>
					'.$this->adminFooterInfo();
					dcPage::helpBlock('dcScript-edit');
		echo
			'	</body>
			</html>';
	}

	# protected functions deprecated (For upgrade from an older version)
	protected static function encrypt($str, $key) {
		$key = pack('H*', hash('sha256', $key));
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, $iv)));
	}

	protected static function decrypt($str, $key) {
		$key = pack('H*', hash('sha256', $key));
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($str), MCRYPT_MODE_ECB, $iv));
	}

	protected function getCryptKey($salt=DC_MASTER_KEY) {
		return sha1($_SERVER['HTTP_HOST'].$salt);
	}

	# protected functions
	protected function codeInsert($behavior) {
		$behaviors = $this->settings('behaviors');
		if($this->settings('enabled') && $behaviors[$behavior]['enabled'] && $behaviors[$behavior]['active'] && !empty($behaviors[$behavior]['content'])) {
			echo "<!-- dcScript ".html::escapeHTML($behavior)." begin -->\n".$behaviors[$behavior]['content']."\n<!-- dcScript ".html::escapeHTML($behavior)." end -->";
		}
	}

	protected function setDefaultSettings() {
		# create config plugin
		$behaviors = array(
			'publicHeadContent'					=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicFooterContent'				=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicEntryBeforeContent'			=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicEntryAfterContent'			=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicCommentBeforeContent'		=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicCommentAfterContent'			=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicCommentFormBeforeContent'	=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicCommentFormAfterContent'		=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicPingBeforeContent'			=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicPingAfterContent'			=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicTopAfterContent'				=> array('enabled' => true, 'active' => true, 'content' => ''),
			'publicInsideFooter'				=> array('enabled' => true, 'active' => true, 'content' => '')
		);
		$this->core->blog->settings->addNamespace($this->plugin_id);
		$this->core->blog->settings->{$this->plugin_id}->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('backup_ext', '.html.txt', 'string', __('Extension Backup Files'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('behaviors', $behaviors, 'array', __('behaviors array'), false, true);
		//$this->core->blog->settings->{$this->plugin_id}->put('behaviors', $behaviors, 'array', __('behaviors array'), true, true);		// debug
	}

	protected function installActions($old_version) {
		# upgrade previous versions - /!\ be exceeded timeout for a lot blogs
		if(!empty($old_version)) {
			try {
				# version < 2
				if(version_compare($old_version, '2', '<')) {
					# upgrade global settings
					$this->core->blog->settings->{$this->plugin_id}->dropAll(true);
					$this->setDefaultSettings();
					# upgrade all blogs settings
					$rs = $this->core->getBlogs();
					while ($rs->fetch()) {
						$settings = new dcSettings($this->core, $rs->blog_id);
						$settings->addNamespace($rs->blog_id);
						$settings->{$this->plugin_id}->put('enabled', $settings->{$this->plugin_id}->get('enabled'));
						$settings->{$this->plugin_id}->put('header_code_enabled', $settings->{$this->plugin_id}->get('header_code_enabled'));
						$settings->{$this->plugin_id}->put('footer_code_enabled', $settings->{$this->plugin_id}->get('footer_code_enabled'));
						$settings->{$this->plugin_id}->put('header_code', self::encrypt(base64_decode($settings->{$this->plugin_id}->get('header_code')), DC_MASTER_KEY));
						$settings->{$this->plugin_id}->put('footer_code', self::encrypt(base64_decode($settings->{$this->plugin_id}->get('footer_code')), DC_MASTER_KEY));
						$settings->{$this->plugin_id}->put('backup_ext', $settings->{$this->plugin_id}->get('backup_ext'));
						unset($settings);
					}
					unset($rs);
				}
				# version < 2.0.0-r0143
				if(version_compare($old_version, '2.0.0-r0143', '<')) {
					# upgrade all blogs settings
					$rs = $this->core->getBlogs();
					while ($rs->fetch()) {
						$settings = new dcSettings($this->core, $rs->blog_id);
						$settings->addNamespace($this->plugin_id);
						$settings->{$this->plugin_id}->put('header_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('header_code'), DC_MASTER_KEY), $this->getCryptKey()));
						$settings->{$this->plugin_id}->put('footer_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('footer_code'), DC_MASTER_KEY), $this->getCryptKey()));
						unset($settings);
					}
					unset($rs);
				}
				# version < 3
				if(version_compare($old_version, '3', '<')) {
					$dir_var = $this->getVarDir('/dcScript/backup');
					$rs = $this->core->getBlogs();
					while ($rs->fetch()) {
						$settings = new dcSettings($this->core, $rs->blog_id);
						$settings->addNamespace($this->plugin_id);
						$behaviors = $settings->{$this->plugin_id}->get('behaviors');
						$html = self::decrypt($settings->{$this->plugin_id}->get('header_code'), $this->getCryptKey());
						if(!empty($html)) {
							@file_put_contents($dir_var.'/'.$rs->blog_id.'-header_code.html.txt', $html);	// security backup
							$behaviors['publicHeadContent']['content'] = $html;
							$behaviors['publicHeadContent']['active'] = $settings->{$this->plugin_id}->get('header_code_enabled');
						}
						$html = self::decrypt($settings->{$this->plugin_id}->get('footer_code'), $this->getCryptKey());
						if(!empty($html)) {
							@file_put_contents($dir_var.'/'.$rs->blog_id.'-footer_code.html.txt', $html);	// security backup
							$behaviors['publicFooterContent']['content'] = $html;
							$behaviors['publicFooterContent']['active'] = $settings->{$this->plugin_id}->get('footer_code_enabled');
						}
						$settings->{$this->plugin_id}->put('behaviors', $behaviors);
						# erase old settings
						$settings->{$this->plugin_id}->drop('header_code_enabled');
						$settings->{$this->plugin_id}->drop('footer_code_enabled');
						$settings->{$this->plugin_id}->drop('header_code');
						$settings->{$this->plugin_id}->drop('footer_code');
						unset($settings, $behaviors);
					}
					unset($rs, $dir_var);
				}
			} catch(Exception $e) {
				$core->error->add(__('Something went wrong with auto upgrade:').' '.$e->getMessage());
			}
		}
	}

}
