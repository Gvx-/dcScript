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

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Plugin\Uninstaller\Uninstaller;

class Uninstall extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::UNINSTALL));
    }

    public static function process(): bool
    {
        // Par sécurtié, on vérifie l'existence du plugin Uninstaller, même si c'est lui qui a dû appeler ce script
        if (!self::status() || !App::plugins()->moduleExists('Uninstaller')) {
            return false;
        }

        // On récupère l'instance singleton de Uninstaller
        Uninstaller::instance()
            ->addUserAction('settings', 'delete_all', My::id())		// Action utilisateur pour effacer les paramètres alliant l'espace de nom du module
            ->addUserAction('plugins', 'delete', My::id())			// Action utilisateur pour effacer les fichiers du module
            ->addUserAction('versions', 'delete', My::id())			// Action utilisateur pour effacer le numéro de version en base du module
            ->addDirectAction('plugins', 'delete', My::id())		// Action directe pour effacer les fichiers du module
            ->addDirectAction('versions', 'delete', My::id())		// Action directe pour effacer le numéro de version en base du module
        ;

        return false;												// aucune action spécifique, on retourne donc false
    }
}
