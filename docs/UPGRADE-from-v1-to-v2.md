# Upgrading from v1 to v2

Find the complete guide on our documentation:

https://deploy-preview-2021--algolia-doc.netlify.com/doc/api-client/getting-started/upgrade-guide/php/


## Methods signature change

It is recommend to go over your Algolia implementation and check if the method signature has changed

✅ Unchanged
🤞 Changed but similar
🛑 Requires attention

Note that the v1 had a magic `$requestHeaders` which as became part of `$requestOptions`. The argument was magic
so it's not used a lot, it's ignored for clarity purpose.


### Client

|    | v1 | v2 |
|----|----|----|
| ✅ | `isAlive()`         | `isAlive($requestOptions = array())` |
| 🤞 | `getTaskStatus($indexName, $taskID, $requestHeaders = array())`         | `getTask($indexName, $taskId, $requestOptions = array())` |
| 🤞 | `multipleQueries($queries, $indexNameKey = 'indexName', $strategy = 'none')`          | `multipleQueries($queries, $requestOptions = array())` |
| 🤞 | `batch($operations)`          | `multipleBatch($operations, $requestOptions = array())` |
| ✅ | **ADDED**          | `multipleGetObjects($queries, $requestOptions = array())` |
| 🤞 | `waitTask($indexName, $taskID, $timeBeforeRetry = 100, $requestHeaders = array())`          | `waitTask($indexName, $taskId, $requestOptions = array())` |
| 🤞 | `getLogs($offset = 0, $length = 10, $type = 'all')`         | `getLogs($requestOptions = array())` |
| 🤞 | `request($context, $method, $path, $params, $data, $hostsArray, $connectTimeout, $readTimeout)`         | `custom($method, $path, $requestOptions = array(), $hosts = null)` |
| 🛑 | `doRequest($context, $method, $host, $path, $params, $data, $connectTimeout, $readTimeout)`         | **REMOVED** |


##### Index management

|    | v1 | v2 |
|----|----|----|
| 🤞 | `listIndexes()`         | `listIndices($requestOptions = array())` |
| ✅ | `deleteIndex($indexName)`         | `deleteIndex($indexName, $requestOptions = array())` |
| ✅ | `moveIndex($srcIndexName, $dstIndexName)`         | `moveIndex($srcIndexName, $dstIndexName, $requestOptions = array())` |
| ✅ | `copyIndex($srcIndexName, $dstIndexName)`         | `copyIndex($srcIndexName, $dstIndexName, $requestOptions = array())` |
| 🤞 | `scopedCopyIndex($srcIndexName, $dstIndexName, array $scope = array(), array $requestHeaders = array())`          | `copyIndex($srcIndexName, $dstIndexName, ['scope' => $scope] + $requestOptions)` |
| ✅ | **ADDED**         | `clearIndex($indexName, $requestOptions = array())` |

##### MultiCluster

|    | v1 | v2 |
|----|----|----|
| ✅ | `assignUserID($userID, $clusterName)`         | `assignUserId($userId, $clusterName, $requestOptions = array())` |
| ✅ | `removeUserID($userID)`         | `removeUserId($userId, $requestOptions = array())` |
| ✅ | `listClusters()`          | `listClusters($requestOptions = array())` |
| ✅ | `getUserID($userID)`          | `getUserId($userId, $requestOptions = array())` |
| 🤞 | `listUserIDs($page = 0, $hitsPerPage = 20)`         | `listUserIds($requestOptions = array())` |
| ✅ | `getTopUserID()`         | `getTopUserId($requestOptions = array())` |
| 🤞 | `searchUserIDs($query, $clusterName = null, $page = 0, $hitsPerPage = 20)`          | `searchUserIds($query, $requestOptions = array())` |

##### API Keys

