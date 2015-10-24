<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_RC_PATH')) { return; }

__('dcScript');						// plugin name
__('Add script for DC 2.7+');		// description plugin

class dcScript {

	public static function publicHeadContent($core, $_ctx) {
		$html = base64_decode($core->dcScript->settings('header_code'));
		if($core->dcScript->settings('enabled') && $core->dcScript->settings('header_code_enabled') && !empty($html)) {
			echo "<!-- dcScript begin -->\n".$html."\n<!-- dcScript end -->";
		}
	}

	public static function publicFooterContent($core, $_ctx) {
		$html = base64_decode($core->dcScript->settings('footer_code'));
		if($core->dcScript->settings('enabled') && $core->dcScript->settings('footer_code_enabled') && !empty($html)) {
			echo "<!-- dcScript begin -->\n".$html."\n<!-- dcScript end -->";
		}
	}
	
	/*---------------------------------------------------------------------------
	 * Helper for dotclear version 2.7 and more
	 * Version : 0.20.2
	 * Copyright © 2008-2015 Gvx
	 * Licensed under the GPL version 2.0 license.
	 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
	 *-------------------------------------------------------------------------*/
	 
	/* --== SPECIFIQUE FUNCTIONS ==-- */

	protected function setDefaultSettings() {
		# config plugin (TODO: specific settings)
		$this->core->blog->settings->addNamespace($this->plugin_id);
		$this->core->blog->settings->{$this->plugin_id}->put('enabled',false,'boolean',__('Enable plugin'),false,true);
		$this->core->blog->settings->{$this->plugin_id}->put('header_code_enabled',false,'boolean',__('Enable header code'),false,true);
		$this->core->blog->settings->{$this->plugin_id}->put('footer_code_enabled',false,'boolean',__('Enable footer code'),false,true);
		$this->core->blog->settings->{$this->plugin_id}->put('header_code',base64_encode(''),'string',__('Header code'),false,true);
		$this->core->blog->settings->{$this->plugin_id}->put('footer_code',base64_encode(''),'string',__('Footer code'),false,true);
		$this->core->blog->settings->{$this->plugin_id}->put('backup_ext','.html.txt','string',__('Extension Backup Files'),false,true);
		# user config plugin (TODO: specific settings)
		//$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
		//$this->core->auth->user_prefs->$this->plugin_id->put('enabled',false,'boolean',__('Enable plugin'),false,true);
	}

	/* --== STANDARD FUNCTIONS ==-- */

	protected $plugin_id;				// ID plugin
	protected $admin_url;				// admin url plugin
	protected $options = array();		// options plugin

	public static function init($options=array(), $instanceName=__CLASS__) {
		global $core;
		try {
			if(!isset($core->{$instanceName})) {
				$core->{$instanceName} = new self($options);
			} else {
				throw new LogicException(sprintf(__('Conflict: dcCore or other plugin, and %s plugin.'), $instanceName));
			}
		} catch(Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	public function __construct($options=array()) {
		global $core;
		$this->core = &$core;
		# check plugin_id and admin url
		if(!array_key_exists('root', $options) || !is_file($options['root'].'/_define.php')) {
			$options['root'] = dirname(__FILE__);
			if(!is_file($options['root'].'/_define.php')) { $options['root'] = dirname($options['root']); }
		}
		if(!is_file($options['root'].'/_define.php')) { throw new DomainException(__('Invalid plugin directory')); }
		$this->plugin_id = basename($options['root']);
		$this->admin_url = 'admin.plugin.'.$this->plugin_id;
		
		# default options
		if(!is_array($options)) { $options = array(); }
		$options['icons'] = array_merge(
			array(
				'small' => '/inc/icon-small.png',
				'large' => '/inc/icon-large.png'
			),
			(array_key_exists('icons', $options) && is_array($options['icons']) ? $options['icons'] : array())
		);
		$this->options = array_merge(
			array(
				'perm'		=> 'admin'
			),
			$options
		);

		# set default settings if empty
		if(is_callable(array($this, 'setDefaultSettings'))) { $this->setDefaultSettings(); }
	}

	public function install($dcMinVer=null) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		try {
			# check DC version
			if(!empty($dcMinVer) && is_string($dcMinVer)) {
				if (version_compare(DC_VERSION, $dcMinVer, '<')) {
					$this->core->plugins->deactivateModule($this->plugin_id);
					throw new Exception(sprintf(__('%s require Dotclear version %s or more.'), $this->core->plugins->moduleInfo($this->plugin_id, 'name'), $dcMinVer));
				}
			}
			# check plugin versions
			$new_version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
			$old_version = $this->core->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) { return; }

			# --BEHAVIOR-- pluginInstallActions
			if($this->core->callBehavior('pluginInstallActions', $this->plugin_id) === false) {
				throw new Exception(sprintf(__('[Plugin %s] Unknown error in installation.'), $this->core->plugins->moduleInfo($this->plugin_id, 'name')));
			}

			# default settings
			if(is_callable(array($this, 'setDefaultSettings'))) { $this->setDefaultSettings(); }
			$this->core->setVersion($this->plugin_id, $new_version);
			return true;
		} catch (Exception $e) {
			$this->core->error->add($e->getMessage());
		}
		return false;
	}

