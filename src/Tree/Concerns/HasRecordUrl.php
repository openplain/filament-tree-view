<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait HasRecordUrl
{
    protected string|Closure|null $recordUrl = null;

    public function recordUrl(string|Closure|null $url): static
    {
        $this->recordUrl = $url;

        return $this;
    }

    public function getRecordUrl(?Model $record = null): ?string
    {
        return $this->evaluate($this->recordUrl, [
            'record' => $record,
        ]);
    }
}
