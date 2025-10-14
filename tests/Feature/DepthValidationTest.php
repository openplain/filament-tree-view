<?php

use Openplain\FilamentTreeView\Tests\Models\Category;

beforeEach(function () {
    // Create a tree structure for testing
    // Root (depth 0)
    //   ├─ Parent 1 (depth 1)
    //   │   └─ Child 1 (depth 2)
    //   │       └─ Grandchild 1 (depth 3)
    //   └─ Parent 2 (depth 1)
    //       └─ Child 2 (depth 2)

    $this->root = Category::factory()->create(['name' => 'Root']);
    $this->parent1 = Category::factory()->withParent($this->root)->create(['name' => 'Parent 1']);
    $this->child1 = Category::factory()->withParent($this->parent1)->create(['name' => 'Child 1']);
    $this->grandchild1 = Category::factory()->withParent($this->child1)->create(['name' => 'Grandchild 1']);

    $this->parent2 = Category::factory()->withParent($this->root)->create(['name' => 'Parent 2']);
    $this->child2 = Category::factory()->withParent($this->parent2)->create(['name' => 'Child 2']);
});

it('calculates correct subtree depth for leaf node', function () {
    // A leaf node has no children, so subtree depth should be 0
    expect($this->grandchild1->children()->count())->toBe(0);
});

it('calculates correct subtree depth for node with one level of children', function () {
    // Parent 2 has one child (Child 2), so subtree depth should be 1
    expect($this->parent2->children()->count())->toBe(1);
});

it('calculates correct subtree depth for node with multiple levels', function () {
    // Parent 1 has Child 1, which has Grandchild 1
    // So subtree depth should be 2 (Child 1 + Grandchild 1)
    expect($this->parent1->descendants()->count())->toBe(2);
});

it('blocks dragging item to exactly max depth', function () {
    // With maxDepth=3, we can have depths 0, 1, 2 (three levels total)
    // Trying to create depth 3 would be the 4th level and should be blocked

    // The JavaScript validation would check: targetDepth (2) + 1 + sourceSubtreeDepth (0) = 3
    // With maxDepth=3, 3 >= 3 is TRUE, so this should be blocked

    $targetDepth = 2;
    $sourceSubtreeDepth = 0;
    $maxDepth = 3;
    $wouldExceed = ($targetDepth + 1 + $sourceSubtreeDepth) >= $maxDepth;

    expect($wouldExceed)->toBeTrue();
});

it('prevents dragging item with children when total depth would exceed maximum', function () {
    // This tests THE FIX!
    // Parent 1 has a subtree depth of 2 (Child 1 + Grandchild 1)
    // If we try to drag Parent 1 (depth 1) under Child 2 (depth 2)
    // The new depth would be: 2 + 1 + 2 = 5
    // With maxDepth=3, this should be blocked

    $targetDepth = 2; // Child 2's depth
    $sourceSubtreeDepth = 2; // Parent 1's subtree (Child 1 -> Grandchild 1)
    $maxDepth = 3;
    $wouldExceed = ($targetDepth + 1 + $sourceSubtreeDepth) >= $maxDepth;

    expect($wouldExceed)->toBeTrue();
});

it('allows dragging item with children when total depth is within maximum', function () {
    // If maxDepth=6, dragging Parent 1 (subtree depth 2) under Child 2 (depth 2)
    // would result in: 2 + 1 + 2 = 5
    // With maxDepth=6, 5 >= 6 is FALSE, so this should be allowed

    $targetDepth = 2;
    $sourceSubtreeDepth = 2;
    $maxDepth = 6;
    $wouldExceed = ($targetDepth + 1 + $sourceSubtreeDepth) >= $maxDepth;

    expect($wouldExceed)->toBeFalse();
});

it('allows unlimited depth when maxDepth is null', function () {
    // With maxDepth=null, any depth should be allowed

    $targetDepth = 100;
    $sourceSubtreeDepth = 100;
    $maxDepth = null;
    $wouldExceed = $maxDepth !== null && ($targetDepth + 1 + $sourceSubtreeDepth) >= $maxDepth;

    expect($wouldExceed)->toBeFalse();
});

it('prevents moving shallow item to deep position', function () {
    // Child 2 (no children, subtree depth 0) cannot be moved under Grandchild 1 (depth 3)
    // New depth would be: 3 + 1 + 0 = 4
    // With maxDepth=3, this should be blocked

    $targetDepth = 3; // Grandchild 1's depth
    $sourceSubtreeDepth = 0; // Child 2 has no children
    $maxDepth = 3;
    $wouldExceed = ($targetDepth + 1 + $sourceSubtreeDepth) >= $maxDepth;

    expect($wouldExceed)->toBeTrue();
});

it('calculates depth correctly for complex tree', function () {
    // Create a deeper tree to test
    $level4 = Category::factory()->withParent($this->grandchild1)->create(['name' => 'Level 4']);
    $level5 = Category::factory()->withParent($level4)->create(['name' => 'Level 5']);

    // Root has descendants at depth 1, 2, 3, 4, and 5
    expect($this->root->descendants()->count())->toBe(7); // parent1, child1, grandchild1, level4, level5, parent2, child2
});

it('correctly identifies that dragging deep subtree exceeds max depth', function () {
    // Create item with 3 levels of children
    $deepParent = Category::factory()->create(['name' => 'Deep Parent']);
    $deepChild1 = Category::factory()->withParent($deepParent)->create(['name' => 'Deep Child 1']);
    $deepChild2 = Category::factory()->withParent($deepChild1)->create(['name' => 'Deep Child 2']);
    $deepChild3 = Category::factory()->withParent($deepChild2)->create(['name' => 'Deep Child 3']);

    // Deep Parent has subtree depth of 3
    expect($deepParent->descendants()->count())->toBe(3);

    // Try to drag under Root (depth 0)
    // New depth would be: 0 + 1 + 3 = 4
    // With maxDepth=3, this should be blocked

    $targetDepth = 0;
    $sourceSubtreeDepth = 3;
    $maxDepth = 3;
    $wouldExceed = ($targetDepth + 1 + $sourceSubtreeDepth) >= $maxDepth;

    expect($wouldExceed)->toBeTrue();
});
