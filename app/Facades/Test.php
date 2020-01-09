<?php
/**
 * Created by Lin.
 * User: xianlinzhang
 * Date: 2020/1/9
 * Time: 10:57
 */

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class Test extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'test';
    }
}