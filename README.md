# PHP Client v2

## Why

All the Algolia clients were designed years ago, we think rewriting them from scratch will help improve the developer experience for every user and make them more maintainable.

More maintainable means faster feature development and less bugs, so in the end, it all comes down to improve the developer experience for the entire Algolia community.


## Requirements

### Backward compatibility

We believe it's sometimes necessary to break backward compatibility, however we want to keep it as minimal as possible, upgrading to the new major version has to be very smooth.

For that reason, most of the public API is kept as-is or slightly changed. The library still rely on 2 main classes to access most of the API: `Client` and `Index`.

Even if we considered doing something more eloquent or even build some sort of query language, we are convinced that this will add a lot of complexity, make upgrade really hard and bring very little value in the end. Simplicity for the win.

About language support, this new version support the exact same PHP version range as before: 5.3+

### New backward compatibility promise

All API clients follow SEMVER.

Backward compatibility is guaranteed on all classes and interface outside of the `Internal` folder

New exceptions can be added at any time but they will necessarily extend AlgoliaException class.

Configuration entry can be added in minor versions.

The type of response from the API (array) can change independently of this library.


### Conventions

A good library should be obvious to use.

* Method signatures have one args for all required parameters and then one array of `requestOptions` which can contain about anything

* the requestOptions array takes precedence.
    In the following example, `better query` will override `some query`.
    ```php
    $index->search('some query', ['query' => 'better query']);
    ```
    
* Verbs:
    * **save** means add or replace existing <-- /!\
    * **update** means partial updates
    * **clear** means delete all
    * **delete** means delete
    * **fresh** means remove all existing and save what is passed

* There is no method doc in the code for public API method, because it's always outdated clusters the code. Instead, there is a link to the Algolia method doc. If the method is internal, doc can be added.

## What's new

### The transport layer

This new version allows developer to change the transport layer easily. If your PHP version is recent enough, all HTTP calls are done by the Guzzle library. The lib also follow the PSR7 norm.

If you have an old version, use the embedded http layer.

In order to implement your own, implement the `HttpClientIntercace` (more doc necessary)


### RequestOptions as a first-class citizen

Timeouts and parameters all managed by requestOptions

RequestOptionsFactory is responsible for splitting the given array into 4 sections: headers, query params (url), post content and timeouts.

Timeouts are the transport layer's responsibility but in order to make them easily customisation per query, they are added to the RequestOptions.

Some params like `forwardToReplicas` or `createIfNotExists` should always be passed in the query params, while other parameters like `cursor` (in browse) should be passed in body since its since could exceed the URL max size.

![RequestOptions schema](/docs/RequestOptions.png)


### New methods

**reindex**

Ever wanted to reindex your data without down time? You had to create a temporary index, add synonyms, rules and settings, index data and rename your index. 
Now you can use `$index->freshObjects($objects)` (yes it cold be called 'reindex' but it doesn't work with synonyms and rules)

**WaitFor**

Operations on keys or userIds don't return a taskID. If you need to ensure this is done, you can use the following method wish simulate the `waitForTask` when there is no taksID.

* `$client->waitForAddedKey($key)`
* `$client->waitForUpdatedKey($key)`
* `$client->waitForRemovedKey($key)`
* `$client->waitForAssignedUserId($userId)`
* `$client->waitForRemovedUserId($userId)`

### Configuration

All available configuration like default timeouts or user agent are now grouped under the Config class.

Today, all API clients implement user agent differently.


### The `doctor` debugging tool

Having an issue? Run `./vendor/bin/algolia-doctor` to get a full configuration check and helpful error message

### Exceptions

A bunch of new Exceptions have been introduced. The main reason is to help the developer debugging but also catch them in production.

* `QuotaExceededException` if you reached your plan limits
* `TaskTooLongException` if a task never completes
* `MissingObjectId` if you tried to add object without objectID
* `BadRequestException` if your request cannot work
* `RetriableException` if something went wrong and query again
* `UnreachableException` - hu ho
* `ACLExceptions` if you're using an key that cannot perform this action



## Upgrade

### ObjectID is required for all objects

Because this feature was a mistake (according to API Core team)

### ForwardToReplicas is `true` by default

Because it makes more sense :D

### ApiKeys can only be managed by the client, not the index

This was already deprecated and has been removed in v2

### Method signature change

Here goes a table with the whole list

| Before                                                      | After                                                                                                 |
|-------------------------------------------------------------|-------------------------------------------------------------------------------------------------------|
| copyIndex('source', 'dest')                                 | copyIndex('source', 'dest')                                                                           |
| scopedCopyIndex('source', 'dest', ['settings', 'synonyms']) | ]copyIndex('source', 'dest', ['scope' => scopedCopyIndex('source', 'dest', ['settings', 'synonyms'])) |
| batchSynonyms($objects, true, false)                        | saveSynonmys($objects)                                                                                |
| batchSynonyms($objects, true, true)                         | freshSynonmys($objects)                                                                               |
| batchSynonyms($objects, false, false)                       | saveSynonmys($objects, ['forwardToReplicas' => false])                                                |
| batchSynonyms($objects, false, true)                        | freshSynonmys($objects, ['forwardToReplicas' => false])                                               |

### Misc

* All `browse*` method return an iterator
* `Index::browseFrom` was removed, use `browse` and pass the cursor in the `$requestOptions`.
Note: this method could easily be added back for DX purpose, as long as it uses browse internally.


## Tests

There are 3 types of tests:

* Public API tests
Check the library has correct method names and arguments (using reflexion)
* Integration test
Call Algolia API to ensure method behave the way they should
* Unit tests
Some part of the code is absolutely critical and must be Unit tested, the RequestOptionsFactory for instance.

Note: Integration tests use a `SyncClient` and `SyncIndex` which automatically wait for all taskID, making tests easier to read.




Questions:

* needs more tests about empty or `[]` POST content (see browse) 

