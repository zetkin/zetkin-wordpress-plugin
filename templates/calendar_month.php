<?php
// Variables
// $year -> The year sent by the class to generate from
// $month -> The year sent by the class to generate from
// $html -> The return value for the calendar (no need to pass as a return value)

$_date = date("Y-m-d", strtotime("$year-$month-01"));

$html .= "<div class=\"zetkin_calendar_month\">";
$html .= "<div class=\"zetkin_calendar_month__name\">";
$html .= strftime("%B", strtotime($_date))." $year";
$html .= "</div>";

for ($day=1; $day<=$days; $day++) {
    $_date = date("Y-m-d", strtotime("$year-$month-$day"));
    if (strtotime($_date) >= strtotime(date("Y-m-d", time()))) {
        if (!empty($action_dates[$_date])) {
            foreach ($action_dates[$_date] as $_action) {
                include __DIR__."/calendar_day.php";
            }
        }    
    }
}
$html .= "</div>";