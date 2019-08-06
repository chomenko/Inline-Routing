<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Inline;

use Chomenko\InlineRouting\AnnotationExtension;
use Chomenko\InlineRouting\Extensions\EntityTransformExtension;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class EntityTransform extends AnnotationExtension
{

	/**
	 * @var string
	 */
	public $class;

	/**
	 * @var string
	 */
	public $parameter;

	/**
	 * @var int
	 */
	public $errorCode = 404;

	/**
	 * @var string
	 */
	public $errorMessage = "Not found. Item '{{ parameter }}' with value '{{ value }}' does not exist.";

	/**
	 * @return string
	 */
	public function getExtensionService(): string
	{
		return EntityTransformExtension::class;
	}

	/**
	 * @return string
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	/**
	 * @return string
	 */
	public function getParameter(): string
	{
		return $this->parameter;
	}

	/**
	 * @return int
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function getErrorMessage(array $parameters): string
	{
		$parameters = array_merge($parameters, get_object_vars($this));
		return $this->replacePatternMessage($this->errorMessage, $parameters);
	}

}
