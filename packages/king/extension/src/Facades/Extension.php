<?php
namespace King\ExtensionForLaravel\Facades;
use Illuminate\Support\Facades\Facade;
class Extension extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'extension';
    }
}