## Change Log

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
