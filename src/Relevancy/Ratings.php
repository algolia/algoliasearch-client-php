<?php

namespace Algolia\AlgoliaSearch\Relevancy;

class Ratings
{
    public static function score($positive, $negative) {
        return ((($positive + 1.9208) / ($positive + $negative) - 1.96 * sqrt((($positive * $negative) / ($positive + $negative)) + 0.9604) / ($positive + $negative)) / (1 + 3.8416 / ($positive + $negative)));
    }

    public static function fiveStarRating($one, $two, $three, $four, $five) {
        $positive = $two * 0.25 + $three * 0.5 + $four * 0.75 + $five;
        $negative = $one + $two * 0.75 + $three * 0.5 + $four * 0.25;

        return self::score($positive, $negative);
    }

    public static function fiveStarRatingAverage($avg, $total)
    {
        $positive = ($avg * $total - $total) / 4;
        $negative = $total - $positive;

        return self::score($positive, $negative);
    }
}
