<?php
/**
  * This file is part of dcScript plugin for Dotclear 2.
  *
  * @package Dotclear\plungin\dcScript
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2014-2022 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if(!defined('DC_RC_PATH')) { return; }

if(Clearbricks::lib()->autoloadSource('dcPluginHelper224') == false) { Clearbricks::lib()->autoload(['dcPluginHelper224' => __DIR__ . '/_dcPluginHelper/class.dcPluginHelper224.php']); }

define('DECRYPTION_PAGE', 'http://promenade.temporelle.free.fr/tools/decrypt.php');

__('dcScript');						// plugin name
__('Add script for DC');			// description plugin

//class dcScript extends dcPluginHelper224 {
class dcScript extends dcPluginHelper224 {

	### Constants ###
	const MCRYPT = 'mcrypt';
	const OPENSSL = 'openssl';
	const OPENSSL_METHOD = 'AES-256-CBC';

	/**
	 * publicHeadContent
	 *
	 * @param  object $core
	 * @param  object $_ctx
	 * @return void
	 */
	public static function publicHeadContent($core, $_ctx) {
		if(dcCore::app()->dcScript->settings('crypt_lib') != self::OPENSSL) { return; }
		$html = self::decrypt(dcCore::app()->dcScript->settings('header_code'), dcCore::app()->dcScript->getCryptKey(), dcCore::app()->dcScript->settings('crypt_lib'));
		if(dcCore::app()->dcScript->settings('enabled') && dcCore::app()->dcScript->settings('header_code_enabled') && !empty($html)) {
			echo "<!-- dcScript header begin -->\n".$html."\n<!-- dcScript header end -->\n";
		}
	}

	/**
	 * publicFooterContent
	 *
	 * @param  object $core
	 * @param  object $_ctx
	 * @return void
	 */
	public static function publicFooterContent($core, $_ctx) {
		if(dcCore::app()->dcScript->settings('crypt_lib') != self::OPENSSL) { return; }
		$html = self::decrypt(dcCore::app()->dcScript->settings('footer_code'), dcCore::app()->dcScript->getCryptKey(), dcCore::app()->dcScript->settings('crypt_lib'));
		if(dcCore::app()->dcScript->settings('enabled') && dcCore::app()->dcScript->settings('footer_code_enabled') && !empty($html)) {
			echo "<!-- dcScript footer begin -->\n".$html."\n<!-- dcScript footer end -->\n";
		}
	}

	/**
	 * encrypt
	 *
	 * @param  string $str
	 * @param  string $key
	 * @param  string $cryptLib
	 * @return string
	 */
	public static function encrypt($str, $key, $cryptLib=self::OPENSSL) {
		global $core;
		$key = pack('H*', hash('sha256', $key));
		if($cryptLib == self::OPENSSL) {
			$ivlen = openssl_cipher_iv_length(self::OPENSSL_METHOD);
			$iv = openssl_random_pseudo_bytes($ivlen);
			return trim(base64_encode($iv.openssl_encrypt($str, self::OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, $iv)));
		} else { // unknown cryptLib
			return self::encrypt($str, $key, dcCore::app()->dcScript->getCryptLib());
		}
	}

	/**
	 * decrypt
	 *
	 * @param  string $str
	 * @param  string $key
	 * @param  string $cryptLib
	 * @return string
	 */
	public static function decrypt($str, $key, $cryptLib=self::OPENSSL) {
		global $core;
		$key = pack('H*', hash('sha256', $key));
		if($cryptLib == self::OPENSSL) {
			$ivlen = openssl_cipher_iv_length(self::OPENSSL_METHOD);
			$str = base64_decode($str);
			return trim(openssl_decrypt(substr($str, $ivlen), self::OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, substr($str, 0, $ivlen)));
		} else { // unknown cryptLib
			return self::decrypt($str, $key, dcCore::app()->dcScript->getCryptLib());
		}
	}

	/**
	 * getCryptKey
	 *
	 * @param  string $salt
	 * @return string
	 */
	public function getCryptKey($salt=DC_MASTER_KEY) {
		return sha1($_SERVER['HTTP_HOST'].$salt);
	}

	/**
	 * getCryptLib
	 *
	 * @return string
	 */
	Public function getCryptLib() {
		$lib =  $this->settings('crypt_lib');
		return (empty($lib) ? self::OPENSSL : $lib);
	}

	Public function debugGetinfos() {
		// debug
		$this->debugLog('Header code', $this->settings('header_code'));
		$this->debugLog('Footer code', $this->settings('footer_code'));
		//$this->debugLog('Key crypt', pack('H*', hash('sha256', $this->getCryptKey())));
		$this->debugLog('Key crypt', hash('sha256', $this->getCryptKey()));
		//$this->debugLog('Key crypt', base64_decode(pack('H*', hash('sha256', $this->getCryptKey()))));
	}

	protected function setDefaultSettings() {
		# create config plugin
		dcCore::app()->blog->settings->addNamespace($this->plugin_id);
		dcCore::app()->blog->settings->{$this->plugin_id}->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		dcCore::app()->blog->settings->{$this->plugin_id}->put('header_code_enabled', false, 'boolean', __('Enable header code'), false, true);
		dcCore::app()->blog->settings->{$this->plugin_id}->put('footer_code_enabled', false, 'boolean', __('Enable footer code'), false, true);
		dcCore::app()->blog->settings->{$this->plugin_id}->put('header_code', self::encrypt('', $this->getCryptKey()), 'string', __('Header code'), false, true);
		dcCore::app()->blog->settings->{$this->plugin_id}->put('footer_code', self::encrypt('', $this->getCryptKey()), 'string', __('Footer code'), false, true);
		dcCore::app()->blog->settings->{$this->plugin_id}->put('backup_ext', '.html.txt', 'string', __('Extension Backup Files'), false, true);
		dcCore::app()->blog->settings->{$this->plugin_id}->put('crypt_lib', self::OPENSSL, 'string', __('Encryption library'), false, true);	// add v2.1.0
	}

	protected function installActions($old_version) {
		# upgrade previous versions
		if(!empty($old_version)) {

			# version < 2
			if(version_compare($old_version, '2', '<')) {		// /!\ timeout possible for a lot of blogs
				# upgrade global settings
				dcCore::app()->blog->settings->{$this->plugin_id}->dropAll(true);
				$this->setDefaultSettings();
				# upgrade all blogs settings
				$rs = dcCore::app()->getBlogs();
				while ($rs->fetch()) {
					$settings = new dcSettings($rs->blog_id);
					$settings->addNamespace($this->plugin_id);
					$settings->{$this->plugin_id}->put('enabled', $settings->{$this->plugin_id}->get('enabled'));
					$settings->{$this->plugin_id}->put('header_code_enabled', $settings->{$this->plugin_id}->get('header_code_enabled'));
					$settings->{$this->plugin_id}->put('footer_code_enabled', $settings->{$this->plugin_id}->get('footer_code_enabled'));
					$settings->{$this->plugin_id}->put('header_code', self::encrypt(base64_decode($settings->{$this->plugin_id}->get('header_code')), DC_MASTER_KEY));
					$settings->{$this->plugin_id}->put('footer_code', self::encrypt(base64_decode($settings->{$this->plugin_id}->get('footer_code')), DC_MASTER_KEY));
					$settings->{$this->plugin_id}->put('backup_ext', $settings->{$this->plugin_id}->get('backup_ext'));
					unset($settings);
				}
				dcAdminNotices::addWarningNotice(__('Default settings update.'));
			}

			# version < 2.0.0-r0143
			if(version_compare($old_version, '2.0.0-r0143', '<')) {		// /!\ timeout possible for a lot of blogs
				# upgrade all blogs settings
				$rs = dcCore::app()->getBlogs();
				while ($rs->fetch()) {
					$settings = new dcSettings($rs->blog_id);
					$settings->addNamespace($this->plugin_id);
					$settings->{$this->plugin_id}->put('header_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('header_code'), DC_MASTER_KEY), $this->getCryptKey()));
					$settings->{$this->plugin_id}->put('footer_code', self::encrypt(self::decrypt($settings->{$this->plugin_id}->get('footer_code'), DC_MASTER_KEY), $this->getCryptKey()));
					unset($settings);
				}
			}

			# version < 2.1.1-dev-r0001
			if(version_compare($old_version, '2.1.1-dev-r0001', '<')) {		// /!\ timeout possible for a lot of blogs
				dcCore::app()->blog->settings->{$this->plugin_id}->put('crypt_lib', self::OPENSSL, 'string', __('Encryption library'), false, true);
				# upgrade all blogs settings
				$rs = dcCore::app()->getBlogs();
				while ($rs->fetch()) {
					$settings = new dcSettings($rs->blog_id);
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

	public function index() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		dcPage::check(dcCore::app()->auth->makePermissions([dcScriptPerms::EDIT]));
		if(!$this->settings('enabled') && is_file(path::real($this->info('root').'/_config.php'))) {
			if(dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]), dcCore::app()->blog->id)) {
				dcCore::app()->adminurl->redirect('admin.plugins', array(
					'module' => $this->info('id'),'conf' => 1, 'redir' => dcCore::app()->adminurl->get($this->info('adminUrl'))
				));
			} else {
				dcAdminNotices::addMessageNotice(sprintf(__('%s plugin is not configured.'), $this->info('name')));
				dcCore::app()->adminurl->redirect('admin.home');
			}
		}
		if (!empty($_POST)) {
			try {
				# submit later (warning page)
				if (isset($_POST['later'])) {
					dcCore::app()->adminurl->redirect('admin.home');
				}
				# submit convert (warning page)
				if (isset($_POST['convert'])) {
					$this->settings('header_code', dcScript::encrypt(trim($_POST['header_code']), $this->getCryptKey(), dcScript::OPENSSL));
					$this->settings('footer_code', dcScript::encrypt(trim($_POST['footer_code']), $this->getCryptKey(), dcScript::OPENSSL));
					$this->settings('crypt_lib', dcScript::OPENSSL);
					dcCore::app()->blog->triggerBlog();
					dcAdminNotices::addSuccessNotice(__('Code successfully updated.'));
					dcCore::app()->adminurl->redirect($this->info('adminUrl'), array(), '#tab-1');
				}
				# submit tab 1 (standard page)
				if (isset($_POST['update_header'])) {
					$this->settings('header_code', dcScript::encrypt(trim($_POST['header_code'])."\n", $this->getCryptKey(), dcScript::OPENSSL));
					dcCore::app()->blog->triggerBlog();
					dcAdminNotices::addSuccessNotice(__('Code successfully updated.'));
					dcCore::app()->adminurl->redirect($this->info('adminUrl'), array(), '#tab-1');
				}
				# submit tab 2 (standard page)
				if (isset($_POST['update_footer'])) {
					$this->settings('footer_code', dcScript::encrypt(trim($_POST['footer_code'])."\n", $this->getCryptKey(), dcScript::OPENSSL));
					dcCore::app()->blog->triggerBlog();
					dcAdminNotices::addSuccessNotice(__('Code successfully updated.'));
					dcCore::app()->adminurl->redirect($this->info('adminUrl'), array(), '#tab-2');
				}
			} catch(exception $e) {
				//dcCore::app()->error->add($e->getMessage());
				dcCore::app()->error->add(__('Unable to save the code'));
			}
		}

		if (!empty($_GET)) {
			try {
				# download code (standard page)
				if(isset($_GET['download']) && in_array($_GET['download'], array('header', 'footer'), true)) {
					$filename = '"'.trim(dcCore::app()->blog->name).'_'.date('Y-m-d').'_'.$_GET['download'].'.'.trim($this->settings('backup_ext'),'.').'"';
					header('Content-Disposition: attachment;filename='.$filename);
					header('Content-Type: text/plain; charset=UTF-8');
					echo dcScript::decrypt($this->settings($_GET['download'].'_code'), $this->getCryptKey(), $this->getCryptLib());
					exit;
				}
			} catch(exception $e) {
				//dcCore::app()->error->add($e->getMessage());
				dcCore::app()->error->add(__('Unable to save the file'));
			}
		}

		$header = html::escapeHTML(dcScript::decrypt($this->settings('header_code'), $this->getCryptKey(), $this->getCryptLib()));
		$footer = html::escapeHTML(dcScript::decrypt($this->settings('footer_code'), $this->getCryptKey(), $this->getCryptLib()));
		$formAction = html::escapeHTML(dcCore::app()->adminurl->get($this->info('adminUrl')));
		$downloadHeader = dcCore::app()->adminurl->get($this->info('adminUrl'), array('download' => 'header'));
		$downloadFooter = dcCore::app()->adminurl->get($this->info('adminUrl'), array('download' => 'footer'));

		echo '<html>'.NL;
		echo '<head>'.NL;
		echo '<title>'.html::escapeHTML($this->info('name')).'</title>'.NL;
		// Begin CodeMirror
		echo $this->cssLoad('/codemirror/lib/codemirror.css');
		echo $this->jsLoad('/codemirror/lib/codemirror.js');
		echo $this->jsLoad('/codemirror/mode/css/css.js');
		echo $this->jsLoad('/codemirror/mode/htmlmixed/htmlmixed.js');
		echo $this->jsLoad('/codemirror/mode/javascript/javascript.js');
		echo $this->jsLoad('/codemirror/mode/xml/xml.js');
		echo $this->jsLoad('/codemirror/addon/comment/comment.js');
		echo $this->jsLoad('/codemirror/addon/dialog/dialog.js');
		echo $this->jsLoad('/codemirror/addon/display/fullscreen.js');
		echo $this->jsLoad('/codemirror/addon/edit/matchbrackets.js');
		echo $this->jsLoad('/codemirror/addon/edit/matchtags.js');
		echo $this->jsLoad('/codemirror/addon/edit/trailingspace.js');
		echo $this->jsLoad('/codemirror/addon/fold/brace-fold.js');
		echo $this->jsLoad('/codemirror/addon/fold/comment-fold.js');
		echo $this->jsLoad('/codemirror/addon/fold/foldcode.js');
		echo $this->jsLoad('/codemirror/addon/fold/foldgutter.js');
		echo $this->jsLoad('/codemirror/addon/fold/indent-fold.js');
		echo $this->jsLoad('/codemirror/addon/fold/xml-fold.js');
		echo $this->jsLoad('/codemirror/addon/search/search.js');
		echo $this->jsLoad('/codemirror/addon/search/searchcursor.js');
		echo $this->jsLoad('/codemirror/addon/selection/active-line.js');
		// End CodeMirror
		echo $this->jsLoad('/inc/js/index.js');
		echo $this->cssLoad('/inc/css/index.css');
		echo dcPage::jsConfirmClose('dcScript-form-tab-1','dcScript-form-tab-2');
		echo dcPage::jsPageTabs(isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'tab-1');
		echo '</head>'.NL;

		echo '<body class="dcscript no-js">'.NL;
		// Baseline
		echo $this->adminBaseline();
		// admin forms
		# Tab 1
		echo
			'<div class="multi-part" id="tab-1" title="'.__('Header code').' - ('.($this->settings('header_code_enabled') ? __('Enabled') : __('Disabled')).')">
				<form action="'.$formAction.'" method="post" id="'.html::escapeHTML($this->info('id')).'-form-header">
					<p>'.dcCore::app()->formNonce().'</p>
					<p>'.form::hidden('change_header', '')/*for check change in CodeMirror => jsConfirmClose()*/.'</p>
					<p>'.form::textArea('header_code', 120, 25, $header."\n", 'maximal', '0').'</p>
					<p class="button-bar clear">
						<input type="submit" id="update_header" name="update_header" title="'.__('Save the configuration').'" value="'.__('Save').'" />
						<input type="reset" id="reset_header" name="reset_header" title="'.__('Undo changes').'" value="'.__('Cancel').'" />
						<a id="export_header" class="button" title="'.__('Export').'" href="'.$downloadHeader.'">'.__('Download').'</a>
					</p>
				</form>
			</div>'.NL;
		# Tab 2
		echo
			'<div class="multi-part" id="tab-2" title="'.__('Footer code').' - ('.($this->settings('footer_code_enabled') ? __('Enabled') : __('Disabled')).')">
				<form action="'.$formAction.'" method="post" id="'.html::escapeHTML($this->info('id')).'-form-footer">
					<p>'.dcCore::app()->formNonce().'</p>
					<p>'.form::hidden('change_footer', '')/*for check change in CodeMirror => jsConfirmClose()*/.'</p>
					<p>'.form::textArea('footer_code', 120, 25, $footer."\n", 'maximal', '0').'</p>
					<p class="button-bar clear">
						<input type="submit" id="update_footer" name="update_footer" title="'.__('Save the configuration').'" value="'.__('Save').'" />
						<input type="reset" id="reset_footer" name="reset_footer" title="'.__('Undo changes').'" value="'.__('Cancel').'" />
						<a id="export_footer" class="button" title="'.__('Export').'" href="'.$downloadFooter.'">'.__('Download').'</a>
					</p>
				</form>
			</div>'.NL;
		// Footer plugin
		echo $this->adminFooterInfo();
		// helpBlock
		dcPage::helpBlock('dcScript-edit');

		echo '</body>'.NL;
		echo '</html>'.NL;
	}

	public function _public() {
		dcCore::app()->addBehavior('publicHeadContent', array('dcScript', 'publicHeadContent'));
		dcCore::app()->addBehavior('publicFooterContent', array('dcScript', 'publicFooterContent'));
	}

	public function _admin() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# define new permissions
		dcCore::app()->auth->setPermissionType(dcCore::app()->auth->makePermissions([dcScriptPerms::EDIT]), __('Edit public scripts'));

		# menu & dashboard
		dcCore::app()->addBehavior('adminDashboardFavorites', array($this, 'adminDashboardFavs'));
		$this->adminMenu('System');

		if(!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]), dcCore::app()->blog->id)) { return; }
		# admin only

		if(!dcCore::app()->auth->isSuperAdmin()) { return; }
		# super admin only

	}

	public function _config() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }

		dcPage::checkSuper();

		if (isset($_POST['save'])) {
			try {
				$this->settings('enabled', !empty($_POST['enabled']));
				$this->settings('header_code_enabled', !empty($_POST['header_code_enabled']));
				$this->settings('footer_code_enabled', !empty($_POST['footer_code_enabled']));
				$this->settings('backup_ext', html::escapeHTML($_POST['backup']));
				dcCore::app()->blog->triggerBlog();
				dcAdminNotices::addSuccessNotice(__('Configuration successfully updated.'));
			} catch(exception $e) {
				//dcCore::app()->error->add($e->getMessage());
				dcCore::app()->error->add(__('Unable to save the configuration'));
			}
			if(!empty($_GET['redir']) && strpos($_GET['redir'], 'p='.$this->info('id')) === false) {
				dcCore::app()->error->add(__('Redirection not found'));
				dcCore::app()->adminurl->redirect('admin.home');
			}
			http::redirect($_REQUEST['redir']);
		}

		echo
			'<div class="fieldset">
				<h3>'.__('Activation').'</h3>
				<p>
					'.form::checkbox('enabled', '1', $this->settings('enabled')).
					'<label class="classic" for="enabled">
						'.sprintf(__('Enable %s on this blog'), html::escapeHTML(__($this->info('name')))).
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
								'.form::checkbox('header_code_enabled', '1', $this->settings('header_code_enabled'))
								.'<label class="classic" for="header_code_enabled">'.__('Enable header code').'</label>
							</p>
							<p class="form-note">'.__('Enable public header code.').'</p>
						</div>
						<div class="col">
							<p>
								'.form::checkbox('footer_code_enabled', '1', $this->settings('footer_code_enabled'))
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
						'.form::field('backup', 25, 255, $this->settings('backup_ext'), 'classic').'
					</p>
					<p class="form-note">'.__('Default extension backup files.').'</p>
				</div>
			</div>
			<hr />
		'.$this->adminFooterInfo();
		dcPage::helpBlock('dcScript-config');
	}

	public function resources($path) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if(!isset(dcCore::app()->resources['help']['dcScript-config'])) { dcCore::app()->resources['help']['dcScript-config'] = $path.'/help/config.html'; }
		if(!isset(dcCore::app()->resources['help']['dcScript-edit'])) { dcCore::app()->resources['help']['dcScript-edit'] = $path.'/help/edit.html'; }
		if(!isset(dcCore::app()->resources['help']['dcScript-warning'])) { dcCore::app()->resources['help']['dcScript-warning'] = $path.'/help/warning.html'; }
	}
}
