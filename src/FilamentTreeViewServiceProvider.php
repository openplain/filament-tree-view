<?php

namespace Openplain\FilamentTreeView;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTreeViewServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-tree-view';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasTranslations()
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        // Register assets using Filament v4 pattern
        FilamentAsset::register([
            Css::make('filament-tree-view-styles', __DIR__.'/../resources/dist/filament-tree-view.css'),
            Js::make('filament-tree-view-scripts', __DIR__.'/../resources/dist/filament-tree-view.js'),
        ], package: 'openplain/filament-tree-view');
    }
}
