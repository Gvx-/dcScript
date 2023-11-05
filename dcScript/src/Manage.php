<?php
/**
  * This file is part of dcScript plugin for Dotclear 2.
  *
  * @package Dotclear\plugin\dcScript
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2014-2023 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

declare(strict_types=1);

namespace Dotclear\Plugin\dcScript;

use Exception;
use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Form\{
    Div,
    Form,
    Hidden,
    Input,
    Link,
    Para,
    Submit,
    Textarea
};

class Manage extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!empty($_POST)) {
            try {
                # submit tab 1 (standard page)
                if (isset($_POST['update_header'])) {
                    My::settings()->put('header_code', _crypt::encrypt(trim($_POST['header_code']) . "\n", _crypt::getCryptKey(), _crypt::getCryptLib()));
                    App::blog()->triggerBlog();
                    Notices::addSuccessNotice(__('Code successfully updated.'));
                    My::redirect([], '#tab-header');
                }
                # submit tab 2 (standard page)
                if (isset($_POST['update_footer'])) {
                    My::settings()->put('footer_code', _crypt::encrypt(trim($_POST['footer_code']) . "\n", _crypt::getCryptKey(), _crypt::getCryptLib()));
                    App::blog()->triggerBlog();
                    Notices::addSuccessNotice(__('Code successfully updated.'));
                    My::redirect([], '#tab-footer');
                }
            } catch(exception $e) {
                //App::error()->add($e->getMessage());
                App::error()->add(__('Unable to save the code'));
            }
        }

        if (!empty($_GET)) {
            try {
                # download code (standard page)
                if (isset($_GET['download']) && in_array($_GET['download'], ['header', 'footer'], true)) {
                    $filename = '"' . trim(App::blog()->name()) . '_' . date('Y-m-d') . '_' . $_GET['download'] . '.' . trim((string) My::settings()->get('backup_ext'), '.') . '"';
                    header('Content-Disposition: attachment;filename=' . $filename);
                    header('Content-Type: text/plain; charset=UTF-8');
                    echo _crypt::decrypt((string) My::settings()->get($_GET['download'] . '_code'), _crypt::getCryptKey(), _crypt::getCryptLib());
                    exit;
                }
            } catch(exception $e) {
                App::error()->add(__('Unable to save the file'));
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }
        Page::openModule(My::name());		// On prépare l'affichage du haut de page
        echo Page::breadcrumb([__('Plugin') => '', My::name() => '']) . Notices::getNotices();		// On ajoute le menu façon Dotclear et on affiche d'éventuelle messages

        $header            = html::escapeHTML(_crypt::decrypt((string) My::settings()->get('header_code'), _crypt::getCryptKey(), _crypt::getCryptLib()));
        $footer            = html::escapeHTML(_crypt::decrypt((string) My::settings()->get('footer_code'), _crypt::getCryptKey(), _crypt::getCryptLib()));
        $urlFormAction     = My::manageUrl();
        $urlDownloadHeader = My::manageUrl(['download' => 'header']);
        $urlDownloadFooter = My::manageUrl(['download' => 'footer']);
        $idFormHeader      = My::id() . '-form-header';
        $idFormFooter      = My::id() . '-form-footer';

        // Begin CodeMirror
        echo My::cssLoad('/codemirror/lib/codemirror');
        echo My::jsLoad('/codemirror/lib/codemirror');
        echo My::jsLoad('/codemirror/mode/css/css');
        echo My::jsLoad('/codemirror/mode/htmlmixed/htmlmixed');
        echo My::jsLoad('/codemirror/mode/javascript/javascript');
        echo My::jsLoad('/codemirror/mode/xml/xml');
        echo My::jsLoad('/codemirror/addon/comment/comment');
        echo My::jsLoad('/codemirror/addon/dialog/dialog');
        echo My::jsLoad('/codemirror/addon/display/fullscreen');
        echo My::jsLoad('/codemirror/addon/edit/matchbrackets');
        echo My::jsLoad('/codemirror/addon/edit/matchtags');
        echo My::jsLoad('/codemirror/addon/edit/trailingspace');
        echo My::jsLoad('/codemirror/addon/fold/brace-fold');
        echo My::jsLoad('/codemirror/addon/fold/comment-fold');
        echo My::jsLoad('/codemirror/addon/fold/foldcode');
        echo My::jsLoad('/codemirror/addon/fold/foldgutter');
        echo My::jsLoad('/codemirror/addon/fold/indent-fold');
        echo My::jsLoad('/codemirror/addon/fold/xml-fold');
        echo My::jsLoad('/codemirror/addon/search/search');
        echo My::jsLoad('/codemirror/addon/search/searchcursor');
        echo My::jsLoad('/codemirror/addon/selection/active-line');
        // End CodeMirror
        echo My::jsLoad('Manage');
        echo My::cssLoad('Manage');
        echo Page::jsConfirmClose($idFormHeader, $idFormFooter);
        echo Page::jsPageTabs($_REQUEST['tab'] ?? 'tab-header');

        # tab header
        echo
        (new Div('tab-header'))->class('multi-part')->title(__('Header code') . ' - (' . ((string) My::settings()->get('header_code_enabled') ? __('Enabled') : __('Disabled')) . ')')->items([
            (new Form($idFormHeader))->method('post')->action($urlFormAction)->items([
                App::nonce()->formNonce(),
                (new Hidden('change_header', '')),														// for check change in CodeMirror => jsConfirmClose()
                (new Textarea('header_code', $header . "\n"))->class('maximal')->cols(120)->rows(25),		// para??
                (new Para())->class('button-bar clear')->items([
                    (new Submit('update_header', __('Save')))->title(__('Save header script')),
                    (new Input('reset_header', 'reset'))->value(__('Cancel'))->title(__('Undo header changes')),
                    (new Link('export_header'))->class('button')->title(__('Export header script'))->text(__('Download'))->href($urlDownloadHeader),
                ]),
            ]),
        ])->render();

        # tab footer
        echo
        (new Div('tab-footer'))->class('multi-part')->title(__('Footer code') . ' - (' . ((string) My::settings()->get('footer_code_enabled') ? __('Enabled') : __('Disabled')) . ')')->items([
            (new Form($idFormFooter))->method('post')->action($urlFormAction)->items([
                App::nonce()->formNonce(),
                (new Hidden('change_footer', '')),														// for check change in CodeMirror => jsConfirmClose()
                (new Textarea('footer_code', $footer . "\n"))->class('maximal')->cols(120)->rows(25),		// para??
                (new Para())->class('button-bar clear')->items([
                    (new Submit('update_footer', __('Save')))->title(__('Save footer script')),
                    (new Input('reset_footer', 'reset'))->value(__('Cancel'))->title(__('Undo footer changes')),
                    (new Link('export_footer'))->class('button')->title(__('Export footer script'))->text(__('Download'))->href($urlDownloadFooter),
                ]),
            ]),
        ])->render();

        Page::helpBlock(My::id() . '-edit');	// On ajoute l'aide de la page
        Page::closeModule();				// On ferme la page
    }
}
