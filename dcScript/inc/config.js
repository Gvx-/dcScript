/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of dcScript for Dotclear 2.
 * Copyright © 2014-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/

/*global */
(function ($, window, document, undefined) {
	'use strict';
	$(document).ready(function () {
		$('input[name="save"]').attr('disabled', 'disabled');
		$('#scope').on('change', function (e) {
			document.body.style.cursor = 'wait';
			$('#scope_go').trigger('click');
		});
		$('form input:not(#scope)').on('change', function (e) {
			$('#scope').attr('disabled', 'disabled');
			$('input[name="save"]').removeAttr('disabled');
		});
	});
}(jQuery, window, document));