<div class="{{ $grid->wrapperClass }}">
    @if ($grid->showPaginationSummary())
        <div classs="{{ $grid->paginationSummaryClass }}">
            Showing {{ ($grid->getPaginator()->currentpage() - 1 ) * $grid->getPaginator()->perpage() + 1 }}
                @if ($grid->getPaginator()->currentpage() * $grid->getPaginator()->perpage() < $grid->getPaginator()->total())
                    to {{ $grid->getPaginator()->currentpage() * $grid->getPaginator()->perpage() }}
                @else
                    to {{ $grid->getPaginator()->total() }}
                @endif
            of {{ $grid->getPaginator()->total() }} items.
        </div>
    @endif
    <table class="{{ $grid->getTableClass() }}">
        <thead>
            <tr>
                <th>#</th>
                
                @foreach ($grid->getTableHeaders() as $attribute => $label)
                    <th>
                        @if ($grid->isSortable($attribute))
                            <a href="{{ route(request()->route()->getName(), ['sort' => $attribute, 'orderby' => $grid->getOrderBy()]) }}">
                                {{ $label }}
                            </a>
                        @else
                            <a href="#">
                                {{ $label }}
                            </a>
                        @endif
                    </th>
                @endforeach

                <th class="action-column">&nbsp;</th>
            </tr>
            <tr class="filters">
                <form id="grid-filter" action="{{ route(request()->route()->getName()) }}" method="GET"></form>
                @foreach ($grid->getTableFilters() as $filter)
                    @if (empty($filter))
                        <td>&nbsp;</td>
                    @elseif ($filter['type'] == 'text')
                        <td>
                            <input type="text" class="form-control" name="{{ $filter['field'] }}" form="grid-filter" value="{{ $filter['value'] }}">
                        </td>
                    @elseif ($filter['type'] == 'select')
                        <td>
                            <select class="form-control" name="{{ $filter['field'] }}" form="grid-filter">
                                <option value=''>All</option>
                                @foreach ($filter['options'] as $key => $value)
                                    <option {{ $filter['value'] == $key ? "Selected" : "" }} value="{{ $key }}"> {{ $value }} </option>
                                @endforeach
                            </select>                        
                        </td>
                    @endif
                @endforeach
                <td>
                    <button type="submit" class="btn btn-outline-primary grid-filter-button" title="filter data" form="grid-filter">Filter&nbsp;<i class="fa fa-filter"></i>
                    </button>
                </td>
            </tr>
        </thead>
        <tbody>
            {!! $grid->getTableBody() !!}
        </tbody>
    </table>
    <div class="col-md-12">
        {!! $grid->renderPaginationLinks() !!}
    </div>
</div>