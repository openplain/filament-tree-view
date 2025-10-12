<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Openplain\FilamentTreeView\Contracts\HasTree;

trait BelongsToLivewire
{
    protected HasTree $livewire;

    public function livewire(HasTree $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): HasTree
    {
        return $this->livewire;
    }
}
