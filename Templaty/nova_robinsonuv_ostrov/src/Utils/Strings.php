<?php

namespace App\Utils;

/**
 * Class Strings
 * @package App\Utils
 */
class Strings
{

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return strlen($needle) === 0 || substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * @param string $s
     * @return string
     */
    public static function lower($s)
    {
        return mb_strtolower($s, 'UTF-8');
    }

    /**
     * @param string $s
     * @return string
     */
    public static function firstLower($s)
    {
        return self::lower(substr($s, 0, 1)) . substr($s, 1);
    }

    /**
     * @param string $s
     * @return string
     */
    public static function upper($s)
    {
        return mb_strtoupper($s, 'UTF-8');
    }

    /**
     * @param string $s
     * @return string
     */
    public static function firstUpper($s)
    {
        return self::upper(substr($s, 0, 1)) . substr($s, 1);
    }

    /**
     * @param string $s
     * @param array $delimeters
     * @return string
     */
    public static function camel($s, $delimeters = ['-', '', '_'])
    {
        return str_replace($delimeters, '', ucwords ($s, implode('', $delimeters)));
    }

    /**
     * This file is part of the Nette Framework (https://nette.org)
     * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
     *
     * Converts to ASCII.
     * @param  string  UTF-8 encoding
     * @return string  ASCII
     */
    public static function toAscii($s)
    {
        static $transliterator = null;

        if ($transliterator === null && class_exists('Transliterator', false)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        }

        $s = preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]#u', '', $s);
        $s = strtr($s, '`\'"^~?', "\x01\x02\x03\x04\x05\x06");
        $s = str_replace(
            ["\xE2\x80\x9E", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x9A", "\xE2\x80\x98", "\xE2\x80\x99", "\xC2\xB0"],
            ["\x03", "\x03", "\x03", "\x02", "\x02", "\x02", "\x04"],
            $s
        );
        if ($transliterator !== null) {
            $s = $transliterator->transliterate($s);
        }
        if (ICONV_IMPL === 'glibc') {
            $s = str_replace(
                ["\xC2\xBB", "\xC2\xAB", "\xE2\x80\xA6", "\xE2\x84\xA2", "\xC2\xA9", "\xC2\xAE"],
                ['>>', '<<', '...', 'TM', '(c)', '(R)'],
                $s
            );
            $s = iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $s);
            $s = strtr(
                $s,
                "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e"
                . "\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3"
                . "\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8"
                . "\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe"
                . "\x96\xa0\x8b\x97\x9b\xa6\xad\xb7",
                'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.'
            );
            $s = preg_replace('#[^\x00-\x7F]++#', '', $s);
        } else {
            $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        }
        $s = str_replace(['`', "'", '"', '^', '~', '?'], '', $s);
        return strtr($s, "\x01\x02\x03\x04\x05\x06", '`\'"^~?');
    }

    /**
     * This file is part of the Nette Framework (https://nette.org)
     * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
     *
     * Converts to web safe characters [a-z0-9-] text.
     * @param  string  UTF-8 encoding
     * @param  string  allowed characters
     * @param  bool
     * @return string
     */
    public static function webalize($s, $charlist = null, $lower = true)
    {
        $s = self::toAscii($s);
        if ($lower) {
            $s = strtolower($s);
        }
        $s = preg_replace('#[^a-z0-9' . ($charlist !== null ? preg_quote($charlist, '#') : '') . ']+#i', '-', $s);
        $s = trim($s, '-');
        return $s;
    }

    /**
     * @param string $emails
     * @return array
     * <code>
     * $formatted = formatEmails('foo@bar.cz, Foo Bar <foo@bar.cz>');
     * var_dump($formatted) = [
     *      foo@bar.cz,
     *      foo@bar.cz => 'Foo Bar',
     * ];
     * </code>
     */
    public static function formatEmails(string $emails)
    {
        $emails = explode(',', $emails);
        $formatted = [];

        foreach ($emails as $emailsItem) {
            if (preg_match('#^(.+) +<(.*)>\z#', $emailsItem, $matches)) {
                $formatted[trim($matches[2])] = trim($matches[1]);
            } else {
                $formatted[] = trim($emailsItem);
            }
        }

        return $formatted;
    }
}
