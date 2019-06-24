<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Response\MultiResponse;

final class AccountClient
{
    /**
     * @param SearchIndex $srcIndex
     * @param SearchIndex $destIndex
     * @param array $requestOptions
     * @return MultiResponse
     * @throws Exceptions\MissingObjectId
     */
    public static function copyIndex(SearchIndex $srcIndex, SearchIndex $destIndex, $requestOptions = array())
    {
        if ($srcIndex->getAppId() === $destIndex->getAppId()) {
            throw new \InvalidArgumentException(
                'If both index are on the same app, please use SearchClient::copyIndex method instead.'
            );
        }

        try {
            $destIndex->getSettings();

            throw new \InvalidArgumentException(
                'Destination index already exists. Please delete it before copying index across applications.'
            );
        } catch (NotFoundException $e) {
            // All good
            // @ignoreException
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
