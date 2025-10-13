{{--
    Filament Tree View - Tree Node Include
    =======================================

    Renders a single tree node with drag-and-drop support.
    This view is recursive - it includes itself to render children.

    Variables:
    - $record: Eloquent model instance
    - $depth: Current nesting level (0 = root)
    - $maxDepth: Maximum depth allowed
    - $livewire: The parent Livewire component
--}}

@php
    // Check if this node has children
    $hasChildren = isset($record->children) && count($record->children) > 0;

    // Get tree instance
    $tree = $livewire->getTree();

    // Get and filter record actions for this record
    $recordActions = array_reduce(
        $tree->getRecordActions(),
        function (array $carry, $action) use ($record): array {
            $action = $action->getClone();

            if (! $action instanceof \Filament\Actions\BulkAction) {
                $action->record($record);
            }

            if ($action->isHidden()) {
                return $carry;
            }

            $carry[] = $action;

            return $carry;
        },
        [],
    );
@endphp

{{-- Tree Item Container --}}
<div
    class="filament-tree-node"
    data-tree-item
    data-item-id="{{ $record->id }}"
    data-parent-id="{{ $record->parent_id ?? -1 }}"
    data-order="{{ $record->order ?? 0 }}"
    data-depth="{{ $depth }}"
    data-item-title="{{ $record->name ?? $record->title ?? 'Item '.$record->id }}"
>
    {{-- Item Content - Using Filament table row classes --}}
    <div class="filament-tree-node-content fi-ta-row">
        <div class="fi-ta-cell p-0">
            <div class="flex items-center mt-1 mb-1 ml-2">
            {{-- Drag Handle --}}
                <button
                    type="button"
                    data-drag-handle
                    class="filament-tree-drag-handle p-2 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 transition-opacity"
                    title="Drag to reorder"
                >
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                    </svg>
                </button>

                {{-- Collapse/Expand Toggle - Fixed width for alignment --}}
                <div class="flex-shrink-0" style="width: 1rem;">
                    @if ($hasChildren)
                        <button
                            type="button"
                            class="tree-toggle-btn py-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 transition-colors"
                            title="Toggle"
                            data-record-id="{{ $record->id }}"
                            onclick="window.toggleTreeNode(this, '{{ $record->id }}')"
                        >
                            <svg class="w-4 h-4 transition-transform {{ $livewire->isExpanded($record->id) ? 'rotate-0' : '-rotate-90' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Item Title/Content --}}
                <div class="filament-tree-node-title flex-1 min-w-0 ml-2">
                    <div class="text-sm font-medium text-gray-950 dark:text-white truncate">
                        {{ $record->name ?? $record->title ?? 'Item '.$record->id }}
                    </div>
                    @if (isset($record->description) && $record->description)
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $record->description }}
                        </div>
                    @endif
                </div>

                {{-- Record Actions --}}
                @if (count($recordActions))
                    <div class="filament-tree-node-actions flex items-center gap-3 ml-auto mr-2">
                        @foreach ($recordActions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Children (Recursive) --}}
    @if ($hasChildren && $livewire->isExpanded($record->id))
        <div class="filament-tree-children" style="margin-left: 2rem;">
            @foreach ($record->children as $child)
                @include('filament-tree-view::tree-node', [
                    'record' => $child,
                    'depth' => $depth + 1,
                    'maxDepth' => $maxDepth,
                    'livewire' => $livewire
                ])
            @endforeach
        </div>
    @endif
</div>
