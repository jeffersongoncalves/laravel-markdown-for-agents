<?php

declare(strict_types=1);

namespace JeffersonGoncalves\MarkdownForAgents\Tests;

use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\MarkdownForAgents\Http\Middleware\NegotiateMarkdown;
use JeffersonGoncalves\MarkdownForAgents\MarkdownForAgentsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public const PAGE = '<!doctype html><html><head><title>Docs: Intro</title></head><body>'
        .'<nav>menu</nav><main><h1>Hello</h1><p>Olá <a href="/x">link</a></p><script>track()</script></main>'
        .'<footer>footer</footer></body></html>';

    protected function getPackageProviders($app): array
    {
        return [MarkdownForAgentsServiceProvider::class];
    }

    protected function defineRoutes($router): void
    {
        Route::middleware(NegotiateMarkdown::class)->group(function (): void {
            Route::get('/page', fn () => response(self::PAGE));
            Route::get('/admin/page', fn () => response(self::PAGE));
            Route::get('/missing', fn () => response(self::PAGE, 404));
            Route::get('/json', fn () => response()->json(['ok' => true]));
            Route::post('/page', fn () => response(self::PAGE));
        });
    }
}
