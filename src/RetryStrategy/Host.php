<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

/**
 * @internal
 */
final class Host
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var bool
     */
    private $up = true;

    /**
     * @var int
     */
    private $lastCheck;

    /**
     * @var int
     */
    const TTL = 300; // 5 minutes

    /**
     * Host constructor.
     *
     * @param string $url
     * @param int    $priority
     */
    public function __construct($url, $priority = 0)
    {
        $this->url = $url;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return bool
     */
    public function isUp()
    {
        if (!$this->up) {
            $this->resetIfExpired();
        }

        return $this->up;
    }

    /**
     * Marks host as down.
     *
     * @return void
     */
    public function markAsDown()
    {
        $this->up = false;
        $this->lastCheck = time();
    }

    /**
     * Reset host.
     *
     * @return void
     */
    public function reset()
    {
        $this->up = true;
        $this->lastCheck = null;
    }

    /**
     * Reset host if expired.
     *
     * @return void
     */
    private function resetIfExpired()
    {
        $expired = $this->lastCheck + self::TTL < time();

        if ($expired) {
            $this->reset();
        }
    }
}
