# Algolia Search API Client for PHP

[Algolia Search](https://www.algolia.com) is a hosted full-text, numerical, and faceted search engine capable of delivering realtime results from the first keystroke.
The **Algolia Search API Client for PHP** lets you easily use the [Algolia Search REST API](https://www.algolia.com/doc/rest-api/search) from your PHP code.

[![Build Status](https://travis-ci.org/algolia/algoliasearch-client-php.svg?branch=master)](https://travis-ci.org/algolia/algoliasearch-client-php) [![Latest Stable Version](https://poser.pugx.org/algolia/algoliasearch-client-php/v/stable.svg)](https://packagist.org/packages/algolia/algoliasearch-client-php) [![Coverage Status](https://coveralls.io/repos/algolia/algoliasearch-client-php/badge.svg)](https://coveralls.io/r/algolia/algoliasearch-client-php)


**Note:** An easier-to-read version of this documentation is available on
[Algolia's website](https://www.algolia.com/doc/api-client/php/).

# Table of Contents


**Getting Started**

1. [Install](#install)
1. [Init index - `initIndex`](#init-index---initindex)
1. [Quick Start](#quick-start)

**Search**

1. [Search an index - `search`](#search-an-index---search)
1. [Search Response Format](#search-response-format)
1. [Search Parameters](#search-parameters)
1. [Search multiple indices - `multipleQueries`](#search-multiple-indices---multiplequeries)
1. [Get Objects - `getObjects`](#get-objects---getobjects)
1. [Search for facet values - `searchForFacetValues`](#search-for-facet-values---searchforfacetvalues)

**Indexing**

1. [Add Objects - `addObjects`](#add-objects---addobjects)
1. [Update objects - `saveObjects`](#update-objects---saveobjects)
1. [Partial update objects - `partialUpdateObjects`](#partial-update-objects---partialupdateobjects)
1. [Delete objects - `deleteObjects`](#delete-objects---deleteobjects)
1. [Delete by query - `deleteByQuery`](#delete-by-query---deletebyquery)
1. [Wait for operations - `waitTask`](#wait-for-operations---waittask)

**Settings**

1. [Get settings - `getSettings`](#get-settings---getsettings)
1. [Set settings - `setSettings`](#set-settings---setsettings)
1. [Index settings parameters](#index-settings-parameters)

**Parameters**

1. [Overview](#overview)
1. [Search](#search)
1. [Attributes](#attributes)
1. [Ranking](#ranking)
1. [Filtering / Faceting](#filtering--faceting)
1. [Highlighting / Snippeting](#highlighting--snippeting)
1. [Pagination](#pagination)
1. [Typos](#typos)
1. [Geo-Search](#geo-search)
1. [Query Strategy](#query-strategy)
1. [Performance](#performance)
1. [Advanced](#advanced)

**Manage Indices**

1. [Create an index](#create-an-index)
1. [List indices - `listIndexes`](#list-indices---listindexes)
1. [Delete an index - `deleteIndex`](#delete-an-index---deleteindex)
1. [Clear an index - `clearIndex`](#clear-an-index---clearindex)
1. [Copy index - `copyIndex`](#copy-index---copyindex)
1. [Move index - `moveIndex`](#move-index---moveindex)

**Api keys**

1. [Overview](#overview)
1. [Generate key - `generateSecuredApiKey`](#generate-key---generatesecuredapikey)

**Synonyms**

1. [Save synonym - `saveSynonym`](#save-synonym---savesynonym)
1. [Batch synonyms - `batchSynonyms`](#batch-synonyms---batchsynonyms)
1. [Editing Synonyms](#editing-synonyms)
1. [Delete synonym - `deleteSynonym`](#delete-synonym---deletesynonym)
1. [Clear all synonyms - `clearSynonyms`](#clear-all-synonyms---clearsynonyms)
1. [Get synonym - `getSynonym`](#get-synonym---getsynonym)
1. [Search synonyms - `searchSynonyms`](#search-synonyms---searchsynonyms)

**Advanced**

1. [Custom batch - `batch`](#custom-batch---batch)
1. [Backup / Export an index - `browse`](#backup--export-an-index---browse)
1. [List api keys - `listApiKeys`](#list-api-keys---listapikeys)
1. [Add user key - `addUserKey`](#add-user-key---adduserkey)
1. [Update user key - `updateUserKey`](#update-user-key---updateuserkey)
1. [Delete user key - `deleteUserKey`](#delete-user-key---deleteuserkey)
1. [Get key permissions - `getUserKeyACL`](#get-key-permissions---getuserkeyacl)
1. [Get latest logs - `getLogs`](#get-latest-logs---getlogs)
1. [REST API](#rest-api)


# Guides & Tutorials

Check our [online guides](https://www.algolia.com/doc):

* [Data Formatting](https://www.algolia.com/doc/indexing/formatting-your-data)
* [Import and Synchronize data](https://www.algolia.com/doc/indexing/import-synchronize-data/php)
* [Autocomplete](https://www.algolia.com/doc/search/auto-complete)
* [Instant search page](https://www.algolia.com/doc/search/instant-search)
* [Filtering and Faceting](https://www.algolia.com/doc/search/filtering-faceting)
* [Sorting](https://www.algolia.com/doc/relevance/sorting)
* [Ranking Formula](https://www.algolia.com/doc/relevance/ranking)
* [Typo-Tolerance](https://www.algolia.com/doc/relevance/typo-tolerance)
* [Geo-Search](https://www.algolia.com/doc/geo-search/geo-search-overview)
* [Security](https://www.algolia.com/doc/security/best-security-practices)
* [API-Keys](https://www.algolia.com/doc/security/api-keys)
* [REST API](https://www.algolia.com/doc/rest)


# Getting Started



## Install

### With composer (Recommended)

Install the package via [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
composer require algolia/algoliasearch-client-php
```

### Without composer

If you don't use Composer, you can download the [package](https://github.com/algolia/algoliasearch-client-php/archive/master.zip) and include it in your code.

```php
<?php
require_once('algoliasearch-client-php-master/algoliasearch.php');
```

### Framework Integrations

If you're a Symfony or Laravel user, you're probably looking for the following integrations

 - **Laravel**: [algolia/algoliasearch-laravel](https://github.com/algolia/algoliasearch-laravel)
 - **Symfony**: [algolia/AlgoliaSearchBundle](https://github.com/algolia/AlgoliaSearchBundle)

## Init index - `initIndex` 

To initialize the client, you need your **Application ID** and **API Key**. You can find both of them on [your Algolia account](https://www.algolia.com/api-keys).

```php
<?php
// composer autoload
require __DIR__ . '/vendor/autoload.php';
// if you are not using composer: require_once 'path/to/algoliasearch.php';

$client = new \AlgoliaSearch\Client('YourApplicationID', 'YourAPIKey');
$index = $client->initIndex('index_name');
```

## Quick Start

In 30 seconds, this quick start tutorial will show you how to index and search objects.

Without any prior configuration, you can start indexing [500 contacts](https://github.com/algolia/algoliasearch-client-csharp/blob/master/contacts.json) in the ```contacts``` index using the following code:

```php
<?php
$index = $client->initIndex('contacts');
$batch = json_decode(file_get_contents('contacts.json'), true);
$index->addObjects($batch);
```

You can now search for contacts using firstname, lastname, company, etc. (even with typos):

```php
<?php
// search by firstname
var_dump($index->search('jimmie'));

// search a firstname with typo
var_dump($index->search('jimie'));

// search for a company
var_dump($index->search('california paint'));

// search for a firstname & company
var_dump($index->search('jimmie paint'));
```

Settings can be customized to tune the search behavior. For example, you can add a custom sort by number of followers to the already great built-in relevance:

```php
<?php
$index->setSettings(['customRanking' => ['desc(followers)']]);
```

You can also configure the list of attributes you want to index by order of importance (first = most important):

```php
<?php
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

Since the engine is designed to suggest results as you type, you'll generally search by prefix. In this case the order of attributes is very important to decide which hit is the best:

```php
<?php
var_dump($index->search('or'));
var_dump($index->search('jim'));
```

**Note:** **Note:** If you are building a web application, you may be more interested in using our [JavaScript client](https://github.com/algolia/algoliasearch-client-javascript) to perform queries.

It brings two benefits:
  * Your users get a better response time by not going through your servers
  * It will offload unnecessary tasks from your servers

```html
<script src="https://cdn.jsdelivr.net/algoliasearch/3/algoliasearch.min.js"></script>
<script>
var client = algoliasearch('ApplicationID', 'apiKey');
var index = client.initIndex('indexName');

// perform query "jim"
index.search('jim', searchCallback);

// the last optional argument can be used to add search parameters
index.search(
  'jim', {
    hitsPerPage: 5,
    facets: '*',
    maxValuesPerFacet: 10
  },
  searchCallback
);

function searchCallback(err, content) {
  if (err) {
    console.error(err);
    return;
  }

  console.log(content);
}
</script>
```


# Search



## Search an index - `search` 

**Notes:** If you are building a web application, you may be more interested in using our [JavaScript client](https://github.com/algolia/algoliasearch-client-javascript) to perform queries. It brings two benefits:
  * Your users get a better response time by not going through your servers
  * It will offload unnecessary tasks from your servers.

To perform a search, you only need to initialize the index and perform a call to the search function.

The search query allows only to retrieve 1000 hits. If you need to retrieve more than 1000 hits (e.g. for SEO), you can use [Backup / Export an index](#backup--export-an-index).

```php
<?php
$index = $client->initIndex('contacts');
$res = $index->search('query string');
$res = $index->search('query string', ['attributesToRetrieve' => 'firstname,lastname', 'hitsPerPage' => 50]);
```

## Search Response Format

### Sample

The server response will look like:

```json
{
  "hits": [
    {
      "firstname": "Jimmie",
      "lastname": "Barninger",
      "objectID": "433",
      "_highlightResult": {
        "firstname": {
          "value": "<em>Jimmie</em>",
          "matchLevel": "partial"
        },
        "lastname": {
          "value": "Barninger",
          "matchLevel": "none"
        },
        "company": {
          "value": "California <em>Paint</em> & Wlpaper Str",
          "matchLevel": "partial"
        }
      }
    }
  ],
  "page": 0,
  "nbHits": 1,
  "nbPages": 1,
  "hitsPerPage": 20,
  "processingTimeMS": 1,
  "query": "jimmie paint",
  "params": "query=jimmie+paint&attributesToRetrieve=firstname,lastname&hitsPerPage=50"
}
```

### Fields

- `hits` (array): The hits returned by the search, sorted according to the ranking formula.

    Hits are made of the JSON objects that you stored in the index; therefore, they are mostly schema-less. However, Algolia does enrich them with a few additional fields:

    - `_highlightResult` (object, optional): Highlighted attributes. *Note: Only returned when [attributesToHighlight](#attributestohighlight) is non-empty.*

        - `${attribute_name}` (object): Highlighting for one attribute.

            - `value` (string): Markup text with occurrences highlighted. The tags used for highlighting are specified via [highlightPreTag](#highlightpretag) and [highlightPostTag](#highlightposttag).

            - `matchLevel` (string, enum) = {`none` \| `partial` \| `full`}: Indicates how well the attribute matched the search query.

            - `matchedWords` (array): List of words *from the query* that matched the object.

            - `fullyHighlighted` (boolean): Whether the entire attribute value is highlighted.

    - `_snippetResult` (object, optional): Snippeted attributes. *Note: Only returned when [attributesToSnippet](#attributestosnippet) is non-empty.*

        - `${attribute_name}` (object): Snippeting for the corresponding attribute.

            - `value` (string): Markup text with occurrences highlighted and optional ellipsis indicators. The tags used for highlighting are specified via [highlightPreTag](#highlightpretag) and [highlightPostTag](#highlightposttag). The text used to indicate ellipsis is specified via [snippetEllipsisText](#snippetellipsistext).

            - `matchLevel` (string, enum) = {`none` \| `partial` \| `full`}: Indicates how well the attribute matched the search query.

    - `_rankingInfo` (object, optional): Ranking information. *Note: Only returned when [getRankingInfo](#getrankinginfo) is `true`.*

        - `nbTypos` (integer): Number of typos encountered when matching the record. Corresponds to the `typos` ranking criterion in the ranking formula.

        - `firstMatchedWord` (integer): Position of the most important matched attribute in the attributes to index list. Corresponds to the `attribute` ranking criterion in the ranking formula.

        - `proximityDistance` (integer): When the query contains more than one word, the sum of the distances between matched words. Corresponds to the `proximity` criterion in the ranking formula.

        - `userScore` (integer): Custom ranking for the object, expressed as a single numerical value. Conceptually, it's what the position of the object would be in the list of all objects sorted by custom ranking. Corresponds to the `custom` criterion in the ranking formula.

        - `geoDistance` (integer): Distance between the geo location in the search query and the best matching geo location in the record, divided by the geo precision.

        - `geoPrecision` (integer): Precision used when computed the geo distance, in meters. All distances will be floored to a multiple of this precision.

        - `nbExactWords` (integer): Number of exactly matched words. If `alternativeAsExact` is set, it may include plurals and/or synonyms.

        - `words` (integer): Number of matched words, including prefixes and typos.

        - `filters` (integer): *This field is reserved for advanced usage.* It will be zero in most cases.

        - `matchedGeoLocation` (object): Geo location that matched the query. *Note: Only returned for a geo search.*

            - `lat` (float): Latitude of the matched location.

            - `lng` (float): Longitude of the matched location.

            - `distance` (integer): Distance between the matched location and the search location (in meters). **Caution:** Contrary to `geoDistance`, this value is *not* divided by the geo precision.

    - `_distinctSeqID` (integer): *Note: Only returned when [distinct](#distinct) is non-zero.* When two consecutive results have the same value for the attribute used for "distinct", this field is used to distinguish between them.

- `nbHits` (integer): Number of hits that the search query matched.

- `page` (integer): Index of the current page (zero-based). See the [page](#page) search parameter. *Note: Not returned if you use `offset`/`length` for pagination.*

- `hitsPerPage` (integer): Maximum number of hits returned per page. See the [hitsPerPage](#hitsperpage) search parameter. *Note: Not returned if you use `offset`/`length` for pagination.*

- `nbPages` (integer): Number of pages corresponding to the number of hits. Basically, `ceil(nbHits / hitsPerPage)`. *Note: Not returned if you use `offset`/`length` for pagination.*

- `processingTimeMS` (integer): Time that the server took to process the request, in milliseconds. *Note: This does not include network time.*

- `query` (string): An echo of the query text. See the [query](#query) search parameter.

- `queryAfterRemoval` (string, optional): *Note: Only returned when [removeWordsIfNoResults](#removewordsifnoresults) is set to `lastWords` or `firstWords`.* A markup text indicating which parts of the original query have been removed in order to retrieve a non-empty result set. The removed parts are surrounded by `<em>` tags.

- `params` (string, URL-encoded): An echo of all search parameters.

- `message` (string, optional): Used to return warnings about the query.

- `aroundLatLng` (string, optional): *Note: Only returned when [aroundLatLngViaIP](#aroundlatlngviaip) is set.* The computed geo location. **Warning: for legacy reasons, this parameter is a string and not an object.** Format: `${lat},${lng}`, where the latitude and longitude are expressed as decimal floating point numbers.

- `automaticRadius` (integer, optional): *Note: Only returned for geo queries without an explicitly specified radius (see `aroundRadius`).* The automatically computed radius. **Warning: for legacy reasons, this parameter is a string and not an integer.**

When [getRankingInfo](#getrankinginfo) is set to `true`, the following additional fields are returned:

- `serverUsed` (string): Actual host name of the server that processed the request. (Our DNS supports automatic failover and load balancing, so this may differ from the host name used in the request.)

- `parsedQuery` (string): The query string that will be searched, after normalization. Normalization includes removing stop words (if [removeStopWords](#removestopwords) is enabled), and transforming portions of the query string into phrase queries (see [advancedSyntax](#advancedsyntax)).

- `timeoutCounts` (boolean) - DEPRECATED: Please use `exhaustiveFacetsCount` in remplacement.

- `timeoutHits` (boolean) - DEPRECATED: Please use `exhaustiveFacetsCount` in remplacement.

... and ranking information is also added to each of the hits (see above).

When [facets](#facets) is non-empty, the following additional fields are returned:

- `facets` (object): Maps each facet name to the corresponding facet counts:

    - `${facet_name}` (object): Facet counts for the corresponding facet name:

        - `${facet_value}` (integer): Count for this facet value.

- `facets_stats` (object, optional): *Note: Only returned when at least one of the returned facets contains numerical values.* Statistics for numerical facets:

    - `${facet_name}` (object): The statistics for a given facet:

        - `min` (integer | float): The minimum value in the result set.

        - `max` (integer | float): The maximum value in the result set.

        - `avg` (integer | float): The average facet value in the result set.

        - `sum` (integer | float): The sum of all values in the result set.

- `exhaustiveFacetsCount` (boolean): Whether the counts are exhaustive (`true`) or approximate (`false`). *Note: In some conditions when [distinct](#distinct) is greater than 1 and an empty query without refinement is sent, the facet counts may not always be exhaustive.*

## Search Parameters

You can see the full list of search parameters here:
[https://www.algolia.com/doc/api-client/php/parameters/](https://www.algolia.com/doc/api-client/php/parameters/)

## Search multiple indices - `multipleQueries` 

You can send multiple queries with a single API call using a batch of queries:

```php
<?php
// perform 3 queries in a single API call:
//  - 1st query targets index `categories`
//  - 2nd and 3rd queries target index `products`
$queries = [
    ['indexName' => 'categories', 'query' => $myQueryString, 'hitsPerPage' => 3],
    ['indexName' => 'products', 'query' => $myQueryString, 'hitsPerPage' => 3, 'facetFilters' => 'promotion'],
    ['indexName' => 'products', 'query' => $myQueryString, 'hitsPerPage' => 10]
];

$results = $client->multipleQueries($queries);

var_dump(results['results']):
```

You can specify a `strategy` parameter to optimize your multiple queries:

- `none`: Execute the sequence of queries until the end.
- `stopIfEnoughMatches`: Execute the sequence of queries until the number of hits is reached by the sum of hits.

### Response

The resulting JSON contains the following fields:

- `results` (array): The results for each request, in the order they were submitted. The contents are the same as in [Search an index](#search-an-index).
    Each result also includes the following additional fields:

    - `index` (string): The name of the targeted index.
    - `processed` (boolean, optional): *Note: Only returned when `strategy` is `stopIfEnoughmatches`.* Whether the query was processed.

## Get Objects - `getObjects` 

You can easily retrieve an object using its `objectID` and optionally specify a comma separated list of attributes you want:

```php
<?php
// Retrieves all attributes
$index->getObject('myID');

// Retrieves firstname and lastname attributes
$index->getObject('myID', 'firstname,lastname');

// Retrieves only the firstname attribute
$index->getObject('myID', 'firstname');
```

You can also retrieve a set of objects:

```php
<?php
$index->getObjects(['myID1', 'myID2']);
```

## Search for facet values - `searchForFacetValues` 

When there are many facet values for a given facet, it may be useful to search within them. For example, you may have dozens of 'brands' for a given index of 'clothes'. Rather than displaying all of the brands, it is often best to only display the most popular and add a search input to allow the user to search for the brand that they are looking for.

Searching on facet values is different than a regular search because you are searching only on *facet values*, not *objects*.

The results are sorted by decreasing count. By default, maximum 10 results are returned. This can be adjusted via [maxFacetHits](#maxfacethits). No pagination is possible.

The facet search can optionally take regular search query parameters.
In that case, it will return only facet values that both:

1. match the facet query
2. are contained in objects matching the regular search query.

**Warning:** For a facet to be searchable, it must have been declared with the `searchable()` modifier in the [attributesForFaceting](#attributesforfaceting) index setting.

#### Example

Let's imagine we have objects similar to this one:

```json
{
    "name": "iPhone 7 Plus",
    "brand": "Apple",
    "category": [
        "Mobile phones",
        "Electronics"
    ]
}
```

Then:

```php
<?php
# Search the values of the "category" facet matching "phone".
$index->searchForFacetValues("category", "phone");
```

... could return:

```json
{
    "facetHits": [
        {
            "value": "Mobile phones",
            "highlighted": "Mobile <em>phone</em>s",
            "count": 507
        },
        {
            "value": "Phone cases",
            "highlighted": "<em>Phone</em> cases",
            "count": 63
        }
    ]
}
```

Let's filter with an additional, regular search query:

```php
<?php
$query = [
    'filters': 'brand:Apple'
];
# Search the "category" facet for values matching "phone" in records
# having "Apple" in their "brand" facet.
$index->searchForFacetValues("category", "phone", $query);
```

... could return:

```json
{
    "facetHits": [
        {
            "value": "Mobile phones",
            "highlighted": "Mobile <em>phone</em>s",
            "count": 41
        }
    ]
}
```


# Indexing



## Add Objects - `addObjects` 

Each entry in an index has a unique identifier called `objectID`. There are two ways to add an entry to the index:

 1. Supplying your own `objectID`.
 2. Using automatic `objectID` assignment. You will be able to access it in the response.

Using your own unique IDs when creating records is a good way to make future updates easier without having to keep track of Algolia's generated IDs.
The value you provide for objectIDs can be an integer or a string.

You don't need to explicitly create an index, it will be automatically created the first time you add an object.
Objects are schema less so you don't need any configuration to start indexing.
If you wish to configure things, the settings section provides details about advanced settings.

Example with automatic `objectID` assignments:

```php
<?php
$res = $index->addObjects(
    [
        [
            'firstname' => 'Jimmie',
            'lastname'  => 'Barninger'
        ],
        [
            'firstname' => 'Warren',
            'lastname'  => 'myID1'
        ]
    ]
);
```

Example with manual `objectID` assignments:

```php
<?php
$res = $index->addObjects(
    [
        [
            'objectID' => '1',
            'firstname' => 'Jimmie',
            'lastname'  => 'Barninger'
        ],
        [
            'objectID' => '2',
            'firstname' => 'Warren',
            'lastname'  => 'myID1'
        ]
    ]
);
```

To add a single object, use the following method:

```php
<?php
$res = $index->addObject(
    [
        'firstname' => 'Jimmie',
        'lastname'  => 'Barninger'
    ],
    'myID'
);
echo 'objectID=' . $res['objectID'] . "\n";
```

## Update objects - `saveObjects` 

You have three options when updating an existing object:

 1. Replace all its attributes.
 2. Replace only some attributes.
 3. Apply an operation to some attributes.

Example on how to replace all attributes existing objects:

```php
<?php
$res = $index->saveObjects(
    [
        [
            'firstname' => 'Jimmie',
            'lastname'  => 'Barninger',
            'objectID'  => 'SFO'
        ],
        [
            'firstname' => 'Warren',
            'lastname'  => 'Speach',
            'objectID'  => 'myID2'
        ]
    ]
);
```

To update a single object, you can use the following method:

```php
<?php
$index->saveObject(
    [
        'firstname' => 'Jimmie',
        'lastname'  => 'Barninger',
        'city'      => 'New York',
        'objectID'  => 'myID'
    ]
);
```

## Partial update objects - `partialUpdateObjects` 

You have many ways to update an object's attributes:

 1. Set the attribute value
 2. Add a string or number element to an array
 3. Remove an element from an array
 4. Add a string or number element to an array if it doesn't exist
 5. Increment an attribute
 6. Decrement an attribute

Example to update only the city attribute of an existing object:

```php
<?php
$index->partialUpdateObject(
    [
        'city'     => 'San Francisco',
        'objectID' => 'myID'
    ]
);
```

Example to add a tag:

```php
<?php
$index->partialUpdateObject(
    [
        '_tags'    => ['value' => 'MyTag', '_operation' => 'Add'],
        'objectID' => 'myID'
    ]
);
```

Example to remove a tag:

```php
<?php
$index->partialUpdateObject(
    [
        '_tags'    => ['value' => 'MyTag', '_operation' => 'Remove'],
        'objectID' => 'myID'
    ]
);
```

Example to add a tag if it doesn't exist:

```php
<?php
$index->partialUpdateObject(
    [
        '_tags'    => ['value' => 'MyTag', '_operation' => 'AddUnique'],
        'objectID' => 'myID'
    ]
);
```

Example to increment a numeric value:

```php
<?php
$index->partialUpdateObject(
    [
        'price'    => ['value' => 42, '_operation' => 'Increment'],
        'objectID' => 'myID'
    ]
);
```

Note: Here we are incrementing the value by `42`. To increment just by one, put
`value:1`.

Example to decrement a numeric value:

```php
<?php
$index->partialUpdateObject(
    [
        'price'    => ['value' => 42, '_operation' => 'Decrement'],
        'objectID' => 'myID'
    ]
);
```

Note: Here we are decrementing the value by `42`. To decrement just by one, put
`value:1`.

To partial update multiple objects using one API call, you can use the following method:

```php
<?php
$res = $index->partialUpdateObjects(
    [
        [
            'firstname' => 'Jimmie',
            'objectID'  => 'SFO'
        ],
        [
            'firstname' => 'Warren',
            'objectID'  => 'myID2'
        ]
    ]
);
```

## Delete objects - `deleteObjects` 

You can delete objects using their `objectID`:

```php
<?php
$res = $index->deleteObjects(["myID1", "myID2"]);
```

To delete a single object, you can use the following method:

```php
<?php
$index->deleteObject('myID');
```

## Delete by query - `deleteByQuery` 

You can delete all objects matching a single query with the following code. Internally, the API client performs the query, deletes all matching hits, and waits until the deletions have been applied.

Take your precautions when using this method. Calling it with an empty query will result in cleaning the index of all its records.

```php
<?php
$params = [];
$index->deleteByQuery('John', $params);
```

## Wait for operations - `waitTask` 

All write operations in Algolia are asynchronous by design.

It means that when you add or update an object to your index, our servers will
reply to your request with a `taskID` as soon as they understood the write
operation.

The actual insert and indexing will be done after replying to your code.

You can wait for a task to complete using the `waitTask` method on the `taskID` returned by a write operation.

For example, to wait for indexing of a new object:

```php
<?php
$res = $index->addObject(
    [
        'firstname' => 'Jimmie',
        'lastname'  => 'Barninger'
    ]
);
$index->waitTask($res['taskID']);
```

If you want to ensure multiple objects have been indexed, you only need to check
the biggest `taskID`.


# Settings



## Get settings - `getSettings` 

You can retrieve settings:

```php
<?php
$settings = $index->getSettings();
var_dump($settings);
```

## Set settings - `setSettings` 

```php
<?php
$index->setSettings(array("customRanking" => array("desc(followers)")));
```

You can find the list of parameters you can set in the [Settings Parameters](#index-settings-parameters) section

**Warning**

Performance wise, it's better to do a `setSettings` before pushing the data

### Replica settings

You can forward all settings updates to the replicas of an index by using the `forwardToReplicas` option:

```php
<?php
$index->setSettings(['customRanking' => ['desc(followers)']], true);
```

## Index settings parameters

You can see the full list of search parameters here:
[https://www.algolia.com/doc/api-client/php/parameters/](https://www.algolia.com/doc/api-client/php/parameters/)


# Manage Indices



## Create an index

To create an index, you need to perform any indexing operation like:
- set settings
- add object

## List indices - `listIndexes` 

You can list all your indices along with their associated information (number of entries, disk size, etc.) with the `listIndexes` method:

```php
<?php
var_dump($client->listIndexes());
```

## Delete an index - `deleteIndex` 

You can delete an index using its name:

```php
<?php
$client->deleteIndex('contacts');
```

## Clear an index - `clearIndex` 

You can delete the index contents without removing settings and index specific API keys by using the `clearIndex` command:

```php
<?php
$index->clearIndex();
```

## Copy index - `copyIndex` 

You can copy an existing index using the `copy` command.

**Warning**: The copy command will overwrite the destination index.

```php
<?php
// Copy MyIndex in MyIndexCopy
$res = $client->copyIndex('MyIndex', 'MyIndexCopy');
```

## Move index - `moveIndex` 

In some cases, you may want to totally reindex all your data. In order to keep your existing service
running while re-importing your data we recommend the usage of a temporary index plus an atomical
move using the `moveIndex` method.

```php
<?php
// Rename MyNewIndex in MyIndex (and overwrite it)
$res = $client->moveIndex('MyNewIndex', 'MyIndex');
```

**Note:** The moveIndex method overrides the destination index, and deletes the temporary one.
  In other words, there is no need to call the `clearIndex` or `deleteIndex` methods to clean the temporary index.
It also overrides all the settings of the destination index (except the [replicas](#replicas) parameter that need to not be part of the temporary index settings).

**Recommended steps**
If you want to fully update your index `MyIndex` every night, we recommend the following process:

 1. Get settings and synonyms from the old index using [Get settings](#get-settings)
  and [Get synonym](#get-synonym).
 1. Apply settings and synonyms to the temporary index `MyTmpIndex`, (this will create the `MyTmpIndex` index)
  using [Set settings](#set-settings) and [Batch synonyms](#batch-synonyms) ([!] Make sure to remove the [replicas](#replicas) parameter from the settings if it exists.
 1. Import your records into a new index using [Add Objects](#add-objects)).
 1. Atomically replace the index `MyIndex` with the content and settings of the index `MyTmpIndex`
 using the [Move index](#move-index) method.
 This will automatically override the old index without any downtime on the search.
 
 You'll end up with only one index called `MyIndex`, that contains the records and settings pushed to `MyTmpIndex`
 and the replica-indices that were initially attached to `MyIndex` will be in sync with the new data.


# Api keys



## Overview

When creating your Algolia Account, you'll notice there are 3 different API Keys:

- **Admin API Key** - it provides full control of all your indices.
*The admin API key should always be kept secure;
do NOT give it to anybody; do NOT use it from outside your back-end as it will
allow the person who has it to query/change/delete data*

- **Search-Only API Key** - It allows you to search on every indices.

- **Monitoring API Key** - It allows you to access the [Monitoring API](https://www.algolia.com/doc/rest-api/monitoring)

### Other types of API keys

The *Admin API Key* and *Search-Only API Key* both have really large scope and sometimes you want to give a key to
someone that have restricted permissions, can it be an index, a rate limit, a validity limit, ...

To address those use-cases we have two different type of keys:

- **Secured API Keys**

When you need to restrict the scope of the *Search Key*, we recommend to use *Secured API Key*.
You can generate them on the fly (without any call to the API)
from the *Search Only API Key* or any search *User Key* using the [Generate key](#generate-key) method

- **User API Keys**

If *Secured API Keys* does not meet your requirements, you can make use of *User keys*.
Managing and especially creating those keys requires a call to the API.

We have several methods to manage them:

- [Add user key](#add-user-key)
- [Update user key](#update-user-key)
- [Delete user key](#delete-user-key)
- [List api keys](#list-api-keys)
- [Get key permissions](#get-key-permissions)

## Generate key - `generateSecuredApiKey` 

When you need to restrict the scope of the *Search Key*, we recommend to use *Secured API Key*.
You can generate a *Secured API Key* from the *Search Only API Key* or any search *User API Key*

There is a few things to know about *Secured API Keys*
- They always need to be generated **on your backend** using one of our API Client
- You can generate them on the fly (without any call to the API)
- They will not appear on the dashboard as they are generated without any call to the API
- The key you use to generate it **needs to become private** and you should not use it in your frontend.
- The generated secured API key **will inherit any restriction from the search key it has been generated from**

You can then use the key in your frontend code

```js
var client = algoliasearch('YourApplicationID', 'YourPublicAPIKey');

var index = client.initIndex('indexName')

index.search('something', function(err, content) {
  if (err) {
    console.error(err);
    return;
  }

  console.log(content);
});
```

#### Filters

Every filter set in the API key will always be applied. On top of that [filters](#filters) can be applied
in the query parameters.

```php
<?php
// generate a public API key for user 42. Here, records are tagged with:
//  - 'user_XXXX' if they are visible by user XXXX
$public_key = \AlgoliaSearch\Client::generateSecuredApiKey('SearchApiKey', ['filters' => '_tags:user_42']);
```

**Warning**:

If you set filters in the key `groups:admin`, and `groups:press OR groups:visitors` in the query parameters,
this will be equivalent to `groups:admin AND (groups:press OR groups:visitors)`

##### Having one API Key per User

One of the usage of secured API keys, is to have allow users to see only part of an index, when this index
contains the data of all users.
In that case, you can tag all records with their associated `user_id` in order to add a `user_id=42` filter when
generating the *Secured API Key* to retrieve only what a user is tagged in.

**Warning**

If you're generating *Secured API Keys* using the [JavaScript client](http://github.com/algolia/algoliasearch-client-javascript) in your frontend,
it will result in a security breach since the user is able to modify the filters you've set
by modifying the code from the browser.

#### Valid Until

You can set a Unix timestamp used to define the expiration date of the API key

```php
<?php
# generate a public API key that is valid for 1 hour:
$validUntil = time() + 3600;
$public_key = \AlgoliaSearch\Client::generateSecuredApiKey('SearchApiKey', ['validUntil' => $validUntil]);
```

#### Index Restriction

You can restrict the key to a list of index names allowed for the secured API key

```php
<?php
# generate a public API key that is restricted to 'index1' and 'index2':
$public_key = \AlgoliaSearch\Client::generateSecuredApiKey('SearchApiKey', ['restrictIndices' => 'index1,index2']);
```

#### Rate Limiting

If you want to rate limit a secured API Key, the API key you generate the secured api key from need to be rate-limited.
You can do that either via the dashboard or via the API using the
[Add user key](#add-user-key) or [Update user key](#update-user-key) method

##### User Rate Limiting

By default the rate limits will only use the `IP`.

This can be an issue when several of your end users are using the same IP.
To avoid that, you can set a `userToken` query parameter when generating the key.

When set, a unique user will be identified by his `IP + user_token` instead of only by his `IP`.

This allows you to restrict a single user to performing a maximum of `N` API calls per hour,
even if he shares his `IP` with another user.

```php
<?php
// generate a public API key for user 42. Here, records are tagged with:
//  - 'user_XXXX' if they are visible by user XXXX
$public_key = $client->generateSecuredApiKey(
    'YourSearchOnlyApiKey',
    ['filters' => 'user_42', 'userToken' => 'user_42']
);
```

#### Network restriction

For more protection against API key leaking and reuse you can restrict the key to be valid only from specific IPv4 networks

```php
<?php
# generate a public API key that is restricted to '192.168.1.0/24':
$public_key = \AlgoliaSearch\Client::generateSecuredApiKey('SearchApiKey', ['restrictSources' => '192.168.1.0/24']);
```


# Synonyms



## Save synonym - `saveSynonym` 

This method saves a single synonym record into the index.

In this example, we specify true to forward the creation to replica indices.
By default the behavior is to save only on the specified index.

```php
<?php
$index->saveSynonym("a-unique-identifier", array(
  "objectID" => "a-unique-identifier",
  "type" => "synonym",
  "synonyms" => array("car", "vehicle", "auto")
), true);
```

## Batch synonyms - `batchSynonyms` 

Use the batch method to create a large number of synonyms at once,
forward them to replica indices if desired,
and optionally replace all existing synonyms
on the index with the content of the batch using the replaceExistingSynonyms parameter.

You should always use replaceExistingSynonyms to atomically replace all synonyms
on a production index. This is the only way to ensure the index always
has a full list of synonyms to use during the indexing of the new list.

```php
<?php
// Batch synonyms, with replica forwarding and atomic replacement of existing synonyms
$index->batchSynonyms(array(array(
  "objectID" => "a-unique-identifier",
  "type" => "synonym",
  "synonyms" => array("car", "vehicle", "auto")
), array(
  "objectID" => "another-unique-identifier",
  "type" => "synonym",
  "synonyms" => array("street", "st")
)), true, true);
```

## Editing Synonyms

Updating the value of a specific synonym record is the same as creating one.
Make sure you specify the same objectID used to create the record and the synonyms
will be updated.
When updating multiple synonyms in a batch call (but not all synonyms),
make sure you set replaceExistingSynonyms to false (or leave it out,
false is the default value).
Otherwise, the entire synonym list will be replaced only partially with the records
in the batch update.

## Delete synonym - `deleteSynonym` 

Use the normal index delete method to delete synonyms,
specifying the objectID of the synonym record you want to delete.
Forward the deletion to replica indices by setting the forwardToReplicas parameter to true.

```php
<?php
// Delete and forward to replicas
$index->deleteSynonym("a-unique-identifier", true);
```

## Clear all synonyms - `clearSynonyms` 

This is a convenience method to delete all synonyms at once.
It should not be used on a production index to then push a new list of synonyms:
there would be a short period of time during which the index would have no synonyms
at all.

To atomically replace all synonyms of an index,
use the batch method with the replaceExistingSynonyms parameter set to true.

```php
<?php
// Clear synonyms and forward to replicas
$index->clearSynonyms(true);
```

## Get synonym - `getSynonym` 

Search for synonym records by their objectID or by the text they contain.
Both methods are covered here.

```php
<?php
$synonym = $index->getSynonym("a-unique-identifier");
```

## Search synonyms - `searchSynonyms` 

Search for synonym records similar to how you’d search normally.

Accepted search parameters:
- query: the actual search query to find synonyms. Use an empty query to browse all the synonyms of an index.
- type: restrict the search to a specific type of synonym. Use an empty string to search all types (default behavior). Multiple types can be specified using a comma-separated list or an array.
- page: the page to fetch when browsing through several pages of results. This value is zero-based.
hitsPerPage: the number of synonyms to return for each call. The default value is 100.

```php
<?php
// Searching for "street" in synonyms and one-way synonyms; fetch the second page with 10 hits per page
$results = $index->searchSynonyms("street", array("synonym", "oneWaySynonym"), 1, 10);
```


# Advanced



## Custom batch - `batch` 

You may want to perform multiple operations with one API call to reduce latency.

Custom batch:

```php
<?php
$res = $index->batch(
    [
        'requests' => [
            [
                'action' => 'addObject',
                'body'   => ['firstname' => 'Jimmie', 'lastname' => 'Barninger']
            ],
            [
                'action' => 'addObject',
                'body'   => ['Warren' => 'Jimmie', 'lastname' => 'Speach']
            ],
            [
                'action'   => 'updateObject',
                'objectID' => 'myID3',
                'body'     => ['firstname' => 'Rob']
            ],
            [
                'action'   => 'deleteObject',
                'objectID' => 'myID4'
            ]
        ]
    ]
);
```

If you have one index per user, you may want to perform a batch operations across several indices.
We expose a method to perform this type of batch:

```php
<?php
$res = $client->batch(
    [
        [
            'action'    => 'addObject',
            'indexName' => 'index1',
            [
                'firstname' => 'Jimmie',
                'lastname'  => 'Barninger'
            ]
        ],
        [
            'action'    => 'addObject',
            'indexName' => 'index1',
            [
                'firstname' => 'Warren',
                'lastname'  => 'myID1'
            ]
        ]
    ]
);
```

The attribute **action** can have these values:

- addObject
- updateObject
- partialUpdateObject
- partialUpdateObjectNoCreate
- deleteObject

## Backup / Export an index - `browse` 

The `search` method cannot return more than 1,000 results. If you need to
retrieve all the content of your index (for backup, SEO purposes or for running
a script on it), you should use the `browse` method instead. This method lets
you retrieve objects beyond the 1,000 limit.

This method is optimized for speed. To make it fast, distinct, typo-tolerance,
word proximity, geo distance and number of matched words are disabled. Results
are still returned ranked by attributes and custom ranking.

#### Response Format

##### Sample

```json
{
  "hits": [
    {
      "firstname": "Jimmie",
      "lastname": "Barninger",
      "objectID": "433"
    }
  ],
  "processingTimeMS": 7,
  "query": "",
  "params": "filters=level%3D20",
  "cursor": "ARJmaWx0ZXJzPWxldmVsJTNEMjABARoGODA4OTIzvwgAgICAgICAgICAAQ=="
}
```

##### Fields

- `cursor` (string, optional): A cursor to retrieve the next chunk of data. If absent, it means that the end of the index has been reached.
- `query` (string): Query text used to filter the results.
- `params` (string, URL-encoded): Search parameters used to filter the results.
- `processingTimeMS` (integer): Time that the server took to process the request, in milliseconds. *Note: This does not include network time.*

The following fields are provided for convenience purposes, and **only when the browse is not filtered**:

- `nbHits` (integer): Number of objects in the index.
- `page` (integer): Index of the current page (zero-based).
- `hitsPerPage` (integer): Maximum number of hits returned per page.
- `nbPages` (integer): Number of pages corresponding to the number of hits. Basically, `ceil(nbHits / hitsPerPage)`.

#### Example

```php
<?php
// Iterate with a filter over the whole index
foreach ($this->index->browse('', ['filters' => 'i<42']) as $hit) {
    print_r($hit);
}

// Retrieve the next cursor from the browse method
$result = $this->index->browseFrom('', ['filters' => 'i<42']);
var_dump($result['cursor']);
```

## List api keys - `listApiKeys` 

To list existing keys, you can use:

```php
<?php
// Lists global API Keys
$client->listUserKeys();

// Lists API Keys that can access only to this index
$index->listUserKeys();
```

Each key is defined by a set of permissions that specify the authorized actions. The different permissions are:

* **search**: Allowed to search.
* **browse**: Allowed to retrieve all index contents via the browse API.
* **addObject**: Allowed to add/update an object in the index.
* **deleteObject**: Allowed to delete an existing object.
* **deleteIndex**: Allowed to delete index content.
* **settings**: allows to get index settings.
* **editSettings**: Allowed to change index settings.
* **analytics**: Allowed to retrieve analytics through the analytics API.
* **listIndexes**: Allowed to list all accessible indexes.

## Add user key - `addUserKey` 

To create API keys:

```php
<?php
// Creates a new global API key that can only perform search actions
$res = $client->addUserKey(['search']);
echo 'key=' . $res['key'] . "\n";

// Creates a new API key that can only perform search action on this index
$res = $index->addUserKey(['search']);
echo 'key=' . $res['key'] . "\n";
```

You can also create an API Key with advanced settings:

##### validity

Add a validity period. The key will be valid for a specific period of time (in seconds).

##### maxQueriesPerIPPerHour

Specify the maximum number of API calls allowed from an IP address per hour. Each time an API call is performed with this key, a check is performed. If the IP at the source of the call did more than this number of calls in the last hour, a 403 code is returned. Defaults to 0 (no rate limit). This parameter can be used to protect you from attempts at retrieving your entire index contents by massively querying the index.

  

Note: If you are sending the query through your servers, you must use the `enableRateLimitForward("TheAdminAPIKey", "EndUserIP", "APIKeyWithRateLimit")` function to enable rate-limit.

##### maxHitsPerQuery

Specify the maximum number of hits this API key can retrieve in one call. Defaults to 0 (unlimited). This parameter can be used to protect you from attempts at retrieving your entire index contents by massively querying the index.

##### indexes

Specify the list of targeted indices. You can target all indices starting with a prefix or ending with a suffix using the '\*' character. For example, "dev\_\*" matches all indices starting with "dev\_" and "\*\_dev" matches all indices ending with "\_dev". Defaults to all indices if empty or blank.

##### referers

Specify the list of referers. You can target all referers starting with a prefix, ending with a suffix using the '\*' character. For example, "https://algolia.com/\*" matches all referers starting with "https://algolia.com/" and "\*.algolia.com" matches all referers ending with ".algolia.com". If you want to allow the domain algolia.com you can use "\*algolia.com/\*". Defaults to all referers if empty or blank.

##### queryParameters

Specify the list of query parameters. You can force the query parameters for a query using the url string format (param1=X&param2=Y...).

##### description

Specify a description to describe where the key is used.

```php
<?php
// Creates a new index specific API key valid for 300 seconds, with a rate limit of 100 calls per hour per IP and a maximum of 20 hits

$params = [
    'validity'               => 300,
    'maxQueriesPerIPPerHour' => 100,
    'maxHitsPerQuery'        => 20,
    'indexes'                => ['dev_*'],
    'referers'               => ['algolia.com/*'],
    'queryParameters'        => 'typoTolerance=strict&ignorePlurals=false',
    'description'            => 'Limited search only API key for algolia.com'
];

$res = $client->addUserKey(params);
echo 'key=' . $res['key'] . "\n";
```

## Update user key - `updateUserKey` 

To update the permissions of an existing key:

```php
<?php
// Update an existing global API key that is valid for 300 seconds
$res = $client->updateUserKey('myAPIKey', ['search'], 300);
echo 'key=' . $res['key'] . "\n";

// Update an existing index specific API key valid for 300 seconds, with a rate limit of 100 calls per hour per IP and a maximum of 20 hits
$res = $index->updateUserKey('myAPIKey', ['search'], 300, 100, 20);
echo 'key=' . $res['key'] . "\n";
```

To get the permissions of a given key:

```php
<?php
// Gets the rights of a global key
$res = $client->getUserKeyACL('f420238212c54dcfad07ea0aa6d5c45f');

// Gets the rights of an index specific key
$res = $index->getUserKeyACL('71671c38001bf3ac857bc82052485107');
```

## Delete user key - `deleteUserKey` 

To delete an existing key:

```php
<?php
// Deletes a global key
$res = $client->deleteUserKey('f420238212c54dcfad07ea0aa6d5c45f');

// Deletes an index specific key
$res = $index->deleteUserKey('71671c38001bf3ac857bc82052485107');
```

## Get key permissions - `getUserKeyACL` 

To get the permissions of a given key:

```php
<?php
// Gets the rights of a global key
$res = $client->getUserKeyACL('f420238212c54dcfad07ea0aa6d5c45f');

// Gets the rights of an index specific key
$res = $index->getUserKeyACL('71671c38001bf3ac857bc82052485107');
```

## Get latest logs - `getLogs` 

You can retrieve the latest logs via this API. Each log entry contains:

* Timestamp in ISO-8601 format
* Client IP
* Request Headers (API Key is obfuscated)
* Request URL
* Request method
* Request body
* Answer HTTP code
* Answer body
* SHA1 ID of entry

You can retrieve the logs of your last 1,000 API calls and browse them using the offset/length parameters:

#### offset

Specify the first entry to retrieve (0-based, 0 is the most recent log entry). Defaults to 0.

#### length

Specify the maximum number of entries to retrieve starting at the offset. Defaults to 10. Maximum allowed value: 1,000.

#### onlyErrors

Retrieve only logs with an HTTP code different than 200 or 201. (deprecated)

#### type

Specify the type of logs to retrieve:

* `query`: Retrieve only the queries.
* `build`: Retrieve only the build operations.
* `error`: Retrieve only the errors (same as `onlyErrors` parameters).

```php
<?php
// Get last 10 log entries
$res = $client->getLogs();

// Get last 100 log entries
$res = $client->getLogs(0, 100);
```

## REST API

We've developed API clients for the most common programming languages and platforms.
These clients are advanced wrappers on top of our REST API itself and have been made
in order to help you integrating the service within your apps:
for both indexing and search.

Everything that can be done using the REST API can be done using those clients.

The REST API lets your interact directly with Algolia platforms from anything that can send an HTTP request
[Go to the REST API doc](https://algolia.com/doc/rest)


