<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Routing\Loader\AnnotationClassLoader as BaseLoader;
use Symfony\Component\Routing\Route as BaseRoute;

class AnnotationClassLoader extends BaseLoader
{

	const CLASS_OPTION_KEY = "_class";
	const METHOD_OPTION_KEY = "_method";
	const HASH_OPTION_KEY = "_hash";
	const EXTENSIONS_OPTION_KEY = "_extensions";
	const NAME_OPTION_KEY = "_name";

	/**
	 * @var Routing
	 */
	private $routing;

	/**
	 * AnnotationClassLoader constructor.
	 * @param Routing $routing
	 * @param Reader $reader
	 */
	public function __construct(Routing $routing, Reader $reader)
	{
		parent::__construct($reader);
		$this->routing = $routing;
	}

	/**
	 * @param BaseRoute|Route $route
	 * @param \ReflectionClass $class
	 * @param \ReflectionMethod $method
	 * @param mixed $annot
	 */
	protected function configureRoute(BaseRoute $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
	{
		$extensions = [];
		foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
			if ($annotation instanceof IAnnotationExtension) {
				$extensions[] = $annotation;
			}
		}
		$route->addOptions([
			self::CLASS_OPTION_KEY => $class->getName(),
			self::METHOD_OPTION_KEY => $method->getName(),
			self::HASH_OPTION_KEY => md5($class->getName() . $method->getName()),
			self::EXTENSIONS_OPTION_KEY => $extensions,
		]);
		Events::INITIALIZE_ROUTE; //link
		$this->routing->onInitializeRoute($route, $class, $method, $annot);
	}

	protected function createRoute($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition)
	{
		return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
	}

}
