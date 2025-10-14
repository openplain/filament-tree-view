<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;

trait CanReorderRecords
{
    protected bool|Closure $isReorderable = true;

    protected bool|Closure $isAutoSave = false;

    public function reorderable(bool|Closure $condition = true): static
    {
        $this->isReorderable = $condition;

        return $this;
    }

    public function autoSave(bool|Closure $condition = true): static
    {
        $this->isAutoSave = $condition;

        return $this;
    }

    public function isReorderable(): bool
    {
        return $this->evaluate($this->isReorderable);
    }

    public function isAutoSave(): bool
    {
        return $this->evaluate($this->isAutoSave);
    }
}
