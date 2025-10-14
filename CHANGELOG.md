# Changelog

All notable changes to `filament-tree-view` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2025-01-15

### Added
- 🎉 Initial release of Filament Tree View
- 🌳 Drag-and-drop tree reordering with Pragmatic Drag & Drop
- 📦 Drop-in replacement for Filament Tables with familiar API
- 🎯 Depth control with configurable `maxDepth()` (default: 10 levels)
- 💾 Manual save mode (default) with Save/Cancel buttons
- 💾 Auto-save mode with `autoSave()` method
- 🔧 Custom fields: `TextField` and `IconField`
- 🎨 Field styling: colors, weights, alignment, character limits
- 🔄 Collapsible trees with individual toggles and header Expand All/Collapse All buttons
- 🔄 Start collapsed with `collapsed()` method
- ⚡ Actions support: edit, delete, and custom actions
- 🎯 Header actions using Filament's standard pattern
- 🌗 Full dark mode support
- ♿ Accessible drag-and-drop with keyboard support
- 🔒 Safe operations: prevents circular references and invalid moves
- 📝 Empty state customization
- 🔍 Query customization with `modifyQueryUsing()`
- 🏗️ `HasTreeStructure` trait for Eloquent models
- 🏗️ Automatic cascade delete for descendants
- 🏗️ Support for legacy databases with custom column names
- 🏗️ Support for custom root parent values (-1, 0, etc.)
- 📚 Comprehensive documentation with real-world examples
- ✅ Test suite with 50 passing tests
- 🎨 Standalone CSS with Tailwind v4
- 🚀 Built with Laravel Adjacency List for recursive queries

### Fixed
- 🐛 maxDepth validation now accounts for subtree depth when dragging items with children
- 🐛 Drag restrictions properly enforce depth limits for complex hierarchies

### Technical Details
- PHP 8.2+ required
- Laravel 11 or 12 required
- Filament 4.0+ required
- Built on [staudenmeir/laravel-adjacency-list](https://github.com/staudenmeir/laravel-adjacency-list)
- Built with [Pragmatic Drag & Drop](https://atlassian.design/components/pragmatic-drag-and-drop) by Atlassian

[Unreleased]: https://github.com/openplain/filament-tree-view/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/openplain/filament-tree-view/releases/tag/v0.1.0
