<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

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

}
