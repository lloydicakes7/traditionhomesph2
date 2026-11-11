<?php

namespace App\Http\Livewire\Concerns;

trait WithListCriteria
{
    protected function listCriteria(): array
    {
        return [
            'search'    => (string) ($this->search ?? ''),
            'filters'   => $this->listFilters(),
            'sort'      => (string) ($this->sortField ?? ''),
            'direction' => (string) ($this->sortDirection ?? 'asc'),
            'perPage'   => (int) ($this->perPage ?? 15),
        ];
    }

    protected function listFilters(): array
    {
        if (property_exists($this, 'filters') && is_array($this->filters)) {
            return $this->filters;
        }

        $filters = [];

        foreach (get_object_vars($this) as $name => $value) {
            if (strpos($name, 'filter') !== 0) {
                continue;
            }

            $filters[lcfirst(substr($name, 6))] = $value;
        }

        return array_filter($filters, function ($value) {
            return $value !== '' && $value !== null;
        });
    }
}
