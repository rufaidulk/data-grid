<?php

namespace Rufaidulk\DataGrid;

use InvalidArgumentException;
use UnexpectedValueException;
use Rufaidulk\DataGrid\Filters\FilterRow;
use Rufaidulk\DataGrid\Filters\FilterQuery;
use Rufaidulk\DataGrid\Columns\DefaultActionColumn;

abstract class Grid
{
    const DEFAULT_PAGE_SIZE = 20;

    public $pageSize;
    public $tableClass;

    private $query;
    private $filters;
    private $paginator;
    private $tableBody;
    private $tableColumns;
    private $filterParams;

    public function __construct($filterParams = [])
    {
        $this->filterParams = $filterParams;
        $this->init();
    }

    abstract public function gridQuery();
    abstract public function columns();

    private function init()
    {
        $this->tableBody = '';
        $this->filters = [];
        $this->query = $this->gridQuery();
        $this->setColumns();
        $this->setFilters();
    }

    public function render()
    {
        $this->createGridView();
        
        return view('rufaidulk::grid.index', ['grid' => $this]);
    }

    public function getTableHeaders()
    {
        return array_column($this->tableColumns, 'label');
    }

    public function getTableFilters()
    {
        return $this->filters;
    }

    public function getTableBody()
    {
        return $this->tableBody;
    }

    public function renderPaginationLinks()
    {
        return $this->paginator->links($this->getPaginationLinkView());
    }

    public function getTableClass()
    {
        return $this->tableClass ?? 'table table-small-font max-col min-col table-1';
    }

    private function getPaginationLinkView()
    {
        return 'rufaidulk::pagination.bootstrap4';
    }

    private function setFilters()
    {
        $this->filters = (new FilterRow($this->tableColumns))->handle();
    }

    private function setColumns()
    {
        $this->tableColumns = $this->columns();

        if (! is_array(($this->tableColumns))) {
            throw new UnexpectedValueException('Columns must be an array');
        }
    }
    
    private function createGridView()
    {
        $result = $this->getQueryResult();
        if ($result->total() == 0) {
            $colspan = count($this->tableColumns) + 2;
            $this->tableBody = "<tr><td colspan='{$colspan}'>No records found</td></tr>";
            return;
        }
        
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

            $this->tableBody .= $html;
            $actionButtons = $this->getActionButtons($result[$key]);
            $this->tableBody .= "<td>{$actionButtons}</td>";
            
            $this->tableBody .= "</tr>";
        }
    }

    private function getQueryResult()
    {
        if (! empty($this->filterParams)) {
            $this->applyFilters();
        }

        $this->paginator = $this->query->paginate($this->getPageSize())->withQueryString();
        
        return $this->paginator;
    }

    private function applyFilters()
    {
        $filterQuery = new FilterQuery($this->tableColumns, $this->filters, $this->filterParams, $this->query);

        list($this->query, $this->filters) = $filterQuery->handle();
    }

    private function getPageSize()
    {
        if ($this->pageSize) 
        {
            if (! is_numeric($this->pageSize)) {
                throw new InvalidArgumentException('Page size must be a numerical value');
            }

            return $this->pageSize;
        }

        return self::DEFAULT_PAGE_SIZE;
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

        $actionHtml = (new DefaultActionColumn($this->tableColumns['action']['routePrefix'], $model))->render();

        return $actionHtml;
    }

    public function scripts()
    {
        $scripts =<<<'EOT'
            <script type="application/javascript">
                function confirmDelete(formId)
                {
                    if (confirm('Are you sure that you want to delete this item?')) {
                        document.getElementById(formId).submit();
                    }
                }
            </script>
        EOT;

        return $scripts;
    }
    
}