<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Trait for Livewire components that need column visibility preferences
 *
 * Provides:
 * - Column visibility tracking
 * - User preference loading and saving
 *
 * Component must define:
 * - protected function getDefaultColumns(): array (returns default column visibility)
 */
trait HasColumnPreferences
{
    /**
     * Visible columns configuration
     *
     * @var array<string, bool>
     */
    public array $visibleColumns = [];

    /**
     * Get default column visibility configuration
     * Component must implement this method
     *
     * @return array<string, bool>
     */
    abstract protected function getDefaultColumns(): array;

    /**
     * Load user column preferences
     */
    public function loadUserColumnPreferences(): void
    {
        $user = Auth::user();
        $defaultColumns = $this->getDefaultColumns();

        if ($user && $user instanceof \App\Models\User && $user->table_columns) {
            if (is_array($user->table_columns)) {
                $userColumns = $user->table_columns;
            } elseif (is_string($user->table_columns)) {
                $userColumns = json_decode($user->table_columns, true) ?? [];
            } else {
                $userColumns = [];
            }
            $this->visibleColumns = array_merge($defaultColumns, $userColumns);
        } else {
            $this->visibleColumns = $defaultColumns;
        }
    }

    /**
     * Handle column visibility update
     */
    public function updatedVisibleColumns(): void
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['table_columns' => $this->visibleColumns]);
        }
    }
}
