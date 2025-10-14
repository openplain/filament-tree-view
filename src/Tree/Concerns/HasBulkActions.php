<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Filament\Actions\BulkAction;

trait HasBulkActions
{
    /**
     * @var array<BulkAction> | Closure
     */
    protected array|Closure $bulkActions = [];

    /**
     * @param  array<BulkAction> | Closure  $actions
     */
    public function bulkActions(array|Closure $actions): static
    {
        $this->bulkActions = $actions;

        return $this;
    }

    /**
     * @return array<BulkAction>
     */
    public function getBulkActions(): array
    {
        return $this->evaluate($this->bulkActions);
    }
}
