<?php

namespace Zetkin\ZetkinWordPressPlugin;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Blocks
{
    public static function init()
    {
        add_action('init', function () {
            self::initBlocks();
        });
    }

    private static function initBlocks()
    {
        $BUILD_DIR = __DIR__ . "/../build";
        /**
         * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
         * based on the registered block metadata.
         * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
         *
         * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
         */
        if (function_exists('wp_register_block_types_from_metadata_collection')) {
            wp_register_block_types_from_metadata_collection($BUILD_DIR, $BUILD_DIR . '/blocks-manifest.php');
            return;
        }

        /**
         * Registers the block(s) metadata from the `blocks-manifest.php` file.
         * Added to WordPress 6.7 to improve the performance of block type registration.
         *
         * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
         */
        if (function_exists('wp_register_block_metadata_collection')) {
            wp_register_block_metadata_collection($BUILD_DIR, $BUILD_DIR . '/blocks-manifest.php');
        }
        /**
         * Registers the block type(s) in the `blocks-manifest.php` file.
         *
         * @see https://developer.wordpress.org/reference/functions/register_block_type/
         */
        $manifest_data = require $BUILD_DIR . '/blocks-manifest.php';
        foreach (array_keys($manifest_data) as $block_type) {
            register_block_type($BUILD_DIR . "/{$block_type}");
        }
    }
}
