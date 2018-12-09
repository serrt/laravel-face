<?php

namespace Serrt\LaravelFace\Facade;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Face extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return 'face';
    }
}