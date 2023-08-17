<?php
/**
  * This file is part of dcScript plugin for Dotclear 2.
  *
  * @package Dotclear\plungin\dcScript
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2014-2023 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if(!defined('DC_CONTEXT_ADMIN')) { return; }

(function(string $pluginId): void {
    # TODO:

    if(!isset(dcCore::app()->resources['help']['dcScript-config'])) { dcCore::app()->resources['help']['dcScript-config'] = __DIR__.'/help/config.html'; }
    if(!isset(dcCore::app()->resources['help']['dcScript-edit'])) { dcCore::app()->resources['help']['dcScript-edit'] = __DIR__.'/help/edit.html'; }
    if(!isset(dcCore::app()->resources['help']['dcScript-warning'])) { dcCore::app()->resources['help']['dcScript-warning'] = __DIR__.'/help/warning.html'; }

})(basename(__DIR__));
