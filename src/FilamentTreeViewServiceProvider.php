<?php

namespace Openplain\FilamentTreeView;

use Illuminate\Support\ServiceProvider;

class FilamentTreeViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-tree-view');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/filament-tree-view.php' => config_path('filament-tree-view.php'),
        ], 'filament-tree-view-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-tree-view'),
        ], 'filament-tree-view-views');

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Commands will be registered here
            ]);
        }
    }

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-tree-view.php',
            'filament-tree-view'
        );
    }
}
