<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Symfony\Component\Routing\Route;

/**
 * @target Nette\Application\UI\Presenter
 */
trait InlineRouting
{

	/**
	 * @var Route
	 */
	private $_route;

	/**
	 * @var Routing
	 */
	private $inlineRouting;

	/**
	 * @param Routing $routing
	 */
	public function injectRouting(Routing $routing)
	{
		$this->inlineRouting = $routing;
	}

	/**
	 * @throws BadRequestException
	 */
	public function actionInlineAction()
	{
		/** @var Request $request */
		$request = $this->getRequest();

		/** @var Route $route */
		$route = $request->getParameter("_route");

		if (!$route) {
			throw new BadRequestException("Page not found", 404);
		}
		$this->_route = $route;

		$method = $this->_route->getOption(AnnotationClassLoader::METHOD_OPTION_KEY);
		$this->setView("InlineRoute");
		$this->tryCall($this->formatActionMethod(ucfirst($method)), $this->params);
	}

	/**
	 * @param object $component
	 * @param string $destination
	 * @param array $args
	 * @param string $mode forward|redirect|link
	 * @return Request|string|null
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	protected function createRequest($component, $destination, array $args, $mode)
	{
		/** @var Request $request */
		$request = $this->request;
		$route = NULL;
		if ($this->inlineRouting->getRoute($destination)) {
			$route = $this->inlineRouting->getRoute($destination);
		}

		if ($destination == "this") {
			$route = $request->getParameter("_route");
		}

		if ($route instanceof Route) {
			$routeName = $route->getOption(AnnotationClassLoader::HASH_OPTION_KEY);

			$match = $request->getParameter("_match");
			$variables = $route->compile()->getVariables();

			$parameters = [];
			foreach ($args as $key => $value) {
				if (is_int($key) && array_key_exists($key, $variables)) {
					$parameters[$variables[$key]] = $value;
					continue;
				}
				$parameters[$key] = $value;
			}

			foreach ($variables as $name) {
				if (!array_key_exists($name, $parameters) && array_key_exists($name, $match)) {
					$parameters[$name] = $match[$name];
				}
			}

			$path = explode('\\', get_class($this));
			$className = array_pop($path);


			if (substr($className, -9) === "Presenter") {
				$className = substr($className, 0, -9);
			}
			$parameters["_match"] = $parameters;
			$parameters["action"] = "inlineAction";
			$parameters["_route"] = $route;

			$appRequest = new Request("Inline:" . $routeName . ":" . $className, Request::FORWARD, $parameters);

			if ($mode === "forward") {
				return $appRequest;
			}
			return $this->requestToUrl($appRequest);
		}
		return parent::createRequest($component, $destination, $args, $mode);
	}

	/**
	 * @param string|Request $destination
	 * @param array $args
	 * @throws \Nette\Application\AbortException
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	public function forward($destination, $args = [])
	{
		if (!$destination instanceof Request) {
			$request = $this->createRequest($this, $destination, $args, 'forward');
			if ($request) {
				$destination = $request;
			}
		}
		parent::forward($destination, $args);
	}


	/**
	 * @throws Exception\RouteException
	 * @throws \ReflectionException
	 */
	public function renderInlineRoute()
	{
		/** @var Request $request */
		$request = $this->getRequest();
		$parameters = $request->getParameters();

		$method = $this->_route->getOption(AnnotationClassLoader::METHOD_OPTION_KEY);
		$this->setView(ucfirst($method));
		$this->inlineRouting->invokeRoute($this, $this->_route, $parameters);
	}

}
