<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Off: the middleware passes every response through untouched.
    |
    */
    'enabled' => (bool) env('MARKDOWN_FOR_AGENTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Content element
    |--------------------------------------------------------------------------
    |
    | Tag name of the element converted to Markdown (the first one on the
    | page). Falls back to <body> when the page has none. `main` keeps the
    | navigation, header and footer of a typical layout out of the output.
    |
    */
    'element' => 'main',

    /*
    |--------------------------------------------------------------------------
    | Removed nodes
    |--------------------------------------------------------------------------
    |
    | Elements dropped (with their content) before conversion.
    |
    */
    'remove_nodes' => ['script', 'style', 'noscript', 'svg', 'template', 'form', 'button', 'iframe', 'nav', 'footer'],

    /*
    |--------------------------------------------------------------------------
    | Front matter
    |--------------------------------------------------------------------------
    |
    | Prepend a YAML front matter block with the page <title> and URL.
    |
    */
    'front_matter' => true,

    /*
    |--------------------------------------------------------------------------
    | Content-Signal
    |--------------------------------------------------------------------------
    |
    | Optional `Content-Signal` header on Markdown responses (contentsignals.org),
    | e.g. "search=yes, ai-input=yes, ai-train=no". Keep it in sync with the
    | Content-Signal line of your robots.txt. null sends no header.
    |
    */
    'content_signal' => env('MARKDOWN_FOR_AGENTS_CONTENT_SIGNAL'),

    /*
    |--------------------------------------------------------------------------
    | Excluded paths
    |--------------------------------------------------------------------------
    |
    | Request path patterns (Request::is() syntax) never converted, e.g.
    | ['admin', 'admin/*'].
    |
    */
    'except' => [],

];
