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

    public function reorderTree(array $order): void
    {
        foreach ($order as $item) {
            $this->reorderTreeItem($item);
        }

        $this->dispatch('tree-reordered');
    }

    protected function reorderTreeItem(array $item, ?int $parentId = null, int $position = 0): void
    {
        $modelClass = $this->getTree()->getQuery()->getModel()::class;
        $record = $modelClass::find($item['id']);

        if (! $record) {
            return;
        }

        $record->parent_id = $parentId;
        $record->order = $position;
        $record->save();

        if (isset($item['children'])) {
            foreach ($item['children'] as $index => $child) {
                $this->reorderTreeItem($child, $item['id'], $index);
            }
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
