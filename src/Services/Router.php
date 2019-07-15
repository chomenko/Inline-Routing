<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Services;

use Chomenko\InlineRouting\Route;
use Chomenko\InlineRouting\Routing;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\Request as AppRequest;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Http\Url;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class Router implements IRouter
{

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Routing
	 */
	private $routing;

	/**
	 * @var IPresenterFactory
	 */
	private $presenterFactory;

	public function __construct(Request $request, Routing $routing, IPresenterFactory $presenterFactory)
	{
		$this->request = $request;
		$this->routing = $routing;
		$this->presenterFactory = $presenterFactory;
	}

	/**
	 * @param IRequest $httpRequest
	 * @return AppRequest|null
	 */
	public function match(IRequest $httpRequest)
	{
		$url = $httpRequest->getUrl();
		$context = $this->routing->createContext($httpRequest);
		$collection = $this->routing->getRouteCollection();
		$urlMatcher = new UrlMatcher($collection, $context);

		try {
			$match = $urlMatcher->match("/" . $url->getPathInfo());
		} catch (NoConfigurationException $exception) {
			return NULL;
		} catch (ResourceNotFoundException $exception) {
			return NULL;
		}

		/** @var Route $route */
		$route = $collection->get($match["_route"]);
		unset($match["_route"]);
		$parameters = $match;
		$parameters["action"] = "inlineAction";
		$parameters["_route"] = $route;
		$parameters["_match"] = $match;

		foreach ($url->getQueryParameters() as $name => $value) {
			if (!array_key_exists($name, $parameters)) {
				$parameters[$name] = $value;
			}
		}

		$class = $route->getClass();
		$presenterName = $this->presenterFactory->unformatPresenterClass($class);

		if (!$presenterName) {
			$path = explode('\\', $class);
			$className = array_pop($path);
			if (substr($className, -9) === "Presenter") {
				$className = substr($className, 0, -9);
			}
			$presenterName = "Inline:" . $route->getHash() . ":" . $className;
		}
		return new AppRequest($presenterName, NULL, $parameters);
	}

	/**
	 * @param AppRequest $appRequest
	 * @param Url $refUrl
	 * @return string|null
	 */
	public function constructUrl(AppRequest $appRequest, Url $refUrl)
	{
		$exp = explode(":", $appRequest->getPresenterName());

		if (isset($exp[0]) && $exp[0] === "Inline" && isset($exp[1])) {
			$routeHash = $exp[1];
			$route = $this->routing->getRouteByHash($routeHash);
			$name = $route->getName();

			$parameters = $appRequest->getParameters();
			unset($parameters["action"]);
			unset($parameters["_match"]);
			unset($parameters["_route"]);

			return new Url($this->routing->createLink($name, $parameters));
		}
		return NULL;
	}

}
