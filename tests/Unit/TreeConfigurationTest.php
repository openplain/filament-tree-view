<?php

use Openplain\FilamentTreeView\Fields\TextField;
use Openplain\FilamentTreeView\Tree;

it('can set max depth', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire)->maxDepth(5);

    expect($tree->getMaxDepth())->toBe(5);
});

it('can set unlimited depth with null', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire)->maxDepth(null);

    expect($tree->getMaxDepth())->toBeNull();
});

it('has default max depth of 10', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire);

    expect($tree->getMaxDepth())->toBe(10);
});

it('can enable collapsible', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire)->collapsible(true);

    expect($tree->isCollapsible())->toBeTrue();
});

it('can disable collapsible', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire)->collapsible(false);

    expect($tree->isCollapsible())->toBeFalse();
});

it('is collapsible by default', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire);

    expect($tree->isCollapsible())->toBeTrue();
});

it('can set collapsed state', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire)->collapsed();

    expect($tree->isDefaultExpanded())->toBeFalse();
});

it('is expanded by default', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire);

    expect($tree->isDefaultExpanded())->toBeTrue();
});

it('can enable auto save', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire)->autoSave();

    expect($tree->isAutoSave())->toBeTrue();
});

it('is manual save by default', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire);

    expect($tree->isAutoSave())->toBeFalse();
});

it('can set fields', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $tree = Tree::make($livewire)->fields([
        TextField::make('name'),
        TextField::make('description'),
    ]);

    expect($tree->getFields())->toHaveCount(2);
});

it('can set record actions', function () {
    $livewire = Mockery::mock('Openplain\FilamentTreeView\Contracts\HasTree');
    $livewire->shouldReceive('cacheAction')->andReturn(null);

    $tree = Tree::make($livewire)->recordActions([
        Filament\Actions\EditAction::make(),
        Filament\Actions\DeleteAction::make(),
    ]);

    expect($tree->getRecordActions())->toHaveCount(2);
});
