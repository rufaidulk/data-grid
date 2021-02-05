## Basic Usage
1. Create data grid class inside the app folder that extends **\Rufaidulk\DataGrid\Grid.php**, which has 2 abstract methods **gridQuery()** and **columns()**.

```php
<?php

namespace App\DataGrids;

use App\Models\City;
use Rufaidulk\DataGrid\Grid;

class CityGrid extends Grid
{   

    public function gridQuery()
    {
        $query = City::query()->with('country');
        
        return $query;
    }

    public function columns()
    {
        return [
            'country_id' => [
                'label' => 'Country',
                'sort' => false,
                'filter' => true,
                'filterOptions' => [
                    'type' => 'text',
                    'attribute' => 'country_id',
                    'operator' => '=',
                ],
                'value' => function ($model) {
                    return $model->country->name;
                }
            ],
            'name' => [
                'label' => 'Name',
                'filter' => true,
            ],
            'status' => [
                'label' => 'Status',
                'filter' => true,
                'filterOptions' => [
                    'type' => 'select',
                    'attribute' => 'status',
                    'operator' => '=',
                    'data' => [1 => 'Active', 2 => 'Inactive']
                ],
                'value' => function ($model) {
                    return $model->status == 1 ? 'Active' : 'Inactive';
                }
            ],
            'created_at' => [
                'label' => 'Created at',
                'value' => function ($model) {
                    return date('d-m-Y', strtotime($model->created_at));
                }
            ],
            'action' => [
                'routePrefix' => 'city',
            ]
        ];
    }

}
```
2. In the controller **CityController.php**
```php
/**
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
public function index()
{
    //passing query params to constructor for filtering and sorting
    $grid = new CityGrid(request()->query());

    return view('city.index', compact('grid'));
}
```

3. Finally in the view file
```php
<div class="col-md-12">
    {!! $grid->render() !!}
</div>

@push('scripts')
    {!! $grid->scripts() !!}
@endpush
```
