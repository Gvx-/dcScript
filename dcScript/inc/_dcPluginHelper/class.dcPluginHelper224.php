<?php
/**
  * Plugin dcHelper for dotclear version 2.24 or hegher
  *
  * @package Dotclear\plugin\dcPluginHelper
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2008-2022 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if(!defined('DC_RC_PATH')) { return; }

if(!defined('NL')) { define('NL', "\n"); }									# New Line

if(!function_exists('getInstance')) {										# get class instance in dcCore::app()
	function getInstance($plugin) { return dcCore::app()->{$plugin}; }
}

/**
 * dcPluginHelper224
 */
abstract class dcPluginHelper224 {

	/** @var string VERSION  */
	const VERSION = '224-r2022.11.13';										# class version
	
	/** @var string FILE_DIR_SEPARATOR  */
	const FILE_DIR_SEPARATOR = DIRECTORY_SEPARATOR;
	/** @var string URL_DIR_SEPARATOR  */
	const URL_DIR_SEPARATOR = '/';
	/** @var string ZIP_DIR_SEPARATOR  */
	const ZIP_DIR_SEPARATOR = '/';

	/**
	 * setDefaultSettings
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	protected function setDefaultSettings() {
		# debug mode
		$this->debugDisplay('Not default settings for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	/**
	 *  installActions
	 *
	 * @todo overloaded
	 *
	 * @param string $old_version
	 *
	 * @return void
	 */
	protected function installActions($old_version) {
		# debug mode
		$this->debugDisplay('Not install actions for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	/**
	 * uninstallActions
	 *
	 * @todo overloaded
	 *
	 * @return boolean
	 */
	protected function uninstallActions() {
		# debug mode
		$this->debugDisplay('Not uninstall actions for '.$this->plugin_id.' ('.get_class($this).') plugin.');
		return true;
	}

	/**
	 * _config
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function _config() {
		if(!defined('DC_CONTEXT_ADMIN') || !dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]), dcCore::app()->blog->id)) { return; }
		$scope = $this->configScope();
		if (isset($_POST['save'])) {
			try {
				//$this->settings('enabled', !empty($_POST['enabled']), $scope);
				dcCore::app()->blog->triggerBlog();
				dcAdminNotices::addSuccessNotice( __('Configuration successfully updated.'));
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

		// TODO: HERE display

		# debug mode
		$this->debugDisplay('Not config page for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	/**
	 * index
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function index() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
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
		try {
			if (isset($_POST['save'])) {
				// TODO: HERE inputs check
			}
			/** @phpstan-ignore-next-line */
		} catch(exception $e) {
			//dcCore::app()->error->add($e->getMessage());
			dcCore::app()->error->add(__('Unable to save the code'));
		}

		// TODO: HERE display

		# debug mode
		$this->debugDisplay('Not index page for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	/**
	 * _prepend
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	protected function _prepend() {
		# common (public & admin)

		if(defined('DC_CONTEXT_ADMIN')) {
			# admin only

			# services

		} else {
			# public only

		}
		# debug mode
		$this->debugDisplay('Not _prepend actions for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	/**
	 * _admin
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function _admin() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }

		# debug mode
		$this->debugDisplay('Not _admin actions for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	/**
	 * _public
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function _public() {

		# debug mode
		$this->debugDisplay('Not _public page for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	/**
	 * resources
	 *
	 * @todo overloaded
	 *
	 * @param  string $path
	 * @return void
	 */
	public function resources($path) {
		/*
		if(!isset(dcCore::app()->resources['help']['dcScript-config'])) { dcCore::app()->resources['help']['dcScript-config'] = $path.'/help/config.html'; }
		if(!isset(dcCore::app()->resources['help']['dcScript-edit'])) { dcCore::app()->resources['help']['dcScript-edit'] = $path.'/help/edit.html'; }
		if(!isset(dcCore::app()->resources['help']['dcScript-warning'])) { dcCore::app()->resources['help']['dcScript-warning'] = $path.'/help/warning.html'; }
		*/

		# debug mode
		$this->debugDisplay('Not resources actions for '.$this->plugin_id.' ('.get_class($this).') plugin.');
	}

	### Standard functions ###

	protected $plugin_id;													# ID plugin
	protected $admin_url;													# admin url plugin
	protected $icon_small;													# small icon file
	protected $icon_large;													# large icon file

	private $debug_mode = false;											# debug mode for plugin
	private $debug_log = false;												# debug Log for plugin
	private $debug_log_reset = false;										# debug logfile reset for plugin
	private $debug_logfile;													# debug logfilename for plugin

	/**
	 * __construct
	 *
	 * @param string $id
	 *
	 * @return void
	 */
	public function __construct($id) {

		# set plugin id and admin url
		$this->plugin_id = $id;
		$this->admin_url = 'admin.plugin.'.$this->plugin_id;

		# set debug mode
		$debug_options = path::real($this->info('root').'/.debug.php');
		if(is_file($debug_options)) { require_once($debug_options); }

		# start logfile
		$this->debugLog('START_DEBUG');
		$this->debugLog('Version', dcCore::app()->getVersion($this->plugin_id));
		$this->debugLog('Helper version', self::VERSION);
		$this->debugLog('Page', $_SERVER['REQUEST_URI']);

		# Set admin context
		if(defined('DC_CONTEXT_ADMIN')) {
			# register self url
			$urls = dcCore::app()->adminurl->dumpUrls();
			if(!$urls->offsetExists('admin.self')) {
				$url = http::getSelfURI();
				$url = str_replace('?'.parse_url($url, PHP_URL_QUERY), '', $url);		// delete query
				$url = substr($url, 1 + strrpos($url, self::URL_DIR_SEPARATOR));		// keep page name
				if(in_array($url, array_column((array)$urls, 'url'))) {					// Register checked
					dcCore::app()->adminurl->register('admin.self', $url, ($_GET ?: array()));
				}
			}

			# set icons
			$this->icon_small = $this->plugin_id.$this->info('_icon_small');
			$this->icon_large = $this->plugin_id.$this->info('_icon_large');

			# uninstall plugin procedure
			if(dcCore::app()->auth->isSuperAdmin()) { dcCore::app()->addBehavior('pluginBeforeDelete', array($this, 'uninstall')); }
		}

		# set default settings if empty
		$this->setDefaultSettings();

		# debug
		//$this->debugDisplay('Debug mode actived for '.$this->plugin_id.' plugin');

		$this->_prepend();
	}

	/**
	 * __destruct
	 *
	 * @return void
	 */
	public function __destruct() {
		# end logfile
		$this->debugLog('END_DEBUG');
	}
	
	### Admin functions ###

	/**
	 * _install
	 *
	 * @return Boolean
	 */
	public final function _install() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }	/** @phpstan-ignore-line */
		try {
			# check plugin versions
			$new_version = $this->info('version');
			$old_version = dcCore::app()->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) { return; }		/** @phpstan-ignore-line */
			# specifics install actions
			$this->installActions($old_version);
			# valid install
			dcCore::app()->setVersion($this->plugin_id, $new_version);
			$this->debugLog('Update', 'version '.$new_version);
			return true;
		} catch (Exception $e) {
			$this->debugDisplay('[Install] : '.$e->getMessage());
			dcCore::app()->error->add($e->getMessage());
		}
		return false;
	}

	/**
	 * uninstall
	 *
	 * @param object $plugin
	 *
     * @return void
	 */
	public final function uninstall($plugin) {
		$this->debugLog('uninstall', 'version '.dcCore::app()->getVersion($this->plugin_id));
		# specifics uninstall actions
		if($plugin['id'] == $this->plugin_id) {
			if($this->uninstallActions()) {
				# clean DC_VAR
				if(self::getVarDir($this->plugin_id)) { files::deltree(self::getVarDir($this->plugin_id)); }
				# delete all users prefs
				dcCore::app()->auth->user_prefs->delWorkSpace($this->plugin_id);
				# delete all blogs settings
				dcCore::app()->blog->settings->delNamespace($this->plugin_id);
				# delete version
				dcCore::app()->delVersion($this->plugin_id);
			}
		}
	}

	/**
	 * configScope
	 *
	 * @return string
	 */
	protected final function configScope() {
		return (isset($_POST['scope']) ? $_POST['scope'] : (dcCore::app()->auth->isSuperAdmin() ? 'global' : 'default'));
	}

	/**
	 * configBaseline
	 *
	 * @param mixed $scope
	 * @param boolean $activate
	 *
	 * @return string
	 */
	protected function configBaseline($scope=null, $activate=true) {
		$html = '';
		if(dcCore::app()->auth->isSuperAdmin()) {
			if(empty($scope)) { $scope = $this->configScope(); }
			$html .= '
					<p class="anchor-nav">
						<label class="classic">'.__('Scope').'&nbsp;:&nbsp;
							'.form::combo('scope', array(__('Global settings') => 'global', sprintf(__('Settings for %s'), html::escapeHTML(dcCore::app()->blog->name)) => 'default'), 'default').'
							<input id="scope_go" name="scope_go" type="submit" value="'.__('Go').'" />
						</label>
						&nbsp;&nbsp;<span class="form-note">'.__('Select the blog in which parameters apply').'</span>
						'.($scope == 'global' ? '&nbsp;&nbsp;<span class="warning">'.__('Update global options').'</span': '').'
					</p>';
		}
		if($activate) {
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
		}
		return NL.$this->jsLoad('/inc/config.js').$this->cssLoad('/inc/config.css', 'all', true).dcPage::jsConfirmClose('module_config').$html;
	}

	/**
	 * adminMenu
	 *
	 * @param string $menu
	 *
     * @return object $this
	 */
	public function adminMenu($menu=dcAdmin::MENU_PLUGINS) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }	/** @phpstan-ignore-line */
		if(dcCore::app()->menu->offsetExists($menu)) {
			dcCore::app()->menu[$menu]->addItem(
				html::escapeHTML(__($this->info('name'))),									# Item menu
				dcCore::app()->adminurl->get($this->admin_url),								# Page admin url
				dcPage::getPF($this->icon_small),											# Icon menu
				preg_match(																	# Pattern url
					self::URL_DIR_SEPARATOR.dcCore::app()->adminurl->get($this->admin_url).'(&.*)?$/',
					$_SERVER['REQUEST_URI']
				),
				dcCore::app()->auth->check($this->info('permissions'), dcCore::app()->blog->id)	# Permissions minimum
			);
		} else {
			$this->debugDisplay('menu not present.');
			throw new ErrorException(sprintf(__('%s menu not present.'), $menu), 0, E_USER_NOTICE, __FILE__, __LINE__);
		}
		return $this;
	}

	/**
	 * adminDashboardFavsV2
	 *
	 * @param object $favs
	 *
     * @return void
	 */
	public function adminDashboardFavsV2($favs) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		$favs->register($this->plugin_id, array(
			'title'			=> dcCore::app()->plugins->moduleInfo($this->plugin_id, 'name'),
			'url'			=> dcCore::app()->adminurl->get($this->admin_url),
			'small-icon'	=> dcPage::getPF($this->icon_small),
			'large-icon'	=> dcPage::getPF($this->icon_large),
			'permissions'	=> dcCore::app()->plugins->moduleInfo($this->plugin_id, 'permissions')
		));
	}

