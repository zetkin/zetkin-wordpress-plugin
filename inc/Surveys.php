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

                $field = substr($key, 0, -8);
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

        $queryArg = self::RESULT_QUERY_ARG_PREFIX . $surveyId;

        // On error, save responses in a transient session
        if (!$response) {
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

    public static function renderSurvey($zetkinSurvey, $attributes, $result = null)
    {
        $textColor = $attributes["textColor"] ?? null;
        $prevSubmission = get_transient(self::RESULT_QUERY_ARG_PREFIX . $zetkinSurvey["id"]);
        if (!$prevSubmission) {
            $prevSubmission = [];
        }

        $redirectUrl = home_url(add_query_arg(null, null)); // Get the current URL

        $surveyElements = $zetkinSurvey["elements"];

        $formElements = [
            new Element(
                "input",
                ["type" => "hidden", "name" => "action", "value" => self::WORDPRESS_ACTION]
            ),
            new Element("input", ["type" => "hidden", "name" => "redirect_url.hidden", "value" => $redirectUrl]),
            new Element("input", ["type" => "hidden", "name" => "survey_id.hidden", "value" => $zetkinSurvey["id"]]),
        ];

        foreach ($surveyElements as $surveyElement) {
            $formElements[] = self::getHTMLElementsForSurveyElement($surveyElement, $prevSubmission, $textColor);
        }

        // Add signature elements, required on all surveys but not in the Zetkin API response.
        $signatureElements = [
            new Element("p", ["class" => "zetkin-survey-signature__description"], "Sign with name and email *")
        ];
        $sigSurveyElements = [
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
        ];

        foreach ($sigSurveyElements as $surveyElement) {
            $signatureElements[] = self::getHTMLElementsForSurveyElement($surveyElement, $prevSubmission, $textColor);
        }
        $formElements[] = new Element("div", ["class" => "zetkin-survey-signature"], $signatureElements);

        $formElements[] = self::getPrivacyHTMLElements($zetkinSurvey, $textColor);

        $buttonStyle = "";
        if (!empty($attributes["buttonColor"])) {
            $buttonColor = $attributes["buttonColor"];
            $buttonStyle .= "background-color:{$buttonColor};";
        }
        if (!empty($attributes["buttonTextColor"])) {
            $buttonTextColor = $attributes["buttonTextColor"];
            $buttonStyle .= "color:{$buttonTextColor};";
        }

        $formElements[] = new Element("button", ["class" => "zetkin-survey-submit zetkin-submit-button", "type" => "submit", "style" => $buttonStyle], __("Submit", "zetkin"));
        if ($result === "error") {
            $formElements[] =
                new Element(
                    "small",
                    ["class" => "zetkin-survey-error"],
                    __("Could not submit your response, please try again later.", "zetkin")
                );
        }

        $spacing = $attributes["spacing"] ?? 0;
        $formStyle = "gap:{$spacing}px";
        $formElement = new Element(
            "form",
            ["class" => "zetkin-survey", "method" => "POST", "action" => admin_url('admin-ajax.php'), "style" => $formStyle],
            $formElements
        );

        if ($zetkinSurvey["title"]) {
            $style = $textColor ? "color:{$textColor};" : "";
            Renderer::renderElement(new Element("h2", ["class" => "zetkin-survey-title", "style" => $style], $zetkinSurvey["title"]));
        }
        Renderer::renderElement($formElement);
    }

    private static function getHTMLElementsForSurveyElement($element, $prevSubmission, $textColor)
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
                return self::getTextAreaElements($element, $prevSubmission, $textColor);
            } else {
                return self::getTextInputElements($element, $prevSubmission, $textColor);
            }
        }

        if ($widgetType === "checkbox" || $widgetType === "radio") {
            return self::getOptionsElements($element, $prevSubmission);
        }

        if ($widgetType === "select") {
            return self::getSelectElements($element, $prevSubmission, $textColor);
        }
    }

    private static function getTextElements($element)
    {
        $children = [];
        $header = $element["text_block"]["header"] ?? "";
        $content = $element["text_block"]["content"] ?? "";
        if ($header) {
            $children[] = new Element(
                "p",
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

    private static function getTextInputElements($element, $prevSubmission, $textColor)
    {
        $id = self::INPUT_ID_PREFIX . $element["id"];
        $children = self::getLabelElements($element);
        $name = $element["id"] . "_text";
        $style = $textColor ? "border-color:{$textColor};" : "";
        $children[] = new Element(
            "input",
            [
                "id" => $id,
                "class" => "zetkin-survey-question__input zetkin-input",
                "name" => $name,
                "type" => $element["question"]["response_config"]["type"] ?? "text",
                "required" => $element["question"]["required"],
                "value" => $prevSubmission[$name] ?? "",
                "style" => $style,
            ]
        );
        return new Element("div", ["class" => "zetkin-survey-question zetkin-survey-question--single-line"], $children);
    }

    private static function getTextAreaElements($element, $prevSubmission, $textColor)
    {
        $id = self::INPUT_ID_PREFIX . $element["id"];
        $children = self::getLabelElements($element);
        $name = $element["id"] . "_text";
        $style = $textColor ? "border-color:{$textColor};" : "";
        $children[] = new Element(
            "textarea",
            [
                "id" => $id,
                "class" => "zetkin-survey-question__textarea zetkin-input",
                "name" => $name,
                "required" => $element["question"]["required"],
                "rows" => 4,
                "style" => $style,
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
                "li",
                ["class" => "zetkin-survey-question__option"],
                [
                    new Element(
                        "label",
                        [
                            "for" => $optionId
                        ],
                        [
                            new Element("span", ["class" => "zetkin-survey-question__option-focus-circle"]),
                            new Element("input", $optionAttrs),
                            new Element("span", [], $option["text"])
                        ]
                    )
                ]
            );
        }
        $children = self::getLabelElements($element);
        $children[] = new Element(
            "ol",
            [
                "class" => "zetkin-survey-question__options"
            ],
            $optionChildren
        );
        return new Element("div", ["class" => "zetkin-survey-question zetkin-survey-question--$widgetType"], $children);
    }

    private static function getSelectElements($element, $prevSubmission, $textColor)
    {
        $id = self::INPUT_ID_PREFIX . $element["id"];
        $name = $element["id"] . "_options";
        $question = $element["question"];
        $style = $textColor ? "border-color:{$textColor};color:{$textColor};" : "";

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
                "class" => "zetkin-survey-question__select zetkin-select",
                "id" => $id,
                "name" => $name,
                "style" => $style,
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
                    "class" => "zetkin-survey-question__label",
                    "for" => $id,
                ],
                $element["question"]["question"] ?? ""
            );
        } else {
            $children[] = new Element(
                "span",
                [
                    "class" => "zetkin-survey-question__label",
                ],
                $element["question"]["question"] ?? ""
            );
        }

        if ($element["question"]["description"] ?? false) {
            $children[] = new Element(
                "p",
                ["class" => "zetkin-survey-question__description"],
                $element["question"]["description"]
            );
        }

        return $children;
    }

    private static function getPrivacyHTMLElements($zetkinSurvey, $textColor)
    {
        $organizationName = $zetkinSurvey["organization"]["title"] ?? "this organization";
        $privacyMessage = sprintf(
            __('When you submit this survey, the information you provide will be stored and processed in Zetkin by %s in order to organize activism and in accordance with the Zetkin privacy policy.', 'zetkin'),
            $organizationName
        );
        $style = $textColor ? "color:{$textColor};" : "";
        $children = [
            new Element("p", ["class" => "zetkin-survey-privacy__title"], __("Privacy policy", "zetkin")),
            new Element("label", ["for" => "zetkin-survey-privacy-policy", "class" => "zetkin-survey-question__option"], [
                new Element(
                    "input",
                    [
                        "type" => "checkbox",

                        "id" => "zetkin-survey-privacy-policy",
                        "name" => "privacy.approval",
                        "required" => true
                    ],
                ),
                new Element("span", [], __("I accept the terms stated below *", "zetkin"))
            ]),
            new Element("div", ["class" => "zetkin-survey-privacy__details"], [
                new Element("p", [], $privacyMessage),
                new Element("p", [], [
                    new Element(
                        "a",
                        ["target" => "_blank", "href" => "https://zetkin.org/privacy", "style" => $style],
                        __("Click to read the full Zetkin Privacy Policy", "zetkin")
                    )
                ])
            ])
        ];
        return new Element("div", ["class" => "zetkin-survey-privacy"], $children);
    }
}
