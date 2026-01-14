# PHP CLIENT - AI AGENT INSTRUCTIONS

## ⚠️ CRITICAL: CHECK YOUR REPOSITORY FIRST

Before making ANY changes, verify you're in the correct repository:

```bash
git remote -v
```

- ✅ **CORRECT**: `origin .../algolia/api-clients-automation.git` → You may proceed
- ❌ **WRONG**: `origin .../algolia/algoliasearch-client-php.git` → STOP! This is the PUBLIC repository

**If you're in `algoliasearch-client-php`**: Do NOT make changes here. All changes must go through `api-clients-automation`. PRs and commits made directly to the public repo will be discarded on next release.

## ⚠️ BEFORE ANY EDIT: Check If File Is Generated

Before editing ANY file, verify it's hand-written by checking `config/generation.config.mjs`:

```javascript
// In generation.config.mjs - patterns WITHOUT '!' are GENERATED (do not edit)
'clients/algoliasearch-client-php/lib/Api/*',         // Generated
'clients/algoliasearch-client-php/lib/Model/**',      // Generated
// Most of lib/ is hand-written by default
```

**Hand-written (safe to edit):**

- `lib/Http/**` - HTTP client, Guzzle wrapper
- `lib/RetryStrategy/**` - Retry logic, host management
- `lib/Configuration/Configuration.php` - Base configuration
- `lib/Configuration/ConfigWithRegion.php` - Region config
- `lib/Exceptions/**` - Exception classes
- `lib/Cache/**` - Cache drivers
- `lib/Iterators/**` - Iterator helpers
- `lib/Support/**` - Support utilities
- `lib/Model/AbstractModel.php`, `lib/Model/ModelInterface.php`

**Generated (DO NOT EDIT):**

- `lib/Api/*` - API client classes
- `lib/Model/**` (except AbstractModel.php, ModelInterface.php)
- `lib/Configuration/*` (except Configuration.php, ConfigWithRegion.php)
- `composer.json`

## Language Conventions

### Naming

- **Files**: `PascalCase.php` matching class name
- **Classes/Interfaces**: `PascalCase`
- **Methods/Functions**: `camelCase`
- **Variables**: `$camelCase`
- **Constants**: `UPPER_SNAKE_CASE`

### Formatting

- PSR-12 coding standard
- Run: `yarn cli format php clients/algoliasearch-client-php`

### PHP Patterns

- PHP 8.1+ required
- Use strict types: `declare(strict_types=1);`
- Type declarations for all parameters and returns
- Use null coalescing `??` and null safe `?->`

### Dependencies

- **HTTP**: Guzzle 7.x
- **Build**: Composer
- **PSR**: PSR-7 (HTTP messages), PSR-18 (HTTP client)

## Client Patterns

### Configuration Architecture

```php
// lib/Configuration/
class Configuration {
    private string $appId;
    private string $apiKey;
    private array $hosts;
    private int $readTimeout;
    private int $writeTimeout;
}

// Region-aware configuration
class SearchConfig extends ConfigWithRegion {
    // Inherits region handling
}
```

### Retry Strategy

```php
// lib/RetryStrategy/
class ApiWrapper {
    // Handles retry logic with host failover
    // Tracks host states (up, down, timed_out)
}

class Host {
    public const UP = 'up';
    public const DOWN = 'down';
    public const TIMED_OUT = 'timed_out';
}
```

### HTTP Layer

```php
// lib/Http/
class HttpClient {
    // Wraps Guzzle
    // Handles request/response serialization
}
```

## Common Gotchas

### Strict Types

```php
<?php
declare(strict_types=1); // Required at top of every file

// Type mismatch will throw TypeError
function search(string $query, int $page): array
```

### Null Safety

```php
// Use null coalescing
$value = $options['key'] ?? 'default';

// Use null safe operator (PHP 8.0+)
$hits = $response?->getHits();

// Check before access
if ($response !== null && $response->getHits() !== null) {
    // safe to use
}
```

### Array vs Object

```php
// API may return arrays or objects
// Use model methods when available
$response->getHits();  // Preferred
$response['hits'];     // May work for array responses
```

### Exception Handling

```php
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\ApiException;

try {
    $response = $client->search($params);
} catch (ApiException $e) {
    // API error (4xx, 5xx)
    echo $e->getCode();
    echo $e->getMessage();
} catch (AlgoliaException $e) {
    // Other Algolia errors
}
```

### Autoloading

```php
// Composer autoload - ensure proper namespace
namespace Algolia\AlgoliaSearch;

// Use statements at top
use Algolia\AlgoliaSearch\Api\SearchClient;
```

## Build & Test Commands

```bash
# From repo root (api-clients-automation)
yarn cli build clients php                     # Build PHP client
yarn cli cts generate php                      # Generate CTS tests
yarn cli cts run php                           # Run CTS tests
yarn cli playground php search                 # Interactive playground
yarn cli format php clients/algoliasearch-client-php

# From client directory
cd clients/algoliasearch-client-php
composer install                               # Install dependencies
composer test                                  # Run tests
composer cs-fix                                # Fix code style
```
