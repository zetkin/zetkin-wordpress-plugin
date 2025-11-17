<?php

namespace Zetkin\ZetkinWordPressPlugin\HTML;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Renderer
{
    /**
     * Echo an HTML element from the provided config.
     * Escapes all input.
     * 
     * @param string $tag
     * @param array $attrs
     * @param string|array $children
     */
    public static function renderElement(Element $element)
    {
        $tag = $element->tag;
        $attrs = $element->attrs;
        $children = $element->children;

        $attrString = "";
        foreach ($attrs as $attr => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $attrString .= " " . $attr;
                }
            } else {
                $attrString .= " " . $attr . '="' . esc_attr($value) . '"';
            }
        }
        $attrString = trim($attrString);
        if ($attrString) {
            echo "<$tag " . $attrString . ">";
        } else {
            echo "<$tag>";
        }
        if ($tag === "img" || $tag === "input") {
            return;
        }
        if (is_string($children)) {
            echo esc_html($children);
            echo "</$tag>\n";
            return;
        }
        foreach ($children as $child) {
            if ($child) {
                self::renderElement($child);
            }
        }
        echo "</$tag>\n";
    }
}
