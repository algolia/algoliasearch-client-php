# Introducing PHP Client v2

A new version of the PHP client is coming. This will be the base for a new version of other client within the next few months.

If you ever found yourself thinking _"I wish I could do this with the client"_, please email me (julien.bourdeau@algolia.com) or open an issue on GitHub.

## Why

All the Algolia clients were designed years ago, we think rewriting them from scratch will help improve the developer experience for every user and make them more maintainable.

More maintainable means faster feature development and less bugs, so in the end, it all comes down to improving the developer experience for the entire Algolia community.


## Requirements

### Backward compatibility

We believe it's sometimes necessary to break backward compatibility, however we want to keep it as minimal as possible, upgrading to the new major version has to be very smooth.

#### Similar public API

For that reason, most of the public API is kept as-is or has very slightly changed. The library still relies on 2 main classes to access most of the API: `Client` and `Index`.

Even if we considered doing something more eloquent or even build some sort of query language, we are convinced that this will add a lot of complexity, make upgrade really hard and bring very little value in the end. Simplicity for the win.

#### PHP 5.3+ supported

This new version support the exact same PHP version range as before: 5.3+. We chose to do it to simplify upgrade as much as possible. Even if it made my life more difficult, I believe it will make everybody else's easier.

#### New backward compatibility promise

