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

use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Exception;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Fieldset,
    Input,
    Label,
    Legend,
    Note,
    Para
};

class Config extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::CONFIG));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (empty($_POST['save'])) {
            return true;
        }

        try {
            My::settings()->put('enabled', !empty($_POST['enabled']));
            My::settings()->put('header_code_enabled', !empty($_POST['header_code_enabled']));
            My::settings()->put('footer_code_enabled', !empty($_POST['footer_code_enabled']));
            My::settings()->put('backup_ext', Html::escapeHTML($_POST['backup']));

            Notices::addSuccessNotice(__('Configuration successfully updated'));

            My::redirect(['conf' => 1, 'redir' => App::Backend()->__get('list')->getRedir()]);
        } catch(exception $e) {
            //App::error()->add($e->getMessage());
            App::error()->add(__('Unable to save the configuration'));
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        echo Page::jsConfirmClose('module_config');

        echo
        (new Div())->items([
            (new Fieldset())->class('fieldset')->legend(new Legend(__('Activation')))->fields([
                (new Para())->items([
                    (new Checkbox('enabled', (bool) My::settings()->get('enabled')))->value('1'),
                    (new Label(sprintf(__('Enable %s on this blog'), Html::escapeHTML((string) __(My::name()))), Label::OUTSIDE_LABEL_AFTER))->class('classic')->for('enabled'),
                ]),
                (new Note())->text(__('Enable the plugin on this blog.'))->class('form-note'),
            ]),
            (new Fieldset())->class('fieldset two-cols')->legend(new Legend(__('Active codes')))->fields([
                (new Div())->class('col')->items([
                    (new Para())->items([
                        (new Checkbox('header_code_enabled', (bool) My::settings()->get('header_code_enabled')))->value('1'),
                        (new Label(__('Enable header code'), Label::OUTSIDE_LABEL_AFTER))->class('classic')->for('header_code_enabled'),
                    ]),
                    (new Note())->text(__('Enable public header code.'))->class('form-note'),
                ]),
                (new Div())->class('col')->items([
                    (new Para())->items([
                        (new Checkbox('footer_code_enabled', (bool) My::settings()->get('footer_code_enabled')))->value('1'),
                        (new Label(__('Enable footer code'), Label::OUTSIDE_LABEL_AFTER))->class('classic')->for('footer_code_enabled'),
                    ]),
                    (new Note())->text(__('Enable public footer code.'))->class('form-note'),
                ]),
            ]),
            (new Fieldset())->class('fieldset')->legend(new Legend(__('Options')))->fields([
                (new Para())->items([
                    (new Label(__('Extension Backup Files') . ' :', Label::OUTSIDE_LABEL_BEFORE))->class('classic')->for('backup'),
                    (new Input('backup'))->size(25)->maxlength(255)->value(My::settings()->get('backup_ext')),
                ]),
                (new Note())->text(__('Default extension backup files.'))->class('form-note'),
            ]),
        ])->render();

        //echo Page::helpBlock(My::id().'-config');
        Page::helpBlock(My::id() . '-config');
    }
}
