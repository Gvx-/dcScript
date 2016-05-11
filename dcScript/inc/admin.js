/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of dcScript for Dotclear 2.
 * Copyright © 2014-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/

/*global jQuery,CodeMirror */
(function ($, window, document, undefined) {
	'use strict';
	$(document).ready(function () {
		var
			cm_options = {																	// codemirror options
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
			actions = function () {															// Actions codemirror
				var
					cm,
					el = document.getElementById('code'),
					$actionButtons = $('#update, #reset'),
					$exportButton = $('#export'),
					$checkChange = $('#change'),
					$behaviors = $('#behaviors'),
					$behaviorsGo = $('#behaviors_go'),
					changes = function () {
						$actionButtons.removeAttr('disabled');
						$exportButton.addClass('disabled');
						$checkChange.val('true');
						$behaviors.val($('#behavior_edit').val()).attr('disabled', 'disabled');
						//$behaviorsGo.attr('disabled', 'disabled');
					};
				if (el) {
					cm = CodeMirror.fromTextArea(el, cm_options);
					cm.focus();
					if (!cm.doc.getValue()) {												// Code empty
						$exportButton.addClass('disabled');
					}
					//$behaviorsGo.attr('disabled', 'disabled');
					cm.on('change', function (e) {											// edit change
						changes();
					});
					$('#active').on('change', function (e) {								// active change
						changes();
					});
					$behaviors.on('change', function (e) {									// select behaviors change
						$behaviorsGo./*removeAttr('disabled').*/trigger('click');
					});
					$('form').on('reset', function (e) {									// Action reset
						cm.doc.setValue(el.defaultValue);
						cm.clearHistory();
						cm.focus();
						$actionButtons.attr('disabled', 'disabled');
						if (cm.doc.getValue()) { $exportButton.removeClass('disabled'); }
						$behaviors.removeAttr('disabled');
						$checkChange.val('');
					});
					$(window).on('hashchange', function (e) {								// set focus
						setTimeout(function () { cm.focus(); }, 0);
					});
				}
				$actionButtons.attr('disabled', 'disabled');
			};
			
		// processing
		actions();
		window.scrollTo(0, 0);																// Go to top
	});
}(jQuery, window, document));