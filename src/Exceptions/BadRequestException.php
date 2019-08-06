<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Exceptions;

use Nette\Application;

class BadRequestException extends Application\BadRequestException
{

	/**
	 * @var Attribute|null
	 */
	private $parameter;

	/**
	 * @return Attribute|null
	 */
	public function getParameter(): ?Attribute
	{
		return $this->parameter;
	}

	/**
	 * @param Attribute|null $parameter
	 */
	public function setParameter(?Attribute $parameter): void
	{
		$this->parameter = $parameter;
	}

}
