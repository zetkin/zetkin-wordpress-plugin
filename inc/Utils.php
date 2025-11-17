<?php

namespace Zetkin\ZetkinWordPressPlugin;

use DateTime;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Utils
{
    const DATE_TIME_FORMAT_WITH_YEAR = "F j Y, g:i A";
    const DATE_TIME_FORMAT_WITHOUT_YEAR = "F j, g:i A";
    const TIME_FORMAT = "g:i A";

    /**
     * Parameters should be strings formatted Zetkin API style
     * e.g. 2025-05-21T15:00:00+00:00
     * 
     * Returns Zetkin-style formatted dates
     * e.g. June 11, 4:40 PM - 6:40 PM
     */
    public static function getFormattedEventTime(string | null $startTime, string | null $endTime)
    {
        if (!$startTime && !$endTime) {
            return null;
        }

        // If no start time is provided, use the end time instead
        if (!$startTime) {
            return self::getFormattedTime($endTime);
        }

        if (!$endTime) {
            return self::getFormattedTime($startTime);
        }

        return self::getFormattedTimes($startTime, $endTime);
    }

    private static function getFormattedTime($startTime)
    {
        $date = new DateTime($startTime);
        $now = new DateTime();
        if ($date->format('Y') === $now->format('Y')) {
            return $date->format(self::DATE_TIME_FORMAT_WITHOUT_YEAR);
        }
        return $date->format(self::DATE_TIME_FORMAT_WITH_YEAR);
    }

    private static function getFormattedTimes($startTime, $endTime)
    {
        $startDate = new DateTime($startTime);
        $endDate = new DateTime($endTime);
        if ($startDate->format("F j Y") === $endDate->format("F j Y")) {
            return self::getFormattedTime($startTime) . ' - ' . $endDate->format(self::TIME_FORMAT);
        }
        return self::getFormattedTime($startTime) . ' - ' . $endDate->format(self::DATE_TIME_FORMAT_WITHOUT_YEAR);
    }

    public static function getBlockStyle($attributes)
    {
        $paddingTop = $attributes["paddingTop"] ?? 0;
        $paddingBottom = $attributes["paddingBottom"] ?? 0;
        $paddingLeft = $attributes["paddingLeft"] ?? 0;
        $paddingRight = $attributes["paddingRight"] ?? 0;
        $style = "padding-top:{$paddingTop}px;padding-bottom:{$paddingBottom}px;padding-left:{$paddingLeft}px;padding-right:{$paddingRight}px;";
        if (!empty($attributes["textColor"])) {
            $color = $attributes["textColor"];
            $style .= "color:{$color};";
        }
        if (!empty($attributes["backgroundColor"])) {
            $backgroundColor = $attributes["backgroundColor"];
            $style .= "background-color:{$backgroundColor};";
        }
        return $style;
    }
}
