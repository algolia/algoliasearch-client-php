<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * SynonymType Class Doc Comment
 *
 * @category Class
 * @description Type of the synonym object.
 * @package Algolia\AlgoliaSearch
 */
class SynonymType
{
    /**
     * Possible values of this enum
     */
    public const SYNONYM = 'synonym';

    public const ONEWAYSYNONYM = 'onewaysynonym';

    public const ALTCORRECTION1 = 'altcorrection1';

    public const ALTCORRECTION2 = 'altcorrection2';

    public const PLACEHOLDER = 'placeholder';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::SYNONYM,
            self::ONEWAYSYNONYM,
            self::ALTCORRECTION1,
            self::ALTCORRECTION2,
            self::PLACEHOLDER,
        ];
    }
}
