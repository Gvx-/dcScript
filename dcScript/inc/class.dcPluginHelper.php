<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * Plugin helper for dotclear version 2.9 or hegher
 * Copyright © 2008-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

if (!defined('NL')) { define('NL', "\n"); }		// New Line
if (!defined('DC_VAR')) { define('DC_VAR', DC_ROOT.'/var'); }		// emulation DC_VAR for dc < 2.10

abstract class dcPluginHelper29c {

	### Constants ###
	const VERSION = '2.9.c';					// class version

	### Specific functions to overload ###

	protected function setDefaultSettings() {
		# create config plugin (TODO: specific settings)
		//$this->core->blog->settings->addNamespace($this->plugin_id);
		//$this->core->blog->settings->{$this->plugin_id}->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		# create user config plugin (TODO: specific settings)
		//$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
		//$this->core->auth->user_prefs->$this->plugin_id->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		# debug mode
		$this->debugDisplay('Not default settings for this plugin.');
	}

	protected function installActions($old_version) {
		# upgrade previous versions
		if(!empty($old_version)) {
			try {
				# TODO HERE Specifics upgrades
			} catch(Exception $e) {
				$core->error->add(__('Something went wrong with auto upgrade:').' '.$e->getMessage());
			}
		}
		$this->debugDisplay('Not install actions for this plugin.');
	}

	protected function uninstallActions() {
		# specific actions for uninstall
		$this->debugDisplay('Not uninstall actions for this plugin.');
	}

	public function configPage() {
		if(!defined('DC_CONTEXT_ADMIN') || !$this->core->auth->check('admin', $this->core->blog->id)) { return; }
		$scope = $this->configScope();
		if (isset($_POST['save'])) {
			try {
				$this->settings('enabled', !empty($_POST['enabled']), $scope);
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
		echo
			$this->configBaseline($scope).
			'<div class="fieldset">
				<h3>'.__('Activation').'</h3>
				<p>
					'.form::checkbox('enabled','1',$this->settings('enabled', null, 'global')).
					'<label class="classic" for="enabled">
						'.sprintf(__('Enable %s on this blog'), html::escapeHTML(__($this->info('name')))).
					'</label>
				</p>
				<p class="form-note">'.__('Enable the plugin on this blog.').'</p>
			</div>
			<!-- HTML CODE HERE -->
			';
			dcPage::helpBlock(/*'dcScript-config'*/);
	}

	public function indexPage() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		//dcPage::check('dcScript.edit');
		if(!$this->settings('enabled') && is_file(path::real($this->info('root').'/_config.php'))) {
			if($this->core->auth->check('admin', $this->core->blog->id)) {
				$this->core->adminurl->redirect('admin.plugins', array(
					'module' => $this->info('id'),'conf' => 1, 'redir' => $this->core->adminurl->get($this->info('adminUrl'))
				));
			} else {
				dcPage::addNotice('message', sprintf(__('%s plugin is not configured.'), $this->info('name')));
				$this->core->adminurl->redirect('admin.home');
			}
		}
		try {
			if (isset($_POST['save'])) {
				// TODO HERE inputs check
			}
		} catch(exception $e) {
			//$this->core->error->add($e->getMessage());
			$this->core->error->add(__('Unable to save the code'));
		}
		echo
			'<html>
				<head>
					<title>'.html::escapeHTML($this->info('name')).'</title>'.
					$this->jsLoad('/inc/index.js').NL.
					$this->cssLoad('/inc/index.css').NL.'
				</head>
				<body class="no-js">
					'.$this->adminBaseline().NL.'
					<!-- HTML CODE HERE -->
					'.$this->adminFooterInfo();
					dcPage::helpBlock(/*'dcScript-edit'*/);
		echo
			'	</body>
			</html>';
	}

	### Standard functions ###

	protected $plugin_id;				// ID plugin
	protected $admin_url;				// admin url plugin
	protected $icon_small;				// small icon file
	protected $icon_large;				// large icon file

	private $debug_mode = false;		// debug mode for plugin
	private $debug_log = false;			// debug Log for plugin
	private $debug_log_reset = false;	// debug logfile reset for plugin
	private $debug_logfile;				// debug logfilename for plugin

	public function __construct($id) {
		global $core;
		$this->core = &$core;
		
		# check DC_VAR
		self::getVarDir('', true);

		# set plugin id and admin url
		$this->plugin_id = $id;
		$this->admin_url = 'admin.plugin.'.$this->plugin_id;

		# set debug mode
		$debug_options = dirname(__FILE__).'/../.debug.php';
		if(is_file($debug_options)) { require_once($debug_options); }

		# start logfile
		$this->debugLog('*****************************************************');
		$this->debugLog('Start log - Version: '.$this->core->getVersion($this->plugin_id));
		$this->debugLog('Page: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);

		# Set admin context
		if(defined('DC_CONTEXT_ADMIN')) {
			# register self url
			$urls = $this->core->adminurl->dumpUrls();
			if(!array_key_exists('admin.self', $urls)) {
				$url = http::getSelfURI();
				$url = str_replace('?'.parse_url($url, PHP_URL_QUERY), '', $url);		// delete query
				$url = substr($url, 1 + strrpos($url, '/'));							// keep page name
				$urls = array_column((array)$urls, 'url');
				if(in_array($url, $urls)) {
					$this->core->adminurl->register('admin.self', $url, (empty($_GET) ? array(): $_GET));
				}
			}

			# set icons
			$this->icon_small = $this->plugin_id.$this->info('_icon_small');
			$this->icon_large = $this->plugin_id.$this->info('_icon_large');

			# uninstall plugin procedure
			if($this->core->auth->isSuperAdmin()) { $this->core->addBehavior('pluginBeforeDelete', array($this, 'uninstall')); }
		}
		
		# set default settings if empty
		$this->setDefaultSettings();

		# debug
		if($this->debug_mode) { $this->debugDisplay('Debug mode actived for this plugin'); }
	}

	### Admin functions ###

	public final function install() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		try {
			# check plugin versions
			$new_version = $this->info('version');
			$old_version = $this->core->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) { return; }
			# specifics install actions
			$this->installActions($old_version);
			# valid install
			$this->core->setVersion($this->plugin_id, $new_version);
			$this->debugLog('Update version '.$new_version);
			return true;
		} catch (Exception $e) {
			$this->debugDisplay('[Install] : '.$e->getMessage());
			$this->core->error->add($e->getMessage());
		}
		return false;
	}

