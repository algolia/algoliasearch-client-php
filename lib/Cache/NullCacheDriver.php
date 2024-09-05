<?php

namespace Algolia\AlgoliaSearch\Cache;

use Psr\SimpleCache\CacheInterface;

final class NullCacheDriver implements CacheInterface
{
    public function get($key, $default = null): mixed
    {
        return $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        return true;
    }

    public function delete($key): bool
    {
        return true;
    }

    public function clear(): bool
    {
        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $return = [];

        foreach ($keys as $key) {
            $return[$key] = $default;
        }

        return $return;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        return true;
    }

    public function has($key): bool
    {
        return false;
    }
}
