<?php
// Variables
// $year -> The year sent by the class to generate from
// $month -> The year sent by the class to generate from
// $day -> The day sent by the month template file
// $_action -> Current event
// $html -> The return value for the calendar (no need to pass as a return value)

$html .= "<div class=\"zetkin_calendar_day\">";
$html .= "<div class=\"zetkin_calendar_day__image\"><img src=\"https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=200x200&maptype=roadmap
&markers={$_action->location->lat},{$_action->location->lng}&key=AIzaSyDobGyeXcUNudzG3WIwtaTLD1NapIxtFrQ\"></div>";
$html .= "<div class=\"zetkin_calendar_day__meta\">";
$html .= "<div class=\"title\">{$_action->title}</div>";
$html .= "<div class=\"time\">";
$html .= "<div class=\"time__start\"><span>Start</span><span>".date("H:i j/n", strtotime($_action->start_time))."</span></div>";
$html .= "<div class=\"time__end\"><span>Slut</span><span>".date("H:i j/n", strtotime($_action->end_time))."</span></div>";
$html .= "</div>";
$html .= "<div class=\"description\">{$_action->info_text}</div>";
$html .= "<div class=\"contact\">Kontaktperson: {$_action->contact->name}</div>";
$html .= "</div>";
$html .= "</div>";
