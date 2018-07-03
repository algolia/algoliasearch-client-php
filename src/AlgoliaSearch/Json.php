<?php

namespace AlgoliaSearch;

/**
 * Class Json.
 */
class Json
{
    public static function encode($value, $options = 0)
    {
        $json = json_encode($value, $options);

        self::checkError();

        return $json;
    }

    public static function decode($json, $assoc = false, $depth = 512)
    {
        $value = json_decode($json, $assoc, $depth);

        self::checkError();

        return $value;
    }

    private static function checkError()
    {
        $error = json_last_error();

        if (!$error) {
            return;
        }

        $errorMsg = 'JSON error';
        switch ($error) {
            case JSON_ERROR_DEPTH:
                $errorMsg = 'JSON parsing error: maximum stack depth exceeded';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errorMsg = 'JSON parsing error: unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $errorMsg = 'JSON parsing error: syntax error, malformed JSON';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errorMsg = 'JSON parsing error: underflow or the modes mismatch';
                break;
            // PHP 5.3 less than 1.2.2 (Ubuntu 10.04 LTS)
            case defined('JSON_ERROR_UTF8') ? JSON_ERROR_UTF8 : -1:
                $errorMsg = 'JSON parsing error: malformed UTF-8 characters, possibly incorrectly encoded';
                break;
        }

        throw new AlgoliaException($errorMsg, $error);
    }
}
