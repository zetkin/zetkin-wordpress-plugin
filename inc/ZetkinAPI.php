<?php

namespace Zetkin\ZetkinWordPressPlugin;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ZetkinAPI
{

    public static function getEvents()
    {
        $organisationId = get_option(Settings::ORGANISATION_ID_OPTION);
        if (!$organisationId) {
            return [];
        }
        $stagingEnv = get_option(Settings::STAGING_ENVIRONMENT_OPTION);
        $baseUrl = $stagingEnv ? "http://api.dev.zetkin.org" : "https://api.zetkin.org";
        $url = $baseUrl . "/v1/orgs/" . $organisationId . "/actions?recursive";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            // TODO: Logging
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return $data["data"];
    }
}
