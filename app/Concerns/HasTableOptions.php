<?php

namespace App\Concerns;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasTableOptions
{
    // Search engine
    public string $searchQuery = '';

    /**
     * Resets pagination when the search query is updated.
     *
     * @param  string  $property
     */
    public function updatedWithSearch($property): void
    {
        if ($property === 'searchQuery') {
            $this->resetPage();
        }
    }

    // Sorting engine
    public string $sortBy = '';

    public string $sortDir = 'asc';

    public function sortByColumn(string $column, ?string $dir = null): void
    {
        if ($dir === '') {
            if ($this->sortBy === $column) {
                $this->clearSorting();
            }

            return;
        }

        if ($dir && $dir !== '') {
            $this->sortBy = $column;
            $this->sortDir = $dir;

            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function clearSorting(): void
    {
        $this->sortBy = '';
        $this->sortDir = 'asc';
    }

    public function applySorting($query)
    {
        if (! filled($this->sortBy)) {
            return $query;
        }

        if (! $this->isSortableColumn($this->sortBy)) {
            return $query;
        }

        if (method_exists($this, 'sortUsingAlgorithm')) {
            return $this->sortUsingAlgorithm($query, $this->sortBy, $this->sortDir) ?? $this->defaultSort($query);
        }

        return $this->defaultSort($query);
    }

    protected function defaultSort($query)
    {
        return $query->orderBy($this->sortBy, $this->sortDir);
    }

    protected function isSortableColumn(string $column): bool
    {
        $sortableCols = $this->sortableColumns();

        if (empty($sortableCols)) {
            return true;
        }

        return in_array($column, $sortableCols);
    }

    protected function sortableColumns(): array
    {
        return [];
    }

    // selection engine
    public array $selectedIds = [];

    public array $visibleIds = [];

    // while we can handle them from here but let's keep all interaction cames from the front end
    public function selectAll()
    {
        $this->dispatch('selectAll');
    }

    public function deselectAll()
    {
        $this->selectedIds = [];
        $this->dispatch('deselectAll');
    }

    protected function applySelection($query)
    {
        return $query->whereIn('id', $this->selectedIds);
    }

    

    // CSB

    /**
     * Generates a CSV string from the given models.
     *
     * @param  Collection  $models
     * @return string
     */
    protected function generateCsv($models)
    {
        if ($models->isEmpty()) {
            return ''; // Return empty string if no models
        }

        // Get the column titles from the first model's attributes
        $titles = implode(',', array_keys($models->first()->getAttributes()));

        // Map each model to a CSV row
        $csvRows = $models->map(function ($model) {
            return implode(',', collect($model->getAttributes())->map(function ($value) {
                // Handle null values
                if ($value === null) {
                    return '""';
                }

                // Cast to string, escape double quotes and wrap each value in quotes
                return '"'.str_replace('"', '""', (string) $value).'"';
            })->toArray());
        });

        // Prepend column titles to the CSV rows
        $csvRows->prepend($titles);

        // Return the CSV content as a string
        return $csvRows->implode(PHP_EOL);
    }

    /**
     * Streams the CSV content as a downloadable file.
     *
     * @param  string  $content
     * @return StreamedResponse
     */
    protected function streamCsv($content)
    {
        $filename = $this->getCsvFilename(); // Make this dynamic

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function getCsvFilename(): string
    {
        return 'export_'.now()->format('Y-m-d_His').'.csv';
    }

    /**
     * Exports the given records as a CSV file.
     *
     * @param  Collection  $records
     * @return StreamedResponse
     */
    protected function csv($records)
    {
        $csvContent = $this->generateCsv($records);

        return $this->streamCsv($csvContent);
    }


}

