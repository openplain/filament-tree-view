<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HasQuery
{
    protected Builder|Relation|Closure|null $query = null;

    protected ?Closure $queryModifier = null;

    protected ?string $modelLabel = null;

    protected ?string $pluralModelLabel = null;

    public function query(Builder|Relation|Closure|null $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->queryModifier = $callback;

        return $this;
    }

    public function modelLabel(string $label): static
    {
        $this->modelLabel = $label;

        return $this;
    }

    public function pluralModelLabel(string $label): static
    {
        $this->pluralModelLabel = $label;

        return $this;
    }

    public function getQuery(): Builder|Relation|null
    {
        $query = $this->evaluate($this->query);

        if ($query && $this->queryModifier) {
            $query = $this->evaluate($this->queryModifier, [
                'query' => $query,
            ]);
        }

        return $query;
    }

    public function getModelLabel(): ?string
    {
        return $this->modelLabel;
    }

    public function getPluralModelLabel(): ?string
    {
        return $this->pluralModelLabel;
    }
}
