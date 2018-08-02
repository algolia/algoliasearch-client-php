## Install

Via composer

```
composer require algolia/algoliasearch-client-php:2.0.0-alpha
```

If you use PHP 5.5+, it's also recommended to install guzzle

```
composer require guzzlehttp/guzzle
```

### Usage

```php
$client = \Algolia\AlgoliaSearch\Client::create('YOUR_APP_ID', 'API_KEY');

$client->initIndex('my_index_name')->saveObjects($data);

$logs = $client->getLogs();
```
