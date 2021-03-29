<?php

class zetkin_calendar {
    function __construct() {

        // Add Styles and Scripts
        $this->init();
        // Enable shortcode
        $this->shortcode();
    }

    private function init() {
        add_action("init", function() {
            $custom_template = get_stylesheet_directory()."/zetkin/calendar.css";
            if (is_file($custom_template)) {
                $style_url = get_stylesheet_directory_uri()."/zetkin/calendar.css";
            } else {
                $style_url = ZETKIN_HOME."templates/calendar.css";
            }
        
            wp_enqueue_style( 'zetkin_calendar', $style_url);    
        });
    
    }
    private function shortcode() {
        add_shortcode( 'zetkin_calendar', function($atts) {
            $calendar = "";
            $month = date("n", time());
            $calendar .= $this->generate_calendar($month, 2021);
            return $calendar;
        });
    }

    private function generate_calendar($month, $year) {
        $action_dates = [];
        $campaigns = $this->get_campaigns(1);
        foreach ($campaigns->data as $_campaign) {
            $actions = $this->get_actions(1, $_campaign->id);
            foreach ($actions->data as $_action) {
                $timestamp = strtotime($_action->start_time);
                $date = date("Y-m-d", $timestamp);
                $time = date("H:i", $timestamp);
                $action_dates[$date][] = $_action;
            }
        }

        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $html = "";
        include __DIR__."/../templates/calendar_month.php";
        return $html;
    }
    private function get_campaigns($id) {
        $campaigns = get_transient("_zetkin_calendar_campaigns_$id");
        if (!$campaigns) {
            $campaigns = file_get_contents("https://api.zetk.in/v1/orgs/$id/campaigns");
            set_transient("_zetkin_calendar_campaigns_$id", $campaigns, 30);
        }
        return json_decode($campaigns);
    }
    private function get_actions($id, $c_id) {
        $actions = get_transient("_zetkin_calendar_actions_$id_$c_id");
        if (!$actions) {
            $actions = file_get_contents("https://api.zetk.in/v1/orgs/$id/campaigns/$c_id/actions");
            set_transient("_zetkin_calendar_actions_$id_$c_id", $actions, 30);
        }
        return json_decode($actions);
    }
}