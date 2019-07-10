<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Services;

class Config
{

	/**
	 * @var string
	 */
	private $tempDir;

	/**
	 * @var array
	 */
	private $loader = [
		"service" => NULL,
		"loadDeclareClasses" => TRUE,
		"loadRobotLoader" => FALSE,
		"in" => []
	];

	/**
	 * Config constructor.
	 * @param array $parameters
	 */
	public function __construct(array $parameters)
	{
		foreach (get_object_vars($this) as $name => $value) {
			if (array_key_exists($name, $parameters)) {
				$this->{$name} = $parameters[$name];
			}
		}
	}

	/**
	 * @return string
	 */
	public function getLoader(): string
	{
		return $this->loader["service"];
	}

	/**
	 * @return string
	 */
	public function getTempDir(): string
	{
		return $this->tempDir;
	}

	/**
	 * @return array
	 */
	public function getLoadPaths(): array
	{
		return $this->loader["in"];
	}

	/**
	 * @return bool
	 */
	public function isEnableLoadDeclareClasses(): bool
	{
		return $this->loader["loadDeclareClasses"];
	}

	/**
	 * @return bool
	 */
	public function isEnableLoadRobotLoader(): bool
	{
		return $this->loader["loadRobotLoader"];
	}

}
