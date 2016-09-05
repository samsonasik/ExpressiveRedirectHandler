ExpressiveRedirectHandler
=====================

[![PHP version](https://badge.fury.io/ph/samsonasik%2Fexpressive-redirect-handler.svg)](https://badge.fury.io/ph/samsonasik%2Fexpressive-redirect-handler)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler.svg?branch=master)](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler)
[![Coverage Status](https://coveralls.io/repos/samsonasik/ExpressiveRedirectHandler/badge.svg?branch=master)](https://coveralls.io/r/samsonasik/ExpressiveRedirectHandler)
[![Downloads](https://img.shields.io/packagist/dt/samsonasik/expressive-redirect-handler.svg?style=flat-square)](https://packagist.org/packages/samsonasik/expressive-redirect-handler)

*ExpressiveRedirectHandler* is a package that contains [zend-expressive](https://github.com/zendframework/zend-expressive) middleware for handling redirect for following conditions:

- When the given url to `RedirectResponse` is not registered in your expressive application, that fit with [zend-expressive-skeleton](https://github.com/zendframework/zend-expressive-skeleton).  

For example, we use `RedirectResponse` instance in our Middleware:

```php
use Zend\Diactoros\Response\RedirectResponse;
// ...
$redirect = '/foo'; // may be a variable from GET
return new RedirectResponse($redirect);
```

if the passed `$redirect` as url is a valid and registered in the routes, it uses default `RedirectResponse` implementation, otherwise, it will redirect to default `default_url` registered in `config/autoload/expressive-redirect-handler.local.php`:

For example, we define:

```php
<?php

return [

    'expressive-redirect-handler' => [
        'allow_not_routed_url' => false,
        'default_url' => '/',
    ],

    // ...
];
```

It means, we can't allow to make redirect to outside registered routes, whenever found un-registered url in routes, then we will be redirected to default_url. Also, it disable to self, so you can't redirect to self.

- When you want to redirect to specific url based on header status code, by activate the `header_handler` config and specify the url you want.

```php
<?php 
return [

    'expressive-redirect-handler' => [
        'allow_not_routed_url' => false,
        'default_url' => '/',
        'header_handler' => [
            'enable' => true, // enable it!
            'headers' => [
                401 => '/login',
                503 => '/maintenance',
            ],
        ],
    ],
    // ...
];
```

Installation
------------

 - Require via composer
```bash
$ composer require samsonasik/expressive-redirect-handler:0.*
```

 - Copy `vendor/samsonasik/samsonasik/expressive-redirect-handler/config/expressive-redirect-handler.local.php.dist` to `config/autoload/expressive-redirect-handler.local.php` and modify on our needs.


Contributing
------------
Contributions are very welcome. Please read [CONTRIBUTING.md](https://github.com/samsonasik/ExpressiveRedirectHandler/blob/master/CONTRIBUTING.md)

Credit
------

- [Abdul Malik Ikhsan](https://github.com/samsonasik)
- [All ExpressiveRedirectHandler contributors](https://github.com/samsonasik/ExpressiveRedirectHandler/contributors)
