<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

/**
 * @internal
 */
final class Host
{
    public const TTL = 300; // 5 minutes
    private $url;

    private $priority;

    private $up = true;

    private $lastCheck;

    private $retryCount = 0;

    public function __construct($url, $priority = 0)
    {
        $this->url = $url;
        $this->priority = $priority;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function isUp()
    {
        if (!$this->up) {
            $this->resetIfExpired();
        }

        return $this->up;
    }

    public function markAsDown()
    {
        $this->up = false;
        $this->lastCheck = time();
    }

    /**
     * @return bool whether the host was down or had timed out before the reset
     */
    public function reset()
    {
        $changed = !$this->up || 0 !== $this->retryCount;

        $this->up = true;
        $this->lastCheck = null;
        $this->retryCount = 0;

        return $changed;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $count): void
    {
        $this->retryCount = $count;
    }

    public function incrementRetryCount(): void
    {
        ++$this->retryCount;
    }

    private function resetIfExpired()
    {
        $expired = $this->lastCheck + self::TTL < time();

        if ($expired) {
            $this->reset();
        }
    }
}