	/**
	 * adminBaseline
	 *
	 * @param array $items
	 *
	 * @return string
	 */
	protected function adminBaseline($items=array()) {
		if(empty($items)) { $items = array( $this->info('name') => ''); }
		return dcPage::breadcrumb(array_merge(array(html::escapeHTML(dcCore::app()->blog->name) => ''), $items)).dcAdminNotices::getNotices().NL;
	}

	/**
	 * adminFooterInfo
	 *
	 * @return string
	 */
	public function adminFooterInfo() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }	/** @phpstan-ignore-line */
		$support = $this->info('support');
		$details = $this->info('details');
		return '<p class="right">
					<img style="vertical-align: middle;" src="'.dcPage::getPF($this->icon_small).'" alt="'.__('icon plugin').'"/>&nbsp;&nbsp;'.
					html::escapeHTML($this->info('name')).'&nbsp;'.
					__('Version').'&nbsp;:&nbsp;'.html::escapeHTML($this->info('version')).'&nbsp;-&nbsp;'.
					__('Author(s)').'&nbsp;:&nbsp;'.html::escapeHTML($this->info('author')).
					($details ? '&nbsp;-&nbsp;<a href="'.$details.'">'.__('Details').'</a>' : '').
					($support ? '&nbsp;-&nbsp;<a href="'.$support.'">'.__('Support').'</a>' : '').'
				</p>
		';
	}

	### Widget functions ###

	/**
	 * widgetHeader
	 *
	 * @param object $w
	 * @param string $title
	 *
	 * @return void
	 */
	protected static function widgetHeader(&$w, $title) {
		$w->setting('title', __('Title (optional)').' :', $title);
	}

	/**
	 * widgetFooter
	 *
	 * @param object $w
	 * @param boolean $context
	 * @param string $class
	 *
	 * @return void
	 */
	protected static function widgetFooter(&$w, $context=true, $class='') {
		if($context) { $w->setting('homeonly', __('Display on:'), 0, 'combo', array(__('All pages') => 0, __('Home page only') => 1, __('Except on home page') => 2)); }
		$w->setting('content_only', __('Content only'), 0, 'check');
		$w->setting('class', __('CSS class:'), $class);
		$w->setting('offline', __('Offline'), false, 'check');
	}

	/**
	 * widgetAddBasic
	 *
	 * @param object $w
	 * @param string $id
	 * @param string $name
	 * @param callable $callback
	 * @param mixed $help
	 * @param string $title
	 *
	 * @return void
	 */
	protected static function widgetAddBasic(&$w, $id, $name, $callback, $help, $title) {
		$w->create($id, $name, $callback, null, $help);
		self::widgetHeader($w->{$id}, $title);
		self::widgetFooter($w->{$id});
	}

	/**
	 * widgetRender
	 *
	 * @param object $w
	 * @param string $content
	 * @param string $class
	 * @param string $attr
	 *
	 * @return string
	 */
	protected static function widgetRender($w, $content, $class='', $attr='') {
		if (($w->homeonly == 1 && dcCore::app()->url->type != 'default') || ($w->homeonly == 2 && dcCore::app()->url->type == 'default') || $w->offline || empty($content)) {
			return;		/** @phpstan-ignore-line */
		}
		$content = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').$content;
		return $w->renderDiv($w->content_only, trim(trim($class).' '.$w->class), trim($attr), $content);
	}

	### Common functions ###

	/**
	 * settings
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $scope
	 *
	 * @return mixed
	 */
	public final function settings($key, $value=null, $scope='default') {
		if(is_null($value)) {
			try {
				if($scope == 'global' || $scope === true) {
					$value = dcCore::app()->blog->settings->{$this->plugin_id}->getGlobal($key);
					$v = json_decode($value, true);
					return is_array($v) ? $v : $value;
				} elseif($scope == 'local') {
					$value = dcCore::app()->blog->settings->{$this->plugin_id}->getLocal($key);
					$v = json_decode($value, true);
					return is_array($v) ? $v : $value;
				}
				$value = dcCore::app()->blog->settings->{$this->plugin_id}->$key;
				$v = json_decode($value, true);
				return is_array($v) ? $v : $value;
			} catch(Exception $e) {
				$this->debugDisplay('Blog settings read error.('.$key.')');
				return null;
			}
		} else {
            $global = ($scope == 'global' || $scope === true);
			try {
				if(is_array($value) || is_object($value)) { $value = json_encode($value); }
				dcCore::app()->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
			} catch(Exception $e) {
				$this->debugDisplay('Blog settings write error (namespace not exist).('.$key.')');
				dcCore::app()->blog->settings->addNamespace($this->plugin_id);
				dcCore::app()->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
			}
		}
	}

	/**
	 * userSettings
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $scope
	 *
	 * @return mixed
	 */
	public final function userSettings($key, $value=null, $scope='default') {
		if(is_null($value)) {
			try {
				if($scope == 'global' || $scope === true) {
					$value = dcCore::app()->auth->user_prefs->{$this->plugin_id}->getGlobal($key);
					$v = json_decode($value, true);
					return is_array($v) ? $v : $value;
				} elseif($scope == 'local') {
					$value = dcCore::app()->auth->user_prefs->{$this->plugin_id}->getLocal($key);
					$v = json_decode($value, true);
					return is_array($v) ? $v : $value;
				}
				$value = dcCore::app()->auth->user_prefs->{$this->plugin_id}->$key;
				$v = json_decode($value, true);
				return is_array($v) ? $v : $value;
			} catch(Exception $e) {
				$this->debugDisplay('User settings read error.('.$key.')');
				return null;
			}
		} else {
            $global = ($scope == 'global' || $scope === true);
			try {
				if(is_array($value) || is_object($value)) { $value = json_encode($value); }
				dcCore::app()->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
			} catch(Exception $e) {
				$this->debugDisplay('User settings write error (namespace not exist).('.$key.')');
				dcCore::app()->auth->user_prefs->addWorkSpace($this->plugin_id);
				dcCore::app()->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
			}
		}
	}

	/**
	 * settingDrop
	 *
	 * @param mixed $key
	 *
     * @return object $this
	 */
	protected final function settingDrop($key) {
		$s = new dcNamespace(dcCore::app(), null, $this->plugin_id);		/** @phpstan-ignore-line */
		$s->drop($key);
		unset($s);
		return $this;
	}

	/**
	 * userSettingDrop
	 *
	 * @param mixed $key
	 *
     * @return object $this
	 */
	protected final function userSettingDrop($key) {
		//$s = new dcWorkspace(dcCore::app(), dcCore::app()->auth->userID(), $this->plugin_id);
		$s = new dcWorkspace(dcCore::app()->auth->userID(), $this->plugin_id);
		$s->drop($key);
		unset($s);
		return $this;
	}

	/**
	 * info
	 *
	 * @param string $item
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public final function info($item=null, $default=null) {
		if(empty($item) || $item == 'id') {
			return $this->plugin_id;
		} elseif($item == 'adminUrl') {
			return (defined('DC_CONTEXT_ADMIN') ? $this->admin_url : null);
		} elseif($item == 'helperVersion') {
			return self::VERSION;
		} else {
			$res = dcCore::app()->plugins->moduleInfo($this->plugin_id, $item);
			return $res === null ? $default : $res;
		}
	}

	/**
	 * nextStep
	 *
	 * @param mixed $step
	 * @param integer $delay
	 *
	 * @return void
	 */
	public function nextStep($step, $delay=0) {
		$timeout = $_SERVER['REQUEST_TIME'] + ini_get('max_execution_time') - 1;
		//$timeout = $_SERVER['REQUEST_TIME'] + 30 - 1;								# for debug
		if($delay > 0 && ($timeout - $delay) < time()) { return; }					# if timeout > next task delay
		if($delay < 0 && ($timeout + $delay) > time()) { return; }					# if timeout - delay < now

		# --BEHAVIOR-- beforeNextStepV2
		if(dcCore::app()->callBehavior('beforeNextStepV2', $this->plugin_id, $step) === false) { return; }

		if(is_array($step)) {
			foreach($step as $k => $v) { $_GET[$k] = $v; }
		} elseif(!empty($step)) {
			$_GET['step'] = $step;
		}
		$url = basename(parse_url(http::getSelfURI(), PHP_URL_PATH)).'?'.http_build_query($_GET,'','&');
		$this->debugLog('nextStep', $url);
		http::redirect($url);
	}

	/**
	 * getVarDir
	 *
	 * @param string $dir
	 * @param boolean $create
	 *
	 * @return mixed
	 */
	public static function getVarDir($dir='', $create=false) {
		$dir = trim($dir, '\\/');
		$var_dir = path::real(DC_VAR.(empty($dir) ? '' : self::FILE_DIR_SEPARATOR.$dir), false);
		if(strpos($var_dir, path::real(DC_VAR, false)) === false) { dcCore::app()->error->add(__('The folder is not in the var directory')); }
		if(!is_dir($var_dir)) {
			if(!$create) { return false; }
			@files::makeDir($var_dir, true);
			if(!is_dir($var_dir)) { dcCore::app()->error->add(__('Creating a var directory failed')); }
		}
		return $var_dir;
	}

	/**
	 * getVF
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public final function getVF($file) {
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::getVF($file);
		} else {
			return dcCore::app()->blog->getVF($file);
		}
	}

	/**
	 * jsLoad
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	public final function jsLoad($src) {
		if(is_file($this->info('root').self::FILE_DIR_SEPARATOR.ltrim($src, self::FILE_DIR_SEPARATOR))) {
			$file = $this->plugin_id.self::URL_DIR_SEPARATOR.ltrim($src, self::URL_DIR_SEPARATOR);
			$version = $this->info('version');
			if(defined('DC_CONTEXT_ADMIN')) {
				return dcPage::jsLoad(dcPage::getPF($file), $version);
			} else {
				return dcUtils::jsLoad(dcCore::app()->blog->getPF($file), $version);
			}
		}
        return '';
	}

	/**
	 * cssLoad
	 *
	 * @param string $src
	 * @param string $media
	 * @param boolean $import
	 *
	 * @return string
	 */
	public final function cssLoad($src, $media='all', $import=false) {
		if(is_file($this->info('root').self::FILE_DIR_SEPARATOR.ltrim($src, self::FILE_DIR_SEPARATOR))) {
			$file = $this->plugin_id.self::URL_DIR_SEPARATOR.ltrim($src, self::URL_DIR_SEPARATOR);
			$version = $this->info('version');
			if(defined('DC_CONTEXT_ADMIN')) {
				if($import) {
					return	'<style type="text/css">@import url('.dcPage::getPF($file).') '.$media.';</style>'.NL;
				} else {
					return dcPage::cssLoad(dcPage::getPF($file), $media, $version);
				}
			} else {
				if($import) {
					return	'<style type="text/css">@import url('.dcCore::app()->blog->getPF($file).') '.$media.';</style>'.NL;
				} else {
					return dcUtils::cssLoad(dcCore::app()->blog->getPF($file), $media, $version);
				}
			}
		}
        return '';
	}

	/**
	 * jsJson
	 *
	 * @param array $vars
	 *
	 * @return string
	 *
	 * @see https://open-time.net/post/2018/11/07/PHP-Javascript-CSS
	 */
	public final function jsJson($vars) {
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsJson($this->plugin_id, $vars);
		} else {
			return dcUtils::jsJson($this->plugin_id, $vars);
		}
	}

	### debug functions ###

	/**
	 * debugDisplay
	 *
	 * @param string $msg
	 *
     * @return object $this
	 */
	protected final function debugDisplay($msg) {
		if($this->debug_mode && !empty($msg)) {
			if(defined('DC_CONTEXT_ADMIN')) { dcAdminNotices::addWarningNotice('<strong>DEBUG - '.$this->plugin_id.'</strong>&nbsp;:&nbsp;'.$msg); }
			$this->debugLog('[Debug display]', $msg);
		}
		return $this;
	}

	/**
	 * debugLog
	 *
	 * @param string $text
	 * @param mixed $value
	 *
     * @return object $this
	 */
	public final function debugLog($text, $value=null) {
		if($this->debug_log && !empty($text)) {
			if(empty($this->debug_logfile)) { $this->setDebugFilename(); }				# initialization
			$cmd = array('START_DEBUG', 'END_DEBUG');
			if(in_array(strtoupper($text), $cmd)) {
				$text = str_pad('**'.strtoupper($text), 66,'*');
			} elseif(is_bool($value)) {
				$text .= ' : '.($value ? 'True' : 'False');
			} elseif(is_numeric($value)) {
				$text .= ' : '.$value;
			} elseif(is_string($value)) {
				if(strpos($value, NL) === false) {
					$text .= ' : '.$value;
				} else {
					$text .= ' :'.NL.$value.NL.str_pad('END_VALUE', 66, '*');
				}
			} elseif(is_null($value)) {
				$text .= ' : <null>';
			} elseif(empty($value)) {
				$text .= ' : <empty>';
			} else {
				$text .= ' :'.NL.print_r($value, true).NL.str_pad('END_VALUE', 66, '*');
			}
			@file_put_contents ($this->debug_logfile, NL.'['.date('YmdHis').'-'.$this->plugin_id.'-'.dcCore::app()->blog->id.'] '.$text, FILE_APPEND);
		}
		return $this;
	}

	/**
	 * setDebugFilename
	 *
	 * @param mixed $filename
	 * @param boolean $reset_file
	 *
     * @return object $this
	 */
	public final function setDebugFilename($filename=null, $reset_file=false) {
		if(empty($filename)) { $filename = self::getVarDir('logs', true).'/log_'.$this->plugin_id.'.txt'; }
		if(!empty($this->debug_logfile)) { $this->debugLog('Change to file', $filename); }
		if(is_dir(dirname($filename))) {
			$this->debug_logfile = $filename;
		} else {
			$this->debug_logfile = self::getVarDir('logs', true).self::FILE_DIR_SEPARATOR.basename($filename);
		}
		if($this->debug_log) {
			if($this->debug_log_reset && $reset_file && is_file($this->debug_logfile)) {
				@unlink($this->debug_logfile);
			} else {
				@file_put_contents ($this->debug_logfile, NL, FILE_APPEND);
			}
		}
		return $this;
	}

}
