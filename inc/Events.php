<?php

namespace Zetkin\ZetkinWordPressPlugin;

use Zetkin\ZetkinWordPressPlugin\HTML\Element;
use Zetkin\ZetkinWordPressPlugin\HTML\Renderer;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Events
{
    public const PAGE_SIZE = 10;

    public static function renderCalendar($events, $attributes)
    {
        $eventElements = [];
        $eventTypes = [];
        $uncategorized = __("Uncategorized", "zetkin");
        foreach ($events as $i => $event) {
            $eventElements[] = self::getHTMLElementsForEvent($i, $event, $uncategorized, $attributes);
            $eventType = $event["activity"]["title"] ?? $uncategorized;
            $eventTypes[$eventType] = true;
        }
        $eventTypes = array_keys($eventTypes);
        usort($eventTypes, function ($a, $b) use ($uncategorized) {
            if ($a === $uncategorized) {
                return 1;
            }
            if ($b === $uncategorized) {
                return -1;
            }
            return $a < $b ? -1 : 1;
        });

        $calendarElement = new Element("div", ["class" => "zetkin-calendar", "data-page-size" => self::PAGE_SIZE, "data-page" => 0], [
            self::getHTMLElementsForFilter($eventTypes, $attributes),
            new Element("p", ["class" => "zetkin-no-events", "style" => $events ? "display:none;" : "display:block;"], __("Could not find any events.", "zetkin")),
            new Element("ol", ["class" => "zetkin-calendar-events"], $eventElements),
            self::getHTMLElementsForPagination(count($events))
        ]);
        Renderer::renderElement($calendarElement);
    }

    private static function getHTMLElementsForFilter($eventTypes, $attributes)
    {
        return new Element("div", ["class" => "zetkin-events-filter"], [
            new Element("button", ["class" => "zetkin-events-filter__button zetkin-events-filter__clear-button", "type" => "button"], [
                new Element("svg", ["viewBox" => "0 0 24 24"], [
                    new Element("path", [
                        "d" => "M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"
                    ], [])
                ])
            ]),
            new Element("button", ["class" => "zetkin-events-filter__button zetkin-events-filter__today-button", "type" => "button"], __("Today", "zetkin")),
            new Element("button", ["class" => "zetkin-events-filter__button zetkin-events-filter__tomorrow-button", "type" => "button"], __("Tomorrow", "zetkin")),
            new Element("button", ["class" => "zetkin-events-filter__button zetkin-events-filter__this-week-button", "type" => "button"], __("This week", "zetkin")),
            new Element("button", ["class" => "zetkin-events-filter__button zetkin-events-filter__datepicker-button", "type" => "button"], [
                new Element("svg", ["viewBox" => "0 0 24 24"], [
                    new Element("path", [
                        "d" => "M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14zm0-12H5V6h14zM9 14H7v-2h2zm4 0h-2v-2h2zm4 0h-2v-2h2zm-8 4H7v-2h2zm4 0h-2v-2h2zm4 0h-2v-2h2z"
                    ], [])
                ]),
                new Element("span", [])
            ]),
            new Element("input", ["tabindex" => "-1", "class" => "zetkin-events-filter__datepicker-input"]),
            new Element("div", ["class" => "zetkin-event-types-filter"], [
                new Element("button", ["class" => "zetkin-events-filter__button", "type" => "button"], [
                    new Element("span", [], __("Event types", "zetkin")),
                    new Element("span", [])
                ]),
                new Element("ul", ["class" => "zetkin-event-types"], array_map(function ($type) {
                    return new Element("li", ["class" => "zetkin-event-type"], [
                        new Element("input", ["type" => "checkbox", "class" => "zetkin-event-type__checkbox", "id" => "$type-checkbox"]),
                        new Element("label", ["class" => "zetkin-event-type__label", "for" => "$type-checkbox"], $type),
                    ]);
                }, $eventTypes))
            ])
        ]);
    }

    private static function getHTMLElementsForEvent($index, $event, $uncategorized, $attributes)
    {
        $isStaging = get_option(Settings::STAGING_ENVIRONMENT_OPTION, '');
        $baseURL = $isStaging ? "https://app.dev.zetkin.org" : "https://app.zetkin.org";

        $eventId = $event["id"] ?? "";
        $organization = $event['organization']['title'] ?? __("Unknown organization", "zetkin");
        $organizationId = $event['organization']['id'] ?? null;
        $startTime = $event['start_time'];
        $endTime = $event['end_time'];
        $time = Utils::getFormattedEventTime($startTime, $endTime);
        $eventHref = $organizationId === null ? "" : "$baseURL/o/$organizationId/events/$eventId";

        $eventElements = [];

        $title = self::getEventTitle($event);
        $textColor = $attributes["textColor"] ?? null;
        $titleStyle = $textColor ? "color:{$textColor};" : "";
        if ($eventHref === null) {
            $eventElements[] = new Element(
                "h2",
                ["class" => "zetkin-event__title", "style" => $titleStyle],
                $title
            );
        } else {
            $eventElements[] = new Element("h2", ["class" => "zetkin-event__title"], [
                new Element("a", ["href" => $eventHref, "target" => "_blank", "style" => $titleStyle], $title)
            ]);
        }

        $svgAttrs = $textColor ? ["style" => "fill:{$textColor};"] : [];

        $projectElement = null;
        if (!empty($event['campaign']['title'])) {
            $projectElement = new Element("p", ["class" => "zetkin-event__project"], $event['campaign']['title'] . "/" . $organization);
        } else {
            $projectElement = new Element("p", ["class" => "zetkin-event__project"], $organization);
        }
        $eventElements[] = new Element("div", ["class" => "zetkin-event__row"], [
            new Element("svg", array_merge(["viewBox" => "0 0 24 24"], $svgAttrs), [
                new Element("path", ["d" => "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"], []),
                new Element("circle", ["cx" => "8", "cy" => "14", "r" => "2"], []),
                new Element("circle", ["cx" => "12", "cy" => "8", "r" => "2"], []),
                new Element("circle", ["cx" => "16", "cy" => "14", "r" => "2"], []),
            ]),
            $projectElement
        ]);

        if ($time) {
            $eventElements[] = new Element("div", ["class" => "zetkin-event__row"], [
                new Element("svg", array_merge(["viewBox" => "0 0 24 24"], $svgAttrs), [
                    new Element("path", ["d" => "M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8m.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7z"], [])
                ]),
                new Element("p", ["class" => "zetkin-event__time"], $time)
            ]);
        }
        if (!empty($event['location']['title'])) {
            $eventElements[] = new Element("div", ["class" => "zetkin-event__row"], [
                new Element("svg", array_merge(["viewBox" => "0 0 24 24"], $svgAttrs), [
                    new Element("path", ["d" => "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7M7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9"], []),
                    new Element("circle", ["cx" => "12", "cy" => "9", "r" => "2.5"], []),
                ]),
                new Element("p", ["class" => "zetkin-event__location"], $event['location']['title'])
            ]);
        }

        if ($eventHref) {
            $buttonStyle = "";
            $buttonColor = $attributes["buttonColor"] ?? "";
            $buttonTextColor = $attributes["buttonTextColor"] ?? "";
            if ($buttonColor) {
                $buttonStyle .= "background-color:{$buttonColor};";
            }
            if ($buttonTextColor) {
                $buttonStyle .= "color:{$buttonTextColor};";
            }
            $eventElements[] = new Element("a", ["class" => "zetkin-event__sign-up zetkin-submit-button", "href" => $eventHref, "target" => "_blank", "style" => $buttonStyle], __("Sign up", "zetkin"));
        }

        $attrs = ["class" => "zetkin-event", "data-index" => $index, "data-starttime" => $startTime, "data-event-type" => $event["activity"]["title"] ?? $uncategorized];
        if ($index >= self::PAGE_SIZE) {
            $attrs["data-hidden-page"] = "true";
        }

        $marginBottom = $attributes["spacing"] ?? 0;
        $style = "margin-bottom:{$marginBottom}px;";

        $backgroundColor = $attributes["eventColor"] ?? null;
        if ($backgroundColor) {
            $style .= "background-color:{$backgroundColor};";
        }
        $attrs["style"] = $style;

        return new Element(
            "li",
            $attrs,
            $eventElements
        );
    }

    private static function getEventTitle($event)
    {
        if (!empty($event["title"])) {
            return $event["title"];
        }
        if (!empty($event["activity"]["title"])) {
            return $event["activity"]["title"];
        }
        return __("Untitled event", "zetkin");
    }

    private static function getHTMLElementsForPagination($count)
    {
        if ($count <= self::PAGE_SIZE) {
            return new Element("ol", ["class" => "zetkin-pagination"]);
        }
        $pages = ceil($count / self::PAGE_SIZE);
        $pageItems = [];
        for ($i = 0; $i < $pages; $i++) {
            $buttonClass = "zetkin-pagination-button";
            if ($i === 0) {
                $buttonClass .= " zetkin-pagination-button--selected";
            }
            $pageItems[] = new Element("li", ["class" => "zetkin-pagination-page" ], [
                new Element("button", ["class" => $buttonClass, "type" => "button"], $i + 1)
            ]);
        }
        return new Element("ol", ["class" => "zetkin-pagination"], $pageItems);
    }
}
