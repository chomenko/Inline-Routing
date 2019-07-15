<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Listeners;

use Chomenko\InlineRouting\AnnotationClassLoader;
use Chomenko\InlineRouting\Routing;
use Chomenko\PresenterFactoryListener\EventArgsGetPresenter;
use Chomenko\PresenterFactoryListener\Events;
use Kdyby\Events\Subscriber;

class PresenterFactory implements Subscriber
{

	/**
	 * @var Routing
	 */
	private $routing;

	/**
	 * PresenterFactory constructor.
	 * @param Routing $routing
	 */
	public function __construct(Routing $routing)
	{
		$this->routing = $routing;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return [
			Events::GET_PRESENTER => "onGetPresenter",
		];
	}

	/**
	 * @param EventArgsGetPresenter $eventArgs
	 */
	public function onGetPresenter(EventArgsGetPresenter $eventArgs)
	{
		$presenter = $eventArgs->getName();
		$exp = explode(":", $presenter);
		if (isset($exp[0]) && $exp[0] == "Inline") {
			$routeHash = $exp[1];
			$route = $this->routing->getRouteByHash($routeHash);
			$class = $route->getOption(AnnotationClassLoader::CLASS_OPTION_KEY);
			$eventArgs->setPresenter($class);
		}
	}

}
