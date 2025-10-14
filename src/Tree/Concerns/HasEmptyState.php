<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

trait HasEmptyState
{
    /**
     * @var array<Action> | Closure
     */
    protected array|Closure $emptyStateActions = [];

    protected string|Htmlable|Closure|null $emptyStateDescription = null;

    protected string|Htmlable|Closure|null $emptyStateHeading = null;

    protected string|Closure|null $emptyStateIcon = null;

    /**
     * @param  array<Action> | Closure  $actions
     */
    public function emptyStateActions(array|Closure $actions): static
    {
        $this->emptyStateActions = $actions;

        return $this;
    }

    public function emptyStateDescription(string|Htmlable|Closure|null $description): static
    {
        $this->emptyStateDescription = $description;

        return $this;
    }

    public function emptyStateHeading(string|Htmlable|Closure|null $heading): static
    {
        $this->emptyStateHeading = $heading;

        return $this;
    }

    public function emptyStateIcon(string|Closure|null $icon): static
    {
        $this->emptyStateIcon = $icon;

        return $this;
    }

    /**
     * @return array<Action>
     */
    public function getEmptyStateActions(): array
    {
        return $this->evaluate($this->emptyStateActions);
    }

    public function getEmptyStateDescription(): string|Htmlable|null
    {
        return $this->evaluate($this->emptyStateDescription);
    }

    public function getEmptyStateHeading(): string|Htmlable|null
    {
        return $this->evaluate($this->emptyStateHeading);
    }

    public function getEmptyStateIcon(): ?string
    {
        return $this->evaluate($this->emptyStateIcon);
    }
}
