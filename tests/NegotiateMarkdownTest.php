<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use JeffersonGoncalves\MarkdownForAgents\MarkdownConverter;
use JeffersonGoncalves\MarkdownForAgents\Tests\TestCase;

const MARKDOWN = ['Accept' => 'text/markdown'];
const BROWSER = ['Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'];

it('answers Accept: text/markdown with the main element as markdown', function () {
    $response = $this->get('/page', MARKDOWN)
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertHeaderMissing('Content-Signal');

    expect($response->getContent())->toBe("---\ntitle: \"Docs: Intro\"\nurl: http://localhost/page\n---\n\n# Hello\n\nOlá [link](/x)\n")
        ->and($response->headers->get('Vary'))->toContain('Accept');
});

it('keeps HTML for browsers and marks it as varying by Accept', function () {
    $response = $this->get('/page', BROWSER)
        ->assertOk()
        ->assertSee('<main>', false);

    expect($response->headers->get('Content-Type'))->toStartWith('text/html')
        ->and($response->headers->get('Vary'))->toContain('Accept');
});

it('prefers HTML when the client accepts anything', function () {
    expect($this->get('/page', ['Accept' => '*/*'])->headers->get('Content-Type'))->toStartWith('text/html');
});

it('serves markdown when it outranks HTML by quality', function () {
    $this->get('/page', ['Accept' => 'text/html;q=0.5, text/markdown'])
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
});

it('sends the configured Content-Signal header', function () {
    config(['markdown-for-agents.content_signal' => 'search=yes, ai-input=yes, ai-train=no']);

    $this->get('/page', MARKDOWN)->assertHeader('Content-Signal', 'search=yes, ai-input=yes, ai-train=no');
});

it('leaves non-200, non-GET and non-HTML responses alone', function () {
    $this->get('/missing', MARKDOWN)->assertNotFound()->assertSee('<main>', false);
    $this->post('/page', [], MARKDOWN)->assertOk()->assertSee('<main>', false);
    $this->get('/json', MARKDOWN)->assertOk()->assertJson(['ok' => true]);
});

it('skips excluded paths', function () {
    config(['markdown-for-agents.except' => ['admin/*']]);

    $this->get('/admin/page', MARKDOWN)->assertSee('<main>', false);
    $this->get('/page', MARKDOWN)->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
});

it('does nothing when disabled', function () {
    config(['markdown-for-agents.enabled' => false]);

    $response = $this->get('/page', MARKDOWN)->assertSee('<main>', false);

    expect((string) $response->headers->get('Vary'))->not->toContain('Accept');
});

it('can drop the front matter', function () {
    config(['markdown-for-agents.front_matter' => false]);

    expect(MarkdownConverter::convert(TestCase::PAGE, 'https://example.test'))->toBe("# Hello\n\nOlá [link](/x)\n");
});

it('falls back to the body when the page has no content element', function () {
    config(['markdown-for-agents.front_matter' => false]);

    expect(MarkdownConverter::convert('<html><body><h2>Only body</h2><p>text</p></body></html>'))
        ->toBe("## Only body\n\ntext\n");
});

it('publishes the config under the markdown-for-agents-config tag', function () {
    expect(ServiceProvider::pathsToPublish(null, 'markdown-for-agents-config'))->not->toBeEmpty();
});

it('converts a configured element and collapses layout whitespace', function () {
    config(['markdown-for-agents.front_matter' => false, 'markdown-for-agents.element' => 'article']);

    $html = "<body><main><p>skip</p></main><article>\n  <div>\n\n\n  </div><p>Kept</p>\n\n\n<p>Next</p></article></body>";

    expect(MarkdownConverter::convert($html))->toBe("Kept\n\nNext\n");
});
