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

namespace Dotclear\Plugin\dcScript;

use Dotclear\App;

class _crypt
{
    ### Constants ###
    public const MCRYPT         = 'mcrypt';
    public const OPENSSL        = 'openssl';
    public const NO_CRYPT       = 'nocrypt';
    public const DEFAULT_CRYPT  = self::NO_CRYPT;
    public const OPENSSL_METHOD = 'AES-256-CBC';

    /**
     * encrypt
     *
     * @param  string $str
     * @param  string $key
     * @param  string $cryptLib
     * @return string
     */
    public static function encrypt(string $str, string $key, string $cryptLib = self::DEFAULT_CRYPT): string
    {
        $key = pack('H*', hash('sha256', $key));
        if ($cryptLib == self::OPENSSL) {
            $ivlen = openssl_cipher_iv_length(self::OPENSSL_METHOD);
            $iv    = openssl_random_pseudo_bytes($ivlen);

            return trim(base64_encode($iv . openssl_encrypt($str, self::OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, $iv)));
        } elseif ($cryptLib == self::NO_CRYPT) {
            return trim(base64_encode($str));
        } elseif ($cryptLib == self::MCRYPT) {       // depreciate
            return self::encrypt($str, $key, self::DEFAULT_CRYPT);
        }                                      // unknown cryptLib

        return self::encrypt($str, $key, self::getCryptLib());
    }

    /**
     * decrypt
     *
     * @param  string $str
     * @param  string $key
     * @param  string $cryptLib
     * @return string
     */
    public static function decrypt(string $str, string $key, string $cryptLib = self::DEFAULT_CRYPT): string
    {
        $key = pack('H*', hash('sha256', $key));
        if ($str == '') {
            return '';
        }
        if ($cryptLib == self::OPENSSL) {
            $ivlen = openssl_cipher_iv_length(self::OPENSSL_METHOD);
            $str   = base64_decode($str);

            return trim(openssl_decrypt(substr($str, $ivlen), self::OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, substr($str, 0, $ivlen)));
        } elseif ($cryptLib == self::NO_CRYPT) {
            return trim(base64_decode($str));
        } elseif ($cryptLib == self::MCRYPT) {       // depreciate
            return self::decrypt($str, $key, self::DEFAULT_CRYPT);
        }                                      // unknown cryptLib

        return self::decrypt($str, $key, self::getCryptLib());
    }

    /**
     * getCryptKey
     *
     * @param  string $salt
     * @return string
     */
    public static function getCryptKey(string $salt = ''): string
    {
        if (empty($salt)) {
            $salt = App::config()->masterKey();
        }

        return sha1($_SERVER['HTTP_HOST'] . $salt);
    }

    /**
     * getCryptLib
     *
     * @return string
     */
    public static function getCryptLib(): string
    {
        $lib = My::settings()->get('crypt_lib');

        return (empty($lib) ? self::DEFAULT_CRYPT : $lib);
    }
}
