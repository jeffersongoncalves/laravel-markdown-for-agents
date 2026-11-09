<?php

declare(strict_types=1);

namespace JeffersonGoncalves\MarkdownForAgents\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JeffersonGoncalves\MarkdownForAgents\MarkdownConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * A client that prefers `Accept: text/markdown` gets the page as Markdown;
 * everyone else keeps the HTML. Every HTML response gets `Vary: Accept`.
 *
 * Register it OUTSIDE any full-page cache (outermost in the `web` group):
 * it converts whatever HTML the stack produced, so the cache only ever
 * stores HTML and a Markdown body can never be served to a browser.
 */
class NegotiateMarkdown
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('markdown-for-agents.enabled', true)
            || ! $request->isMethod('GET')
            || ! str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')
            || $request->is(...(array) config('markdown-for-agents.except', []))) {
            return $response;
        }

        // The same URL answers HTML or Markdown depending on Accept.
        $response->setVary('Accept', false);

        $html = $response->getContent();

        if ($response->getStatusCode() !== 200 || $html === false
            || $request->prefers(['text/html', 'text/markdown']) !== 'text/markdown') {
            return $response;
        }

        $response->setContent(MarkdownConverter::convert($html, $request->url()));
        $response->headers->set('Content-Type', 'text/markdown; charset=utf-8');
        $response->headers->remove('Content-Length');
        $response->headers->remove('ETag');

        $signal = config('markdown-for-agents.content_signal');

        if (is_string($signal) && $signal !== '') {
            $response->headers->set('Content-Signal', $signal);
        }

        return $response;
    }
}
