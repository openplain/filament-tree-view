<?php

use Openplain\FilamentTreeView\Tests\Models\Category;

it('can get parent relationship', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();

    expect($child->parent->id)->toBe($parent->id);
});

it('can get children relationship', function () {
    $parent = Category::factory()->create();
    $child1 = Category::factory()->withParent($parent)->create();
    $child2 = Category::factory()->withParent($parent)->create();

    expect($parent->children)->toHaveCount(2);
    expect($parent->children->pluck('id'))->toContain($child1->id, $child2->id);
});

it('can get root nodes', function () {
    $root1 = Category::factory()->create(['name' => 'Root 1']);
    $root2 = Category::factory()->create(['name' => 'Root 2']);
    $child = Category::factory()->withParent($root1)->create(['name' => 'Child of Root 1']);

    // Get only root nodes (where parent_id is null)
    $roots = Category::whereNull('parent_id')->get();

    expect($roots)->toHaveCount(2);
    expect($roots->pluck('id'))->toContain($root1->id, $root2->id);
    expect($roots->pluck('id'))->not->toContain($child->id);
});

it('can get ancestors', function () {
    $grandparent = Category::factory()->create();
    $parent = Category::factory()->withParent($grandparent)->create();
    $child = Category::factory()->withParent($parent)->create();

    $ancestors = $child->ancestors()->get();

    expect($ancestors)->toHaveCount(2);
    expect($ancestors->pluck('id'))->toContain($grandparent->id, $parent->id);
});

it('can get descendants', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();
    $grandchild = Category::factory()->withParent($child)->create();

    $descendants = $parent->descendants()->get();

    expect($descendants)->toHaveCount(2);
    expect($descendants->pluck('id'))->toContain($child->id, $grandchild->id);
});

it('can get leaves', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();
    $leaf = Category::factory()->withParent($child)->create();

    // A leaf is a node with no children
    // Get IDs of categories that have no children
    $leaves = Category::all()->filter(fn ($node) => $node->children->isEmpty());

    expect($leaves)->toHaveCount(1);
    expect($leaves->first()->id)->toBe($leaf->id);
});

it('has correct parent key name', function () {
    $category = new Category;

    expect($category->getParentKeyName())->toBe('parent_id');
});

it('has correct local key name', function () {
    $category = new Category;

    expect($category->getLocalKeyName())->toBe('id');
});

it('has correct depth name', function () {
    $category = new Category;

    expect($category->getDepthName())->toBe('depth');
});

it('has correct children key name', function () {
    $category = new Category;

    expect($category->getChildrenKeyName())->toBe('children');
});

it('has null as default parent value', function () {
    $category = new Category;

    expect($category->getParentKeyDefaultValue())->toBeNull();
});

it('cascades delete to descendants', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();
    $grandchild = Category::factory()->withParent($child)->create();

    $parent->delete();

    expect(Category::find($child->id))->toBeNull();
    expect(Category::find($grandchild->id))->toBeNull();
});
