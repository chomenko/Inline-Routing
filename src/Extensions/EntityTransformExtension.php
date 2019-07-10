<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Extensions;

use Chomenko\InlineRouting\Extension;
use Chomenko\InlineRouting\IAnnotationExtension;
use Chomenko\InlineRouting\Arguments;
use Chomenko\InlineRouting\Exception\RouteException;
use Chomenko\InlineRouting\Inline\EntityTransform;
use Doctrine\ORM\EntityManager;
use Nette\Application\BadRequestException;
use Symfony\Component\Routing\Route;

class EntityTransformExtension extends Extension
{

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * EntityTransformExtension constructor.
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param \ReflectionMethod $method
	 * @return mixed|void
	 * @throws BadRequestException
	 * @throws RouteException
	 */
	public function invoke(Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $method)
	{
		if (!$annotation instanceof EntityTransform) {
			throw RouteException::entityTransformerMustInstance();
		}

		$parameter = $annotation->getParameter();

		if (!array_key_exists($parameter, $parameters)) {
			throw RouteException::entityTransformerRequireParameter($parameter);
		}

		$value = $parameters[$parameter];
		$entity = $this->entityManager->getRepository($annotation->getClass())->find($value);

		if (!$entity) {
			$parameter = $this->getMethodParameter($parameter, $method);
			if (!$parameter) {
				return;
			}
			if ($parameter->isOptional()) {
				return;
			}
			if ($parameter->hasType()) {
				if ($parameter->getType()->allowsNull() && $value === NULL) {
					return;
				}
			}
			throw new BadRequestException($annotation->getErrorMessage(["value" => $value]), $annotation->getErrorCode());
		}
		$arguments->set($parameter, $entity);
	}

}
