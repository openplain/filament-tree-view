<?php

namespace Openplain\FilamentTreeView\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Openplain\FilamentTreeView\Tree;

trait InteractsWithTree
{
    protected Tree $tree;

    protected bool $hasTreeModalRendered = false;

    protected bool $shouldMountInteractsWithTree = false;

    /**
     * @var array<string, mixed>
     */
    public array $treeState = [];

    public ?array $selectedRecords = [];

    /**
     * Snapshot of mountedActions captured during the boot phase, before
     * bootedInteractsWithActions() can clear them. Tree record actions are not
     * yet in cachedActions when bootedInteractsWithActions() runs (it comes from a
     * parent class and therefore executes first), so caching fails and mountedActions
     * is reset to []. bootedInteractsWithTree() uses this snapshot to restore the
     * mounted action state after the tree has registered its actions.
     *
     * @var array<array<string, mixed>>
     */
    protected array $preMountedActionsSnapshot = [];

    public function bootInteractsWithTree(): void
    {
        // Runs during Livewire's boot phase, before any bootedXxx() hooks fire.
        // Capture mountedActions now so bootedInteractsWithTree() can restore them
        // if bootedInteractsWithActions() cleared them.
        $this->preMountedActionsSnapshot = $this->mountedActions ?? [];
    }

    public function bootedInteractsWithTree(): void
    {
        $this->tree = $this->tree($this->makeTree());

        // bootedInteractsWithActions() runs before this method and may have cleared
        // mountedActions when it failed to resolve tree actions (not yet registered).
        // Restore from the snapshot captured in bootInteractsWithTree().
        $actionsToRestore = ! empty($this->mountedActions)
            ? $this->mountedActions
            : $this->preMountedActionsSnapshot;

        if (filled($actionsToRestore) && ! empty($this->cacheMountedActions($actionsToRestore))) {
            $this->mountedActions = $actionsToRestore;
        } else {
            $this->cacheMountedActions($this->mountedActions);
        }
    }

    public function tree(Tree $tree): Tree
    {
        return $tree;
    }

    public function getTree(): Tree
    {
        return $this->tree;
    }

    protected function makeTree(): Tree
    {
        return Tree::make($this);
    }

    public function getTreeQuery(): Builder|Relation
    {
        return $this->getTree()->getQuery();
    }

    public function getTreeRecords(): array
    {
        $query = $this->getTree()->getQuery();
        $model = $query->getModel();
        $orderKeyName = $model->getOrderKeyName();

        // Get all records ordered, using the configured query
        $nodes = (clone $query)
            ->orderBy($orderKeyName)
            ->orderBy('id')
            ->get();

        // Build nested tree structure
        return $this->buildNestedArray($nodes);
    }

    /**
     * Get the value representing root nodes from the model.
     */
    protected function getRootParentValue(): mixed
    {
        $model = $this->getTree()->getQuery()->getModel();

        return $model->getParentKeyDefaultValue();
    }

    /**
     * Check if a value represents a root node.
     */
    protected function isRootValue(mixed $value): bool
    {
        $rootValue = $this->getRootParentValue();
    
        if ($rootValue === null) {
            return $value === null
                || $value === ''
                || $value === -1
                || $value === '-1';
        }
        // Loose comparison to handle -1 vs "-1" etc
        return $value == $rootValue;
    }
    protected function buildNestedArray($nodes, $parentId = null): array
    {
        $branch = [];
        $rootValue = $this->getRootParentValue();
        $parentKeyName = $nodes->first()?->getParentKeyName() ?? 'parent_id';

        foreach ($nodes as $node) {
            // Check if this node belongs to the requested parent level
            $isMatch = $parentId === null
                ? $this->isRootValue($node->{$parentKeyName})
                : $node->{$parentKeyName} == $parentId;

            if ($isMatch) {
                $children = $this->buildNestedArray($nodes, $node->id);
                // Keep the model instance and add children as a relation
                $node->setRelation('children', collect($children));
                $branch[] = $node;
            }
        }

        return $branch;
    }

    public function reorderTree(array $moves): void
    {
        if (empty($moves)) {
            return;
        }

        foreach ($moves as $moveData) {
            $this->processSingleMove($moveData);
        }

        $this->dispatch('tree-reordered');
    }

