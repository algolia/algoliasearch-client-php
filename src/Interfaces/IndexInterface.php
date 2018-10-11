<?php

namespace Algolia\AlgoliaSearch\Interfaces;

interface IndexInterface
{
    public function search($query, $requestOptions = array());

    public function clear($requestOptions = array());

    public function move($newIndexName, $requestOptions = array());

    public function getSettings($requestOptions = array());

    public function setSettings($settings, $requestOptions = array());

    public function getObject($objectId, $requestOptions = array());

    public function getObjects($objectIds, $requestOptions = array());

    public function saveObject($object, $requestOptions = array());

    public function saveObjects($objects, $requestOptions = array());

    public function partialUpdateObject($object, $requestOptions = array());

    public function partialUpdateObjects($objects, $requestOptions = array());

    public function partialUpdateOrCreateObject($object, $requestOptions = array());

    public function partialUpdateOrCreateObjects($objects, $requestOptions = array());

    public function deleteObject($objectId, $requestOptions = array());

    public function deleteObjects($objectIds, $requestOptions = array());

    public function deleteBy(array $args, $requestOptions = array());

    public function batch($requests, $requestOptions = array());

    public function browse($requestOptions = array());

    public function searchSynonyms($query, $requestOptions = array());

    public function getSynonym($objectId, $requestOptions = array());

    public function saveSynonym($synonym, $requestOptions = array());

    public function saveSynonyms($synonyms, $requestOptions = array());

    public function freshSynonyms($synonyms, $requestOptions = array());

    public function deleteSynonym($objectId, $requestOptions = array());

    public function clearSynonyms($requestOptions = array());

    public function browseSynonyms($requestOptions = array());

    public function searchRules($query, $requestOptions = array());

    public function getRule($objectId, $requestOptions = array());

    public function saveRule($rule, $requestOptions = array());

    public function saveRules($rules, $requestOptions = array());

    public function freshRules($rules, $requestOptions = array());

    public function deleteRule($objectId, $requestOptions = array());

    public function clearRules($requestOptions = array());

    public function browseRules($requestOptions = array());

    public function getTask($taskId, $requestOptions = array());

    public function waitTask($taskId, $requestOptions = array());

    public function custom($method, $path, $requestOptions = array(), $hosts = null);
}