	protected final function uninstall() {
		$this->debugLog('uninstall version '.$this->core->getVersion($this->plugin_id));
		# specifics uninstall actions
		$this->uninstallActions();
		# delete all users prefs
		$this->core->auth->user_prefs->delWorkSpace($this->plugin_id);
		# delete all blogs settings
		$this->core->blog->settings->delNamespace($this->plugin_id);
		# delete version
		$this->core->delVersion($this->plugin_id);
	}

	protected final function configLink($label, $redir=null, $prefix='', $suffix='') {
		if($this->core->auth->check('admin', $this->core->blog->id) && is_file(path::real($this->info('root').'/_config.php'))) {
			$redir = $this->core->adminurl->get(empty($redir) ? $this->admin_url : $redir);
			$href = $this->core->adminurl->get('admin.plugins', array('module' => $this->plugin_id,'conf' => 1, 'redir' => $redir));
			return $prefix.'<a href="'.$href.'">'.$label.'</a>'.$suffix;
		}
	}

	protected final function configScope() {
		return (isset($_POST['scope']) ? $_POST['scope'] : ($this->core->auth->isSuperAdmin() ? 'global' : 'default'));
	}

	protected function configBaseline($scope=null) {
		if($this->core->auth->isSuperAdmin()) {
			if(empty($scope)) { $scope = $this->configScope(); }
			$html =	'<p class="anchor-nav">
						<label class="classic">'.__('Scope').'&nbsp;:&nbsp;
							'.form::combo('scope', array(__('Global settings') => 'global', sprintf(__('Settings for %s'), html::escapeHTML($this->core->blog->name)) => 'default'), $scope).'
							<input id="scope_go" name="scope_go" type="submit" value="'.__('Go').'" />
						</label>
						&nbsp;&nbsp;<span class="form-note">'.__('Select the blog in which parameters apply').'</span>
						'.($scope == 'global' ? '&nbsp;&nbsp;<span class="warning">'.__('Update global options').'</span': '').'
					</p>';
		} else {
			$html = '';
		}
		$html .= '
			<div class="fieldset clear">
				<h3>'.__('Activation').'</h3>
				<p>
					'.form::checkbox('enabled','1',$this->settings('enabled', null, $scope)).
					'<label class="classic" for="enabled">
						'.sprintf(__('Enable %s on this blog'), html::escapeHTML(__($this->info('name')))).'&nbsp;&nbsp;&nbsp;
					</label>
					<span class="form-note">'.__('Enable the plugin on this blog.').'</span>
				</p>
			</div>'.NL;
		return NL.$this->jsLoad('/inc/config.js').$this->cssLoad('/inc/config.css', 'all', true).dcPage::jsConfirmClose('module_config').$html;
	}

