Linio Tortilla
==============
[![Latest Stable Version](https://poser.pugx.org/linio/tortilla/v/stable.svg)](https://packagist.org/packages/linio/tortilla) [![License](https://poser.pugx.org/linio/tortilla/license.svg)](https://packagist.org/packages/linio/tortilla) [![Build Status](https://secure.travis-ci.org/LinioIT/tortilla.png)](http://travis-ci.org/LinioIT/tortilla) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LinioIT/tortilla/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LinioIT/tortilla/?branch=master)

Linio Tortilla is provides a very thin web abstraction layer built on top of [FastRoute](https://github.com/nikic/FastRoute) and [Pimple](http://pimple.sensiolabs.org). No frills, light and efficient. We believe that the [web is just a delivery mechanism](https://youtu.be/WpkDN78P884?t=8m50s) and no framework should dictate how you design the architecture of your applications.

And, just like a tasty super-thin tortilla, you can wrap it around anything you want.

Install
-------

The recommended way to install Linio Tortilla is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "linio/tortilla": "~1.2"
    }
}
```

If you need help preparing your tortilla, there are recipes available:

    $ composer create-project linio/burrito-recipe full_app
    $ composer create-project linio/tortilla-recipe basic_app

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
