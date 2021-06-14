<?php

namespace Rufaidulk\DataGrid;

use InvalidArgumentException;
use UnexpectedValueException;
use Rufaidulk\DataGrid\Filters\FilterRow;
use Rufaidulk\DataGrid\Filters\FilterQuery;
use Rufaidulk\DataGrid\Columns\ActionColumn;

abstract class Grid
{
    const DEFAULT_PAGE_SIZE = 20;

    /**
     * Number of items per page
     * 
     * @var int 
     */
    public $pageSize;

    /**
     * Pagination query param name
     * 
     * @var string
     */
    public $pageParam;

    /**
     * Pagination path
     * 
     * @var string
     */
    public $paginationPath;

    /**
     * CSS class selector for html table tag
     * 
     * @var string
     */
    public $tableClass;

    /**
     * CSS class selector for grid
     * 
     * @var string
     */
    public $wrapperClass;

    /**
     * Status of pagination summary. if it is set to false, summary will be hidden
     * 
     * @var bool
     */
    public $paginationSummary;

    /**
     * CSS class selector of pagination summary content
     * 
     * @var string
     */
    public $paginationSummaryClass;

    /**
     * Controls visibility filter row
     * 
     * @var bool
     */
    public $showFilters = true;

    /**
     * Data source query
     * 
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $query;

    /**
     * sorting order. possible values are desc and asc
     * 
     * @var string
     */
    private $orderBy;

    /**
     * Grid fitlers
     * 
     * @var array
     */
    private $filters;

    /**
     * query paginator
     * 
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private $paginator;

    /**
     * Grid table body content
     * 
     * @var string
     */
    private $tableBody;

    /**
     * Defined table columns
     * 
     * @var array
     */
    private $tableColumns;

    /**
     * filter params
     * 
     * @var array
     */
    private $filterParams;

    /**
     * Creates a new instance
     * 
     * @return void
     */
    public function __construct($filterParams = [])
    {
        $this->filterParams = $filterParams;
        $this->init();
    }

    abstract public function gridQuery();
    abstract public function columns();

    /**
     * initiliazes the properties
     */
    private function init()
    {
        $this->tableBody = '';
        $this->filters = [];
        $this->query = $this->gridQuery();
        $this->setColumns();
        $this->setFilters();
    }

    /**
     * Renders the grid view
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->createGridView();
        $this->restoreSortOrder();

        return view('rufaidulk::grid.index', ['grid' => $this]);
    }

    /**
     * @return array
     */
    public function getTableHeaders()
    {
        $attributes = array_keys($this->tableColumns);
        $attributes = array_filter($attributes, function ($attribute) {
            return $attribute != 'action';
        });
        $labels = array_column($this->tableColumns, 'label');
        $headers = array_combine($attributes, $labels);
        
        return $headers;
    }

    /**
     * @return array
     */
    public function getTableFilters()
    {
        return $this->filters;
    }

    /**
     * @return string
     */
    public function getTableBody()
    {
        return $this->tableBody;
    }

    /**
     * @return string
     */
    public function renderPaginationLinks()
    {
        return $this->paginator->links($this->getPaginationLinkView());
    }

    /**
     * @param string
     * 
     * @return bool
     */
    public function isSortable($attribute)
    {
        $column = $this->tableColumns[$attribute];
        if (array_key_exists('sort', $column) && ! $column['sort']) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return string
     */
    public function getTableClass()
    {
        return $this->tableClass ?? 'table table-small-font max-col min-col table-1';
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * @return bool|mixed
     */
    public function showPaginationSummary()
    {
        return $this->paginationSummary ?? true;
    }

    /**
     * @return bool
     */
    public function hasFilters()
    {
        return $this->showFilters;
    }

    /**
     * @return string
     */
    private function getPaginationLinkView()
    {
        return 'rufaidulk::pagination.bootstrap4';
    }

    private function setFilters()
    {
        $this->filters = (new FilterRow($this->tableColumns))->handle();
    }

    /**
     * @throws \UnexpectedValueException
     */
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
        
        $index = $this->getStartingIndex();
        foreach ($result as $key => $value)
        {
            $html = "<tr data-key='114'>";
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

                $html .= $this->applyHtmlOptions($data, $attribute, $column);
            }

            $this->tableBody .= $html;
            $actionColumnHtml = $this->getActionButtons($result[$key]);
            $this->tableBody .= $actionColumnHtml;
            
            $this->tableBody .= "</tr>";
            $index++;
        }
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function getQueryResult()
    {
        if (! empty($this->filterParams)) {
            $this->applyFilters();
        }

        $this->paginator = $this->query->paginate($this->getPageSize(), ['*'], $this->getPageParamName())
                                ->withQueryString();
    
        if ($this->paginationPath) {
            $this->paginator->withPath($this->paginationPath);
        }
        
        return $this->paginator;
    }

    /**
     * Set the pagination param for current pagination instance.
     * Default will be 'page'
     * 
     * @return string
     */
    private function getPageParamName()
    {
        if ($this->pageParam) {
            return $this->pageParam;
        }

        return 'page';
    }

    /**
     * @return void
     */
    private function applyFilters()
    {
        $filterQuery = new FilterQuery($this->tableColumns, $this->filters, $this->filterParams, $this->query);

        list($this->query, $this->filters) = $filterQuery->handle();
    }

    /**
     * @return int
     */
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

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * 
     * @return string
     */
    private function getActionButtons($model)
    {
        $actionHtml = '';

        if (! array_key_exists('action', $this->tableColumns) || (is_bool($this->tableColumns['action']) && ! $this->tableColumns['action'])) {
            return $actionHtml;
        }

        if (! is_array($this->tableColumns['action']) || ! array_key_exists('routePrefix', $this->tableColumns['action'])) {
            throw new InvalidArgumentException('Action route prefix must be defined');
        }

        $actionHtml = (new ActionColumn($this->tableColumns['action'], $model))->render();

        if (isset($this->tableColumns['action']['contentCssClass'])) {
            $cssClasses = $this->tableColumns['action']['contentCssClass'];
            $actionHtml = "<td class='{$cssClasses}'>{$actionHtml}</td>";
        }
        else {
            $actionHtml = "<td>{$actionHtml}</td>";
        }

        return $actionHtml;
    }

    /**
     * @param string $data
     * @param string $attribute
     * @param array $column
     * 
     * @return string 
     */
    private function applyHtmlOptions($data, $attribute, $column)
    {
        if (! isset($column['contentCssClass']) || $attribute == 'action') {
            return "<td>{$data}</td>";
        }

        $cssClasses = $column['contentCssClass'];

        return "<td class='{$cssClasses}'>{$data}</td>";
    }

    private function restoreSortOrder()
    {
        $this->orderBy = 'asc';

        if (isset($this->filterParams['orderby']) && $this->filterParams['orderby'] == 'asc') {
            $this->orderBy = 'desc';
        }
    }
    
    /**
     * @return int
     */
    private function getStartingIndex()
    {
        return ($this->getPaginator()->currentpage() - 1 ) * $this->getPaginator()->perpage() + 1;
    }

    /**
     * @return string
     */
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