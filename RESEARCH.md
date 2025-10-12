# Filament Source Code Research

**Date**: 2025-10-12
**Purpose**: Understand Filament's internal architecture to mirror it exactly for the Tree plugin

---

## 1. Table Builder Pattern & Configuration Storage

**File**: `vendor/filament/tables/src/Table.php`

### Key Findings

#### Class Structure
```php
class Table extends ViewComponent
{
    use HasActions;
    use HasBulkActions;
    use HasColumns;
    use HasFilters;
    use HasHeaderActions;
    use CanPaginateRecords;
    use CanSearchRecords;
    use CanSortRecords;
    // ... many more concerns
}
```

**Insight**: Filament uses **trait composition** extensively. Each feature is a separate trait.

#### Static Factory Pattern
```php
public static function make(HasTable $livewire): static
{
    $static = app(static::class, ['livewire' => $livewire]);
    $static->configure();
    return $static;
}
```

**Insight**:
- Uses Laravel's service container (`app()`)
- Requires a Livewire component implementing `HasTable` interface
- Calls `configure()` which triggers `setUp()` method

#### Configuration Storage
- All configuration is stored in **protected properties** on the Table instance
- Methods use **fluent interface** (return `$this`) for method chaining
- Configuration is stored via traits like `HasActions`, `HasColumns`, etc.

**Example from HasActions trait**:
```php
trait HasActions
{
    protected array $flatActions = [];
    protected array $flatBulkActions = [];

    protected function cacheAction(Action $action): void
    {
        $this->flatActions[$action->getName()] = $action;
    }
}
```

#### Parent Classes
```php
ViewComponent extends Component implements Htmlable
```

**Insight**: ViewComponent handles rendering to Blade views with `render()` method

---

## 2. Livewire Integration Pattern

**File**: `vendor/filament/tables/src/Concerns/InteractsWithTable.php`

### Key Findings

#### The Contract
```php
interface HasTable
{
    public function table(Table $table): Table;
    public function getTable(): Table;
}
```

**Insight**: Any Livewire component using Tables must implement `HasTable` contract

#### Initialization Flow
```php
trait InteractsWithTable
{
    protected Table $table;

    public function bootedInteractsWithTable(): void
    {
        // 1. Create Table instance via makeTable()
        $this->table = $this->table($this->makeTable());

        // 2. Cache filters form
        $this->cacheSchema('tableFiltersForm', $this->getTableFiltersForm());

        // 3. Cache mounted actions
        $this->cacheMountedActions($this->mountedActions);

        // 4. Initialize other features (pagination, search, etc.)
    }

    protected function makeTable(): Table
    {
        return Table::make($this);
    }

    public function table(Table $table): Table
    {
        return $table; // Override this to configure
    }
}
```

#### Configuration Pattern
**In Resource**:
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->actions([...])
        ->bulkActions([...]);
}
```

**In Page (ListRecords)**:
```php
protected function makeTable(): Table
{
    return $this->makeBaseTable()
        ->query(fn(): Builder => $this->getTableQuery())
        ->when($this->getModelLabel(), fn (Table $table, string $label) =>
            $table->modelLabel($label)
        );
}
```

**Insight**:
- Resource defines the **base configuration**
- Page can **override** via `makeTable()` to add query, model labels, etc.
- Uses `when()` helper for conditional configuration

---

## 3. Action System Architecture

**File**: `vendor/filament/actions/src/Action.php`

### Key Findings

#### Class Structure
```php
class Action extends ViewComponent implements Arrayable
{
    use HasLabel;
    use HasIcon;
    use HasColor;
    use CanOpenModal;
    use HasSchema;  // Forms inside modals
    use CanNotify;
    use CanRedirect;
    use InteractsWithRecord;
    // ... 40+ traits
}
```

**Insight**: Actions are **highly composable** through traits

#### Static Factory
```php
public static function make(?string $name = null): static
{
    $static = app(static::class, ['name' => $name ?? static::getDefaultName()]);
    $static->configure();
    return $static;
}
```

#### Action Configuration Examples
```php
Action::make('create')
    ->label('Create')
    ->icon('heroicon-o-plus')
    ->modal()
    ->form([...])
    ->action(function (array $data) {
        // Execute action
    })
