# Changelog

All notable changes to `laravel-markdown-for-agents` will be documented in this file.

## 1.0.0 - 2026-11-09

Initial release.

Serve clean Markdown to AI agents: a request with `Accept: text/markdown` gets the page's main content converted to Markdown, while browsers keep the HTML.

- `NegotiateMarkdown` middleware: converts only `GET` `200` `text/html` responses when the client prefers `text/markdown`; adds `Vary: Accept` to every HTML response.
- Converts the first `<main>` (configurable `element`, falls back to `<body>`), drops scripts, styles, SVG, forms, nav and footer (configurable `remove_nodes`).
- YAML front matter with the page title and URL (`front_matter`).
- Optional `Content-Signal` header (`content_signal`, contentsignals.org).
- `except` path patterns and an `enabled` switch.
- `MarkdownConverter::convert()` for converting HTML yourself.

Register it outside any full-page cache so the cache only ever stores HTML. Laravel 11–13, PHP 8.2+.
