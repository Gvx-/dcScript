/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of dcScript for Dotclear 2.
 * Copyright © 2014-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/

/*global jQuery,CodeMirror */
(function ($, window, document, undefined) {
	'use strict';
	// -- INFORMATIONS BLOCK BEGIN ----------------------------------------------------
	var informations = {
		name:			'dcScriptAdmin',
		description:	'dcScript for dotclear version 2.7+',
		version:		'0.24.0',
		author:			'Gvx',
		copyright:		'© 2014-2015 Gvx',
		support:		'',
		license:		'http://www.gnu.org/licenses/old-licenses/gpl-2.0.html'
	};
	// -- INFORMATIONS BLOCK END ------------------------------------------------------

	$(document).ready(function () {
		var
			cm_options = {
				mode: 'text/html',
				//theme: 'eclipse',
				//indentWithTabs: true,
				//indentUnit: 2,
				tabSize: 2,
				tabMode: 'indent',
				lineWrapping: true,
				lineNumbers: true,
				matchBrackets: true,
				matchTags: { bothTags: true },
				extraKeys: {
					'Ctrl-J': 'toMatchingTag',
					'Ctrl-Q': function (cm) { cm.foldCode(cm.getCursor()); },
					'F11': function (cm) { cm.setOption('fullScreen', !cm.getOption('fullScreen')); },
					'Esc': function (cm) { if (cm.getOption('fullScreen')) { cm.setOption('fullScreen', false); } }
				},
				showTrailingSpace: true,
				foldGutter: true,
				gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
				styleActiveLine: true
			},
			actionTabCm = function (id, tab) {															// Actions tabs codemirror
				var
					cm,
					tabSelector = '#tab-' + tab,
					el = document.getElementById(id + '_code'),
					$actionButtons = $('#update_' + id + ', ' + tabSelector + ' input[type="reset"]'),
					$exportButton = $('#export_' + id),
					$checkChange = $('#change_' + id),
					urlExport = $exportButton.attr('href');
				if (el) {
					cm = CodeMirror.fromTextArea(el, cm_options);
					cm.focus();
					if (!cm.doc.getValue()) {																													// Code empty
						$exportButton.attr('href', tabSelector).addClass('disabled');
					}
					cm.on('change', function (e) {
						$actionButtons.removeAttr('disabled');
						$exportButton.attr('href', tabSelector).addClass('disabled');
						$checkChange.val('true');
					});
					$(tabSelector).on('reset', function (e) {											// Action reset tabs
						cm.doc.setValue(el.value);
						cm.focus();
						$actionButtons.attr('disabled', 'disabled');
						if (cm.doc.getValue()) {
							$exportButton.attr('href', urlExport).removeClass('disabled');
						}
						$checkChange.val('');
					});
					$(window).on('hashchange', function (e) {											// set focus
						setTimeout(function () { cm.focus(); }, 0);
					});
				}
				$actionButtons.attr('disabled', 'disabled');
			};
		// processing
		actionTabCm('header', 1);
		actionTabCm('footer', 2);
		window.scrollTo(0, 0);																																	// Go to top
	});
}(jQuery, window, document));