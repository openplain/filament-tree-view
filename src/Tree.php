<?php

namespace Openplain\FilamentTreeView;

use Filament\Support\Components\ViewComponent;
use Filament\Support\Concerns\HasExtraAttributes;
use Openplain\FilamentTreeView\Contracts\HasTree;

class Tree extends ViewComponent
{
    use HasExtraAttributes;
    use Tree\Concerns\BelongsToLivewire;
    use Tree\Concerns\CanCollapse;
    use Tree\Concerns\CanControlDepth;
    use Tree\Concerns\CanReorderRecords;
    use Tree\Concerns\HasActions;
    use Tree\Concerns\HasBulkActions;
    use Tree\Concerns\HasContent;
    use Tree\Concerns\HasEmptyState;
    use Tree\Concerns\HasFields;
    use Tree\Concerns\HasQuery;
    use Tree\Concerns\HasRecordAction;
    use Tree\Concerns\HasRecordActions;
    use Tree\Concerns\HasRecords;
    use Tree\Concerns\HasRecordUrl;

    /**
     * @var view-string
     */
    protected string $view = 'filament-tree-view::index';

    protected string $viewIdentifier = 'tree';

    protected string $evaluationIdentifier = 'tree';

    public const LOADING_TARGETS = [
        'reorderTree',
        'expandTreeNode',
        'collapseTreeNode',
    ];

    final public function __construct(HasTree $livewire)
    {
        $this->livewire($livewire);
    }

    public static function make(HasTree $livewire): static
    {
        $static = app(static::class, ['livewire' => $livewire]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->emptyStateDescription(function (Tree $tree): ?string {
            if (! $tree->hasAction('create')) {
                return null;
            }

            return __('filament-tree-view::tree.empty.description', [
                'model' => $tree->getModelLabel(),
            ]);
        });
    }

    /**
     * Get a cached action by name.
     * This method is used by resolveTreeAction() to retrieve actions.
     */
    public function getCachedAction(string $name): ?\Filament\Actions\Action
    {
        return $this->getLivewire()->getCachedAction($name);
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'livewire' => [$this->getLivewire()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}
