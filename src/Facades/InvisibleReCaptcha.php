<?php

/*
* @name        DARKLYY Invisible reCAPTCHA
* @link        https://darklyy.ru/
* @copyright   Copyright (C) 2012-2022 ООО «ПРИС»
* @license     LICENSE.txt (see attached file)
* @version     VERSION.txt (see attached file)
* @author      Komarov Ivan
*/

namespace Darkeum\InvisibleReCaptcha\Facades;

use Boot\Support\Facades\Facade;

class InvisibleReCaptcha extends Facade
{
    /**
     * Получение зарегистрированное имя компонента.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'recaptcha';
    }
}
