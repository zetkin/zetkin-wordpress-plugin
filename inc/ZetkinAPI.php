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
        // Note: this is a V2 request, so doesn't use the V1 request method
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
        return self::doSafeV1Request("join_forms/$formId/submissions", "POST", $body);
    }

    public static function getSurveys()
    {
        return self::doSafeV1Request("surveys") ?? [];
    }

    public static function getSurvey($surveyId)
    {
        return self::doSafeV1Request("surveys/$surveyId");
    }

    public static function postSurveyResponse($surveyId, $body)
    {
        return self::doSafeV1Request("surveys/$surveyId/submissions", "POST", $body);
    }

    private static function doSafeV1Request($path, $method = "GET", $body = null)
    {
        try {
            return self::doV1Request($path, $method, $body);
        } catch (\Exception $e) {
            // Todo: logging
        }
        return null;
    }

    private static function doV1Request($path, $method = "GET", $body = null)
    {
        $organizationId = get_option(Settings::ORGANIZATION_ID_OPTION);
        if (!$organizationId) {
            throw new \Exception("Organization ID is missing.");
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
            $msg = $response->get_error_message();
            throw new \Exception("HTTP request failed: $msg");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data) {
            throw new \Exception("Invalid JSON response: $body");
        }

        if (!empty($data["error"])) {
            $msg = is_string($data["error"]) ? $data["error"] : json_encode($data["error"]);
            throw new \Exception("API error: $msg");
        }

        if (!array_key_exists("data", $data)) {
            throw new \Exception("Malformed API response—missing 'data' field.");
        }

        return $data["data"];
    }


    private static function getBaseUrl()
    {
        $stagingEnv = get_option(Settings::STAGING_ENVIRONMENT_OPTION);
        return $stagingEnv ? "http://api.dev.zetkin.org" : "https://api.zetk.in";
    }
}
