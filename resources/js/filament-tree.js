/**
 * ==================================================================
 * FilamentTree - Drag & Drop Tree Component for Filament
 * ==================================================================
 *
 * A lightweight, accessible drag-and-drop tree manager using Pragmatic Drag & Drop.
 * Designed specifically for Filament PHP's admin panel framework.
 *
 * Key Features:
 * - ✅ Batch save mode (accumulate changes, save all at once)
 * - ✅ Max depth validation (prevent nesting too deep)
 * - ✅ Circular reference prevention (can't move parent into child)
 * - ✅ Visual drop indicators with position feedback
 * - ✅ Accessible keyboard navigation
 * - ✅ Livewire integration for backend persistence
 *
 * Technology Stack:
 * - Pragmatic Drag & Drop (@atlaskit/pragmatic-drag-and-drop)
 * - Hitbox Package (@atlaskit/pragmatic-drag-and-drop-hitbox)
 * - Livewire (Laravel real-time components)
 *
 * @see https://atlassian.design/components/pragmatic-drag-and-drop
 */

import { draggable, dropTargetForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import { monitorForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import { attachInstruction, extractInstruction } from '@atlaskit/pragmatic-drag-and-drop-hitbox/list-item';

/**
 * FilamentTree Class
 *
 * Manages drag-and-drop interactions for hierarchical tree structures in Filament.
 */
export default class FilamentTree {
    /**
     * Initialize FilamentTree instance
     *
     * @param {Function|null} onMove - Optional callback when item is moved (legacy)
     * @param {Object} options - Configuration options
     * @param {number|null} options.maxDepth - Maximum nesting depth (default: 10, null for unlimited)
     * @param {Object} options.livewireComponent - Livewire component instance
     * @param {boolean} options.autoSave - Enable auto-save mode (default: true)
     */
    constructor(onMove, options = {}) {
        this.onMove = onMove;
        this.options = {
            maxDepth: options.maxDepth ?? 10,
            livewireComponent: options.livewireComponent || null,
            autoSave: options.autoSave ?? true,
        };

        // State management
        this.cleanupFunctions = [];
        this.dropIndicator = null;
        this.pendingMoves = [];
        this.hasUnsavedChanges = false;
        this.isDragging = false;
        this.needsReinit = false;
    }

    /**
     * Initialize the tree component
     */
    init() {
        this.createDropIndicator();
        this.registerDraggables();
        this.registerDropTargets();
        this.registerDropAtEnd();
        this.registerMonitor();
    }

    /**
     * Create the visual drop indicator element
     */
    createDropIndicator() {
        this.dropIndicator = document.createElement('div');
        this.dropIndicator.className = 'filament-tree-drop-indicator';
        this.dropIndicator.style.position = 'fixed';
        this.dropIndicator.style.pointerEvents = 'none';
        this.dropIndicator.style.zIndex = '9999';
        this.dropIndicator.style.display = 'none';
        document.body.appendChild(this.dropIndicator);
    }

    /**
     * Check if an element is a descendant of a potential ancestor
     */
    isDescendantOf(element, potentialAncestor) {
        const elementId = parseInt(element.dataset.itemId);
        const ancestorId = parseInt(potentialAncestor.dataset.itemId);

        const childrenContainer = potentialAncestor.querySelector(':scope > .filament-tree-children');
        if (!childrenContainer) return false;

        const foundElement = childrenContainer.querySelector(`[data-item-id="${elementId}"]`);
        return foundElement !== null;
    }

    /**
     * Get the maximum depth of a subtree (how many levels deep its deepest child is)
     * Returns 0 if element has no children
     */
    getSubtreeDepth(element) {
        const childrenContainer = element.querySelector(':scope > .filament-tree-children');
        if (!childrenContainer) return 0;

        const directChildren = childrenContainer.querySelectorAll(':scope > [data-tree-item]');
        if (directChildren.length === 0) return 0;

        let maxChildDepth = 0;
        directChildren.forEach(child => {
            const childSubtreeDepth = this.getSubtreeDepth(child);
            maxChildDepth = Math.max(maxChildDepth, childSubtreeDepth);
        });

        // Return 1 (for the direct child level) + the max depth of any child's subtree
        return 1 + maxChildDepth;
    }

    /**
     * Register all tree items as draggable
     */
    registerDraggables() {
        const items = document.querySelectorAll('[data-tree-item]');

        items.forEach(item => {
            const handle = item.querySelector('[data-drag-handle]');
            if (!handle) return;

            const cleanup = draggable({
                element: item,
                dragHandle: handle,
                getInitialData: () => ({
                    type: 'tree-item',
                    id: parseInt(item.dataset.itemId),
                    depth: parseInt(item.dataset.depth) || 0,
                    parentId: parseInt(item.dataset.parentId) || -1,
                }),
                onDragStart: () => {
                    item.classList.add('filament-tree-dragging');
                },
                onDrop: () => {
                    item.classList.remove('filament-tree-dragging');
                },
            });

            this.cleanupFunctions.push(cleanup);
        });
    }

    /**
     * Register all tree items as drop targets
     */
    registerDropTargets() {
        const items = document.querySelectorAll('[data-tree-item]');

        items.forEach(item => {
            const content = item.querySelector(':scope > .filament-tree-node-content');
            if (!content) return;

            const cleanup = dropTargetForElements({
                element: content,
                canDrop: ({ source }) => source.element !== item,
                getIsSticky: () => true,
                getData: ({ input, element, source }) => {
                    const treeItem = item;
                    const data = {
                        type: 'tree-item',
                        id: parseInt(treeItem.dataset.itemId),
                    };

                    const targetDepth = parseInt(treeItem.dataset.depth) || 0;
                    const targetId = parseInt(treeItem.dataset.itemId);

                    let combineBlocked = false;
                    let reorderBeforeBlocked = false;
                    let reorderAfterBlocked = false;

                    if (source?.element) {
                        const sourceElement = source.element;
                        const sourceId = parseInt(sourceElement.dataset.itemId);
                        const droppingOnSelf = sourceId === targetId;
                        const isDescendant = this.isDescendantOf(treeItem, sourceElement);

                        // Calculate the depth including the source's subtree
                        const sourceSubtreeDepth = this.getSubtreeDepth(sourceElement);
                        const combineDepth = targetDepth + 1 + sourceSubtreeDepth;
                        // maxDepth null means unlimited depth
                        const exceedsDepth = this.options.maxDepth !== null && combineDepth >= this.options.maxDepth;

                        combineBlocked = droppingOnSelf || isDescendant || exceedsDepth;
                        reorderBeforeBlocked = droppingOnSelf || isDescendant;
                        reorderAfterBlocked = droppingOnSelf || isDescendant;
                    }

                    return attachInstruction(data, {
                        input,
                        element: content,
                        operations: {
                            'reorder-before': reorderBeforeBlocked ? 'blocked' : 'available',
                            'reorder-after': reorderAfterBlocked ? 'blocked' : 'available',
                            'combine': combineBlocked ? 'blocked' : 'available',
                        },
                        axis: 'vertical',
                    });
                },
                onDragEnter: ({ self, location }) => {
                    const closestTarget = location.current.dropTargets[0];
                    if (closestTarget?.element !== self.element) return;

                    const instruction = extractInstruction(self.data);
                    if (instruction) {
                        this.showDropIndicator(item, instruction.operation, instruction.blocked);
                    }
                },
                onDrag: ({ self, location }) => {
                    const closestTarget = location.current.dropTargets[0];
                    if (closestTarget?.element !== self.element) return;

                    const instruction = extractInstruction(self.data);
                    if (instruction) {
                        this.showDropIndicator(item, instruction.operation, instruction.blocked);
                    }
                },
                onDragLeave: ({ self, location }) => {
                    const closestTarget = location.current.dropTargets[0];
                    if (closestTarget?.element !== self.element) return;

                    this.hideDropIndicator();
                },
                onDrop: ({ source, self, location }) => {
                    const closestTarget = location.current.dropTargets[0];
                    if (closestTarget?.element !== self.element) return;

                    this.hideDropIndicator();

                    if (!source?.data || !self?.data) return;

                    const instruction = extractInstruction(self.data);
                    if (!instruction || instruction.blocked) return;

                    const sourceId = source.data.id;
                    const targetId = self.data.id;
                    let moveData;

                    if (instruction.operation === 'combine') {
                        moveData = {
                            nodeId: sourceId,
                            newParentId: targetId,
                            position: 'inside',
                            referenceId: null,
                        };
                    } else if (instruction.operation === 'reorder-before') {
                        const targetElement = document.querySelector(`[data-item-id="${targetId}"]`);
                        const targetParentId = parseInt(targetElement?.dataset.parentId || '-1');
                        moveData = {
                            nodeId: sourceId,
                            newParentId: targetParentId,
                            position: 'before',
                            referenceId: targetId,
                        };
                    } else if (instruction.operation === 'reorder-after') {
                        const targetElement = document.querySelector(`[data-item-id="${targetId}"]`);
                        const targetParentId = parseInt(targetElement?.dataset.parentId || '-1');
                        moveData = {
                            nodeId: sourceId,
                            newParentId: targetParentId,
                            position: 'after',
                            referenceId: targetId,
                        };
                    }

                    if (!this.options.autoSave) {
                        // Manual save mode: track changes and update DOM optimistically
                        this.pendingMoves.push(moveData);
                        this.hasUnsavedChanges = true;
                        this.updateButtonStates();

                        // Perform optimistic DOM update
                        this.applyMoveToDOM(source.element, item, instruction.operation, moveData);

                        // Mark that we need reinit - will be handled by monitor after all handlers complete
                        this.needsReinit = true;
                    } else if (this.options.livewireComponent && moveData) {
                        // Auto-save mode: save to server immediately
                        // Also apply optimistic DOM update for instant feedback
                        this.applyMoveToDOM(source.element, item, instruction.operation, moveData);
                        this.needsReinit = true;

                        // Call Livewire to save
                        this.options.livewireComponent.call('reorderTree', [moveData]);
                    }
                },
            });

            this.cleanupFunctions.push(cleanup);
        });
    }

    /**
     * Register "drop at end" zone for root level
     */
    registerDropAtEnd() {
        const dropAtEnd = document.querySelector('[data-drop-at-end]');
        if (!dropAtEnd) return;

        const cleanup = dropTargetForElements({
            element: dropAtEnd,
            getIsSticky: () => true,
            getData: () => ({ type: 'drop-at-end', depth: 0 }),
            onDragEnter: () => {
                const rect = dropAtEnd.getBoundingClientRect();
                const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-600').trim();

                this.dropIndicator.style.display = 'block';
                this.dropIndicator.style.left = `${rect.left}px`;
                this.dropIndicator.style.width = `${rect.width}px`;
                this.dropIndicator.style.top = `${rect.top}px`;
                this.dropIndicator.style.height = '3px';
                this.dropIndicator.style.backgroundColor = primaryColor;
                this.dropIndicator.style.borderRadius = '3px';
                this.dropIndicator.style.boxShadow = `0 0 8px ${primaryColor}`;
            },
            onDrop: ({ source }) => {
                this.hideDropIndicator();
                if (!source?.data) return;

                const rootItems = document.querySelectorAll('.filament-tree-container > [data-tree-item]');
                const lastRootItem = rootItems[rootItems.length - 1];
                const lastRootItemId = parseInt(lastRootItem?.dataset.itemId || '-1');

                const moveData = {
                    nodeId: source.data.id,
                    newParentId: -1,
                    position: 'after',
                    referenceId: lastRootItemId,
                };

                if (!this.options.autoSave) {
                    // Manual save mode: track changes and update DOM optimistically
                    this.pendingMoves.push(moveData);
                    this.hasUnsavedChanges = true;
                    this.updateButtonStates();

                    // Perform optimistic DOM update
                    if (lastRootItem) {
                        this.applyMoveToDOM(source.element, lastRootItem, 'reorder-after', moveData);
                    }

                    // Mark that we need reinit
                    this.needsReinit = true;
                } else if (this.options.livewireComponent) {
                    // Auto-save mode: save to server immediately
                    // Also apply optimistic DOM update for instant feedback
                    if (lastRootItem) {
                        this.applyMoveToDOM(source.element, lastRootItem, 'reorder-after', moveData);
                    }
                    this.needsReinit = true;

                    // Call Livewire to save
                    this.options.livewireComponent.call('reorderTree', [moveData]);
                }
            },
        });

        this.cleanupFunctions.push(cleanup);
    }

    /**
     * Show drop indicator
     */
    showDropIndicator(element, operation, blocked = false) {
        if (!this.dropIndicator) return;

        const content = element.querySelector(':scope > .filament-tree-node-content');
        if (!content) return;

        const rect = content.getBoundingClientRect();

        // Get computed color values from CSS variables
        const styles = getComputedStyle(document.documentElement);
        const primaryColor = styles.getPropertyValue('--primary-600').trim();
        const dangerColor = styles.getPropertyValue('--danger-600').trim();
        const color = blocked ? dangerColor : primaryColor;

        this.dropIndicator.style.display = 'block';

        if (operation === 'combine') {
            const inset = 4;
            this.dropIndicator.style.left = `${rect.left + inset}px`;
            this.dropIndicator.style.width = `${rect.width - (inset * 2)}px`;
            this.dropIndicator.style.top = `${rect.top + inset}px`;
            this.dropIndicator.style.height = `${rect.height - (inset * 2)}px`;
            this.dropIndicator.style.backgroundColor = 'transparent';
            this.dropIndicator.style.border = `3px dashed ${color}`;
            this.dropIndicator.style.borderRadius = '8px';
        } else if (operation === 'reorder-before') {
            this.dropIndicator.style.left = `${rect.left}px`;
            this.dropIndicator.style.width = `${rect.width}px`;
            this.dropIndicator.style.top = `${rect.top - 6}px`;
            this.dropIndicator.style.height = '3px';
            this.dropIndicator.style.backgroundColor = color;
            this.dropIndicator.style.border = 'none';
            this.dropIndicator.style.borderRadius = '3px';
        } else if (operation === 'reorder-after') {
            this.dropIndicator.style.left = `${rect.left}px`;
            this.dropIndicator.style.width = `${rect.width}px`;
            this.dropIndicator.style.top = `${rect.bottom + 3}px`;
            this.dropIndicator.style.height = '3px';
            this.dropIndicator.style.backgroundColor = color;
            this.dropIndicator.style.border = 'none';
            this.dropIndicator.style.borderRadius = '3px';
        }
    }

    /**
     * Hide drop indicator
     */
    hideDropIndicator() {
        if (this.dropIndicator) {
            this.dropIndicator.style.display = 'none';
        }
    }

    /**
     * Register global monitor
     */
    registerMonitor() {
        const cleanup = monitorForElements({
            onDragStart: () => {
                this.isDragging = true;
            },
            onDrop: () => {
                this.isDragging = false;
                this.hideDropIndicator();

                if (this.needsReinit) {
                    setTimeout(() => {
                        if (!this.isDragging && this.needsReinit) {
                            this.reinit();
                            this.needsReinit = false;
                        }
                    }, 50);
                }
            },
        });

        this.cleanupFunctions.push(cleanup);
    }

    /**
     * Apply move to DOM (optimistic update)
     */
    applyMoveToDOM(sourceElement, targetElement, operation, moveData) {
        if (!sourceElement || !targetElement) return;

        if (operation === 'combine') {
            // Move as child of target
            let childrenContainer = targetElement.querySelector(':scope > .filament-tree-children');

            // If no children container exists, create one
            if (!childrenContainer) {
                childrenContainer = document.createElement('div');
                childrenContainer.className = 'filament-tree-children';
                // All styling is handled by CSS - no inline styles needed
                targetElement.appendChild(childrenContainer);
            }

            // Append source element as child
            childrenContainer.appendChild(sourceElement);

            // Update depth recursively
            const targetDepth = parseInt(targetElement.dataset.depth) || 0;
            this.updateItemDepth(sourceElement, targetDepth + 1);
            sourceElement.dataset.parentId = moveData.newParentId;

        } else if (operation === 'reorder-before') {
            // Insert before target element
            targetElement.parentElement.insertBefore(sourceElement, targetElement);

            // Update depth to match target's level
            const targetDepth = parseInt(targetElement.dataset.depth) || 0;
            this.updateItemDepth(sourceElement, targetDepth);
            sourceElement.dataset.parentId = targetElement.dataset.parentId || '-1';

        } else if (operation === 'reorder-after') {
            // Insert after target element
            if (targetElement.nextSibling) {
                targetElement.parentElement.insertBefore(sourceElement, targetElement.nextSibling);
            } else {
                targetElement.parentElement.appendChild(sourceElement);
            }

            // Update depth to match target's level
            const targetDepth = parseInt(targetElement.dataset.depth) || 0;
            this.updateItemDepth(sourceElement, targetDepth);
            sourceElement.dataset.parentId = targetElement.dataset.parentId || '-1';
        }

        // Clean up empty containers
        this.cleanupEmptyContainers();
    }

    /**
     * Update item depth recursively
     */
    updateItemDepth(item, newDepth) {
        item.dataset.depth = newDepth;

        // Update all children recursively
        const childrenContainer = item.querySelector(':scope > .filament-tree-children');
        if (childrenContainer) {
            const children = childrenContainer.querySelectorAll(':scope > [data-tree-item]');
            children.forEach(child => {
                this.updateItemDepth(child, newDepth + 1);
            });
        }
    }

    /**
     * Clean up empty tree-children containers
     */
    cleanupEmptyContainers() {
        const allChildrenContainers = document.querySelectorAll('.filament-tree-children');
        allChildrenContainers.forEach(container => {
            const hasChildren = container.querySelector('[data-tree-item]') !== null;
            if (!hasChildren) {
                container.remove();
            }
        });
    }

    /**
     * Reinitialize after DOM updates
     */
    reinit() {
        // Clean up all event listeners
        this.cleanupFunctions.forEach(cleanup => cleanup());
        this.cleanupFunctions = [];

        // Re-register everything with fresh DOM
        this.registerDraggables();
        this.registerDropTargets();
        this.registerDropAtEnd();
        this.registerMonitor();
    }

    /**
     * Save all pending moves to backend
     */
    saveChanges() {
        if (this.options.autoSave || this.pendingMoves.length === 0) {
            return;
        }

        if (this.options.livewireComponent) {
            // Use call() for Livewire 3 compatibility
            // livewireComponent IS $wire, so call directly
            this.options.livewireComponent.call('reorderTree', this.pendingMoves);
            this.pendingMoves = [];
            this.hasUnsavedChanges = false;
            this.updateButtonStates();
        }
    }

    /**
     * Cancel all pending moves and reload from server
     */
    cancelChanges() {
        if (this.options.autoSave) {
            return;
        }

        this.pendingMoves = [];
        this.hasUnsavedChanges = false;
        this.updateButtonStates();

        // Reload the page to reset tree state
        if (this.options.livewireComponent) {
            this.options.livewireComponent.$refresh();
        }
    }

    /**
     * Update Save/Cancel button states based on pending changes
     */
    updateButtonStates() {
        if (this.options.autoSave) {
            return;
        }

        const saveBtn = document.getElementById('tree-save-btn');
        const cancelBtn = document.getElementById('tree-cancel-btn');
        const indicator = document.getElementById('tree-changes-indicator');

        if (saveBtn && cancelBtn && indicator) {
            if (this.hasUnsavedChanges) {
                // Enable buttons (Filament style)
                saveBtn.removeAttribute('disabled');
                saveBtn.removeAttribute('aria-disabled');
                saveBtn.classList.remove('fi-disabled');

                cancelBtn.removeAttribute('disabled');
                cancelBtn.removeAttribute('aria-disabled');
                cancelBtn.classList.remove('fi-disabled');

                indicator.classList.remove('hidden');
                indicator.classList.add('flex');
            } else {
                // Disable buttons (Filament style)
                saveBtn.setAttribute('disabled', '');
                saveBtn.setAttribute('aria-disabled', 'true');
                saveBtn.classList.add('fi-disabled');

                cancelBtn.setAttribute('disabled', '');
                cancelBtn.setAttribute('aria-disabled', 'true');
                cancelBtn.classList.add('fi-disabled');

                indicator.classList.add('hidden');
                indicator.classList.remove('flex');
            }
        }
    }

    /**
     * Destroy and cleanup
     */
    destroy() {
        this.cleanupFunctions.forEach(cleanup => cleanup());
        this.cleanupFunctions = [];
        if (this.dropIndicator) {
            this.dropIndicator.remove();
            this.dropIndicator = null;
        }
    }
}

// Export to window for easy access in Blade templates
window.FilamentTree = FilamentTree;
