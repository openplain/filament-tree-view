<x-filament-panels::page>
    @if ($tree = $this->getTree())
        <div class="filament-tree-view">
            @php
                $records = $this->getTreeRecords();
            @endphp

            @if (count($records) > 0)
                <div class="tree-container space-y-1">
                    @foreach ($records as $record)
                        @include('filament-tree-view::tree-node', [
                            'record' => $record,
                            'depth' => 0,
                            'maxDepth' => $tree->getMaxDepth(),
                            'livewire' => $this
                        ])
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No categories found.</p>
                </div>
            @endif
        </div>
    @endif

    <x-filament-actions::modals />

    @script
    <script>
        let treeInstance = null;

        function initTree() {
            if (treeInstance) {
                treeInstance.destroy();
            }

            if (typeof window.FilamentTree === 'undefined') {
                return;
            }

            // Use $wire to get the current component
            const component = $wire;

            if (component) {
                treeInstance = new window.FilamentTree(null, {
                    maxDepth: {{ $tree->getMaxDepth() }},
                    livewireComponent: component,
                    enableBatchSave: false,
                });

                treeInstance.init();
            }
        }

        // Initialize tree after Livewire is ready
        initTree();

        // Reinitialize after Livewire updates
        Livewire.hook('commit', ({ component, respond }) => {
            respond(() => {
                setTimeout(() => {
                    initTree();
                }, 100);
            });
        });
    </script>
    @endscript
</x-filament-panels::page>
