<?php

namespace Algolia\AlgoliaSearch\Contracts;

interface ClientInterface
{
//    public function multipleQueries($queries, $strategy, $requestOptions);
//
//    public function batch($operations, $requestOptions);

    public function listIndices($requestOptions); # listIndexes
//
//    public function moveIndex($indexNameSrc, $indexNameDest, $requestOptions);
//
//    public function copyIndex($indexNameSrc, $indexNameDest, $scope, $requestOptions);
//    public function clearIndex($indexName, $requestOptions); // Move to Index class?
}
