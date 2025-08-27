<?php

namespace Zetkin\ZetkinWordPressPlugin;

use Zetkin\ZetkinWordPressPlugin\HTML\Element;
use Zetkin\ZetkinWordPressPlugin\HTML\Renderer;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Events
{
    public static function renderCalendar($events)
    {
        $eventElements = [];
        foreach ($events as $event) {
            $eventElements[] = self::getHTMLElementsForEvent($event);
        }
        $calendarElement = new Element("div", ["class" => "zetkin-calendar"], [
            self::getHTMLElementsForFilter(),
            new Element("ol", ["class" => "zetkin-calendar-events"], $eventElements)
        ]);
        Renderer::renderElement($calendarElement);
    }

    private static function getHTMLElementsForFilter()
    {
        return new Element("div", ["class" => "zetkin-events-filter"], [
            new Element("button", ["class" => "zetkin-events-filter__button zetkin-events-filter__clear-button", "type" => "button"], [
                new Element("svg", ["viewBox" => "0 0 24 24"], [
                     new Element("path", [
                        "d" => "M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"
                    ], [])
                ])
            ]),
            new Element("button", ["class" => "zetkin-events-filter__button", "type" => "button"], __("Today", "zetkin")),
            new Element("button", ["class" => "zetkin-events-filter__button", "type" => "button"], __("Tomorrow", "zetkin")),
            new Element("button", ["class" => "zetkin-events-filter__button", "type" => "button"], __("This week", "zetkin")),
            new Element("button", ["class" => "zetkin-events-filter__button zetkin-events-filter__datepicker-button", "type" => "button"], [
                new Element("svg", ["viewBox" => "0 0 24 24"], [
                    new Element("path", [
                        "d" => "M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14zm0-12H5V6h14zM9 14H7v-2h2zm4 0h-2v-2h2zm4 0h-2v-2h2zm-8 4H7v-2h2zm4 0h-2v-2h2zm4 0h-2v-2h2z"
                    ], [])
                ])
            ]),
            new Element("input", ["tabindex" => "-1", "class" => "zetkin-events-filter__datepicker-input"])
        ]);
    }

    private static function getHTMLElementsForEvent($event)
    {
        $isStaging = get_option(Settings::STAGING_ENVIRONMENT_OPTION, '');
        $baseURL = $isStaging ? "https://app.dev.zetkin.org" : "https://app.zetkin.org";

        $eventId = $event["id"] ?? "";
        $organization = $event['organization']['title'] ?? __("Unknown organization", "zetkin");
        $organizationId = $event['organization']['id'] ?? null;
        $startTime = $event['start_time'];
        $endTime = $event['end_time'];
        $time = Utils::getFormattedEventTime($startTime, $endTime);

        $eventElements = [];

        if (empty($event["title"])) {
            $eventElements[] = new Element(
                "h2",
                ["class" => "zetkin-event__title"],
                __("Unknown Event", "zetkin")
            );
        } else if ($organizationId === null) {
            $eventElements[] = new Element(
                "h2",
                ["class" => "zetkin-event__title"],
                $event["title"]
            );
        } else {
            $eventElements[] = new Element("h2", ["class" => "zetkin-event__title"], [
                new Element("a", ["href" => "$baseURL/o/$organizationId/events/$eventId"], $event["title"])
            ]);
        }

        if (!empty($event['campaign']['title'])) {
            $eventElements[] = new Element("p", ["class" => "zetkin-event__project"], $event['campaign']['title'] . "/" . $organization);
        } else {
            $eventElements[] = new Element("p", ["class" => "zetkin-event__project"], $organization);
        }
        if ($time) {
            $eventElements[] = new Element("p", ["class" => "zetkin-event__time"], $time);
        }
        if (!empty($event['location']['title'])) {
            $eventElements[] = new Element("p", ["class" => "zetkin-event__location"], $event['location']['title']);
        }
        return new Element(
            "li",
            ["class" => "zetkin-event", "data-starttime" => $startTime],
            $eventElements
        );
    }
}
