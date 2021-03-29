<?php
$_date = date("Y-m-d", strtotime("$year-$month-$day"));

if (!empty($action_dates[$_date])) {
    foreach ($action_dates[$_date] as $_action) {
        $html .= "<div class=\"zetkin_calendar_day\">";
        $html .= "<div class=\"zetkin_calendar_day__image\"><img src=\"https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=600x300&maptype=roadmap
        &markers={$_action->location->lat},{$_action->location->lng}&key=AIzaSyDobGyeXcUNudzG3WIwtaTLD1NapIxtFrQ\"></div>";
        $html .= "<div class=\"zetkin_calendar_day__meta\">";
        $html .= "<div class=\"start_time\">{$_action->start_time}</div>";
        $html .= "<div class=\"end_time\">{$_action->end_time}</div>";
        $html .= "<div class=\"title\">{$_action->title}</div>";
        $html .= "<div class=\"contact\">{$_action->contact->name}</div>";
        $html .= "<div class=\"description\">{$_action->info_text}</div>";
        $html .= "</div>";
        $html .= "</div>";
    }
}
