/**
 * This file is part of dcScript plugin for Dotclear 2.
 *
 * @package Dotclear\plungin\dcScript
 *
 * @author Gvx <g.gvx@free.fr>
 * @copyright © 2014-2021 Gvx
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
'use strict';

/** @version 0.27.0 */

/*global CodeMirror, window, document*/

'use strict';

// polyfill globalThis
if (typeof globalThis === 'undefined') {
    var globalThis = function() {
        if (typeof self !== 'undefined') { return self; }
        if (typeof window !== 'undefined') { return window; }
        if (typeof global !== 'undefined') { return global; }
        if (typeof this !== 'undefined') { return this; }
        throw new Error("impossible de trouver l'objet global");
    }();
}

document.addEventListener('DOMContentLoaded', function() {

    let
        cm_options = {
            mode: 'text/html',
            //theme: 'eclipse',
            //indentWithTabs: true,
            //indentUnit: 2,
            tabSize: 2,
            tabMode: 'indent',
            //lineWrapping: true,
            lineWrapping: false,
            lineNumbers: true,
            matchBrackets: true,
            matchTags: { bothTags: true },
            extraKeys: {
                'Ctrl-J': 'toMatchingTag',
                'Ctrl-Q': function(cm) { cm.foldCode(cm.getCursor()); },
                'F11': function(cm) { cm.setOption('fullScreen', !cm.getOption('fullScreen')); },
                'Esc': function(cm) { if (cm.getOption('fullScreen')) { cm.setOption('fullScreen', false); } }
            },
            showTrailingSpace: true,
            foldGutter: true,
            gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
            styleActiveLine: true
        },
        actionTabCm = function(idBase) { // Actions tabs codemirror
            let
                cm,
                tabActive = document.getElementById('dcScript-form-' + idBase),
                source = document.getElementById(idBase + '_code'),
                saveBtn = document.getElementById('update_' + idBase),
                resetBtn = document.getElementById('reset_' + idBase),
                exportBtn = document.getElementById('export_' + idBase),
                checkChange = document.getElementById('change_' + idBase),
                refresh = function() {
                    cm.refresh();
                    cm.focus();
                    cm.execCommand('goDocEnd');
                };

            if (source) {
                cm = CodeMirror.fromTextArea(source, cm_options);
                if (!cm.doc.getValue()) { exportBtn.classList.add('disabled'); } // Code empty
                refresh();

                cm.on('change', function(e) { // Modification
                    saveBtn.removeAttribute('disabled');
                    resetBtn.removeAttribute('disabled');
                    exportBtn.classList.add('disabled');
                    checkChange.value = 'true';
                });

                tabActive.addEventListener('reset', function() { // Form reset
                    cm.doc.setValue(source.value);
                    refresh();
                    saveBtn.disabled = 'disabled';
                    resetBtn.disabled = 'disabled';
                    exportBtn.classList.add('disabled');
                    if (cm.doc.getValue()) { exportBtn.classList.remove('disabled'); }
                    checkChange.value = '';
                }, false);

                globalThis.addEventListener('hashchange', function() { // tab set focus
                    refresh();
                }, false);
            }
            saveBtn.disabled = 'disabled';
            resetBtn.disabled = 'disabled';
        };

    // processing
    let timeoutID = setTimeout(function() {
        clearTimeout(timeoutID);
        actionTabCm('footer');
        actionTabCm('header');
        globalThis.scrollTo(0, 0); // Go to top
    }, 5);

});
