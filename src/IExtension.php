<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */
namespace Chomenko\InlineRouting;

use Nette\Application\UI\Presenter;

interface IExtension
{

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param \ReflectionMethod $method
	 * @return mixed
	 */
	public function invoke(Presenter $presenter, Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $method): void;

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param mixed $result
	 * @return void
	 */
	public function invoked(Presenter $presenter, Route $route, $result): void;

}
