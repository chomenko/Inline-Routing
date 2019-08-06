<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Exceptions;

use Chomenko\InlineRouting\Extension;
use Chomenko\InlineRouting\Inline\EntityTransform;

class RouteException extends \Exception
{

	/**
	 * @return RouteException
	 */
	public static function extendServiceMustInstance()
	{
		return new self('Extend service must implement \'' . Extension::class . '\'');
	}


	/**
	 * @return RouteException
	 */
	public static function entityTransformerMustInstance()
	{
		return new self('Entity transformer annotation must instance \'' . EntityTransform::class . '\'');
	}

	/**
	 * @param string $parameter
	 * @return RouteException
	 */
	public static function entityTransformerRequireParameter(string $parameter)
	{
		return new self("Entity transformer require parameter '$parameter'");
	}


	/**
	 * @param string $parameter
	 * @return RouteException
	 */
	public static function messageReplaceRequireParameter(string $parameter)
	{
		return new self("Message replace require parameter '$parameter'");
	}

	/**
	 * @return RouteException
	 */
	public static function routeIsNotInitialized()
	{
		return new self("Route is not initialized");
	}

}
