<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Chomenko\InlineRouting\Exceptions\RouteException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
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

/**
 *  @method onInvokeMethod(Presenter $presenter, Route $route, array $parameters, Arguments $arguments)
 *  @method onInvokedMethod(Presenter $presenter, Route $route, $result)
 *  @method onInitializeRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
 *  @method onInitialized(RouteCollection $collection)
 */
class Routing
{

	use SmartObject;

	const CACHE_NAMESPACE = "InlineRouting.Mapping";

	/**
	 * @var callable[]
	 */
	public $onInitialized = [];

	/**
	 * @var callable[]
	 */
	public $onInvokeMethod = [];

	/**
	 * @var callable[]
	 */
	public $onInvokedMethod = [];

	/**
	 * @var callable[]
	 */
	public $onInitializeRoute = [];

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
		$this->config = $config;
		$this->context = $this->createContext($request);
		$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
		$this->loader = $loader;
		$this->reader = $reader;
		$this->classLoader = new AnnotationClassLoader($this, $this->reader);
		$this->container = $container;
		$this->request = $request;
	}

	/**
	 * @internal
	 */
	public function initialize()
	{
		$routeCollection = $this->cache->load("RouteCollection");
		if (!$routeCollection) {
			$this->loader->initialize();
			$classes = $this->loader->getPresenters();
			$routeCollection = new RouteCollection();
			foreach ($classes as $class) {
				$collection = $this->classLoader->load($class);
				if ($collection->count() > 0) {
					$routeCollection->addCollection($collection);
				}
			}
			foreach ($routeCollection as $name => $route) {
				$route->setOption(AnnotationClassLoader::NAME_OPTION_KEY, $name);
			}
			$this->cache->save("RouteCollection", $routeCollection);
		}

		Events::INITIALIZED; //link
		$this->onInitialized($routeCollection);

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
	public function getRoute(string $name): ?Route
	{
		return $this->getRouteCollection()->get($name);
	}

	/**
	 * @param string $hash
	 * @return Route|null
	 */
	public function getRouteByHash(string $hash): ?Route
	{
		/** @var Route $route */
		foreach ($this->getRouteCollection() as $route) {
			if ($hash === $route->getHash()) {
				return $route;
			}
		}
	}

	/**
	 * @param string $class
	 * @return mixed|Route
	 */
	public function getRouteByClass($class)
	{
		/** @var Route $route */
		foreach ($this->getRouteCollection() as $route) {
			if ($route->getClass() === $class) {
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
		$refClass = new \ReflectionClass($route->getClass());
		$refMethod = $refClass->getMethod($route->getMethod());

		$arguments = new Arguments();
		foreach ($refMethod->getParameters() as $parameter) {
			if (array_key_exists($parameter->getName(), $parameters)) {
				$arguments->set($parameter->getName(), $parameters[$parameter->getName()]);
			} else {
				$arguments->set($parameter->getName(), $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : NULL);
			}
		}
		/** @var IExtension[] $extensions */
		$extensions = [];
		foreach ($route->getExtensions() as $annotation) {
			$extension = $this->getExtension($annotation->getExtensionService());
			$extensions[] = $extension;
			$extension->invoke($presenter, $route, $annotation, $parameters, $arguments, $refMethod);
		}
		Events::INVOKE_METHOD; //link
		$this->onInvokeMethod($presenter, $route, $parameters, $arguments);

		$result = $refMethod->invokeArgs($presenter, $arguments->toArray());

		foreach ($extensions as $extension) {
			$extension->invoked($presenter, $route, $result);
		}
		Events::INVOKED_METHOD; //link
		$this->onInvokedMethod($presenter, $route, $result);
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
			$this->config->getHttpPort(),
			$this->config->getHttpsPort(),
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
