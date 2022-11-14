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

# define id and class specific plugin
$pluginId = basename(__DIR__);
$pluginClassName = dcCore::app()->plugins->moduleInfo($pluginId, '_class_name');

# Loadings & initialization
if(!empty($pluginClassName)) {
	Clearbricks::lib()->autoload([$pluginClassName => __DIR__.dcCore::app()->plugins->moduleInfo($pluginId, '_class_path')]);
	dcCore::app()->{$pluginClassName} = new $pluginClassName($pluginId);
}
