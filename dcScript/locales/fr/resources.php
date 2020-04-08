<?php
/**
  * This file is part of dcScript plugin for Dotclear 2.
  *
  * @package Dotclear\plungin\dcScript
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2014-2020 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if(!defined('DC_CONTEXT_ADMIN')) { return; }

if (!isset($__resources['help']['dcScript-config'])) {
	$__resources['help']['dcScript-config'] = dirname(__FILE__).'/help/config.html';
}
if (!isset($__resources['help']['dcScript-edit'])) {
	$__resources['help']['dcScript-edit'] = dirname(__FILE__).'/help/edit.html';
}
if (!isset($__resources['help']['dcScript-warning'])) {
	$__resources['help']['dcScript-warning'] = dirname(__FILE__).'/help/warning.html';
}