    protected function processSingleMove(array $data): void
    {
        $nodeId = $data['nodeId'];
        $newParentId = $data['newParentId'];
        $position = $data['position'] ?? 'after';
        $referenceId = $data['referenceId'] ?? null;

        // Use the configured tree query instead of direct model query
        $node = (clone $this->getTree()->getQuery())->find($nodeId);

        if (! $node) {
            return;
        }

        // Cast IDs to the model's key type so strict === comparisons work correctly.
        // JavaScript sends IDs as strings from dataset.itemId, but integer PKs come
        // back from Eloquent as integers.
        if ($node->getKeyType() === 'int') {
            $nodeId = (int) $nodeId;
            $referenceId = $referenceId !== null ? (int) $referenceId : null;
        }

        // Get the parent key name from the model
        $parentKeyName = $node->getParentKeyName();

        // Remember old parent
        $oldParentId = $node->{$parentKeyName};

        // Move to new parent
        // Frontend sends null for root, convert to model's root value
        $rootValue = $this->getRootParentValue();
        $node->{$parentKeyName} = $newParentId === null ? $rootValue : $newParentId;
        $node->save();

        // Reorder siblings in old parent
        if ($oldParentId !== $newParentId) {
            $this->reorderSiblings($oldParentId);
        }

        // Position in new parent
        $this->reorderSiblingsWithInsert($newParentId, $nodeId, $position, $referenceId);
    }

    protected function reorderSiblings(mixed $parentId): void
    {
        $query = $this->getTree()->getQuery();
        $model = $query->getModel();
        $rootValue = $model->getParentKeyDefaultValue();
        $parentKeyName = $model->getParentKeyName();
        $orderKeyName = $model->getOrderKeyName();
        $primaryKey = $model->getKeyName();

        // Use the configured tree query as base
        $siblings = clone $query;

        if ($parentId === null || $this->isRootValue($parentId)) {
            // Query for root nodes
            if ($rootValue === null) {
                $siblings = $siblings->whereNull($parentKeyName);
            } else {
                $siblings = $siblings->where($parentKeyName, $rootValue);
            }
        } else {
            $siblings = $siblings->where($parentKeyName, $parentId);
        }

        $siblings = $siblings->orderBy($orderKeyName)->orderBy($primaryKey)->get();

        $order = 1;
        foreach ($siblings as $sibling) {
            if ($sibling->{$orderKeyName} !== $order) {
                $sibling->{$orderKeyName} = $order;
                $sibling->save();
            }
            $order++;
        }
    }

    protected function reorderSiblingsWithInsert(mixed $parentId, int|string $nodeId, string $position, int|string|null $referenceId): void
    {
        $query = $this->getTree()->getQuery();
        $model = $query->getModel();
        $rootValue = $model->getParentKeyDefaultValue();
        $parentKeyName = $model->getParentKeyName();
        $orderKeyName = $model->getOrderKeyName();
        $primaryKey = $model->getKeyName();

        // Use the configured tree query as base
        $siblings = clone $query;

        if ($parentId === null || $this->isRootValue($parentId)) {
            // Query for root nodes
            if ($rootValue === null) {
                $siblings = $siblings->whereNull($parentKeyName);
            } else {
                $siblings = $siblings->where($parentKeyName, $rootValue);
            }
        } else {
            $siblings = $siblings->where($parentKeyName, $parentId);
        }

        $siblings = $siblings->orderBy($orderKeyName)->orderBy($primaryKey)->get();

        $movedNode = $siblings->firstWhere('id', $nodeId);
        $otherSiblings = $siblings->reject(fn ($item) => $item->id === $nodeId);

        $newOrder = [];

        if ($position === 'inside' || ! $referenceId) {
            $newOrder = $otherSiblings->values()->all();
            $newOrder[] = $movedNode;
        } else {
            $referenceItem = $otherSiblings->firstWhere('id', $referenceId);

            if (! $referenceItem) {
                $newOrder = $otherSiblings->values()->all();
                $newOrder[] = $movedNode;
            } else {
                foreach ($otherSiblings as $sibling) {
                    if ($position === 'before' && $sibling->id === $referenceId) {
                        $newOrder[] = $movedNode;
                    }

                    $newOrder[] = $sibling;

                    if ($position === 'after' && $sibling->id === $referenceId) {
                        $newOrder[] = $movedNode;
                    }
                }
            }
        }

        $order = 1;
        foreach ($newOrder as $item) {
            if ($item->{$orderKeyName} !== $order) {
                $item->{$orderKeyName} = $order;
                $item->save();
            }
            $order++;
        }
    }

