<?php

require_once __DIR__ . "/../vendor/autoload.php";

// Define a dummy ABSPATH variable to allow plugin files to be loaded
// Usually this is set in the WordPress setup, but tests don't load WordPress (yet)
define("ABSPATH", "dummy");
