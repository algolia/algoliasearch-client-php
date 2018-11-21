# Algolia Search API Client for PHP

[Algolia Search](https://www.algolia.com) is a hosted full-text, numerical, and faceted search engine capable of delivering realtime results from the first keystroke.

The **Algolia Search API Client for PHP**
lets you easily use the [Algolia Search REST API](https://www.algolia.com/doc/rest-api/search) from
your PHP code.

[![Build Status](https://travis-ci.org/algolia/algoliasearch-client-php.svg?branch=master)](https://travis-ci.org/algolia/algoliasearch-client-php) [![Latest Stable Version](https://poser.pugx.org/algolia/algoliasearch-client-php/v/stable.svg)](https://packagist.org/packages/algolia/algoliasearch-client-php) [![Coverage Status](https://coveralls.io/repos/algolia/algoliasearch-client-php/badge.svg)](https://coveralls.io/r/algolia/algoliasearch-client-php)


If you're a Symfony or Laravel user, you're probably looking for the following integrations:

- **Laravel**: [Laravel Scout](/doc/api-client/laravel/algolia-and-scout/)
- **Symfony**: [algolia/AlgoliaSearchBundle](https://github.com/algolia/AlgoliaSearchBundle)


## Install



### Supported platforms

The API client is compatible with PHP version 5.3 and later.

### Install

#### With Composer (recommended)

Install the package via [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
composer require algolia/algoliasearch-client-php
```

#### Without Composer

If you don't use Composer, you can download the [package](https://github.com/algolia/algoliasearch-client-php/archive/master.zip) and include it in your code:

```php
require_once('algoliasearch-client-php-master/algoliasearch.php');
```

#### Framework Integrations

We officially provide support for the **Laravel** and **Symfony** frameworks:

If you are using one of those two frameworks have a look at our
[Laravel documentation](https://www.algolia.com/doc/api-client/laravel/algolia-and-scout/) or [Symfony documentation](https://www.algolia.com/doc/api-client/symfony/getting-started/)

### Quick Start

In 30 seconds, this quick start tutorial will show you how to index and search objects.

#### Initialize the client

To begin, you will need to initialize the client. In order to do this you will need your **Application ID** and **API Key**.
You can find both on [your Algolia account](https://www.algolia.com/api-keys).

```php
// composer autoload
require __DIR__ . '/vendor/autoload.php';

// if you are not using composer
// require_once 'path/to/algoliasearch.php';

$client = new \AlgoliaSearch\Client('YourApplicationID', 'YourAdminAPIKey');

$index = $client->initIndex('your_index_name');
```

### Push data

Without any prior configuration, you can start indexing [500 contacts](https://github.com/algolia/datasets/blob/master/contacts/contacts.json) in the `contacts` index using the following code:

```php
$index = $client->initIndex('contacts');
$batch = json_decode(file_get_contents('contacts.json'), true);
$index->addObjects($batch);
```

### Configure

Settings can be customized to fine tune the search behavior. For example, you can add a custom sort by number of followers to further enhance the built-in relevance:

```php
$index->setSettings(['customRanking' => ['desc(followers)']]);
```

You can also configure the list of attributes you want to index by order of importance (most important first).

**Note:** The Algolia engine is designed to suggest results as you type, which means you'll generally search by prefix.
In this case, the order of attributes is very important to decide which hit is the best:

```php
$index->setSettings(
  [
    'searchableAttributes' => [
      'lastname',
      'firstname',
      'company',
      'email',
      'city',
      'address'
    ]
  ]
);
```

### Search

You can now search for contacts using `firstname`, `lastname`, `company`, etc. (even with typos):

```php
// Search for a first name
var_dump($index->search('jimmie'));

// Search for a first name with typo
var_dump($index->search('jimie'));

// Search for a company
var_dump($index->search('california paint'));

// Search for a first name and a company
var_dump($index->search('jimmie paint'));
```

### Search UI

**Warning:** If you are building a web application, you may be more interested in using one of our
[frontend search UI libraries](https://www.algolia.com/doc/guides/search-ui/search-libraries/)

The following example shows how to build a front-end search quickly using
[InstantSearch.js](https://community.algolia.com/instantsearch.js/)

#### index.html

```html
<!doctype html>
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css">
  <!-- Always use `2.x` versions in production rather than `2` to mitigate any side effects on your website,
  Find the latest version on InstantSearch.js website: https://community.algolia.com/instantsearch.js/v2/guides/usage.html -->
</head>
<body>
  <header>
    <div>
       <input id="search-input" placeholder="Search for products">
       <!-- We use a specific placeholder in the input to guides users in their search. -->
    
  </header>
  <main>
      
      
  </main>

  <script type="text/html" id="hit-template">
    
      <p class="hit-name">{{{_highlightResult.firstname.value}}} {{{_highlightResult.lastname.value}}}</p>
    
  </script>

  <script src="https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js"></script>
  <script src="app.js"></script>
</body>
```

#### app.js

```js
var search = instantsearch({
  // Replace with your own values
  appId: 'YourApplicationID',
  apiKey: 'YourSearchOnlyAPIKey', // search only API key, no ADMIN key
  indexName: 'contacts',
  routing: true,
  searchParameters: {
    hitsPerPage: 10
  }
});

search.addWidget(
  instantsearch.widgets.searchBox({
    container: '#search-input'
  })
);

search.addWidget(
  instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: document.getElementById('hit-template').innerHTML,
      empty: "We didn't find any results for the search <em>\"{{query}}\"</em>"
    }
  })
);

search.start();
```






## Methods


### Search index

Method used for querying an index.


The search query only **allows for the retrieval of up to 1000 hits**.
If you need to retrieve more than 1000 hits (e.g. for SEO),
you can either leverage the [Browse index](/doc/api-reference/api-methods/browse/)
method or increase the [paginationLimitedTo](/doc/api-reference/api-parameters/paginationLimitedTo/) parameter.




#### Examples

##### Search

```php
$index = $client->initIndex('contacts');

// without search parameters
$res = $index->search('query string');

// with search parameters
$res = $index->search('query string', [
  'attributesToRetrieve' => [
    'firstname',
    'lastname',
  ],
  'hitsPerPage' => 50
]);
```

##### Search and send an extra header

```php
$index = $client->initIndex('your_index_name');

$searchParameters = [];
$extraHeaders = [
  'X-Algolia-User-ID' => 'user123'
];

$res = $index->search('query string', $searchParameters, $extraHeaders);
```




### Search for facet values

Search for a set of values within a given facet attribute. Can be combined with a query.


This method enables you to search through the values of a facet attribute,
selecting only a **subset of those values that meet a given criteria**.

**Note** For a facet attribute to be searchable, it must have been declared in the
[attributesForFaceting](/doc/api-reference/api-parameters/attributesForFaceting/) index setting with the `searchable()` modifier.

Facet-searching only affects facet values. It does not impact the underlying index search.



The results are **sorted by decreasing count**.
This can be adjusted via [sortFacetValuesBy](/doc/api-reference/api-parameters/sortFacetValuesBy/).

By default, maximum **10 results are returned**.
This can be adjusted via [maxFacetHits](/doc/api-reference/api-parameters/maxFacetHits/).

This is often used in combination with a user's current search (using the `searchParameters`).
By combining facet and query searches,
you can control the number of facet values a user sees,
thereby focusing the user's attention on what you consider to be the most relevant facet values.


#### Examples

##### Search for facet values

```php
# Search the values of the "category" facet matching "phone".
$index->searchForFacetValues("category", "phone");
```

##### Search for facet values with an additional searchParameters:

```php
$query = [
    'filters': 'brand:Apple'
];

// Search the "category" facet for values matching "phone" in records
// having "Apple" in their "brand" facet.
$index->searchForFacetValues("category", "phone", $query);
```

##### Search for facet values and send extra http headers

```php
$query = [];

$extraHeaders = [
  'X-Algolia-User-ID' => 'user123'
];

# Search the "category" facet for values matching "phone" in records
$index->searchForFacetValues("category", "phone", $query, $extraHeaders);
```




### Search multiple indices

Perform a search on several indices at the same time, with one method call.


The returned results are broken down by query.



This method can be used in several different kinds of situations.
Here are two typical usage scenarios:

  1. You have **multiple indices that serve different purposes**.
  This is typical when you want to search your main index
  as well as an index that contains a history of searches (to be used for [autocomplete](/doc/tutorials/search-ui/autocomplete/how-to-display-results-from-multiple-indices-with-autocomplete-js/)).
  2. You want to target **one index** and send it multiple queries,
  where, for example, each query contains **different settings or filters**,
  or the **query itself is slightly adjusted**.

**Note** that for 2., you will want to use the "stopIfEnoughMatches"
value of the `strategy` parameter.


#### Examples

##### Multiple queries

```php
// perform 3 queries in a single API call:
//  - 1st query targets index `categories`
//  - 2nd and 3rd queries target index `products`

$queries = [
  [
    'indexName' => 'categories',
    'query' => $myQueryString,
    'hitsPerPage' => 3
  ],
  [
    'indexName' => 'products',
    'query' => $myQueryString,
    'hitsPerPage' => 3,
    'facetFilters' => 'promotion'
  ],
  [
    'indexName' => 'products',
    'query' => $myQueryString,
    'hitsPerPage' => 10
  ]
];

$results = $client->multipleQueries($queries);

var_dump($results['results']);
```

##### Multiple queries and send extra http headers

```php
$queries = [/* queries */];

$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$results = $client->multipleQueries(
  $queries,
  'indexName',
  'none',
  $extraHeaders
);

var_dump($results['results']);
```




### Browse index

Get all index content without any record limit. Can be used for backups.


The browse method is an **alternative to the [Search index](/doc/api-reference/api-methods/search/) method**.
The `search` method cannot return more than 1,000 results. If you need to
retrieve all the content of your index (for backup, SEO purposes or for running
a script on it), you should use this method instead.


Results are **ranked by attributes and custom ranking**.

But for performance reasons, there is **no ranking based on**:
- distinct
- typo-tolerance
- number of matched words
- proximity
- geo distance



#### Examples

The two examples below are actually doing the same thing.
However, the first one hides the cursor logic of the second example by using iterators.

###### Browse an index (recommended way)

This example shows how to iterate over the whole index:

```php
$query = ''; // Empty query will match all records

foreach ($index->browse($query, ['filters' => 'i<42']) as $hit) {
  var_dump($hit);
}
```

###### Browse an index using a cursor

This example shows how to iterate with a filter over the index using a cursor:

```php
$query = ''; // Empty query will match all records

$result = $index->browseFrom($query, ['filters' => 'i<42']);

while (isset($result['cursor'])) {
  var_dump($result);

  $params = null; // Filters are embeded in the cursor
  $result = $index->browseFrom('', $params, $result['cursor']);
}
```

###### Browse an index and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$query = ''; // Empty query will match all records
$searchParameters = [];

foreach ($index->browse($query, $searchParameters, $extraHeaders) as $hit) {
  var_dump($hit);
}
```

###### Browse an index using a cursor and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$query = ''; // Empty query will match all records
$searchParameters = [];

$result = $index->browseFrom($query, $searchParameters);

while (isset($result['cursor'])) {
  var_dump($result);

  $params = null; // Filters are embeded in the cursor
  $result = $index->browseFrom(
    '',
    $params,
    $result['cursor'],
    $extraHeaders
  );
}
```




### Add objects

Add new objects to an index.




This method allows you to create records on your index by sending one or more objects.
Each [object](/doc/api-reference/api-methods/add-objects/#method-param-object) contains a set of attributes and values,
which [represents a full record on an index](/doc/api-client/indexing/#object--record).

There is no limit to the number of objects that can be passed, but a size limit of 1 GB on the total request. For performance
reasons, it is recommended to push batches of ~10 MB of payload.

Batching records allows you to reduce the number of network calls required for multiple operations.
But note that each indexed object counts as a single indexing operation.

When adding large numbers of objects, or large sizes, be aware of our [rate limit](/doc/faq/indexing/is-there-a-rate-limit/).
You'll know you've reached the rate limit when you start receiving errors
on your indexing operations.
This can only be resolved if you wait before sending any further indexing operations.

**Note:** This method also has a [singular version](#add-a-single-object).


#### Examples

##### Add objects with automatic `objectID` assignments

```php
$res = $index->addObjects(
  [
    [
      'firstname' => 'Jimmie',
      'lastname'  => 'Barninger'
    ],
    [
      'firstname' => 'Warren',
      'lastname'  => 'Speach'
    ]
  ]
);
```

##### Add objects with manual `objectID` assignments

```php
$index->addObjects(
  [
    [
      'objectID' => 'myID1',
      'firstname' => 'Jimmie',
      'lastname'  => 'Barninger'
    ],
    [
      'objectID' => 'myID2',
      'firstname' => 'Warren',
      'lastname'  => 'Speach'
    ]
  ]
);
```

##### Add a single object

```php
$index->addObject(
  [
    'objectID' => 'myID',
    'firstname' => 'Jimmie',
    'lastname'  => 'Barninger'
  ]
);
```

##### Add objects and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$objects = [/* objects */];
$res = $index->addObjects($objects, 'objectID', $extraHeaders);
```




### Update objects

Replace an existing object with an updated set of attributes.


The update method is used to **redefine the entire set of an object's attributes**
(except of course its `objectID`).
In other words, it *fully replaces* an existing object.



Updating objects has the **same effect as the [add objects](/doc/api-reference/api-methods/add-objects/)
method if you specify objectIDs** for every record (which is required in the update objects).

This method **differs from [partial update objects](/doc/api-reference/api-methods/partial-update-objects/)**
in a significant way:

- With `update objects` you define an object's full set of attributes.
Attributes not specified will no longer exist.
For example, if an existing object contains attribute X,
but X is not defined in a later update call,
attribute X will no longer exist for that object.
- In contrast, with `partial update objects` you can single out one or more attributes,
and either remove them, add them, or update their content.
Additionally, attributes that already exist but are not specified in a partial update are not impacted.

When updating large numbers of objects, or large sizes, be aware of our [rate limit](/doc/faq/indexing/is-there-a-rate-limit/).
You'll know you've reached the rate limit when you start receiving errors
on your indexing operations.
This can only be resolved if you wait before sending any further indexing operations.

**Note:** This method also has a [singular version](#replace-all-attributes-of-a-single-object).


#### Examples

##### Replace all attributes from existing objects

```php
$res = $index->saveObjects(
  [
    [
      'objectID'  => 'myID1',
      'firstname' => 'Jimmie',
      'lastname'  => 'Barninger'
    ],
    [
      'objectID'  => 'myID2',
      'firstname' => 'Warren',
      'lastname'  => 'Speach'
    ]
  ]
);
```

##### Replace all attributes of a single object

```php
$index->saveObject(
  [
    'firstname' => 'Jimmie',
    'lastname'  => 'Barninger',
    'city'      => 'New York',
    'objectID'  => 'myID'
  ]
);
```

##### Replace all attributes from existing objects and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$objects = [/* objects */];
$res = $index->saveObjects($objects, 'objectID', $extraHeaders);
```




### Partial update objects

Update one or more attributes of an existing object.


This method enables you to **update only a part of an object** by
singling out one or more *attributes* of an existing object
and performing the following actions:
  - add new attributes
  - update the content of existing attributes

You can perform the above actions on multiple objects in a single method call



You will want to use the **[update objects](/doc/api-reference/api-methods/save-objects/) method** if you want to
**completely redefine an existing object**, or to replace an object with a different one.

**Nested attributes cannot be individually updated**. If you specify a nested attribute,
it will be treated as a replacement of its first-level ancestor.
To change nested attributes, you will need to use the update object method. You can
initially get the object's data either from your own data
or by using the [get object](/doc/api-reference/api-methods/get-objects/) method.

When updating large numbers of objects, or large sizes, be aware of our [rate limit](/doc/faq/indexing/is-there-a-rate-limit/).
You'll know you've reached the rate limit when you start receiving errors
on your indexing operations.
This can only be resolved if you wait before sending any further indexing operations.

**Note:** This method also has a [singular version](#update-only-the-city-attribute-of-an-existing-object).


#### Examples

##### Partially update multiple objects using one API call

```php
$res = $index->partialUpdateObjects(
    [
        [
            'objectID'  => 'myID1',
            'firstname' => 'Jimmie'
        ],
        [
            'objectID'  => 'myID2',
            'firstname' => 'Warren'
        ]
    ]
);
```

##### Partially update multiple objects using one API call and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$createIfNotExists = true;

$objects = [/* objects */];

$res = $index->partialUpdateObjects(
  $objects,
  'objectID',
  $createIfNotExists,
  $extraHeaders
);
```

##### Update only the city attribute of an existing object

```php
$index->partialUpdateObject(
  [
    'city'     => 'San Francisco',
    'objectID' => 'myID'
  ]
);
```




### Delete objects

Remove objects from an index using their object ids.


This method enables you to **remove one or more objects from an index**.



There are **2 methods available to delete objects**:
- this one, which uses an *objectID* to identify objects
- and [delete by](/doc/api-reference/api-methods/delete-by/), which uses *filters* to identify objects

**Note:** This method also has a [singular version](#delete-a-single-object).


#### Examples

##### Delete multiple objects using their `objectID`s

```php
$index->deleteObjects(["myID1", "myID2"]);
```

##### Delete a single object

```php
$index->deleteObject('myID');
```

##### Delete multiple objects and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$objectIDs = [/* objectIDs */];
$index->deleteObjects($objectIDs, $extraHeaders);
```




### Delete by

Remove all objects matching a filter (including geo filters).


This method enables you to **delete one or more objects based
on filters** (numeric, facet, tag or geo queries).

It **does not accept empty filters or a query**.

If you have a way to **fetch the list of objectIDs you want to delete, use the [delete method](/doc/api-reference/api-methods/delete-objects/)
instead** as it is more performant.

The delete by method only counts as 1 operation - even if it deletes more than one object.
This is exceptional; most indexing options that affect more than one object
normally count each object as a separate operation.

When deleting large numbers of objects, or large sizes, be aware of our [rate limit](/doc/faq/indexing/is-there-a-rate-limit/).
You'll know you've reached the rate limit when you start receiving errors
on your indexing operations.
This can only be resolved if you wait before sending any further indexing operations.




#### Examples

##### Delete records by filter

```php
$index->deleteBy([
  'filters' => 'category:cars',
  'aroundLatLng' => '40.71, -74.01'
  /* add any filter parameters */
]);
```

##### Delete records by filter and send extra http headers

```php
$params = [
  'filters' => 'category:cars',
  'aroundLatLng' => '40.71, -74.01'
  /* add any filter parameters */
];

$requestOptions = [
  'X-Algolia-User-ID': 'user123'
];

$index->deleteBy($params, $requestOptions);
```




### Get objects

Get one or more objects using their object ids.


The get objects method enables you to **retrieve index objects**.

**You can specify a list of attributes** to retrieve.
This list will apply to all objects.

If you **don't specify any attributes, every attribute will be returned**.




#### Examples

##### Retrieve a set of objects with their objectIDs

```php
$index->getObjects(['myId1', 'myId2']);
```

##### Retrieve a set of objects with only a subset of their attributes

Optionally you can specify a comma separated list of attributes you want to retrieve.

```php
$index->getObjects(['myId1', 'myId2'], ['firstname', 'lastname']);
```

**Note:** This will return an array of objects for all objects found;
for those not found, it will return a null.

##### Retrieve only one object

```php
// Retrieves all attributes
$index->getObject('myId');

// Retrieves firstname and lastname attributes
$index->getObject('myId', ['firstname', 'lastname']);

// Retrieves only the firstname attribute
$index->getObject('myId', ['firstname']);
```

If the object exists it will be returned as is.
Otherwise the function will return an error and not null like getObjects.

##### Retrieve a set of objects and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$attributesToRetrieve = null;

$objectIDs = [/* objectIDs */];
$index->getObjects($objectIDs, $attributesToRetrieve, $extraHeaders);
```




### Custom batch

Perform several indexing operations in one API call.


This method enables you to **batch multiple different indexing operations** in one API,
like add or delete objects,
potentially targeting multiple indices.



You would **use this method to**:
- *reduce latency* - only one network trip is required for multiple operations
- *ensure data integrity* - all operations inside the batch will be executed atomically.
Meaning that instead of deleting 30 objects then adding 20 new objects in two operations,
we do both operations in one go. This will remove the time during which an index
is in an inconsistent state and could be a great alternative to doing an atomic
reindexing using a temporary index.

When batching of a large numbers of objects, or large sizes,
be aware of our [rate limit](/doc/faq/indexing/is-there-a-rate-limit/).
You'll know you've reached the rate limit when you start receiving errors
on your indexing operations.
This can only be resolved if you wait before sending any further indexing operations.


#### Examples

##### Batch write operations

```php
$res = $client->batch(
  [
    [
      'action'    => 'addObject',
      'indexName' => 'index1',
      'body'      => [
        'firstname' => 'Jimmie',
        'lastname'  => 'Barninger'
      ]
    ],
    [
      'action'    => 'updateObject',
      'indexName' => 'index1',
      'body'      => [
        'objectID' => 'myID2',
        'firstname' => 'Max',
        'lastname'  => 'Barninger'
      ]
    ],
    [
      'action'    => 'partialUpdateObject',
      'indexName' => 'index1',
      'body'      => [
        'objectID'  => 'myID3',
        'lastname'  => 'McFarway'
      ]
    ],
    [
      'action'    => 'partialUpdateObjectNoCreate',
      'indexName' => 'index1',
      'body'      => [
        'objectID'  => 'myID4',
        'firstname' => 'Warren'
      ]
    ],
    [
      'action'    => 'deleteObject',
      'indexName' => 'index2',
      'body'      => [
        'objectID'  => 'myID5'
      ]
    ]
  ]
);
```

##### Batch write operations and send extra http headers

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$operations = [
  [
    'action' => 'addObject',
    'indexName' => 'index1',
    'body' => [
      'firstname' => 'Jimmie',
      'lastname' => 'Barninger'
    ]
  ],
  [
    'action' => 'addObject',
    'indexName' => 'index2',
    'body' => [
      'firstname' => 'Warren',
      'lastname' => 'Speach'
    ]
  ]
];

$res = $client->batch($operations, $extraHeaders);
```




### Get settings

Get the settings of an index.


You can find the list of settings in the [Settings Parameters](/doc/api-reference/settings-api-parameters/) page.




#### Examples

##### Retrieve settings for an index

```php
$settings = $index->getSettings();
var_dump($settings);
```




### Set settings

Create or change an index's settings.


**Only specified settings are overridden**; unspecified settings are left unchanged.
Specifying `null` for a setting resets it to its default value.

The supported settings are listed in the [Settings Parameters](/doc/api-reference/settings-api-parameters/) page.



**Performance wise**, it's better to **`set settings` before pushing the data**.


#### Examples

##### Simple set settings

```php
$index->setSettings(
  array(
    "customRanking" => array("desc(followers)")
  )
);
```

##### Set setting and forward to replicas

```php
$forwardToReplicas = true;

$index->setSettings(
  [
    "searchableAttributes": ["name", "address"]
  ],
  $forwardToReplicas
);
```




### List indexes

Get a list of [indexes/indices](/doc/api-client/indexing/#indexes--indices) with their associated metadata.


This method **retrieves a list of all indices** associated with a given application id.

The returned list includes the **name of the index as well as its associated metadata**,
such as the number of records, size, last build time, and pending tasks.

Calling this method returns all indices, with **no paging**.
So if there are 1000s of indices for a certain application id,
then all 1000 indices will be returned at the same time.




#### Examples



```php
var_dump($client->listIndexes());
```




### Delete index

Delete an index and all its settings, including links to its replicas.


This method not only removes an index from your application,
it also **removes its metadata and configured settings**
(like searchable attributes or custom ranking).

If the index has [replicas](/doc/api-reference/api-parameters/replicas/), they will be preserved but **will no longer be linked to their primary index**.
Instead, they'll become independent indices.



If you want to **only remove the records** from the index,
**use the [clear method](/doc/api-reference/api-methods/clear-index/)**.

Deleting an index will have **no impact on Analytics data** because
[you cannot delete an indexâ€™s Analytics data](/doc/api-client/manage-indices/#analytics-data).


#### Examples

##### Delete an index by name

```php
$client->deleteIndex('contacts');
```




### Copy index

Make a copy of an index, including its objects, settings, synonyms, and query rules.


This method enables you to **copy the entire index** (objects, settings, synonyms, and rules)
**OR** one or more of **the following index elements**:
  - settings
  - synonyms
  - and rules (query rules)

You can control which of these are copied by using the
[scope parameter](/doc/api-reference/api-methods/copy-index/#method-param-scope).
{:.no-margin}


The copy command will **overwrite the destination index**.
This means everything will be lost in the destination index except its API keys and
Analytics data.

Regarding the API Keys, **the source's API Keys will be merged**
with the existing keys of the destination index.


Copying an index will have **no impact on Analytics data** because
[you cannot copy an indexâ€™s Analytics data](/doc/api-client/manage-indices/#analytics-data).

**Replicas are not copied** when copying settings.


#### Examples

##### Copy an index

```php
// Copy indexNameSrc to indexNameDest
$res = $client->copyIndex('indexNameSrc', 'indexNameDest');
```

##### Copy resources between indices

```php
// Copy settings and synonyms (but not rules) from "indexNameSrc" to "indexNameDest".
$res = $client->scopedCopyIndex('indexNameSrc', 'indexNameDest', ['settings', 'synonyms']);
```




### Move index

Rename an index.
Normally used to [reindex your data atomically](/doc/tutorials/indexing/synchronization/atomic-reindexing/),
without any down time.


The move index method is a safe and atomic way to **rename an index**.

By using this method, you can keep your existing service running while the data from the
old index is being imported into the new index.


**Moving an index overrides** the objects and settings of the destination index.

Regarding **replicas**:
- If the destination index contains replicas, they will be left untouched.
- If the source index contains replicas, the method will fail.


Moving an index will have **no impact on Analytics data** because
[you cannot move an indexâ€™s Analytics data](/doc/api-client/manage-indices/#analytics-data).


#### Examples



```php
// Rename indexNameSrc to indexNameDest (and overwrite it)
$res = $client->moveIndex('indexNameSrc', 'indexNameDest');
```




### Clear index

Clear the records of an index without affecting its settings.


This method enables you to **delete an indexâ€™s contents** (records)
without removing any settings, rules and synonyms.



If you want to **remove the entire index** and not just its records,
**use the [delete method](/doc/api-reference/api-methods/delete-index/) instead**.

Clearing an index will have **no impact on its Analytics data** because
[you cannot clear an indexâ€™s analytics data](/doc/api-client/manage-indices/#analytics-data).


#### Examples



```php
$index->clearIndex();
```




### Create secured API Key

Generate a virtual API Key without any call to the server.


When you need to **restrict the scope of a API key**, we recommend using the *Secured API Key*.
You can generate a *Secured API Key* from any *API key*.

Learn more about [secured API keys](/doc/guides/security/api-keys/#secured-api-keys).


If you're generating *Secured API Keys* using the [JavaScript client](http://github.com/algolia/algoliasearch-client-javascript) on your frontend,
it will result in a **security breach** since the user is able to modify the filters you've set by modifying the code from the browser.
{:.alert .alert-warning}


You can define a number of restrictions (valid until, restrict indices, etc.).

If you want to **rate-limit a secured API Key**,
the **API key you generate from** the secured api key **needs to be rate-limited**.
You can do that via the dashboard or the API using the
[Add API Key](/doc/api-reference/api-methods/add-api-key/) or [Update API Key](/doc/api-reference/api-methods/update-api-key/) method


#### Examples

##### Generate a secured API key containing a filter

```php
// generate a public API key for user 42. Here, records are tagged with:
//  - 'user_XXXX' if they are visible by user XXXX
$public_key = \AlgoliaSearch\Client::generateSecuredApiKey(
  'SearchApiKey',
  [
    'filters' => '_tags:user_42'
  ]
);
```

##### Generate a secured API key with an expiration date

```php
// generate a public API key that is valid for 1 hour:
$validUntil = time() + 3600;
$public_key = \AlgoliaSearch\Client::generateSecuredApiKey(
  'SearchApiKey',
  [
    'validUntil' => $validUntil
  ]
);
```

##### Generate a secured API key with indices restriction

```php
// generate a public API key that is restricted to 'index1' and 'index2':

$public_key = \AlgoliaSearch\Client::generateSecuredApiKey(
  'SearchApiKey',
  [
    'restrictIndices' => 'index1,index2'
  ]
);
```

##### Generate a secured API key with a network restriction

```php
# generate a public API key that is restricted to '192.168.1.0/24':
$public_key = \AlgoliaSearch\Client::generateSecuredApiKey(
  'SearchApiKey',
  [
    'restrictSources' => '192.168.1.0/24'
  ]
);
```

##### Generate a secured API key with a rate limiting applied per user

```php
// generate a public API key for user 42. Here, records are tagged with:
//  - 'user_XXXX' if they are visible by user XXXX

$public_key = $client->generateSecuredApiKey(
  'YourSearchOnlyApiKey',
  [
    'filters' => 'user_42',
    'userToken' => 'user_42'
  ]
);
```




### Add API Key

Add a new API Key with specific permissions/restrictions.





#### Examples

##### Create API Key

```php
// Creates a new API key that can only perform search actions
$res = $client->addApiKey(['search']);
echo 'key=' . $res['key'] . "\n";
```

##### Create API Key with advanced restrictions

```php
// Creates a new index specific API key valid for 300 seconds,
// with a rate limit of 100 calls per hour per IP and a maximum of 20 hits

$validity = 300;
$maxQueriesPerIPPerHour = 100;
$maxHitsPerQuery = 20;

$params = [
    'acl'                    => ['search'],
    'indexes'                => ['dev_*'],
    'referers'               => ['algolia.com/*'],
    'restrictSources'        => '192.168.1.0/24',
    'queryParameters'        => 'typoTolerance=strict&ignorePlurals=false',
    'description'            => 'Limited search only API key for algolia.com',
];

$res = $client->addApiKey(
  $params,
  $validity,
  $maxQueriesPerIPPerHour,
  $maxHitsPerQuery
);

echo 'key=' . $res['key'] . "\n";
```




### Update API Key

Update the permissions of an existing API Key.





#### Examples

##### Update the permissions of an existing key

```php
// Update an existing API key that is valid for 300 seconds
$res = $client->updateApiKey(
  'myAPIKey',
  [
    'acl' => 'search',
    'validity' => 300
  ],

);

echo 'key=' . $res['key'] . "\n";

// Update an existing index specific API key valid for 300 seconds,
// with a rate limit of 100 calls per hour per IP and a maximum of 20 hits
$res = $index->updateApiKey(
  'myAPIKey',
  [
    'acl' => ['search'],
    'validity' => 300,
    'maxQueriesPerIPPerHour' => 100,
    'maxHitsPerQuery' => 20
  ]
);

echo 'key=' . $res['key'] . "\n";
```




### Delete API Key

Delete an existing API Key.


**Be careful** not to accidentally revoke a user's access to the Dashboard
by deleting any key that grants such access.
More generally: always be aware of a key's privileges before deleting it,
to avoid any unexpected consequences.




#### Examples

To delete an existing key:

```php
// Deletes a key
$res = $client->deleteApiKey('f420238212c54dcfad07ea0aa6d5c45f');
```




### Get API Key permissions

Get the permissions of an API Key.





#### Examples

To get the permissions of a given key:

```php
// Gets the rights of a key
$res = $client->getApiKey('f420238212c54dcfad07ea0aa6d5c45f');
```




### List API Keys

Get the full list of API Keys.





#### Examples

##### List existing keys

```php
$client->listApiKeys();
```




### Save synonym

Create or update a single synonym on an index.


Whether you create or update a synonym, you **must specify a unique objectID**.
If the objectID is not found in the index, the method will automatically create a new synonym.

Each synonym has a **single type**.

**Each type** consists of a **unique set of attributes**




#### Examples

##### Create/Update a regular two way synonym

```php
$forwardToReplicas = true;

$index->saveSynonym('a-unique-identifier', [
  'objectID' => 'a-unique-identifier',
  'type' => 'synonym',
  'synonyms' => [
    'car',
    'vehicle',
    'auto'
  ]
], $forwardToReplicas);
```

##### Create/Update a one way synonym

```php
$forwardToReplicas = true;

$index->saveSynonym('a-unique-identifier', [
  'objectID' => 'a-unique-identifier',
  'type' => 'oneWaySynonym',
  'input' => 'car',
  'synonyms' => [
    'vehicle',
    'auto'
  ]
], $forwardToReplicas);
```

##### Create/Update a alternative correction 1 synonym

```php
$forwardToReplicas = true;

$index->saveSynonym('a-unique-identifier', [
  'objectID' => 'a-unique-identifier',
  'type' => 'altCorrection1',
  'word' => 'car',
  'corrections' => [
    'vehicle',
    'auto'
  ]
], $forwardToReplicas);
```

##### Create/Update a alternative correction 2 synonym

```php
$forwardToReplicas = true;

$index->saveSynonym('a-unique-identifier', [
  'objectID' => 'a-unique-identifier',
  'type' => 'altCorrection2',
  'word' => 'car',
  'corrections' => [
    'vehicle',
    'auto'
  ]
], $forwardToReplicas);
```

##### Create/Update a placeholder synonym

To create placeholders, enclose the desired terms in angle brackets in the records.
Consider this record:

```json
{
  "address": "589 Howard <Street>"
}
```

The angle-bracketed <Street> above refers to the placeholder as defined
below when the synonym is created:

```php
$forwardToReplicas = true;

$index->saveSynonym('a-unique-identifier', [
  'objectID' => 'a-unique-identifier',
  'type' => 'placeholder',
  'placeholder' => '<Street>',
  'replacements' => [
    'street',
    'st'
  ]
], $forwardToReplicas);
```




### Batch synonyms

Create or update multiple synonyms.


This method enables you to **create or update one or more synonyms in a single call**.

You can also **recreate your entire set of synonyms**
by using the [replaceExistingSynonyms](/doc/api-reference/api-methods/batch-synonyms/#method-param-replaceexistingsynonyms)
parameter.

Note that **each synonym object counts as a single indexing operation**.




#### Examples

##### Batch synonyms

```php
// Batch synonyms,
// with replica forwarding and atomic replacement of existing synonyms

$forwardToReplicas = true;
$replaceExistingSynonyms = true;

$index->batchSynonyms(
  array(
    array(
      "objectID" => "a-unique-identifier",
      "type" => "synonym",
      "synonyms" => array("car", "vehicle", "auto")
    ),
    array(
      "objectID" => "another-unique-identifier",
      "type" => "synonym",
      "synonyms" => array("street", "st")
    )
  )
  $forwardToReplicas,
  $replaceExistingSynonyms
);
```




### Delete synonym

Remove a single synonym from an index using its object id.





#### Examples



```php
// Delete and forward to replicas
$forwardToReplicas = true;

$index->deleteSynonym("a-unique-identifier", $forwardToReplicas);
```




### Clear all synonyms

Remove all synonyms from an index.


This is a convenience method to delete all synonyms at once.

**This Clear All method should not be used on a production index to push a new list of synonyms**
because it will result in a short down period during which the index would have no synonyms
at all. Instead, use the [batch method](/doc/api-reference/api-methods/batch-synonyms/)
(with `replaceExistingSynonyms` set to true)
to atomically replace all synonyms of an index with no down time.
{: .alert .alert-warning}




#### Examples

##### Clear all synonyms and forward to replicas

```php
$index->clearSynonyms(true);
```




### Get synonym

Get a single synonym using its object id.





#### Examples

To retrieve a synonym by objectID:

```php
$synonym = $index->getSynonym("a-unique-identifier");
```




### Search synonyms

Get all synonyms that match a query.





#### Examples



```php
// Searching for "street" in synonyms and one-way synonyms;
// fetch the second page with 10 hits per page

$page = 1;
$hitsPerPage = 10;

$results = $index->searchSynonyms(
  "street",
  array(
    "synonym",
    "oneWaySynonym"
  ),
  $page,
  $hitsPerPage
);
```




### Export Synonyms

Retrieve an index's complete list of synonyms.


The list includes all synonyms - whether created on the dashboard or pushed by the API.

The method returns an iterator.




#### Examples



```php
$browser = $index->initSynonymIterator();

foreach ($browser as $key => $synonym) {
    var_dump($synonym);
}
```




### Save rule

Create or update a single rule.





#### Examples

##### Save a rule

```php
$rule = array(
    'objectID' => 'a-rule-id',
    'condition' => array(
        'pattern'   => 'smartphone',
        'anchoring' => 'contains',
    ),
    'consequence' => array(
        'params' => array(
            'filters' => 'category = 1',
        )
    )
);

// Optionally, to disable the rule
$rule['enabled'] = false;

// Optionally, to add validity time ranges
$rule['validity'] = array(
  array(
    'from' => time(),
    'until' => time() + 10*24*60*60,
  )
);

$response = $index->saveRule($rule['objectID'], $rule);
```




### Batch rules

Create or update a specified set of rules, or all rules.


Each rule will be created or updated, depending on whether a rule with the same `objectID` already exists.




#### Examples



```php
$rules = array($rule1, $rule2);
$response = $index->batchRules($rules);
```




### Get rule

Get the object/definition of a specific rule.





#### Examples



```php
$rule = $index->getRule('a-rule-id');
```




### Delete rule

Delete a specific rule using its id.





#### Examples



```php
$index->deleteRule('a-rule-id');
```




### Clear rules

Delete all rules in an index.





#### Examples



```php
$index->clearRules();
```




### Search rules

Search for rules matching various criteria.





#### Examples



```php
$response = $index->searchRules(array(
    'query' => 'something'
));
```




### Export rules

Retrieve an index's full list of rules using an iterator.


The list contains the rule name, plus the complete details of its conditions and consequences.

The list includes all rules, whether created on the dashboard or pushed by the API.

To export rules, you will need to use an iterator.




#### Examples



```php
$iterator = $index->initRuleIterator(500 /* hitsPerPage: 500 is default */);
foreach ($iterator as $key => $rule) {
  var_dump($rule);
}
```




### Add A/B test

Create an A/B test





#### Examples

##### Add an A/B test

```php
$endDate = new \DateTime('tomorrow');
$endDate = $endDate->format('Y-m-d\TH:i:s\Z');

$analytics = $client->initAnalytics();
$response = $analytics->addABTest([
  'name' => 'myABTest',
  'variants' => [
    [
      'index' => 'indexName1',
      'trafficPercentage' => 90,
      'description' =>  'a description'
    ],
    [
      'index' => 'indexName1-alt',
      'trafficPercentage' => 10
    ],
  ],
  "endAt" => $endDate,
]);
```




### Get A/B test

Get an A/B test information and results.





#### Examples

##### Get an A/B test

```php
$analytics = $client->initAnalytics();
$analytics->getABTest(42);
```




### List A/B tests

List A/B tests information and results.





#### Examples

##### List all A/B test

```php
$analytics = $client->initAnalytics();
$analytics->getABTests(array('offset' => 10, 'limit' => 20));
```




### Stop A/B test

Stop an A/B test


Marks the A/B Test as stopped.
At this point, the test is over and cannot be restarted.
Additionally, your application is back to normal: index A will perform as usual,
receiving 100% of all search requests.

**Note** that *stopping* is different from a [deleting](/doc/api-reference/api-methods/stop-ab-test/):
  When you **stop** a test, all associated metadata and metrics are stored and remain accessible.




#### Examples

##### Stop an A/B test

```php
$analytics = $client->initAnalytics();
$response = $analytics->stopABTest(42);
```




### Delete A/B test

Delete an A/B test


Deletes the A/B Test from your application
and removes all associated metadata & metrics.
You will therefore no longer be able to view or access the results.

**Note** that deleting a test is different from [stopping](/doc/api-reference/api-methods/stop-ab-test/):
  When you **delete** a test, all associated metadata and metrics are deleted.




#### Examples

##### Delete an A/B test

```php
$analytics = $client->initAnalytics();
$analytics->deleteABTest(42);
```




### Assign or Move userID

Assign or Move a userID to a cluster.


The time it takes to migrate (move) a user is proportional
to the amount of data linked to the userID.




#### Examples

##### Assign a user to a cluster

```php
$client->assignUserID('myUserID1', 'c1-test');
```




### Get top userID

Get the top 10 userIDs with the highest number of records per cluster.


The data returned will usually be a few seconds behind real-time, because userID usage
may take up to a few seconds to propagate to the different clusters.




#### Examples

##### Get the top userIDs

```php
$client->getTopUserID();
```




### Get userID

Returns the userID data stored in the mapping.


The data returned will usually be a few seconds behind real-time, because userID usage
may take up to a few seconds to propagate to the different clusters.




#### Examples

##### Get userID

```php
$client->getUserID('myUserID1');
```




### List clusters

List the clusters available in a multi-clusters setup for a single appID.





#### Examples

##### List clusters

```php
var_dump($client->listClusters());
```




### List userIDs

List the userIDs assigned to a multi-clusters appID.


The data returned will usually be a few seconds behind real-time, because userID usage
may take up to a few seconds to propagate to the different clusters.




#### Examples

##### List userId

```php
$page = 0;
$hitsPerPage = 20;
$client->listUserIDs($page, $hitsPerPage);
```




### Remove userID

Remove a userID and its associated data from the multi-clusters.



Even if the userID doesn't exist, the request to remove the userID will still succeed
because of the asynchronous handling of this request.



#### Examples

##### Remove a userID and its associated data

```php
$client->removeUserID('myUserID1');
```




### Search userID

Search for userIDs.


The data returned will usually be a few seconds behind real-time, because userID usage
may take up to a few seconds propagate to the different clusters.

To keep updates moving quicky, the index of userIDs isn't built synchronously with the mapping.
Instead, the index is built once every 12h, at the same time as the update of userID usage.
For example, when you perform a modification like adding or moving a userID,
the search will report an outdated value until the next rebuild of the mapping,
which takes place every 12h.
{: .alert .alert-info}




#### Examples

##### Search userID

```php
$page = 0;
$hitsPerPage = 12;
$client->searchUserIDs('query', 'c1-test', $page, $hitsPerPage);
```




### Get logs

Get the logs of the latest search and indexing operations.


You can retrieve the logs of your last 1,000 API calls.
It is designed for immediate, real-time debugging.

All `logs` older than 7 days will be removed and won't be accessible anymore from the API.
{: .alert .alert-warning }


This API is counted in your operation quota but is not logged.



#### Examples



```php
// Get last 10 log entries
$res = $client->getLogs();

// Get last 100 log entries
$res = $client->getLogs(0, 100);
```




### Configuring timeouts

Override the pre-configured timeouts.


Network & DNS resolution can be slow.
That is why we have pre-configured timeouts.
We do not advise to change them, but it could make sense to change them in some special cases.

**Note:** Not all parameters are available for every language.
See [Parameters](/doc/api-reference/api-methods/configuring-timeouts/#parameters) below.




#### Examples



```php
$client = new \AlgoliaSearch\Client('YourApplicationID', 'YourAdminAPIKey');
$client->setConnectTimeout(
  2, // the connection timeout
  30, // the read timeout for the query
  5 // the read timeout used for search queries only
);
```




### Set extra header

Sends an extra http header to Algolia, for all subsequent queries.


This method allows you to send the server a specific key/value pair - that we call
an **extra http header**, with every query.
By doing this, you are giving the api an additional header that it can later
use in certain situations.

Here are some headers with different use cases:

- Setting **X-Forwarded-For** for **analytics** purposes.
If your server sends the end-user's IP along with every search, this enables analytics to distinguish between end-users.
Otherwise, the analytics will be based on the server IP, not giving you the detail of each user. Alternatively, see **X-Algolia-UserToken** below.
- Setting **X-Forwarded-For** for **geo** purposes.
This ensures that the geo search location will be based on the IP of the end-user and not that of your backend server.
For an example of this, see the [aroundLatLngViaIP parameter](/doc/api-reference/api-parameters/aroundLatLngViaIP/).
- Setting **X-Algolia-UserToken** for [API key rate-limit](/doc/guides/security/api-keys/#rate-limit) purposes.
- Setting **X-Algolia-UserToken** for **analytics** purposes. The provided value will be used by the analytics to distinguish between end-users.
It takes priority over any value given to **X-Forwarded-For**. Use this header if you need to forward the end-user's identity without relying on IPs.

Note that this will be replaced eventually by the "Request Options" parameter, which will allow you to set the header as part of your query parameters.




#### Examples



```php
$client->setExtraHeader('NAME-OF-HEADER', 'value-of-header');
```




### Wait for operations

Wait for a task to complete before executing the next line of code, to synchronize index updates.


All write operations in Algolia are asynchronous by design.

It means that when you add or update an object to your index, our servers will
reply to your request with a `taskID` as soon as they understood the write operation.
The actual insert and indexing will be done after replying to your code.
You can wait for a task to complete by using the `taskID' and this method.

Check out our full discussion about [asynchronous methods](/doc/api-client/indexing/#asynchronous-methods).




#### Examples

##### Wait for indexing of a new object:

```php
$res = $index->addObject(
  [
    'firstname' => 'Jimmie',
    'lastname'  => 'Barninger'
  ]
);
$index->waitTask($res['taskID']);
```

If you want to ensure multiple objects have been indexed, you only need to check
the highest `taskID` (last operation)

##### Wait for indexing of a new object and send extra http header

```php
$extraHeaders = [
  'X-FORWARDED-FOR' => '94.228.178.246'
];

$timeBeforeRetry = 100;

$index->waitTask($res['taskID'], $timeBeforeRetry, $extraHeaders);
```
