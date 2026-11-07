<?php

namespace App\Services\Reports;

class ReportContent
{
    /** Keep formatting, but never accept executable markup, external resources or font overrides. */
    public static function clean(string $html): string
    {
        if (trim($html) === '') { return ''; }
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $render = function ($node) use (&$render): string {
            if ($node instanceof \DOMText) { return htmlspecialchars($node->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
            if (! $node instanceof \DOMElement) { return ''; }
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg', 'math', 'img', 'input', 'textarea'], true)) { return ''; }
            $children = '';
            foreach ($node->childNodes as $child) { $children .= $render($child); }
            if ($tag === 'br') { return '<br>'; }
            if (in_array($tag, ['b', 'strong', 'i', 'em', 'u'], true)) { return '<'.$tag.'>'.$children.'</'.$tag.'>'; }
            if (in_array($tag, ['p', 'div', 'h1', 'h2', 'h3', 'li'], true)) {
                $align = $node->getAttribute('align');
                if (preg_match('/(?:^|;)\s*text-align\s*:\s*(left|center|right|justify)\s*(?:;|$)/i', $node->getAttribute('style'), $match)) { $align = strtolower($match[1]); }
                if (! in_array($align, ['left', 'center', 'right', 'justify'], true)) { $align = 'left'; }
                if (in_array($tag, ['h1', 'h2', 'h3'], true)) { $children = '<b>'.$children.'</b>'; }
                return '<p align="'.$align.'">'.$children.'</p>';
            }
            return $children;
        };
        return $render($dom->getElementsByTagName('body')->item(0));
    }
}
