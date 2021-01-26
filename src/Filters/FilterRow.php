<?php

namespace Rufaidulk\DataGrid\Filters;

final class FilterRow
{
    const DEFAULT_TYPE = 'text';

    /**
     * @var array
     */
    private $tableColumns;

    /**
     * @var array
     */
    private $filterView;

    public function __construct($tableColumns)
    {
        $this->tableColumns = $tableColumns;
        $this->filterView = [];
    }

    /**
     * @return string
     */
    public function handle()
    {
        return $this->getFilterView();
    }

    /**
     * @return string
     */
    private function getFilterView()
    {
        if (! $this->filterView) {
            $this->generate();
        }

        return $this->filterView;
    }

    private function generate()
    {
        foreach ($this->tableColumns as $attribute => $column)
        {
            if (! $this->columnHasFilter($column)) {
                continue;
            }
            
            if ($column['filter']) 
            {
                if (! $this->columnHasFilterOption($column)) {
                    $this->appendDefaultFilter($attribute);
                }
                else {
                    $filterOption = new FilterOption($column['filterOptions'], $attribute);
                    array_push($this->filterView, $filterOption->handle());
                }
            }
            else 
            {
                //todo:: refactor
                array_push($this->filterView, null);
            }
        }

        $this->sanitizeFilters();
    }

    /**
     * @param array $column
     * 
     * @return bool
     */
    private function columnHasFilter($column)
    {
        if (! array_key_exists('filter', $column) || ! is_bool($column['filter'])) {
            array_push($this->filterView, null);
            return false;
        }

        return true;
    }

    /**
     * @param array $column
     * 
     * @return bool
     */
    private function columnHasFilterOption($column)
    {
        return array_key_exists('filterOptions', $column) && ! empty($column['filterOptions']);
    }

    /**
     * @param string $attribute
     */
    private function appendDefaultFilter($attribute)
    {
        array_push($this->filterView, [
            'field' => $attribute, 
            'type' => self::DEFAULT_TYPE, 
            'value' => ''
        ]);
    }

    private function sanitizeFilters()
    {
        if (empty(array_filter($this->filterView))) {
            $this->filterView = [];
        }
        else {
            array_unshift($this->filterView, null);
        }
    }
}