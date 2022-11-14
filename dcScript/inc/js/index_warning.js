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

/** @version 0.2.0 */

(function(undefined) {
    'use strict';

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