```

**Insight**: Actions are **self-contained** - they define their own:
- Label & icon
- Modal window
- Form schema
- Execution logic

---

## 4. Action Mounting & Execution

**File**: `vendor/filament/actions/src/Concerns/InteractsWithActions.php`

### Key Findings

#### State Management
```php
trait InteractsWithActions
{
    public ?array $mountedActions = [];
    protected array $cachedActions = [];
    protected ?array $cachedMountedActions = null;
}
```

#### Boot Process
```php
public function bootedInteractsWithActions(): void
{
    if (filled($originallyMountedActionIndex = array_key_last($this->mountedActions))) {
        $this->originallyMountedActionIndex = $originallyMountedActionIndex;
    }

    $this->cacheTraitActions();

    if (! ($this instanceof HasTable)) {
        $this->cacheMountedActions($this->mountedActions);
    }
}
```

**Insight**: Actions can be **nested** - tracked via array indexes

#### Mounting Flow
```php
public function mountAction(string $name, array $arguments = [], array $context = []): mixed
{
    // 1. Add to mounted actions stack
    $this->mountedActions[] = [
        'name' => $name,
        'arguments' => $arguments,
        'context' => $context,
    ];

    // 2. Get the action instance
    $action = $this->getMountedAction();

    // 3. Check if disabled
    if ($action->isDisabled()) {
        $this->unmountAction(canCancelParentActions: false);
        return null;
    }

    // 4. Check authorization
    if ($action->getAuthorizationResponseWithMessage()->denied()) {
        $action->sendUnauthorizedNotification($response);
        throw new Cancel;
    }

    // 5. Execute lifecycle hooks (before, etc.)
    // 6. Mount the action
}
```

**Insight**:
- Actions are **mounted** (not immediately executed)
- Supports **nested actions** via stack
- Full **authorization** and **lifecycle hooks**

---

## 5. Resource & Page Integration

**Files**:
- `vendor/filament/filament/src/Resources/Resource.php`
- `vendor/filament/filament/src/Resources/Pages/ListRecords.php`

### Key Findings

#### Resource Structure
```php
abstract class Resource
{
    public static function table(Table $table): Table
    {
        return $table; // Override to configure
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }
}
```

#### ListRecords Page
```php
class ListRecords extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable {
        makeTable as makeBaseTable;
    }

    public function table(Table $table): Table
    {
        return $table; // Can override Resource's config here
    }

    protected function makeTable(): Table
    {
        return $this->makeBaseTable()
            ->query(fn(): Builder => $this->getTableQuery())
            ->modifyQueryUsing(/* ... */)
            ->modelLabel($this->getModelLabel())
            ->recordAction(/* ... */);
    }
}
```

#### Configuration Flow
1. **Page** calls `makeTable()` which creates `Table::make($this)`
2. **Page** passes it to **Resource**'s static `table()` method for base config
3. **Page** can further customize via its own `table()` method
4. Result is stored in `$this->table` property

**Code from ListRecords**:
```php
public function bootedInteractsWithTable(): void
{
    // This calls:
    // 1. $this->makeTable() -> creates Table with query
    // 2. static::getResource()::table($table) -> adds columns, actions
    // 3. $this->table($table) -> page-specific overrides
    $this->table = $this->table(
        static::getResource()::table($this->makeTable())
    );
}
```

---

## 6. Asset Registration Pattern

**File**: `vendor/filament/filament/src/FilamentServiceProvider.php`

### Key Pattern
```php
public function packageBooted(): void
{
    Filament::registerScripts([
        'filament-app' => __DIR__ . '/../dist/filament.js',
    ]);

    Filament::registerStyles([
        'filament-app' => __DIR__ . '/../dist/filament.css',
    ]);
}
```

**Insight**:
- Use Filament's asset registration system
- Assets are automatically injected into panel layouts

---

## 7. Service Provider Registration

**File**: Various Filament service providers

### Key Patterns

#### Package Service Provider Base
```php
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTreeViewServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-tree-view')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations();
    }
}
```

#### Livewire Component Registration
```php
Livewire::component('filament.resources.pages.tree-page', TreePage::class);
```

---

## Key Architectural Principles

### 1. Builder Pattern Everywhere
Every major component (Table, Action, Schema) uses:
```php
ComponentClass::make()
    ->property1()
    ->property2()
    ->methodN()