	public function adminMenu($menu='Plugins') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $_menu;
		$_menu[$menu]->addItem(
			html::escapeHTML(__($this->core->plugins->moduleInfo($this->plugin_id,'name'))),		// Item menu
			$this->core->adminurl->get($this->admin_url),											// Page admin url
			dcPage::getPF($this->plugin_id.$this->options['icons']['small']),						// Icon menu
			preg_match(																																		// Pattern url
				'/'.$this->core->adminurl->get($this->admin_url).'(&.*)?$/',
				$_SERVER['REQUEST_URI']
			),
			$this->core->auth->check($this->options['perm'], $this->core->blog->id)					// Permissions minimum
		);
	}

	public function adminDashboardFavs($core, $favs) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		$favs->register($this->plugin_id, array(
			'title'			=> $core->plugins->moduleInfo($this->plugin_id, 'name'),
			'url'			=> $core->adminurl->get($this->admin_url),
			'small-icon'	=> dcPage::getPF($this->plugin_id.$this->options['icons']['small']),
			'large-icon'	=> dcPage::getPF($this->plugin_id.$this->options['icons']['large']),
			'permissions'	=> $this->options['perm']
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
		$config = is_file(path::real($this->core->plugins->moduleInfo($this->plugin_id, 'root').'/_config.php')) ? $this->core->adminurl->get('admin.plugins', array('module' => $this->plugin_id,'conf' => 1, 'redir' => $this->core->adminurl->get($this->admin_url))) : null;
		return '<p class="right">
					<img style="vertical-align: middle;" src="'.dcPage::getPF($this->plugin_id.$this->options['icons']['small']).'" alt="'.__('icon plugin').'"/>&nbsp;&nbsp;'.
					($config ? '<a href="'.$config.'">'.__('Settings').'</a>&nbsp;-&nbsp;' : '').
					html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'name')).'&nbsp;'.
					__('Version').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'version')).'&nbsp;-&nbsp;'.
					__('Author(s)').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'author')).
					($details ? '&nbsp;-&nbsp;<a href="'.$details.'">'.__('Details').'</a>' : '').
					($support ? '&nbsp;-&nbsp;<a href="'.$support.'">'.__('Support').'</a>' : '').'
				</p>
		';
	}

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
			return $this->admin_url;
		} else {
			return $this->core->plugins->moduleInfo($this->plugin_id, $item);
		}
	}
	
	public function jsLoad($src) {
		return dcPage::jsLoad(dcPage::getPF($this->plugin_id.'/'.ltrim($src, '/')), $this->core->plugins->moduleInfo($this->plugin_id, 'version'));
	}
	
	public function cssLoad($src, $media='screen') {
		return dcPage::cssLoad(dcPage::getPF($this->plugin_id.'/'.ltrim($src, '/')), $media, $this->core->plugins->moduleInfo($this->plugin_id, 'version'));
	}
	
}
