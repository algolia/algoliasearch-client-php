<?php

/*
 * Copyright (c) 2013 Algolia
 * http://www.algolia.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

namespace AlgoliaSearch;

class Version
{
    const VALUE = '1.25.0';

    public static $custom_value = '';

    private static $prefixUserAgentSegments = '';
    private static $suffixUserAgentSegments = '';

    // Method untouched to keep backward compatibility
    public static function get()
    {
        return self::VALUE.static::$custom_value;
    }

    public static function getUserAgent()
    {
        $userAgent = self::$prefixUserAgentSegments.'Algolia for PHP ('.self::VALUE.')'.static::$suffixUserAgentSegments;

        // Keep backward compatibility
        $userAgent .= static::$custom_value;

        return $userAgent;
    }

    public static function addPrefixUserAgentSegment($segment, $version)
    {
        $prefix = $segment.' ('.$version.'); ';

        if (false === mb_strpos(self::getUserAgent(), $prefix)) {
            self::$prefixUserAgentSegments = $prefix . self::$prefixUserAgentSegments;
        }
    }

    public static function addSuffixUserAgentSegment($segment, $version)
    {
        $suffix = '; '.$segment.' ('.$version.')';

        if (false === mb_strpos(self::getUserAgent(), $suffix)) {
            self::$suffixUserAgentSegments .= $suffix;
        }
    }

    public static function clearUserAgentSuffixesAndPrefixes()
    {
        self::$suffixUserAgentSegments = '';
        self::$prefixUserAgentSegments = '';
    }
}