* All API clients follow [SemVer](https://semver.org/).
* Backward compatibility is guaranteed on all classes and interface except of the `Internal` folder
* New exceptions can be added at any time but they will necessarily extend AlgoliaException class.
* Configuration entry can be added in minor versions.
* The structure of the API response can have new field added independently of this library.


### Conventions

A good library should be obvious to use.

* Method signatures have an argument for all required parameters and then one array of `requestOptions` which can contain about anything

* Required arguments take precedence over the requestOptions array.

    In the following example, `some query` will override `better query`.
    ```php
    $index->search('some query', ['query' => 'better query']);
    ```

* Verbs:
    * **save** means add or replace existing <-- /!\
    * **partialUpdate** means updates only the given fields
    * **clear** means delete all
    * **delete** means delete
    * **fresh** means remove all existing and save what is passed

* There is no phpdoc in the code for public API method, because it's usually outdated and clutters the code. Instead, refers to the doc on algolia.com (to be published along with the final version). If the method is internal, doc can be added if necessary.

## What's new

### The transport layer

This new version allows developers to change the transport layer easily. If your PHP version is recent enough, all HTTP calls are done by the Guzzle library. The lib also follow the PSR7 norm.

If you have an old version, use the embedded http layer.

In order to implement your own, implement the `HttpClientIntercace` (more doc necessary)


### RequestOptions as a first-class citizen

Timeouts and parameters all managed by requestOptions

RequestOptionsFactory is responsible for splitting the given array into 4 sections: headers, query params (url), post content and timeouts.

Timeouts are the transport layer's responsibility but in order to change them easily per query, they were added to the RequestOptions.

Some params like `forwardToReplicas` or `createIfNotExists` should always be passed in the query params, while other parameters like `cursor` (in browse) should be passed in body, RequestOptions will take care of that.

![RequestOptions schema](/docs/RequestOptions.png)

**NOTE:** Passing an array makes the library much easier to use but if you need total control, you can pass a `RequestOption` object instead.

### Logger

The client now integrates a logger, which allow you to get some information about the request lifecycle.

1. You can enable/disable it via a static call.

```php
Logger::enable();
$index->addObjects(objects)
Logger::disable();
```

2. Or you can also define your own `PSR-3` Logger:

```php
// Injecting a default logger for all clients.
ClientConfig::setDefaultLogger($myLogger);

$client = Client::create($appId, $apiKey);
$client->initIndex('index_name')->saveObjects($objects);

// Or injecting a specific logger.
$config = ClientConfig::create($appId, $apiKey);
$config->setLogger($myOtherLogger);

$client = Client::createWithConfig($config);
$client->initIndex('index_name')->saveObjects($objects);
```

### Canary Release

The library ships with a `CanaryClient` class which extends `Client`. The point would be to add methods in _beta_ in this class so you can start using them if necessary. Once they're considered stable, they'll be moved to the Client class.

The idea is that features here don't have to follow the normal release cycle. A feature in canary could be in beta while 6 minor versions are deployed.

Features in canary could change or be removed.

### New methods

**reindex**

Ever wanted to reindex your data without down time? You had to create a temporary index, add synonyms, rules and settings, index data and rename your index.
Now you can use `$index->freshObjects($objects)` (yes it cold be called 'reindex' but it doesn't work with synonyms and rules)

**WaitFor**

Operations on keys or userIds don't return a taskID. To ensure the task is completed, new methods were added to simulate the behavior.

Currently, only `$client->waitForKeyAdded($key)` is implemented, others are coming.

### Configuration

All available configurations like default timeouts or user agent are now grouped under the Config class. Today, every API clients implement user agent differently.

This is also how you set the HTTP Client you want to use.

Example:

```php
Config::setHttpClient(function () {
    return new MyHttpClient(getenv('SOMETHING'));
});
```

#### Client configuration

We also added a way to have a different configuration for each Client instance, in case you are
using multiple apps, in case you need 2 clients with the same app different timeouts or anything similar.


```php
$config1 = new ClientConfig([
    'appId' => getenv('LEGACY_APP_ID')
    'apiKey' => getenv('LEGACY_API_KEY'),
    'writeTimeout' => 90,
]);

$client1 = Client::createWithConfig($config1);

$config2 = new ClientConfig(
    'writeTimeout' => 10,
]);
// Note that credentials will automatically be read from env variables
// Make sure you have set `ALGOLIA_APP_ID` and `ALGOLIA_API_KEY`
$client2 = Client::createWithConfig($config1);
```

### Singleton

A new way to use the client was added. In most cases, you need one client, everywhere in your application and
you are using only one Algolia app. In this case, I believe it's best to use a singleton, and our lib
now takes care of it for you.

**In this case, the client will automatically read the credentials from the env variables: `ALGOLIA_APP_ID` and `ALGOLIA_API_KEY`.(())

```php
Client::get()->getLogs();
//...
Client::get()->waitTask($indexName, $taskId);

```


### The `doctor` tool

Having an issue? 💊 Run `./vendor/bin/algolia-doctor` to get a full configuration check and provide helpful message to improve your setup.

You can find the check in the `bin/` directory. **What other check would you like to see?**

### Exceptions

A bunch of new Exceptions have been introduced. The main reason is to help the developer debugging but also catch them in production.

* `TaskTooLongException` if a task never completes
* `MissingObjectId` if you tried to add object without objectID
* `BadRequestException` if your request cannot work
* `NotFoundException` if the API returned a 404 (extends `BadRequestException`)
* `RetriableException` if something went wrong and query again
* `UnreachableException` - hu ho

Could be added if necessary:

* `QuotaExceededException` if you reached your plan limits
* `ACLExceptions` if you're using an key that cannot perform this action



## Upgrade

Please follow the upgrade guide in [docs/UPGRADE-from-v1-to-v2.md.md]().

This doc will be updated during the beta and will be complete before the final release.

## Tests

There are 3 types of tests.

| Type              | Description                                                                                                   |
|-------------------|---------------------------------------------------------------------------------------------------------------|
| Public API tests  | Check the library has correct method names and arguments (using reflexion)                                    |
| Integration tests | Call Algolia API to ensure method behave the way they should                                                  |
| Unit tests        | Some part of the code is absolutely critical and must be Unit tested, the RequestOptionsFactory for instance. |

Integration tests use a `SyncClient` and `SyncIndex` which automatically wait for all taskID, making tests easier to read and more stable.



# Call for feedback

If you are already an Algolia user or if you plan to be, please share any feedback you may have via:

* [GitHub issues](https://github.com/algolia/algoliasearch-client-php/issues/new) or [Pull Requests](https://github.com/algolia/algoliasearch-client-php/pulls)
* Email at <mailto:julien.bourdeau@algolia.com>

In general, I'd like to hear:

* What you would like to see added
* How long was it to upgrade
* If it's clear enough
* What feature you'd like to see added
* How well (or not) it integrates in your stack

## Question

#### Should we consider `client`, `index` and such as final or let developers extend them?

#### Do you see any missing feature?