```

### 2. Trait Composition Over Inheritance
Instead of deep inheritance, Filament uses traits:
- Each feature is a trait
- Compose features as needed
- Easy to understand and extend

### 3. Fluent Interfaces
All configuration methods return `$this`:
```php
public function maxDepth(int $depth): static
{
    $this->maxDepth = $depth;
    return $this;
}
```

### 4. Closure-Based Configuration
Many methods accept closures for dynamic values:
```php
->visible(fn (Model $record): bool => $record->isPublished())
```

### 5. Container Resolution
Use Laravel's service container everywhere:
```php
app(TreeComponent::class, ['livewire' => $livewire])
```

---

## Implementation Strategy for Tree Plugin

### Phase 1: Create Core Classes
1. **Tree Builder** (`src/Tree.php`)
   - Extend `ViewComponent`
   - Mirror Table's trait structure
   - Add tree-specific traits (HasDepthControl, CanNest, etc.)

2. **InteractsWithTree Trait** (`src/Concerns/InteractsWithTree.php`)
   - Mirror `InteractsWithTable`
   - Boot process, makeTree(), getTree()

3. **HasTree Interface** (`src/Contracts/HasTree.php`)
   - Define contract for Livewire components

### Phase 2: Create Page Class
1. **TreePage** (`src/Resources/Pages/TreePage.php`)
   - Mirror `ListRecords`
   - Implement `HasTree` interface
   - Use `InteractsWithTree` trait

### Phase 3: Integrate Actions
- Tree should use **existing** Filament actions
- No need to create custom action classes
- Just configure action context appropriately

### Phase 4: Create Artisan Command
```bash
php artisan make:filament-tree-resource Category
```
- Mirror `make:filament-resource` command
- Generate TreePage instead of ListRecords

---

## Questions Answered

✅ **How does Filament pass config to Livewire components?**
- Via protected properties on the component class
- Stored during `bootedInteractsWithTable()` lifecycle hook
- Configuration happens via the `table(Table $table)` method

✅ **How are actions mounted and executed?**
- Actions are "mounted" to a stack (`$mountedActions[]`)
- Each mount includes name, arguments, context
- Livewire renders modals based on mounted actions
- Forms inside modals are separate Schema instances

✅ **How do forms work inside action modals?**
- Actions have a `HasSchema` trait
- Define form via `->form([...])` method
- Schema is cached and validated separately
- Data is passed to `action()` callback

✅ **Service provider registration patterns?**
- Use Spatie's `PackageServiceProvider`
- Register Livewire components manually
- Use Filament's asset registration system

✅ **Asset registration (CSS/JS)?**
- Use `Filament::registerScripts()` and `Filament::registerStyles()`
- Called in service provider's `packageBooted()` method

✅ **How does Filament handle `wire:ignore` scenarios?**
- Filament renders components inside Livewire
- For JS libraries (like drag-drop), use `wire:ignore` on container
- Use Alpine.js to bridge between Livewire and JS library
- Dispatch Livewire events from JS when data changes

---

## Next Steps

1. Create `Tree` builder class mirroring `Table`
2. Create `InteractsWithTree` trait
3. Create `HasTree` interface
4. Create `TreePage` base class
5. Port working drag-drop code from `api.ritograk.fo`
6. Create Artisan command for scaffolding

**Remember**: Look at Filament source code first before implementing anything!
