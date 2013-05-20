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

Search object
-------------

