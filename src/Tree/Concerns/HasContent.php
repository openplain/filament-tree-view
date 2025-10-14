<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Illuminate\Contracts\Support\Htmlable;

trait HasContent
{
    protected string|Htmlable|Closure|null $content = null;

    public function content(string|Htmlable|Closure|null $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): string|Htmlable|null
    {
        return $this->evaluate($this->content);
    }
}
