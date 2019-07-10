<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Symfony\Component\Routing\Loader\AnnotationClassLoader as BaseLoader;
use Symfony\Component\Routing\Route;

class AnnotationClassLoader extends BaseLoader
{

	const CLASS_OPTION_KEY = "_class";
	const METHOD_OPTION_KEY = "_method";
	const EXTENSIONS_OPTION_KEY = "_extensions";

	/**
	 * @param Route $route
	 * @param \ReflectionClass $class
	 * @param \ReflectionMethod $method
	 * @param mixed $annot
	 */
	protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
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
			self::EXTENSIONS_OPTION_KEY => $extensions,
		]);
	}

}
