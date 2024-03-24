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
$this->registerModule(
    'dcScript',							// Name
    'Add public script for DC',			// Description
    'Gvx',								// Author
    '4.3.0-dev-r0001',					// Version
    [
        'permissions'			=> 'My',
        'type'                 	=> 'plugin',
        'Priority'             	=> 1010,
        'support'	/* url */   => 'http://forum.dotclear.org/viewtopic.php?pid=335785#p335785',
        'details' 	/* url */   => 'https://github.com/Gvx-/dcScript',
        'requires'	/* id(s) */ => [
            ['core', '2.28'],
        ],
        'settings' => [
            //'self'			=> '', 														            // Optional: '#tab' (or false since 2.17)
            //'blog'			=> '#params.id',											            // Optional: '#params.id'
            //'pref'			=> '#user-options.id',										            // Optional: '#user-options.id'
        ],
        'repository' => 'https://raw.githubusercontent.com/Gvx-/dcScript/master/dcstore.xml',	    	// Optional: URL
    ]
);

# ---------------------------------------------------------
# use codemirror version 5.65.15
# see: http://codemirror.net/
# ---------------------------------------------------------
