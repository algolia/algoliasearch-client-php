# Upgrading from v1 to v2

### Creating client

The `Client` class now takes its dependencies in the constructor, so you cannot only use the
constructor with Algolia credentials. Instead you need to call the `create` static factory.

```php
// Replace
$client = new \AlgoliaSearch\Client($appId, $apiKey);
// By
$client = \Algolia\AlgoliaSearch\Client::create($appId, $apiKey)
```

If you passed hosts to the constructor, you need to use the configuration. Checkout the
documentation to learn more about the configuration.

```php
// Replace
$client = new \AlgoliaSearch\Client($appId, $apiKey, $hosts);
// By
$config = (new \Algolia\AlgoliaSearch\Config\ClientConfig())
            ->setAppId($appId)
            ->setApiKey($apiKey)
            ->setHosts($hosts);
$client = \Algolia\AlgoliaSearch\Client::createWithConfig($config)
```


## Method signature change

In v2, all methods follow a more consistent norm.

* All arguments required by the REST API match one argument in the method signature.
* All optional arguments are passed to the `$requestOptions`
* The client never set any default values

#### Example

Most methods change are simply moving optional parameters to RequestOptions.

```php
// Replace
$client->getLogs($offset, $length, $type);
// By
$client->getLogs([
    'offset' => $offset,
    'length' => $length,
    'type' => $type,
]);
```

This allows you to use the default value without passing them

```php
// v1
$client->getLogs(0, 10, $type);
// v2
$client->getLogs([
    'type' => $type,
]);
```

### List of method signature change

Please review all changes here: [PHP v2 method signature change]().


## Misc

### ObjectID is required for all objects

`addObjects` was removed because we want to enforce people to set their own objectID.

### ApiKeys can only be managed by the client, not the index

This was already deprecated and has been removed in v2. You can't add new keys on the index,
but if you already have set some, you can use the 2 following methods to get and delete them.

If you relied on keys on the index, update your code to use `Client::*ApiKey` and set an index restriction.

Delete the keys on the index from the dashboard.

### Browse

* All `browse*` method return an iterator
* `Index::browseFrom` was removed, use `browse` and pass the cursor in the `$requestOptions`.
Note: this method could easily be added back for DX purpose, as long as it uses browse internally.
