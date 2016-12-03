ExpressiveRedirectHandler
=====================

[![PHP version](https://badge.fury.io/ph/samsonasik%2Fexpressive-redirect-handler.svg)](https://badge.fury.io/ph/samsonasik%2Fexpressive-redirect-handler)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler.svg?branch=master)](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler)
[![Coverage Status](https://coveralls.io/repos/samsonasik/ExpressiveRedirectHandler/badge.svg?branch=master)](https://coveralls.io/r/samsonasik/ExpressiveRedirectHandler)
[![Downloads](https://img.shields.io/packagist/dt/samsonasik/expressive-redirect-handler.svg?style=flat-square)](https://packagist.org/packages/samsonasik/expressive-redirect-handler)

*ExpressiveRedirectHandler* is a package that contains [zend-expressive](https://github.com/zendframework/zend-expressive) middleware for handling redirect that fit with [zend-expressive-skeleton](https://github.com/zendframework/zend-expressive-skeleton) for following conditions:

1. When the given url to `RedirectResponse` is not registered in routing config
-------------------------------------------------------------------------------

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

        'options' => [
            'exclude_urls' => [
                // 'https://www.github.com/samsonasik/ExpressiveRedirectHandler',
            ], // to allow excluded urls to always be redirected
            'exclude_hosts' => [
                // 'www.github.com'
            ],
        ],
    ],

    // ...
];
```

It means, we can't allow to make redirect to outside registered routes, whenever found un-registered url in routes, then we will be redirected to default_url.  For specific urls that exceptional ( allowed to be redirected even not registered in routes), you can register at exclude_urls or exclude_hosts options.

Also, it disable to self, so you can't redirect to self.

> if you define exclude_urls and/or exclude_hosts options, which one of them is your own current url, its your risk to still get "infinite" redirection loops. so, make sure exclude_urls is not your own urls.

2. When you want to redirect to specific url based on header status code
------------------------------------------------------------------------

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

Based on the configuration above, when header status code is 401 or 503, it will be redirected to its paired value.


Installation
------------

 - Require via composer
```bash
composer require samsonasik/expressive-redirect-handler
```

 - Copy `vendor/samsonasik/samsonasik/expressive-redirect-handler/config/expressive-redirect-handler.local.php.dist` to `config/autoload/expressive-redirect-handler.local.php` and modify on our needs.


Contributing
------------
Contributions are very welcome. Please read [CONTRIBUTING.md](https://github.com/samsonasik/ExpressiveRedirectHandler/blob/master/CONTRIBUTING.md)

Credit
------

- [Abdul Malik Ikhsan](https://github.com/samsonasik)
- [All ExpressiveRedirectHandler contributors](https://github.com/samsonasik/ExpressiveRedirectHandler/contributors)
