<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * Plugin helper for dotclear version 2.8 and more
 * Version : 0.22.0
 * Copyright © 2008-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }


abstract class dcPluginHelper022 {

	### Specific functions to overload ###

	protected function setDefaultSettings() {
		# config plugin (TODO: specific settings)
		//$this->core->blog->settings->addNamespace($this->plugin_id);
		//$this->core->blog->settings->{$this->plugin_id}->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		# user config plugin (TODO: specific settings)
		//$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
		//$this->core->auth->user_prefs->$this->plugin_id->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
	}

	protected function installActions($old_version) {

	}

	protected function uninstallActions() {

	}

	### Standard functions ###

	protected $plugin_id;				// ID plugin
	protected $admin_url;				// admin url plugin
	protected $icon_small;				// small icon file
	protected $icon_large;				// large icon file

	public static function init() {
		global $core;
		$instanceName = get_called_class();
		try {
			if(!isset($core->{$instanceName})) {
				$core->{$instanceName} = new $instanceName();
			} else {
				throw new LogicException(sprintf(__('Conflict: dcCore or other plugin, and %s plugin.'), $instanceName));
			}
		} catch(Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	public function __construct() {
		global $core;
		$this->core = &$core;
		# check plugin_id and admin url
		$plugin = realpath(dirname(__FILE__));
		foreach(array_map('realpath', array_reverse(explode(PATH_SEPARATOR, DC_PLUGINS_ROOT))) as $path) {
			if(strpos($plugin, $path) === 0) {
				$id = explode(DIRECTORY_SEPARATOR, trim(str_replace($path, '', $plugin), DIRECTORY_SEPARATOR));
				if(is_file($path.DIRECTORY_SEPARATOR.$id[0].DIRECTORY_SEPARATOR.'_define.php')) {
					$this->plugin_id = $id[0];
					break;
				}
			}
		}
		if(empty($this->plugin_id)) { throw new DomainException(__('Invalid plugin directory')); }
		$this->admin_url = 'admin.plugin.'.$this->plugin_id;

		# set icons
		$this->icon_small = $this->plugin_id.$this->core->plugins->moduleInfo($this->plugin_id, '_icon_small');
		$this->icon_large = $this->plugin_id.$this->core->plugins->moduleInfo($this->plugin_id, '_icon_large');

		# set default settings if empty
		$this->setDefaultSettings();

		# uninstall plugin procedure
		if($this->core->auth->isSuperAdmin()) { $this->core->addBehavior('pluginBeforeDelete', array($this, 'uninstall')); }
	}

	### Admin functions ###

	public final function install() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		try {
			# check DC version
			$dcMinVer = $this->core->plugins->moduleInfo($this->plugin_id, '_dc_min_version');
			if(!empty($dcMinVer)) {
				if (version_compare(DC_VERSION, $dcMinVer, '<')) {
					$this->core->plugins->deactivateModule($this->plugin_id);
					throw new Exception(sprintf(__('%s require Dotclear version %s or more.'), $this->core->plugins->moduleInfo($this->plugin_id, 'name'), $dcMinVer));
				}
			}
			# check plugin versions
			$new_version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
			$old_version = $this->core->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) { return; }
			# default settings
			$this->setDefaultSettings();
			# specifics install actions
			$this->installActions($old_version);
			# valid install
			$this->core->setVersion($this->plugin_id, $new_version);
			return true;
		} catch (Exception $e) {
			$this->core->error->add($e->getMessage());
		}
		return false;
	}

	public final function uninstall() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# specifics uninstall actions
		$this->uninstallActions();
		# delete settings and version
		$this->core->blog->settings->{$this->plugin_id}->dropAll(true);
		$this->core->delVersion($this->plugin_id);
	}

	public final function configLink($label, $redir=null, $prefix='', $suffix='') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if($this->core->auth->isSuperAdmin() && is_file(path::real($this->core->plugins->moduleInfo($this->plugin_id, 'root').'/_config.php'))) {
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
				html::escapeHTML(__($this->core->plugins->moduleInfo($this->plugin_id,'name'))),		// Item menu
				$this->core->adminurl->get($this->admin_url),											// Page admin url
				dcPage::getPF($this->icon_small),										// Icon menu
				preg_match(																																		// Pattern url
					'/'.$this->core->adminurl->get($this->admin_url).'(&.*)?$/',
					$_SERVER['REQUEST_URI']
				),
				$this->core->auth->check($this->core->plugins->moduleInfo($this->plugin_id, 'permissions'), $this->core->blog->id)	// Permissions minimum
			);
		} else {
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

	public function adminBaseline($items=array()) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if(empty($items)) { $items = array( $this->core->plugins->moduleInfo($this->plugin_id,'name') => ''); }
		return dcPage::breadcrumb(array_merge(array(html::escapeHTML($this->core->blog->name) => ''),$items)).dcPage::notices()."\n";
	}

	public function adminFooterInfo() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		$support = $this->core->plugins->moduleInfo($this->plugin_id, 'support');
		$details = $this->core->plugins->moduleInfo($this->plugin_id, 'details');
		return '<p class="right">
					<img style="vertical-align: middle;" src="'.dcPage::getPF($this->icon_small).'" alt="'.__('icon plugin').'"/>&nbsp;&nbsp;'.
					$this->configLink(__('Settings'), $this->admin_url, '', '&nbsp;-&nbsp;').
					html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'name')).'&nbsp;'.
					__('Version').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'version')).'&nbsp;-&nbsp;'.
					__('Author(s)').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'author')).
					($details ? '&nbsp;-&nbsp;<a href="'.$details.'">'.__('Details').'</a>' : '').
					($support ? '&nbsp;-&nbsp;<a href="'.$support.'">'.__('Support').'</a>' : '').'
				</p>
		';
	}

	### Common functions ###

	public function settings($key, $value=null, $global=false) {
		if(is_null($value)) {
			return $this->core->blog->settings->{$this->plugin_id}->$key;
		} else {
			$this->core->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
		}
	}

	public function userSettings($key, $value=null, $global=false) {
		if(is_null($value)) {
			return $this->core->auth->user_prefs->{$this->plugin_id}->$key;
		} else {
			$this->core->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
		}
	}

	public function info($item=null) {
		if(empty($item) || $item == 'id') {
			return $this->plugin_id;
		} elseif($item == 'adminUrl') {
			return (defined('DC_CONTEXT_ADMIN') ? $this->admin_url : null);
		} else {
			return $this->core->plugins->moduleInfo($this->plugin_id, $item);
		}
	}

	public function jsLoad($src) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsLoad(dcPage::getPF($file), $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$file = html::escapeHTML($file).(strpos($file,'?') === false ? '?' : '&amp;').'v='.$version;
				return '<script type="text/javascript" src="'.$this->core->blog->getQmarkURL().'pf='.$file.'"></script>'."\n";
			} else {
				return dcUtils::jsLoad($this->core->blog->getPF($file), $version);
			}
		}
	}

	public function cssLoad($src, $media='screen') {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::cssLoad(dcPage::getPF($file), $media, $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$file = html::escapeHTML($file).(strpos($file,'?') === false ? '?' : '&amp;').'v='.$version;
				return '<link rel="stylesheet" href="'.$this->core->blog->getQmarkURL().'pf='.$file.'" type="text/css" media="'.$media.'" />'."\n";
			} else {
				return dcUtils::cssLoad($this->core->blog->getPF($file), $media, $version);
			}
		}
	}

}
