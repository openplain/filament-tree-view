<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Arr;
use PHPUnit\Event\InvalidArgumentException;

trait HasHeaderActions
{
    /**
     * @var array<Action | ActionGroup>
     */
    protected array $headerActions = [];

    /**
     * @param  array<Action | ActionGroup> | ActionGroup  $actions
     */
    public function headerActions(array | ActionGroup $actions): static
    {
        $this->headerActions = [];
        $this->pushHeaderActions($actions);

        return $this;
    }

    /**
     * @param  array<Action | ActionGroup> | ActionGroup  $actions
     */
    public function pushHeaderActions(array | ActionGroup $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {
            if ($action instanceof ActionGroup) {
                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                foreach ($flatActions as $flatAction) {
                    $this->getLivewire()->cacheAction($flatAction);
                }
            } elseif ($action instanceof Action) {
                $this->getLivewire()->cacheAction($action);
            } else {
                throw new InvalidArgumentException('Tree header actions must be an instance of [' . Action::class . '] or [' . ActionGroup::class . '].');
            }

            $this->headerActions[] = $action;
        }

        return $this;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getHeaderActions(): array
    {
        return $this->headerActions;
    }

    /**
     * @return array<string, Action>
     */
    public function getFlatHeaderActions(): array
    {
        $flatActions = [];

        foreach ($this->getHeaderActions() as $action) {
            if ($action instanceof ActionGroup) {
                $flatActions = array_merge($flatActions, $action->getFlatActions());
            } else {
                $flatActions[$action->getName()] = $action;
            }
        }

        return $flatActions;
    }
}
