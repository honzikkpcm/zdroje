<?php

namespace App\Utils;

/**
 * Class Random
 * @package App\Utils
 */
class Random
{
    /**
     * @param int $length
     * @param string $charlist
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function generate($length = 32, $charlist = '0-9a-z'): string
    {
        if ($length < 1)
            throw new \InvalidArgumentException('Length must be greater than zero.');

        $charlist = count_chars(preg_replace_callback('#.-.#', function (array $m) {
            return implode('', range($m[0][0], $m[0][2]));
        }, $charlist), 3);

        $chLen = strlen($charlist);

        if ($chLen < 2)
            throw new \InvalidArgumentException('Character list must contain as least two chars.');

        $res = '';

        for ($i = 0; $i < $length; $i++) {
            $res .= $charlist[random_int(0, $chLen - 1)];
        }

        return $res;
    }
}
