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

use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

class InvisibleReCaptcha
{
    const API_URI = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URI = 'https://www.google.com/recaptcha/api/siteverify';
    const POLYFILL_URI = 'https://cdn.polyfill.io/v2/polyfill.min.js';
    const DEBUG_ELEMENTS = [
        '_submitForm',
        '_captchaForm',
        '_captchaSubmit'
    ];

    /**
     * Публичный ключ
     *
     * @var string
     */
    protected $siteKey;

    /**
     * Секретный ключ
     *
     * @var string
     */
    protected $secretKey;

    /**
     * Дополнительные опции
     *
     * @var array
     */
    protected $options;

    /**
     * Клиент Guzzle
     * 
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * InvisibleReCaptcha.
     *
     * @param string $secretKey
     * @param string $siteKey
     * @param array $options
     */
    public function __construct($siteKey, $secretKey, $options = [])
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->setOptions($options);
        $this->setClient(
            new Client([
                'timeout' => $this->getOption('timeout', 5)
            ])
        );
    }

    /**
     * Получить reCaptcha js с дополнительным языковым параметром.
     *
     * @param string $lang
     *
     * @return string
     */
    public function getCaptchaJs($lang = null)
    {
        return $lang ? static::API_URI . '?hl=' . $lang : static::API_URI;
    }

    /**
     * Получить PolyfillJs
     *
     * @return string
     */
    public function getPolyfillJs()
    {
        return static::POLYFILL_URI;
    }

    /**
     * Рендеринг HTML reCaptcha с дополнительным языковым параметром.
     *
     * @return string
     */
    public function render($lang = null, $nonce = null)
    {
        $html = $this->renderPolyfill();
        $html .= $this->renderCaptchaHTML();
        $html .= $this->renderFooterJS($lang, $nonce);
        return $html;
    }

    /**
     * Рендеринг HTML reCaptcha из директивы Blade
     *
     * @return string
     */
    public function renderCaptcha(...$arguments)
    {
        return $this->render(...$arguments);
    }

    /**
     * Рендеринг только PolyfillJs
     *
     * @return string
     */
    public function renderPolyfill()
    {
        return '<script src="' . $this->getPolyfillJs() . '"></script>' . PHP_EOL;
    }

    /**
     * Рендеринг HTML код reCaptcha
     *
     * @return string
     */
    public function renderCaptchaHTML()
    {
        $html = '<div id="_g-recaptcha"></div>' . PHP_EOL;
        if ($this->getOption('hideBadge', false)) {
            $html .= '<style>.grecaptcha-badge{display:none !important;}</style>' . PHP_EOL;
        }

        $html .= '<div class="g-recaptcha" data-sitekey="' . $this->siteKey .'" ';
        $html .= 'data-size="invisible" data-callback="_submitForm" data-badge="' . $this->getOption('dataBadge', 'bottomright') . '"></div>';
        return $html;
    }

    /**
     * Рендеринг JS, необходимый для интеграции recaptcha.
     *
     * @return string
     */
    public function renderFooterJS(...$arguments)
    {
        $lang = Arr::get($arguments, 0);
        $nonce = Arr::get($arguments, 1);

        $html = '<script src="' . $this->getCaptchaJs($lang) . '" async defer';
        if (isset($nonce) && ! empty($nonce)) {
            $html .= ' nonce="' . $nonce . '"';
        }
        $html .= '></script>' . PHP_EOL;
        $html .= '<script>var _submitForm,_captchaForm,_captchaSubmit,_execute=true,_captchaBadge;</script>';
        $html .= "<script>window.addEventListener('load', _loadCaptcha);" . PHP_EOL;
        $html .= "function _loadCaptcha(){";
        if ($this->getOption('hideBadge', false)) {
            $html .= "_captchaBadge=document.querySelector('.grecaptcha-badge');";
            $html .= "if(_captchaBadge){_captchaBadge.style = 'display:none !important;';}" . PHP_EOL;
        }
        $html .= '_captchaForm=document.querySelector("#_g-recaptcha").closest("form");';
        $html .= "_captchaSubmit=_captchaForm.querySelector('[type=submit]');";
        $html .= '_submitForm=function(){if(typeof _submitEvent==="function"){_submitEvent();';
        $html .= 'grecaptcha.reset();}else{_captchaForm.submit();}};';
        $html .= "_captchaForm.addEventListener('submit',";
        $html .= "function(e){e.preventDefault();if(typeof _beforeSubmit==='function'){";
        $html .= "_execute=_beforeSubmit(e);}if(_execute){grecaptcha.execute();}});";
        if ($this->getOption('debug', false)) {
            $html .= $this->renderDebug();
        }
        $html .= "}</script>" . PHP_EOL;
        return $html;
    }

    /**
     * Получить отладочный код javascript.
     *
     * @return string
     */
    public function renderDebug()
    {
        $html = '';
        foreach (static::DEBUG_ELEMENTS as $element) {
            $html .= $this->consoleLog('"Checking element binding of ' . $element . '..."');
            $html .= $this->consoleLog($element . '!==undefined');
        }

        return $html;
    }

    /**
     * Получите функцию console.log для кода javascript.
     *
     * @return string
     */
    public function consoleLog($string)
    {
        return "console.log({$string});";
    }

    /**
     * Проверка ответа reCaptcha.
     *
     * @param string $response
     * @param string $clientIp
     *
     * @return bool
     */
    public function verifyResponse($response, $clientIp)
    {
        if (empty($response)) {
            return false;
        }

        $response = $this->sendVerifyRequest([
            'secret' => $this->secretKey,
            'remoteip' => $clientIp,
            'response' => $response
        ]);

        return isset($response['success']) && $response['success'] === true;
    }

    /**
     * Проверьте ответ reCaptcha с помощью Symfony Request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return $this->verifyResponse(
            $request->get('g-recaptcha-response'),
            $request->getClientIp()
        );
    }

    /**
     * Отправить запрос на проверку.
     *
     * @param array $query
     *
     * @return array
     */
    protected function sendVerifyRequest(array $query = [])
    {
        $response = $this->client->post(static::VERIFY_URI, [
            'form_params' => $query,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Функция получения ключа сайта
     *
     * @return string
     */
    public function getSiteKey()
    {
        return $this->siteKey;
    }

    /**
     * Функция получения секретного ключа
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Установить параметры
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Установить параметр
     *
     * @param string $key
     * @param string $value
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Функция получения опций
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Получить значение параметра по умолчанию для параметров.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function getOption($key, $value = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $value;
    }

    /**
     * Установить Guzzle клиент
     *
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Получить Guzzle клиент
     *
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }
}
