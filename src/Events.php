<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

class Events
{
	/**
	 * onInvokeMethod(Presenter $presenter, Route $route, array $parameters, Arguments $arguments)
	 */
	const INVOKE_METHOD = Routing::class . "::onInvokeMethod";

	/**
	 * onInvokeMethod(Presenter $presenter, Route $route, array $parameters, Arguments $arguments)
	 */
	const INVOKED_METHOD = Routing::class . "::onInvokedMethod";

	/**
	 * onInitializeRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
	 */
	const INITIALIZE_ROUTE = Routing::class . "::onInitializeRoute";

	/**
	 * onInitialized(RouteCollection $collection)
	 */
	const INITIALIZED = Routing::class . "::onInitializeRoute";

}
