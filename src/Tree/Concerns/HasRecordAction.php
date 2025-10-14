<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait HasRecordAction
{
    protected string|Closure|null $recordAction = null;

    public function recordAction(string|Closure|null $action): static
    {
        $this->recordAction = $action;

        return $this;
    }

    public function getRecordAction(?Model $record = null): ?string
    {
        return $this->evaluate($this->recordAction, [
            'record' => $record,
        ]);
    }
}