|    | v1 | v2 |
|----|----|----|
| ✅ | `listApiKeys()`         | `listApiKeys($requestOptions = array())` |
| ✅ | `getApiKey($key)`         | `getApiKey($key, $requestOptions = array())` |
| ✅ | `deleteApiKey($key)`          | `deleteApiKey($key, $requestOptions = array())` |
| 🤞 | `addApiKey($obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0, $indexes = null)`          | `addApiKey($keyParams, $requestOptions = array())` |
| 🤞 | `updateApiKey($key, $obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0, $indexes = null)`         | `updateApiKey($key, $keyParams, $requestOptions = array())` |

##### Misc

|    | v1 | v2 |
|----|----|----|
| ✅ | `initIndex($indexName)`         | `initIndex($indexName)` |
| 🛑 | `initAnalytics()`         | **REMOVED** use Analytics::create($appId, $apiKey) |
| 🛑 | `generateSecuredApiKey($privateApiKey, $query, $userToken = null)` (static)      | `generateSecuredApiKey($parentApiKey, $restrictions)` |
| 🛑 | `buildQuery($args)` (static)     | **REMOVED** use `Helpers::buildQuery($args)` |

|    | v1 | v2 |
|----|----|----|
| 🛑 | `setExtraHeader($key, $value)`          | **UNKNOWN**  |
| 🛑 | `setConnectTimeout($connectTimeout, $timeout = 30, $searchTimeout = 5)`         | **REMOVED** Use configuration |
| 🛑 | `enableRateLimitForward($adminAPIKey, $endUserIP, $rateLimitAPIKey)`          | **REMOVED** Use `$requestOptions` |
| 🛑 | `setForwardedFor($ip)`          | **REMOVED** Use `$requestOptions` |
| 🛑 | `setAlgoliaUserToken($token)`         | **REMOVED** Use `$requestOptions` |
| 🛑 | `disableRateLimitForward()`         | **REMOVED** |
| 🛑 | `initPlaces($appId = null, $apiKey = null, $hostsArray = null, $options = array())` (static )     | **REMOVED** Place was removed |
| 🛑 | `getContext()`          | **REMOVED** `Context` was removed |


### Index

##### Search

|    | v1 | v2 |
|----|----|----|
| ✅ | `search($query, $searchParameters = null)`      | `search($query, $requestOptions = array())` |
| ✅ | `searchForFacetValues($facetName, $facetQuery, $searchParameters = array())`      | `searchForFacetValues($facetName, $facetQuery, $searchParameters + $requestOptions)`  |
| ✅ | `searchDisjunctiveFaceting($query, $disjunctive_facets, $params = array(), $refinements = array())`     | Coming soon |
| ✅ | `searchFacet($facetName, $facetQuery, $query = array())`      | Use `searchForFacetValues`|


##### objects

|    | v1 | v2 |
|----|----|----|
| ✅ | `batch($operations)`      | `batch($requests, $requestOptions = array())` |
| 🛑 | `batchObjects($objects, $objectIDKey = 'objectID', $objectActionKey = 'objectAction')`      | **REMOVED** Use `batch` |
| 🛑 | `addObject($content, $objectID = null)`     | **REMOVED** Use saveObject |
| 🛑 | `addObjects($objects, $objectIDKeyLegacy = 'objectID')`     | **REMOVED** Use saveObjects |
| 🤞 | `getObject($objectID, $attributesToRetrieve = null)`      | `getObject($objectId, $requestOptions = array())` |
| 🤞 | `getObjects($objectIDs, $attributesToRetrieve = '')`      | `getObjects($objectIds, $requestOptions = array())` |
| 🛑 | `partialUpdateObject($partialObject, $createIfNotExists = true)`      | `partialUpdateObject($object, $requestOptions = array())` and `partialUpdateOrCreateObject($object, $requestOptions = array())` |
| 🛑 | `partialUpdateObjects($objects, $createIfNotExistsOrObjectIDKeyLegacy = 'objectID', $createIfNotExistsLegacy = true)`     | `partialUpdateObjects($object, $requestOptions = array())` and `partialUpdateOrCreateObjects($object, $requestOptions = array())` |
| ✅ | `saveObject($object, $objectIDKeyLegacy = 'objectID')`      | `saveObject($object, $requestOptions = array())` |
| ✅ | `saveObjects($objects, $objectIDKeyLegacy = 'objectID')`      | `saveObjects($objects, $requestOptions = array())` |
| ✅ | `deleteObject($objectID)`     | `deleteObject($objectId, $requestOptions = array())` |
| ✅ | `deleteObjects($objects)`     | `deleteObject($objectId, $requestOptions = array())` |
| ✅ | `deleteBy(array $filterParameters)`     | `deleteBy(array $args, $requestOptions = array())` |
| 🛑 | `deleteByQuery($query, $args = array(), $waitLastCall = true)`      | **REMOVED** use deleteBy |


