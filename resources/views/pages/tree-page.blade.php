<x-filament-panels::page>
    @if ($tree = $this->getTree())
        @php
            $records = $this->getTreeRecords();
        @endphp

        {{-- Filament Table Wrapper --}}
        <div class="fi-ta">
            <div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
                @if (count($records) > 0)
                    <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
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
                    </div>
                @else
                    <div class="fi-ta-empty-state px-6 py-12">
                        <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                            <div class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400">
                                No records found.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
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
