<?php

namespace Rufaidulk\DataGrid\Filters;

final class FilterQuery
{
    private $query;
    private $filters;
    private $tableColumns;
    private $filterParams;
    
    public function __construct($tableColumns, $filters, $filterParams, $query)
    {
        $this->query = $query;
        $this->filters = $filters;
        $this->tableColumns = $tableColumns;
        $this->filterParams = $filterParams;
    }

    public function handle()
    {
        $this->filterQuery();

        return [$this->query, $this->filters];
    }

    private function filterQuery()
    {
        $key = 1;
        foreach ($this->tableColumns as $attribute => $column)
        {
            if (! $this->columnHasFilter($column) || ! $this->filterParamHasAttribute($attribute)) {
                ++$key;
                continue;
            }

            $this->filters[$key]['value'] = $this->filterParams[$attribute];

            if ($this->columnHasFilterOption($column)) {
                $this->applyFilterByFilterOptions($column['filterOptions'], $attribute);
            }
            else {
                $this->query = $this->query->where($attribute, 'like', '%' . $this->filterParams[$attribute] . '%');
            }

            ++$key;
        }

        $this->applySorting();
    }

    private function filterParamHasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->filterParams);
    }

    private function columnHasFilterOption($column)
    {
        return array_key_exists('filterOptions', $column) && ! empty($column['filterOptions']);
    }

    private function columnHasFilter($column)
    {
        if (! array_key_exists('filter', $column) || ! is_bool($column['filter'])) {
            return false;
        }

        return true;
    }

    private function applyFilterByFilterOptions($filterOptions, $attribute)
    {
        if (empty($this->filterParams[$attribute])) {
            return;
        }
        
        $columnFilter = new FilterOption($filterOptions, $attribute);
        $this->query = $columnFilter->addFilterWhere($this->query, $this->filterParams[$attribute]);
    }

    private function applySorting()
    {
        if (! isset($this->filterParams['sort'])) {
            return;
        }
        
        $orderBy = 'asc';
        if (isset($this->filterParams['orderby']) && $this->filterParams['orderby'] == 'desc') {
            $orderBy = 'desc';
        }

        $this->query->orderBy($this->filterParams['sort'], $orderBy);
    }

}