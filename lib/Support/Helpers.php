<?php

namespace Algolia\AlgoliaSearch\Support;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\ExceededRetriesException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;

final class Helpers
{
    /**
     * When building a query string, array values must be json_encoded.
     * This function can be used to turn any array into a Algolia-valid query string.
     *
     * Do not use a typical implementation where ['key' => ['one', 'two']] is
     * turned into key[1]=one&key[2]=two. Algolia will not understand key[x].
     * It should be turned into key=['one','two'] (before being url_encoded).
     *
     * @return string The urlencoded query string to send to Algolia
     */
    public static function buildQuery(array $args)
    {
        if (!$args) {
            return '';
        }

        $args = array_map(function ($value) {
            if (is_array($value)) {
                // PHP converts `true,false` in arrays to `1,`, so we create strings instead
                // to avoid sending wrong values
                $values = array_map(function ($v) {
                    if (is_bool($v)) {
                        return $v ? 'true' : 'false';
                    }

                    return $v;
                }, $value);

                // We then return the array as a string comma separated
                return implode(',', $values);
            }
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return $value;
        }, $args);

        return http_build_query($args, encoding_type: PHP_QUERY_RFC3986);
    }

    /**
     * Wrapper for json_decode that throws when an error occurs.
     *
     * This function is extracted from Guzzlehttp/Guzzle package which is not
     * compatible with PHP 5.3 so the client cannot always use it.
     *
     * @param string $json  JSON data to parse
     * @param bool   $assoc when true, returned objects will be converted
     *                      into associative arrays
     * @param int    $depth user specified recursion depth
     *
     * @throws \InvalidArgumentException if the JSON cannot be decoded
     *
     * @see http://www.php.net/manual/en/function.json-decode.php
     */
    public static function json_decode($json, $assoc = false, $depth = 512)
    {
        $data = \json_decode($json, $assoc, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_decode error: '.json_last_error_msg());
        }

        return $data;
    }

    /**
     * Generic helper which retries a function until some conditions are met.
     *
     * @param object   $object             Object calling the function
     * @param string   $function           Function to be called
     * @param array    $args               Arguments to be passed to the function
     * @param callable $validate           Condition to be met to stop the retry
     * @param int      $maxRetries         Max number of retries
     * @param int      $timeout            Timeout
     * @param string   $timeoutCalculation name of the method to call to calculate the timeout
     *
     * @return object the last result of the function
     *
     * @throws ExceededRetriesException
     */
    public static function retryUntil(
        $object,
        $function,
        array $args,
        callable $validate,
        $maxRetries,
        $timeout,
        $timeoutCalculation = 'Algolia\AlgoliaSearch\Support\Helpers::linearTimeout'
    ) {
        $retry = 0;

        while ($retry < $maxRetries) {
            try {
                $res = call_user_func_array([$object, $function], $args);

                if ($validate($res)) {
                    return $res;
                }
            } catch (\Exception $e) {
                // if the task is interrupted, just return
                return null;
            }

            ++$retry;
            usleep(
                call_user_func_array($timeoutCalculation, [$timeout, $retry])
            );
        }

        throw new ExceededRetriesException('Maximum number of retries ('.$maxRetries.') exceeded.');
    }

    /**
     * Helper for Api keys which retries a function until some conditions are met.
     *
     * @param string       $operation
     * @param SearchClient $searchClient       search client
     * @param string       $key
     * @param array        $apiKey
     * @param int          $maxRetries         Max number of retries
     * @param int          $timeout            Timeout
     * @param string       $timeoutCalculation name of the method to call to calculate the timeout
     * @param array        $requestOptions
     *
     * @throws ExceededRetriesException
     */
    public static function retryForApiKeyUntil(
        $operation,
        $searchClient,
        $key,
        $apiKey,
        $maxRetries,
        $timeout,
        $timeoutCalculation = 'Algolia\AlgoliaSearch\Support\Helpers::linearTimeout',
        $requestOptions = []
    ) {
        $retry = 0;

        while ($retry < $maxRetries) {
            try {
                $response = $searchClient->getApiKey($key, $requestOptions);

                // In case of an addition, if there was no error, the $key has been added as it should be
                if ('add' === $operation) {
                    return $response;
                }

                // In case of an update, check if the key has been updated as it should be
                if ('update' === $operation) {
                    if (self::isKeyUpdated($response, $apiKey)) {
                        return $response;
                    }
                }

                // Else try again ...
            } catch (NotFoundException $e) {
                // In case of a deletion, if there was an error, the $key has been deleted as it should be
                if (
                    'delete' === $operation
                    && 404 === $e->getCode()
                ) {
                    return null;
                }

                // Else try again ...
            }

            ++$retry;
            usleep(
                call_user_func_array($timeoutCalculation, [$timeout, $retry])
            );
        }

        throw new ExceededRetriesException('Maximum number of retries ('.$maxRetries.') exceeded.');
    }

    /**
     * Define timeout before retry.
     *
     * @param int $defaultTimeout
     * @param int $retries
     *
     * @return float|int
     */
    private static function linearTimeout($defaultTimeout, $retries)
    {
        // minimum between timeout and 200 milliseconds * number of retries
        // Convert into microseconds for usleep (* 1000)
        return min($defaultTimeout, $retries * 200) * 1000;
    }

    private static function isKeyUpdated($key, $keyParams)
    {
        $upToDate = true;
        foreach ($keyParams as $param => $value) {
            if (isset($key[$param])) {
                $upToDate &= $key[$param] === $value;
            }
        }

        return $upToDate;
    }
}
