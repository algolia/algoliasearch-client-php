Algolia Search API Client for PHP
==================

This PHP client let you easily use the Algolia Search API from your backend.
The service is currently in Beta, you can request an invite on our [website](http://www.algolia.com/pricing/).

Table of Content
-------------
**Get started**

1. [Setup](#setup) 
1. [Quick Start](#quick-start)

**Commands reference**

1. [Search](#search)
1. [Add a new object](#add-a-new-object-in-the-index)
1. [Update an object](#update-an-existing-object-in-the-index)
1. [Get an object](#get-an-object)
1. [Delete an object](#delete-an-object)
1. [Index settings](#index-settings)
1. [List indexes](#list-indexes)
1. [Delete an index](#delete-an-index)
1. [Wait indexing](#wait-indexing)
1. [Batch writes](#batch-writes)
1. [Security / User API Keys](#security--user-api-keys)
1. [Copy or rename an index](#copy-or-rename-an-index)
1. [Logs](#logs)

Setup
-------------
To setup your project, follow these steps:

 1. Download and add the [algoliasearch.php](https://github.com/algolia/algoliasearch-client-php/archive/master.zip) file to your project
 2. Add the `require` call to your project
 3. Initialize the client with your ApplicationID and API-Key. You can find all of them on [your Algolia account](http://www.algolia.com/users/edit).

```php
require 'algoliasearch.php';
$client = new \AlgoliaSearch\Client('YourApplicationID', 'YourAPIKey');
```


Quick Start
-------------
This quick start is a 30 seconds tutorial where you can discover how to index and search objects.

Without any prior-configuration, you can index [500 contacts](https://github.com/algolia/algoliasearch-client-php/blob/master/contacts.json) in the ```contacts``` index with the following code:
```php
$index = $client->initIndex("contacts");
$batch = json_decode(file_get_contents("contacts.json"), true);
$index->addObjects($batch);
```

You can then start to search for a contact firstname, lastname, company, ... (even with typos):
```php
// search by firstname
var_dump($index->search('jimmie'));
// search a firstname with typo
var_dump($index->search('jimie'));
// search for a company
var_dump($index->search('california paint'));
// search for a firstname & company
var_dump($index->search('jimmie paint'));
```

Settings can be customized to tune the search behavior. For example you can add a custom sort by number of followers to the already good out-of-the-box relevance:
```php
$index->setSettings(array("customRanking" => array("desc(followers)")));
```
You can also configure the list of attributes you want to index by order of importance (first = most important):
```php
$index->setSettings(array("attributesToIndex" => array("lastname", "firstname", "company",
                                                       "email", "city", "address")));
```

Since the engine is designed to suggest results as you type, you'll generally search by prefix. In this case the order of attributes is very important to decide which hit is the best:
```ruby
var_dump($index->search('or'));
var_dump($index->search('jim'));
```

Search
-------------
> **Opening note:** If you are building a web application, you may be more interested in using our [javascript client](https://github.com/algolia/algoliasearch-client-js) to send queries. It brings two benefits: (i) your users get a better response time by avoiding to go threw your servers, and (ii) it will offload your servers of unnecessary tasks.

To perform a search, you just need to initialize the index and perform a call to the search function.<br/>
You can use the following optional arguments:

 * **attributes**: a string that contains the names of attributes to retrieve separated by a comma.<br/>By default all attributes are retrieved.
 * **numerics**: specify the list of numeric filters you want to apply separated by a comma. The syntax of one filter is `attributeName` followed by `operand` followed by `value`. Supported operands are `<`, `<=`, `=`, `>` and `>=`. 
 You can have multiple conditions on one attribute like for example `numerics=price>100,price<1000`.
 * **attributesToHighlight**: a string that contains the names of attributes to highlight separated by a comma.<br/>By default indexed attributes are highlighted. Numerical attributes cannot be highlighted. A **matchLevel** is returned for each highlighted attribute and can contain: "full" if all the query terms were found in the attribute, "partial" if only some of the query terms were found, or "none" if none of the query terms were found.
 * **attributesToSnippet**: a string that contains the names of attributes to snippet alongside the number of words to return (syntax is 'attributeName:nbWords'). Attributes are separated by a comma (Example: "attributesToSnippet=name:10,content:10").<br/>By default no snippet is computed.
 * **minWordSizeForApprox1**: the minimum number of characters in a query word to accept one typo in this word.<br/>Defaults to 3.
 * **minWordSizeForApprox2**: the minimum number of characters in a query word to accept two typos in this word.<br/>Defaults to 7.
 * **getRankingInfo**: if set to 1, the result hits will contain ranking information in _rankingInfo attribute.
 * **page**: *(pagination parameter)* page to retrieve (zero base).<br/>Defaults to 0.
 * **hitsPerPage**: *(pagination parameter)* number of hits per page.<br/>Defaults to 10.
 * **aroundLatLng**: search for entries around a given latitude/longitude (specified as two floats separated by a comma).<br/>For example `aroundLatLng=47.316669,5.016670`).<br/>You can specify the maximum distance in meters with the **aroundRadius** parameter (in meters) and the precision for ranking with **aroundPrecision** (for example if you set aroundPrecision=100, two objects that are distant of less than 100m will be considered as identical for "geo" ranking parameter).<br/>At indexing, you should specify geoloc of an object with the _geoloc attribute (in the form `{"_geoloc":{"lat":48.853409, "lng":2.348800}}`)
 * **insideBoundingBox**: search entries inside a given area defined by the two extreme points of a rectangle (defined by 4 floats: p1Lat,p1Lng,p2Lat,p2Lng).<br/>For example `insideBoundingBox=47.3165,4.9665,47.3424,5.0201`).<br/>At indexing, you should specify geoloc of an object with the _geoloc attribute (in the form `{"_geoloc":{"lat":48.853409, "lng":2.348800}}`)
 * **queryType**: select how the query words are interpreted:
  * **prefixAll**: all query words are interpreted as prefixes (default behavior).
  * **prefixLast**: only the last word is interpreted as a prefix. This option is recommended if you have a lot of content to speedup the processing.
  * **prefixNone**: no query word is interpreted as a prefix. This option is not recommended.
 * **tags**: filter the query by a set of tags. You can AND tags by separating them by commas. To OR tags, you must add parentheses. For example, `tags=tag1,(tag2,tag3)` means *tag1 AND (tag2 OR tag3)*.<br/>At indexing, tags should be added in the _tags attribute of objects (for example `{"_tags":["tag1","tag2"]}` )

```php
$index = $client->initIndex("contacts");
$res = $index->search("query string");
$res = $index->search("query string", array("attributes" => "fistname,lastname", "hitsPerPage" => 50)));
```

The server response will look like:

```javascript
{
  "hits": [
    {
      "firstname": "Jimmie",
      "lastname": "Barninger",
      "company": "California Paint & Wlpaper Str",
      "address": "Box #-4038",
      "city": "Modesto",
      "county": "Stanislaus",
      "state": "CA",
      "zip": "95352",
      "phone": "209-525-7568",
      "fax": "209-525-4389",
      "email": "jimmie@barninger.com",
      "web": "http://www.jimmiebarninger.com",
      "followers": 3947,
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
        },
        "address": {
          "value": "Box #-4038",
          "matchLevel": "none"
        },
        "city": {
          "value": "Modesto",
          "matchLevel": "none"
        },
        "email": {
          "value": "<em>jimmie</em>@barninger.com",
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
  "params": "query=jimmie+paint&"
}
```

Add a new object in the Index
-------------

Each entry in an index has a unique identifier called `objectID`. You have two ways to add en entry in the index:

 1. Using automatic `objectID` assignement, you will be able to retrieve it in the answer.
 2. Passing your own `objectID`

You don't need to explicitely create an index, it will be automatically created the first time you add an object.
Objects are schema less, you don't need any configuration to start indexing. The settings section provide details about advanced settings.

Example with automatic `objectID` assignement:

```php
$res = $index->addObject(array("firstname" => "Jimmie", 
                               "lastname" => "Barninger"));
echo "objectID=" . $res['objectID'] . "\n";
```

Example with manual `objectID` assignement:

```php
$res = $index->addObject(array("firstname" => "Jimmie", 
                               "lastname" => "Barninger"), "myID");
echo "objectID=" . $res['objectID'] . "\n";
```

Update an existing object in the Index
-------------

You have two options to update an existing object:

 1. Replace all its attributes.
 2. Replace only some attributes.

Example to replace all the content of an existing object:

```php
$index->saveObject(array("firstname" => "Jimmie", 
                         "lastname" => "Barninger",
                         "city" => "New York",
                         "objectID" => "myID"));
```

Example to update only the city attribute of an existing object:

```php
$index->partialUpdateObject(array("city" => "San Francisco",
                                  "objectID" => "myID"));
```

Get an object
-------------

You can easily retrieve an object using its `objectID` and optionnaly a list of attributes you want to retrieve (using comma as separator):

```php
// Retrieves all attributes
$index->getObject("myID");
// Retrieves firstname and lastname attributes
$index->getObject("myID", "firstname,lastname");
// Retrieves only the firstname attribute
$index->getObject("myID", "firstname");
```

Delete an object
-------------

You can delete an object using its `objectID`:

```php
$index->deleteObject("myID");
```

Index Settings
-------------

You can retrieve all settings using the `getSettings` function. The result will contains the following attributes:

 * **minWordSizeForApprox1**: (integer) the minimum number of characters to accept one typo (default = 3).
 * **minWordSizeForApprox2**: (integer) the minimum number of characters to accept two typos (default = 7).
 * **hitsPerPage**: (integer) the number of hits per page (default = 10).
 * **attributesToRetrieve**: (array of strings) default list of attributes to retrieve in objects.
 * **attributesToHighlight**: (array of strings) default list of attributes to highlight.
 * **attributesToSnippet**: (array of strings) default list of attributes to snippet alongside the number of words to return (syntax is 'attributeName:nbWords')<br/>By default no snippet is computed.
 * **attributesToIndex**: (array of strings) the list of fields you want to index.<br/>By default all textual and numerical attributes of your objects are indexed, but you should update it to get optimal results.<br/>This parameter has two important uses:
  * *Limits the attributes to index*.<br/>For example if you store a binary image in base64, you want to store it and be able to retrieve it but you don't want to search in the base64 string.
  * *Controls part of the ranking*.<br/>Matches in attributes at the beginning of the list will be considered more important than matches in attributes further down the list. 
 * **ranking**: (array of strings) controls the way hits are sorted.<br/>We have six available criteria:
  * **typo**: sort according to number of typos,
  * **geo**: sort according to decreasing distance when performing a geo-location based search,
  * **proximity**: sort according to the proximity of query words in hits, 
  * **attribute**: sort according to the order of attributes defined by **attributesToIndex**,
  * **exact**: sort according to the number of words that are matched identical to query word (and not as a prefix),
  * **custom**: sort according to a user defined formula set in **customRanking** attribute.
  <br/>The default order is `["typo", "geo", "proximity", "attribute", "exact", "custom"]`. We strongly recommend to keep this configuration.
 * **customRanking**: (array of strings) lets you specify part of the ranking.<br/>The syntax of this condition is an array of strings containing attributes prefixed by asc (ascending order) or desc (descending order) operator.
 For example `"customRanking" => ["desc(population)", "asc(name)"]`
 * **queryType**: select how the query words are interpreted:
  * **prefixAll**: all query words are interpreted as prefixes (default behavior).
  * **prefixLast**: only the last word is interpreted as a prefix. This option is recommended if you have a lot of content to speedup the processing.
  * **prefixNone**: no query word is interpreted as a prefix. This option is not recommended.

You can easily retrieve settings or update them:

```php
$settings = $index->getSettings();
var_dump($settings);
```

```php
$index->setSettings(array("customRanking" => array("desc(followers)")));
```

List indexes
-------------
You can list all your indexes with their associated information (number of entries, disk size, etc.) with the `listIndexes` method:

```php
var_dump($client->listIndexes());
```

Delete an index
-------------
You can delete an index using its name:

```php
$client->deleteIndex("contacts");
```

Wait indexing
-------------

All write operations return a `taskID` when the job is securely stored on our infrastructure but not when the job is published in your index. Even if it's extremely fast, you can easily ensure indexing is complete using the `waitTask` method on the `taskID` returned by a write operation.

For example, to wait for indexing of a new object:
```php
$res = $index->addObject(array("firstname" => "Jimmie", 
                               "lastname" => "Barninger"));
$index->waitTask($res['taskID']);
```

If you want to ensure multiple objects have been indexed, you can only check the biggest taskID.

Batch writes
-------------

You may want to perform multiple operations with one API call to reduce latency.
We expose two methods to perform batch:
 * `addObjects`: add an array of object using automatic `objectID` assignement
 * `saveObjects`: add or update an array of object that contains an `objectID` attribute

Example using automatic `objectID` assignement:
```php
$res = $index->addObjects(array(array("firstname" => "Jimmie", 
                                      "lastname" => "Barninger"),
                                array("firstname" => "Warren", 
                                      "lastname" => "myID1")));
```

Example with user defined `objectID` (add or update):
```php
$res = $index->saveObjects(array(array("firstname" => "Jimmie", 
                                       "lastname" => "Barninger",
                                       "objectID" => "SFO"),
                                 array("firstname" => "Warren", 
                                       "lastname" => "Speach",
                                       "objectID" => "myID2")));
```

Security / User API Keys
-------------

The admin API key provides full control of all your indexes. 
You can also generate user API keys to control security. 
These API keys can be restricted to a set of operations or/and restricted to a given index.

To list existing keys, you can use `listUserKeys` method:
```php
// Lists global API Keys
$client->listUserKeys();
// Lists API Keys that can access only to this index
$index->listUserKeys();
```

Each key is defined by a set of rights that specify the authorized actions. The different rights are:
 * **search**: allows to search,
 * **addObject**: allows to add/update an object in the index,
 * **deleteObject**: allows to delete an existing object,
 * **deleteIndex**: allows to delete index content,
 * **settings**: allows to get index settings,
 * **editSettings**: allows to change index settings.

Example of API Key creation:
```php
// Creates a new global API key that can only perform search actions
$res = $client->addUserKey(array("search"));
echo "key=" . $res['key'] . "\n";
// Creates a new API key that can only perform search action on this index
$res = $index->addUserKey(array("search"));
echo "key=" . $res['key'] . "\n";
```
You can also create a temporary API key that will be valid only for a specific period of time (in seconds):
```php
// Creates a new global API key that is valid for 300 seconds
$res = $client->addUserKey(array("search"), 300);
echo "key=" . $res['key'] . "\n";
// Creates a new index specific API key valid for 300 seconds
$res = $index->addUserKey(array("search"), 300);
echo "key=" . $res['key'] . "\n";
```

Get the rights of a given key:
```php
// Gets the rights of a global key
$res = $client->getUserKeyACL("f420238212c54dcfad07ea0aa6d5c45f");
// Gets the rights of an index specific key
$res = $index->getUserKeyACL("71671c38001bf3ac857bc82052485107");
```

Delete an existing key:
```php
// Deletes a global key
$res = $client->deleteUserKey("f420238212c54dcfad07ea0aa6d5c45f");
// Deletes an index specific key
$res = $index->deleteUserKey("71671c38001bf3ac857bc82052485107");
```


Copy or rename an index
-------------

You can easily copy or rename an existing index using the `copy` and `move` command. 
**Note**: Move and copy commands overwrite destination index.

The move command is particulary usefull is you want to update a big index atomically from one version to another. For example, if you recreate your index `MyIndex`each night from a database by batch, you have just to :
 1. Import your database in a new index using [batches](#batch-writes). We will call this new index `MyNewIndex`
 1. Rename `MyNewIndex` in `MyIndex` using the move command. This will automatically override the old index and new queries will be served on this index.

```php
// Rename MyNewIndex in MyIndex
$res = $client->moveIndex("MyNewIndex", "MyIndex");
// Copy MyNewIndex in MyIndex
$res = $client->copyIndex("MyNewIndex", "MyIndex");
```

Logs
-------------

You can retrieve last logs via this API. Each log entry contains: 
 * Timestamp in ISO-8601 format
 * Client IP
 * Request Headers (API-Key is obfuscated)
 * Request URL
 * Request method
 * Request body
 * Answer HTTP code
 * Answer body
 * SHA1 ID of entry

You can retrieve log of your last 1000 API calls and you can navigate into them using the offset/length parameter:
 * ***offset***: Specify the first entry to retrieve (0-based, 0 is the most recent log entry). Default to 0.
 * ***length***: Specify the maximum number of entries to retrieve starting at offset. Default to 10. Maximum allowed value: 1000.

```objc
// Get last 10 log entries
$res = $client->getLogs();
// Get last 100 log entries
$res = $client->getLogs(0, 100);
```