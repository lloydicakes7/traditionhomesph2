<?php

namespace App\Http\Livewire\Concerns;

/**
 * Shared list sorting behavior for the admin panel's list pages.
 *
 * Page components own the $sortField / $sortDirection public properties
 * (and their $queryString entry) — this trait only supplies the toggle and
 * search-reset logic so every page sorts identically.
 */
trait WithSorting
{
    /**
     * Toggle sort direction, or sort ascending by a new field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Return to page 1 whenever the search query changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
}
