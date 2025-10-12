<div class="filament-tree">
    @if ($tree->getRecords()?->isNotEmpty())
        <div class="tree-container">
            {{-- Tree nodes will be rendered here --}}
            @foreach ($tree->getRecords() as $record)
                <x-filament-tree-view::tree-node
                    :record="$record"
                    :tree="$tree"
                />
            @endforeach
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
