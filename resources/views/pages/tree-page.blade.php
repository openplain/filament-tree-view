<x-filament-panels::page>
    @if ($tree = $this->getTree())
        @php
            $records = $this->getTreeRecords();
        @endphp

        @if (count($records) > 0)
            {{-- Header Bar --}}
            <div class="flex items-center justify-between gap-3 mb-6">
                {{-- Left Side: Expand/Collapse Buttons --}}
                @if ($tree->isCollapsible())
                    <div class="inline-flex rounded-lg shadow-sm">
                    <button
                        type="button"
                        id="tree-expand-all"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 ring-1 ring-gray-950/10 dark:ring-white/20 fi-ac-action fi-ac-btn-action rounded-r-none"
                    >
                        <svg class="fi-btn-icon transition duration-75 h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="fi-btn-label">Expand</span>
                    </button>
                    <button
                        type="button"
                        id="tree-collapse-all"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 ring-1 ring-gray-950/10 dark:ring-white/20 fi-ac-action fi-ac-btn-action rounded-l-none -ml-px"
                    >
                        <svg class="fi-btn-icon transition duration-75 h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        <span class="fi-btn-label">Collapse</span>
                    </button>
                </div>
                @else
                    <div></div> {{-- Spacer for flex layout --}}
                @endif

                {{-- Right Side: Status and Action Buttons --}}
                @if ($tree->shouldBatchSave())
                <div class="flex items-center gap-3">
                    {{-- Unsaved Changes Indicator --}}
                    <div
                        id="tree-changes-indicator"
                        class="hidden items-center gap-2 text-warning-600 dark:text-warning-400"
                    >
                        <svg class="h-4 w-4 animate-pulse" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <span class="text-sm font-medium">Unsaved changes</span>
                    </div>

                    {{-- Cancel Button --}}
                    <button
                        type="button"
                        id="tree-cancel-btn"
                        disabled
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 ring-1 ring-gray-950/10 dark:ring-white/20 fi-ac-action fi-ac-btn-action disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none"
                    >
                        <svg class="fi-btn-icon transition duration-75 h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="fi-btn-label">Cancel</span>
                    </button>

                    {{-- Save Button --}}
                    <button
                        type="button"
                        id="tree-save-btn"
                        disabled
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 dark:bg-custom-500 dark:hover:bg-custom-400 ring-1 ring-custom-600 dark:ring-custom-500 fi-ac-action fi-ac-btn-action disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none"
                        style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                    >
                        <svg class="fi-btn-icon transition duration-75 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span class="fi-btn-label">Save Changes</span>
                    </button>
                </div>
                @endif
            </div>

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
                    enableBatchSave: {{ $tree->shouldBatchSave() ? 'true' : 'false' }},
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

        // ========================================
        // Expand/Collapse All Functionality
        // ========================================

        @if ($tree->isCollapsible())
        const expandAllBtn = document.getElementById('tree-expand-all');
        const collapseAllBtn = document.getElementById('tree-collapse-all');

        if (expandAllBtn) {
            expandAllBtn.addEventListener('click', () => {
                // Expand all nodes
                document.querySelectorAll('.tree-item').forEach(item => {
                    const childrenContainer = item.querySelector('.filament-tree-children');
                    if (childrenContainer) {
                        childrenContainer.style.display = 'block';
                    }
                });

                // Update all collapse buttons to expanded state
                document.querySelectorAll('.tree-collapse-btn').forEach(btn => {
                    const svg = btn.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('-rotate-90');
                        svg.classList.add('rotate-0');
                    }
                });

                // Save preference to localStorage
                localStorage.setItem('filament_tree_expand_state', 'expanded');
            });
        }

        if (collapseAllBtn) {
            collapseAllBtn.addEventListener('click', () => {
                // Collapse all nodes
                document.querySelectorAll('.tree-item').forEach(item => {
                    const childrenContainer = item.querySelector('.filament-tree-children');
                    if (childrenContainer) {
                        childrenContainer.style.display = 'none';
                    }
                });

                // Update all collapse buttons to collapsed state
                document.querySelectorAll('.tree-collapse-btn').forEach(btn => {
                    const svg = btn.querySelector('svg');
                    if (svg) {
                        svg.classList.add('-rotate-90');
                        svg.classList.remove('rotate-0');
                    }
                });

                // Save preference to localStorage
                localStorage.setItem('filament_tree_expand_state', 'collapsed');
            });
        }

        // Apply initial expand/collapse state from localStorage or default
        function applyInitialExpandState() {
            const savedState = localStorage.getItem('filament_tree_expand_state');
            const defaultExpanded = {{ $tree->isDefaultExpanded() ? 'true' : 'false' }};
            const shouldExpand = savedState !== null ? savedState === 'expanded' : defaultExpanded;

            document.querySelectorAll('.tree-item').forEach(item => {
                const childrenContainer = item.querySelector('.filament-tree-children');
                if (childrenContainer) {
                    childrenContainer.style.display = shouldExpand ? 'block' : 'none';
                }
            });

            document.querySelectorAll('.tree-collapse-btn').forEach(btn => {
                const svg = btn.querySelector('svg');
                if (svg) {
                    if (shouldExpand) {
                        svg.classList.remove('-rotate-90');
                        svg.classList.add('rotate-0');
                    } else {
                        svg.classList.add('-rotate-90');
                        svg.classList.remove('rotate-0');
                    }
                }
            });
        }

        // Apply initial state after a short delay to ensure DOM is ready
        setTimeout(applyInitialExpandState, 50);
        @endif

        // ========================================
        // Save/Cancel Button Handlers
        // ========================================

        @if ($tree->shouldBatchSave())
        const saveBtn = document.getElementById('tree-save-btn');
        const cancelBtn = document.getElementById('tree-cancel-btn');

        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                if (treeInstance) {
                    treeInstance.saveChanges();
                }
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                if (treeInstance) {
                    treeInstance.cancelChanges();
                }
            });
        }
        @endif
    </script>
    @endscript
</x-filament-panels::page>
