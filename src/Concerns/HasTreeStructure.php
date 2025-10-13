<?php

namespace Openplain\FilamentTreeView\Concerns;

use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

trait HasTreeStructure
{
    use HasRecursiveRelationships;

    /**
     * Boot the HasTreeStructure trait.
     * Automatically cascade delete all descendants when a node is deleted.
     */
    protected static function bootHasTreeStructure(): void
    {
        static::deleting(function ($model) {
            // Only cascade delete if not force deleting (to allow soft delete to work)
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                // Force delete all descendants
                $model->descendants()->forceDelete();
            } else {
                // Regular delete all descendants (respects soft deletes)
                $model->descendants()->delete();
            }
        });
    }

    public function getParentKeyName(): string
    {
        return 'parent_id';
    }

    public function getLocalKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getDepthName(): string
    {
        return 'depth';
    }

    public function getPathName(): string
    {
        return 'path';
    }

    public function getChildrenKeyName(): string
    {
        return 'children';
    }

    public function getQualifiedParentKeyName(): string
    {
        return $this->qualifyColumn($this->getParentKeyName());
    }

    /**
     * Get the value used to represent root nodes (nodes without a parent).
     *
     * By default, root nodes have parent_id = NULL.
     * Override this method to use a different value (e.g., -1 or 0) for existing databases.
     *
     * @return mixed The value representing "no parent" (null, -1, 0, etc.)
     */
    public function getParentKeyDefaultValue(): mixed
    {
        return null;
    }
}
