<?php

namespace App\Utility;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlHelper
{
    /**
     * The tags a formatted setting may keep. Everything else is either
     * unwrapped (a harmless container) or dropped with its contents (a script or
     * a frame).
     *
     * @var array<int, string>
     */
    const ALLOWED_TAGS = [
        'p', 'br', 'div', 'span', 'b', 'strong', 'i', 'em', 'u',
        'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4',
    ];

    /**
     * Tags whose contents are dangerous and go with them.
     *
     * @var array<int, string>
     */
    const DROPPED_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'svg'];

    /**
     * The only style properties a formatted setting keeps.
     *
     * `font-family` is never allowed: every document is set in the typeface its
     * own stylesheet decides, not in whatever the editor was showing.
     *
     * @var array<int, string>
     */
    const ALLOWED_STYLES = [
        'font-size', 'color', 'text-align', 'font-weight',
        'font-style', 'text-decoration', 'line-height',
        'letter-spacing', 'text-transform',
    ];

    /**
     * Clean rich text down to what a document may contain.
     *
     * @param  string|null  $html
     * @return string
     */
    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
            . '<body>' . $html . '</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMNode) {
            return '';
        }

        static::cleanChildren($body);

        $output = '';

        foreach ($body->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    /**
     * Whether the given text carries no tags at all — plain text somebody typed
     * before a rich editor wrote the value.
     */
    public static function isPlainText(string $html): bool
    {
        return strip_tags($html) === $html;
    }

    /**
     * The same text, ready to print inside an HTML block: escaped, with its own
     * line breaks kept.
     */
    public static function toParagraphs(string $text): string
    {
        return nl2br(e($text));
    }

    /**
     * Walk a node's children, dropping what is not allowed and sanitizing the
     * rest.
     */
    private static function cleanChildren(DOMNode $node): void
    {
        // Iterate over a copy: unwrapping a node mutates the live list.
        $children = [];

        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->nodeName);

                if (in_array($tag, static::DROPPED_TAGS, true)) {
                    $node->removeChild($child);
                    continue;
                }

                if (! in_array($tag, static::ALLOWED_TAGS, true)) {
                    static::cleanChildren($child);
                    static::unwrap($child);
                    continue;
                }

                static::cleanAttributes($child);
            }

            static::cleanChildren($child);
        }
    }

    /**
     * Keep only a `style` attribute on an element, and only its allowed
     * declarations.
     */
    private static function cleanAttributes(DOMElement $element): void
    {
        $style = $element->hasAttribute('style') ? $element->getAttribute('style') : null;

        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->nodeName;
        }

        foreach ($attributes as $name) {
            $element->removeAttribute($name);
        }

        if ($style === null) {
            return;
        }

        $clean = static::cleanStyle($style);

        if ($clean !== '') {
            $element->setAttribute('style', $clean);
        }
    }

    /**
     * Keep only the allowed declarations, and no `url()` anywhere in them.
     */
    private static function cleanStyle(string $style): string
    {
        $kept = [];

        foreach (explode(';', $style) as $declaration) {
            if (strpos($declaration, ':') === false) {
                continue;
            }

            [$property, $value] = explode(':', $declaration, 2);

            $property = strtolower(trim($property));
            $value = trim($value);

            if (! in_array($property, static::ALLOWED_STYLES, true)) {
                continue;
            }

            if (stripos($value, 'url(') !== false || stripos($value, 'expression') !== false) {
                continue;
            }

            $value = str_replace(['"', "'", ';'], '', $value);

            if ($value !== '') {
                $kept[] = $property . ': ' . $value;
            }
        }

        return implode('; ', $kept);
    }

    /**
     * Replace an element with its own children.
     */
    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
