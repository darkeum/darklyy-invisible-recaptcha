Добавляет Invisible reCAPTCHA в Darklyy
==========
[![Latest Version on Packagist](https://img.shields.io/packagist/v/darkeum/darklyy-json-attributes.svg?style=flat-square)](https://packagist.org/packages//darkeum/darklyy-json-attributes)
[![Total Downloads](https://img.shields.io/packagist/dt/darkeum/darklyy-json-attributes.svg?style=flat-square)](https://packagist.org/packages/darkeum/darklyy-json-attributes)

## Почему Invisible reCAPTCHA?

Invisible reCAPTCHA — это улучшенная версия reCAPTCHA v2 (без капчи). В reCAPTCHA v2 пользователям нужно нажать кнопку «Я не робот», чтобы доказать, что они люди. В невидимой reCAPTCHA не будет встроенного окна с капчей, по которому пользователи могут щелкнуть. Это совершенно незаметно! Только значок будет отображаться в нижней части страницы, чтобы намекнуть пользователям, что ваш веб-сайт использует эту технологию. (Значок можно скрыть, но не рекомендуется.)

## Установка

```
composer require darkeum/darklyy-invisible-recaptcha
```

## Конфигурация
Для начала вам необходимо получить публичные и приватны ключи  `Invisible reCAPTCHA`.

Когда вы получили ключи добавьте их  **.env** файл в переменные`INVISIBLE_RECAPTCHA_SITEKEY`, `INVISIBLE_RECAPTCHA_SECRETKEY`

```
// обязательно
INVISIBLE_RECAPTCHA_SITEKEY={siteKey}
INVISIBLE_RECAPTCHA_SECRETKEY={secretKey}

// опционально
INVISIBLE_RECAPTCHA_BADGEHIDE=false
INVISIBLE_RECAPTCHA_DATABADGE='bottomright'
INVISIBLE_RECAPTCHA_TIMEOUT=5
INVISIBLE_RECAPTCHA_DEBUG=false
```

> Вы можете установить три разных стиля капчи: `bottomright`, `bottomleft`, `inline`

> Если вы установите `INVISIBLE_RECAPTCHA_BADGEHIDE` в значение true, вы можете скрыть логотип значка.

> Вы можете увидеть статус привязки элементов каптчи в консоли браузера, установив значение `INVISIBLE_RECAPTCHA_DEBUG` в true.

### Использование

Перед визуализацией капчи помните об этих замечаниях:

* Функция `render()` или `renderHTML()` должна вызываться внутри формы.
* Вы должны убедиться, что атрибут `type` вашей кнопки отправки должен быть `submit`.
* В вашей форме может быть только одна кнопка отправки.

##### Отображение reCAPTCHA в View

```php
@recaptcha

// или

{!! app('recaptcha')->render() !!}

```

С пользовательской языком:

```php
@recaptcha('ru')

// или

{!! app('recaptcha')->render('ru') !!}

```

##### Проверка

Добавьте `'g-recaptcha-response' => 'required|recaptcha'` в массив правил.

```php
$validate = Validator::make(Input::all(), [
    'g-recaptcha-response' => 'required|recaptcha'
]);

```
## Работа с функцией отправки
Используйте эту функцию только тогда, когда вам нужно взять на себя все управление после нажатия кнопки отправки. Проверка Recaptcha не будет запущена, если вы вернете false в этой функции.

```javascript
_beforeSubmit = function(e) {
    console.log('submit button clicked.');
    // ваш код 
    return false;
}
```

## Настроить функцию отправки
Если вы хотите настроить функцию отправки, например: сделать что-то после нажатия кнопки отправки или изменить отправку на вызов ajax и т. д.

Единственное, что вам нужно сделать, это реализовать `_submitEvent` в javascript.
```javascript
_submitEvent = function() {
    console.log('submit button clicked.');
    // ваш код 
    _submitForm();
}
```
Вот пример использования отправки ajax (с использованием jquery)
```javascript
_submitEvent = function() {
    $.ajax({
        type: "POST",
        url: "{{route('message.send')}}",
         data: {
            "name": $("#name").val(),
            "email": $("#email").val(),
            "content": $("#content").val(),
            // важный! не забудьте отправить `g-recaptcha-response`
            "g-recaptcha-response": $("#g-recaptcha-response").val()
        },
        dataType: "json",
        success: function(data) {
            // успех
        },
        error: function(data) {
            // ошибка
        }
    });
};
```