<?php
/**
  * This file is part of dcScript plugin for Dotclear 2.
  *
  * @package Dotclear\plugin\dcScript
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2014-2023 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.h_tml
 */

namespace Dotclear\Plugin\dcScript;

use dcCore;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;
use Exception;
use form;

class Config extends Process {

    public static function init(): bool {
        return self::status(My::checkContext(My::CONFIG));
    }

    public static function process(): bool {
        if (!self::status()) { return false; }

        if (empty($_POST['save'])) { return true; }

        try {
            My::settings()->put('enabled', !empty($_POST['enabled']));
            My::settings()->put('header_code_enabled', !empty($_POST['header_code_enabled']));
            My::settings()->put('footer_code_enabled', !empty($_POST['footer_code_enabled']));
            My::settings()->put('backup_ext', Html::escapeHTML($_POST['backup']));

            Notices::addSuccessNotice(__('Configuration successfully updated'));

            dcCore::app()->admin->url->redirect('admin.plugins', ['module' => My::id(), 'conf' => 1, 'chk' => 1, 'redir' => dcCore::app()->admin->__get('list')->getRedir()]);
			
        } catch(exception $e) {
            //dcCore::app()->error->add($e->getMessage());
            dcCore::app()->error->add(__('Unable to save the configuration'));
        }

        return true;
    }

    public static function render(): void {
        if (!self::status()) { return; }

		echo My::jsLoad('config').My::cssLoad('config', 'all').Page::jsConfirmClose('module_config');

		echo
			'<div class="fieldset clear">
				<h3>'.__('Activation').'</h3>
				<p>
					'.form::checkbox('enabled','1', (string)My::settings()?->get('enabled')).
					'<label class="classic" for="enabled">'.sprintf(__('Enable %s on this blog'), Html::escapeHTML((string)__(My::name()))).'</label>
				</p>
				<p class="form-note">'.__('Enable the plugin on this blog.').'</p>
			</div>
			<div id="options">
				<div class="fieldset">
					<h3>'.__('Active codes').'</h3>
					<div class="two-cols clear">
						<div class="col">
							<p>
								'.form::checkbox('header_code_enabled', '1', (string)My::settings()?->get('header_code_enabled'))
								.'<label class="classic" for="header_code_enabled">'.__('Enable header code').'</label>
							</p>
							<p class="form-note">'.__('Enable public header code.').'</p>
						</div>
						<div class="col">
							<p>
								'.form::checkbox('footer_code_enabled', '1', (string)My::settings()?->get('footer_code_enabled'))
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
						'.form::field('backup', 25, 255, (string)My::settings()?->get('backup_ext'), 'classic').'
					</p>
					<p class="form-note">'.__('Default extension backup files.').'</p>
				</div>
			</div>';
		# helpBlock
		Page::helpBlock(My::id().'-config');
		
    }
}