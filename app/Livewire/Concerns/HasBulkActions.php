<?php

namespace App\Livewire\Concerns;

/**
 * Trait for Livewire components that need bulk action functionality
 *
 * Provides:
 * - Selected items tracking
 * - Select all functionality
 * - Selection reset
 *
 * Component must define:
 * - protected function getSelectedItemsPropertyName(): string (returns property name like 'selectedPosts')
 * - protected function getVisibleItemIds(): array (returns array of visible item IDs)
 */
trait HasBulkActions
{
    /**
     * Select all checkbox state
     */
    public bool $selectAll = false;

    /**
     * Bulk action to apply
     */
    public string $bulkAction = '';

    /**
     * Get the property name for selected items
     * Override this in component if different from 'selectedItems'
     * Examples: 'selectedPosts', 'selectedLogs', 'selectedStocks'
     *
     * @return string
     */
    protected function getSelectedItemsPropertyName(): string
    {
        return 'selectedItems';
    }

    /**
     * Get visible item IDs for current page
     * Component must implement this method
     *
     * @return array<int|string>
     */
    abstract protected function getVisibleItemIds(): array;

    /**
     * Reset selection
     */
    public function resetSelection(): void
    {
        $propertyName = $this->getSelectedItemsPropertyName();
        $this->$propertyName = [];
        $this->selectAll = false;
    }

    /**
     * Handle select all checkbox update
     */
    public function updatedSelectAll($value): void
    {
        $propertyName = $this->getSelectedItemsPropertyName();

        if ($value) {
            $this->$propertyName = $this->getVisibleItemIds();
        } else {
            $this->$propertyName = [];
        }
    }

    /**
     * Handle individual item selection update
     * This method is called automatically by Livewire for the selected items property
     * Component can override this to add custom logic, but should call parent::updated() first
     */
    public function updated($propertyName): void
    {
        $selectedPropertyName = $this->getSelectedItemsPropertyName();

        if ($propertyName === $selectedPropertyName) {
            if (! is_array($this->$propertyName)) {
                $this->$propertyName = [];
            }

            $visibleIds = $this->getVisibleItemIds();
            $diff = array_diff($visibleIds, $this->$propertyName);
            $this->selectAll = empty($diff);
        }
    }

    /**
     * Apply bulk action
     * Component must implement this method to define action logic
     *
     * @return void
     */
    abstract public function applyBulkAction(): void;

    /**
     * Clear bulk action state after successful operation
     */
    protected function clearBulkActionState(): void
    {
        $propertyName = $this->getSelectedItemsPropertyName();
        $this->$propertyName = [];
        $this->selectAll = false;
        $this->bulkAction = '';
    }
}

