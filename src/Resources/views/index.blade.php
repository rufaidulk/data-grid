<table class="{{ $tableClass }}">
    <thead>
        <tr>
            <th>#</th>
            
            @foreach ($tableHeaders as $header)
            <th>{{ $header }}</th>
            @endforeach

            <th class="action-column">&nbsp;</th>
        </tr>
        <!-- <tr id="w0-filters" class="filters">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><input type="text" class="form-control" name="CustomerSearch[name]"></td>
            <td><input type="text" class="form-control" name="CustomerSearch[mobile]"></td>
            <td><input type="text" class="form-control" name="CustomerSearch[email]"></td>
            <td><select id="customersearch-status" class="form-control" name="CustomerSearch[status]">
                    <option value="">All</option>
                    <option value="10">Active</option>
                    <option value="9">Inactive</option>
                    <option value="8">Pending</option>
                </select></td>
            <td>&nbsp;</td>
        </tr> -->
        <tr class="filters">
            <form id="grid-filter" action="{{ route(request()->route()->getName()) }}" method="GET"></form>
            @foreach ($filters as $filter)
                @if (empty($filter))
                    <td>&nbsp;</td>
                @elseif ($filter['type'] == 'text')
                    <td>
                        <input type="text" class="form-control" name="{{ $filter['name'] }}" form="grid-filter" value="{{ $filter['value'] }}">
                    </td>
                @elseif ($filter['type'] == 'select')
                    <td>
                        <select class="form-control" name="{{ $filter['name'] }}" form="grid-filter">
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
        {!! $queryResult !!}
    </tbody>
</table>

@push('scripts')
    <script type="application/javascript">
        function confirmDelete(formId)
        {
            swal.fire({
                title: '<p class="font-weight-normal" style="font-size: 20px;">Are you sure you want to cancel this booking?</p>',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                showCancelButton: true
            }).then(function(result) {
                if (result.value) {
                    document.getElementById('formId').submit();
                }
            });
        }
    </script>


@endpush