<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Exceptions;

class Attribute
{

	/**
	 * @var string
	 */
	private $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

}
