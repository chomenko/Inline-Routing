# Inline Routing

[Symfony Routing](https://symfony.com/doc/current/routing.html) adapter into [Nette](https://nette.org/)

## Required

Install these packages with [composer](https://getcomposer.org/). Packages kdyby/doctrine, kdyby/annotations require configurations.

- [symfony/config](https://symfony.com/doc/current/components/config.html)
- [symfony/routing](https://symfony.com/doc/current/routing.html#creating-routes)
- [kdyby/doctrine](https://github.com/Kdyby/Doctrine/blob/master/docs/en/index.md)
- [kdyby/annotations](https://github.com/Kdyby/Annotations/blob/master/docs/en/index.md)

## Install and configure

The best way to install chomenko/inline-routing is using [Composer](http://getcomposer.org/):

````sh
composer require chomenko/inline-routing
````

and now enable the extension using your neon config

```neon
extensions:
	console: Kdyby\Console\DI\ConsoleExtension
	events: Kdyby\Events\DI\EventsExtension
	annotations: Kdyby\Annotations\DI\AnnotationsExtension
	doctrine: Kdyby\Doctrine\DI\OrmExtension
	
	inlineRouting: Chomenko\InlineRouting\DI\InlineRoutingExtension
```

and adding trait into base presenter

```php
<?php

namespace App;

use Chomenko\InlineRouting\InlineRouting;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
	use InlineRouting;
}

```
## Usage

Create simple route. You can create a route by using annotations ``@Inline\Route``<br>
The basic Nette route must be created for the presenter.

```php
<?php

namespace App;

use Chomenko\InlineRouting\Inline;

class SimplePresenter extends BasePresenter
{
	
	/**
	 * @link http://example.com/helow-world
	 *
	 * @Inline\Route("/hello-world", name="first-route")
	 */
	public function helloWorld()
	{
		$this->payload->hello = "world";
		$this->sendPayload();
	}
	
	/**
	 * @link http://example.com/hello-parameters/this-si-foo/prefix-this-is-bar
	 * 
	 * @Inline\Route("/hello-parameters/{foo}/prefix-{bar}", name="hello-parameters")
	 *
 	 * @param mixed $foo
 	 * @param mixed $bar
	 */
	public function helloParameters($foo, $bar)
	{
		$this->payload->foo = $foo;
		$this->payload->bar = $bar;
		$this->sendPayload();
	}
	
}

```

you can also create a routine over the presenter

```php
<?php

namespace App;

use Chomenko\InlineRouting\Inline;

/**
 * @Inline\Route("/prefix", name="prefix_")
 */
class SimplePresenter extends BasePresenter
{
	
	/**
	 * @link http://example.com/prefix/hello-world
   	 * 
	 * @Inline\Route("/hello-world", name="first-route")
	 */
	public function helloWorld()
	{
		$this->payload->hello = "world";
		$this->sendPayload();
	}
	
}

```

you can also transform a value into an entity

```php
<?php

namespace App;

use Chomenko\InlineRouting\Inline;
use Entity\User;

/**
 * @Inline\Route("/user", name="users-")
 */
class SimplePresenter extends BasePresenter
{
	
	/**
	 * @link http://example.com/user/detail/1
   	 * 
	 * @Inline\Route("/detail/{userId}", name="detail")
 	 * @Inline\EntityTransform(
	 *     class="Entity\User",
	 *     parameter="userId"
	 * ) 
	 * 
 	 * @param User $user
	 */
	public function detail(User $user)
	{
		$this->payload->userName = $user->getName();
		$this->sendPayload();
	}
	
}

```
