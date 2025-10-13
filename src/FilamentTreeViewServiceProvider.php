<?php

namespace Openplain\FilamentTreeView;

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
        // Register JavaScript assets
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    protected function getAssetPackageName(): ?string
    {
        return 'openplain/filament-tree-view';
    }

    protected function getAssets(): array
    {
        return [
            Js::make('filament-tree-view-scripts', __DIR__ . '/../resources/dist/filament-tree-view.js'),
        ];
    }
}
