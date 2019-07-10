<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Services;

use Chomenko\InlineRouting\AnnotationClassLoader;
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

		$route = $collection->get($match["_route"]);
		unset($match["_route"]);
		$parameters = $match;
		$parameters["action"] = "inlineAction";
		$parameters["_route"] = $route;
		$parameters["_match"] = $match;

		$class = $route->getOption(AnnotationClassLoader::CLASS_OPTION_KEY);
		$presenterName = $this->presenterFactory->unformatPresenterClass($class);
		return new AppRequest($presenterName, NULL, $parameters);
	}

	/**
	 * @param AppRequest $appRequest
	 * @param Url $refUrl
	 * @return string|null
	 */
	public function constructUrl(AppRequest $appRequest, Url $refUrl)
	{
		return NULL;
	}

}
