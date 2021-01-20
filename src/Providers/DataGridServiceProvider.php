<?php

namespace Rufaidulk\DataGrid\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Rufaidulk\DataGrid\View\Components\DataTable;
use Rufaidulk\DataGrid\View\Components\Filter;

/**
* DataGridServiceProvider
*/
class DataGridServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap services.
    *
    * @return void
    */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'datagrid');
        Blade::component('x-datatable', DataTable::class);
        // Blade::component('x-filter', Filter::class);
    }

    /**
    * Register services.
    *
    * @return void
    */
    public function register()
    {

    }
}
