# Filament Tree View

[![Latest Version on Packagist](https://img.shields.io/packagist/v/openplain/filament-tree-view.svg?style=flat-square)](https://packagist.org/packages/openplain/filament-tree-view)
[![Total Downloads](https://img.shields.io/packagist/dt/openplain/filament-tree-view.svg?style=flat-square)](https://packagist.org/packages/openplain/filament-tree-view)

A powerful drag-and-drop tree view for Filament resources. Display and manage hierarchical data with the same elegant developer experience you expect from Filament.

![Filament Tree View Demo](docs/images/demo.gif)
*[Screenshot placeholder: Animated GIF showing drag-and-drop tree reordering]*

## Why This Package?

We created Filament Tree View because we couldn't find a hierarchical data solution that truly embraced Filament's philosophy and architecture. Most tree packages feel like external additions rather than native Filament components.

**Our Goal:** Make hierarchical data management feel as natural as using Filament's Table component.

### Built on Proven Technology

Rather than reinventing the wheel, we leverage battle-tested libraries:

- **[Laravel Adjacency List](https://github.com/staudenmeir/laravel-adjacency-list)** - Mature, proven package for recursive relationships with thousands of production deployments
- **[Pragmatic Drag & Drop](https://atlassian.design/components/pragmatic-drag-and-drop)** - Atlassian's accessible, performant drag-and-drop library used in Jira, Trello, and Confluence
- **Filament's Core Components** - Built with the same patterns, conventions, and architecture as native Filament resources

This foundation gives you reliability, performance, and accessibility out of the box.

## Features

- 🌳 **Drag-and-Drop Reordering** - Intuitive tree manipulation with visual feedback
- 📦 **Drop-in Replacement** - Familiar API if you've used Filament Tables
- 🎯 **Depth Control** - Limit tree nesting to prevent overly complex hierarchies
- 💾 **Save Modes** - Choose between auto-save or batch save with manual confirmation
- 🎨 **Custom Fields** - Display any data in your tree nodes with TextField and ToggleField
- 🔧 **Actions Support** - Full support for Filament actions (edit, delete, custom actions)
- 🌗 **Dark Mode** - Seamless integration with Filament's theming system
- ♿ **Accessible** - Keyboard navigation and screen reader support built-in
- 🔒 **Safe Operations** - Prevents circular references and invalid moves

![Tree Features](docs/images/features.png)
*[Screenshot placeholder: Split view showing different tree features]*

## Requirements

- PHP 8.2 or higher
- Laravel 11 or 12
- Filament 4.0 or higher

## Installation

Install the package via Composer:

```bash
composer require openplain/filament-tree-view
```

Publish the package assets:

```bash
php artisan filament:assets
```

That's it! No config files to publish, no service providers to register - everything works out of the box.

## Quick Start

### 1. Prepare Your Database

Create a migration with the required tree structure columns:

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();

    // Required for tree structure
    $table->foreignId('parent_id')->nullable()->constrained('categories');
    $table->integer('order')->default(0);

    $table->timestamps();
});
```

> **Important:** By default, root nodes should have `parent_id = NULL`. If your existing system uses a different value (like `-1` or `0`), you can override this in your model:
>
> ```php
> public function getParentKeyDefaultValue(): mixed
> {
>     return -1; // or 0, or your custom value
> }
> ```

### 2. Add Trait to Your Model

Add the `HasTreeStructure` trait to enable tree functionality:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Concerns\HasTreeStructure;

class Category extends Model
{
    use HasTreeStructure;

    protected $fillable = ['name', 'description', 'parent_id', 'order'];
}
```

The trait provides:
- Recursive parent/child relationships
- Automatic cascade delete for descendants
- Tree query helpers (roots, leaves, depth calculations)

### 3. Create a Tree Page

Create a new page that extends `TreePage`:

```php
<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Openplain\FilamentTreeView\Resources\Pages\TreePage;
use Openplain\FilamentTreeView\Tree;

class TreeCategories extends TreePage
{
    protected static string $resource = CategoryResource::class;

    public function tree(Tree $tree): Tree
    {
        return $tree
            ->maxDepth(6)
            ->enableCollapse()
            ->defaultExpanded(true);
    }
}
```

### 4. Register the Page in Your Resource

Add the tree page to your resource's `getPages()` method:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Resources\Resource;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    // ... your form configuration

    public static function getPages(): array
    {
        return [
            'index' => Pages\TreeCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
```

That's it! You now have a fully functional tree view.

![Quick Start Result](docs/images/quickstart-result.png)
*[Screenshot placeholder: Basic tree view after quick start]*

## Configuration

### Tree Display Options

Configure how your tree displays and behaves:

```php
public function tree(Tree $tree): Tree
{
    return $tree
        // Maximum nesting depth (default: no limit)
        ->maxDepth(6)

        // Enable collapse/expand toggles
        ->enableCollapse()

        // Default expanded state when collapse is enabled
        ->defaultExpanded(true)

        // Auto-save changes immediately (default: false)
        // false = shows Save/Cancel buttons
        // true = saves on every reorder
        ->autoSave(false);
}
```

### Custom Fields

Display custom data in your tree nodes using the Field API:

```php
use Openplain\FilamentTreeView\Fields\TextField;
use Openplain\FilamentTreeView\Fields\ToggleField;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;

public static function tree(Tree $tree): Tree
{
    return $tree
        ->fields([
            TextField::make('name')
                ->weight(FontWeight::Medium)
                ->dimWhenInactive(),

            TextField::make('description')
                ->color('gray')
                ->limit(50)
                ->dimWhenInactive(),

            ToggleField::make('is_active')
                ->alignEnd(),
        ]);
}
```

![Custom Fields](docs/images/custom-fields.png)
*[Screenshot placeholder: Tree with various field types]*

#### TextField Options

```php
TextField::make('name')
    // Typography
    ->size('sm' | 'base' | 'lg')
    ->weight(FontWeight::Thin | FontWeight::Medium | FontWeight::Bold)

    // Colors (Filament color names)
    ->color('primary' | 'gray' | 'success' | 'warning' | 'danger')

    // Alignment
    ->alignStart()  // default
    ->alignCenter()
    ->alignEnd()

    // Content formatting
    ->limit(50)  // Truncate with ellipsis
    ->formatStateUsing(fn (string $state): string => strtoupper($state))

    // Conditional dimming
    ->dimWhenInactive()  // Requires is_active field
    ->dimWhen('field_name', value: false);
```

#### ToggleField Options

```php
ToggleField::make('is_active')
    // Icons (Heroicon enum)
    ->trueIcon(Heroicon::OutlinedCheckCircle)
    ->falseIcon(Heroicon::OutlinedXCircle)

    // Colors
    ->trueColor('success')
    ->falseColor('danger')

    // Alignment
    ->alignEnd();  // Typically right-aligned
```

### Actions

Add actions to tree nodes just like Filament Tables:

```php
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

public static function tree(Tree $tree): Tree
{
    return $tree
        ->recordActions([
            // Navigate to edit page
            EditAction::make()
                ->url(fn (Category $record): string =>
                    static::getUrl('edit', ['record' => $record])
                ),

            // Edit in modal
            Action::make('editModal')
                ->label('Quick Edit')
                ->icon('heroicon-o-pencil-square')
                ->fillForm(fn (Category $record): array => [
                    'name' => $record->name,
                    'description' => $record->description,
                ])
                ->form([
                    TextInput::make('name')->required(),
                    Textarea::make('description'),
                ])
                ->action(function (Category $record, array $data) {
                    $record->update($data);

                    Notification::make()
                        ->title('Category updated')
                        ->success()
                        ->send();
                }),

            // Delete with descendant warning
            DeleteAction::make()
                ->modalDescription(function (Category $record): string {
                    $count = $record->descendants()->count();

                    if ($count === 0) {
                        return 'Are you sure you want to delete this category?';
                    }

                    return "This category has {$count} descendants that will also be deleted.";
                }),
        ]);
}
```

![Record Actions](docs/images/actions.png)
*[Screenshot placeholder: Dropdown menu showing record actions]*

### Header Actions

Add actions to the tree header:

```php
use Filament\Actions\CreateAction;
use Filament\Actions\Action;

public function tree(Tree $tree): Tree
{
    return $tree
        ->headerActions([
            CreateAction::make()
                ->url(fn (): string => static::getResource()::getUrl('create')),

            Action::make('export')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    // Export logic
                }),
        ]);
}
```

### Model Configuration

The `HasTreeStructure` trait uses sensible defaults, but you can customize column names:

```php
class Category extends Model
{
    use HasTreeStructure;

    /**
     * Parent ID column name (default: 'parent_id')
     */
    public function getParentKeyName(): string
    {
        return 'parent_id';
    }

    /**
     * Primary key column name (default: 'id')
     */
    public function getLocalKeyName(): string
    {
        return $this->getKeyName(); // Usually 'id'
    }

    /**
     * Virtual depth attribute (default: 'depth')
     * Calculated during queries, not stored
     */
    public function getDepthName(): string
    {
        return 'depth';
    }

    /**
     * Virtual path attribute (default: 'path')
     * Example: [1, 5, 12] = root(1) > parent(5) > current(12)
     * Calculated during queries, not stored
     */
    public function getPathName(): string
    {
        return 'path';
    }

    /**
     * Children relationship name (default: 'children')
     */
    public function getChildrenKeyName(): string
    {
        return 'children';
    }

    /**
     * Root parent value (default: null)
     * Override this for existing databases that use -1, 0, or other values
     * to represent root nodes (nodes without a parent)
     */
    public function getParentKeyDefaultValue(): mixed
    {
        return null; // or -1, 0, etc.
    }
}
```

### Working with Existing Databases

If you have an existing database that uses `-1`, `0`, or another value to represent root nodes instead of `NULL`, simply override the `getParentKeyDefaultValue()` method:

```php
class Category extends Model
{
    use HasTreeStructure;

    /**
     * Existing database uses -1 for root nodes
     */
    public function getParentKeyDefaultValue(): mixed
    {
        return -1;
    }
}
```

No database migration needed! The package will automatically handle querying and saving with your custom root value.

## Advanced Usage

### Customizing Empty State

```php
public function tree(Tree $tree): Tree
{
    return $tree
        ->emptyStateHeading('No categories yet')
        ->emptyStateDescription('Get started by creating your first category.')
        ->emptyStateIcon('heroicon-o-rectangle-stack')
        ->emptyStateActions([
            CreateAction::make()
                ->label('Create first category'),
        ]);
}
```

### Batch Save Mode

Control when changes are persisted:

```php
// Auto-save: Changes saved immediately
->autoSave(true)

// Manual save: User clicks "Save Changes" button
->autoSave(false)  // default
```

With manual save mode, users can:
- Make multiple changes
- Review all changes before saving
- Cancel to discard all changes

![Batch Save](docs/images/batch-save.png)
*[Screenshot placeholder: Save/Cancel buttons in action]*

### Query Customization

Modify the base query for your tree:

```php
public function tree(Tree $tree): Tree
{
    return $tree
        ->modifyQueryUsing(fn (Builder $query) => $query
            ->where('status', 'active')
            ->orderBy('name')
        );
}
```

## Video Tutorial

Watch our comprehensive video guide:

[![Filament Tree View Tutorial](docs/images/video-thumbnail.png)](https://youtube.com/watch/...)
*[Video placeholder: Link to tutorial video]*

## Common Patterns

### Building a Navigation Menu

```php
class MenuItem extends Model
{
    use HasTreeStructure;

    protected $fillable = ['label', 'url', 'icon', 'parent_id', 'order', 'is_active'];
}

public static function tree(Tree $tree): Tree
{
    return $tree
        ->maxDepth(3) // Limit menu depth
        ->fields([
            TextField::make('label')->weight(FontWeight::Medium),
            TextField::make('url')->color('gray'),
            TextField::make('icon')->color('gray'),
            ToggleField::make('is_active')->alignEnd(),
        ])
        ->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
}
```

### Product Categories with Status

```php
public static function tree(Tree $tree): Tree
{
    return $tree
        ->fields([
            TextField::make('name')
                ->weight(FontWeight::Medium)
                ->dimWhenInactive(),

            TextField::make('products_count')
                ->formatStateUsing(fn (int $state): string => "{$state} products")
                ->color('gray'),

            TextField::make('status')
                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                ->color(fn (string $state): string => match ($state) {
                    'published' => 'success',
                    'draft' => 'warning',
                    default => 'gray',
                }),

            ToggleField::make('is_active')->alignEnd(),
        ]);
}
```

### Department Hierarchy

```php
class Department extends Model
{
    use HasTreeStructure;

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}

public static function tree(Tree $tree): Tree
{
    return $tree
        ->maxDepth(5)
        ->fields([
            TextField::make('name')->weight(FontWeight::Bold),
            TextField::make('manager_name')->color('gray'),
            TextField::make('employees_count')
                ->formatStateUsing(fn (?int $state): string =>
                    $state ? "{$state} employees" : 'No employees'
                )
                ->color('gray'),
        ]);
}
```

## Troubleshooting

### JavaScript Not Loading

If drag-and-drop doesn't work after installation:

```bash
# Publish assets
php artisan filament:assets

# Clear caches
php artisan filament:cache-components
php artisan view:clear
```

### Drag Restrictions

If you can't drag items to certain positions:

1. **Depth limit reached** - Check your `maxDepth()` setting
2. **Circular reference** - Can't move a parent into its own descendant
3. **Custom canDrop logic** - Review any custom drop validation

### Performance with Large Trees

For trees with hundreds of nodes:

- Consider pagination or filtering at the root level
- Use `->defaultExpanded(false)` to collapse by default
- Eager load relationships in `modifyQueryUsing()`

```php
->modifyQueryUsing(fn (Builder $query) =>
    $query->with(['children', 'someRelation'])
)
```

## Testing

Run the test suite:

```bash
composer test
```

Run Pint for code style:

```bash
composer pint
```

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please email security@openplain.com. All security vulnerabilities will be promptly addressed.

## Credits

Built with these excellent open-source libraries:

- **[Laravel Adjacency List](https://github.com/staudenmeir/laravel-adjacency-list)** by Jonas Staudenmeir - Battle-tested recursive tree queries with thousands of production deployments
- **[Pragmatic Drag & Drop](https://atlassian.design/components/pragmatic-drag-and-drop)** by Atlassian - Accessible, performant drag-and-drop used in Jira, Trello, and Confluence

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

<p align="center">
  <strong>Built with ❤️ by <a href="https://openplain.com">Openplain</a></strong>
</p>
