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
	 * @param string $destination
	 * @param array $args
	 * @return string
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	public function link($destination, $args = [])
	{
		if ($this->inlineRouting->getRoute($destination)) {
			return $this->inlineRouting->createLink($destination, $args);
		}
		return parent::link($destination);
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
