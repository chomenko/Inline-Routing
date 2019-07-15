<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Chomenko\InlineRouting\Exception\RouteException;

class Route extends \Symfony\Component\Routing\Route
{

	/**
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->getOption(AnnotationClassLoader::HASH_OPTION_KEY);
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->getOption(AnnotationClassLoader::METHOD_OPTION_KEY);
	}

	/**
	 * @return string
	 */
	public function getClass(): string
	{
		return $this->getOption(AnnotationClassLoader::CLASS_OPTION_KEY);
	}

	/**
	 * @return array
	 */
	public function getExtensions(): array
	{
		$extensions = $this->getOption(AnnotationClassLoader::EXTENSIONS_OPTION_KEY);
		return $extensions ? $extensions : [];
	}

	/**
	 * @return string
	 * @throws RouteException
	 */
	public function getName(): string
	{
		$name = $this->getOption(AnnotationClassLoader::NAME_OPTION_KEY);
		if (!$name) {
			throw RouteException::routeIsNotInitialized();
		}
		return $name;
	}

}
