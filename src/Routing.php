<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Chomenko\InlineRouting\Exception\RouteException;
use Chomenko\InlineRouting\Services\Config;
use Chomenko\InlineRouting\Services\ILoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\SmartObject;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Tracy\Debugger;

/**
 *  @method onInvokeMethod(Presenter $presenter, Route $route, array $parameters, Arguments $arguments)
 */
class Routing
{

	use SmartObject;

	const CACHE_NAMESPACE = "InlineRouting.Mapping";

	/**
	 * @var callable[]
	 */
	public $onInvokeMethod = [];

	/**
	 * @var ILoader
	 */
	private $loader;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var AnnotationReader
	 */
	private $reader;

	/**
	 * @var AnnotationClassLoader
	 */
	private $classLoader;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var RouteCollection
	 */
	private $routeCollection;

	/**
	 * @var IAnnotationExtension[]
	 */
	private $extensions = [];

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var UrlGenerator
	 */
	private $generator;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var RequestContext
	 */
	private $context;

	/**
	 * Routing constructor.
	 * @param ILoader $loader
	 * @param Reader $reader
	 * @param Config $config
	 * @param IStorage $storage
	 * @param Container $container
	 * @param IRequest $request
	 */
	public function __construct(
		ILoader $loader,
		Reader $reader,
		Config $config,
		IStorage $storage,
		Container $container,
		IRequest $request
	) {
		$this->context = $this->createContext($request);
		$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
		$this->loader = $loader;
		$this->reader = $reader;

		$this->classLoader = new AnnotationClassLoader($this->reader);
		$this->config = $config;
		$this->container = $container;
		$this->request = $request;
	}

	/**
	 * @internal
	 */
	public function initialize()
	{
		$routeCollection = $this->cache->load("RouteCollection");
		if (!$routeCollection || !Debugger::$productionMode) {
			$this->loader->initialize();
			$classes = $this->loader->getPresenters();
			$routeCollection = new RouteCollection();
			foreach ($classes as $class) {
				$collection = $this->classLoader->load($class);
				if ($collection->count() > 0) {
					$routeCollection->addCollection($collection);
				}
			}
			$this->cache->save("RouteCollection", $routeCollection);
		}
		$this->routeCollection = $routeCollection;
		$this->generator = new UrlGenerator($routeCollection, $this->context);
	}

	/**
	 * @param string $routeName
	 * @param array $args
	 * @return string
	 */
	public function createLink(string $routeName, array $args = [])
	{
		return $this->generator->generate($routeName, $args);
	}

	/**
	 * @return RouteCollection
	 */
	public function getRouteCollection(): RouteCollection
	{
		return $this->routeCollection;
	}

	/**
	 * @param string $name
	 * @return Route|null
	 */
	public function getRoute(string $name)
	{
		return $this->getRouteCollection()->get($name);
	}

	/**
	 * @param string $class
	 * @return mixed|Route
	 */
	public function getRouteByClass($class)
	{
		foreach ($this->getRouteCollection() as $route) {
			if ($route->getOption(AnnotationClassLoader::CLASS_OPTION_KEY) === $class) {
				return $route;
			}
		}
		return NULL;
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param array $parameters
	 * @throws RouteException
	 * @throws \ReflectionException
	 */
	public function invokeRoute(Presenter $presenter, Route $route, array $parameters = [])
	{
		/** @var IAnnotationExtension[] $extensions */
		$extensions = $route->getOption(AnnotationClassLoader::EXTENSIONS_OPTION_KEY);
		$class = $route->getOption(AnnotationClassLoader::CLASS_OPTION_KEY);
		$method = $route->getOption(AnnotationClassLoader::METHOD_OPTION_KEY);

		$refClass = new \ReflectionClass($class);
		$refMethod = $refClass->getMethod($method);

		$arguments = new Arguments();
		foreach ($refMethod->getParameters() as $parameter) {
			if (array_key_exists($parameter->getName(), $parameters)) {
				$arguments->set($parameter->getName(), $parameters[$parameter->getName()]);
			} else {
				$arguments->set($parameter->getName(), $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : NULL);
			}
		}

		foreach ($extensions as $annotation) {
			$extension = $this->getExtension($annotation->getExtensionService());
			$extension->invoke($route, $annotation, $parameters, $arguments, $refMethod);
		}

		$this->onInvokeMethod($presenter, $route, $parameters, $arguments);
		$refMethod->invokeArgs($presenter, $arguments->toArray());
	}

	/**
	 * @param IRequest $httpRequest
	 * @return RequestContext
	 */
	public function createContext(IRequest $httpRequest)
	{
		$url = $httpRequest->getUrl();
		return new RequestContext(
			trim($url->getBaseUrl(), "/"),
			$httpRequest->getMethod(),
			$url->getHost(),
			$url->getScheme(),
			80,
			443,
			$url->getPath(),
			$url->getQuery()
		);
	}

	/**
	 * @param string $name
	 * @return IExtension
	 * @throws RouteException
	 */
	protected function getExtension(string $name): IExtension
	{
		if (!array_key_exists($name, $this->extensions)) {
			$service = $this->container->getByType($name);

			if (!$service instanceof IExtension) {
				throw RouteException::extendServiceMustInstance();
			}
			$this->extensions[$name] = $service;
		}
		return $this->extensions[$name];
	}

}