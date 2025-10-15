<x-filament-panels::page>
    @if ($tree = $this->getTree())
        @php
            $records = $this->getTreeRecords();
        @endphp

        {{-- Inline script to prevent flash of wrong expand state --}}
        @if ($tree->isCollapsible())
            <script>
                (function() {
                    const savedState = localStorage.getItem('filament_tree_expand_state');
                    const defaultExpanded = {{ $tree->isDefaultExpanded() ? 'true' : 'false' }};
                    const shouldExpand = savedState !== null ? savedState === 'expanded' : defaultExpanded;

                    // Apply state as soon as DOM is ready
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', applyState);
                    } else {
                        applyState();
                    }

                    function applyState() {
                        // Apply visibility to children containers
                        document.querySelectorAll('.filament-tree-children').forEach(container => {
                            container.style.display = shouldExpand ? 'block' : 'none';
                        });

                        // Sync chevron rotation with visibility
                        document.querySelectorAll('.tree-toggle-btn').forEach(button => {
                            const svg = button.querySelector('svg');
                            if (svg) {
                                if (shouldExpand) {
                                    svg.classList.remove('-rotate-90');
                                    svg.classList.add('rotate-0');
                                } else {
                                    svg.classList.remove('rotate-0');
                                    svg.classList.add('-rotate-90');
                                }
                            }
                        });
                    }
                })();
            </script>
        @endif

        @if (count($records) > 0)
            {{-- Header Bar --}}
            <div class="flex items-center justify-between gap-3 -mb-4">
                {{-- Left Side: Expand/Collapse Buttons --}}
                @if ($tree->isCollapsible())
                    <div class="fi-btn-group">
                    <x-filament::button
                        type="button"
                        id="tree-expand-all"
                        color="gray"
                        size="md"
                        grouped
                    >
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </x-slot>
                        Expand
                    </x-filament::button>
                    <x-filament::button
                        type="button"
                        id="tree-collapse-all"
                        color="gray"
                        size="md"
                        grouped
                    >
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            </svg>
                        </x-slot>
                        Collapse
                    </x-filament::button>
                </div>
                @else
                    <div></div> {{-- Spacer for flex layout --}}
                @endif

                {{-- Right Side: Status, Action Buttons, and Header Actions --}}
                <div class="flex items-center gap-4">
                    {{-- Unsaved Changes Indicator and Save/Cancel Buttons --}}
                    @if (!$tree->isAutoSave())
                        {{-- Unsaved Changes Indicator --}}
                        <div
                            id="tree-changes-indicator"
                            class="hidden items-center gap-2"
                            style="color: var(--warning-600);"
                        >
                            <svg class="h-4 w-4 animate-pulse" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span class="text-sm">Unsaved changes</span>
                        </div>

                        {{-- Cancel Button --}}
                        <x-filament::button
                            type="button"
                            id="tree-cancel-btn"
                            color="gray"
                            disabled
                            size="md"
                        >
                            <x-slot name="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </x-slot>
                            Cancel
                        </x-filament::button>

                        {{-- Save Button --}}
                        <x-filament::button
                            type="button"
                            id="tree-save-btn"
                            color="gray"
                            disabled
                            size="md"
                        >
                            <x-slot name="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </x-slot>
                            Save Changes
                        </x-filament::button>
                    @endif
                </div>
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

                {{-- Drop zone at end for root level --}}
                <div
                    class="filament-tree-drop-at-end"
                    data-drop-at-end
                    data-depth="0"
                ></div>
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
        const isCollapsible = {{ $tree->isCollapsible() ? 'true' : 'false' }};
        const isAutoSave = {{ $tree->isAutoSave() ? 'true' : 'false' }};
        const defaultExpanded = {{ $tree->isDefaultExpanded() ? 'true' : 'false' }};

        function initTree() {
            if (treeInstance) {
                treeInstance.destroy();
            }

            if (typeof window.FilamentTree === 'undefined') {
                return;
            }

            const component = $wire;

            if (component) {
                treeInstance = new window.FilamentTree(null, {
                    maxDepth: {{ $tree->getMaxDepth() }},
                    livewireComponent: component,
                    autoSave: isAutoSave,
                });

                treeInstance.init();
                window.currentTreeInstance = treeInstance;
            }
        }

        initTree();

        Livewire.hook('commit', ({ component, respond }) => {
            // Before Livewire updates DOM, save current expand/collapse state of all nodes
            const expandedNodes = new Set();
            if (isCollapsible) {
                document.querySelectorAll('[data-tree-item]').forEach(item => {
                    const childrenContainer = item.querySelector('.filament-tree-children');
                    if (childrenContainer && childrenContainer.style.display !== 'none') {
                        const recordId = item.getAttribute('data-item-id');
                        if (recordId) {
                            expandedNodes.add(recordId);
                        }
                    }
                });
            }

            respond(() => {
                // Use requestAnimationFrame to ensure DOM is ready and prevent flicker
                requestAnimationFrame(() => {
                    // In manual save mode (!autoSave), only reinit if we don't have unsaved changes
                    // Otherwise, reinitializing would wipe out our optimistic DOM updates
                    if (!isAutoSave && window.currentTreeInstance?.hasUnsavedChanges) {
                        // Just reinit event handlers, don't destroy and recreate
                        if (window.currentTreeInstance?.needsReinit) {
                            window.currentTreeInstance.reinit();
                            window.currentTreeInstance.needsReinit = false;
                        }
                    } else {
                        // Normal mode or no unsaved changes: full reinit
                        initTree();

                        // Restore individual node expand/collapse states immediately to prevent flicker
                        // Always restore if collapsible, even if all nodes were collapsed (expandedNodes.size === 0)
                        if (isCollapsible) {
                            // Also check localStorage for individual node states as a failsafe
                            const storageKey = 'filament_tree_nodes_state';
                            let nodesState = {};
                            try {
                                const stored = localStorage.getItem(storageKey);
                                if (stored) nodesState = JSON.parse(stored);
                            } catch (e) {}

                            document.querySelectorAll('[data-tree-item]').forEach(item => {
                                const recordId = item.getAttribute('data-item-id');
                                const childrenContainer = item.querySelector('.filament-tree-children');
                                const toggleBtn = item.querySelector('.tree-toggle-btn');
                                const svg = toggleBtn?.querySelector('svg');

                                if (childrenContainer && recordId) {
                                    // Check captured state first, then localStorage, then default
                                    let shouldBeExpanded = expandedNodes.has(recordId);

                                    // If we have an individual node state in localStorage, prefer that
                                    if (nodesState.hasOwnProperty(recordId)) {
                                        shouldBeExpanded = nodesState[recordId];
                                    }

                                    childrenContainer.style.display = shouldBeExpanded ? 'block' : 'none';

                                    // Sync chevron rotation
                                    if (svg) {
                                        if (shouldBeExpanded) {
                                            svg.classList.remove('-rotate-90');
                                            svg.classList.add('rotate-0');
                                        } else {
                                            svg.classList.remove('rotate-0');
                                            svg.classList.add('-rotate-90');
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
            });
        });

        // Global function to toggle individual tree nodes
        window.toggleTreeNode = function(button, recordId) {
            const item = button.closest('[data-tree-item]');
            const childrenContainer = item.querySelector('.filament-tree-children');
            const svg = button.querySelector('svg');

            if (childrenContainer) {
                const isCurrentlyVisible = childrenContainer.style.display !== 'none';

                // Toggle visibility
                childrenContainer.style.display = isCurrentlyVisible ? 'none' : 'block';

                // Toggle chevron rotation
                if (isCurrentlyVisible) {
                    svg.classList.remove('rotate-0');
                    svg.classList.add('-rotate-90');
                } else {
                    svg.classList.remove('-rotate-90');
                    svg.classList.add('rotate-0');
                }

                // Persist individual node state to localStorage
                const storageKey = 'filament_tree_nodes_state';
                let nodesState = {};
                try {
                    const stored = localStorage.getItem(storageKey);
                    if (stored) nodesState = JSON.parse(stored);
                } catch (e) {}

                nodesState[recordId] = !isCurrentlyVisible;
                localStorage.setItem(storageKey, JSON.stringify(nodesState));
            }
        };

        if (isCollapsible) {
            const expandAllBtn = document.getElementById('tree-expand-all');
            const collapseAllBtn = document.getElementById('tree-collapse-all');

            if (expandAllBtn) {
                expandAllBtn.addEventListener('click', () => {
                    document.querySelectorAll('[data-tree-item]').forEach(item => {
                        const childrenContainer = item.querySelector('.filament-tree-children');
                        const toggleBtn = item.querySelector('.tree-toggle-btn');
                        const svg = toggleBtn?.querySelector('svg');

                        if (childrenContainer) {
                            childrenContainer.style.display = 'block';
                        }

                        // Update chevron to expanded state
                        if (svg) {
                            svg.classList.remove('-rotate-90');
                            svg.classList.add('rotate-0');
                        }
                    });
                    localStorage.setItem('filament_tree_expand_state', 'expanded');
                    // Clear individual node states since we're expanding all
                    localStorage.removeItem('filament_tree_nodes_state');
                });
            }

            if (collapseAllBtn) {
                collapseAllBtn.addEventListener('click', () => {
                    document.querySelectorAll('[data-tree-item]').forEach(item => {
                        const childrenContainer = item.querySelector('.filament-tree-children');
                        const toggleBtn = item.querySelector('.tree-toggle-btn');
                        const svg = toggleBtn?.querySelector('svg');

                        if (childrenContainer) {
                            childrenContainer.style.display = 'none';
                        }

                        // Update chevron to collapsed state
                        if (svg) {
                            svg.classList.remove('rotate-0');
                            svg.classList.add('-rotate-90');
                        }
                    });
                    localStorage.setItem('filament_tree_expand_state', 'collapsed');
                    // Clear individual node states since we're collapsing all
                    localStorage.removeItem('filament_tree_nodes_state');
                });
            }

            function applyInitialExpandState() {
                const savedState = localStorage.getItem('filament_tree_expand_state');
                const shouldExpand = savedState !== null ? savedState === 'expanded' : defaultExpanded;

                // Get individual node states from localStorage
                const storageKey = 'filament_tree_nodes_state';
                let nodesState = {};
                try {
                    const stored = localStorage.getItem(storageKey);
                    if (stored) nodesState = JSON.parse(stored);
                } catch (e) {}

                document.querySelectorAll('[data-tree-item]').forEach(item => {
                    const childrenContainer = item.querySelector('.filament-tree-children');
                    const toggleBtn = item.querySelector('.tree-toggle-btn');
                    const svg = toggleBtn?.querySelector('svg');
                    const recordId = item.getAttribute('data-item-id');

                    if (childrenContainer) {
                        // Check if this specific node has a saved state
                        let nodeExpanded = shouldExpand;
                        if (recordId && nodesState.hasOwnProperty(recordId)) {
                            nodeExpanded = nodesState[recordId];
                        }

                        childrenContainer.style.display = nodeExpanded ? 'block' : 'none';

                        // Sync chevron rotation with visibility
                        if (svg) {
                            if (nodeExpanded) {
                                svg.classList.remove('-rotate-90');
                                svg.classList.add('rotate-0');
                            } else {
                                svg.classList.remove('rotate-0');
                                svg.classList.add('-rotate-90');
                            }
                        }
                    }
                });
            }

            // Apply immediately on first load to prevent flash
            applyInitialExpandState();
        }

        if (!isAutoSave) {
            const saveBtn = document.getElementById('tree-save-btn');
            const cancelBtn = document.getElementById('tree-cancel-btn');

            if (saveBtn) {
                saveBtn.addEventListener('click', () => {
                    if (window.currentTreeInstance) {
                        window.currentTreeInstance.saveChanges();
                    }
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    if (window.currentTreeInstance) {
                        window.currentTreeInstance.cancelChanges();
                    }
                });
            }
        }
    </script>
    @endscript
</x-filament-panels::page>
