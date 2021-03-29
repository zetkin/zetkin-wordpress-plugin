<?php
$_date = date("Y-m-d", strtotime("$year-$month-01"));

$html .= "<div class=\"zetkin_calendar_month\">";
$html .= "<div class=\"zetkin_calendar_month__name\">";
$html .= strftime("%B", strtotime($_date));
$html .= "</div>";

for ($day=1; $day<=$days; $day++) {
    include __DIR__."/calendar_day.php";
}
$html .= "</div>";