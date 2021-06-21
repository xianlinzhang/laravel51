<?php

namespace King\ExtensionForLaravel;

use Illuminate\Console\Application as ArtisanApp;

use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $commands = [
        Console\ImportCommand::class,
        Console\CreateCommand::class,

    ];

    /**
     * 服务提供者加是否延迟加载.
     *
     * @var bool
     */
    protected $defer = true; // 延迟加载服务
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'Extension'); // 视图目录指定


        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/extension'),  // 发布视图目录到resources 下
            __DIR__.'/config/extension.php' => config_path('extension.php'), // 发布配置文件到 laravel 的config 下
        ]);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config' => config_path()], 'laravel-admin-config');
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'laravel-admin-lang');
            $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'laravel-admin-migrations');
            $this->publishes([__DIR__.'/../resources/assets' => public_path('vendor/laravel-admin')], 'laravel-admin-assets');
        }
    }

    /**
     * Load the given routes file if routes are not already cached.
     *
     * @param  string  $path
     * @return void
     */
    protected function loadRoutesFrom($path)
    {
        if (! $this->app->routesAreCached()) {
            require $path;
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
         // 单例绑定服务
        /*$this->app->singleton('extension', function ($app) {
            return new Extension($app['session'], $app['config']);
        });*/

        $this->commands($this->commands);
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        // 因为延迟加载 所以要定义 provides 函数 具体参考laravel 文档
        return ['extension'];
    }
}
