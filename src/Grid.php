<?php

namespace Packages\DataGrid;

use InvalidArgumentException;
use UnexpectedValueException;

abstract class Grid
{
    protected $query;
    protected $filters;
    protected $tableClass = 'table table-small-font max-col min-col table-1';
    protected $tableColumns = [];
    
    private $filterParams;

    public function __construct($filterParams = [])
    {
        $this->filterParams = $filterParams;    
    }

    abstract public function gridQuery();
    abstract public function columns();

    public function render()
    {
        $this->filters = [];
        $this->query = $this->gridQuery();
        $this->setColumns();
        $this->setFilters();
        return view('datagrid::index', $this->getViewData());
    }

    public function scripts()
    {
        $scripts =<<<'EOT'
            <script type="application/javascript">
                function confirmDelete(formId)
                {
                    swal.fire({
                        title: '<p class="font-weight-normal" style="font-size: 20px;">Are you sure that you want to delete this item?</p>',
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        showCancelButton: true
                    }).then(function(result) {
                        if (result.value) {
                            document.getElementById(formId).submit();
                        }
                    });
                }
            </script>
        EOT;

        return $scripts;
    }

    private function getViewData()
    {
        return [
            'tableClass' => $this->tableClass,
            'tableHeaders' => $this->getHeaders(),
            'queryResult' => $this->getGridView(),
            'filters' => $this->filters,
        ];
    }

    private function getHeaders()
    {
        return array_column($this->tableColumns, 'label');
    }

    private function setColumns()
    {
        $this->tableColumns = $this->columns();
        if (! is_array(($this->tableColumns))) {
            throw new UnexpectedValueException('Columns must be an array');
        }

    }

    private function getGridView()
    {
        $result = $this->getQueryResult();
        $tableBody = '';
        
        foreach ($result as $key => $value)
        {
            $html = "<tr data-key='114'>";
            $index = $key + 1;
            $html .= "<td>{$index}</td>";

            foreach ($this->tableColumns as $attribute => $column)
            {
                $data = '';
                if (array_key_exists('value', $column) && is_callable($column['value']))
                {
                    $data = call_user_func($column['value'], $result[$key]);
                }
                else {
                    $data = $value[$attribute];
                }

                $html .= "<td>{$data}</td>";
            }

            $tableBody .= $html;
            $actionButtons = $this->getActionButtons($result[$key]);
            $tableBody .= "<td>{$actionButtons}</td>";
            
            $tableBody .= "</tr>";
        }

        return $tableBody;
    }

    private function getQueryResult()
    {   
        if (! empty($this->filterParams)) {
            $this->applyFilters();
        }

        return $this->query->get();
    }

    private function applyFilters()
    {
        $key = 1;
        foreach ($this->tableColumns as $attribute => $column)
        {
            if (! array_key_exists('filter', $column) || ! is_bool($column['filter'])) {
                ++$key;
                continue;
            }
            
            if ($column['filter'] && empty($column['filterOptions']) && array_key_exists($attribute, $this->filterParams)) {
                $this->filters[$key]['value'] = $this->filterParams[$attribute];
                $this->query = $this->query->where($attribute, 'like', '%' . $this->filterParams[$attribute] . '%');
            }
            else if ($column['filter'] && ! empty($column['filterOptions']) && array_key_exists($attribute, $this->filterParams)) 
            {
                $this->filters[$key]['value'] = $this->filterParams[$attribute];
                $this->applyFilterByFilterOptions($column['filterOptions'], $attribute);
            }

            ++$key;
        }
    }

    private function applyFilterByFilterOptions($filterOptions, $attribute)
    {
        if (empty($this->filterParams[$attribute])) {
            return;
        }
        
        $columnFilter = new Filter($filterOptions, $attribute);
        $this->query = $columnFilter->addFilterWhere($this->query, $this->filterParams[$attribute]);
    }

    private function setFilters()
    {
        foreach ($this->tableColumns as $attribute => $column)
        {
            if (! array_key_exists('filter', $column) || ! is_bool($column['filter'])) {
                array_push($this->filters, null);
                continue;
            }
            
            if ($column['filter'] && empty($column['filterOptions'])) {
                array_push($this->filters, ['name' => $attribute, 'type' => 'text', 'value' => '']);
            }
            else if ($column['filter'] && ! empty($column['filterOptions'])) {
                $columnFilter = new Filter($column['filterOptions'], $attribute);
                $res = $columnFilter->handle();
                array_push($this->filters, $res);
            }
            else {
                array_push($this->filters, null);
            }
            
        }

        if (empty(array_filter($this->filters))) {
            $this->filters = [];
        }
        else {
            array_unshift($this->filters, null);
        }
    }

    private function getActionButtons($model)
    {
        $actionHtml = '';

        if (! array_key_exists('action', $this->tableColumns) || (is_bool($this->tableColumns['action']) && ! $this->tableColumns['action'])) {
            return $actionHtml;
        }

        if (! is_array($this->tableColumns['action']) || ! array_key_exists('routePrefix', $this->tableColumns['action'])) {
            throw new InvalidArgumentException('Action route prefix must be defined');
        }

        $actionHtml = (new DefaultAction($this->tableColumns['action']['routePrefix'], $model))->render();

        return $actionHtml;
    }
    
}