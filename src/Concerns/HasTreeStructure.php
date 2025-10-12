<?php

namespace Openplain\FilamentTreeView\Concerns;

use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

trait HasTreeStructure
{
    use HasRecursiveRelationships;

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
}
