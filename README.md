Linio Tortilla
==============
[![Latest Stable Version](https://poser.pugx.org/linio/tortilla/v/stable.svg)](https://packagist.org/packages/linio/tortilla) [![License](https://poser.pugx.org/linio/tortilla/license.svg)](https://packagist.org/packages/linio/tortilla) [![Build Status](https://secure.travis-ci.org/LinioIT/tortilla.png)](http://travis-ci.org/LinioIT/tortilla) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LinioIT/tortilla/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LinioIT/tortilla/?branch=master)

Linio Tortilla is an extremely lightweight framework built as a middleware layer, providing the base application to be passed to the stack. It proudly supports the [Stack PHP](http://stackphp.com) convention and it's built upon [FastRoute](https://github.com/nikic/FastRoute) and [Pimple](http://pimple.sensiolabs.org). The objective is to keep the web abstraction layer as thin as possible, but also allowing you to compose it with as many middlewares as you need.

With this philosophy, you can quickly build up a tailor-made, framework-agnostic HTTP stack. And, just like a tasty super-thin tortilla, wrap it with anything you can dream of.

Install
-------

The recommended way to install Linio Tortilla is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "linio/tortilla": "0.2.*"
    }
}
```

Tests
-----

To run the test suite, you need install the dependencies via composer, then
run PHPUnit.

    $ composer install
    $ phpunit

Goals
-----

* Efficiency at all costs
* Reduce, as much as possible, the amount of moving parts under the hood
* Tackle complexity

Usage
-----

Preparing your tortilla is quite simple. This is an example of a simple front-controller:

```php
<?php

require '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Linio\Tortilla\Application;

$app = new Application();
$app->get('/hello/{name}', function (Request $request, $name) {
    return new Response('Hello ' . $name);
});

// add your Stack PHP middlewares to $app

$app->run();
```

You can also define controllers as services instead of closures. Since a Tortilla
application is also a Pimple container:

```php
<?php

require '../vendor/autoload.php';

use Linio\Tortilla\Application;

$app = new Application();
$app['default'] = function () {
    return new Acme\Controller\DefaultController();
};
$app->get('/hello/{name}', 'default:indexAction');

// add your Stack PHP middlewares to $app

$app->run();
```

Defining actions
----------------

The Linio Tortilla dispatcher will always dispatch the HTTP request to your controller
actions as the first argument. The method signature looks like this:

```php

use Symfony\Component\HttpFoundation\Request;

public function yourAction(Request $request, $arg1, $arg2, ...);
```

We do this to keep the dispatching procedure efficient. If we decided to use PHP's
reflection mechanism to decide whether to inject the request object or not, we would
lose precious milliseconds.
