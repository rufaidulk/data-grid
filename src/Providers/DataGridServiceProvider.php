<?php

namespace Rufaidulk\DataGrid\Providers;

use Illuminate\Support\ServiceProvider;

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
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'rufaidulk');
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
