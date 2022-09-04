<?php

/*
* @name        DARKLYY Invisible reCAPTCHA
* @link        https://darklyy.ru/
* @copyright   Copyright (C) 2012-2022 ООО «ПРИС»
* @license     LICENSE.txt (see attached file)
* @version     VERSION.txt (see attached file)
* @author      Komarov Ivan
*/

namespace Darkeum\InvisibleReCaptcha;

use Illuminate\View\Compilers\BladeCompiler;
use Darkeum\DarklyyPackageTools\Package;
use Darkeum\DarklyyPackageTools\PackageServiceProvider;

class InvisibleReCaptchaServiceProvider extends PackageServiceProvider
{    
    /**
     * Конфигурирование пакета
     *
     * @param  Package $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('darklyy-invisible-recaptcha')
            ->hasConfigFile();
    }
    
    /**
     * Регистрация пакета
     *
     * @return void
     */
    public function registeringPackage(): void
    {
        $this->app->singleton('recaptcha', function ($app) {
            return new InvisibleReCaptcha(
                $app['config']['invisible-recaptcha.siteKey'],
                $app['config']['invisible-recaptcha.secretKey'],
                $app['config']['invisible-recaptcha.options']
            );
        });

        $this->app->afterResolving('blade.compiler', function () {
            $this->addBladeDirective($this->app['blade.compiler']);
        });
    }

    /**
     * Загрузка пакета
     *
     * @return void
     */
    public function bootingPackage()
    {
        $this->app['validator']->extend('recaptcha', function ($attribute, $value) {
            return $this->app['recaptcha']->verifyResponse($value, $this->app['request']->getClientIp());
        });
    }

    /**
     * Получить услуги, предоставляемые провайдером.
     *
     * @return array
     */
    public function provides()
    {
        return ['recaptcha'];
    }

    /**
     * Регистрация деректив Blade
     * 
     * @param BladeCompiler $blade
     * @return void
     */
    public function addBladeDirective(BladeCompiler $blade)
    {
        $blade->directive('recaptcha', function ($arguments) {
            return "<?php echo app('recaptcha')->renderCaptcha({$arguments}); ?>";
        });
        $blade->directive('recaptchaPolyfill', function () {
            return "<?php echo app('recaptcha')->renderPolyfill(); ?>";
        });
        $blade->directive('recaptchaHTML', function () {
            return "<?php echo app('recaptcha')->renderCaptchaHTML(); ?>";
        });
        $blade->directive('recaptchaScripts', function ($arguments) {
            return "<?php echo app('recaptcha')->renderFooterJS({$arguments}); ?>";
        });
    }
}
