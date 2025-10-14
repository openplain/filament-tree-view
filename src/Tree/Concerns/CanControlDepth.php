<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;

trait CanControlDepth
{
    protected int|Closure|null $maxDepth = 10;

    public function maxDepth(int|Closure|null $depth): static
    {
        $this->maxDepth = $depth;

        return $this;
    }

    public function getMaxDepth(): ?int
    {
        return $this->evaluate($this->maxDepth);
    }

    public function hasMaxDepth(): bool
    {
        return $this->getMaxDepth() !== null;
    }
}
