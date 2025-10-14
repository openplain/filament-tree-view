<?php

namespace Openplain\FilamentTreeView\Resources\Pages;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Openplain\FilamentTreeView\Concerns\InteractsWithTree;
use Openplain\FilamentTreeView\Contracts\HasTree;
use Openplain\FilamentTreeView\Tree;

class TreePage extends Page implements HasTree
{
    use InteractsWithTree {
        makeTree as makeBaseTree;
    }

    protected string $view = 'filament-tree-view::pages.tree-page';

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void {}

    public function getBreadcrumb(): ?string
    {
        return static::$breadcrumb ?? __('filament-tree-view::tree.breadcrumb');
    }

    public function tree(Tree $tree): Tree
    {
        return $tree;
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? static::getResource()::getTitleCasePluralModelLabel();
    }

    public function form(Schema $schema): Schema
    {
        return static::getResource()::form($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return static::getResource()::infolist($schema);
    }

    public function getDefaultActionSchemaResolver(Action $action): ?Closure
    {
        return match (true) {
            $action instanceof CreateAction, $action instanceof EditAction => fn (Schema $schema): Schema => $this->form($schema->columns(2)),
            $action instanceof ViewAction => fn (Schema $schema): Schema => $this->infolist($this->form($schema->columns(2))),
            default => null,
        };
    }

    protected function makeTree(): Tree
    {
        $tree = $this->makeBaseTree()
            ->query(fn (): Builder => $this->getTreeQuery())
            ->when(
                $this->getParentRecord(),
                fn (Tree $tree, Model $parentRecord): Tree => $tree->query(
                    fn (Builder $query) => static::getResource()::scopeEloquentQueryToParent($query, $parentRecord),
                ),
            )
            ->when($this->getModelLabel(), fn (Tree $tree, string $modelLabel): Tree => $tree->modelLabel($modelLabel))
            ->when($this->getPluralModelLabel(), fn (Tree $tree, string $pluralModelLabel): Tree => $tree->pluralModelLabel($pluralModelLabel))
            ->recordAction(function (Model $record, Tree $tree): ?string {
                foreach (['view', 'edit'] as $action) {
                    $action = $tree->getAction($action);

                    if (! $action) {
                        continue;
                    }

                    $action->record($record);
                    $action->getGroup()?->record($record);

                    if ($action->isHidden()) {
                        continue;
                    }

                    if ($action->getUrl()) {
                        continue;
                    }

                    return $action->getName();
                }

                return null;
            })
            ->recordUrl(function (Model $record, Tree $tree): ?string {
                foreach (['view', 'edit'] as $action) {
                    $action = $tree->getAction($action);

                    if (! $action) {
                        continue;
                    }

                    $action->record($record);
                    $action->getGroup()?->record($record);

                    if ($action->isHidden()) {
                        continue;
                    }

                    $url = $action->getUrl();

                    if (! $url) {
                        continue;
                    }

                    return $url;
                }

                $resource = static::getResource();

                foreach (['view', 'edit'] as $action) {
                    if (! $resource::hasPage($action)) {
                        continue;
                    }

                    if (! $resource::{'can'.ucfirst($action)}($record)) {
                        continue;
                    }

                    return $this->getResourceUrl($action, ['record' => $record]);
                }

                return null;
            });

        return static::getResource()::tree($tree);
    }

    public function getTreeQuery(): Builder|Relation
    {
        return static::getResource()::getEloquentQuery();
    }

    public function getModelLabel(): ?string
    {
        return static::getResource()::getModelLabel();
    }

    public function getPluralModelLabel(): ?string
    {
        return static::getResource()::getPluralModelLabel();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getWidgetData(): array
    {
        return [];
    }

    public function getRecord(): ?Model
    {
        return null;
    }

    public function getParentRecord(): ?Model
    {
        return null;
    }
}
