<?php

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

/**
 * Trait for Livewire components that need search and filter functionality
 *
 * Provides:
 * - Search property and updatedSearch method
 * - Filter reset functionality
 * - Automatic page reset on filter changes
 */
trait HasSearchAndFilters
{
    use WithPagination;

    /**
     * Search query string
     */
    public ?string $search = null;

    /**
     * Get filter properties that should trigger page reset
     * Override this method in component to customize
     *
     * @return array<string>
     */
    protected function getFilterProperties(): array
    {
        return ['search', 'status', 'post_type', 'editorFilter', 'categoryFilter'];
    }

    /**
     * Get query string configuration for filters
     * Override this method in component to customize
     *
     * @return array<string, array<string, string>>
     */
    protected function getFilterQueryString(): array
    {
        $filters = [];
        foreach ($this->getFilterProperties() as $property) {
            if (property_exists($this, $property)) {
                $filters[$property] = ['except' => ''];
            }
        }

        return $filters;
    }

    /**
     * Reset filters to default values
     * Override this method in component to customize default values
     */
    public function resetFilters(): void
    {
        foreach ($this->getFilterProperties() as $property) {
            if (property_exists($this, $property)) {
                $this->$property = null;
            }
        }
        $this->resetPage();
    }

    /**
     * Handle search property update
     */
    public function updatedSearch(): void
    {
        $this->onFilterUpdated('search');
    }

    /**
     * Handle filter property updates
     * This method is called automatically by Livewire for any property matching filter properties
     * Component can override this to add custom logic, but should call parent::updated() first
     */
    public function updated($propertyName): void
    {
        if (in_array($propertyName, $this->getFilterProperties())) {
            $this->onFilterUpdated($propertyName);
        }
    }

    /**
     * Called when a filter is updated
     * Override this method in component to add custom logic
     */
    protected function onFilterUpdated(string $propertyName): void
    {
        // Reset selection if component uses bulk actions
        if (method_exists($this, 'resetSelection')) {
            $this->resetSelection();
        }

        $this->resetPage();
    }
}

