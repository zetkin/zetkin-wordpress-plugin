<?php

namespace Zetkin\ZetkinWordPressPlugin;

use WP_REST_Request;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class RestAPI
{

    public static function init()
    {
        add_action('rest_api_init', function () {
            register_rest_route('zetkin', '/surveys', array(
                'methods'  => 'GET',
                'callback' => function () {
                    return ZetkinAPI::getSurveys();
                },
                'permission_callback' => '__return_true',
            ));

            register_rest_route('zetkin', '/surveys/(?P<survey_id>[\d]+)', array(
                'methods'             => 'GET',
                'callback'            => function ($request) {
                    $surveyId = (int) $request->get_param('survey_id');
                    return ZetkinAPI::getSurvey($surveyId);
                },
                'args'                => array(
                    'survey_id' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_numeric($param);
                        },
                    ),
                ),
                'permission_callback' => '__return_true',
            ));

            register_rest_route('zetkin', '/surveys/(?P<survey_id>[\d]+)/submissions', array(
                'methods'             => 'POST',
                'callback'            => function ($request) {
                    $surveyId = (int) $request->get_param('survey_id');
                    $body = $request->get_json_params();
                    $response = ZetkinAPI::postSurveyResponse($surveyId, $body);
                    if ($response) {
                        return ["ok" => true];
                    }
                    return ["ok" => false];
                },
                'args'                => array(
                    'survey_id' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_numeric($param);
                        },
                    ),
                ),
                'permission_callback' => '__return_true',
            ));
        });
    }
}
