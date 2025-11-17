<?php

namespace Zetkin\ZetkinWordPressPlugin;

use Zetkin\ZetkinWordPressPlugin\HTML\Element;
use Zetkin\ZetkinWordPressPlugin\HTML\Renderer;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class JoinForms
{
    const WORDPRESS_ACTION = "zetkin_join_form";
    const RESULT_QUERY_ARG_PREFIX = "zetkin_join_";

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
        $joinFormId = $_POST["join_form_id_hidden"];
        $redirectUrl = $_POST["redirect_url_hidden"];

        // Used in a transient session to display previous input values on error
        $allFields = [];

        foreach ($_POST as $key => $value) {
            if ($key !== "action" && $key !== "redirect_url_hidden" && $key !== "join_form_id_hidden") {
                $allFields[$key] = $value;
            }
        }

        $response = ZetkinAPI::submitJoinForm(
            $joinFormId,
            $allFields,
        );

        $queryArg = self::RESULT_QUERY_ARG_PREFIX . $joinFormId;

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

    public static function renderJoinForm($form, $attributes, $result = null)
    {
        $prevSubmission = get_transient(self::RESULT_QUERY_ARG_PREFIX . $form["id"]);
        if (!$prevSubmission) {
            $prevSubmission = [];
        }

        $redirectUrl = home_url(add_query_arg(null, null)); // Get the current URL

        $formElements = [
            new Element(
                "input",
                ["type" => "hidden", "name" => "action", "value" => self::WORDPRESS_ACTION]
            ),
            new Element("input", ["type" => "hidden", "name" => "redirect_url.hidden", "value" => $redirectUrl]),
            new Element("input", ["type" => "hidden", "name" => "join_form_id.hidden", "value" => $form["id"]]),
        ];
        foreach ($form["fields"] as $field) {
            $formElements[] = self::getHTMLElementsForField($field, $prevSubmission[$field["slug"]] ?? "", $attributes);
        }

        $buttonStyle = "";
        if (!empty($attributes["buttonColor"])) {
            $buttonColor = $attributes["buttonColor"];
            $buttonStyle .= "background-color:{$buttonColor};";
        }
        if (!empty($attributes["buttonTextColor"])) {
            $buttonTextColor = $attributes["buttonTextColor"];
            $buttonStyle .= "color:{$buttonTextColor};";
        }

        $formElements[] =  new Element("button", ["class" => "zetkin-join-form-submit zetkin-submit-button", "type" => "submit", "style" => $buttonStyle], __("Submit", "zetkin"));
        if ($result === "error") {
            $formElements[] =
                new Element(
                    "small",
                    ["class" => "zetkin-join-form-error"],
                    __("Could not sign up, please try again later.", "zetkin")
                );
        }

        $spacing = $attributes["spacing"] ?? 0;
        $formStyle = "gap:{$spacing}px;";

        Renderer::renderElement(new Element("form", ["class" => "zetkin-join-form", "method" => "POST", "action" => admin_url('admin-ajax.php'), "style" => $formStyle], $formElements));
    }

    private static function getHTMLElementsForField($field, $value, $attributes)
    {
        $textColor = $attributes["textColor"] ?? null;
        $selectStyle = $textColor ? "color:{$textColor};border-color:{$textColor};" : "";
        if ($field["slug"] === "gender") {
            $label = __("Gender", "zetkin");
            return new Element("div", ["class" => "zetkin-join-form-input"], [
                new Element("label", ["for" => $field["slug"]], $label),
                new Element("select", ["id" => $field["slug"], "class" => "zetkin-select", "name" => $field["slug"], "style" => $selectStyle], [
                    new Element("option", ["value" => "unspecified", "selected" => $value === "unspecified"], __("Unspecified", "zetkin")),
                    new Element("option", ["value" => "m", "selected" => $value === "m"], __("Male", "zetkin")),
                    new Element("option", ["value" => "f", "selected" => $value === "f"], __("Female", "zetkin")),
                    new Element("option", ["value" => "o", "selected" => $value === "o"], __("Other", "zetkin")),
                ])
            ]);
        }

        $inputStyle = $textColor ? "border-color:{$textColor};" : "";

        $label = ucwords(strtolower(preg_replace("/_+/", " ", $field["slug"])));
        return new Element("div", ["class" => "zetkin-join-form-input"], [
            new Element("label", ["for" => $field["slug"]], $label),
            new Element("input", ["id" => $field["slug"], "class" => "zetkin-input", "name" => $field["slug"], "value" => $value, "style" => $inputStyle], [])
        ]);
    }
}
