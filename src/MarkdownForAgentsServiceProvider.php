<?php

declare(strict_types=1);

namespace JeffersonGoncalves\MarkdownForAgents;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MarkdownForAgentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-markdown-for-agents')
            ->hasConfigFile('markdown-for-agents');
    }
}
