<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;

trait CanReorderRecords
{
    protected bool | Closure $isReorderable = true;

    protected bool | Closure $shouldBatchSave = false;

    public function reorderable(bool | Closure $condition = true): static
    {
        $this->isReorderable = $condition;

        return $this;
    }

    public function batchSave(bool | Closure $condition = true): static
    {
        $this->shouldBatchSave = $condition;

        return $this;
    }

    public function isReorderable(): bool
    {
        return $this->evaluate($this->isReorderable);
    }

    public function shouldBatchSave(): bool
    {
        return $this->evaluate($this->shouldBatchSave);
    }
}
