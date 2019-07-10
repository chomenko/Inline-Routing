<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */
namespace Chomenko\InlineRouting;

use Symfony\Component\Routing\Route;

interface IExtension
{

	/**
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param \ReflectionMethod $method
	 * @return mixed
	 */
	public function invoke(Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $method);

}
