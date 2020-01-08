ExpressiveRedirectHandler
=====================

[![PHP version](https://badge.fury.io/ph/samsonasik%2Fexpressive-redirect-handler.svg)](https://badge.fury.io/ph/samsonasik%2Fexpressive-redirect-handler)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler.svg?branch=master)](https://travis-ci.org/samsonasik/ExpressiveRedirectHandler)
[![Coverage Status](https://coveralls.io/repos/samsonasik/ExpressiveRedirectHandler/badge.svg?branch=master)](https://coveralls.io/r/samsonasik/ExpressiveRedirectHandler)
[![Downloads](https://poser.pugx.org/samsonasik/expressive-redirect-handler/downloads)](https://packagist.org/packages/samsonasik/expressive-redirect-handler)

> This is README for version ^2.0 which only support Mezzio version 3 with php ^7.1.

> This is README for version ^1.0, , you can read at [version 1.* readme](https://github.com/samsonasik/ExpressiveRedirectHandler/tree/1.x.x) which support ZF Expressive version 3 with php ^7.1.

> For version 0.*, you can read at [version 0.* readme](https://github.com/samsonasik/ExpressiveRedirectHandler/tree/0.x.x) which still ZF Expressive version 1 and 2 with php ^5.6|^7.0 support.

*ExpressiveRedirectHandler* is a package that contains [Mezzio](https://github.com/mezzio/mezzio) middleware for handling redirect that fit with [mezzio-skeleton](https://github.com/mezzio/mezzio-skeleton) for following conditions:

1. When the given url to `RedirectResponse` is not registered in routing config
-------------------------------------------------------------------------------

For example, we use `RedirectResponse` instance in our Middleware:

```php
use Laminas\Diactoros\Response\RedirectResponse;
// ...
$redirect = '/foo'; // may be a variable from GET
return new RedirectResponse($redirect);
```

if the passed `$redirect` as url is a valid and registered in the routes, it uses default `RedirectResponse` implementation, otherwise, it will redirect to default `default_url` registered in `config/autoload/mezzio-redirect-handler.local.php`:

For example, we define:

```php
<?php

return [

    'mezzio-redirect-handler' => [
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

It means, we can't allow to make redirect to outside registered routes, whenever found un-registered url in routes, then we will be redirected to default_url. It also disable redirect to self, so you can't redirect to self.

For specific urls that exceptional ( allowed to be redirected even not registered in routes), you can register at `exclude_urls`/`exclude_hosts` options.

> if you define exclude_urls/exclude_hosts options, which one of them is your own current url/host/domain, its your risk to still get "infinite" redirection loops. so, make sure exclude_urls/exclude_hosts is not your current own.

2. When you want to redirect to specific url based on header status code
------------------------------------------------------------------------

```php
<?php
return [

    'mezzio-redirect-handler' => [
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

 - Copy `vendor/samsonasik/expressive-redirect-handler/config/mezzio-redirect-handler.local.php.dist` to `config/autoload/mezzio-redirect-handler.local.php` and modify on our needs.

 - Open `config/pipeline.php` and add:

```php
$app->pipe(ExpressiveRedirectHandler\Middleware\RedirectHandlerAction::class);
```

at the very first pipeline records.

Contributing
------------
Contributions are very welcome. Please read [CONTRIBUTING.md](https://github.com/samsonasik/ExpressiveRedirectHandler/blob/master/CONTRIBUTING.md)

Credit
------

- [Abdul Malik Ikhsan](https://github.com/samsonasik)
- [All ExpressiveRedirectHandler contributors](https://github.com/samsonasik/ExpressiveRedirectHandler/contributors)