    public function toggleExpanded(string $recordId): void
    {
        $defaultExpanded = $this->getTree()->isDefaultExpanded();
        $this->treeState[$recordId] = ! ($this->treeState[$recordId] ?? $defaultExpanded);
    }

    public function isExpanded(string $recordId): bool
    {
        // Default expanded state from tree configuration
        $defaultExpanded = $this->getTree()->isDefaultExpanded();

        return $this->treeState[$recordId] ?? $defaultExpanded;
    }

    /**
     * Resolve a record from its key for actions.
     * This method is called by getTreeRecord() when resolving actions.
     */
    public function getTreeRecord(int|string $key): ?\Illuminate\Database\Eloquent\Model
    {
        // Use the configured tree query to respect modifyQueryUsing()
        return (clone $this->getTree()->getQuery())->find($key);
    }

    /**
     * Override Filament's action resolution to handle tree actions.
     * This intercepts the action resolution process to check if the action belongs to the tree.
     *
     * @param  array<array<string, mixed>>  $actions
     * @return array<\Filament\Actions\Action>
     */
    protected function resolveActions(array $actions, bool $isMounting = true): array
    {
        $resolvedActions = [];

        foreach ($actions as $actionNestingIndex => $action) {
            if (blank($action['name'] ?? null)) {
                throw new \Filament\Actions\Exceptions\ActionNotResolvableException('An action tried to resolve without a name.');
            }

            // Check if this is a tree action by looking in the cached actions on the Livewire component
            $isTreeAction = ! count($resolvedActions) && array_key_exists($action['name'], $this->cachedActions);

            if ($isTreeAction) {
                $resolvedAction = $this->resolveTreeAction($action, $resolvedActions);
            } else {
                // Fall back to parent resolution (handles schemaComponent, table, and regular actions)
                $resolvedAction = parent::resolveActions([$actionNestingIndex => $action], $isMounting)[0] ?? null;

                if (! $resolvedAction) {
                    continue;
                }

                $resolvedActions[] = $resolvedAction;

                continue;
            }

            if (! $resolvedAction) {
                continue;
            }

            $resolvedAction->nestingIndex($actionNestingIndex);
            $resolvedAction->boot();

            $resolvedActions[] = $resolvedAction;

            $this->cacheSchema(
                "mountedActionSchema{$actionNestingIndex}",
                $this->getMountedActionSchema($actionNestingIndex, $resolvedAction),
            );
        }

        return $resolvedActions;
    }

    /**
     * Resolve a tree-specific action.
     * This method is called by our overridden resolveActions() method.
     *
     * @param  array<string, mixed>  $action
     * @param  array<\Filament\Actions\Action>  $parentActions
     */
    protected function resolveTreeAction(array $action, array $parentActions): \Filament\Actions\Action
    {
        if (! ($this instanceof \Openplain\FilamentTreeView\Contracts\HasTree)) {
            throw new \Filament\Actions\Exceptions\ActionNotResolvableException('Failed to resolve tree action for Livewire component without the ['.\Openplain\FilamentTreeView\Contracts\HasTree::class.'] interface.');
        }

        $resolvedAction = null;

        if (count($parentActions)) {
            $parentAction = \Illuminate\Support\Arr::last($parentActions);
            $resolvedAction = $parentAction->getModalAction($action['name']) ?? throw new \Filament\Actions\Exceptions\ActionNotResolvableException("Action [{$action['name']}] was not found for action [{$parentAction->getName()}].");
        } else {
            // Get the action from cached actions on the Livewire component
            $resolvedAction = $this->cachedActions[$action['name']] ?? throw new \Filament\Actions\Exceptions\ActionNotResolvableException("Action [{$action['name']}] not found on tree.");
        }

        if (filled($action['context']['recordKey'] ?? null)) {
            $record = $this->getTreeRecord($action['context']['recordKey']);

            $resolvedAction->getRootGroup()?->record($record) ?? $resolvedAction->record($record);
        }

        return $resolvedAction;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'livewire' => [$this],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}
