<?php

namespace App\Helpers;


class Crypto
{

    /**
     * generating salt using ENCRYPTION KEY
     * @param $salt
     * @return array
     */

    private static function generateSalt($salt)
    {
        $passphrase = env('ENCRYPTE_KEY');
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx . $passphrase . $salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);
        return [$key, $iv];
    }

    /**
     * decodes base64 string via salt
     * @param $base64
     * @return array
     */

    private static function decode($base64)
    {
        $data = base64_decode($base64);
        if (substr($data, 0, 8) !== "Salted__") {
            throw new \InvalidArgumentException();
        }
        $salt = substr($data, 8, 8);
        $ct = substr($data, 16);
        return [$ct, $salt];
    }

    /**
     * encodes data to base64 with salt
     * @param $ct
     * @param $salt
     * @return string
     */

    private static function encode($ct, $salt)
    {
        return base64_encode("Salted__" . $salt . $ct);
    }

    /**
     * encryptes data and returns hash
     * @param $data
     * @param null $salt
     * @return string
     */

    public static function encrypt($data, $salt = null)
    {
        $salt = $salt ?: openssl_random_pseudo_bytes(8);
        list($key, $iv) = self::generateSalt($salt);
        $ct = openssl_encrypt($data, 'aes-256-cbc', $key, true, $iv);
        return self::encode($ct, $salt);
    }

    /**
     * decrypts data from base64 format to array or json
     * @param $base64
     * @return string
     */

    public static function decrypt($base64)
    {
        list($ct, $salt) = self::decode($base64);
        list($key, $iv) = self::generateSalt($salt);
        $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
        return $data;
    }
}