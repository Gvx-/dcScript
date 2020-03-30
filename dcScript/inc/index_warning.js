/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of dcScript for Dotclear 2.
 * Copyright © 2014-2020 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/

(function (undefined) {
	'use strict';
	// -- INFORMATIONS BLOCK BEGIN ----------------------------------------------------
	var informations = {
		name:			'dcScriptIndexWarning',
		description:	'dcScript for dotclear version 2.7+',
		version:		'0.1.0',
		author:			'Gvx',
		copyright:		'© 2020 Gvx',
		support:		'',
		license:		'http://www.gnu.org/licenses/old-licenses/gpl-2.0.html'
	};
	// -- INFORMATIONS BLOCK END ------------------------------------------------------

	function copy(element, button) {
		if (element) {
			var range = document.createRange();
			var selection = window.getSelection();
			range.selectNode(element);
			selection.removeAllRanges();
			selection.addRange(range);
			if (button) { button.classList.remove('copy_done'); }
			if (document.execCommand('copy')) {
				if (button) { button.classList.add('copy_done'); }
			} else {
				console.log('Erreur de copie de "' + element.id + '"');
			}
		}
	}

	document.addEventListener('DOMContentLoaded', function() {
		document.getElementById('copy_key_crypt').addEventListener('click', function() { copy(document.getElementById('key_crypt'), this); });
		document.getElementById('copy_header_code').addEventListener('click', function() { copy(document.getElementById('header_code'), this); });
		document.getElementById('copy_footer_code').addEventListener('click', function() { copy(document.getElementById('footer_code'), this); });
	});

}());
