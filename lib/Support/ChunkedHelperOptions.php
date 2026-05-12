<?php

namespace Algolia\AlgoliaSearch\Support;

/**
 * Optional configuration for chunked helpers that batch records and poll for task completion.
 *
 * Designed to grow over time; future shared helper config (e.g. timeout, batchSize defaults)
 * should be added here instead of widening every helper's parameter list.
 */
final class ChunkedHelperOptions
{
    public function __construct(
        public ?int $maxRetries = null,
    ) {}
}
