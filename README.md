ExpressiveRedirectHandler
=====================

[![Latest Version](https://img.shields.io/github/release/samsonasik/ExpressiveRedirectHandler.svg?style=flat-square)](https://github.com/samsonasik/ExpressiveRedirectHandler/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler.svg?branch=master)](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler)
[![Coverage Status](https://coveralls.io/repos/samsonasik/ExpressiveRedirectHandler/badge.svg?branch=master)](https://coveralls.io/r/samsonasik/ExpressiveRedirectHandler)
[![Downloads](https://img.shields.io/packagist/dt/samsonasik/redirect-handler-module.svg?style=flat-square)](https://packagist.org/packages/samsonasik/redirect-handler-module)

*ExpressiveRedirectHandler* is a package that contains [zend-expressive](https://github.com/zendframework/zend-expressive) middleware for handling redirect when the given url to `RedirectResponse` is not registered in your zf2 application, that fit with [zend-expressive-skeleton](https://github.com/zendframework/zend-expressive-skeleton).  

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

It means, we can't allow to make redirect to outside registered routes, whenever found un-registered url in routes, then we will be redirected to default_url.

Installation
------------

 - Require via composer
```bash
$ composer require samsonasik/expressive-redirect-handler:0.*
```

 - Copy `vendor/samsonasik/samsonasik/expressive-redirect-handler/config/expressive-redirect-handler.local.php.dist` to `config/autoload/expressive-redirect-handler.local.php` and modify on your needs.


Contributing
------------
Contributions are very welcome. Please read [CONTRIBUTING.md](https://github.com/samsonasik/ExpressiveRedirectHandler/blob/master/CONTRIBUTING.md)

Credit
------

- [Abdul Malik Ikhsan](https://github.com/samsonasik)
- [All ExpressiveRedirectHandler contributors](https://github.com/samsonasik/ExpressiveRedirectHandler/contributors)
