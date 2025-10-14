<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Size;
use Illuminate\Support\Arr;
use PHPUnit\Event\InvalidArgumentException;

trait HasRecordActions
{
    /**
     * @var array<Action | ActionGroup>
     */
    protected array $recordActions = [];

    protected ?Closure $modifyUngroupedRecordActionsUsing = null;

    /**
     * @param  array<Action | ActionGroup> | ActionGroup  $actions
     */
    public function recordActions(array|ActionGroup $actions): static
    {
        $this->recordActions = [];
        $this->pushRecordActions($actions);

        return $this;
    }

    /**
     * @param  array<Action | ActionGroup> | ActionGroup  $actions
     */
    public function pushRecordActions(array|ActionGroup $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {
            // Note: Actions don't have a tree() method, they work with the livewire component directly
            // The action will be associated with the tree through the livewire component

            if ($action instanceof ActionGroup) {
                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                $this->getLivewire()->mergeCachedFlatActions($flatActions);
            } elseif ($action instanceof Action) {
                $action->defaultSize(Size::Small);
                $action->defaultView($action::LINK_VIEW);

                if ($this->modifyUngroupedRecordActionsUsing) {
                    $this->evaluate($this->modifyUngroupedRecordActionsUsing, ['action' => $action]);
                }

                $this->getLivewire()->cacheAction($action);
            } else {
                throw new InvalidArgumentException('Tree actions must be an instance of ['.Action::class.'] or ['.ActionGroup::class.'].');
            }

            $this->recordActions[] = $action;
        }

        return $this;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getRecordActions(): array
    {
        return $this->recordActions;
    }

    /**
     * @return array<string, Action>
     */
    public function getFlatRecordActions(): array
    {
        $flatActions = [];

        foreach ($this->getRecordActions() as $action) {
            if ($action instanceof ActionGroup) {
                $flatActions = array_merge($flatActions, $action->getFlatActions());
            } else {
                $flatActions[$action->getName()] = $action;
            }
        }

        return $flatActions;
    }

    public function modifyUngroupedRecordActionsUsing(?Closure $callback = null): static
    {
        $this->modifyUngroupedRecordActionsUsing = $callback;

        return $this;
    }
}
