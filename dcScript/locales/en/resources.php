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
\Dotclear\App::backend()->resources()
    ->set('help', 'dcScript-config', __DIR__ . '/help/config.html')
    ->set('help', 'dcScript-edit', __DIR__ . '/help/edit.html');
