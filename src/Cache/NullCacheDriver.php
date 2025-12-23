<?php

namespace Algolia\AlgoliaSearch\Cache;

use Psr\SimpleCache\CacheInterface;

final class NullCacheDriver implements CacheInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null): mixed
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $return = [];

        foreach ($keys as $key) {
            $return[$key] = $default;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return false;
    }
}
