<?php

namespace Zetkin\ZetkinWordPressPlugin;

use Zetkin\ZetkinWordPressPlugin\HTML\Element;
use Zetkin\ZetkinWordPressPlugin\HTML\Renderer;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Surveys
{
    const INPUT_ID_PREFIX = "zetkin-survey-question-";
    const WORDPRESS_ACTION = "zetkin_survey_form";
    const RESULT_QUERY_ARG_PREFIX = "zetkin_survey_";

    public static function init()
    {
        add_action('wp_ajax_' . self::WORDPRESS_ACTION, function () {
            self::handleSubmission();
        });
        add_action('wp_ajax_nopriv_' . self::WORDPRESS_ACTION, function () {
            self::handleSubmission();
        });
    }

    private static function handleSubmission()
    {
        $surveyId = $_POST["survey_id_hidden"];
        $redirectUrl = $_POST["redirect_url_hidden"];

        $responses = [];
        $signature = [];

        // Used in a transient session to display previous input values on error
        $allFields = [];

        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, "sig_")) {
                $allFields[$key] = $value;

                $field = substr($key, 4, -5);
                $signature[$field] = $value;
                continue;
            }
            if (str_ends_with($key, "_text")) {
                $allFields[$key] = $value;

                $field = substr($key, 0, -5);
                $responses[] = [
                    "question_id" => (int) $field,
                    "response" => $value
                ];
                continue;
            }
            if (str_ends_with($key, "_options")) {
                if (!is_array($value)) {
                    $value = [$value];
                }
                // Ensure always an array in transient session
                $allFields[$key] = $value;

                $field = substr($key, 0, -10);
                $options = [];
                foreach ($value as $v) {
                    if ($v !== "") {
                        $options[] = (int) $v;
                    }
                }
                $responses[] = [
                    "question_id" => (int) $field,
                    "options" => $options
                ];
            }
        }

        $response = ZetkinAPI::postSurveyResponse(
            $surveyId,
            ["responses" => $responses, "signature" => $signature]
        );
        $response = false;

        // On error, save responses in a transient session
        if (!$response) {
            $queryArg = self::RESULT_QUERY_ARG_PREFIX . $surveyId;
            set_transient($queryArg, $allFields, 60); // 60 seconds lifetime
        }

        // Add submission result to the URL query args
        // Remove previous result, if exists
        $redirectUrl = remove_query_arg($queryArg, $redirectUrl);
        $redirectUrl = add_query_arg([
            $queryArg => $response ? "success" : "error"
        ], $redirectUrl);

        wp_redirect($redirectUrl);
        exit;
    }

    public static function renderSurvey($zetkinSurvey, $result = null)
    {
        $prevSubmission = get_transient(self::RESULT_QUERY_ARG_PREFIX . $zetkinSurvey["id"]);
        if (!$prevSubmission) {
            $prevSubmission = [];
        }

        $redirectUrl = home_url(add_query_arg(null, null)); // Get the current URL

        $surveyElements = $zetkinSurvey["elements"];
        // Add signature elements, required on all surveys but not in the Zetkin API response.
        $surveyElements = array_merge($surveyElements, [
            [
                "id" => "sig_first_name",
                "type" => "question",
                "question" => [
                    "question" => __("First name", "zetkin"),
                    "response_type" => "text",
                    "response_config" => [
                        "multiline" => false
                    ],
                    "required" => true
                ]
            ],
            [
                "id" => "sig_last_name",
                "type" => "question",
                "question" => [
                    "question" => __("Last name", "zetkin"),
                    "response_type" => "text",
                    "response_config" => [
                        "multiline" => false
                    ],
                    "required" => true
                ]
            ],
            [
                "id" => "sig_email",
                "type" => "question",
                "question" => [
                    "question" => __("Email", "zetkin"),
                    "response_type" => "text",
                    "response_config" => [
                        "multiline" => false,
                        "type" => "email"
                    ],
                    "required" => true
                ]
            ]
        ]);

        $formElements = [
            new Element(
                "input",
                ["type" => "hidden", "name" => "action", "value" => self::WORDPRESS_ACTION]
            ),
            new Element("input", ["type" => "hidden", "name" => "redirect_url.hidden", "value" => $redirectUrl]),
            new Element("input", ["type" => "hidden", "name" => "survey_id.hidden", "value" => $zetkinSurvey["id"]]),
        ];

        foreach ($surveyElements as $surveyElement) {
            $formElements[] = self::getHTMLElementsForSurveyElement($surveyElement, $prevSubmission);
        }

        $formElements[] = self::getPrivacyHTMLElements($zetkinSurvey);
        $formElements[] = new Element("button", ["class" => "zetkin-survey-submit", "type" => "submit"], __("Submit", "zetkin"));
        if ($result === "error") {
            $formElements[] =
                new Element(
                    "small",
                    ["class" => "zetkin-survey-error"],
                    __("Could not submit your response, please try again later.", "zetkin")
                );
        }
        $formElement = new Element(
            "form",
            ["class" => "zetkin-survey", "method" => "POST", "action" => admin_url('admin-ajax.php')],
            $formElements
        );

        Renderer::renderElement($formElement);
    }

    private static function getHTMLElementsForSurveyElement($element, $prevSubmission)
    {
        $type = $element["type"];

        if ($type === "text") {
            return self::getTextElements($element);
        }

        $question = $element["question"];
        $responseType = $question["response_type"];
        $responseConfig = $question["response_config"];
        $widgetType = $responseConfig["widget_type"] ?? null;

        if ($responseType === "text") {
            if ($responseConfig["multiline"] ?? false) {
                return self::getTextAreaElements($element, $prevSubmission);
            } else {
                return self::getTextInputElements($element, $prevSubmission);
            }
        }

        if ($widgetType === "checkbox" || $widgetType === "radio") {
            return self::getOptionsElements($element, $prevSubmission);
        }

        if ($widgetType === "select") {
            return self::getSelectElements($element, $prevSubmission);
        }
    }

    private static function getTextElements($element)
    {
        $children = [];
        $header = $element["text_block"]["header"] ?? "";
        $content = $element["text_block"]["content"] ?? "";
        if ($header) {
            $children[] = new Element(
                "h2",
                [
                    "class" => "zetkin-survey-text__header",
                ],
                $header
            );
        }
        if ($content) {
            $children[] = new Element(
                "p",
                [
                    "class" => "zetkin-survey-text__content",
                ],
                $content
            );
        }
        return new Element("div", ["class" => "zetkin-survey-text"], $children);
    }

    private static function getTextInputElements($element, $prevSubmission)
    {
        $id = self::INPUT_ID_PREFIX . $element["id"];
        $children = self::getLabelElements($element);
        $name = $element["id"] . "_text";
        $children[] = new Element(
            "input",
            [
                "id" => $id,
                "name" => $name,
                "type" => $element["question"]["response_config"]["type"] ?? "text",
                "required" => $element["question"]["required"],
                "value" => $prevSubmission[$name] ?? ""
            ]
        );
        return new Element("div", ["class" => "zetkin-survey-question zetkin-survey-question--single-line"], $children);
    }

    private static function getTextAreaElements($element, $prevSubmission)
    {
        $id = self::INPUT_ID_PREFIX . $element["id"];
        $children = self::getLabelElements($element);
        $name = $element["id"] . "_text";
        $children[] = new Element(
            "textarea",
            [
                "id" => $id,
                "name" => $name,
                "required" => $element["question"]["required"],
            ],
            $prevSubmission[$name] ?? ""
        );
        return new Element("div", ["class" => "zetkin-survey-question zetkin-survey-question--multi-line"], $children);
    }

    private static function getOptionsElements($element, $prevSubmission)
    {
        $question = $element["question"];
        $name = $element["id"] . "_options";
        $widgetType = $question["response_config"]["widget_type"];
        $optionChildren = [];
        foreach ($question["options"] ?? [] as $option) {
            $optionId = "zetkin-survey-option-" . $option["id"];
            $optionAttrs = [
                "id" => $optionId,
                "name" => $name . '[]',
                "type" =>  $widgetType,
                "value" => $option["id"],
            ];
            if (in_array($option["id"], $prevSubmission[$name] ?? [])) {
                $optionAttrs["checked"] = true;
            }

            $optionChildren[] = new Element(
                "div",
                [
                    "class" => "zetkin-survey-option"
                ],
                [
                    new Element("input", $optionAttrs),
                    new Element("label", ["for" => $optionId], $option["text"])
                ]
            );
        }
        $children = self::getLabelElements($element);
        $children[] = new Element(
            "ol",
            [
                "class" => "zetkin-survey-options"
            ],
            $optionChildren
        );
        return new Element("div", ["class" => "zetkin-survey-question zetkin-survey-question--$widgetType"], $children);
    }

    private static function getSelectElements($element, $prevSubmission)
    {
        $id = self::INPUT_ID_PREFIX . $element["id"];
        $name = $element["id"] . "_options";
        $question = $element["question"];

        $optionChildren = [
            new Element(
                "option",
                [
                    "value" => ""
                ],
                __("Select an option", "zetkin")
            )
        ];
        foreach ($question["options"] ?? [] as $option) {
            $attrs = [
                "value" => $option["id"]
            ];
            if (in_array($option["id"], $prevSubmission[$name] ?? [])) {
                $attrs["selected"] = true;
            }
            $optionChildren[] = new Element(
                "option",
                $attrs,
                $option["text"]
            );
        }

        $children = self::getLabelElements($element);
        $children[] = new Element(
            "select",
            [
                "class" => "zetkin-survey-select",
                "id" => $id,
                "name" => $name,
            ],
            $optionChildren,
        );
        return new Element("div", ["class" => "zetkin-survey-question zetkin-survey-question--select"], $children);
    }

    private static function getLabelElements($element)
    {
        $children = [];
        $id = self::INPUT_ID_PREFIX . $element["id"];
        $widgetType = $element["question"]["response_config"]["widget_type"] ?? "";
        $isRealLabel = $widgetType !== "checkbox" && $widgetType !== "radio";

        if ($isRealLabel) {
            $children[] = new Element(
                "label",
                [
                    "class" => "zetkin-survey-question-label",
                    "for" => $id,
                ],
                $element["question"]["question"] ?? ""
            );
        } else {
            $children[] = new Element(
                "span",
                [
                    "class" => "zetkin-survey-question-label",
                ],
                $element["question"]["question"] ?? ""
            );
        }

        if ($element["question"]["description"] ?? false) {
            $children[] = new Element(
                "p",
                ["class" => "zetkin-survey-question-description"],
                $element["question"]["description"]
            );
        }

        return $children;
    }

    private static function getPrivacyHTMLElements($zetkinSurvey)
    {
        $organizationName = $zetkinSurvey["organization"]["title"] ?? "this organization";
        $privacyMessage = sprintf(
            __('When you submit this survey, the information you provide will be stored and processed in Zetkin by %s in order to organize activism and in accordance with the Zetkin privacy policy.', 'zetkin'),
            $organizationName
        );
        $children = [
            new Element("p", [], __("Privacy policy", "zetkin")),
            new Element(
                "input",
                ["type" => "checkbox", "id" => "zetkin-survey-privacy-policy", "name" => "privacy.approval", "required" => true],
            ),
            new Element("label", ["for" => "zetkin-survey-privacy-policy"], __("I accept the terms stated below", "zetkin")),
            new Element("p", [], $privacyMessage),
            new Element("p", [], [
                new Element(
                    "a",
                    ["target" => "_blank", "href" => "https://zetkin.org/privacy"],
                    __("Click to read the full Zetkin Privacy Policy", "zetkin")
                )
            ])
        ];
        return new Element("div", ["class" => "zetkin-survey-privacy"], $children);
    }
}
