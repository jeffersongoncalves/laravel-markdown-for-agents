<?php

declare(strict_types=1);

namespace JeffersonGoncalves\MarkdownForAgents;

use DOMDocument;
use League\HTMLToMarkdown\HtmlConverter;

class MarkdownConverter
{
    /**
     * The configured content element of an HTML page as Markdown, with an
     * optional title/url front matter block.
     */
    public static function convert(string $html, ?string $url = null): string
    {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true); // HTML5 tags (<main>, <svg>...) warn in libxml
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $element = (string) config('markdown-for-agents.element', 'main');
        $node = $dom->getElementsByTagName($element)->item(0) ?? $dom->getElementsByTagName('body')->item(0);
        $content = $node !== null ? (string) $dom->saveHTML($node) : $html;

        $markdown = (new HtmlConverter([
            'header_style' => 'atx',
            'strip_tags' => true,
            'remove_nodes' => implode(' ', (array) config('markdown-for-agents.remove_nodes', [])),
            'hard_break' => true,
        ]))->convert($content);

        // Layout wrappers leave whitespace-only lines behind: trim line ends
        // and collapse blank runs to one empty line.
        $markdown = trim((string) preg_replace(["/[ \t]+$/m", "/\n{3,}/"], ['', "\n\n"], $markdown))."\n";

        if (! config('markdown-for-agents.front_matter', true)) {
            return $markdown;
        }

        $title = trim((string) $dom->getElementsByTagName('title')->item(0)?->textContent);
        // json_encode() output is a valid YAML scalar, so titles with ":" stay safe.
        $front = "---\ntitle: ".json_encode($title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";

        if ($url !== null) {
            $front .= 'url: '.$url."\n";
        }

        return $front."---\n\n".$markdown;
    }
}
