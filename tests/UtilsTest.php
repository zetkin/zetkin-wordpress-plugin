<?php

namespace Zetkin\ZetkinWordPressPlugin\Tests;

use PHPUnit\Framework\TestCase;
use Zetkin\ZetkinWordPressPlugin\Utils;

class UtilsTest extends TestCase {

    public function testGetFormattedEventTime()
    {
        $testCases = [
            [
                "startTime" => "2025-05-21T15:00:00+00:00",
                "endTime" => "2025-05-21T16:30:00+00:00",
                "result" => "May 21, 3:00 PM - 4:30 PM",
            ],
            [
                "startTime" => "2025-05-21T15:00:00+00:00",
                "endTime" => "2025-05-22T16:30:00+00:00",
                "result" => "May 21, 3:00 PM - May 22, 4:30 PM",
            ],
            [
                "startTime" => "2026-05-21T15:00:00+00:00",
                "endTime" => "2026-05-21T16:30:00+00:00",
                "result" => "May 21 2026, 3:00 PM - 4:30 PM",
            ],
            [
                "startTime" => "2026-05-21T15:00:00+00:00",
                "endTime" => "2026-05-22T16:30:00+00:00",
                "result" => "May 21 2026, 3:00 PM - May 22, 4:30 PM",
            ],
            [
                "startTime" => "2025-05-21T15:00:00+00:00",
                "endTime" => null,
                "result" => "May 21, 3:00 PM",
            ],
            [
                "startTime" => null,
                "endTime" => "2025-05-21T16:30:00+00:00",
                "result" => "May 21, 4:30 PM",
            ],
            [
                "startTime" => null,
                "endTime" => null,
                "result" => "",
            ],
        ];
        foreach ($testCases as $testCase) {
            $this->assertEquals($testCase['result'], Utils::getFormattedEventTime($testCase['startTime'], $testCase['endTime']));
        }
    }
}