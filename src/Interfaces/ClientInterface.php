<?php

namespace Algolia\AlgoliaSearch\Interfaces;

interface ClientInterface
{
    public function initIndex($indexName);

    public function setExtraHeader($headerName, $headerValue);

    public function multipleQueries($queries, $requestOptions = array());

    public function multipleBatchObjects($operations, $requestOptions = array());

    public function multipleGetObjects($requests, $requestOptions = array());

    public function listIndexes($requestOptions = array());

    public function moveIndex($srcIndexName, $dstIndexName, $requestOptions = array());

    public function copyIndex($srcIndexName, $dstIndexName, $requestOptions = array());

    public function clearIndex($indexName, $requestOptions = array());

    public function deleteIndex($indexName, $requestOptions = array());

    public function copySettings($srcIndexName, $dstIndexName, $requestOptions = array());

    public function copySynonyms($srcIndexName, $dstIndexName, $requestOptions = array());

    public function copyRules($srcIndexName, $dstIndexName, $requestOptions = array());

    public function listApiKeys($requestOptions = array());

    public function getApiKey($key, $requestOptions = array());

    public function addApiKey($keyParams, $requestOptions = array());

    public function updateApiKey($key, $keyParams, $requestOptions = array());

    public function deleteApiKey($key, $requestOptions = array());

    public static function generateSecuredApiKey($parentApiKey, $restrictions);

    public function searchUserIds($query, $requestOptions = array());

    public function listClusters($requestOptions = array());

    public function listUserIds($requestOptions = array());

    public function getUserId($userId, $requestOptions = array());

    public function getTopUserId($requestOptions = array());

    public function assignUserId($userId, $clusterName, $requestOptions = array());

    public function removeUserId($userId, $requestOptions = array());

    public function getLogs($requestOptions = array());

    public function getTask($indexName, $taskId, $requestOptions = array());

    public function waitTask($indexName, $taskId, $requestOptions = array());

    public function custom($method, $path, $requestOptions = array(), $hosts = null);
}
