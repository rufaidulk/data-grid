<?php

namespace Rufaidulk\DataGrid\Filters;

final class FilterQuery
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $query;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $tableColumns;

    /**
     * @var array
     */
    private $filterParams;
    
    public function __construct($tableColumns, $filters, $filterParams, $query)
    {
        $this->query = $query;
        $this->filters = $filters;
        $this->tableColumns = $tableColumns;
        $this->filterParams = $filterParams;
    }

    /**
     * @return array
     */
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

    /**
     * @return bool
     */
    private function filterParamHasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->filterParams) && ! empty($this->filterParams[$attribute]);
    }

    /**
     * @return bool
     */
    private function columnHasFilterOption($column)
    {
        return array_key_exists('filterOptions', $column) && ! empty($column['filterOptions']);
    }

    /**
     * @param array $column
     * 
     * @return bool
     */
    private function columnHasFilter($column)
    {
        if (! array_key_exists('filter', $column) || ! is_bool($column['filter'])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $filterOptions
     * @param string $attribute
     * 
     * @return void
     */
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