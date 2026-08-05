<?php

namespace Algolia\AlgoliaSearch\Exceptions;

final class UnreachableException extends AlgoliaException
{
    private const DEFAULT_MESSAGE = 'Unreachable hosts. If the error persists, please visit our help center https://alg.li/support-unreachable-hosts or reach out to the Algolia Support team: https://alg.li/support';

    /**
     * @var array<int, array{host: string, error: RequestException}>
     */
    private $errors = [];

    public function __construct($message = '', $code = 0, $previous = null)
    {
        if (!$message) {
            $message = self::DEFAULT_MESSAGE;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @param array<int, array{host: string, error: RequestException}> $errors every failed attempt, in attempt order
     *
     * @return self
     */
    public static function fromErrors(array $errors)
    {
        $lastError = end($errors);

        if (false === $lastError) {
            return new self();
        }

        $exception = new self(
            self::DEFAULT_MESSAGE.' Last error for '.$lastError['host'].': '.$lastError['error']->getMessage(),
            $lastError['error']->getCode(),
            $lastError['error']
        );
        $exception->errors = $errors;

        return $exception;
    }

    /**
     * @return array<int, array{host: string, error: RequestException}>
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
