Algolia Search API Client for PHP
==================

This PHP client let you easily use the Algolia Search API on your server.

Setup
-------------
To setup your project, follow these steps:

 1. Download and add the `algoliasearch.php` file to your project
 2. Add the `require` call to your project
 3. Initialize the client with your ApplicationID, API-Key and list of hostnames (you can find all of them on your Algolia account)

<pre><code>
  require 'algoliasearch.php';
  $client = new \AlgoliaSearch\Client('<YourApplicationID>', '<YourAPIKey>', array("host1", "host2", "host3"));
</code></pre>

General Principle
-------------

All API calls will return an object <em>Answer</em> that will expose three methods

 1. hasError() that returns true if an error occured
 2. errorMsg() that describes the error
 3. getContent() that return the deserialized json object of API when there is no error

Search 
-------------
To perform a search, you have just to initialize the index and perform a call to search.<br/>
You can optionally use the following arguments :

 * **attributes**: a string that contains attribute names to retrieve separated by a comma.<br/>By default all attributes are retrieved.
 * **attributesToHighlight**: a string that contains attribute names to highlight separated by a comma.<br/>By default all textual attributes are highlighted.
 * **minWordSizeForApprox1**: the minimum number of characters in a query word to accept one typo in this word.<br/>Defaults to 3.
 * **minWordSizeForApprox2**: the minimum number of characters in a query word to accept two typos in this word.<br/>Defaults to 7.
 * **getRankingInfo**: if set to 1, the result hits will contain ranking information in _rankingInfo attribute.
 * **page**: *(pagination parameter)* page to retrieve (zero base).<br/>Defaults to 0.
 * **hitsPerPage**: *(pagination parameter)* number of hits per page.<br/>Defaults to 10.
 * **aroundLatLng**: search for entries around a given latitude/longitude (specified as two floats separated by a comma).<br/>For example `aroundLatLng=47.316669,5.016670`).<br/>You can specify the maximum distance in meters with **aroundRadius parameter** (in meters).<br/>At indexing, you should specify geoloc of an object with _geoloc attribute (in the form `{"_geoloc":{"lat":48.853409, "lng":2.348800}}`)
 * **insideBoundingBox**: search entries inside a given area defined by the two extreme points of a rectangle (defined by 4 floats: p1Lat,p1Lng,p2Lat, p2Lng).<br/>For example `insideBoundingBox=47.3165,4.9665,47.3424,5.0201`).<br/>At indexing, you should specify geoloc of an object with _geoloc attribute (in the form `{"_geoloc":{"lat":48.853409, "lng":2.348800}}`)
 * **tags**: filter the query by a set of tags (contains a list of tags separated by a comma).<br/>At indexing, tags should be added in _tags attribute of objects (for example `{"_tags":["tag1","tag2"]}` )

<pre><code>
$index = $client->initIndex("MyIndexName");
$res = $index->search("query string");
$res = $index->search("query string", array("attributes" => "population,name", "hitsPerPage" => 50)));
</code></pre>
