<?php

/**
 * Plugin Name:       Zetkin
 * Description:       Blocks to integrate Zetkin with WordPress.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Common Knowledge, Zetkin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       zetkin
 *
 * @package Zetkin
 */

use Zetkin\ZetkinWordPressPlugin\Blocks;
use Zetkin\ZetkinWordPressPlugin\Settings;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor/autoload.php';

Blocks::init();
Settings::init();
