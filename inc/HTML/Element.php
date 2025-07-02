<?php

namespace Zetkin\ZetkinWordPressPlugin\HTML;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Element {
    public $tag;
    public $attrs;
    public $children;

    public function __construct(string $tag, array $attrs, string|array $children = "")
    {
        $this->tag = $tag;
        $this->attrs = $attrs;
        $this->children = $children;
    }
}
