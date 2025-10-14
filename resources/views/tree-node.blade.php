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
    data-parent-id="{{ $record->{$record->getParentKeyName()} ?? -1 }}"
    data-order="{{ $record->order ?? 0 }}"
    data-depth="{{ $depth }}"
    data-item-title="{{ $record->name ?? $record->title ?? 'Item '.$record->id }}"
>
    {{-- Item Content - Using Filament table row classes --}}
    <div class="filament-tree-node-content fi-ta-row">
        <div class="fi-ta-cell p-0">
            <div class="flex items-center ml-2">
            {{-- Drag Handle --}}
                <button
                    type="button"
                    data-drag-handle
                    class="relative filament-tree-drag-handle flex-shrink-0 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 transition-opacity"
                    title="Drag to reorder"
                >
                    <span class="absolute -inset-2"></span>
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                    </svg>
                </button>

                {{-- Collapse/Expand Toggle - Only shown if collapse is enabled --}}
                <div class="filament-tree-toggle-container">
                    @if ($tree->isCollapsible() && $hasChildren)
                        <button
                            type="button"
                            class="relative tree-toggle-btn text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 transition-colors"
                            title="Toggle"
                            data-record-id="{{ $record->id }}"
                            onclick="window.toggleTreeNode(this, '{{ $record->id }}')"
                        >
                            <span class="absolute -inset-2 left-0.5"></span>
                            <svg class="w-4 h-4 transition-transform {{ $livewire->isExpanded($record->id) ? 'rotate-0' : '-rotate-90' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Item Title/Content --}}
                <div class="filament-tree-node-title flex-1 min-w-0">
                    <div class="flex items-center">
                        @php
                            $fields = $tree->getVisibleFields($record);
                            $leftFields = [];
                            $rightFields = [];
                            foreach ($fields as $field) {
                                if ($field->getAlignment() === \Filament\Support\Enums\Alignment::End) {
                                    $rightFields[] = $field;
                                } else {
                                    $leftFields[] = $field;
                                }
                            }
                        @endphp

                        {{-- Left-aligned fields grouped together --}}
                        @if (count($leftFields) > 0)
                            <div class="flex items-center gap-4 flex-shrink">
                                @foreach ($leftFields as $field)
                                    <div class="flex-shrink-0 {{ $field->getAlignmentClass() }}">
                                        {!! $field->render($record) !!}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Right-aligned fields --}}
                        @if (count($rightFields) > 0)
                            <div class="flex items-center gap-3" style="margin-left: auto; padding-left: 1rem;">
                                @foreach ($rightFields as $field)
                                    <div class="flex items-center">
                                        {!! $field->render($record) !!}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Record Actions --}}
                @if (count($recordActions))
                    <div class="filament-tree-node-actions">
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
        <div class="filament-tree-children">
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
