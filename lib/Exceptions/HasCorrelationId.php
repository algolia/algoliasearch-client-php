<?php

namespace Algolia\AlgoliaSearch\Exceptions;

trait HasCorrelationId
{
    /**
     * @var null|string
     */
    protected $correlationId;

    /**
     * @return null|string the `Correlation-ID` of the failing response, null when the server sent none
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     * Stores the `Correlation-ID`, normalizing '' to null, and returns the message with the
     * ` (Correlation-ID: …)` suffix appended once.
     *
     * @param string      $message
     * @param null|string $correlationId
     *
     * @return string
     */
    private function attachCorrelationId($message, $correlationId)
    {
        if ('' === $correlationId) {
            $correlationId = null;
        }

        $this->correlationId = $correlationId;

        if (null !== $correlationId) {
            $suffix = ' (Correlation-ID: '.$correlationId.')';

            if (false === strpos($message, $suffix)) {
                $message .= $suffix;
            }
        }

        return $message;
    }
}
