<?php

return [
    // публичный ключ вашего сайта.
    'siteKey' => env('INVISIBLE_RECAPTCHA_SITEKEY'),
    // секретный ключ вашего сайта.
    'secretKey' => env('INVISIBLE_RECAPTCHA_SECRETKEY'),
    // другие параметры для настройки ваших конфигурации
    'options' => [
        // установите значение true, если вы хотите скрыть свой значок recaptcha
        'hideBadge' => env('INVISIBLE_RECAPTCHA_BADGEHIDE', false),
        // необязательно, переместите значок reCAPTCHA. 'inline' позволяет вам управлять CSS.
        // доступные значения: bottomright, bottomleft, inline
        'dataBadge' => env('INVISIBLE_RECAPTCHA_DATABADGE', 'bottomright'),
        // значение тайм-аута для клиента guzzle
        'timeout' => env('INVISIBLE_RECAPTCHA_TIMEOUT', 5),
        // установите значение true, чтобы показать статус привязки на вашей консоли javascript
        'debug' => env('INVISIBLE_RECAPTCHA_DEBUG', false)
    ]
];
