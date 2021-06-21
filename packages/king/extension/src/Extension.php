<?php

namespace King\ExtensionForLaravel;

/*use Encore\Admin\Auth\Database\Permission;*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

abstract class Extension
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    public $css = [];

    /**
     * @var array
     */
    public $js = [];

    /**
     * @var string
     */
    public $assets = '';

    /**
     * @var string
     */
    public $views = '';

    /**
     * @var string
     */
    public $migrations = '';

    /**
     * @var array
     */
    public $menu = [];

    /**
     * @var array
     */
    public $permission = [];

    /**
     * Extension instance.
     *
     * @var Extension
     */
    protected static $instance;

    /**
     * Extension namespace.
     *
     * @var Extension
     */
    protected static $namespace;

    /**
     * The menu validation rules.
     *
     * @var array
     */
    protected $menuValidationRules = [
        'title'    => 'required',
        'path'     => 'required',
        'icon'     => 'required',
        'children' => 'nullable|array',
    ];

    /**
     * The permission validation rules.
     *
     * @var array
     */
    protected $permissionValidationRules = [
        'name'  => 'required',
        'slug'  => 'required',
        'path'  => 'required',
    ];

    /**
     * Returns the singleton instance.
     *
     * @return self
     */
    protected static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$instance[$class]) || !self::$instance[$class] instanceof $class) {
            self::$instance[$class] = new static();
        }

        return static::$instance[$class];
    }

    /**
     * Bootstrap this extension.
     */
    public static function boot()
    {
        $extension = static::getInstance();

        ExtensionManage::extend($extension->name, get_called_class());

        if ($extension->disabled()) {
            return false;
        }

        if (!empty($css = $extension->css())) {
            ExtensionManage::css($css);
        }

        if (!empty($js = $extension->js())) {
            ExtensionManage::js($js);
        }

        return true;
    }

    /**
     * Get the path of assets files.
     *
     * @return string
     */
    public function assets()
    {
        return $this->assets;
    }

    /**
     * Get the paths of css files.
     *
     * @return array
     */
    public function css()
    {
        return $this->css;
    }

    /**
     * Get the paths of js files.
     *
     * @return array
     */
    public function js()
    {
        return $this->js;
    }

    /**
     * Get the path of view files.
     *
     * @return string
     */
    public function views()
    {
        return $this->views;
    }

    /**
     * Get the path of migration files.
     *
     * @return string
     */
    public function migrations()
    {
        return $this->migrations;
    }

    /**
     * @return array
     */
    public function menu()
    {
        return $this->menu;
    }

    /**
     * @return array
     */
    public function permission()
    {
        return $this->permission;
    }

    /**
     * Whether the extension is enabled.
     *
     * @return bool
     */
    public function enabled()
    {
        return static::config('enable') !== false;
    }

    /**
     * Whether the extension is disabled.
     *
     * @return bool
     */
    public function disabled()
    {
        return !$this->enabled();
    }

    /**
     * Get config set in config/extension.php.
     *
     * @param string $key
     * @param null   $default
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public static function config($key = null, $default = null)
    {
        $name = array_search(get_called_class(), ExtensionManage::$extensions);

        if (is_null($key)) {
            $key = sprintf('extension.extensions.%s', strtolower($name));
        } else {
            $key = sprintf('extension.extensions.%s.%s', strtolower($name), $key);
        }

        return config($key, $default);
    }

    /**
     * Import menu item and permission to application.
     */
    public static function import()
    {
        $extension = static::getInstance();
        DB::transaction(function () use ($extension) {
            if ($menu = $extension->menu() && false) {
                if ($extension->validateMenu($menu)) {
                    extract($menu);
                    $children = Arr::get($menu, 'children', []);
                    static::createMenu($title, $path, $icon, 0, $children);
                }
            }
            if ($permission = $extension->permission() && false) {
                $name = Arr::get($permission, 'name', null);
                if (null !== $name) {
                    $permission = [$permission];
                }
                foreach ($permission as $item) {
                    if ($extension->validatePermission($item)) {
                        $method = [];
                        extract($item);
                        static::createPermission($name, $slug, implode("\r\n", $path), $method);
                    }
                }
            }
        });
    }

    /**
     * Validate menu fields.
     *
     * @param array $menu
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validateMenu(array $menu)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($menu, $this->getMenuValidationRules());

        if ($validator->passes()) {
            return true;
        }

        $message = "Invalid menu:\r\n".implode("\r\n", Arr::flatten($validator->errors()->messages()));

        throw new \Exception($message);
    }

    /**
     * Get menu validation rules.
     *
     * @return array
     */
    protected function getMenuValidationRules()
    {
        return [
            'title'    => 'required',
            'path'     => ['required'],
            'icon'     => 'required',
            'children' => 'nullable|array',
        ];
    }

    /**
     * Validate permission fields.
     *
     * @param array $permission
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validatePermission(array $permission)
    {
        if (!empty($permission['method'])) {
            $permission['method'] = array_map('strtoupper', $permission['method']);
        }

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($permission, $this->getPermissionValidationRules());

        if ($validator->passes()) {
            return true;
        }

        $message = "Invalid permission:\r\n".implode("\r\n", Arr::flatten($validator->errors()->messages()));

        throw new \Exception($message);
    }

    /**
     * Get permission validation rules.
     *
     * @return array
     */
    protected function getPermissionValidationRules()
    {
        return [
            'name'     => 'required',
            'slug'     => ['required'],
            'path'     => 'required|array',
            'path.*'   => 'string',
            'method'   => 'nullable|array',
            'method.*' => ['string'],
        ];
    }

    /**
     * Create a item in laravel-admin left side menu.
     *
     * @param string $title
     * @param string $uri
     * @param string $icon
     * @param int    $parentId
     * @param array  $children
     *
     * @throws \Exception
     *
     * @return Model
     */
    protected static function createMenu($title, $uri, $icon = 'fa-bars', $parentId = 0, array $children = [])
    {
        $menuModel = config('extension.database.menu_model');

        $lastOrder = $menuModel::max('order');
        /**
         * @var Model
         */
        $menu = $menuModel::create([
            'parent_id' => $parentId,
            'order'     => $lastOrder + 1,
            'title'     => $title,
            'icon'      => $icon,
            'uri'       => $uri,
        ]);
        if (!empty($children)) {
            $extension = static::getInstance();
            foreach ($children as $child) {
                if ($extension->validateMenu($child)) {
                    $subTitle = Arr::get($child, 'title');
                    $subUri = Arr::get($child, 'path');
                    $subIcon = Arr::get($child, 'icon');
                    $subChildren = Arr::get($child, 'children', []);
                    static::createMenu($subTitle, $subUri, $subIcon, $menu->getKey(), $subChildren);
                }
            }
        }

        return $menu;
    }

    /**
     * Create a permission for this extension.
     *
     * @param       $name
     * @param       $slug
     * @param       $path
     * @param array $methods
     */
    protected static function createPermission($name, $slug, $path, $methods = [])
    {
        $permissionModel = config('extension.database.permissions_model');

        $permissionModel::create([
            'name'        => $name,
            'slug'        => $slug,
            'http_path'   => '/'.trim($path, '/'),
            'http_method' => $methods,
        ]);
    }

    /**
     * Set routes for this extension.
     *
     * @param $callback
     */
    public static function routes($callback)
    {
        /*$attributes = array_merge(
            [
                'prefix'     => config('admin.route.prefix'),
                'middleware' => config('admin.route.middleware'),
            ],
            static::config('route', [])
        );

        Route::group($attributes, $callback);*/

        Route::group(['namespace' => static::$namespace], function ($router) use($callback) {
            require $callback;
        });
    }
}