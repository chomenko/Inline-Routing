<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Nette\Application\UI\Presenter;

abstract class Extension implements IExtension
{

	/**
	 * @param string $name
	 * @param \ReflectionMethod $method
	 * @return \ReflectionParameter|null
	 */
	protected function getMethodParameter(string $name, \ReflectionMethod $method): ?\ReflectionParameter
	{
		foreach ($method->getParameters() as $parameter) {
			if ($parameter->getName() == $name) {
				return $parameter;
			}
		}
		return NULL;
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param \ReflectionMethod $method
	 * @return mixed|void
	 */
	public function invoke(Presenter $presenter, Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $method): void
	{
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param mixed $result
	 */
	public function invoked(Presenter $presenter, Route $route, $result): void
	{
	}

}
