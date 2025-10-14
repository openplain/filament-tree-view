<?php

use Filament\Support\Enums\Alignment;
use Openplain\FilamentTreeView\Fields\IconField;

it('can create icon field', function () {
    $field = IconField::make('is_active');

    expect($field)->toBeInstanceOf(IconField::class);
    expect($field->getName())->toBe('is_active');
});

it('can set custom icons', function () {
    $field = IconField::make('is_active')
        ->icons('heroicon-o-check', 'heroicon-o-x-mark');

    $trueRecord = ['is_active' => true];
    $falseRecord = ['is_active' => false];

    $trueRendered = $field->render($trueRecord);
    $falseRendered = $field->render($falseRecord);

    // The render() method generates SVG, not icon names
    expect($trueRendered)->toContain('<svg');
    expect($falseRendered)->toContain('<svg');
    expect($trueRendered)->toContain('fi-tree-toggle-icon');
    expect($falseRendered)->toContain('fi-tree-toggle-icon');
});

it('can set custom colors', function () {
    $field = IconField::make('is_active')
        ->colors('primary', 'gray');

    $trueRecord = ['is_active' => true];
    $falseRecord = ['is_active' => false];

    $trueRendered = $field->render($trueRecord);
    $falseRendered = $field->render($falseRecord);

    expect($trueRendered)->toContain('fi-color-primary');
    expect($falseRendered)->toContain('fi-color-gray');
});

it('can set end alignment', function () {
    $field = IconField::make('is_active')->alignEnd();

    expect($field->getAlignment())->toBe(Alignment::End);
});

it('has default true and false icons', function () {
    $field = IconField::make('is_active');

    $trueRecord = ['is_active' => true];
    $falseRecord = ['is_active' => false];

    $trueRendered = $field->render($trueRecord);
    $falseRendered = $field->render($falseRecord);

    // Should render SVG icons (actual icon names are not in the rendered output)
    expect($trueRendered)->toContain('<svg');
    expect($falseRendered)->toContain('<svg');
    expect($trueRendered)->toContain('fi-tree-toggle-icon');
    expect($falseRendered)->toContain('fi-tree-toggle-icon');
});

it('has default true and false colors', function () {
    $field = IconField::make('is_active');

    $trueRecord = ['is_active' => true];
    $falseRecord = ['is_active' => false];

    $trueRendered = $field->render($trueRecord);
    $falseRendered = $field->render($falseRecord);

    expect($trueRendered)->toContain('fi-color-success');
    expect($falseRendered)->toContain('fi-color-danger');
});
