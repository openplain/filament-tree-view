<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;

trait CanCollapse
{
    protected bool|Closure $isCollapsible = true;

    protected bool|Closure $isDefaultExpanded = true;

    /**
     * Control collapsible tree nodes.
     * By default, trees are collapsible with individual toggles + header Expand All/Collapse All buttons.
     * Pass false to disable collapsible functionality for simple/small trees.
     * Matches Filament's Section::collapsible() pattern.
     */
    public function collapsible(bool|Closure $condition = true): static
    {
        $this->isCollapsible = $condition;

        return $this;
    }

    /**
     * Start with all nodes collapsed instead of expanded.
     * Matches Filament's Section::collapsed() pattern.
     * Useful for large trees to improve initial load performance.
     */
    public function collapsed(bool|Closure $condition = true): static
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