	public function adminMenu($menu='Plugins') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $_menu;
		if(array_key_exists($menu, $_menu)) {
			$_menu[$menu]->addItem(
				html::escapeHTML(__($this->info('name'))),									// Item menu
				$this->core->adminurl->get($this->admin_url),								// Page admin url
				dcPage::getPF($this->icon_small),											// Icon menu
				preg_match(																	// Pattern url
					'/'.$this->core->adminurl->get($this->admin_url).'(&.*)?$/',
					$_SERVER['REQUEST_URI']
				),
				$this->core->auth->check($this->info('permissions'), $this->core->blog->id)	// Permissions minimum
			);
		} else {
			$this->debugDisplay('menu not present.');
			throw new ErrorException(sprinf(__('%s menu not present.'), $menu), 0, E_USER_NOTICE, __FILE__, __LINE__);
		}
	}

	public function adminDashboardFavs($core, $favs) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		$favs->register($this->plugin_id, array(
			'title'			=> $core->plugins->moduleInfo($this->plugin_id, 'name'),
			'url'			=> $core->adminurl->get($this->admin_url),
			'small-icon'	=> dcPage::getPF($this->icon_small),
			'large-icon'	=> dcPage::getPF($this->icon_large),
			'permissions'	=> $core->plugins->moduleInfo($this->plugin_id, 'permissions')
		));
	}

	protected function adminBaseline($items=array()) {
		if(empty($items)) { $items = array( $this->info('name') => ''); }
		return dcPage::breadcrumb(array_merge(array(html::escapeHTML($this->core->blog->name) => ''),$items)).dcPage::notices()."\n";
	}

	protected function adminFooterInfo() {
		$support = $this->info('support');
		$details = $this->info('details');
		return '<p class="right">
					<img style="vertical-align: middle;" src="'.dcPage::getPF($this->icon_small).'" alt="'.__('icon plugin').'"/>&nbsp;&nbsp;'.
					$this->configLink(__('Settings'), $this->admin_url, '', '&nbsp;-&nbsp;').
					html::escapeHTML($this->info('name')).'&nbsp;'.
					__('Version').'&nbsp;:&nbsp;'.html::escapeHTML($this->info('version')).'&nbsp;-&nbsp;'.
					__('Author(s)').'&nbsp;:&nbsp;'.html::escapeHTML($this->info('author')).
					($details ? '&nbsp;-&nbsp;<a href="'.$details.'">'.__('Details').'</a>' : '').
					($support ? '&nbsp;-&nbsp;<a href="'.$support.'">'.__('Support').'</a>' : '').'
				</p>
		';
	}

	### Widget functions ###

	protected static function widgetHeader(&$w, $title) {
		$w->setting('title', __('Title (optional)').' :', $title);
	}

	protected static function widgetFooter(&$w, $context=true, $class='') {
		if($context) { $w->setting('homeonly', __('Display on:'), 0, 'combo', array(__('All pages') => 0, __('Home page only') => 1, __('Except on home page') => 2)); }
		$w->setting('content_only', __('Content only'), 0, 'check');
		$w->setting('class', __('CSS class:'), $class);
		$w->setting('offline', __('Offline'), false, 'check');
	}

	protected static function widgetAddBasic(&$w, $id, $name, $callback, $help, $title) {
		$w->create($id, $name, $callback, null, $help);
		self::widgetHeader($w->{$id}, $title);
		self::widgetFooter($w->{$id});
	}

	protected static function widgetRender($w, $content, $class='', $attr='') {
		global $core;
		if (($w->homeonly == 1 && $core->url->type != 'default') || ($w->homeonly == 2 && $core->url->type == 'default') || $w->offline || empty($content)) {
			return;
		}
		$content = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').$content;
		return $w->renderDiv($w->content_only, trim(trim($class).' '.$w->class), trim($attr), $content);
	}

	### Common functions ###

	protected static function getVarDir($dir='', $create=false) {
		$dir = trim($dir, '\\/');
		$var_dir = path::real(DC_VAR.(empty($dir) ? '' : '/'.$dir), false);
		if(strpos($var_dir, DC_VAR) === false) { throw new Exception(__('The folder is not in the var directory')); }
		if(!is_dir($var_dir)) {
			if($create) {
				if(!@mkdir($var_dir, 0700, true)) { throw new Exception(__('Creating a var directory failed')); }
				$f = DC_VAR.'/.htaccess';
				if (!file_exists($f)) { @file_put_contents($f,'Require all denied'.NL.'Deny from all'.NL); }
			} else{
				return false;
			}
		}
		return $var_dir;
	}
	
	protected final function settings($key, $value=null, $scope='default') {
		if(is_null($value)) {
			try {
				if($scope == 'global' || $scope === true) {
					return $this->core->blog->settings->{$this->plugin_id}->getGlobal($key);
				} elseif($scope == 'local') {
					return $this->core->blog->settings->{$this->plugin_id}->getlocal($key);
				}
				return $this->core->blog->settings->{$this->plugin_id}->$key;
			} catch(Exception $e) {
				$this->debugDisplay('Blog settings read error.('.$key.')');
				return null;
			}
		} else {
			try {
				$global = ($scope == 'global' || $scope === true);
				$this->core->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
			} catch(Exception $e) {
				$this->debugDisplay('Blog settings write error (namespace not exist).('.$key.')');
				$this->core->blog->settings->addNamespace($this->plugin_id);
				$this->core->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
			}
		}
	}

	protected final function userSettings($key, $value=null, $scope='default') {
		if(is_null($value)) {
			try {
				return $this->core->auth->user_prefs->{$this->plugin_id}->$key;
			} catch(Exception $e) {
				$this->debugDisplay('User settings read error.('.$key.')');
				return null;
			}
		} else {
			try {
				$global = ($scope == 'global' || $scope === true);
				$this->core->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
			} catch(Exception $e) {
				$this->debugDisplay('User settings write error (namespace not exist).('.$key.')');
				$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
				$this->core->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
			}
		}
	}

	protected final function info($item=null, $default=null) {
		if(empty($item) || $item == 'id') {
			return $this->plugin_id;
		} elseif($item == 'adminUrl') {
			return (defined('DC_CONTEXT_ADMIN') ? $this->admin_url : null);
		} elseif($item == 'helperVersion') {
			return self::VERSION;
		} else {
			$res = $this->core->plugins->moduleInfo($this->plugin_id, $item);
			return $res === null ? $default : $res;
		}
	}

	# since dc 2.9
	protected final function jsLoad($src) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->info('version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsLoad(dcPage::getPF($file), $version);
		} else {
			return dcUtils::jsLoad($this->core->blog->getPF($file), $version);
		}
	}

	# since dc 2.9
	protected final function cssLoad($src, $media='all', $import=false) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->info('version');
		if(defined('DC_CONTEXT_ADMIN')) {
			if($import) {
				return	'<style type="text/css">@import url('.dcPage::getPF($file).') '.$media.';</style>'.NL;
			} else {
				return dcPage::cssLoad(dcPage::getPF($file), $media, $version);
			}
		} else {
			if($import) {
				return	'<style type="text/css">@import url('.$this->core->blog->getPF($file).') '.$media.';</style>'.NL;
			} else {
				return dcUtils::cssLoad($this->core->blog->getPF($file), $media, $version);
			}
		}
	}

	# since dc 2.10
	protected final function getVF($file) {
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::getVF($file);
		} else {
			return $this->core->blog->getVF($file);
		}
	}

	### debug functions ###

	protected final function debugDisplay($msg) {
		if($this->debug_mode && !empty($msg)) {
			if(defined('DC_CONTEXT_ADMIN')) { dcPage::addWarningNotice(':: [DEBUG] :: ['.$this->plugin_id.']<br />'.$msg); }
			$this->debugLog('[Debug display]: '.$msg);
		}
	}

	protected final function debugLog($text, $value=null) {
		if($this->debug_log && !empty($text)) {
			if(empty($this->debug_logfile)) {				# initialization
				$this->debug_logfile = self::getVarDir('logs', true).'/log_'.$this->plugin_id.'.txt';
				if($this->debug_log_reset && is_file($this->debug_logfile)) { @unlink($this->debug_logfile); }
			}
			if(!empty($value)) { $text .= ':'.NL.print_r($value, true).NL.str_pad('', 60, '*'); }
			@file_put_contents ($this->debug_logfile, NL.'['.date('Y-m-d-H-i-s').'] : ['.$this->plugin_id.'] : ['.$this->core->blog->id.'] : '.$text, FILE_APPEND);
		}
	}

}
