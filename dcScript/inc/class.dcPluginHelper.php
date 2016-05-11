<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * Plugin helper for dotclear version 2.8 and more
 * Version : 0.25.0
 * Copyright © 2008-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

if (!defined('NL')) { define('NL', "\n"); }    // New Line

abstract class dcPluginHelper025 {

	### Constants ###
	const VERSION = '0.25.2';				// class version
	const DC_SHARED_DIR = '_shared';		// shared directory name 

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

		}
		$this->debugDisplay('Not install actions for this plugin.');
	}

	protected function uninstallActions() {
		# specific actions for uninstall
		$this->debugDisplay('Not uninstall actions for this plugin.');
	}
	
	public function configPage() {
		global $core;
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		dcPage::checkSuper();
		if (isset($_POST['save'])) {
			try {
				$global = ($_POST['global_settings'] == 'global_settings' ? 'global' :'local');
				$this->settings('enabled', !empty($_POST['enabled']), $global);
				$core->blog->triggerBlog();
				dcPage::addSuccessNotice(__('Configuration successfully updated.'));
			} catch(exception $e) {
				//$core->error->add($e->getMessage());
				$core->error->add(__('Unable to save the configuration'));
			}
			if(!empty($_GET['redir']) && strpos($_GET['redir'], 'p='.$this->info('id')) === false) {
				$core->error->add(__('Redirection not found'));
				$core->adminurl->redirect('admin.home');
			}
			http::redirect($_REQUEST['redir']);
		}

		echo
			'<div class="fieldset">
				<h3>'.__('Activation').'</h3>
				<p>
					'.form::checkbox('enabled','1',$this->settings('enabled', null, 'global')).
					'<label class="classic" for="enabled">
						'.sprintf(__('Enable %s on this blog'), html::escapeHTML(__($this->info('name')))).
					'</label>
				</p>
				<p class="form-note">'.__('Enable the plugin on this blog.').'</p>
				<p class="info">'
					.sprintf(__('Settings for %s'),html::escapeHTML($core->blog->name)).' : <strong>'
					.($this->settings('enabled') ? __('Enabled') : __('Disabled')).'</strong>
				</p>
			</div>
			<!-- HTML CODE HERE -->
			<div class="fieldset clear">
				<h3>'.__('Scope').'</h3>
				<div class="two-cols clear">
					<div class="col">
						<p>'.form::radio(array('global_settings'), html::escapeHTML('global_settings'), true).'
						<label class="classic" for="global_settings">'.__('Global settings').'</label></p>
					</div>
					<div class="col">
						<p>'.form::radio(array('global_settings', 'local_settings'), html::escapeHTML('local_settings'), false).'
						<label class="classic" for="local_settings">'.sprintf(__('Settings for %s'),html::escapeHTML($core->blog->name)).'</label></p>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		';
		dcPage::helpBlock(/*'dcScript-config'*/);
		
	}
	
	public function indexPage() {
		global $core;
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		//dcPage::check('dcScript.edit');
		if(!$this->settings('enabled') && is_file(path::real($this->info('root').'/_config.php'))) {
			if($core->auth->isSuperAdmin()) {
				$core->adminurl->redirect('admin.plugins', array(
					'module' => $this->info('id'),'conf' => 1, 'redir' => $core->adminurl->get($this->info('adminUrl'))
				));
			} else {
				dcPage::addNotice('message', sprintf(__('%s plugin is not configured.'), $this->info('name')));
				$core->adminurl->redirect('admin.home');
			}
		}
		echo
			'<html>
				<head>
					<title>'.html::escapeHTML($this->info('name')).'</title>'.
					$this->jsLoad('/inc/admin.js').NL.
					$this->cssLoad('/inc/style.css').NL.'
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

		# set icons
		$this->icon_small = $this->plugin_id.$this->info('_icon_small');
		$this->icon_large = $this->plugin_id.$this->info('_icon_large');

		# set default settings if empty
		$this->setDefaultSettings();

		# uninstall plugin procedure
		if(defined('DC_CONTEXT_ADMIN') && $this->core->auth->isSuperAdmin()) { $this->core->addBehavior('pluginBeforeDelete', array($this, 'uninstall')); }

		# debug
		//$this->debugDisplay('Debug mode actived for this plugin');
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
		if($this->core->auth->isSuperAdmin() && is_file(path::real($this->info('root').'/_config.php'))) {
			$redir = $this->core->adminurl->get(empty($redir) ? $this->admin_url : $redir);
			$href = $this->core->adminurl->get('admin.plugins', array('module' => $this->plugin_id,'conf' => 1, 'redir' => $redir));
			return $prefix.'<a href="'.$href.'">'.$label.'</a>'.$suffix;
		}
	}

	public function adminMenu($menu='Plugins') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $_menu;
		if(array_key_exists($menu, $_menu)) {
			$_menu[$menu]->addItem(
				html::escapeHTML(__($this->info('name'))),									// Item menu
				$this->core->adminurl->get($this->admin_url),								// Page admin url
				dcPage::getPF($this->icon_small),											// Icon menu
				preg_match(																																		// Pattern url
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

	protected static function widgetRender($w, $content) {
		global $core;
		if (($w->homeonly == 1 && $core->url->type != 'default') || ($w->homeonly == 2 && $core->url->type == 'default') || $w->offline || empty($content)) {
			return;
		}
		$content = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').$content;
		return $w->renderDiv($w->content_only, $w->class,'',$content);
	}

	### Common functions ###

	protected static function getSharedDir($dir='') {
		$dir = trim($dir, '\\/');
		$dc_shared = DC_TPL_CACHE.'/'.self::DC_SHARED_DIR;
		$shared = path::real($dc_shared.(empty($dir) ? '' : '/'.$dir), false);
		if(strpos($shared, $dc_shared) === false) { throw new Exception(__('The folder is not in the shared directory')); }
		if(!is_dir($shared)) {
			if(!mkdir($shared, 0700, true)) { throw new Exception(__('Creating a shared directory failed')); }
		}
		return $shared;
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

	protected final function jsLoad($src) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->info('version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsLoad(dcPage::getPF($file), $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$href = $this->core->blog->getQmarkURL().'pf='.html::escapeHTML($file).(strpos($file, '?') === false ? '?' : '&amp;').'v='.$version;
				return '<script type="text/javascript" src="'.$href.'"></script>'."\n";
			} else {
				return dcUtils::jsLoad($this->core->blog->getPF($file), $version);
			}
		}
	}

	protected final function cssLoad($src, $media='screen') {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->info('version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::cssLoad(dcPage::getPF($file), $media, $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$href = $this->core->blog->getQmarkURL().'pf='.html::escapeHTML($file).(strpos($file, '?') === false ? '?' : '&amp;').'v='.$version;
				return '<link rel="stylesheet" href="'.$href.'" type="text/css" media="'.$media.'" />'."\n";
			} else {
				return dcUtils::cssLoad($this->core->blog->getPF($file), $media, $version);
			}
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
				$this->debug_logfile = self::getSharedDir('logs').'/log_'.$this->plugin_id.'.txt';
				if($this->debug_log_reset && is_file($this->debug_logfile)) { @unlink($this->debug_logfile); }
			}
			if(!empty($value)) { $text .= ':'.NL.print_r($value, true).NL.str_pad('', 60, '*'); }
			file_put_contents ($this->debug_logfile, NL.'['.date('Y-m-d-H-i-s').'] : ['.$this->plugin_id.'] : ['.$this->core->blog->id.'] : '.$text, FILE_APPEND);
		}
	}

}
