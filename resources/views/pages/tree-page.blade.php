<x-filament-panels::page>
    @if ($tree = $this->getTree())
        @php
            $records = $this->getTreeRecords();
        @endphp

        @if (count($records) > 0)
            <div class="filament-tree-container">
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
            <div class="rounded-xl bg-white px-6 py-12 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mx-auto grid max-w-lg justify-items-center text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No records found.</p>
                </div>
            </div>
        @endif
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
