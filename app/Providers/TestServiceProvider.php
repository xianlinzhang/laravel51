<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Test\Test;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\App\Facades\Test::class,function(){
            //return new TestService();
            return new Test;
        });

    }
}
