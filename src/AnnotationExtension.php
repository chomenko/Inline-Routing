<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

use Chomenko\InlineRouting\Exception\RouteException;

abstract class AnnotationExtension implements IAnnotationExtension
{

	/**
	 * @param string $message
	 * @return array
	 */
	protected function matchPattern(string $message): array
	{
		$keys = [];
		preg_match_all("~{{\s([a-zA-Z_0-9]+)\s}}~", $message, $matches);
		foreach ($matches[0] as $index => $value) {
			$name = $matches[1][$index];
			$keys[$name] = [
				"search" => $value,
				"name" => $name,
			];
		}
		return $keys;
	}

	/**
	 * @param string $message
	 * @param array $parameters
	 * @return mixed
	 * @throws RouteException
	 */
	protected function replacePatternMessage(string $message, array $parameters)
	{
		foreach ($this->matchPattern($message) as $pattern) {
			if (!array_key_exists($pattern["name"], $parameters)) {
				throw RouteException::messageReplaceRequireParameter($pattern["name"]);
			}
			$value = $parameters[$pattern["name"]];
			$message = str_replace($pattern["search"], $value, $message);
		}
		return $message;
	}

}
