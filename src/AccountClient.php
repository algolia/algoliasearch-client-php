<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Response\MultiResponse;

final class AccountClient
{
    public static function copyIndex(SearchIndex $srcIndex, SearchIndex $destIndex, $requestOptions = array())
    {
        try {
            $destIndex->getSettings();

            throw new \InvalidArgumentException(
                'Destination index already exists. Please delete it before copying index across applications.   '
            );
        } catch (NotFoundException $e) {
            // All good
        }

        $allResponses = array();

        $settings = $srcIndex->getSettings();
        $allResponses[] = $destIndex->setSettings($settings);

        $synonymsIterator = $srcIndex->browseSynonyms();
        $allResponses[] = $destIndex->saveSynonyms($synonymsIterator);

        $objectsIterator = $srcIndex->browseObjects();
        $allResponses[] = $destIndex->saveObjects($objectsIterator);

        $rulesIterator = $srcIndex->browseRules();
        $allResponses[] = $destIndex->saveRules($rulesIterator);

        return new MultiResponse($allResponses);
    }
}
