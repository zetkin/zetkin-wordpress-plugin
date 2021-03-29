<?php
/* 
Plugin Name: Zetkin Wordpress Plugin
Plugin URI: https://spacewalk.it
Version: 1.0
Author: Spacewalk
Author URI: http://spacewalk.it
Description: Display your Zetkin calendar feed
*/

$url = plugin_dir_url(__FILE__);
define('ZETKIN_HOME',$url);

require_once "core/class.php";

$zetkin_calendar = new zetkin_calendar();