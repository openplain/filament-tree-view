<?php

namespace Openplain\FilamentTreeView\Concerns;

use Filament\Actions\Concerns\HasActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Openplain\FilamentTreeView\Tree;

trait InteractsWithTree
{
    use HasActions;

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

    public function getTreeQuery(): Builder | Relation
    {
        return $this->getTree()->getQuery();
    }

    public function getTreeRecords(): Collection
    {
        $query = $this->getTreeQuery();

        // Get root nodes with their descendants using Laravel Adjacency List
        return $query
            ->whereNull('parent_id')
            ->withRecursiveQueryDepth()
            ->get()
            ->toTree();
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

        // Remember old parent
        $oldParentId = $node->parent_id;

        // Move to new parent
        $node->parent_id = $newParentId === -1 ? null : $newParentId;
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

        if ($parentId === -1 || $parentId === null) {
            $siblings = $modelClass::whereNull('parent_id');
        } else {
            $siblings = $modelClass::where('parent_id', $parentId);
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

        if ($parentId === -1) {
            $siblings = $modelClass::whereNull('parent_id');
        } else {
            $siblings = $modelClass::where('parent_id', $parentId);
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
        $this->treeState[$recordId] = ! ($this->treeState[$recordId] ?? false);
    }

    public function isExpanded(string $recordId): bool
    {
        // Default expanded state from tree configuration
        $defaultExpanded = $this->getTree()->isDefaultExpanded();

        return $this->treeState[$recordId] ?? $defaultExpanded;
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
