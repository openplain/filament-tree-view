<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Filament\Actions\Action;

trait HasHeaderActions
{
    /**
     * @var array<Action> | Closure
     */
    protected array | Closure $headerActions = [];

    /**
     * @param  array<Action> | Closure  $actions
     */
    public function headerActions(array | Closure $actions): static
    {
        $this->headerActions = $actions;

        return $this;
    }

    /**
     * @return array<Action>
     */
    public function getHeaderActions(): array
    {
        return $this->evaluate($this->headerActions);
    }
}
