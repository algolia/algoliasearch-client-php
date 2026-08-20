<?php

namespace Algolia\AlgoliaSearch\Support;

use Psr\Http\Message\ResponseInterface;

final class RequestId
{
    public const HEADER = 'request-id';

    public const QUERY_PARAMETER = 'x-algolia-request-id';

    public const CORRELATION_ID_HEADER = 'Correlation-ID';

    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    private const LENGTH = 11;

    /**
     * Mints a fresh identifier for the `request-id` header. Degrades to a non-cryptographic
     * source when the entropy pool is unavailable: the identifier is a tracing breadcrumb, not a
     * secret, and a request must not fail over it.
     *
     * @return string 11 base62 characters
     */
    public static function generate()
    {
        $alphabetLength = strlen(self::ALPHABET);
        $id = '';

        try {
            $bytes = random_bytes(self::LENGTH);
        } catch (\Throwable $e) {
            $bytes = '';

            for ($i = 0; $i < self::LENGTH; ++$i) {
                $bytes .= chr(mt_rand(0, 255));
            }
        }

        for ($i = 0; $i < self::LENGTH; ++$i) {
            $id .= self::ALPHABET[ord($bytes[$i]) % $alphabetLength];
        }

        return $id;
    }

    /**
     * @param array $headers list of header name/value pairs
     *
     * @return bool whether a `request-id` header is already set, whatever its casing
     */
    public static function isPresentInHeaders($headers)
    {
        return self::hasKey($headers, self::HEADER);
    }

    /**
     * @param array $queryParameters list of query parameter name/value pairs
     *
     * @return bool whether an `x-algolia-request-id` parameter is already set, whatever its casing
     */
    public static function isPresentInQueryParameters($queryParameters)
    {
        return self::hasKey($queryParameters, self::QUERY_PARAMETER);
    }

    /**
     * @return null|string the response's `Correlation-ID`, or null when absent or empty
     */
    public static function correlationIdOf(ResponseInterface $response)
    {
        $correlationId = $response->getHeaderLine(self::CORRELATION_ID_HEADER);

        return '' === $correlationId ? null : $correlationId;
    }

    /**
     * @param mixed  $values
     * @param string $name   the lowercase name to look for
     *
     * @return bool
     */
    private static function hasKey($values, $name)
    {
        if (!is_array($values)) {
            return false;
        }

        foreach (array_keys($values) as $key) {
            if (strtolower((string) $key) === $name) {
                return true;
            }
        }

        return false;
    }
}
