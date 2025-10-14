<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tree View Configuration
    |--------------------------------------------------------------------------
    |
    | NOTE: This configuration file is reserved for future use.
    | Currently, all tree settings must be configured directly in your
    | TreePage class using the fluent API methods.
    |
    | Default values when methods are not called:
    | - maxDepth: 10 (reasonable limit for most use cases)
    | - collapsible: true (enabled with individual toggles + header buttons)
    | - Nodes start expanded by default (use collapsed() to override)
    | - autoSave: false (manual save with buttons)
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Max Depth
    |--------------------------------------------------------------------------
    |
    | The default maximum depth for tree nesting.
    | Default is 10 levels which covers most use cases.
    | Set to null for unlimited depth. Can be overridden per tree.
    |
    */
    'max_depth' => 10,

    /*
    |--------------------------------------------------------------------------
    | Enable Collapsible Trees
    |--------------------------------------------------------------------------
    |
    | Trees are collapsible by default (individual toggles + header buttons).
    | Nodes start expanded by default.
    | Use collapsible(false) method to disable for simple/small trees.
    | Use collapsed() method to begin with nodes collapsed.
    |
    */
    'collapsible' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-Save Changes
    |--------------------------------------------------------------------------
    |
    | Whether to save changes immediately on drag-and-drop.
    | false = shows Save/Cancel buttons (recommended)
    | true = saves immediately without confirmation
    |
    */
    'auto_save' => false,
];
