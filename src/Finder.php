<?php

namespace Altivebir\Mystique;

/**
 * Class Finder
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\Mystique
 */
class Finder
{
    /**
     * Glob files with braces support.
     *
     * @param string $pattern
     * @param int    $flags
     *
     * @return array
     *
     * @example
     * FileFinder::glob('/path/{*.ext,*.php'});
     * // => ['/path/file.ext', '/path/file.php']
     */
    public static function glob(string $pattern, int $flags = 0): array
    {
        if (defined('GLOB_BRACE') && !static::startsWith($pattern, '{')) {
            return glob($pattern, $flags | GLOB_BRACE | GLOB_NOSORT);
        }

        $files = [];

        foreach (static::expandBraces($pattern) as $file) {
            $files = array_merge($files, glob($file, $flags | GLOB_NOSORT) ?: []);
        }

        return $files;
    }

    /**
     * Checks if string starts with a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     *
     * @example
     * FileFinder::startsWith('jason', 'jas');
     * // => true
     */
    public static function startsWith(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && (string) $needle === substr($haystack, 0, strlen($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Expand a glob braces to array.
     *
     * @param string $pattern
     *
     * @return array
     *
     * @example
     * FileFinder::expandBraces('foo/{2,3}/bar');
     * // => ['foo/2/bar', 'foo/3/bar']
     */
    public static function expandBraces(string $pattern): array
    {
        $braces = [];
        $expanded = [];
        $callback = function ($matches) use (&$braces) {

            $index = '{' . count($braces) . '}';
            $braces[$index] = $matches[0];

            return $index;
        };

        if (preg_match($regex = '/{((?:[^{}]+|(?R))*)}/', $pattern, $matches, PREG_OFFSET_CAPTURE)) {

            list($matches, $replaces) = $matches;

            foreach (explode(',', preg_replace_callback($regex, $callback, $replaces[0])) as $replace) {
                $expand = substr_replace($pattern, strtr($replace, $braces), $matches[1], strlen($matches[0]));
                $expanded = array_merge($expanded, static::expandBraces($expand));
            }
        }

        return $expanded ?: [$pattern];
    }
}