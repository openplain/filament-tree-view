<div class="filament-tree" x-data="{ tree: null }" x-init="
    tree = new window.FilamentTree(null, {
        maxDepth: {{ $tree->getMaxDepth() ?? 10 }},
        livewireComponent: $wire,
        enableBatchSave: {{ $tree->shouldBatchSave() ? 'true' : 'false' }},
    });
    tree.init();
">
    @if ($tree->getRecords()?->isNotEmpty())
        <div class="filament-tree-container" wire:key="tree-{{ now()->timestamp }}">
            @foreach ($tree->getRecords() as $record)
                <x-filament-tree-view::tree-node
                    :record="$record"
                    :tree="$tree"
                    :level="0"
                    :collapsed="!$tree->isDefaultExpanded()"
                />
            @endforeach

            {{-- Drop zone at end for root level --}}
            <div
                class="filament-tree-drop-at-end"
                data-drop-at-end
                data-depth="0"
            ></div>
        </div>
    @else
        <x-filament-tables::empty-state
            :actions="$tree->getEmptyStateActions()"
            :description="$tree->getEmptyStateDescription()"
            :heading="$tree->getEmptyStateHeading()"
            :icon="$tree->getEmptyStateIcon()"
        />
    @endif
</div>
