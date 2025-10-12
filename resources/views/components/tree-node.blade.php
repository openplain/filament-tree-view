{{--
    Filament Tree View - Tree Node Component
    =========================================

    Renders a single tree node with drag-and-drop support.
    This component is recursive - it calls itself to render children.

    Props:
    - record: Eloquent model instance
    - tree: Tree builder instance
    - level: Current nesting level (0 = root)
    - collapsed: Whether node is collapsed
--}}

@props(['record', 'tree', 'level' => 0, 'collapsed' => false])

@php
    // Check if this node has children
    $hasChildren = $record->children && $record->children->count() > 0;

    // Current depth level
    $depth = $level;

    // Get actions for this record
    $actions = [];
    foreach ($tree->getFlatActions() as $action) {
        $action->record($record);
        if (!$action->isHidden()) {
            $actions[] = $action;
        }
    }
@endphp

{{-- Tree Item Container --}}
<div
    class="filament-tree-node"
    data-tree-item
    data-item-id="{{ $record->getKey() }}"
    data-parent-id="{{ $record->parent_id ?? -1 }}"
    data-order="{{ $record->order ?? 0 }}"
    data-depth="{{ $depth }}"
    data-item-title="{{ $record->name ?? $record->title ?? 'Item '.$record->getKey() }}"
>
    {{-- Item Content --}}
    <div class="filament-tree-node-content fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
        <div class="fi-ta-cell p-0 first-of-type:ps-3 last-of-type:pe-3 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
            <div class="flex items-center gap-3 px-3 py-3">

                {{-- Drag Handle --}}
                @if ($tree->isReorderable())
                    <button
                        type="button"
                        data-drag-handle
                        class="filament-tree-drag-handle cursor-move flex-shrink-0 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400"
                        title="{{ __('filament-tree-view::tree.actions.drag') }}"
                    >
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                        </svg>
                    </button>
                @endif

                {{-- Collapse/Expand Toggle --}}
                @if ($tree->isCollapsible() && $hasChildren)
                    <button
                        type="button"
                        class="filament-tree-collapse-btn flex-shrink-0 {{ $collapsed ? 'collapsed' : '' }}"
                        title="{{ $collapsed ? __('filament-tree-view::tree.actions.expand') : __('filament-tree-view::tree.actions.collapse') }}"
                        wire:click="toggleExpanded('{{ $record->getKey() }}')"
                    >
                        <svg class="w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                @elseif ($tree->isCollapsible())
                    {{-- Spacer for alignment when no children --}}
                    <div class="w-4 flex-shrink-0"></div>
                @endif

                {{-- Item Title/Content --}}
                <div class="filament-tree-node-title flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-950 dark:text-white truncate">
                        {{ $record->name ?? $record->title ?? 'Item '.$record->getKey() }}
                    </div>
                    @if (isset($record->description) && $record->description)
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $record->description }}
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                @if (count($actions) > 0)
                    <div class="filament-tree-node-actions flex items-center gap-1 ml-auto">
                        @foreach ($actions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Children (Recursive) --}}
    @if ($hasChildren)
        <div class="filament-tree-children {{ $collapsed ? 'hidden' : '' }}" style="margin-left: 2rem;">
            @foreach ($record->children as $child)
                <x-filament-tree-view::tree-node
                    :record="$child"
                    :tree="$tree"
                    :level="$level + 1"
                    :collapsed="$tree->getLivewire()->isExpanded((string) $child->getKey()) === false"
                />
            @endforeach
        </div>
    @endif
</div>
