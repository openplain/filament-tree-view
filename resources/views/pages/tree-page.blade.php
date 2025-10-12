<x-filament-panels::page>
    <x-filament-panels::header :actions="$this->getCachedHeaderActions()" />

    @if ($tree = $this->getTree())
        <div class="filament-tree-view">
            {{-- Tree content will be rendered here --}}
            <div wire:ignore>
                <div id="tree-container" data-tree="{{ json_encode($this->getTreeRecords()) }}">
                    {{-- Tree structure will be rendered via Blade component --}}
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
