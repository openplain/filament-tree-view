# Filament Tree View

A Filament plugin that provides a Tree Resource as a drop-in replacement for Filament's standard Table Resource. Display hierarchical data in a drag-and-drop tree view instead of a flat table.

## Features

- 🌳 **100% reliable drag-and-drop** - Reorder and nest items at any level
- 🎯 **API parity with Filament Tables** - Use the same patterns you already know
- ⚡ **Depth control** - Limit nesting levels
- 💾 **Batch save mode** - Accumulate changes, save all at once
- 🔒 **Circular reference prevention** - Can't move parent into its own child
- 🎨 **Visual feedback** - Clear indicators for reordering and nesting
- 🌓 **Dark mode support** - Full Filament theme integration

## Installation

```bash
composer require openplain/filament-tree-view
```

## Quick Start

### 1. Prepare Your Model

Add the `HasTreeStructure` trait to your Eloquent model:

```php
use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Concerns\HasTreeStructure;

class Category extends Model
{
    use HasTreeStructure;
}
```

Your database table needs these columns:

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('parent_id')->nullable()->constrained('categories');
    $table->integer('order')->default(0);
    $table->timestamps();
});
```

### 2. Create a Tree Resource

```bash
php artisan make:filament-tree-resource Category
```

### 3. Configure Your Tree

```php
use Openplain\FilamentTreeView\Tree;

public static function tree(Tree $tree): Tree
{
    return $tree
        ->maxDepth(6)
        ->enableCollapse()
        ->defaultExpanded(false)
        ->actions([
            // Same as Filament table actions
        ])
        ->bulkActions([
            // Same as Filament bulk actions
        ])
        ->headerActions([
            // Same as Filament header actions
        ]);
}
```

## Documentation

Coming soon...

## Technology Stack

- **Backend**: Laravel Adjacency List (Staudenmeir)
- **Drag & Drop**: Pragmatic Drag & Drop (Atlassian)
- **Frontend State**: Alpine.js + Livewire 3
- **UI**: Filament 4

## Credits

Built on top of the amazing work from:
- [Filament](https://filamentphp.com)
- [Laravel Adjacency List](https://github.com/staudenmeir/laravel-adjacency-list)
- [Pragmatic Drag & Drop](https://atlassian.design/components/pragmatic-drag-and-drop)

## License

MIT License
