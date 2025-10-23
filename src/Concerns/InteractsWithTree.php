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

    public function bootedInteractsWithTree(): void
    {
        $this->tree = $this->tree($this->makeTree());

        $this->cacheMountedActions($this->mountedActions);
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

        // Get all records ordered, using the configured query
        $nodes = (clone $query)
            ->orderBy('order')
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
        $newParentId = $data['newParentId'] ?? -1;
        $position = $data['position'] ?? 'after';
        $referenceId = $data['referenceId'] ?? null;

        $modelClass = $this->getTree()->getQuery()->getModel()::class;
        $node = $modelClass::find($nodeId);

        if (! $node) {
            return;
        }

        // Get the parent key name from the model
        $parentKeyName = $node->getParentKeyName();

        // Remember old parent
        $oldParentId = $node->{$parentKeyName};

        // Move to new parent
        // Frontend sends -1 for root, convert to model's root value
        $rootValue = $this->getRootParentValue();
        $node->{$parentKeyName} = $newParentId === -1 ? $rootValue : $newParentId;
        $node->save();

        // Reorder siblings in old parent
        if ($oldParentId !== $newParentId) {
            $this->reorderSiblings($oldParentId);
        }

        // Position in new parent
        $this->reorderSiblingsWithInsert($newParentId, $nodeId, $position, $referenceId);
    }

    protected function reorderSiblings(?int $parentId): void
    {
        $modelClass = $this->getTree()->getQuery()->getModel()::class;
        $model = new $modelClass;
        $rootValue = $model->getParentKeyDefaultValue();
        $parentKeyName = $model->getParentKeyName();

        if ($parentId === -1 || $parentId === null || $this->isRootValue($parentId)) {
            // Query for root nodes
            if ($rootValue === null) {
                $siblings = $modelClass::whereNull($parentKeyName);
            } else {
                $siblings = $modelClass::where($parentKeyName, $rootValue);
            }
        } else {
            $siblings = $modelClass::where($parentKeyName, $parentId);
        }

        $siblings = $siblings->orderBy('order')->orderBy('id')->get();

        $order = 1;
        foreach ($siblings as $sibling) {
            if ($sibling->order !== $order) {
                $sibling->order = $order;
                $sibling->save();
            }
            $order++;
        }
    }

    protected function reorderSiblingsWithInsert(int $parentId, int $nodeId, string $position, ?int $referenceId): void
    {
        $modelClass = $this->getTree()->getQuery()->getModel()::class;
        $model = new $modelClass;
        $rootValue = $model->getParentKeyDefaultValue();
        $parentKeyName = $model->getParentKeyName();

        if ($parentId === -1 || $this->isRootValue($parentId)) {
            // Query for root nodes
            if ($rootValue === null) {
                $siblings = $modelClass::whereNull($parentKeyName);
            } else {
                $siblings = $modelClass::where($parentKeyName, $rootValue);
            }
        } else {
            $siblings = $modelClass::where($parentKeyName, $parentId);
        }

        $siblings = $siblings->orderBy('order')->orderBy('id')->get();

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
            if ($item->order !== $order) {
                $item->order = $order;
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
        $modelClass = $this->getTree()->getQuery()->getModel()::class;

        return $modelClass::find($key);
    }

    /**
     * Override Filament's action resolution to handle tree actions.
     * This intercepts the action resolution process to check if the action belongs to the tree.
     *
     * @param  array<array<string, mixed>>  $actions
     * @return array<\Filament\Actions\Action>
     */
    protected function resolveActions(array $actions): array
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
                $resolvedAction = parent::resolveActions([$actionNestingIndex => $action])[0] ?? null;

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
