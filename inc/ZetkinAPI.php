<?php

namespace Zetkin\ZetkinWordPressPlugin;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ZetkinAPI
{

    public static function getEvents()
    {
        return self::doV1Request("actions?recursive") ?? [];
    }

    public static function getJoinForm($formId)
    {
        $organizationId = get_option(Settings::ORGANIZATION_ID_OPTION);
        if (!$organizationId) {
            return [];
        }
        $baseUrl = self::getBaseUrl();
        $url = $baseUrl . "/v2/orgs/" . $organizationId . "/join_forms/$formId";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            // TODO: Logging
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data) {
            // TODO: Logging
            return [];
        }

        return $data["data"];
    }

    public static function submitJoinForm($formId, $formData)
    {
        $submitToken = get_option("ZETKIN_JOIN_FORM_SUBMIT_TOKEN_" . $formId);
        $body = [
            "form_data" => $formData,
            "submit_token" => $submitToken
        ];
        return self::doV1Request("join_forms/$formId/submissions", "POST", $body);
    }

    public static function getSurveys()
    {
        return self::doV1Request("surveys") ?? [];
    }

    public static function getSurvey($surveyId)
    {
        return self::doV1Request("surveys/$surveyId");
    }

    public static function postSurveyResponse($surveyId, $body)
    {
        return self::doV1Request("surveys/$surveyId/submissions", "POST", $body);
    }

    private static function doV1Request($path, $method = "GET", $body = null)
    {
        $organizationId = get_option(Settings::ORGANIZATION_ID_OPTION);
        if (!$organizationId) {
            return [];
        }
        $baseUrl = self::getBaseUrl();
        $url = $baseUrl . "/v1/orgs/" . $organizationId . "/$path";

        if ($method === "POST") {
            $response = wp_remote_post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode($body),
            ]);
        } else {
            $response = wp_remote_get($url);
        }

        if (is_wp_error($response)) {
            // TODO: Logging
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !empty($data["error"])) {
            // TODO: Logging
            return null;
        }

        return $data["data"];
    }

    private static function getBaseUrl()
    {
        $stagingEnv = get_option(Settings::STAGING_ENVIRONMENT_OPTION);
        return $stagingEnv ? "http://api.dev.zetkin.org" : "https://api.zetk.in";
    }
}
