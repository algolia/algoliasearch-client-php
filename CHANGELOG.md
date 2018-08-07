## Change Log

## 2.0

<To Be Written>

#### 2.0 Alpha 1

* Add multiple indexes search/index methods
* Add common files (licence, changelog...)
* **Rename `index` to `initIndex` for easier upgrade**
* Fix default HttpClient wrapper for PHP53
* Setup Travis

#### 2.0 Alpha 2

* Add getter and setter for `Index::indexName` property
* Fix Traviss


### UNRELEASED

<Contributors, please add your changes below this line>


### 1.27.0

* Introduce AB Testing feature - PR [#408](https://github.com/algolia/algoliasearch-client-php/pull/#408)
    List/Create/Stop/Delete AB Tests programmatically
    Introduce new Analytics object, wrapper around the
    [Analytics API](https://www.algolia.com/doc/rest-api/analytics/) (more methods to come).

* 2 methods about taskID initially available in the `Index` moved to the `Client`. 
    You could get some taskID from the engine without necessarily have an instance of Index, 
    instead of instanciating an index that you won't need, you can now call waitTask and getTaskStatus on the client.
    The original methods on the index still work are **not** deprecated.
 
     ```php
     $client->waitTask($indexName, $taskID)
     $client->getTaskStatus($indexName, $taskID)
    ```
    
### 1.26.1

* Fix PHP warning when using `searchDisjunctiveFaceting` - See [#407](https://github.com/algolia/algoliasearch-client-php/pull/407)

### 1.26.0

🎉 Note to contributors:
Everybody is now able to run the test on Travis, since we moved to temporary credentials.️ ⤵️
https://blog.algolia.com/travis-encrypted-variables-external-contributions/

- Fix: `addApiKey` was fixed in 1.25.0 (see changelog entry below). The same fix was ported to `updateApiKey`.

- Fix: Curl was added to the composer requirements. If you get an error because curl is not enabled in CLI, enable it or use the flag `--ignore-platform-reqs`

- Fix: Adding a rule with an empty ID failed silently, it will now throw an exception

- Deprecation: Keys should not be managed at the Index level but at the Client level

    All methods `Index::(list|get|add|update)ApiKeys()` are now
    deprecated. If you already have keys on the Index, it would be best
    to delete them and regenerate new keys with client, adding the `indexes`
    restriction.

    Example:
    ```php
    $client->addApiKey([
        'acl' => 'search',
        'indexes' => 'my_index_name',
    ])
    ```
- Fix: Add $requestHeaders arg to Index::browse and Index::deleteBy

- Fix: When browsing, ensure cursor is passed in the body
    Cursor can become so long that the generated URL fails (error HTTP 414).

- Chore: Add PHP version to the UserAgent

### 1.25.1

- feat(places): Set write hosts when using Places

Even though Algolia Places indices are read-only, we still need to take into
account the write hosts to let the user generate its own API keys.

### 1.25.0

- feat: Let you define all API keys capabilities in one array

    Example:
    ```php
    $client->addApiKey([
        'acl' => [
            'search',
            'listIndexes',
        ],
        'validity' => $validity,
        'maxQueriesPerIPPerHour' => 1000,
        'maxHitsPerQuery' => 50,
        'indexes' => ['prefix_*'],
    ]);
    ```
    instead of
    ```php
    $client->addApiKey(['search', 'listIndexes'], $validity, 1000, 50, ['prefix_*']);
    ```

### 1.24.0

- feat: Introduce ScopedCopyIndex method, to copy settings, synonyms or query rules between indices

### 1.23.1

- fix: remove all requestHeaders params from method signatures as it breaks backward compatibility. Features added in 1.23.0 still work the same.

### 1.23.0

- feat: add a `requestHeaders` parameter to every method to allow passing custom HTTP headers on a per request basis
- feat: add multi cluster management endpoints


### 1.22.0

- feat: Introduce SynonymIterator to easily export all synonyms
- feat: Introduce RuleIterator to easily export all query rules

### 1.21.0

- feat: exclude disjunctive queries from analytics
- fix: autoload Tests namespace only in dev
- fix: remove usage of deprecated API key methods

### 1.20.0

- feat(places): add `getObject` support

### 1.19.0

- feat(query-rules): add query rules support
- feat(delete-by): add `delete by` support
- feat: add a dedicated `AlgoliaConnectionException`


### 1.18.0

- make API credentials optional for Client::initPlaces()
- make API credentials optional if places enabled in ClientContext class
- raise exception when unknown method is called on Index class

### 1.17.0

- add a strategy to keep state between PHP requests

### 1.16.0

- rename userKey to apiKey in the different keys related methods

### 1.15.0

- Make sure we don't have duplicated user agent
- Allow more than one user agent prefix
- Allow to specify attributesToRetrieve in getObjects method

### 1.14.0
- Better disjunctive faceting (included in the search method)

### 1.13.0
- rename searchFacet to searchForFacetValues
- better checks for curl handles

### 1.12.1
- Fix UA version number

### 1.12.0
- Improved retry strategy (#187)

### 1.11.0
- Add searchFacet feature

### 1.10.3
 - Renamed `slaves` to `replicas` (#159)
 - Renamed `attributesToIndex` to `searchableAttributes` (#159)
 - Fixed `$objectID` parameter type in DOC block (#150)

### 1.10.2
 - Fix of passing `$strategy` parameter to API in `Client::multipleQueries`
 - Follow new User-Agent header convention

### 1.10.1
 - Fix `Index::saveSynonym`

### 1.10.0
 - Add forwardToSlaves option in setSettings

### 1.9.4
 - All JSON operations are now validated by new `Json` class

### 1.9.3
 - Made `Index::deleteByQuery` return the number of delete operations triggered

### 1.9.2
 - Restored PHP 5.3 support

### 1.9.1
 - Fix `Index::saveObject` method signature
 - Made the AccessTests more reliable

### 1.9.0
 - Made the SecurityTests more reliable
 - Added support for Synonyms API endpoints
 - Randomized the order of fallback hosts per ClientContext instance
 - Fixed some minor typos in the README.md and converted the arrays to short array syntax

### 1.8.2
 - Fix custom headers

### 1.8.1
 - Add `setExtraHeader` on places index

### 1.8.0
 - Add new secured api key
 - Drop php 5.3 support
 - Project is now following PSR-2 Coding style
 - Add 7.0 support
 - New `snippetEllipsisText` search parameter exposed
 - Adding in multipleQueries request

### 1.6.1
 - fix deleteByQuery (was not using 1000 hits for deletes)
 - force distinct false in deleteByQuery
 - add waitLastCall option in deleteByQuery

### 1.6.0
 - Add new browse methods that supports filtering
 - Add getTaskStatus to retrieve the status of a task without waiting

### 1.5.8
 - Fix bug introduced in the previous release with the nested array arguments in the search

### 1.5.7
 - Replace GET search method by POST search method to avoid the url limit

### 1.5.6
 - Add new methods to add/update api key
 - Add batch method to target multiple indices
 - Add strategy parameter for the multipleQueries
 - Add new method to generate secured api key from query parameters

### 1.5.5
 - Better retry strategy using two different provider (Improve high-availability of the solution, retry is done on algolianet.com)
 - Read operations are performed to APPID-dsn.algolia.net domain first to leverage Distributed Search Network (select the closest location)
 - Improved timeout strategy: increasse timeout after 2 trials & have a different read timeout for search operations

### 1.5.4
 - Added allOptional support for removeWordsIfNoResult

### 1.5.3
 - Added global timeout for a query
 - Fixed missing namespace escaping

### 1.5.2
 - Changed default connect timeout from 1s to 5s (was too agressive and timeout was triggered on some machines)

### 1.5.1
 - Changed default connect timeout from 30s to 1s and add an option to override it

### 1.5.0
 - Nove to .net instead of .io
 - Ability to pass a custom certificate path

### 1.4.1
- Fixed the retry on hosts

### 1.4.0
- Fixed performance issue with curl_multi_select
- Read the API client version from composer.json if possible
- Add setExtraHeader

### 1.3.5
- updated default typoTolerance setting & updated removedWordsIfNoResult documentation
- Added updateACL

### 1.3.4
- Add notes about the JS API client
- Remove automatic affectation of updateObject on batchObjects
- added aroundLatLngViaIP documentation
- Add documentation about removeWordsIfNoResult
- Fixed addUserKey method: it takes an array, not a string

### 1.3.3
- Added check on empty string as index name
- Add tutorial links + minor enhancements

### 1.3.2
- Added documentation of suffix/prefix index name matching in API key (@speedblue)
- Fixed parse error

### 1.3.1
- Added restrictSearchableAttributes

### 1.3.0
- Fix CA path
- Code reorganization

### 1.2.2
- Add deleteByQuery
- searchDisjunctiveFaceting: do not try to retrieve any attributes in the underlying faceting queries
- Added support of createIfNotExists
- Added createIfNotExists

### 1.2.1
- Fixed array syntax to be compliant with PHP 5.3 and HHVM
- Added disableTypoToleranceOn & altCorrections index settings
- Added getObjects method
- Fixed handling of error message

### 1.2.0
- Added analytics,synonyms,enableSynonymsInHighlight query parameters
- Force CURLOPT_NOSIGNAL to ensure curl is not using UNIX signals to detect timeouts, see http://ravidhavlesha.wordpress.com/2012/01/08/curl-timeout-problem-and-solution/
- Add disjunctive faceting helper
- Add typoTolerance & allowsTyposOnNumericTokens query parameters.
- Fix Exception class instantiation.
- Fixed performance bug with multi_exec

### 1.1.9
- New numericFilters documentation
- Wait until task is published using destination index. (@redox)
- First implementation of curl_multi_*

### 1.1.8
- Change sha256 to hmac with sha256
- Add onlyErrors parametter to getLogs
- Add advancedSyntax query parameter documentation
- Improve handling of http errors

### 1.1.7
- Added deleteObjects
- Ability to generate secured API keys + specify list of indexes targeted by user keys
- Add multipleQueries

### 1.1.6
- Fixed a bug in PHP client that was leading to BAD REQUEST
- Removed debug code

### 1.1.5
- Include package version in the user-agent

### 1.1.2
- Fixed typo
- Try fixing travis build
- Improved test suite
- Include version number in the comment header, bump to 1.1.2

### 1.1.1
- Travis integration
- Add badges
- Expose batch request

### 1.1.0
- Minor fixes

### 1.0.0
- Initial import
