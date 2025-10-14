<?php

use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Openplain\FilamentTreeView\Fields\TextField;

it('can create text field', function () {
    $field = TextField::make('name');

    expect($field)->toBeInstanceOf(TextField::class);
    expect($field->getName())->toBe('name');
});

it('can set color', function () {
    $field = TextField::make('name')->color('primary');
    $record = ['name' => 'Test'];
    $rendered = $field->render($record);

    expect($rendered)->toContain('text-primary-600');
});

it('can set font weight', function () {
    $field = TextField::make('name')->weight(FontWeight::Bold);
    $record = ['name' => 'Test'];
    $rendered = $field->render($record);

    expect($rendered)->toContain('font-bold');
});

it('can set character limit', function () {
    $field = TextField::make('name')->limit(5);
    $record = ['name' => 'Very long text'];
    $rendered = $field->render($record);

    expect($rendered)->toContain('Very ...');
});

it('can set start alignment', function () {
    $field = TextField::make('name')->alignStart();

    expect($field->getAlignment())->toBe(Alignment::Start);
});

it('can set center alignment', function () {
    $field = TextField::make('name')->alignCenter();

    expect($field->getAlignment())->toBe(Alignment::Center);
});

it('can set end alignment', function () {
    $field = TextField::make('name')->alignEnd();

    expect($field->getAlignment())->toBe(Alignment::End);
});

it('has null alignment by default', function () {
    $field = TextField::make('name');

    expect($field->getAlignment())->toBeNull();
});

it('can enable dim when inactive', function () {
    $field = TextField::make('name')->dimWhenInactive();
    $activeRecord = ['name' => 'Test', 'is_active' => true];
    $inactiveRecord = ['name' => 'Test', 'is_active' => false];

    $activeRendered = $field->render($activeRecord);
    $inactiveRendered = $field->render($inactiveRecord);

    expect($activeRendered)->not->toContain('opacity: 0.4');
    expect($inactiveRendered)->toContain('opacity: 0.4');
});

it('can set custom dim condition', function () {
    $field = TextField::make('name')->dimWhen('status', value: 'inactive');
    $activeRecord = ['name' => 'Test', 'status' => 'active'];
    $inactiveRecord = ['name' => 'Test', 'status' => 'inactive'];

    $activeRendered = $field->render($activeRecord);
    $inactiveRendered = $field->render($inactiveRecord);

    expect($activeRendered)->not->toContain('opacity: 0.4');
    expect($inactiveRendered)->toContain('opacity: 0.4');
});
