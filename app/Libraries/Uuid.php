<?php

/**
 * UUID Class
 *
 * This implements the abilities to create UUIDs for CodeIgniter.
 * Code has been borrowed from the following comments on php.net
 * and has been optimized for CodeIgniter use.
 * http://www.php.net/manual/en/function.uniqid.php#94959
 *
 * @category Libraries
 * @author Dan Storm
 * @link http://catalystcode.net/
 * @license GNU LGPL
 * @version 2.1
 */

namespace App\Libraries;

class Uuid
{
    /**
     * Generate a version 3 UUID (MD5-based).
     *
     * @param string $name
     * @param string|null $namespace
     * @return string|false
     */
    public function v3(string $name, ?string $namespace = null): string|false
    {
        if (is_null($namespace)) {
            $namespace = $this->v4();
        }

        if (empty($name)) {
            return false;
        }

        if (!$this->is_valid($namespace)) {
            return false;
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace(['-', '{', '}'], '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ($i = 0; $i < strlen($nhex); $i += 2) {
            $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }

        // Calculate hash value
        $hash = md5($nstr . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',
            substr($hash, 0, 8), // 32 bits for "time_low"
            substr($hash, 8, 4), // 16 bits for "time_mid"
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000, // 16 bits for "time_hi_and_version"
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000, // 16 bits for "clk_seq_hi_res" and "clk_seq_low"
            substr($hash, 20, 12) // 48 bits for "node"
        );
    }

    /**
     * Generate a version 4 UUID (random-based).
     *
     * @param bool $trim
     * @return string
     */
    public function v4(bool $trim = false): string
    {
        $format = $trim ? '%04x%04x%04x%04x%04x%04x%04x%04x' : '%04x%04x-%04x-%04x-%04x-%04x%04x%04x';

        return sprintf($format,
            random_int(0, 0xffff), random_int(0, 0xffff), // 32 bits for "time_low"
            random_int(0, 0xffff), // 16 bits for "time_mid"
            random_int(0, 0x0fff) | 0x4000, // 16 bits for "time_hi_and_version"
            random_int(0, 0x3fff) | 0x8000, // 16 bits for "clk_seq_hi_res" and "clk_seq_low"
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff) // 48 bits for "node"
        );
    }

    /**
     * Generate a version 5 UUID (SHA1-based).
     *
     * @param string $name
     * @param string|null $namespace
     * @return string|false
     */
    public function v5(string $name, ?string $namespace = null): string|false
    {
        if (is_null($namespace)) {
            $namespace = $this->v4();
        }

        if (empty($name)) {
            return false;
        }

        if (!$this->is_valid($namespace)) {
            return false;
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace(['-', '{', '}'], '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ($i = 0; $i < strlen($nhex); $i += 2) {
            $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }

        // Calculate hash value
        $hash = sha1($nstr . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',
            substr($hash, 0, 8), // 32 bits for "time_low"
            substr($hash, 8, 4), // 16 bits for "time_mid"
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000, // 16 bits for "time_hi_and_version"
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000, // 16 bits for "clk_seq_hi_res" and "clk_seq_low"
            substr($hash, 20, 12) // 48 bits for "node"
        );
    }

    /**
     * Validate a UUID.
     *
     * @param string $uuid
     * @return bool
     */
    public function is_valid(string $uuid): bool
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }
}