##### Index resources

###### Settings

|    | v1 | v2 |
|----|----|----|
| ✅ | `getSettings()`     | `getSettings($requestOptions = array())` |
| 🤞 | `setSettings($settings, $forwardToReplicas = false)`      | `setSettings($settings, $requestOptions = array())` |

###### Synonyms

|    | v1 | v2 |
|----|----|----|
| 🤞 | `searchSynonyms($query, array $synonymType = array(), $page = 0, $hitsPerPage = 100)`       | `searchSynonyms($query, $requestOptions = array())` |
| ✅ | `getSynonym($objectID)`       | `getSynonym($objectId, $requestOptions = array())` |
| 🤞 | `deleteSynonym($objectID, $forwardToReplicas = false)`        | `deleteSynonym($objectId, $requestOptions = array())` |
| 🤞 | `clearSynonyms($forwardToReplicas = false)`       | `clearSynonyms($requestOptions = array())` |
| 🛑 | `batchSynonyms($objects, $forwardToReplicas = false, $replaceExistingSynonyms = false)`       | **REMOVED** Use `saveSynonyms`, `deleteSynonyms` (plurial) |
| 🤞 | `saveSynonym($objectID, $content, $forwardToReplicas = false)`        | `saveSynonym($synonym, $requestOptions = array())` |
| 🤞 | `initSynonymIterator($batchSize = 1000)`      | `browseSynonyms($requestOptions = array())` |

###### Rules

|    | v1 | v2 |
|----|----|----|
| 🤞 | `searchRules(array $params = array())`      | `searchRules($query, $requestOptions = array())` |
| ✅ | `getRule($objectID)`      | `getRule($objectId, $requestOptions = array())` |
| 🤞 | `deleteRule($objectID, $forwardToReplicas = false)`     | `deleteRule($objectId, $requestOptions = array())` |
| 🤞 | `clearRules($forwardToReplicas = false)`      | `clearRules($requestOptions = array())` |
| 🛑 | `batchRules($rules, $forwardToReplicas = false, $clearExistingRules = false)`     | **REMOVED** Use `saveRules`, `deleteRules` (plurial) |
| 🤞 | `saveRule($objectID, $content, $forwardToReplicas = false)`     | `saveRule($rule, $requestOptions = array())` |
| 🤞 | `initRuleIterator($batchSize = 500)`      | `browseRules($requestOptions = array())` |

##### Api Keys

|    | v1 | v2 |
|----|----|----|
| 🛑 | `listApiKeys()`     | **REMOVED** Manage keys on the Client |
| 🤞 | `getApiKey($key)`     | `getDeprecatedIndexApiKey($key, $requestOptions = array())` |
| 🤞 | `deleteApiKey($key)`      | `deleteDeprecatedIndexApiKey($key, $requestOptions = array())` |
| 🛑 | `addApiKey($obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0)`     | **REMOVED** Manage keys on the Client |
| 🛑 | `updateApiKey($key, $obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0)`      | **REMOVED** Manage keys on the Client |

##### Misc

|    | v1 | v2 |
|----|----|----|
| 🤞 | `waitTask($taskID, $timeBeforeRetry = 100)`     | `waitTask($taskId, $requestOptions = array())` |
| 🤞 | `getTaskStatus($taskID)`      | `getTask($taskId, $requestOptions = array())` |
| 🤞 | `clearIndex()`      | `clear($requestOptions = array())` |
| 🤞 | `browseFrom($query, $params = null, $cursor = null)`      | `browse($requestOptions = array())` |
