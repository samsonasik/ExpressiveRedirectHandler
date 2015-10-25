RedirectHandlerModule
=====================

[![Latest Version](https://img.shields.io/github/release/samsonasik/RedirectHandlerModule.svg?style=flat-square)](https://github.com/samsonasik/RedirectHandlerModule/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/samsonasik/RedirectHandlerModule.svg?branch=master)](https://travis-ci.org/samsonasik/RedirectHandlerModule)
[![Coverage Status](https://coveralls.io/repos/samsonasik/RedirectHandlerModule/badge.svg?branch=master)](https://coveralls.io/r/samsonasik/RedirectHandlerModule)
[![Downloads](https://img.shields.io/packagist/dt/samsonasik/redirect-handler-module.svg?style=flat-square)](https://packagist.org/packages/samsonasik/redirect-handler-module)

*RedirectHandlerModule* is a module for handling redirect when the given url to redirect plugin is not registered in your zf2 application. It simply override existing ZF2 redirect plugin, so we can just use it.

For example, we use `redirect()` plugin in your controller:

```php
$redirect = '/foo'; // may be a variable from GET
return $this->redirect()->toUrl($redirect);
```

if the passed `$redirect` as url is a valid and registered in the routes, it uses default `redirect()` implementation, otherwise, it will redirect to default `default_url` registered in `config/redirect-handler-module.local.php`:

For example, we define:

```php
return array(
    'allow_not_routed_url' => false,
    'default_url' => '/',
);
```

It means, we can't allow to make redirect to outside registered routes, whenever found un-registered url in routes, then we will be redirected to default_url.

Installation
------------

 - Require via composer
```bash
$ composer require samsonasik/redirect-handler-module:0.*
```

 - Copy `vendor/samsonasik/redirect-handler-module/config/redirect-handler-module.local.php.dist` to `config/autoload/redirect-handler-module.local.php` and modify on your needs.

 - register to `config/application.config.php`:

```php
return array(
    'modules' => array(
        // ...
        'RedirectHandlerModule',
    ),
);
```

Contributing
------------
Contributions are very welcome. Please read [CONTRIBUTING.md](https://github.com/samsonasik/RedirectHandlerModule/blob/master/CONTRIBUTING.md)

Credit
------

- [Abdul Malik Ikhsan](https://github.com/samsonasik)
- [All RedirectHandlerModule contributors](https://github.com/samsonasik/RedirectHandlerModule/contributors)
