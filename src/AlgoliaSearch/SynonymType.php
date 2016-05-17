<?php

namespace AlgoliaSearch;

class SynonymType
{
    const SYNONYM = 0;
    const SYNONYM_ONEWAY = 1;
    const PLACEHOLDER = 2;
    const ALTCORRECTION_1 = 3;
    const ALTCORRECTION_2 = 4;

    public static function getSynonymsTypeString($synonymType)
    {
        if ($synonymType == self::SYNONYM) {
            return 'synonym';
        }

        if ($synonymType == self::SYNONYM_ONEWAY) {
            return 'oneWaySynonym';
        }

        if ($synonymType == self::PLACEHOLDER) {
            return 'placeholder';
        }

        if ($synonymType == self::ALTCORRECTION_1) {
            return 'altCorrection1';
        }

        if ($synonymType == self::ALTCORRECTION_2) {
            return 'altCorrection2';
        }

        return;
    }
}
