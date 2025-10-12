<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;

trait CanCollapse
{
    protected bool | Closure $isCollapsible = false;

    protected bool | Closure $isDefaultExpanded = true;

    public function enableCollapse(bool | Closure $condition = true): static
    {
        $this->isCollapsible = $condition;

        return $this;
    }

    public function disableCollapse(bool | Closure $condition = true): static
    {
        $this->isCollapsible = fn (): bool => ! $this->evaluate($condition);

        return $this;
    }

    public function defaultExpanded(bool | Closure $condition = true): static
    {
        $this->isDefaultExpanded = $condition;

        return $this;
    }

    public function defaultCollapsed(bool | Closure $condition = true): static
    {
        $this->isDefaultExpanded = fn (): bool => ! $this->evaluate($condition);

        return $this;
    }

    public function isCollapsible(): bool
    {
        return $this->evaluate($this->isCollapsible);
    }

    public function isDefaultExpanded(): bool
    {
        return $this->evaluate($this->isDefaultExpanded);
    }
}
